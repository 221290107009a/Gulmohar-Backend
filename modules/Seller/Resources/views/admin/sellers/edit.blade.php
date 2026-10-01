@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('seller::sellers.seller')]))
    @slot('subtitle', $seller->title)

    <li><a href="{{ route('admin.sellers.index') }}">{{ trans('seller::sellers.sellers') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('seller::sellers.seller')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.sellers.update', $seller) }}" class="form-horizontal" id="seller-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('seller')) !!}
    </form>
@endsection

@include('seller::admin.sellers.partials.shortcuts')

@push('globals')
    @vite([
        'modules/Brand/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush