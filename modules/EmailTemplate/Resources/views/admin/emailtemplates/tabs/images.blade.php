@include('media::admin.image_picker.single', [
    'title' => trans('emailtemplate::emailtemplates.form.logo'),
    'inputName' => 'files[logo]',
    'file' => $emailTemplate->logo,
])
