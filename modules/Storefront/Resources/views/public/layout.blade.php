<!DOCTYPE html>
<html lang="{{ locale() }}">

<head>
    <base href="{{ config('app.url') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta name="robots" content="index, follow">

    <title>
        @hasSection('title')
        @yield('title') - {{ setting('store_name') }}
        @else
        {{ setting('store_name') }}
        @endif
    </title>

    @stack('meta')
    @PWA

    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="canonical" href="https://sangho.app/en">

    <script src="{{ v(asset('build/assets/jquery.min.js')) }}"></script>
    <script src="{{ v(asset('build/assets/bootstrap.min.js')) }}"></script>
    <script src="{{ v(asset('build/assets/slick.min.js')) }}"></script>

    @vite([
    'modules/Storefront/Resources/assets/public/sass/main.scss',
    'modules/Storefront/Resources/assets/public/js/main.js',
    ])

    {{-- Google Web Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500&family=Jost:wght@500;600;700&display=swap"
        rel="stylesheet">

    {{-- Icon Font Stylesheet --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Customized Bootstrap Stylesheet --}}
    <link href="{{ v(asset('build/assets/app-bootstrap.min.css')) }}" rel="stylesheet">

    {{-- Template Stylesheet --}}
    {{-- <link href="{{ v(asset('build/assets/app-style.css')) }}" rel="stylesheet"> --}}
    <link href="{{ v(asset('build/assets/custom.css')) }}" rel="stylesheet">
    @stack('styles')

    {!! setting('custom_header_assets') !!}

    <script>
    window.FleetCart = {
        baseUrl: '{{ config('
        app.url ') }}',
        rtl: {
            {
                is_rtl() ? 'true' : 'false'
            }
        },
        storeName: '{{ setting('
        store_name ') }}',
        storeLogo: '{{ $logo }}',
        loggedIn: {
            {
                auth() - > check() ? 'true' : 'false'
            }
        },
        csrfToken: '{{ csrf_token() }}',
        razorpayKeyId: '{{ setting('
        razorpay_key_id ') }}',
        cart: {
            !!$cart!!
        },
        wishlist: {
            !!$wishlist!!
        },
        compareList: {
            !!$compareList!!
        },
        langs: {
            'storefront::layout.next': '{{ trans('
            storefront::layout.next ') }}',
            'storefront::layout.prev': '{{ trans('
            storefront::layout.prev ') }}',
            'storefront::layout.search_for_products': '{{ trans('
            storefront::layout.search_for_products ') }}',
            'storefront::layout.all_categories': '{{ trans('
            storefront::layout.all_categories ') }}',
            'storefront::layout.most_searched': '{{ trans('
            storefront::layout.most_searched ') }}',
            'storefront::layout.category_suggestions': '{{ trans('
            storefront::layout.category_suggestions ') }}',
            'storefront::layout.product_suggestions': '{{ trans('
            storefront::layout.product_suggestions ') }}',
            'storefront::layout.more_results': '{{ trans('
            storefront::layout.more_results ') }}',
            'storefront::product_card.out_of_stock': '{{ trans('
            storefront::product_card.out_of_stock ') }}',
            'storefront::product_card.new': '{{ trans('
            storefront::product_card.new ') }}',
            'storefront::product_card.add_to_cart': '{{ trans('
            storefront::product_card.add_to_cart ') }}',
            'storefront::product_card.view_options': '{{ trans('
            storefront::product_card.view_options ') }}',
            'storefront::product_card.compare': '{{ trans('
            storefront::product_card.compare ') }}',
            'storefront::product_card.wishlist': '{{ trans('
            storefront::product_card.wishlist ') }}',
            'storefront::product_card.available': '{{ trans('
            storefront::product_card.available ') }}',
            'storefront::product_card.sold': '{{ trans('
            storefront::product_card.sold ') }}',
            'storefront::product_card.days': '{{ trans('
            storefront::product_card.days ') }}',
            'storefront::product_card.hours': '{{ trans('
            storefront::product_card.hours ') }}',
            'storefront::product_card.minutes': '{{ trans('
            storefront::product_card.minutes ') }}',
            'storefront::product_card.seconds': '{{ trans('
            storefront::product_card.seconds ') }}',
            'storefront::blog.blog_posts.view_all': '{{ trans('
            storefront::blog.blog_posts.view_all ') }}',
            'storefront::blog.blog_posts.read_more': '{{ trans('
            storefront::blog.blog_posts.read_more ') }}',
        },
    };
    </script>

    {!! $schemaMarkup->toScript() !!}

    @stack('globals')

    @routes
</head>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-X4DBWQR316"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-X4DBWQR316');
</script>
<body dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="page-template {{ is_rtl() ? 'rtl' : 'ltr' }}"
    data-theme-color="{{ $themeColor->toHexString() }}" style="
        --color-primary: {{ tinycolor($themeColor->toString())->toHexString() }};
        --color-primary-hover: {{ tinycolor($themeColor->toString())->darken(8)->toString() }};
        --color-primary-transparent: {{ tinycolor($themeColor->toString())->setAlpha(0.8)->toString() }};
        --color-primary-transparent-lite: {{ tinycolor($themeColor->toString())->setAlpha(0.3)->toString() }};
        --color-primary-transparent-lite-2: {{ tinycolor($themeColor->toString())->setAlpha(0.12)->toString() }};
    ">
    <div class="" id="app">
        @if (! request()->routeIs('home'))
            @include('storefront::public.layout.header')
        @endif

        @yield('content')

        @if (! request()->routeIs('home'))
            @include('storefront::public.layout.footer')
        @endif

        <div class="overlay"></div>

        {{-- @include('storefront::public.layout.sidebar_menu')
        @include('storefront::public.layout.localization') --}}

        {{-- @if (!request()->routeIs('checkout.create'))
            @include('storefront::public.layout.sidebar_cart')
        @endif --}}

       {{-- @include('storefront::public.layout.alert')
        @include('storefront::public.layout.newsletter_popup') --}}
        @include('storefront::public.layout.cookie_bar')
    </div>

    @stack('pre-scripts')
    {{-- JavaScript Libraries --}}
    <script src="{{ v(asset('build/assets/lib/wow/wow.min.js')) }}"></script>
    <script src="{{ v(asset('build/assets/lib/waypoints/waypoints.min.js')) }}"></script>
    <script src="{{ v(asset('build/assets/lib/counterup/counterup.min.js')) }}"></script>
    {{-- Template Javascript --}}
    <script src="{{ v(asset('build/assets/app-main.js')) }}"></script>
    @stack('scripts')

    {!! setting('custom_footer_assets') !!}
    <script>
        function toggleMenuDropdown() {
            var dropdown = document.getElementById("menuDropdown");
            if (dropdown) {
                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.menu-icon')) {
                var dropdowns = document.getElementsByClassName("menu-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }
    </script>
</body>

</html>