{{ Form::text('name', trans('emailtemplate::attributes.name'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true]) }}

<?php
    $selectCaseCategory = [];
    $case_category_id = $emailTemplate->case_category_id;
    if(!empty($case_category_id)){
        $selectCaseCategory = explode(",",$case_category_id);
    }
 ?>

<div class="form-group">
    <label for="case_category_id" class="col-md-2 control-label text-left" >
        {{ trans('emailtemplate::attributes.category') }}
    </label>
    <div class="col-md-10">
        <select class="form-control selectize prevent-creation" id="case_category_id" name="case_category_id[]" multiple>
                @foreach(caseCategory() as $key => $casecate)
                    <option @if(in_array($key,$selectCaseCategory)) selected @endif value="{{ $key }}">{{ $casecate }}</option>
                @endforeach
        </select>
    </div>
</div>


 <!-- {{ Form::select('case_category_id',trans('emailtemplate::attributes.category'), $errors, caseCategory(), $selectCaseCategory, ['required' => true, 'labelCol' => 2,'class' => 'selectize prevent-creation', 'multiple' => true]) }} -->


{{ Form::wysiwyg('description',trans('emailtemplate::attributes.description'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true, 'class' => "required"]) }}

{{ Form::text('project', trans('emailtemplate::attributes.project'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true]) }}

{{ Form::text('client', trans('emailtemplate::attributes.client'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true]) }}

{{ Form::text('location', trans('emailtemplate::attributes.location'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true]) }}

{{ Form::text('sort_order', trans('emailtemplate::attributes.sort_order'), $errors, $emailTemplate, ['labelCol' => 2, 'required' => true]) }}

<div class="form-group">
    <div class="col-md-2">
        <label for="complete_date">
            {{ trans('emailtemplate::attributes.complete_date') }}
        </label>
    </div>
    <div class="col-md-10">
        <input type="date" name="complete_date" value="{{ $emailTemplate->complete_date }}" id="complete_date" class="form-control">

        @error('complete_date')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('is_active', trans('emailtemplate::attributes.is_active'), trans('emailtemplate::emailtemplates.form.enable_the_emailtemplate'), $errors, $emailTemplate) }}

        {{ Form::checkbox('display_on_home', trans('emailtemplate::attributes.display_on_home'), trans('emailtemplate::emailtemplates.form.enable_the_Home'), $errors, $emailTemplate) }}
    </div>
</div>