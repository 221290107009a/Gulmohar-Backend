<!DOCTYPE html>
<html lang="en">
<head>
@php
    $shareSource = $shareSource ?? 'app-share';
    
    if ($shareSource === 'quote-share') {
        $displayTitle = $title ?? '✨ Shared Shrestha Vichar Templates';
        $displayDesc = $description ?? 'Open the Sangho App to view Daily Shrestha Vichar Templates. Add your photo, and share inspiration with your friends and family.';
        $metaTitle = 'Sangho App - ' . $displayTitle;
        $badgeText = 'Quote Shared';
        $badgeColor = '#8b5cf6';
        $badgeBg = '#ede9fe';
    } else {
        $displayTitle = $title ?? '✨ You\'re Invited To Join Sangho App!';
        $displayDesc = $description ?? 'Join India\'s growing community app! Explore trending posts, connect with like-minded people, and create beautiful personalized content to share.';
        $metaTitle = 'Sangho App - ' . $displayTitle;
        $badgeText = 'App Invite';
        $badgeColor = '#3b82f6';
        $badgeBg = '#eff6ff';
    }
@endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    
    <!-- Open Graph Meta Tags for Rich Preview -->
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $displayDesc }}">
    <meta property="og:image" content="{{ $image ?? (isset($logo) && $logo ? $logo->path : '') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #10A3F6;
            --primary-hover: #0D8DD6;
            --primary-gradient: linear-gradient(135deg, #10A3F6 0%, #0672CB 100%);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --border-color: #f1f5f9;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            text-align: center;
            background-color: var(--bg-body);
            background-image: radial-gradient(at 0% 0%, rgba(16, 163, 246, 0.08) 0, transparent 50%), radial-gradient(at 100% 100%, rgba(16, 163, 246, 0.08) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 32px;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.6);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .header img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: contain;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .content-preview {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 30px;
            text-align: left;
            box-shadow: 0 8px 24px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .badge-tag {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            backdrop-filter: blur(4px);
        }

        .content-preview:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        .content-preview img.preview-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
        }

        .content-preview img.logo-fallback {
            object-fit: contain;
            height: 160px;
            padding: 28px;
            background-color: #ffffff;
        }

        .preview-text {
            padding: 18px;
        }

        .preview-text h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .preview-text p {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
        }

        .loader {
            border: 3px solid #f3f4f6;
            border-radius: 50%;
            border-top: 3px solid var(--primary-color);
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }

        .loader-text {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-muted);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 16px 24px;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            margin-bottom: 16px;
            box-shadow: 0 6px 16px rgba(16, 163, 246, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 24px rgba(16, 163, 246, 0.4);
        }

        .btn:active {
            transform: translateY(1px) scale(0.98);
        }

        .playstore-btn {
            background: #000000;
            color: #ffffff;
            justify-content: center;
            gap: 12px;
            padding: 10px 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            border-radius: 14px;
        }

        .playstore-btn:hover {
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
            background: #111111;
            transform: translateY(-2px) scale(1.02);
        }

        .play-store-icon {
            width: 32px;
            height: 32px;
        }

        .playstore-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            text-align: left;
        }

        .playstore-text .small {
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
            margin-bottom: 2px;
            color: #a3a3a3;
        }

        .playstore-text .large {
            font-size: 19px;
            font-weight: 600;
            line-height: 1;
            letter-spacing: -0.2px;
        }

        .btn-outline {
            background: transparent;
            color: var(--text-main);
            border: 2px solid #e2e8f0;
            box-shadow: none;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(0,0,0,0.04);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #9ca3af;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }

        .divider:not(:empty)::before {
            margin-right: 15px;
        }

        .divider:not(:empty)::after {
            margin-left: 15px;
        }

        #action-state {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logo) && $logo && $logo->path)
                <img src="{{ $logo->path }}" alt="Sangho Logo">
            @endif
            <h1>Sangho</h1>
        </div>
        
        <div class="content-preview">
            <div class="badge-tag" style="color: {{ $badgeColor }}; background-color: {{ $badgeBg }};">{{ $badgeText }}</div>
            @if(isset($image) && $image)
                <img src="{{ $image }}" alt="Preview" class="preview-image">
            @elseif(isset($logo) && $logo && $logo->path)
                <img src="{{ $logo->path }}" alt="Sangho App Logo" class="preview-image logo-fallback">
            @else
                <div class="preview-image logo-fallback" style="display:flex;align-items:center;justify-content:center;background:#f3f4f6;">
                    <span style="color:#9ca3af;font-size:14px;font-weight:500;">Sangho App</span>
                </div>
            @endif
            
            <div class="preview-text">
                <h3>{{ $displayTitle }}</h3>
                <p>{{ $displayDesc }}</p>
            </div>
        </div>

        <div id="loading-state" class="loader-container">
            <div class="loader"></div>
            <div class="loader-text">Opening in App...</div>
        </div>

        <div id="action-state">
            <a href="{{ $playStoreUrl }}" class="btn playstore-btn">
                <img src="{{ asset('build/assets/google_play.svg') }}" alt="Google Play" class="play-store-icon">
                <div class="playstore-text">
                    <span class="small">Get it on</span>
                    <span class="large">Google Play</span>
                </div>
            </a>
            
            <div class="divider">Or</div>
            
            <a href="{{ $webRedirectUrl }}" class="btn btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                Continue on Web
            </a>
        </div>
    </div>
    
    <script>
        // The deep link URL for the mobile app
        var deepLinkUrl = "{{ $deepLinkUrl }}";
        
        // Mobile platform detection
        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        var isMobile = /android|ipad|iphone|ipod/i.test(userAgent.toLowerCase());
        
        if (isMobile) {
            // Attempt to open the app via deep link
            window.location.href = deepLinkUrl;

            // If the app is not installed, the page will remain active.
            // After 2.5 seconds, we assume the app didn't open and show alternative options.
            var appTimeout = setTimeout(function() {
                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('action-state').style.display = 'block';
            }, 2500);

            // Clear the timeout if the app opens successfully and the browser goes to the background
            document.addEventListener("visibilitychange", function() {
                if (document.hidden) {
                    clearTimeout(appTimeout);
                }
            });
        } else {
            // If on desktop, show actions immediately (skip loader since deep link won't work)
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('action-state').style.display = 'block';
        }
    </script>
</body>
</html>
