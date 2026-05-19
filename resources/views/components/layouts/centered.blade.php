<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AlmaConnect' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes ac-card-in {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes ac-icon-in {
            0%   { opacity: 0; transform: scale(0.8); }
            70%  { opacity: 1; transform: scale(1.06); }
            100% { opacity: 1; transform: scale(1); }
        }
        .ac-card  { animation: ac-card-in 350ms ease-out both; }
        .ac-icon  { animation: ac-icon-in 400ms cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        @media (prefers-reduced-motion: reduce) {
            .ac-card, .ac-icon { animation: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-maroon-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="mb-8 text-2xl font-semibold tracking-tight text-gray-900">
            Alma<span class="text-maroon-600">Connect</span>
        </div>

        <div class="ac-card w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-xl">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
