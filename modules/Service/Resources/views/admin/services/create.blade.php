@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('service::services.service')]))

    <li><a href="{{ route('admin.services.index') }}">{{ trans('service::services.services') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('service::services.service')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.services.store') }}" class="form-horizontal" id="flash-sale-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('service')) !!}
    </form>
@endsection

@include('service::admin.services.partials.shortcuts')

@push('globals')
    @vite([
        'modules/Brand/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
