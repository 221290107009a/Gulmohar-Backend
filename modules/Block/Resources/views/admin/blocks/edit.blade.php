@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('block::blocks.block')]))
    @slot('subtitle', $block->title)

    <li><a href="{{ route('admin.blocks.index') }}">{{ trans('block::blocks.blocks') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('block::blocks.block')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.blocks.update', $block) }}" class="form-horizontal" id="block-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

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