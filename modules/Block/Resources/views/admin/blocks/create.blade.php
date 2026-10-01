@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('block::blocks.block')]))

    <li><a href="{{ route('admin.blocks.index') }}">{{ trans('block::blocks.blocks') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('block::blocks.block')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.blocks.store') }}" class="form-horizontal" id="block-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('block')) !!}
    </form>
@endsection

@include('block::admin.blocks.partials.shortcuts')

@push('globals')
    @vite([
        'modules/Page/Resources/assets/admin/sass/main.scss',
        'modules/Page/Resources/assets/admin/js/main.js',
    ])
@endpush