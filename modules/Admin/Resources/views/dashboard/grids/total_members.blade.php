<div class="col-lg-3 col-md-6 col-sm-6">
    <div class="single-grid" style="background: #2563eb; color: #fff;">
        <div>
            <span class="count">{{ $totalMembers ?? $totalCustomers }}</span>
            <span class="title">
                <a href="{{ route('admin.member.index') }}" style="color: #fff; text-decoration: none;">
                    {{ trans('admin::dashboard.total_members') }}
                </a>
            </span>
        </div>
        <div class="single-grid-icon">
            <a href="{{ route('admin.member.index') }}" style="color: rgba(255,255,255,0.85);">
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
