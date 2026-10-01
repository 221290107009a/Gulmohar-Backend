<div class="row">
    @if($customTemplates)
        @foreach($customTemplates as $template)
            <div class="col-md-3">
                <div class="card m-b-20">               
                    <img src="{{ url('storage/templates/').'/'.$template->template_url }}" class="img-fluid" alt="Template Image" style="max-width: 100%; max-height: 100%;">
                </div>
            </div>
        @endforeach
    @endif
</div>

