<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseRequest;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\TransactionWorkflowService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'file_id' => 'required|string',
            'file_name' => 'nullable|string',
            'document_type' => 'nullable|in:invoice,receipt,delivery_note,grn,completion_report,purchase_request,asset_registry,payment,other',
            'vendor_name' => 'nullable|string',
            'vendor_tin' => 'nullable|string',
            'vat_registered' => 'nullable|boolean',
            'amount' => 'nullable|numeric',
            'date' => 'nullable|date',
            'transaction_type' => 'nullable|in:goods,service,asset,other',
            'direction' => 'nullable|in:in,out',
            'metadata' => 'nullable|array',
            'file_hash' => 'nullable|string',
            'bank_log_id' => 'nullable|integer|exists:bank_logs,id',
            'transaction_id' => 'nullable|integer|exists:transactions,id',
            'counterparty_name' => 'nullable|string',
            'narration' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'extracted_text' => 'nullable|string',
        ]);

        $vendor = null;
        if (!empty($validated['vendor_name'])) {
            $vendor = Vendor::firstOrCreate(
                ['legal_name' => $validated['vendor_name']],
                [
                    'tin' => $validated['vendor_tin'] ?? null,
                    'vat_registered' => $validated['vat_registered'] ?? false,
                    'category' => 'mixed',
                ]
            );
        }

        $transaction = null;
        if (!empty($validated['transaction_id'])) {
            $transaction = Transaction::findOrFail($validated['transaction_id']);
            $transaction->fill([
                'amount' => $validated['amount'] ?? $transaction->amount,
                'txn_date' => $validated['date'] ?? $transaction->txn_date,
                'counterparty_name' => $validated['counterparty_name'] ?? $validated['vendor_name'] ?? $transaction->counterparty_name,
                'narration' => $validated['narration'] ?? $transaction->narration,
                'transaction_type' => $validated['transaction_type'] ?? $transaction->transaction_type,
                'direction' => $validated['direction'] ?? $transaction->direction,
            ]);
            $transaction->save();
        } elseif (!empty($validated['bank_log_id'])) {
            $transaction = Transaction::firstOrCreate(
                ['bank_log_id' => $validated['bank_log_id']],
                [
                    'direction' => $validated['direction'] ?? 'out',
                    'amount' => $validated['amount'] ?? 0,
                    'txn_date' => $validated['date'] ?? null,
                    'counterparty_name' => $validated['counterparty_name'] ?? $validated['vendor_name'] ?? null,
                    'narration' => $validated['narration'] ?? null,
                    'transaction_type' => $validated['transaction_type'] ?? 'other',
                    'status' => 'documented',
                    'vendor_id' => $vendor?->id,
                ]
            );
        }

        if (!$transaction) {
            $transaction = Transaction::create([
                'direction' => $validated['direction'] ?? 'out',
                'amount' => $validated['amount'] ?? 0,
                'txn_date' => $validated['date'] ?? null,
                'counterparty_name' => $validated['counterparty_name'] ?? $validated['vendor_name'] ?? null,
                'narration' => $validated['narration'] ?? null,
                'transaction_type' => $validated['transaction_type'] ?? 'other',
                'status' => 'documented',
                'vendor_id' => $vendor?->id,
            ]);
        }

        if ($vendor && !$transaction->vendor_id) {
            $transaction->update(['vendor_id' => $vendor->id]);
        }

        $documentType = $validated['document_type'] ?? 'other';
        $attachment = Attachment::create([
            'bank_log_id' => $validated['bank_log_id'] ?? $transaction->bank_log_id,
            'transaction_id' => $transaction->id,
            'vendor_id' => $vendor?->id,
            'document_type' => $documentType,
            'metadata' => $validated['metadata'] ?? null,
            'file_hash' => $validated['file_hash'] ?? null,
            'google_drive_file_id' => $validated['file_id'],
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'extracted_text' => $validated['extracted_text'] ?? null,
            'file_name' => $validated['file_name'] ?? null,
        ]);

        if ($documentType === 'purchase_request') {
            PurchaseRequest::firstOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'description' => $validated['narration'] ?? null,
                    'amount' => $validated['amount'] ?? 0,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]
            );
        }

        if (in_array($documentType, ['grn', 'delivery_note'], true)) {
            GoodsReceivedNote::firstOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'notes' => $validated['narration'] ?? null,
                    'status' => 'received',
                    'received_at' => now(),
                ]
            );
        }

        $transaction = $workflow->refreshStatus($transaction->refresh());

        return response()->json([
            'message' => 'Document ingested successfully',
            'transaction_id' => $transaction->id,
            'attachment_id' => $attachment->id,
            'status' => $transaction->status,
        ], 201);
    }

    public function classify(Request $request, Attachment $attachment, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'document_type' => 'nullable|string',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'metadata' => 'nullable|array',
        ]);

        $attachment->update([
            'document_type' => $validated['document_type'] ?? $attachment->document_type,
            'vendor_id' => $validated['vendor_id'] ?? $attachment->vendor_id,
            'metadata' => $validated['metadata'] ?? $attachment->metadata,
        ]);

        if ($attachment->transaction_id) {
            $transaction = Transaction::find($attachment->transaction_id);
            if ($transaction) {
                $workflow->refreshStatus($transaction);
            }
        }

        return response()->json([
            'message' => 'Document classification updated',
            'attachment_id' => $attachment->id,
        ], 200);
    }
}
