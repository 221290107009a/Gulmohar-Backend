<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $productData['title'] }} - Sangho App</title>
    
    <!-- Open Graph Meta Tags for Social Preview -->
    <meta property="og:title" content="{{ $productData['title'] }} - Sangho App">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($productData['description'] ?? ''), 150) ?: 'View this product on Sangho App.' }}">
    @if(!empty($productData['coverImage']))
        <meta property="og:image" content="{{ $productData['coverImage'] }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $productData['title'] }} - Sangho App">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($productData['description'] ?? ''), 150) }}">
    @if(!empty($productData['coverImage']))
        <meta name="twitter:image" content="{{ $productData['coverImage'] }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #10A3F6;
            --primary-hover: #0D8DD6;
            --primary-gradient: linear-gradient(135deg, #10A3F6 0%, #0672CB 100%);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f1f5f9;
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
            color: #0284c7;
            background-color: #e0f2fe;
        }

        .content-preview img.preview-image {
            width: 100%;
            height: 240px;
            object-fit: contain;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
            padding: 15px;
        }

        .preview-text {
            padding: 18px;
        }

        .preview-text h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
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
            <div class="badge-tag">Product</div>
            
            @if(!empty($productData['coverImage']))
                <img src="{{ $productData['coverImage'] }}" alt="{{ $productData['title'] }}" class="preview-image">
            @endif
            
            <div class="preview-text">
                <h3>{{ $productData['title'] }}</h3>
                @if(!empty($productData['description']))
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($productData['description']), 150) }}</p>
                @endif
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
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>
                Continue on Web
            </a>
        </div>
    </div>
    
    <script>
        var deepLinkUrl = "{{ $deepLinkUrl }}";
        var intentUrl = "{!! $intentUrl !!}";
        
        var urlParams = new URLSearchParams(window.location.search);
        var isFallback = urlParams.get('fallback') === '1';

        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        var isAndroid = /android/i.test(userAgent.toLowerCase());
        var isIOS = /ipad|iphone|ipod/i.test(userAgent.toLowerCase());
        var isMobile = isAndroid || isIOS;
        
        if (isMobile && !isFallback) {
            if (isAndroid) {
                window.location.replace(intentUrl);
            } else {
                window.location.href = deepLinkUrl;
            }

            var appTimeout = setTimeout(function() {
                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('action-state').style.display = 'block';
            }, 2500);

            document.addEventListener("visibilitychange", function() {
                if (document.hidden) {
                    clearTimeout(appTimeout);
                }
            });
        } else {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('action-state').style.display = 'block';
        }
    </script>
</body>
</html>
