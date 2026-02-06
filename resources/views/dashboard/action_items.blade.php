<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit-Ready Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --accent-color: #38bdf8;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #22c55e;
            --glass-border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image:
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.1) 0px, transparent 50%);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .card h2 {
            margin-top: 0;
            font-size: 1.25rem;
        }

        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(56, 189, 248, 0.2);
            color: var(--accent-color);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        th {
            text-align: left;
            color: #94a3b8;
            font-weight: 400;
            padding: 0 16px;
            font-size: 0.85rem;
        }

        td {
            background: rgba(15, 23, 42, 0.4);
            padding: 16px;
            font-size: 0.9rem;
        }

        tr td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        tr td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-open {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger-color);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning-color);
        }

        .status-ready {
            background: rgba(34, 197, 94, 0.2);
            color: var(--success-color);
        }

        .subtle {
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Audit-Ready Action Items</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    style="background: none; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: 0.2s;">
                    Logout
                </button>
            </form>
        </header>

        <div class="grid">
            <div class="card">
                <h2>Open Exceptions</h2>
                <p class="subtle">Missing evidence or mismatches that block audit readiness.</p>
                <div class="tag">{{ $exceptions->count() }} open</div>
            </div>
            <div class="card">
                <h2>Pending Tax Actions</h2>
                <p class="subtle">VAT/WHT assessments waiting for approval or remittance.</p>
                <div class="tag">{{ $pendingTax->count() }} pending</div>
            </div>
            <div class="card">
                <h2>Approvals Queue</h2>
                <p class="subtle">PR approvals and overrides that require sign-off.</p>
                <div class="tag">{{ $pendingApprovals->count() }} pending</div>
            </div>
            <div class="card">
                <h2>Recent Transactions</h2>
                <p class="subtle">Latest case files updated by the workflow.</p>
                <div class="tag">{{ $recentTransactions->count() }} updated</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 32px;">
            <h2>Exception Queue</h2>
            <table>
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Issue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $exception)
                        <tr>
                            <td>
                                #{{ $exception->transaction_id }}
                                <div class="subtle">{{ $exception->transaction?->counterparty_name ?? 'Unknown counterparty' }}</div>
                            </td>
                            <td>{{ str_replace('_', ' ', ucfirst($exception->type)) }}</td>
                            <td><span class="status-pill status-open">Open</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="subtle">No open exceptions 🎉</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card" style="margin-bottom: 32px;">
            <h2>Tax Actions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>VAT</th>
                        <th>WHT</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTax as $assessment)
                        <tr>
                            <td>#{{ $assessment->transaction_id }}</td>
                            <td>₦{{ number_format($assessment->vat_amount, 2) }}</td>
                            <td>₦{{ number_format($assessment->wht_amount, 2) }}</td>
                            <td><span class="status-pill status-pending">Pending</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="subtle">No pending tax actions.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card" style="margin-bottom: 32px;">
            <h2>Approval Tasks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Target</th>
                        <th>Action</th>
                        <th>Requested By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingApprovals as $approval)
                        <tr>
                            <td>{{ ucfirst($approval->target_type) }} #{{ $approval->target_id }}</td>
                            <td>{{ ucfirst($approval->action) }}</td>
                            <td>{{ $approval->approver?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="subtle">No approval tasks queued.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Recent Transactions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Case File</th>
                        <th>Counterparty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>
                                <a href="{{ route('audit.bundle.show', $transaction) }}" style="color: var(--accent-color); text-decoration: none;">
                                    #{{ $transaction->id }}
                                </a>
                                <div class="subtle">{{ $transaction->txn_date?->format('Y-m-d') ?? 'No date' }}</div>
                            </td>
                            <td>{{ $transaction->counterparty_name ?? 'Unknown' }}</td>
                            <td>
                                <span class="status-pill {{ $transaction->status === 'audit_ready' ? 'status-ready' : 'status-pending' }}">
                                    {{ str_replace('_', ' ', ucfirst($transaction->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="subtle">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
