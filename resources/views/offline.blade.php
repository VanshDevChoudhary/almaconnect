<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You're offline — AlmaConnect</title>
    <meta name="theme-color" content="#4F46E5">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 50%, #ede9fe 100%);
            padding: 1.5rem;
        }
        .card {
            text-align: center;
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            max-width: 30rem;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 10px 30px -5px rgba(79,70,229,.12);
        }
        .logo {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2rem;
            letter-spacing: -0.01em;
        }
        .logo span { color: #4F46E5; }
        .icon {
            width: 4.5rem;
            height: 4.5rem;
            color: #d1d5db;
            margin: 0 auto 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @media (prefers-reduced-motion: reduce) {
            .icon { animation: none; }
        }
        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.625rem;
        }
        p {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.65;
            max-width: 22rem;
            margin: 0 auto 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            background: #4F46E5;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.375rem;
            border-radius: 0.6rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s, transform 0.1s;
        }
        .btn:hover { background: #4338ca; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Alma<span>Connect</span></div>
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke-width="1.3" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12" />
        </svg>
        <h1>You're offline</h1>
        <p>AlmaConnect needs an internet connection. Reconnect and we'll be right back.</p>
        <button class="btn" onclick="window.location.reload()">Try again</button>
    </div>
</body>
</html>
