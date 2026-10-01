<table class="table m-b-20">
    <thead>
        <tr>
            <th>Profile Picture</th>
            <th>Referral Type</th>
            <th>{{ trans('community::community_members.user_id') }}</th>
            <th>{{ trans('community::community_members.name') }}</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($refferedUsers as $user)
            @php
                if ($user->referredUser){
                    $arrayUser = $user->referredUser->toArray();
                }
            @endphp
            <tr>
                <td style="vertical-align: middle;"><img src="{{ asset('storage/profile_pictures/' . $user->referredUser->activeProfile($user->referredUser->id)) }}" alt="user-profile" style="width: 50px;"></td>
                <td style="vertical-align: middle;">
                    @if ($user && $user->referral_share_type)
                        {{ collect(explode('-', $user->referral_share_type))
                            ->map(fn($word) => ucfirst($word))
                            ->join(' ') }}
                    @endif
                </td>
                <td style="vertical-align: middle;"><a href="{{route('admin.member.edit', $user->referredUser->id)}}" target="_blank">{{ $user->referredUser->id}}</a></td>
                <td style="vertical-align: middle;">{{$arrayUser['fullname'] ?? ''}}</td>
                <td style="vertical-align: middle;">{{$user->referredUser->email}}</td>
                <td style="vertical-align: middle;">{{$user->referredUser->phone}}</td>
                <td style="vertical-align: middle;">{{ date('d M Y', strtotime($user->created_at)) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">
                    No Members Found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($refferedUsers->hasPages())
    <div class="pagination-wrapper">
        {{ $refferedUsers->appends(['tab' => 'reffered_users'])->links() }}
    </div>
@endif

<style>
#reffered_users.active + .tab-pane.fade + .form-group .btn-primary {
    display: none;
}
#reffered_users .pagination-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
#reffered_users .pagination-wrapper span.page-link {
    height: 32px;
    width: 32px;
    text-align: center;
    line-height: 30px;
    padding: 0;
}
#reffered_users .las.la-angle-left{
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#reffered_users .las.la-angle-left:before {
    content: "";
}
#reffered_users .las.la-angle-right {
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#reffered_users .las.la-angle-right:before {
    content: "";
}
</style>