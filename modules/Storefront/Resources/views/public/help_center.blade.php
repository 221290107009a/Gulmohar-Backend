@extends('storefront::public.layout')

@section('title', 'Help Center - Nested Design')

@push('styles')
<link rel="stylesheet" href="{{ asset('build/assets/help_center.css') }}">
<style>
    .category-icon {
        display: inline-block !important;
    }
</style>
@endpush

@section('content')
<header class="help-center-header">
    <div class="hc-header-container">
        <div class="hc-header-left">
            <button class="menu-toggle-btn" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('home') }}" class="hc-logo">
                <img src="{{ asset('build/assets/logo.png') }}" alt="Sangho Logo">
                <span>Help Center</span>
            </a>
        </div>
        <div class="hc-header-right">
            <button class="mobile-search-btn" id="mobile-search-toggle">
                <i class="fas fa-search"></i>
            </button>
            <div class="hc-search-wrapper" id="hc-search-wrapper">
                <i class="fas fa-search hc-search-icon"></i>
                <input type="text" class="hc-search-input" id="hc-search-input" placeholder="Search..." autocomplete="off">
                <i class="fas fa-times hc-search-clear" id="hc-search-clear"></i>
                <div class="hc-search-suggestions" id="hc-search-suggestions"></div>
            </div>
            <div class="hc-lang-selector">
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 ms-2">EN <i class="fas fa-chevron-down ms-1"></i></button>
            </div>
        </div>
    </div>
</header>

<main class="hc-main">
    <aside class="hc-sidebar">
        <ul class="sidebar-menu">
            @foreach ($helpTree as $l1)
                <li class="menu-item-l1">
                    <div class="menu-trigger-l1">
                        <span><i class="category-icon {{ $l1->icon }}">&nbsp;</i> {{ $l1->name }}</span>
                        @if ($l1->items->isNotEmpty() || ($l1->pages && count($l1->pages) > 0))
                            <i class="fas fa-chevron-down"></i>
                        @endif
                    </div>
                    @if ($l1->items->isNotEmpty() || ($l1->pages && count($l1->pages) > 0))
                        <div class="menu-content-l1">
                            {{-- Native nested subcategories and direct articles --}}
                            @foreach ($l1->items as $l2)
                                @if ($l2->type === 'subcategory')
                                    <div class="menu-item-l2">
                                        <div class="menu-trigger-l2">
                                            <span><i class="category-icon {{ $l2->icon }}">&nbsp;</i> {{ $l2->name }}</span>
                                            @if ($l2->items->isNotEmpty())
                                                <i class="fas fa-chevron-down"></i>
                                            @endif
                                        </div>
                                        @if ($l2->items->isNotEmpty())
                                            <div class="menu-content-l2">
                                                @foreach ($l2->items as $article)
                                                    <a href="#" class="article-link" data-slug="{{ $article->slug }}">{{ $article->name }}</a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Direct Article --}}
                                    <div class="menu-item-l2 direct-article">
                                        <div class="menu-content-l2" style="display: block;">
                                            <a href="#" class="article-link" data-slug="{{ $l2->slug }}">{{ $l2->name }}</a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- JSON pages (Add More articles) --}}
                            @if ($l1->pages)
                                @php 
                                    $groupedPages = [];
                                    foreach($l1->pages as $originalIndex => $page) {
                                        $subtitle = $page['subtitle'] ?? '';
                                        $groupedPages[$subtitle][$originalIndex] = $page;
                                    }
                                @endphp
                                @foreach ($groupedPages as $subtitle => $pages)
                                    <div class="menu-item-l2">
                                        @if($subtitle)
                                        <div class="menu-trigger-l2">
                                            <span><i class="category-icon {{ reset($pages)['icon'] }}">&nbsp;</i> {{ $subtitle }}</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        @endif
                                        <div class="menu-content-l2" style="{{ !$subtitle ? 'display: block;' : '' }}">
                                            @foreach ($pages as $originalIndex => $page)
                                                <a href="#" class="article-link" data-slug="{{ $l1->slug }}-{{ $page['slug'] ?? 'p-'.$originalIndex }}">{{ $page['name'] ?? 'Untitled' }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="hc-content-area">
        @php
            $firstArticle = null;
            $firstArticleBody = '';
            
            foreach ($helpTree as $l1) {
                // Check native nested items first (subcategories or direct articles)
                foreach ($l1->items as $l2) {
                    if ($l2->type === 'subcategory' && $l2->items->isNotEmpty()) {
                        $firstArticle = $l2->items->first();
                        $firstArticleBody = $firstArticle->body;
                        break 2;
                    } elseif ($l2->type === 'article') {
                        $firstArticle = $l2;
                        $firstArticleBody = $l2->body;
                        break 2;
                    }
                }
                
                // Then check JSON pages
                if ($l1->pages && count($l1->pages) > 0) {
                    $firstArticle = (object) $l1->pages[0];
                    $firstArticleBody = $l1->pages[0]['body'];
                    break;
                }
            }
        @endphp

        <div class="article-card">
            <div class="article-title-row">
                <h1 id="article-title">{{ $firstArticle->name ?? 'Help Center' }}</h1>
                <button class="btn-copy"><i class="fas fa-link"></i> Copy link</button>
            </div>
            <hr>
            
            <div id="article-body" class="content-placeholder">
                @if ($firstArticleBody)
                    {!! $firstArticleBody !!}
                @else
                    <p>Please select an article from the sidebar.</p>
                @endif
            </div>
        </div>


    </div>
</main>

@endsection

@push('scripts')
<script>
    // Pre-loaded articles for instant access
    window.hcArticles = @json($articles);
</script>
<script src="{{ v(asset('build/assets/help_center.js')) }}"></script>
@endpush
