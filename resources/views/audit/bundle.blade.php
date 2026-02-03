<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Bundle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --accent-color: #38bdf8;
            --glass-border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 24px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .label {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }

        .pill {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.2);
            color: var(--accent-color);
            font-size: 0.8rem;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        li:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Transaction Case File #{{ $transaction->id }}</h1>

        <div class="card grid">
            <div>
                <div class="label">Counterparty</div>
                <div>{{ $transaction->counterparty_name ?? 'Unknown' }}</div>
            </div>
            <div>
                <div class="label">Amount</div>
                <div>₦{{ number_format($transaction->amount, 2) }}</div>
            </div>
            <div>
                <div class="label">Date</div>
                <div>{{ $transaction->txn_date?->format('Y-m-d') ?? 'No date' }}</div>
            </div>
            <div>
                <div class="label">Status</div>
                <div class="pill">{{ str_replace('_', ' ', ucfirst($transaction->status)) }}</div>
            </div>
        </div>

        <div class="card">
            <h2>Linked Documents</h2>
            <ul>
                @forelse($transaction->attachments as $attachment)
                    <li>
                        {{ ucfirst(str_replace('_', ' ', $attachment->document_type)) }} -
                        <a href="https://drive.google.com/file/d/{{ $attachment->google_drive_file_id }}/view" target="_blank" style="color: var(--accent-color);">
                            {{ $attachment->file_name ?? $attachment->google_drive_file_id }}
                        </a>
                    </li>
                @empty
                    <li>No documents linked yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <h2>Tax Assessment</h2>
            <ul>
                @forelse($transaction->taxAssessments as $assessment)
                    <li>
                        VAT: ₦{{ number_format($assessment->vat_amount, 2) }} | WHT: ₦{{ number_format($assessment->wht_amount, 2) }}
                        <span class="pill">{{ ucfirst($assessment->status) }}</span>
                    </li>
                @empty
                    <li>No tax assessment recorded.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <h2>Approval History</h2>
            <ul>
                @forelse($transaction->approvals as $approval)
                    <li>
                        {{ ucfirst($approval->action) }} by {{ $approval->approver?->name ?? 'System' }}
                        <span class="pill">{{ $approval->created_at->format('Y-m-d') }}</span>
                    </li>
                @empty
                    <li>No approvals recorded.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <h2>Open Exceptions</h2>
            <ul>
                @php
                    $openExceptions = $transaction->exceptions->where('status', 'open');
                @endphp
                @forelse($openExceptions as $exception)
                    <li>{{ str_replace('_', ' ', ucfirst($exception->type)) }}</li>
                @empty
                    <li>No open exceptions.</li>
                @endforelse
            </ul>
        </div>
    </div>
</body>

</html>
