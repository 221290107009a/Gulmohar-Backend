{{ Form::select('user_id', 'Select Member', $errors, $users, $seller, [
    'labelCol' => 2,
    'required' => true,
    'class' => 'selectize prevent-creation',
    'placeholder' => 'Select a member'
]) }}
{{ Form::text('shop_name', 'Shop Name', $errors, $seller, ['labelCol' => 2, 'required' => true]) }}
{{ Form::text('owner_name', 'Owner Name', $errors, $seller, ['labelCol' => 2, 'required' => true]) }}
{{ Form::wysiwyg('description', 'Shop Description', $errors, $seller, ['labelCol' => 2, 'required' => true]) }}


{{ Form::text('phone', 'Phone Number', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,
    'placeholder' => '98xxxxxxxx',
    'type' => 'tel'
]) }}

{{ Form::email('email', 'Email', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,
    'placeholder' => 'example@gmail.com'
]) }}



@push('styles')
<style>
    .btn-group .btn-primary.active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25) !important;
    }
    .btn-group .btn-primary {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
</style>
@endpush

<div class="form-group">
    <label class="col-md-2 control-label" style="text-align: left;">What are you looking to sell on Sangho?</label>
    <div class="col-md-10">
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-primary {{ old('category', $seller->category ?? 'all') == 'all' ? 'active' : '' }}">
                <input type="radio" name="category" value="all" {{ old('category', $seller->category ?? 'all') == 'all' ? 'checked' : '' }}> All Categories
            </label>
            <label class="btn btn-primary {{ old('category', $seller->category ?? 'all') == 'books' ? 'active' : '' }}">
                <input type="radio" name="category" value="books" {{ old('category', $seller->category ?? 'all') == 'books' ? 'checked' : '' }}> Only Books
            </label>
        </div>
        @if ($errors->has('category'))
            <span class="help-block">{{ $errors->first('category') }}</span>
        @endif
    </div>
</div>

<div class="gst-field {{ old('category', $seller->category ?? 'all') == 'all' ? '' : 'hidden' }}">
    {{ Form::text('gst_number', 'GST Number', $errors, $seller, [
        'labelCol' => 2,
        'placeholder' => 'Enter GST Number'
    ]) }}
</div>

<div class="pan-field {{ old('category', $seller->category ?? 'all') == 'books' ? '' : 'hidden' }}">
    {{ Form::text('pan_number', 'PAN Number', $errors, $seller, [
        'labelCol' => 2,
        'placeholder' => 'Enter PAN Number'
    ]) }}
</div>


{{ Form::select('state', 'State', $errors, [''=>'Please select'] + states(), $seller, [
    'labelCol' => 2,
    'required' => true,
    'class' => 'selectize',
    'placeholder' => 'Select State'
])}}
{{ Form::text('city', 'City', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,    
    'placeholder' => 'Enter City'
]) }}
{{ Form::textarea('address1', 'Locality / Area / Village', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,
    'placeholder' => 'Enter Your Locality / Area / Village',
    'rows' => 4
]) }}
{{ Form::textarea('address2', 'Address', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,
    'placeholder' => 'Enter Your Shop Address',
    'rows' => 4
]) }}
{{ Form::text('pincode', 'Pincode', $errors, $seller, [
    'labelCol' => 2,
    'required' => true,
    'placeholder' => 'Enter Your Pincode'    
]) }}
<div class="row">
    <div class="col-md-8">
        {{ Form::select('current_status', 'Current Status', $errors, Modules\Seller\Entities\Seller::getStatuses(), $seller, [
            'labelCol' => 3,
            'required' => true,
            'class' => 'selectize',
            'placeholder' => 'Select Status'
        ]) }}
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('is_active', trans('seller::attributes.is_active'), trans('seller::sellers.form.enable_the_seller'), $errors, $seller) }}
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('input[name="category"]').change(function() {
            var category = $(this).val();
            if (category === 'all') {
                $('.gst-field').removeClass('hidden');
                $('.pan-field').addClass('hidden');
            } else if (category === 'books') {
                $('.pan-field').removeClass('hidden');
                $('.gst-field').addClass('hidden');
            }
        });
    });
</script>
@endpush