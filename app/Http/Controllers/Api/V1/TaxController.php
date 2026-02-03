<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxAssessment;
use App\Models\Transaction;
use App\Services\TransactionWorkflowService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function assess(Request $request, Transaction $transaction, TransactionWorkflowService $workflow)
    {
        $validated = $request->validate([
            'vat_applicable' => 'nullable|boolean',
            'vat_rate' => 'nullable|numeric',
            'wht_applicable' => 'nullable|boolean',
            'wht_rate' => 'nullable|numeric',
            'status' => 'nullable|in:pending,withheld,remitted',
            'notes' => 'nullable|string',
        ]);

        $vendor = $transaction->vendor;
        $vatApplicable = $validated['vat_applicable'] ?? ($vendor?->vat_registered ?? false);
        $vatRateInput = $validated['vat_rate'] ?? 0.075;
        $vatRateFraction = $vatRateInput > 1 ? $vatRateInput / 100 : $vatRateInput;
        $vatAmount = $vatApplicable ? round($transaction->amount * $vatRateFraction, 2) : 0;

        $defaultWhtRates = [
            'goods' => 0.05,
            'service' => 0.1,
            'asset' => 0.05,
            'other' => 0.05,
        ];
        $whtRateInput = $validated['wht_rate'] ?? ($defaultWhtRates[$transaction->transaction_type] ?? 0.05);
        $whtRateFraction = $whtRateInput > 1 ? $whtRateInput / 100 : $whtRateInput;
        $whtApplicable = $validated['wht_applicable'] ?? in_array($transaction->transaction_type, ['service', 'asset', 'goods'], true);
        $whtAmount = $whtApplicable ? round($transaction->amount * $whtRateFraction, 2) : 0;

        $assessment = TaxAssessment::create([
            'transaction_id' => $transaction->id,
            'vat_applicable' => $vatApplicable,
            'vat_amount' => $vatAmount,
            'wht_applicable' => $whtApplicable,
            'wht_rate' => $whtRateFraction * 100,
            'wht_amount' => $whtAmount,
            'status' => $validated['status'] ?? 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        $transaction = $workflow->refreshStatus($transaction->refresh());

        return response()->json([
            'message' => 'Tax assessment recorded',
            'tax_assessment_id' => $assessment->id,
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
        ], 201);
    }
}
