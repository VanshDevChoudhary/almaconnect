<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') — AlmaConnect</title>
    <meta name="theme-color" content="#8b1627">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fef2f3 0%, #fde3e6 50%, #fde3e6 100%);
            padding: 1.5rem;
        }
        .card {
            text-align: center;
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            max-width: 30rem;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 10px 30px -5px rgba(139,22,39,.12);
            animation: fadeUp 0.4s ease-out both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .card { animation: none; }
        }
        .logo {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2rem;
            letter-spacing: -0.01em;
        }
        .logo span { color: #8b1627; }
        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: #fef2f3;
            font-size: 1.75rem;
            font-weight: 800;
            color: #8b1627;
            margin: 0 auto 1.5rem;
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
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            background: #8b1627;
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
        .btn:hover { background: #761524; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-ghost {
            background: transparent;
            color: #374151;
            border: 1.5px solid #d1d5db;
        }
        .btn-ghost:hover { background: #f9fafb; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Alma<span>Connect</span></div>
        @yield('content')
    </div>
</body>
</html>
