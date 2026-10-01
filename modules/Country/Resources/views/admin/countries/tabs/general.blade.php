{{ Form::text('name', trans('country::attributes.name'), $errors, $country, ['labelCol' => 2, 'required' => true]) }}

{{ Form::text('code', trans('country::attributes.code'), $errors, $country, ['labelCol' => 2, 'required' => true]) }}
{{ Form::text('sort_order', "Sort Order", $errors, $country, ['labelCol' => 2, 'required' => true]) }}
<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('is_active', trans('country::attributes.is_active'), trans('country::countries.form.enable_the_country'), $errors, $country) }}
    </div>
</div>