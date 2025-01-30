<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Created</title>
</head>

<body>
    <h1>Hello, {{ $firstname }} {{ $lastname }}</h1>

    <p>Your new User has been successfully created at:</p>
    <p><a href="{{ $site_url }}">{{ $site_url }}</a></p>

    <p>You can log in to the account with the following information:</p>
    <p><strong>Username:</strong> {{ $username }}</p>
    <p><strong>Password:</strong> {{ $password }}</p>
    <p>Log in here: <a href="{{ $login_url }}">{{ $login_url }}</a></p>

    <p>We hope you enjoy your new site. Thanks!</p>
</body>

</html>