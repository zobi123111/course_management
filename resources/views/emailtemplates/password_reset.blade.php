<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <h1>Hi {{ $user->name }},</h1>
    <p>You requested to reset your password. Click the link below to reset it:</p>
    <a href="{{ $resetLink }}">Reset Password</a>
    <p>If you did not request a password reset, please ignore this email.</p>
</body>
</html>
