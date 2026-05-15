<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticating...</title>
</head>
<body>
<script>
    @if(isset($token))
    (function () {
        var payload = {
            type: 'wynntils_oauth_callback',
            success: true,
            token: {!! json_encode($token, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) !!},
            accountLinked: true
        };
        if (window.opener) {
            window.opener.postMessage(payload, '*');
            window.close();
        } else {
            window.location.href = '/crash';
        }
    })();
    @else
    (function () {
        var message = {!! json_encode($message ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        var payload = {
            type: 'wynntils_oauth_callback',
            success: false,
            message: message
        };
        if (window.opener) {
            window.opener.postMessage(payload, '*');
            window.close();
        } else {
            window.location.href = '/auth/login?error=' + encodeURIComponent(message);
        }
    })();
    @endif
</script>
</body>
</html>
