<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankLog;
use App\Models\Transaction;
use App\Services\TransactionWorkflowService;
use Carbon\Carbon;

class BankTransactionController extends Controller
{
    /**
     * Handle incoming bank transaction webhook from n8n (Email Parser).
     */
    public function store(Request $request, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:debit,credit',
            'bank_source' => 'nullable|string', // e.g., "GTBank Email Alert"
        ]);

        // Prevent duplicate entries (same amount + description + type + bank source in a short window)
        // This avoids blocking legitimate multi-alert sequences like principal + charges.
        $transactionDate = Carbon::parse($validated['transaction_date']);
        $bankSource = $validated['bank_source'] ?? 'Unknown Source';
        $amount = round($validated['amount'], 2);

        $exists = BankLog::whereDate('transaction_date', $transactionDate->toDateString())
            ->where('amount', $amount)
            ->where('type', $validated['type'])
            ->where('description', $validated['description'])
            ->where('bank_source', $bankSource)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Duplicate transaction skipped'], 200);
        }

        $log = BankLog::create([
            'transaction_date' => Carbon::parse($validated['transaction_date']),
            'description' => $validated['description'],
            'amount' => $amount,
            'type' => $validated['type'],
            'bank_source' => $bankSource,
            'status' => 'unverified'
        ]);

        $transaction = Transaction::create([
            'bank_log_id' => $log->id,
            'direction' => $validated['type'] === 'credit' ? 'in' : 'out',
            'amount' => $amount,
            'txn_date' => Carbon::parse($validated['transaction_date']),
            'counterparty_name' => $validated['description'],
            'narration' => $validated['description'],
            'transaction_type' => 'other',
            'status' => 'captured',
        ]);

        $workflow->refreshStatus($transaction->refresh());

        return response()->json([
            'message' => 'Bank transaction logged successfully',
            'id' => $log->id,
            'transaction_id' => $transaction->id,
        ], 201);
    }
}
