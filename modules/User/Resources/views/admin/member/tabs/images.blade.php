@include('media::admin.image_picker.single', [
    'title' => 'Profile Banner',
    'inputName' => 'files[profile_banner]',
    'file' => $user->profile_banner,
])
