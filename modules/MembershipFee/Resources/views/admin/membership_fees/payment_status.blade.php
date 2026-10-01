<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
</head>
<body>
    <script>
        // Try to open app first
        window.location.href = "sangho://contexts/payment-status";
        
        // Fallback to Play Store/App Store if app is not installed
        setTimeout(function() {
            // Android
            if (/android/i.test(navigator.userAgent)) {
                window.location.href = 'https://play.google.com/store/apps/details?id=sangho.app';
            }
        }, 1000);
    </script>
    <p>Redirecting to app...</p>
</body>
</html>