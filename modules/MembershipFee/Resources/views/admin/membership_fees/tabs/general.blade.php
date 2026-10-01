<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('membershipfee::attributes.name'), $errors, $membershipFee, ['required' => true]) }}
        {{ Form::select('duration', trans('membershipfee::attributes.duration'), $errors, trans('membershipfee::months'), $membershipFee, ['required' => true, 'class' => "required"]) }}
        {{ Form::number('membershipfee', trans('membershipfee::attributes.membershipfee'), $errors, $membershipFee,['required' => true, 'min' => '0']) }}
        {{ Form::checkbox('is_active', trans('membershipfee::attributes.is_active'), trans('membershipfee::membership_fees.form.enable_the_membershipfee'), $errors, $membershipFee) }}        
    </div>
</div>
