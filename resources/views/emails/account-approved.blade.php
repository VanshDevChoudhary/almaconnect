<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account approved — AlmaConnect</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px; }
        .card { background: #ffffff; border-radius: 12px; padding: 40px 36px; border: 1px solid #e5e7eb; }
        .logo { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 28px; }
        .logo span { color: #4F46E5; }
        .badge { display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 999px; margin-bottom: 20px; }
        h1 { font-size: 22px; font-weight: 700; color: #111827; margin: 0 0 12px; }
        p { font-size: 15px; color: #4b5563; line-height: 1.7; margin: 0 0 16px; }
        .btn { display: inline-block; background: #4F46E5; color: #ffffff; font-size: 15px; font-weight: 600; padding: 12px 28px; border-radius: 8px; text-decoration: none; margin: 8px 0 24px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .footer { font-size: 13px; color: #9ca3af; text-align: center; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="logo">Alma<span>Connect</span></div>

            <div class="badge">
                ✓ &nbsp;Account approved
            </div>

            <h1>Welcome to the alumni network, {{ $user->name }}!</h1>

            <p>
                Great news — your AlmaConnect account has been reviewed and approved by our team.
                You now have full access to the alumni directory, groups, events, job board, and more.
            </p>

            <a href="{{ config('app.url') }}/dashboard" class="btn">
                Go to your dashboard →
            </a>

            <hr class="divider">

            <p style="margin:0; font-size:14px; color:#6b7280;">
                If you have any questions, reply to this email or reach us at
                <a href="mailto:{{ config('almaconnect.contact_email') }}" style="color:#4F46E5;">{{ config('almaconnect.contact_email') }}</a>.
            </p>
        </div>

        <div class="footer">
            AlmaConnect · You're receiving this because you registered an account.
        </div>
    </div>
</body>
</html>
