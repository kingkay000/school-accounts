<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankLog;
use App\Models\Transaction;
use App\Services\TransactionWorkflowService;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    public function confirm(Request $request, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id',
            'bank_log_id' => 'required|integer|exists:bank_logs,id',
        ]);

        $transaction = Transaction::findOrFail($validated['transaction_id']);
        $bankLog = BankLog::findOrFail($validated['bank_log_id']);

        $transaction->update([
            'bank_log_id' => $bankLog->id,
            'amount' => $transaction->amount ?: $bankLog->amount,
            'txn_date' => $transaction->txn_date ?: $bankLog->transaction_date,
        ]);

        $bankLog->update(['status' => 'matched']);

        $transaction = $workflow->refreshStatus($transaction->refresh());

        return response()->json([
            'message' => 'Transaction matched successfully',
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
        ], 200);
    }
}
