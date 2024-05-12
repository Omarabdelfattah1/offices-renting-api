<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <title>Login Widget Example</title>
    </head>
    <body>
        <center>
            <script async
            src="https://telegram.org/js/telegram-widget.js?2"
            data-telegram-login="{{$bot_username}}"
            data-size="large"
            data-auth-url="{{$redirect}}"></script>

        </center>
    </body>
</html>
