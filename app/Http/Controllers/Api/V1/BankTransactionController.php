<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankLog;
use Carbon\Carbon;

class BankTransactionController extends Controller
{
    /**
     * Handle incoming bank transaction webhook from n8n (Email Parser).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:debit,credit',
            'bank_source' => 'nullable|string', // e.g., "GTBank Email Alert"
        ]);

        // Prevent duplicate entries (simple check based on date, amount, and description)
        $exists = BankLog::where('transaction_date', Carbon::parse($validated['transaction_date'])->format('Y-m-d'))
            ->where('amount', $validated['amount'])
            ->where('description', $validated['description'])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Duplicate transaction skipped'], 200);
        }

        $log = BankLog::create([
            'transaction_date' => Carbon::parse($validated['transaction_date']),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'status' => 'unverified'
        ]);

        return response()->json([
            'message' => 'Bank transaction logged successfully',
            'id' => $log->id
        ], 201);
    }
}
