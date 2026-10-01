@include('media::admin.image_picker.single', [
    'title' => trans('seller::sellers.form.logo'),
    'inputName' => 'files[logo]',
    'file' => $seller->logo,
])

@include('media::admin.image_picker.single', [
    'title' => trans('seller::sellers.form.image'),
    'inputName' => 'files[image]',
    'file' => $seller->image,
])