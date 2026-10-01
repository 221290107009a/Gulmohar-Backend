@include('product::admin.products.layouts.sections.general')

<draggable
    animation="150"
    class="product-form-column"
    :class="{ dragging: isLeftColumnSectionDragging }"
    data-name="product-form-left-sections"
    force-fallback="true"
    handle=".drag-handle"
    :list="formLeftSections"
    :store="storeFormSections"
    @choose="isLeftColumnSectionDragging = true"
    @unchoose="isLeftColumnSectionDragging = false"
    @change="notifySectionOrderChange"
>
    <div class="box" v-for="(section, sectionIndex) in formLeftSections" :data-id="section" :key="sectionIndex">
        @include('product::admin.products.layouts.sections.attributes')
        @include('product::admin.products.layouts.sections.downloads')
        @include('product::admin.products.layouts.sections.variations')
        @include('product::admin.products.layouts.sections.variants')
        @include('product::admin.products.layouts.sections.options')
    </div>
    {{-- Footwear Size Inventory section (always shown, not part of draggable sections) --}}
    <div class="box" style="margin-top:16px;">
        @include('product::admin.products.layouts.sections.footwear_sizes')
    </div>
</draggable>
