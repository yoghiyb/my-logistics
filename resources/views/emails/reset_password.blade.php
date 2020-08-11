<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>

<body>
    <h1>Hai {{ $user->name }}</h1>
    <p>Anda telah melakukan permintaan reset password, silahkan konfirmasi memalui <a href="{{ env('URL_APPS') . '/' . $user->reset_token }}">link ini</a></p>
</body>

</html>
