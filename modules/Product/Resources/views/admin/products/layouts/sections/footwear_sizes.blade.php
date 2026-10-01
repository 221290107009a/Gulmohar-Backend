<div class="box-header">
    <h5>
        <i class="fa fa-tag" style="margin-right:6px;color:#e63946;"></i>
        Footwear Size Inventory
    </h5>
</div>

<div class="box-body">
    <div class="alert alert-info" style="margin-bottom:16px;font-size:13px;">
        <i class="fa fa-info-circle" style="margin-right:6px;"></i>
        Add each shoe size and its available stock quantity. Sizes will show live on the frontend with real-time inventory counts. <strong>Qty = 0</strong> will mark that size as Sold Out automatically.
    </div>

    {{-- Size Inventory Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="footwear-size-table" style="table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th style="width:45px;text-align:center;">#</th>
                    <th style="white-space:nowrap;">Shoe Size <span class="text-red" style="display:inline !important;margin-left:2px;">*</span></th>
                    <th style="width:165px;white-space:nowrap;">Available Qty <span class="text-red" style="display:inline !important;margin-left:2px;">*</span></th>
                    <th style="width:145px;white-space:nowrap;">In Stock?</th>
                    <th style="width:60px;text-align:center;">Del</th>
                </tr>
            </thead>
            <tbody id="footwear-size-rows">
                @if(isset($product))
                    @foreach($product->sizeInventory()->orderBy('position')->get() as $i => $sizeRow)
                        <tr class="footwear-size-row">
                            <td class="text-center" style="vertical-align:middle;">
                                <span class="text-muted" style="font-size:12px;">{{ $i + 1 }}</span>
                            </td>
                            <td style="vertical-align:middle;">
                                <input
                                    type="text"
                                    name="size_inventory[{{ $i }}][size]"
                                    value="{{ $sizeRow->size }}"
                                    placeholder="e.g. UK 7"
                                    class="form-control"
                                    required
                                >
                            </td>
                            <td style="vertical-align:middle;">
                                <input
                                    type="number"
                                    name="size_inventory[{{ $i }}][qty]"
                                    value="{{ $sizeRow->qty }}"
                                    min="0"
                                    step="1"
                                    class="form-control footwear-qty-input"
                                    required
                                >
                            </td>
                            <td style="vertical-align:middle;">
                                <select name="size_inventory[{{ $i }}][in_stock]" class="form-control custom-select-black footwear-stock-select" style="width:100%;">
                                    <option value="1" {{ $sizeRow->in_stock ? 'selected' : '' }}>✓ In Stock</option>
                                    <option value="0" {{ !$sizeRow->in_stock ? 'selected' : '' }}>✗ Out of Stock</option>
                                </select>
                            </td>
                            <td class="text-center" style="vertical-align:middle;">
                                <button type="button" class="btn btn-sm btn-danger remove-footwear-row" title="Remove this size">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
        <button type="button" class="btn btn-sm" id="add-footwear-row" style="background:#e63946;color:#fff;border-color:#e63946;font-weight:600;">
            <i class="fa fa-plus"></i> Add Size Row
        </button>
        <button type="button" class="btn btn-default btn-sm" id="prefill-uk-men" title="Adds UK 6–12 rows with 10 qty each">
            <i class="fa fa-male"></i> Prefill Men's UK 6–12
        </button>
        <button type="button" class="btn btn-default btn-sm" id="prefill-uk-women" title="Adds Women's UK 3–8 rows with 8 qty each">
            <i class="fa fa-female"></i> Prefill Women's UK 3–8
        </button>
        <button type="button" class="btn btn-default btn-sm" id="clear-footwear-rows" title="Remove all size rows">
            <i class="fa fa-times"></i> Clear All
        </button>
    </div>

    <p class="help-block" style="font-size:11px;color:#999;margin-top:4px;">
        Tip: You can also use the <strong>Variations</strong> section above for complex size+colour combinations. This panel is for simple size-only inventory.
    </p>
</div>

@push('globals')
<script>
(function() {
    var footwearRowIndex = {{ isset($product) && $product->sizeInventory ? $product->sizeInventory->count() : 0 }};

    function buildFootwearRow(sizeName, qty, inStock) {
        var i = footwearRowIndex++;
        var rowNum = document.querySelectorAll('.footwear-size-row').length + 1;
        var selectedIn  = (inStock !== 0 && inStock !== '0' && inStock !== false) ? 'selected' : '';
        var selectedOut = (inStock === 0 || inStock === '0' || inStock === false) ? 'selected' : '';
        return [
            '<tr class="footwear-size-row">',
            '  <td class="text-center" style="vertical-align:middle;"><span class="text-muted" style="font-size:12px;">' + rowNum + '</span></td>',
            '  <td style="vertical-align:middle;"><input type="text" name="size_inventory[' + i + '][size]" value="' + (sizeName || '') + '" placeholder="e.g. UK 7" class="form-control" required></td>',
            '  <td style="vertical-align:middle;"><input type="number" name="size_inventory[' + i + '][qty]" value="' + (qty !== undefined ? qty : 0) + '" min="0" step="1" class="form-control footwear-qty-input" required></td>',
            '  <td style="vertical-align:middle;">',
            '    <select name="size_inventory[' + i + '][in_stock]" class="form-control custom-select-black footwear-stock-select" style="width:100%;">',
            '      <option value="1" ' + selectedIn  + '>✓ In Stock</option>',
            '      <option value="0" ' + selectedOut + '>✗ Out of Stock</option>',
            '    </select>',
            '  </td>',
            '  <td class="text-center" style="vertical-align:middle;"><button type="button" class="btn btn-sm btn-danger remove-footwear-row"><i class="fa fa-trash"></i></button></td>',
            '</tr>'
        ].join('');
    }

    // Intercept jQuery AJAX requests so size_inventory is automatically attached to admin form submit
    if (typeof $ !== 'undefined') {
        $(document).ajaxSend(function(event, jqXHR, settings) {
            if (settings.url && (settings.url.indexOf('/admin/products') !== -1)) {
                var sizeData = [];
                $('#footwear-size-rows .footwear-size-row').each(function() {
                    var size = $(this).find('input[name*="[size]"]').val();
                    var qty = $(this).find('input[name*="[qty]"]').val();
                    var inStock = $(this).find('select[name*="[in_stock]"]').val();
                    if (size && size.trim() !== '') {
                        sizeData.push({
                            size: size.trim(),
                            qty: parseInt(qty, 10) || 0,
                            in_stock: inStock === '1' || inStock === 1 ? 1 : 0
                        });
                    }
                });

                if (typeof settings.data === 'string') {
                    if (settings.data.indexOf('size_inventory') === -1) {
                        sizeData.forEach(function(item, idx) {
                            settings.data += (settings.data ? '&' : '') + 'size_inventory[' + idx + '][size]=' + encodeURIComponent(item.size);
                            settings.data += '&size_inventory[' + idx + '][qty]=' + item.qty;
                            settings.data += '&size_inventory[' + idx + '][in_stock]=' + item.in_stock;
                        });
                    }
                } else if (typeof settings.data === 'object' && settings.data !== null) {
                    settings.data.size_inventory = sizeData;
                }
            }
        });
    }

    // Auto-sync in_stock dropdown when qty changes
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('footwear-qty-input')) {
            var row = e.target.closest('.footwear-size-row');
            if (row) {
                var sel = row.querySelector('.footwear-stock-select');
                if (sel) sel.value = parseInt(e.target.value, 10) > 0 ? '1' : '0';
            }
        }
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button');
        if (!btn) return;

        // Remove single row
        if (btn.classList.contains('remove-footwear-row')) {
            var row = btn.closest('.footwear-size-row');
            if (row) row.remove();
            return;
        }

        // Add blank row
        if (btn.id === 'add-footwear-row') {
            document.getElementById('footwear-size-rows').insertAdjacentHTML('beforeend', buildFootwearRow('', 0, 1));
            return;
        }

        // Prefill Men's UK 6–12
        if (btn.id === 'prefill-uk-men') {
            if (!confirm("Add Men's UK 6–12 size rows (10 qty each)?")) return;
            var tbody = document.getElementById('footwear-size-rows');
            ['UK 6','UK 7','UK 8','UK 9','UK 10','UK 11','UK 12'].forEach(function(s) {
                tbody.insertAdjacentHTML('beforeend', buildFootwearRow(s, 10, 1));
            });
            return;
        }

        // Prefill Women's UK 3–8
        if (btn.id === 'prefill-uk-women') {
            if (!confirm("Add Women's UK 3–8 size rows (8 qty each)?")) return;
            var tbody = document.getElementById('footwear-size-rows');
            ['UK 3 (36)','UK 4 (37)','UK 5 (38)','UK 6 (39)','UK 7 (40)','UK 8 (41)'].forEach(function(s) {
                tbody.insertAdjacentHTML('beforeend', buildFootwearRow(s, 8, 1));
            });
            return;
        }

        // Clear all rows
        if (btn.id === 'clear-footwear-rows') {
            if (!confirm('Remove all size inventory rows?')) return;
            document.getElementById('footwear-size-rows').innerHTML = '';
            footwearRowIndex = 0;
            return;
        }
    });
})();
</script>
<style>
#footwear-size-table {
    table-layout: fixed;
    width: 100%;
}
#footwear-size-table th,
#footwear-size-table td {
    vertical-align: middle !important;
}
#footwear-size-table thead th {
    background: #1a1e2b !important;
    color: #f5d77f !important;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 2px solid #e63946 !important;
    padding: 10px 8px;
    white-space: nowrap !important;
}
#footwear-size-table thead th .text-red,
#footwear-size-table thead th span.text-red {
    display: inline !important;
    float: none !important;
    margin-left: 2px !important;
    color: #e63946 !important;
}
#footwear-size-table td {
    padding: 6px 8px;
}
#footwear-size-table .footwear-size-row:hover {
    background: #fff9f9;
}
#footwear-size-table .form-control {
    height: 34px;
    padding: 4px 8px;
    font-size: 13px;
}
</style>
@endpush
