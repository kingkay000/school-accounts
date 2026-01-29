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

        // Prevent duplicate entries (simple check based on date, amount, and description)
        // Prevent duplicate entries
        // Note: Using precise timestamp check
        $transactionDate = Carbon::parse($validated['transaction_date']);

        $exists = BankLog::where('transaction_date', $transactionDate)
            ->where('amount', $validated['amount'])
            ->where('type', $validated['type'])
            // Description might vary slightly, so we can be optional or strict. 
            // Strict is better for now to avoid duplicates.
            ->where('description', $validated['description'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Duplicate transaction skipped'], 200);
        }

        $log = BankLog::create([
            'transaction_date' => Carbon::parse($validated['transaction_date']),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'bank_source' => $validated['bank_source'] ?? 'Unknown Source',
            'status' => 'unverified'
        ]);

        $transaction = Transaction::create([
            'bank_log_id' => $log->id,
            'direction' => $validated['type'] === 'credit' ? 'in' : 'out',
            'amount' => $validated['amount'],
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
