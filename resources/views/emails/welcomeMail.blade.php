<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to Our Platform</title>
</head>
<body>
    <div class="container">
    <h1>Welcome, {{ $mailData['username'] }}!</h1>
    <p>Your account has been created successfully.</p>
    <p><strong>Email:</strong> {{ $mailData['email'] }}</p>
    <p><strong>Password:</strong> {{ $mailData['password'] }}</p>
    <p>You can login at: <a href="{{ $mailData['site_url'] }}">{{ $mailData['site_url'] }}</a></p>      
    </div>
</body>
</html>