<div class="accordion-box-content">
    <div class="tab-content clearfix">
        <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.slide_section') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_slide_section_content]', trans('storefront::attributes.storefront_slide_section_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}
				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_slide_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_slide_section'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>
		<div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.about_section') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_about_section_content]', trans('storefront::attributes.storefront_about_section_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}
				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_about_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_about_section'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>
    	<div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.download_section') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_download_section_content]', trans('storefront::attributes.storefront_download_section_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_download_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_download_section'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	 
         <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.hero_content') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_hero_content]', trans('storefront::attributes.storefront_hero_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_hero_content_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_hero_content'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
         <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.sangho_support') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_sangho_support_content]', trans('storefront::attributes.storefront_sangho_support_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_sangho_support_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_sangho_support'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
          <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.mobbin_showcase') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_mobbin_showcase_content]', trans('storefront::attributes.storefront_mobbin_showcase_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_mobbin_showcase_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_mobbin_showcase'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
          <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.bahujan_knowledge') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_bahujan_knowledge_content]', trans('storefront::attributes.storefront_bahujan_knowledge_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_bahujan_knowledge_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_bahujan_knowledge'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
          <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.community_engagement') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_community_engagement_content]', trans('storefront::attributes.storefront_community_engagement_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_community_engagement_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_community_engagement'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
        <div class="panel">
			<div class="panel-header">
		        <h5>{{ trans('storefront::storefront.form.one_app_community') }}</h5>
		    </div>
		    <div class="panel-body">
				{{ Form::wysiwyg('translatable[storefront_one_app_community_content]', trans('storefront::attributes.storefront_one_app_community_content'), $errors, $settings, ['labelCol' => 2, 'required' => true]) }}

				<div class="row">
					<div class="col-md-8">
						{{ Form::checkbox('storefront_one_app_community_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_one_app_community'), $errors, $settings) }}
					</div>
				</div>
		    </div>
		</div>	
        <div class="panel">
			<div class="panel-header" style="border-bottom: 2px solid #f1f1f1; padding: 15px 20px; background: #fafafa; border-radius: 8px 8px 0 0;">
                <h5 style="font-weight: 700; color: #333; margin: 0; font-size: 16px;">
                    {{ trans('storefront::storefront.form.showcase') }}
                </h5>
            </div>
		    <div class="panel-body" style="padding: 25px;">
                {{ Form::text('translatable[storefront_showcase_section_title]', trans('storefront::attributes.storefront_showcase_title'), $errors, $settings) }}
                {{ Form::checkbox('storefront_showcase_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_showcase'), $errors, $settings) }}
                
                <hr style="margin: 20px 0; border-top: 2px solid #f1f3f9;">
                
                <div id="showcase-wrapper">
                    @foreach(range(1, 30) as $index)
                        <div class="showcase-item-box {{ $index > 1 && !setting("storefront_showcase_item_{$index}_title") ? 'hidden' : '' }}" 
                             data-index="{{ $index }}" 
                             style="background: #ffffff; border: 1px solid #e3e6f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: all 0.3s ease;">
                            
                            <div class="item-header clearfix" style="margin-bottom: 20px; border-bottom: 1px solid #f8f9fc; padding-bottom: 10px;">
                                <h6 class="pull-left" style="margin: 5px 0; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                    Showcase Item {{ $index }}
                                </h6>
                                @if($index > 1)
                                    <button type="button" class="btn btn-link remove-showcase pull-right" style="color: #e74a3b; padding: 0; text-decoration: none; font-size: 13px;">
                                        <i class="fa fa-trash m-r-5"></i> Remove
                                    </button>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                     @include('media::admin.image_picker.single', [
                                        'title' => trans('storefront::attributes.storefront_showcase_item_image'),
                                        'inputName' => "storefront_showcase_item_{$index}_image",
                                        'file' => ${"showcase{$index}Image"},
                                    ])
                                </div>
                                <div class="col-md-8">
                                    {{ Form::text("translatable[storefront_showcase_item_{$index}_title]", trans('storefront::attributes.storefront_showcase_item_title'), $errors, $settings) }}
                                    
                                    <div class="form-group clearfix" style="margin-top: 15px;">
                                        <label class="col-md-3 control-label text-left" style="font-weight: 500;">Status</label>
                                        <div class="col-md-9" style="padding-top: 2px;">
                                            <div class="checkbox" style="margin: 0;">
                                                <input type="hidden" name="storefront_showcase_item_{{ $index }}_active" value="0">
                                                <input type="checkbox" name="storefront_showcase_item_{{ $index }}_active" id="showcase_item_{{ $index }}_active" value="1" {{ setting("storefront_showcase_item_{$index}_active") ? 'checked' : '' }}>
                                                <label for="showcase_item_{{ $index }}_active" style="padding-left: 30px; color: #171818ff;">Is_active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="form-group">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="button" id="add-new-showcase" class="btn btn-default">
                            <i class="fa fa-plus m-r-5"></i> Add more
                        </button>
                    </div>
                </div>
		    </div>
		</div>	
            <div class="panel-header" style="border-bottom: 2px solid #f1f1f1; padding: 15px 20px; background: #fafafa; border-radius: 8px 8px 0 0;">
                <h5 style="font-weight: 700; color: #333; margin: 0; font-size: 16px;">
                   {{ trans('storefront::storefront.form.Intelligent_Digital_Solutions') }}
                </h5>
            </div>
		    <div class="panel-body" style="padding: 25px;">
                {{ Form::text('translatable[storefront_intelligent_digital_solutions_section_title]', trans('storefront::attributes.storefront_intelligent_digital_solutions_title'), $errors, $settings) }}
                {{ Form::text('translatable[storefront_intelligent_digital_solutions_section_subtitle]', trans('storefront::attributes.storefront_intelligent_digital_solutions_subtitle'), $errors, $settings) }}
                {{ Form::textarea('translatable[storefront_intelligent_digital_solutions_section_description]', trans('storefront::attributes.storefront_intelligent_digital_solutions_description'), $errors, $settings, ['rows' => 3]) }}
                <hr style="margin: 20px 0; border-top: 2px solid #f1f3f9;">
                <div id="solutions-wrapper">
                    @foreach(range(1, 30) as $index)
                        <div class="solution-item-box {{ $index > 1 && !setting("storefront_intelligent_digital_solutions_item_{$index}_title") ? 'hidden' : '' }}" 
                             data-index="{{ $index }}" 
                             style="background: #ffffff; border: 1px solid #e3e6f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: all 0.3s ease;">
                            
                            <div class="item-header clearfix" style="margin-bottom: 20px; border-bottom: 1px solid #f8f9fc; padding-bottom: 10px;">
                                <h6 class="pull-left" style="margin: 5px 0; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                    Solution Card {{ $index }}
                                </h6>
                                @if($index > 1)
                                    <button type="button" class="btn btn-link remove-solution pull-right" style="color: #e74a3b; padding: 0; text-decoration: none; font-size: 13px;">
                                        <i class="fa fa-trash m-r-5"></i> Remove
                                    </button>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    @include('media::admin.image_picker.multiple', [
                                        'title' => 'Slider Images (Mobile View)',
                                        'inputName' => "translatable[storefront_intelligent_digital_solutions_item_{$index}_images][]",
                                        'files' => ${"solution{$index}Images"},
                                    ])
                                </div>
                            </div>

                            <hr style="margin: 20px 0; border-top: 1px solid #f1f3f9;">

                            <div class="row">
                                <div class="col-md-12">
                                    {{ Form::text("translatable[storefront_intelligent_digital_solutions_item_{$index}_title]", trans('storefront::attributes.title'), $errors, $settings) }}
                                    {{ Form::text("translatable[storefront_intelligent_digital_solutions_item_{$index}_desc]", trans('storefront::attributes.short_description'), $errors, $settings) }}
                                    
                                    <div class="form-group clearfix" style="margin-top: 15px;">
                                        <label class="col-md-3 control-label text-left" style="font-weight: 500;">Status</label>
                                        <div class="col-md-9" style="padding-top: 2px;">
                                            <div class="checkbox" style="margin: 0;">
                                                <input type="hidden" name="storefront_intelligent_digital_solutions_item_{{ $index }}_active" value="0">
                                                <input type="checkbox" name="storefront_intelligent_digital_solutions_item_{{ $index }}_active" id="item_{{ $index }}_active" value="1" {{ setting("storefront_intelligent_digital_solutions_item_{$index}_active") ? 'checked' : '' }}>
                                                <label for="item_{{ $index }}_active" style="padding-left: 30px; color: #171818ff;">Is_active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="button" id="add-new-solution" class="btn btn-default">
                            <i class="fa fa-plus m-r-5"></i> Add more
                        </button>
                    </div>
                </div>
		    </div>
		</div>		
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                function setupDynamicSection(addBtnId, wrapperId, itemBoxClass, removeBtnClass, confirmMsg) {
                    const addBtn = document.getElementById(addBtnId);
                    const wrapper = document.getElementById(wrapperId);

                    if (addBtn) {
                        addBtn.addEventListener('click', function() {
                            const hiddenItem = wrapper.querySelector(`.${itemBoxClass}.hidden`);
                            if (hiddenItem) {
                                hiddenItem.classList.remove('hidden');
                            } else {
                                alert('Maximum items reached.');
                            }
                        });
                    }

                    if (wrapper) {
                        wrapper.addEventListener('click', function(e) {
                            const removeBtn = e.target.closest(`.${removeBtnClass}`);
                            if (removeBtn) {
                                const box = removeBtn.closest(`.${itemBoxClass}`);
                                if (confirm(confirmMsg)) {
                                    box.classList.add('hidden');
                                    box.querySelectorAll('input').forEach(input => {
                                        if (input.type === 'checkbox') input.checked = false;
                                        else if (input.type !== 'hidden') input.value = '';
                                    });
                                }
                            }
                        });
                    }
                }

                setupDynamicSection('add-new-solution', 'solutions-wrapper', 'solution-item-box', 'remove-solution', 'Are you sure you want to remove this item?');
                setupDynamicSection('add-new-showcase', 'showcase-wrapper', 'showcase-item-box', 'remove-showcase', 'Are you sure you want to remove this item?');
            });
        </script>
	</div>
</div>


@push('globals')
    @vite([
        'modules/Page/Resources/assets/admin/sass/main.scss',
        'modules/Page/Resources/assets/admin/js/main.js',
    ])
@endpush