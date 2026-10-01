@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    <meta name="title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta name="description" content="{{ $page->meta->meta_description ?? setting('home_page_meta_description') }}">
    <meta name="keywords" content="{{ $page->meta->meta_keywords ?? setting('home_page_meta_keywords') }}">

    
    <meta name="twitter:card" content="summary">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta property="og:description" content="{{ $page->meta->meta_description ?? setting('home_page_meta_description') }}">
    <meta property="og:image" content="{{ $logo }}">
    <meta property="og:locale" content="{{ locale() }}">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="sangho.app">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta name="twitter:description" content="{{ $page->meta->meta_description ?? setting('home_page_meta_description') }}">
    <meta name="twitter:image" content="{{ $logo }}">


    @foreach (supported_locale_keys() as $code)
        <meta property="og:locale:alternate" content="{{ $code }}">
    @endforeach
@endpush

@section('content')
    <section class="custom-page-wrap clearfix">
        <div class="container">
            <div class="custom-page-content clearfix">
                {!! $page->body !!}
            </div>
        </div>
    </section>
@endsection
