<table class="table m-b-20">
    <thead>
        <tr>
            <th>Business Id</th>
            <th>Image</th>
            <th>Type</th>
            <th>Title</th>
            <th>Address</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($businesses as $business)
            @php
                $business->media = explode(',', $business->media);
            @endphp
            
            <tr>
                <td style="vertical-align: middle;"><a href="{{ route('admin.businesses.edit', $business->id) }}" target="_blank">{{$business->id}}</a></td>
                <td style="vertical-align: middle;"><img src="{{ asset('storage/business_media/'.$business->media['0']) }}" alt="community image" style="width: 50px;"></td>
                <td style="vertical-align: middle;text-transform: capitalize;">{{$business->business_type}}</td>
                <td style="vertical-align: middle;">{{$business->title}}</td>
                <td style="vertical-align: middle;" width="30%">{{$business->address, $business->city, $business->state, $business->pincode }}</td>
                <td style="vertical-align: middle;">{{ date('d M Y', strtotime($business->created_at)) }}</td>
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

@if($businesses->hasPages())
    <div class="pagination-wrapper">
        {{ $businesses->appends(['tab' => 'business'])->links() }}
    </div>
@endif

<style>
#business.active + .form-group .btn-primary,
#business.active + .tab-pane.fade + .form-group .btn-primary {
    display: none;
}
#business .pagination-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
#business .pagination-wrapper span.page-link {
    height: 32px;
    width: 32px;
    text-align: center;
    line-height: 30px;
    padding: 0;
}
#business .las.la-angle-left{
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#business .las.la-angle-left:before {
    content: "";
}
#business .las.la-angle-right {
    display: inline-block;
    font: 14px / 1 FontAwesome;
    font-size: inherit;
}
#business .las.la-angle-right:before {
    content: "";
}
</style>