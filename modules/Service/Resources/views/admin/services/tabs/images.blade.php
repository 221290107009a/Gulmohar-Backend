@include('media::admin.image_picker.single', [
    'title' => trans('service::services.form.icon'),
    'inputName' => 'files[logo]',
    'file' => $service->logo,
])
@error('files.logo')
    <span class="help-block text-red error-message m-b-20">{{ $message }}</span>
@enderror
