<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankLog;

class DashboardController extends Controller
{
    public function index()
    {
        $bankLogs = BankLog::with('attachments')->orderBy('transaction_date', 'desc')->get();
        return view('dashboard', compact('bankLogs'));
    }
}
