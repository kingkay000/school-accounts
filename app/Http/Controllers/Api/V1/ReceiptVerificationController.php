<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankLog;
use App\Models\Attachment;
use App\Models\LedgerEntry;
use Carbon\Carbon;

class ReceiptVerificationController extends Controller
{
    public function webhook(Request $request)
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
                'google_drive_file_id' => $validated['file_id'],
                'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'file_name' => $validated['file_name'] ?? null,
            ]);

            // 3. Update Bank Log
            $bankLog->update(['status' => 'tax_ready']);

            return response()->json(['message' => 'Matched and Merged', 'bank_log_id' => $bankLog->id], 200);
        } else {
            // No match found
            Attachment::create([
                'bank_log_id' => null,
                'ledger_entry_id' => null,
                'google_drive_file_id' => $validated['file_id'],
                'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                'extracted_text' => $validated['extracted_text'] ?? null,
                'file_name' => $validated['file_name'] ?? null,
            ]);

            return response()->json(['message' => 'Receipt stored, no matching bank log found'], 202);
        }
    }
}
