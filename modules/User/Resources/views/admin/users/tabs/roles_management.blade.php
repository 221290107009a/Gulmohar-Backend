<style type="text/css">
section.user_committee {
    margin-bottom: 30px;
}
.user_committee_title {
    margin-bottom: 20px;
}
.user_committee_title h4 {
    border-bottom: 1px solid #e3dcdc;
   width: 105px;
}	

section.user_position {
	display: none;
}
section.user_position {
    margin-bottom: 30px;
}
.user_position_title {
    margin-bottom: 10px;
}
.user_position_title h4 {
	border-bottom: 1px solid #e3dcdc;
     width: 77px;
}
ul.user_position_item{
    width: 100%;
}
ul.user_position_item li {
    width: 33%;
    display: inline-block;
}
label.position_label {
    font-weight: normal;
}
.error{
    color: red;
    font-weight: 300;
}
@media only screen and (max-width: 414px) {
ul.user_position_item li {
    width: 100%;
    display: block;
}
}
</style>
	<section class="user_committee">
		<div class="user_committee_title">
			<h4> {{ trans('user::attributes.users.committee') }} </h4>
		</div>
		<div class="user_committee_list">
			<select class="form-control select_committee" id="committee" name="committee_id" >
	            @foreach(userCommittee() as $key => $committee)
	                 <option value="{{ $key }}" @if(isset($user->userPositionData->first()->committee_id) && $user->userPositionData->first()->committee_id == $key) selected @endif > {{ $committee }}</option>
	            @endforeach
	        </select>
	        @error('committee_id')
	            <span class="error-message">{{ $message }}</span>
	        @enderror
	        <div class="committee_id_err error"></div>
		</div>
	</section> 


	<section class="user_position">
		<div class="user_position_title">
			<h4>{{ trans('user::attributes.users.position') }} </h4>
		</div>
		<div class="user_position_list">
			<ul class="user_position_item">
				@foreach(userPosition() as $key => $position)
					<li>
						<div class="checkbox">
							<input type="checkbox" value="{{ $key }}" id="check_postion_{{ $key }}" class="check_postion" name="position_id[]" 
							@if($user->getSelectedPosition($key) == true) checked @endif  >
							
					        <label class="position_label" for="check_postion_{{ $key }}">{{ $position }}</label>
					     </div>
					</li>
				@endforeach
			</ul>
			@error('position_id')
	            <span class="error-message">{{ $message }}</span>
	        @enderror
			<div class="position_id_err error"></div>
			<div class="specialities_err error"></div>
		</div>
	</section>
	

