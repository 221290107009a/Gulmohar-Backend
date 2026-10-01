@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('seller::sellers.seller')]))

    <li><a href="{{ route('admin.sellers.index') }}">{{ trans('seller::sellers.sellers') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('seller::sellers.seller')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.sellers.store') }}" class="form-horizontal" id="seller-create-form" novalidate>
        {{ csrf_field() }}

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