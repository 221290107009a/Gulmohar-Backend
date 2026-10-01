@extends('storefront::public.layout')
@push('meta')
    <meta name="title" content="{{ setting('home_page_meta_title') }}">
    <meta name="description" content="{{ setting('home_page_meta_description') }}">
    <meta name="keywords" content="{{ setting('home_page_meta_keywords') }}">
    
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ setting('home_page_meta_title') }}">
    <meta name="og:description" content="{{ setting('home_page_meta_description') }}">
    <meta property="og:locale" content="{{ locale() }}">
    <meta property="og:image" content="{{ $logo }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="safirhotels.com">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ setting('home_page_meta_title') }}">
    <meta name="twitter:description" content="{{ setting('home_page_meta_description') }}">
    <meta name="twitter:image" content="{{ $logo }}">


    @if(setting('home_page_meta_header_tag'))
        {!! setting('home_page_meta_header_tag') !!}
    @endif
@endpush
@section('content')
<div class="hero-section">
    @include('storefront::public.layout.header')
    <div class="container">
        @if (setting('storefront_hero_content_enabled') == 1)
        {!! the_content(setting('storefront_hero_content')) !!}
        @endif
    </div>
</div>
@if (setting('storefront_sangho_support_enabled') == 1)
    {!! the_content(setting('storefront_sangho_support_content')) !!}
@endif
<div class="mobbin-scroll-container">
    <section class="mobbin-showcase-section">
        <div class="sticky-container">
            @if (setting('storefront_mobbin_showcase_enabled') == 1)
                {!! the_content(setting('storefront_mobbin_showcase_content')) !!}
            @endif
            <div class="mobbin-icons-container">
                @php
                    $speeds = [0.1, -0.15, 0.2, -0.25, 0.12, -0.18, 0.22, -0.08, 0.15, -0.2, 0.1, -0.12, 0.18, -0.22, 0.08, -0.15];
                @endphp
                @foreach($services->take(16) as $index => $service)
                    <div class="floating-icon icon-{{ $index + 1 }} initial-center" data-speed="{{ $speeds[$index] ?? 0.1 }}">
                        <div class="icon-inner">
                            <img src="{{ $service->logo->path }}" alt="{{ $service->name }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<section class="bahujan-support-section">
   <svg class="waves waves-top" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">            
        <defs>
            <path id="gentle-wave-smart" d="M-160 44q2-2 4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0v44h-352z" />
        </defs>
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
    @if (setting('storefront_bahujan_knowledge_enabled') == 1)
    {!! the_content(setting('storefront_bahujan_knowledge_content')) !!}
    @endif
    <svg class="waves waves-bottom" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
</section>
<section class="showcase">
    @if($showcase['enabled'] == 1)
        <div class="container">
            <div class="showcase-title">
                <h2 class="head-sub-title">{{ $showcase['title'] }}</h2>
            </div>
        </div>
        <div class="slider-track" id="sliderTrack">
            @foreach($showcase['items'] as $item)
                <div class="screen-item">
                    <h2>{{ $item['title'] }}</h2>
                    <div class="phone"><img src="{{ $item['image']->path }}" alt="{{ $item['title'] }}"></div>
                </div>
            @endforeach
        </div>
    @endif
</section>
<section class="smart-tools-section">
    <svg class="waves waves-top" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">            
        <defs>
            <path id="gentle-wave-smart" d="M-160 44q2-2 4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0v44h-352z" />
        </defs>
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="head-sub-title">{{ setting('storefront_intelligent_digital_solutions_section_title') }}</h2>
            <p class="main-subtitle">{{ setting('storefront_intelligent_digital_solutions_section_subtitle') }}</p>
            <p class="main-desc">{{ setting('storefront_intelligent_digital_solutions_section_description') }}</p>
        </div>

        <div class="row justify-content-center">            
            @foreach($solutions as $index => $solution)
                <div class="col-lg-4 col-md-6">
                    <div class="custom-tool-card {{ $index == 1 ? 'center-card' : 'side-card' }}">
                        <div class="phone-stand-slot">
                            <div class="solution-slider">
                                @foreach($solution->images as $image)
                                    <img src="{{ $image->path }}" class="phone-img-fallback" alt="{{ $solution->title }}">
                                @endforeach
                            </div>
                        </div>
                        <div class="card-info">
                            <h3>{{ $solution->title }}</h3>
                            <p>{{ $solution->desc }}</p>
                        </div>
                    </div>
                    @if($index == 1)
                        <div class="center-card-btn">
                            <a href="#app-features" class="btn-explore-blue">Explore App Features</a>
                        </div>
                    @elseif($index == 2)
                        <div class="right-card-btn">
                            <a href="#app-features" class="btn-explore-blue">Explore App Features</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
<section class="faq-section" id="app-features">
    <div class="container">
    <h2 class="head-sub-title">Explore App Features</h2>
        <div class="faq-grid">
            @foreach($services as $service)
                <div class="faq-card">
                    <div class="faq-header">
                        <div class="faq-icon">
                            <div class="image">
                                <img src="{{ $service->logo->path }}" alt="{{ $service->title }}">
                            </div>
                        </div>
                        <h3>{!! $service->name !!}</h3>
                        <div class="faq-btn">+</div>
                    </div>
                    <div class="faq-desc">
                        {!! $service->description !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bahujan-support-section">
   <svg class="waves waves-top" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">            
        <defs>
            <path id="gentle-wave-smart" d="M-160 44q2-2 4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0v44h-352z" />
        </defs>
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
    @if (setting('storefront_community_engagement_enabled') == 1)
    {!! the_content(setting('storefront_community_engagement_content')) !!}
    @endif
    <svg class="waves waves-bottom" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
</section>
<section class="reviews-section">
    <div class="container">
        <h2 class="head-sub-title">What our users are saying.</h2>
        <div class="reviews-grid">
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">HARSHWARDHAN GAURYA</span>
                            <div class="review-stars"> ★★★★★ </div>     
                        </div>
                    </div>
                <p class="review-text">An app that paves the way for us to follow the ideals of great people.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review1.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Makwana Harjee</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">We Have Benefited Greatly From Your Efforts. We Have Learned a Lot About The Life Events of Our Great Warriors. Thank You.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review7.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">RAJKAMAL AGRA</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">very good and informative related to budhism.i love it.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review2.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Jaypal Meraiya </span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">BHAHUT HI ACHHE SUVICHAR MILATE HAI. BHARATKE SABHI MAHAN SANTO KE PHOTO'S OR UNKE VICHAR JO HUME JIVAN ME SAHI DISH ME JANE KE LIYE PRERIT KARTA HAI.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review4.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Jaypal Meraiya</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">BHAHUT HI ACHHE SUVICHAR MILATE HAI. BHARATKE SABHI MAHAN SANTO KE PHOTO'S OR UNKE VICHAR JO HUME JIVAN ME SAHI DISH ME JANE KE LIYE PRERIT KARTA HAI.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review5.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Manoj Manoj</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">sangho is the best app for general knowledge of Buddhism.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review6.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Daxaben</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">Very useful app, Thank you for this wonderful creation.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review8.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">NATIONAL AND INTERNATIONAL BUDDHIST SOCIETY</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">this app is better than Kutumb App.</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review9.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Prem Pal</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">Super Application Bhudism and Very Useful apps</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review10.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Nihal</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">very informative app</p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review11.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Vishv pratap Singh</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">बहुत ही शानदार एप बहुत बहुत धन्यवाद </p>
            </div>
            <div class="review-card">
                <div class="user-meta">
                    <img src="{{ asset('build/assets/review12.webp') }}" class="avatar" alt="review">
                    <div class="user-details">
                        <span class="name">Madhumati Chauhan</span>
                        <div class="review-stars"> ★★★★★ </div> 
                    </div>
                </div>
                <p class="review-text">Very good app,meeting all our need</p>
            </div>
        </div>
        <div class="reviews-btn-wrap">
            <a href="https://play.google.com/store/apps/details?id=sangho.app&hl=en&showAllReviews=true" class="reviews-btn">
                <img src="{{ asset('build/assets/google_play.svg') }}" alt="Google" class="google-icon">
                Share Your Experience on Google
            </a>
        </div>
    </div>
</section>
<section class="explore-features-section">
   <svg class="waves waves-top" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">            
        <defs>
            <path id="gentle-wave-smart" d="M-160 44q2-2 4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0t4 0v44h-352z" />
        </defs>
        <use href="#gentle-wave-smart" class="wave-line" x="50" y="0" fill="#fff"/>
    </svg>
    <div class="container">
        <h2 class="head-sub-title">Your Complete Cultural & Community Platform</h2>
        <p class="explore-subtitle">
            Explore culture, literature, spirituality, business, events, and 
            digital tools – all seamlessly united in one app.
        </p>        
    </div>
    <div class="slider">
        <div class="slide-track" id="track1">
            @foreach($services as $service)
                <div class="slide">
                    <img src="{{ $service->logo->path }}" alt="{{ $service->name }}">
                    <span>{{ $service->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slider">
        <div class="slide-track reverse" id="track2">
            @foreach($services->reverse() as $service)
                <div class="slide">
                    <img src="{{ $service->logo->path }}" alt="{{ $service->name }}" >
                    <span>{{ $service->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slider">
        <div class="slide-track" id="track3">
            @foreach($services->shuffle() as $service)
                <div class="slide">
                    <img src="{{ $service->logo->path }}" alt="{{ $service->name }}">
                    <span>{{ $service->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@if (setting('storefront_one_app_community_enabled') == 1)
    {!! the_content(setting('storefront_one_app_community_content')) !!}
@endif

<!-- Video Modal -->
<div id="videoModal" class="video-modal">
    <div class="video-modal-content">
        <span class="close-modal">&times;</span>
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/JNmDLWqNlyM?si=cQm7fK-Pm_UfDmHk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>
</div>

@include('storefront::public.layout.footer')

@endsection
