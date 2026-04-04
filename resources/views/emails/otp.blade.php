<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Public Sans', Arial, sans-serif; background: #f4f5f7; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .otp-code { font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #1572e8; text-align: center; background: #f0f6ff; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .info { color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Employee Login OTP</h2>
        <p>Employee <strong>{{ $employeeName }}</strong> ({{ $employeeEmail }}) is trying to log in.</p>
        <p>Here is the OTP:</p>
        <div class="otp-code">{{ $otp }}</div>
        <p class="info">This OTP is valid for {{ config('constants.otp_validity_minutes') }} minutes.</p>
        <p class="info">If you did not request this, please ignore this email.</p>
    </div>
</body>
</html>
