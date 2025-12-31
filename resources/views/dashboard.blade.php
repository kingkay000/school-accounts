<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Accounts</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --accent-color: #38bdf8;
            --danger-color: #ef4444;
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
            padding: 40px 20px;
        }

        header {
            margin-bottom: 60px;
            text-align: center;
        }

        h1 {
            font-size: 3rem;
            font-weight: 600;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.8s ease-out;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 16px;
        }

        th {
            text-align: left;
            color: #94a3b8;
            font-weight: 400;
            padding: 0 24px;
        }

        td {
            background: rgba(15, 23, 42, 0.4);
            padding: 24px;
        }

        tr td:first-child {
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }

        tr td:last-child {
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        tr {
            transition: transform 0.2s;
        }

        tr:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-verified,
        .status-tax_ready {
            background: rgba(34, 197, 94, 0.2);
            color: var(--success-color);
        }

        .status-unverified {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger-color);
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-color);
            transition: 0.2s;
            font-family: inherit;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-btn:hover {
            color: var(--accent-color);
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 100;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            max-width: 80%;
            max-height: 80%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 50px rgba(56, 189, 248, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-content img {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Ledger & Verification</h1>
        </header>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankLogs as $log)
                        <tr>
                            <td>{{ $log->transaction_date }}</td>
                            <td>
                                {{ $log->description }}
                                @if($log->bank_source)
                                    <div style="font-size: 0.8em; color: #94a3b8;">{{ $log->bank_source }}</div>
                                @endif
                            </td>
                            <td>₦{{ number_format($log->amount, 2) }}</td>
                            <td>{{ ucfirst($log->type) }}</td>
                            <td>
                                <span class="status-badge status-{{ $log->status }}">
                                    {{ str_replace('_', ' ', ucfirst($log->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->attachments->count() > 0)
                                    <button class="icon-btn"
                                        onclick="openModal('{{ $log->attachments->first()->thumbnail_url }}')">
                                        Show Receipt 📷
                                    </button>
                                @else
                                    @if($log->type == 'debit')
                                        <span style="color: #ef4444; font-size: 1.5rem; cursor:help;"
                                            title="Missing Receipt">🔴</span>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px; color: #64748b;">No bank transactions
                                found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="imageModal" class="modal" onclick="closeModal()">
        <div class="modal-content">
            <img id="modalImage" src="" alt="Receipt">
        </div>
    </div>

    <script>
        function openModal(url) {
             if (!url) return;
            document.getElementById('modalImage').src = url;
            document.getElementById('imageModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('imageModal').classList.remove('active');
    }
    </script>
</body>

</html>