<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\TaxAssessment;
use App\Models\Transaction;
use App\Models\TransactionException;

class DashboardController extends Controller
{
    public function index()
    {
        $exceptions = TransactionException::with('transaction')
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingTax = TaxAssessment::with('transaction')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingApprovals = Approval::with('approver')
            ->where('action', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentTransactions = Transaction::orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.action_items', compact('exceptions', 'pendingTax', 'pendingApprovals', 'recentTransactions'));
    }
}
