<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class AuditBundleController extends Controller
{
    public function show(Transaction $transaction)
    {
        $transaction->load(['attachments', 'taxAssessments', 'approvals', 'exceptions']);

        return view('audit.bundle', compact('transaction'));
    }
}
