@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('membershipfee::membership_fees.membership_fee')]) }}</dd>
    </dl>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'b', route: "{{ route('admin.membership_fees.index') }}" }
        ]);
    </script>
@endpush
