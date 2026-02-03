<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TransactionException;
use App\Services\TransactionWorkflowService;
use Illuminate\Http\Request;

class ExceptionController extends Controller
{
    public function index()
    {
        $exceptions = TransactionException::with('transaction')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($exceptions);
    }

    public function resolve(Request $request, TransactionException $exception, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'status' => 'required|in:resolved,dismissed',
            'resolved_by' => 'nullable|integer|exists:users,id',
        ]);

        $exception->update([
            'status' => $validated['status'],
            'resolved_by' => $validated['resolved_by'] ?? null,
        ]);

        $transaction = $exception->transaction;
        if ($transaction) {
            $workflow->refreshStatus($transaction->refresh());
        }

        return response()->json([
            'message' => 'Exception updated',
            'exception_id' => $exception->id,
        ], 200);
    }
}
