@extends('admin::layout')

@component('admin::components.page.header')
	@slot('title',trans('user::subscription_histories.show_subscription_history'))

    <li>{{ trans('user::subscription_histories.show_subscription_history') }}</li>
	<li class="active">{{ trans('admin::resource.edit', ['resource' => "member"]) }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="subscription_histories-table"">
            <div class="row">
		        <div class="col-md-12">
		            <div class="order clearfix">
		                <div class="table-responsive">
		                    <table class="table">
		                    	<tbody>
		                        	<tr>
		                                <td width='30%'>{{ trans('admin::admin.table.id') }}</td>
		                                <td>{{ $subscriptionHistory->id }}</td>
		                            </tr>
                                    <tr>
		                                <td width='30%'>{{ trans('user::users.user_id') }}</td>
		                                <td><a href="{{route('admin.member.edit', $subscriptionHistory->user_id)}}" target="_blank">{{ $subscriptionHistory->user_id }} </a> </td>
		                            </tr>
									<tr>
		                                <td width='30%'>{{ trans('membershipfee::attributes.membershipfee') }}</td>
		                                <td>{{ $subscriptionHistory->subscription_fees }}</td>
		                            </tr>
									<tr>
		                                <td width='30%'>{{ trans('membershipfee::attributes.name') }}</td>
		                                <td>{{ $subscriptionHistory->subscription_fees_name }}</td>
		                            </tr>
									<tr>
		                                <td width='30%'>{{ trans('membershipfee::attributes.duration') }}</td>
		                                <td>{{ $subscriptionHistory->subscription_fees_duration }}</td>
		                            </tr>
									<tr>
		                                <td width='30%'>{{ trans('user::attributes.users.start_date') }}</td>
										<td>{{ date('d M Y, g:i A', strtotime($subscriptionHistory->start_date)) }}</td>
		                            </tr>
									<tr>
		                                <td width='30%'>{{ trans('user::attributes.users.end_date') }}</td>
										<td>{{ date('d M Y, g:i A', strtotime($subscriptionHistory->end_date)) }}</td>
		                            </tr>
		                        </tbody>
		                    </table>
		                </div>
		            </div>
		        </div>
		    </div>
        </div>
    </div>
@endsection