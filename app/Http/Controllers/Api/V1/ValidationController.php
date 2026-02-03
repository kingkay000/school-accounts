<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionWorkflowService;

class ValidationController extends Controller
{
    public function validateTransaction(Transaction $transaction, TransactionWorkflowService $workflow)
    {
        $transaction = $workflow->refreshStatus($transaction->refresh());

        $openExceptions = $transaction->exceptions()->where('status', 'open')->get();

        return response()->json([
            'message' => 'Validation completed',
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
            'open_exceptions' => $openExceptions,
        ], 200);
    }
}
