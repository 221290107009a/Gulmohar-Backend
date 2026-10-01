<?php
$arrUser = $user->toArray();
?>
<div class="row">
    <div class="col-md-8">
        <div class="box-content clearfix">
            <h4 class="section-title">Personal Details</h4>
            
            <div class="form-group">
                <label for="fullname" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.full_name') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <input type="text" name="fullname" class="form-control" id="fullname" value="{{ old('fullname', $arrUser['fullname'] ?? '') }}">
                    @error('fullname')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            {{ Form::email('email', trans('user::attributes.users.email'), $errors, $user, ['required' => true]) }}
            {{ Form::text('phone', trans('user::attributes.users.mobile'), $errors, $user, ['required' => true]) }}
            {{ Form::text('personal_details', trans('user::attributes.users.personal_details'), $errors, $user, ['required' => false]) }}
            {{ Form::text('dob', trans('user::attributes.users.dob'), $errors, $user, ['class' => 'datetime-picker', 'data-default-date' => $user->dob, 'required' => true]) }}
            
            {{ Form::select('education', trans('user::attributes.users.education'), $errors, education(), $user, ['class' => 'selectize prevent-creation']) }}
            {{ Form::text('profession', trans('user::attributes.users.profession'), $errors, $user, ['required' => false]) }}
            <div class="form-group">
                <label class="col-md-3 control-label text-left">{{ trans('user::attributes.users.gender') }}</label>
                <div class="col-md-9">
                    <div class="form-radio">
                        <label>
                            <input type="radio" name="gender" value="male" {{ old('gender', $user->gender) == 'male' ? 'checked' : 'checked' }}>
                            <b>{{ trans('user::attributes.users.male') }}</b>
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" {{ old('gender', $user->gender) == 'female' ? 'checked' : '' }}>
                            <b>{{ trans('user::attributes.users.female') }}</b>
                        </label>
                    </div>
                    <div class="gender_err error"></div>
                </div>
                @error('gender')
                    <span class="help-block text-red">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <hr>
        <div class="box-content clearfix">
            <h4 class="section-title">Address Details</h4>
            
            <div class="row form-group state-main">
                <label for="state" class="control-label text-left col-md-3" @click="focusEditor">
                    {{ trans('user::attributes.users.state') }}<span class="m-l-5 text-red">*</span>
                </label>            
                <div class="col-md-9">
                    <select name="state" id="state" class="form-control custom-select-black">
                        <option value="">Please select</option>
                        @foreach ($states as $keystate => $state)
                            <option value="{{ $keystate }}" {{ old('state', $user->defaultAddress?->address?->state ?? $user->state ) == $keystate ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>

                    @error('state')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="city" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.city') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <select name="city" id="city" class="form-control custom-select-black">
                        <option value="">Please select</option>
                        @if($user->defaultAddress?->address?->city)
                            <option value="{{ $user->defaultAddress?->address?->city }}" selected>{{ $user->defaultAddress?->address?->city }}</option>
                        @endif
                    </select>
                    @error('city')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label text-left">{{ trans('user::attributes.users.area_type') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <div class="form-radio">
                        <label>
                            <input type="radio" name="area_type" value="urban" {{ old('area_type', $user->defaultAddress?->address?->area_type ?? 'urban') == 'urban' ? 'checked' : '' }}>
                            <b>Urban (शहरी)</b>
                        </label>
                        <label>
                            <input type="radio" name="area_type" value="rural" {{ old('area_type', $user->defaultAddress?->address?->area_type ?? '') == 'rural' ? 'checked' : '' }}>
                            <b>Rural (ग्रामीण)</b>
                        </label>
                    </div>
                    @error('area_type')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="taluka" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.taluka') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <select name="taluka" id="taluka" class="form-control custom-select-black">
                        <option value="">Please select</option>
                        @if($user->defaultAddress?->address?->taluka)
                            <option value="{{ $user->defaultAddress?->address?->taluka }}" selected>{{ $user->defaultAddress?->address?->taluka }}</option>
                        @endif
                    </select>
                    @error('taluka')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group" id="village-group" style="display: {{ old('area_type', $user->defaultAddress?->address?->area_type ?? 'urban') == 'rural' ? 'block' : 'none' }};">
                <label for="village" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.village') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <select name="village" id="village" class="form-control custom-select-black">
                        <option value="">Please select</option>
                        @if($user->defaultAddress?->address?->village)
                            <option value="{{ $user->defaultAddress?->address?->village }}" selected>{{ $user->defaultAddress?->address?->village }}</option>
                        @endif
                    </select>
                    @error('village')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address_2" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.address_2') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <input type="text" name="address_2" class="form-control" id="address_2" value="{{ old('address_2', $user->defaultAddress?->address?->address_2 ?? '') }}">
                    @error('address_2')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="address_1" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.address_1') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <input type="text" name="address_1" class="form-control" id="address_1" value="{{ old('address_1', $user->defaultAddress?->address?->address_1 ?? '') }}">
                    @error('address_1')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="zip" class="col-md-3 control-label text-left">{{ trans('user::attributes.users.pincode') }}</label>
                <div class="col-md-9">
                    <input type="text" name="zip" class="form-control" id="zip" value="{{ old('zip', $user->defaultAddress?->address?->zip ?? '') }}">
                    @error('zip')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        <hr>
        <div class="box-content clearfix">
            <h4 class="section-title">Social Links & Role</h4>
            
            {{ Form::text('facebook', trans('user::attributes.users.facebook'), $errors, $user, ['required' => false]) }}
            {{ Form::text('instagram', trans('user::attributes.users.instagram'), $errors, $user, ['required' => false]) }}
            {{ Form::text('twitter', trans('user::attributes.users.twitter'), $errors, $user, ['required' => false]) }}        
            {{ Form::text('linkedin', trans('user::attributes.users.linkedin'), $errors, $user, ['required' => false]) }}

            {{ Form::select('roles', trans('user::attributes.users.roles'), $errors, $roles, $user, ['multiple' => true, 'required' => true, 'class' => 'selectize prevent-creation']) }}

            <div class="form-group">
                <label class="col-md-3 control-label text-left">{{ trans('user::attributes.users.photo') }}<span class="m-l-5 text-red">*</span></label>
                <div class="col-md-9">
                    <input name="profile" type="file" id="imageInput" class="form-control required" accept="image/*" onchange="previewImage(event)">
                    <input type="hidden" class="existing_profile" name="existing_profile" value="{{ $user->activeProfile($user->id) ?? '' }}">
                    @error('profile')
                        <span class="help-block help-block text-red">{{ $message }}</span>
                    @enderror
                    
                    <div id="imagePreview" class="m-t-5">
                        @if($user->activeProfile($user->id) != null)
                            <img src="{{ url('storage/profile_pictures').'/'.$user->activeProfile($user->id) }}" width="100" height="100" class="img-thumbnail" alt="Profile Picture">
                        @endif                        
                    </div>                    
                </div>
            </div>
        </div>        
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        event.target.value = '';
        return;
    }
    
    const img = document.createElement('img');
    img.classList.add('img-thumbnail', 'm-t-5');
    img.width = 100;
    img.height = 100;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        img.src = e.target.result;
    }
    
    reader.readAsDataURL(file);
    preview.appendChild(img);
}

$(document).ready(function() {
    const oldState = '{{ old('state', $user->defaultAddress?->address?->state ?? '') }}';
    const oldCity = '{{ old('city', $user->defaultAddress?->address?->city ?? '') }}';
    const oldAreaType = '{{ old('area_type', $user->defaultAddress?->address?->area_type ?? 'urban') }}';
    const oldTaluka = '{{ old('taluka', $user->defaultAddress?->address?->taluka ?? '') }}';
    const oldVillage = '{{ old('village', $user->defaultAddress?->address?->village ?? '') }}';

    async function loadDistricts(stateName, selectedDistrict = '') {
        if (!stateName) {
            $('#city').html('<option value="">Please select</option>').prop('disabled', true);
            $('#taluka').html('<option value="">Please select</option>').prop('disabled', true);
            $('#village').html('<option value="">Please select</option>');
            return;
        }

        $('#city').html('<option value="">Loading districts...</option>').prop('disabled', true);

        try {
            const districts = await $.ajax({
                url: '{{ route('admin.users.districts') }}',
                method: 'GET',
                data: { state: stateName }
            });

            let options = '<option value="">Please select</option>';
            districts.forEach(function(district) {
                const selected = district === selectedDistrict ? 'selected' : '';
                options += `<option value="${district}" ${selected}>${district}</option>`;
            });

            $('#city').html(options).prop('disabled', false);

            if (selectedDistrict) {
                await loadTalukas(selectedDistrict, oldTaluka);
            }
        } catch (error) {
            console.error('Error loading districts:', error);
            $('#city').html('<option value="">Error loading districts</option>');
        }
    }

    async function loadTalukas(districtName, selectedTaluka = '') {
        if (!districtName) {
            $('#taluka').html('<option value="">Please select</option>').prop('disabled', true);
            $('#village').html('<option value="">Please select</option>');
            return;
        }

        $('#taluka').html('<option value="">Loading talukas...</option>').prop('disabled', true);

        try {
            const talukas = await $.ajax({
                url: '{{ route('admin.users.talukas') }}',
                method: 'GET',
                data: { district: districtName }
            });

            let options = '<option value="">Please select</option>';
            talukas.forEach(function(talukaName) {
                const selected = talukaName === selectedTaluka ? 'selected' : '';
                options += `<option value="${talukaName}" ${selected}>${talukaName}</option>`;
            });

            $('#taluka').html(options).prop('disabled', false);

            if (selectedTaluka) {
                const areaType = $('input[name="area_type"]:checked').val();
                if (areaType === 'rural') {
                    await loadVillages(selectedTaluka, oldVillage);
                } else {
                    await loadPincode();
                }
            }
        } catch (error) {
            console.error('Error loading talukas:', error);
            $('#taluka').html('<option value="">Error loading talukas</option>');
        }
    }

    async function loadVillages(talukaName, selectedVillage = '') {
        if (!talukaName) {
            $('#village').html('<option value="">Please select</option>').prop('disabled', true);
            return;
        }

        $('#village').html('<option value="">Loading villages...</option>').prop('disabled', true);

        try {
            const villages = await $.ajax({
                url: '{{ route('admin.users.villages') }}',
                method: 'GET',
                data: { taluka: talukaName }
            });

            let options = '<option value="">Please select</option>';
            villages.forEach(function(villageObj) {
                const selected = villageObj.name === selectedVillage ? 'selected' : '';
                options += `<option value="${villageObj.name}" data-code="${villageObj.code}" ${selected}>${villageObj.name}</option>`;
            });

            $('#village').html(options).prop('disabled', false);

            if (selectedVillage) {
                await loadPincode();
            }
        } catch (error) {
            console.error('Error loading villages:', error);
            $('#village').html('<option value="">Error loading villages</option>');
        }
    }

    async function loadPincode() {
        const stateVal = $('#state').val();
        const cityVal = $('#city').val();
        const talukaVal = $('#taluka').val();
        const areaType = $('input[name="area_type"]:checked').val();
        
        let villageCode = '';
        if (areaType === 'rural') {
            villageCode = $('#village').find(':selected').data('code') || '';
            if (!villageCode) return;
        }

        if (!stateVal || !cityVal) return;

        $('#zip').val('').attr('placeholder', 'Loading pincode...');

        try {
            const response = await $.ajax({
                url: '{{ route('admin.users.pincode') }}',
                method: 'GET',
                data: {
                    state: stateVal,
                    district: cityVal,
                    taluka: talukaVal,
                    village_code: villageCode
                }
            });

            if (response.success && response.pincode) {
                $('#zip').val(response.pincode);
            } else {
                $('#zip').val('').attr('placeholder', 'Pincode not found');
            }
        } catch (error) {
            console.error('Error loading pincode:', error);
            $('#zip').val('').attr('placeholder', 'Pincode not found');
        }
    }

    // Event Listeners
    $('#state').on('change', function() {
        const stateName = $(this).val();
        $('#city').html('<option value="">Please select</option>').prop('disabled', true);
        $('#taluka').html('<option value="">Please select</option>').prop('disabled', true);
        $('#village').html('<option value="">Please select</option>').prop('disabled', true);
        $('#zip').val('');
        loadDistricts(stateName);
    });

    $('#city').on('change', function() {
        const districtName = $(this).val();
        $('#taluka').html('<option value="">Please select</option>').prop('disabled', true);
        $('#village').html('<option value="">Please select</option>').prop('disabled', true);
        $('#zip').val('');
        loadTalukas(districtName);
    });

    $('input[name="area_type"]').on('change', function() {
        const areaType = $(this).val();
        $('#zip').val('');
        if (areaType === 'rural') {
            $('#village-group').slideDown();
            const talukaVal = $('#taluka').val();
            if (talukaVal) {
                loadVillages(talukaVal);
            }
        } else {
            $('#village-group').slideUp();
            $('#village').html('<option value="">Please select</option>');
            loadPincode();
        }
    });

    $('#taluka').on('change', function() {
        const talukaName = $(this).val();
        $('#zip').val('');
        const areaType = $('input[name="area_type"]:checked').val();
        if (areaType === 'rural') {
            loadVillages(talukaName);
        } else {
            loadPincode();
        }
    });

    $('#village').on('change', function() {
        loadPincode();
    });

    // Initialize if values exist (editing mode)
    if (oldState) {
        loadDistricts(oldState, oldCity);
    } else {
        $('#city').prop('disabled', true);
        $('#taluka').prop('disabled', true);
        $('#village').prop('disabled', true);
    }
});
</script>