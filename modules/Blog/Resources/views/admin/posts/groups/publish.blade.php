{{-- <div class="box">
    <div class="box-header">
        <h5>{{ trans('blog::blog.posts.groups.youtube_video_link') }}</h5>
    </div>

    <div class="box-body">
        <div class="form-group">
            <label for="youtube_video_link" class="control-label text-left">
                {{ trans('blog::blog.posts.groups.youtube_video_link') }}
            </label>
            
            <input type="text" name="youtube_video_link" id="youtube_video_link" class="form-control" x-model="form.youtube_video_link" autofocus>
        </div>
    </div>
</div> --}}
<div class="box">
    <div class="box-header">
        <h5>{{ trans('blog::blog.posts.groups.publish') }}</h5>
    </div>

    <div class="box-body">
        <div class="form-group">
            <label for="publish_status" class="control-label text-left asterisk-align">
                {{ trans('blog::attributes.posts.publish_status') }}

                <span class="text-red">*</span>
            </label>

            <select name="publish_status" id="publish_status" class="form-control custom-select-black" x-model="form.publish_status">
                <option value="published" {{ old('publish_status') == 'published' ? 'selected' : '' }}>
                    {{ trans('blog::blog.posts.form.publish_status.published') }}
                </option>

                <option value="unpublished" {{ old('publish_status') == 'unpublished' ? 'selected' : '' }}>
                    {{ trans('blog::blog.posts.form.publish_status.unpublished') }}
                </option>
            </select>

            <template x-if="errors.has('publish_status')">
                <span class="help-block text-red" x-text="errors.get('publish_status')"></span>
            </template>
        </div>
    </div>
</div>