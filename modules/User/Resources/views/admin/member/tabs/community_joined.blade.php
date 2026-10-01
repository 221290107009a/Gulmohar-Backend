<table class="table m-b-20">
    <thead>
        <tr>
            <th>Community Id</th>
            <th>Image</th>
            <th>name</th>
            <th>designation</th>
            <th>Joined At</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($communityMembers as $member)            
            <tr>
                <td style="vertical-align: middle;"><a href="{{ route('admin.communities.edit', $member->community_id) }}" target="_blank">{{$member->community_id}}</a></td>
                <td style="vertical-align: middle;"><img src="{{ asset('storage/community/'.$member->community->image) }}" alt="community image" style="width: 50px;"></td>
                <td style="vertical-align: middle;">{{$member->community->name}}</td>
                <td style="vertical-align: middle;text-transform: capitalize;">{{$member->designation}}</td>
                <td style="vertical-align: middle;">{{ date('d M Y | h:i A', strtotime($member->created_at)) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">
                    No Community Found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($communityMembers->hasPages())
    <div class="pagination-wrapper">
        {{ $communityMembers->appends(['tab' => 'community_joined'])->links() }}
    </div>
@endif

<style>
#community_joined.active + .form-group .btn-primary,
#community_joined.active + .tab-pane.fade + .form-group .btn-primary {
    display: none;
}
#community_joined .pagination-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
#community_joined .pagination-wrapper span.page-link {
    height: 32px;
    width: 32px;
    text-align: center;
    line-height: 30px;
    padding: 0;
}
#community_joined .las.la-angle-left{
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#community_joined .las.la-angle-left:before {
    content: "";
}
#community_joined .las.la-angle-right {
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#community_joined .las.la-angle-right:before {
    content: "";
}
</style>