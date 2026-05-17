<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2>Thank you for your donation</h2>
    <p>
        Hi {{ $donation->is_anonymous ? 'there' : $donation->user->name }},
    </p>
    <p>
        We've received your contribution of
        <strong>&#8377;{{ number_format((int) $donation->amount) }}</strong>
        @if ($donation->campaign) to <strong>{{ $donation->campaign->title }}</strong>@endif.
        Your receipt is attached to this email.
    </p>
    <p>With gratitude,<br>The AlmaConnect Alumni Cell</p>
</body>
</html>
