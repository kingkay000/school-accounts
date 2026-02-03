<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankLog;
use App\Models\Attachment;
use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\TransactionWorkflowService;
use Carbon\Carbon;

class ReceiptVerificationController extends Controller
{
    public function webhook(Request $request, TransactionWorkflowService $workflow)
    {
        // validation
        $validated = $request->validate([
            'file_id' => 'required|string',
            'file_name' => 'nullable|string',
            'extracted_text' => 'nullable|string',
            'amount' => 'required|numeric',
            'date' => 'nullable|date',
            'vendor_name' => 'nullable|string',
            'gl_code_suggestion' => 'nullable|string',
            'gl_code' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
        ]);

        $receiptAmount = abs($validated['amount']);

        // Search for Bank Log (debit) +/- 2 days
        // We assume receipt date is close to bank transaction date. 
        $searchDate = $validated['date'] ? Carbon::parse($validated['date']) : Carbon::now();

        $startDate = $searchDate->copy()->subDays(2);
        $endDate = $searchDate->copy()->addDays(2);

        $bankLog = BankLog::where('amount', $receiptAmount)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'unverified')
            ->first();

        if ($bankLog) {
            // Match found!

            $vendor = null;
            if (!empty($validated['vendor_name'])) {
                $vendor = Vendor::firstOrCreate(
                    ['legal_name' => $validated['vendor_name']],
                    ['vat_registered' => false, 'category' => 'mixed']
                );
            }

            $transaction = Transaction::firstOrCreate(
                ['bank_log_id' => $bankLog->id],
                [
                    'direction' => $bankLog->type === 'credit' ? 'in' : 'out',
                    'amount' => $bankLog->amount,
                    'txn_date' => $bankLog->transaction_date,
                    'counterparty_name' => $validated['vendor_name'] ?? $bankLog->description,
                    'transaction_type' => 'other',
                    'status' => 'matched',
                    'vendor_id' => $vendor?->id,
                ]
            );

            // 1. Create Ledger Entry
            $ledgerEntry = LedgerEntry::create([
                'entry_date' => $bankLog->transaction_date,
                'description' => $validated['vendor_name'] ?? $bankLog->description,
                'amount' => $bankLog->amount,
                'gl_code' => $validated['gl_code_suggestion'] ?? $validated['gl_code'] ?? '4400',
                'bank_log_id' => $bankLog->id,
            ]);

            // 2. Create Attachment
            Attachment::create([
                'bank_log_id' => $bankLog->id,
                'ledger_entry_id' => $ledgerEntry->id,
                'transaction_id' => $transaction->id,
                'vendor_id' => $vendor?->id,
                'document_type' => 'receipt',
                'google_drive_file_id' => $validated['file_id'],
                'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'file_name' => $validated['file_name'] ?? null,
            ]);

            // 3. Update Bank Log
            $bankLog->update(['status' => 'tax_ready']);

            $workflow->refreshStatus($transaction->refresh());

            return response()->json(['message' => 'Matched and Merged', 'bank_log_id' => $bankLog->id], 200);
        } else {
            // No match found
            $vendor = null;
            if (!empty($validated['vendor_name'])) {
                $vendor = Vendor::firstOrCreate(
                    ['legal_name' => $validated['vendor_name']],
                    ['vat_registered' => false, 'category' => 'mixed']
                );
            }

            $transaction = Transaction::create([
                'direction' => 'out',
                'amount' => $receiptAmount,
                'txn_date' => $validated['date'] ?? null,
                'counterparty_name' => $validated['vendor_name'] ?? null,
                'transaction_type' => 'other',
                'status' => 'documented',
                'vendor_id' => $vendor?->id,
            ]);

            Attachment::create([
                'bank_log_id' => null,
                'ledger_entry_id' => null,
                'transaction_id' => $transaction->id,
                'vendor_id' => $vendor?->id,
                'document_type' => 'receipt',
                'google_drive_file_id' => $validated['file_id'],
                'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'file_name' => $validated['file_name'] ?? null,
            ]);

            $workflow->refreshStatus($transaction->refresh());

            return response()->json(['message' => 'Receipt stored, no matching bank log found'], 202);
        }
    }
}
