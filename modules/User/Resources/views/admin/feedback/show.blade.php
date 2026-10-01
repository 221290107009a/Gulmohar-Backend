@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Feedback Details')

    <li><a href="{{ route('admin.feedback.index') }}">Feedbacks</a></li>
    <li class="active">Feedback Details</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="feedback-detail">
            <div class="table-responsive">
                <form method="POST" action="{{ route('admin.feedback.update', $feedback->id) }}" id="feedback-form">
                    {{ csrf_field() }}
                    {{ method_field('put') }}
                    <table class="table">
                        <tbody>
                            <tr>
                                <td width='30%'>Feedback ID</td>
                                <td>{{ $feedback->id }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>User ID</td>
                                <td><a href="{{ route('admin.member.edit', $feedback->user_id) }}" target="_blank">{{ $feedback->user_id }}</a></td>
                            </tr>
                            <tr>
                                <td width='30%'>User Name</td>
                                <td>{{ $feedback->user->fullname ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>User Email</td>
                                <td>{{ $feedback->user->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>Subject</td>
                                <td>{{ $feedback->subject }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>App Version</td>
                                <td>{{ $feedback->app_version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>OS Version</td>
                                <td>{{ $feedback->os_version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>Device Model</td>
                                <td>{{ $feedback->device_model ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td width='30%'>Network Type</td>
                                <td>{{ $feedback->network_type ?? 'N/A' }}</td>
                            </tr>
                            <!-- <tr>
                                <td width='30%'>Current Route</td>
                                <td>{{ $feedback->current_route ?? 'N/A' }}</td>
                            </tr> -->
                            <tr>
                                <td width='30%'>Message</td>
                                <td style="white-space: pre-wrap;">{{ $feedback->message }}</td>
                            </tr>
                            @if($feedback->image)
                                <tr>
                                    <td width='30%'>Attachment</td>
                                    <td>
                                        <a href="{{ url($feedback->image) }}" target="_blank">
                                            <img src="{{ url($feedback->image) }}" class="img-responsive thumbnail" style="max-width: 300px;">
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td width='30%'>Status</td>
                                <td>
                                    <select class="custom-select-black" name="status">
                                        <option value="pending" {{ $feedback->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="resolved" {{ $feedback->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colSpan="2" align="center">
                                    <button type="submit" class="btn btn-primary" style="margin-right: 10px;">Save Changes</button>
                                    <button type="button" class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete this feedback?')) document.getElementById('delete-feedback-form').submit();">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

                <form id="delete-feedback-form" action="{{ route('admin.feedback.destroy', $feedback->id) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
@endsection
