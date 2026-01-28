<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Accounts</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --accent-color: #38bdf8;
            --danger-color: #ef4444;
            --input-bg: rgba(15, 23, 42, 0.6);
            --glass-border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image:
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.1) 0px, transparent 50%);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            margin: 0 0 8px 0;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: #94a3b8;
            margin: 0;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        button:hover {
            opacity: 0.9;
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 6px;
        }

        @keyframes slideUp {
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
    <div class="login-card">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to access the ledger</p>
        </div>

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="admin@school.com">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>
</body>

</html>