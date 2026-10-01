{{ Form::text('name', trans('block::attributes.name'), $errors, $block, ['labelCol' => 2, 'required' => true]) }}
{{ Form::text('identifier', trans('block::attributes.identifier'), $errors, $block, ['labelCol' => 2, 'required' => true]) }}
{{ Form::wysiwyg('content', trans('block::attributes.content'), $errors, $block, ['labelCol' => 2, 'required' => true]) }}

<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('is_active', trans('block::attributes.is_active'), trans('block::blocks.form.enable_the_block'), $errors, $block) }}
    </div>
</div>