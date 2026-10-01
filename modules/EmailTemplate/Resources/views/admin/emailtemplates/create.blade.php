@extends('admin::layout')

@component('admin::components.page.header')
    @if(request()->routeIs('admin.emailtemplates.edit'))
        @slot('title', trans('admin::resource.edit', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]))
        <li><a href="{{ route('admin.emailtemplates.index') }}">{{ trans('emailtemplate::emailtemplates.emailtemplates') }}</a></li>
        <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]) }}</li>
    @else
        @slot('title', trans('admin::resource.create', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]))
        <li><a href="{{ route('admin.emailtemplates.index') }}">{{ trans('emailtemplate::emailtemplates.emailtemplates') }}</a></li>
        <li class="active">{{ trans('admin::resource.create', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]) }}</li>
    @endif
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.emailtemplates.store') }}" class="form-horizontal" id="emailtemplate-create-form" novalidate>
        {{ csrf_field() }}

        {{--  {!! $tabs->render(compact('emailTemplate')) !!} --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
           
    <section class="edit">
        <div class="email-navbar email-navbar-fixed-top navbar-layoutit">
            <div class="navbar-header">
                <button data-target="navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
                    <span class="glyphicon-bar"></span>
                    <span class="glyphicon-bar"></span>
                    <span class="glyphicon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav" id="menu-layoutit">
                    <li>
                        <span id="messagefromphp"></span>
                        <span id="messagefromphp2"></span>
                        {{-- <div class="btn-group" data-toggle="buttons-radio">
                            <button type="button" class="btn btn btn-default" id="tags"><i class="fa fa-tags"></i> Tags</button>
                        </div> --}}
                        <div class="btn-group" data-toggle="buttons-radio">
                            <button type="button" class="btn btn btn-default" id="sourcepreview"><i class="glyphicon-eye-open glyphicon"></i> Preview</button>
                        </div>
                        <div class="btn-group">
                            <a class="btn btn btn-warning" href="#save" id="save" ><i class="glyphicon glyphicon-floppy-disk"></i> Save </a>
                        </div>                        
                    </li>
                </ul>
            </div><!--/.navbar-collapse -->
        </div><!--/.navbar-fixed-top -->
        <div class="box email-fillds">
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-md-2">
                        <label for="name" class="control-label text-left">Name<span class="m-l-5 text-red">*</span></label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control required" id="name" value="{{ $emailTemplate->name?? '' }}">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-2">
                        <label for="subject" class="control-label text-left">Subject<span class="m-l-5 text-red">*</span></label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="subject" class="form-control required" id="subject" value="{{ $emailTemplate->subject?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="row email-temp-main">
            <div class="sidebar-nav" style="overflow:auto;">
                <!-- Nav tabs -->
                <div id="elements">
                    <ul class="nav nav-list accordion-group">
                        <li class="side-panel-main headers">
                            <div class="side-panel-heading">
                                <h4 class="side-panel-title">Header <i class="fa fa-plus"></i></h4>
                            </div>
                            @include('emailtemplate::admin.emailtemplates.header')
                        </li>
                        <li class="side-panel-main contents">
                            <div class="side-panel-heading">
                                <h4 class="side-panel-title">Content <i class="fa fa-plus"></i></h4>
                            </div>
                            @include('emailtemplate::admin.emailtemplates.content')
                        </li>
                        <li class="side-panel-main footers">
                            <div class="side-panel-heading">
                                <h4 class="side-panel-title">Footer <i class="fa fa-plus"></i></h4>
                            </div>                            
                            @include('emailtemplate::admin.emailtemplates.footers')
                        </li>
                        <li class="side-panel-main">
                            <div class="side-panel-heading">
                                <h4 class="side-panel-title">Custom Controller <i class="fa fa-plus"></i></h4>
                            </div>
                            @include('emailtemplate::admin.emailtemplates.custom-controls')
                        </li>
                    </ul>
                </div>
                <!-- END DROP ELEMENTS -->
                <!-- START ELEMENT -->
                <div class="hide" id="settings">
                    <form class="form-inline" id="common-settings">
                        <div class="margin-setting" style="margin-bottom: 15px;padding-bottom: 15px; border-bottom: 1px solid #dadfe1;">
                        <h4 class="text text-info">Margin</h4>
                        <center>
                            <table>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td><input type="text" class="form-control" placeholder="top" title="Top" id="mtop" name="mtop" style="width: 60px; margin-right: 5px"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="form-control" placeholder="left" title="Left" id="mleft" name="mleft" style="width: 60px; margin-right: 5px"></td>
                                        <td></td>
                                        <td><input type="text" class="form-control" placeholder="right" title="Right" id="mright" name="mright" style="width: 60px; margin-right: 5px"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><input type="text" class="form-control" placeholder="bottom" title="Bottom" id="mbottom" name="mbottom" style="width: 60px; margin-right: 5px"></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                        </div>
                        <h4 class="text text-info">Padding</h4>
                            <center>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td><input type="text" class="form-control" placeholder="top" value="15px" id="ptop" name="ptop" style="width: 60px; margin-right: 5px"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" class="form-control" placeholder="left" value="15px" id="pleft" name="mtop" style="width: 60px; margin-right: 5px"></td>
                                            <td></td>
                                            <td><input type="text" class="form-control" placeholder="right" value="15px" id="pright" name="mbottom" style="width: 60px; margin-right: 5px"></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><input type="text" class="form-control" placeholder="bottom" value="15px" id="pbottom" name="pbottom" style="width: 60px; margin-right: 5px"></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </center>

                    </form>

                    <h4  style="margin-bottom: 10px;" class="text text-info">Style</h4>
                    <form id="background"  class="form-inline">
                        <div class="form-group">
                            <label for="bgcolor">Background</label>
                            <div class="color-circle" id="bgcolor"></div>
                            
                        </div>
                    </form>

                    <form class="form-inline" id="font-settings" style="margin-top:5px">
                        <div class="form-group">
                            <label for="fontstyle">Font style</label>
                            <div id="fontstyle" class="color-circle"><i class="fa fa-font"></i></div>
                        </div>
                    </form>

                    <div class="hide" id='font-style'>
                        <div id="mainfontproperties" >
                            <div class="input-group" style="margin-bottom: 5px;height: 42px;">
                                <span class="input-group-addon" style="min-width: 60px;">Color</span>
                                <input type="text" class="form-control picker colortext" onfocus="getColorText()" id="colortext" >
                                <span class="input-group-addon"></span>
                            </div>                            
                            <div class="input-group" style="margin-bottom: 5px;height: 42px;">
                                <span class="input-group-addon" style="min-width: 60px;">Size</span>
                                <input type="text" class="form-control " id="sizetext" style="width: 75px;height: 42px !important; ">
                                &nbsp;
                                <a class="btn btn-default plus m-r-10" href="#" style="height: 42px;">+</a>
                                <a class="btn btn-default minus" href="#" style="height: 42px;">-</a>
                            </div>

                            <hr/>
                            <div class="text text-right">
                                <a class="btn btn-info" id="confirm-font-properties">OK</a>
                            </div>
                        </div>
                    </div>
                    <form id="editorlite" style="margin-top:5px">
                        <textarea type="text" style="width:100%; margin-bottom: 15px" class="panel panel-body panel-default html5editorlite" id="html5editorlite"></textarea>
                        Alignment: <select id="allineamento">
                            <option value=""></option>
                            <option value="left">left</option>
                            <option value="right">right</option>
                            <option value="center">center</option>
                        </select>
                    </form>
                    <div id="imageproperties" style="margin-top:25px">
                        <h4 style="margin-bottom: 10px;">Image Edit</h4>
                        <div class="form-group">
                             <div class="row">
                                <div class="col-xs-10">
                                    <input type="text" id="image-link-url" class="form-control" data-id="none"/>
                                </div>                                 
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-8">
                                    <input type="text" id="image-url" class="form-control" data-id="none"/>
                                </div>
                                <div class="col-xs-4">
                                    <div class="slide-image" data-slide-number="<%- slideNumber %>">
                                        <span class="btn btn-default">Browse</span>
                                    </div>                                    
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-1">
                                    W:
                                </div>
                                <div class="col-xs-3">
                                    <input type="text" id="image-w" class="form-control" name="director" />
                                </div>

                                <div class="col-xs-1">
                                    H:
                                </div>

                                <div class="col-xs-3">
                                    <input type="text" id="image-h"class="form-control" name="writer" />
                                </div>

                                <div class="col-xs-4">

                                    <a class="btn btn-warning" id="change-image"><i class="fa fa-edit"></i>&nbsp;Apply</a>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <div id="social-links">
                        <h4 style="margin: 10px 0;">Social Links</h4>
                        <ul class="list-group" id="social-list">
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-youtube-play"></i></span>
                                    <input type="text" class="form-control social-input" name="youtube" value="{{ setting('storefront_youtube_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="youtube" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-facebook-official"></i></span>
                                    <input type="text" class="form-control social-input" name="facebook" value="{{ setting('storefront_facebook_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="facebook" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-linkedin"></i></span>
                                    <input type="text" class=" form-control social-input" name="linkedin" value="{{ setting('storefront_linkedin_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="linkedin" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-google"></i></span>
                                    <input type="text" class=" form-control social-input" name="google" value="{{ route('home') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="google" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-twitter"></i></span>
                                    <input type="text" class=" form-control social-input" name="twitter" value="{{ setting('storefront_twitter_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="twitter" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-pinterest-p"></i></span>
                                    <input type="text" class=" form-control social-input" name="pinterest" value="{{ setting('storefront_instagram_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" name="pinterest" class="social-check"/></span>
                                </div>
                            </li>
                            <li>
                                <div class="input-group">
                                    <span class="input-group-addon" ><i class="fa fa-2x fa-instagram"></i></span>
                                    <input type="text" class=" form-control social-input" name="instagram" value="{{ setting('storefront_pinterest_link') }}" style="height:48px"/>
                                    <span class="input-group-addon" ><input type="checkbox" checked="checked" name="instagram" class="social-check" /></span>
                                </div>
                            </li> 
                        </ul>
                    </div>

                    <div id="buttons" style="max-width: 400px; margin-top: 25px;">
                        <h4 style="margin-bottom: 10px;">Button Edit</h4>
                        <div class="form-group">
                            <select class="form-control">
                                <option value="center">Align buttons to Center</option>
                                <option value="left">Align buttons to Left</option>
                                <option value="right">Align buttons to Right</option>
                            </select>
                        </div>
                        <ul id="buttonslist" class="list-group">
                            <li class="hide" style="padding:10px; border:1px solid #DADFE1; border-radius: 4px">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Enter Button Title" name="btn_title"/>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-paperclip"></i></span>
                                    <input type="text" class="form-control"  placeholder="Add link to button" aria-describedby="basic-addon1" name="btn_link"/>
                                </div>
                                <div class="input-group" style="margin-top:10px">
                                    <label for="buttonStyle">Button Style</label>
                                    <div   class="color-circle buttonStyle" data-original-title="" title="">
                                        <i class="fa fa-font"></i>
                                    </div>
                                    <div class="stylebox hide">
                                        <label> Button Size</label>
                                        <div class="input-group " style="margin-bottom: 5px">
                                            <span class="input-group-addon button"  ><i class="fa fa-plus" style="  cursor : pointer;"></i></span>
                                            <input type="text" class="form-control text-center"  placeholder="Button Size"  name="ButtonSize"/>
                                            <span class="input-group-addon button"  ><i class="fa fa-minus" style="  cursor : pointer;"></i></span>
                                        </div>
                                        <label> Font Size</label>
                                        <div class="input-group " style="margin-bottom: 5px">
                                            <span class="input-group-addon font"  ><i class="fa fa-plus" style="  cursor : pointer;"></i></span>
                                            <input type="text" class="form-control text-center"  placeholder="Font Size"  name="FontSize"/>
                                            <span class="input-group-addon font"  ><i class="fa fa-minus" style="  cursor : pointer;"></i></span>
                                        </div>
                                        <div class="input-group background" style="margin-bottom: 5px">
                                            <span class="input-group-addon " style="width: 50px;">Background Color</span>
                                            <span class="input-group-addon picker" onclick="getPicker()" data-color="bg"></span>
                                        </div>

                                        <div class="input-group fontcolor" style="margin-bottom: 5px" >
                                            <span class="input-group-addon" style="width: 50px;">Font Color</span>
                                            <span class="input-group-addon picker"  onclick="getPicker()" data-color="font"></span>
                                           
                                        </div>
                                        <div class="text text-right">
                                            <a href="javascript:void(0);" class="btn btn-xs btn-default confirm">Ok</a>
                                        </div>
                                    </div>                                    
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="text text-right" style="margin-top:10px">
                        <a href="#" id="saveElement" class="btn btn-info">Done</a>
                    </div>
                </div>
                <!-- END SETTINGS -->

            </div>
            <!--/span-->

            <a href="javascript:void(0);" class="btn btn-info btn-xs" id="edittamplate">Edit background</a>
            <div id="tosave" data-id=""  data-paramone="11" data-paramtwo="22" data-paramthree="33" data-file-url="{{ $emailTemplate->file_url?? ''}}" class="content-right">
              @if(request()->routeIs('admin.emailtemplates.edit'))
                {!! $emailTemplate->content !!}
              @else
                <!-- inizio parte html da salvare -->
               <table  width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" style="background: #fff; height:100%;" >
                   <tr>
                       <td width="100%" id="primary" class="main demo" align="center" valign="top" >
                            <div class="column">
                                <!-- default element text -->
                               <div class="lyrow demo-section">
                                   <a class="remove label label-danger"><i class="glyphicon-remove glyphicon"></i></a>
                                   <span class="drag label label-default"><i class="glyphicon glyphicon-move"></i></span>
                                   <div class="view">
 
                                        <div class="row clearfix">
                                           <table width="640" class="main" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" align="center" data-type='text-block' style="background-color: #FFFFFF;">
                                               <tbody>
                                                   <tr>
                                                       <td  class="block-text" align="left" style="padding:10px 50px 10px 50px;font-family: Poppins;font-size:13px;color:#000000;line-height:22px">
                                                           <p style="margin:0px 0px 10px 0px;line-height:22px">
                                                               <center>
                                                                   <i class="fa fa-arrow-up fa-3x"></i> <br><br>
                                                                Modify me or drag the content of email in top or bottom <br><br>
                                                               <i class="fa fa-arrow-down fa-3x"></i>
                                                               </center>
                                                           </p>
 
                                                        </td>
                                                   </tr>
                                               </tbody>
                                           </table>
                                       </div>
                                   </div>
                               </div>

                            </div>
                        </td>
                   </tr>
               </table>
              @endif
            </div>
            <div id="download-layout">

            </div>
        </div>

        <div class="modal fade" id="html5editorPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-body">
                <form id="editor" style="margin-top:5px">
                    <div class="panel panel-body panel-default html5editor" id="html5editor"></div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editsave">Save changes</button>
              </div>
            </div>
          </div>
        </div>
        <!--/row-->
        <!-- Button trigger modal -->

        <!-- Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="min-width:120px">
                    <div class="modal-header">
                        <input id="httphref" type="text" name="href" value="" data-url="{{ url('storage/email_templates') }}" class="form-control" />
                    </div>
                    <div class="modal-body" align="center">
                        <div class="btn-group  previewActions">
                            <a class="btn btn-default btn-sm active" href="#">iphone</a>
                            <a class="btn btn-default btn-sm " href="#">smalltablet</a>
                            <a class="btn btn-default btn-sm " href="#">ipad</a>
                        </div>
                        <iframe id="previewFrame"  class="iphone"></iframe>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <textarea id="imageid" class="hide"></textarea>
        <textarea id="download" class="hide"></textarea>        
        <textarea id="selector" class="hide"></textarea>        
        <textarea id="selector2" class="hide"></textarea>        
        <textarea  id="path" class="hide"></textarea>

        <div class="modal fade" id="previewimg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="min-width:120px">
                    <div class="modal-header">
                        Image Gallery
                    </div>
                    <div class="modal-body" align="center">
                        <div id="contenutoimmagini"></div>
                        <!-- <form enctype="multipart/form-data" id="form-id"> -->                           
                           <!--  <input name="image_url" type="file" id="imagefile" />
                            <input class="button" type="button" value="Upload" /> -->
                        <!-- </form>
                        <progress value="0"></progress> -->
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="tagPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">              
              <div class="modal-body">
                <div class="tags-main">
                    <h3 class="p-b-10 p-l-10">Recipients Tags</h3>
                    <ul class="tags-ul">
                        @foreach(trans('emailtemplate::attributes.tags') as $key => $tag)
                            <li class="tags-li">
                                <input class="copy-input" value="{{$tag}}" readonly>
                                <i class="fa fa-clone copyclipboard"></i>
                            </li>
                        @endforeach
                    </ul>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
              <div id="copied-success" class="copied">
                <span>Copied to clipboard!</span>
              </div>
            </div>
          </div>
        </div>
    </section>

    </form>
@endsection

@push('globals')
    @vite([        
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js'
    ])
@endpush


@push('scripts')
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="{{ Storage::url('css/custom.css') }}">
<link rel="stylesheet" href="{{ Storage::url('css/colpick.css') }}">
<link rel="stylesheet" href="{{ Storage::url('css/responsive-table.css') }}">
<link rel="stylesheet" href="{{ Storage::url('css/template.editor.css') }}">
<link rel="stylesheet" href="{{ Storage::url('css/default.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<script src="{{ Storage::url('js/template.editor.js') }}"></script>
<script src="{{ Storage::url('js/colpick.js') }}"></script>
<script src="{{ Storage::url('js/ResizeSensor.js') }}"></script>
<script src="{{ Storage::url('js/theia-sticky-sidebar.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/w9g3nh77fwgfyed8mc1fw9p4lljbh58rsyeah7funhrdxtw1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript">

$('body').on('click', '.slide-image', (e) => {
    let picker = new MediaPicker();
    picker.on('select', (file) => {
        let imgeUrl = `<a onclick="inserisci(this);" class="insert-image" data-image="${file.path}">`;
        inserisci(imgeUrl);
    });
});

//tag copyText
$(".tags-li").click(function () {
    let copyText = $(this).find(".copy-input");
    let copySuccess = document.getElementById("copied-success");
    copyText.select();
    navigator.clipboard.writeText(copyText.val());

    copySuccess.style.opacity = "1";
    setTimeout(function(){ copySuccess.style.opacity = "0" }, 500);
});
$('#schedule').on('change', function () {
    var option = $(this).find('option:selected');
    if(option.val() == "future")
    {
        $('.schedule-panel').show();
    } else {
        $('.date').val('');
        $('.time').val('');
        $('.schedule-panel').hide();
    }
});

$('#schedule').trigger('change');


/*Text color picker call*/
function getColorText() {
    $('#colortext').colpick({
        layout: 'hex',
        onChange: function (hsb, hex, rgb, el, bySetColor) {
            if (!bySetColor)
            $(el).val('#' + hex);
        },
        onSubmit: function (hsb, hex, rgb, el) {
            $(el).next('.input-group-addon').css('background-color', '#' + hex);
            $(el).colpickHide();
        }

    }).keyup(function () {
        $(this).colpickSetColor(this.value);
    });
}
// Color picker call
function getPicker() {    
    $('.picker').colpick({
        layout: 'hex',
        onChange: function (hsb, hex, rgb, el, bySetColor) {
            if (!bySetColor)
                $(el).css('background-color', '#' + hex);
                
            var color = $(el).data('color');            
            var indexBnt = getIndex($(el).parent().parent().parent().parent().parent(), $('#buttonslist li')) - 1;
            if (color === 'bg') {                
                $($('#' + $('#path').val()).find('table tbody tr td a.url')).css('background-color', '#' + hex);
                $(el).parent().parent().parent().parent().find('div.color-circle').css('background-color', '#' + hex);
                //fix td in email
                //$($('#' + $('#path').val()).find('table tbody tr td a.url').parent('td')).css('background-color', '#' + hex);
            } else {
                $($('#' + $('#path').val()).find('table tbody tr td a.url')).css('color', '#' + hex);
                $(el).parent().parent().parent().parent().find('div.color-circle').css('color', '#' + hex);
            }
        },
        onSubmit: function (hsb, hex, rgb, el) {
            $(el).css('background-color', '#' + hex);
            $(el).colpickHide();
            var color = $(el).data('color');
            var indexBnt = getIndex($(el).parent().parent().parent().parent().parent(), $('#buttonslist li')) - 1;
            if (color === 'bg') {
                $($('#' + $('#path').val()).find('table tbody tr td a.url')).css('background-color', '#' + hex);
            } else {
                $($('#' + $('#path').val()).find('table tbody tr td a.url')).css('color', '#' + hex);
            }
        }
    }).keyup(function () {
        $(this).colpickSetColor(this.value);
    });
}      

$(document).ready(function(){
    
    $('#bgcolor').colpick({
        layout: 'hex',
        onBeforeShow: function () {
            $(this).colpickSetColor($('#bgcolor').css('backgroundColor').replace('#', ''));
        },
        onChange: function (hsb, hex, rgb, el, bySetColor) {

            if (!bySetColor)
                $(el).css('background-color', '#' + hex);
        },
        onSubmit: function (hsb, hex, rgb, el) {
            $(el).css('background-color', '#' + hex);

            $('#' + $('#path').val()).css('background-color', '#' + hex);
            $(el).colpickHide();
        }

    }).keyup(function () {
        $(this).colpickSetColor(this.value);
    });
    loadimages();

    $('.side-panel-heading').on('click',function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).siblings('.side-panel-body').removeClass('active');
        } else{
            $(this).addClass('active');
            $(this).siblings('.side-panel-body').addClass('active');
        }
        $(this).siblings('.side-panel-body').slideToggle(300);
    });

    $('.sub-side-panel-heading').on('click',function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).siblings('.sub-side-panel-body').removeClass('active');
        } else{
            $(this).addClass('active');
            $(this).siblings('.sub-side-panel-body').addClass('active');
        }
        $(this).siblings('.sub-side-panel-body').slideToggle(300);
    });

    $('.dropdown-toggle').on('click',function(){
        $(this).parent().toggleClass('open');
    });
});

function inserisci(elemento){
    var link = $(elemento);
    var image = link.data('image');

    $('#image-url').val(image);
    var id= $('#image-url').data('id');
    if ($('#'+id).css('background-image') != 'none') {
        $('#'+id).css('background-image',"url('"+image+"')");
        $('#'+id).attr('background', $('#image-url').val()) ;        
    }
    $('#'+id).attr('src', $('#image-url').val()) ;
    $('#'+id).attr('width', $('#image-w').val()) ;
    $('#'+id).attr('height', $('#image-h').val()) ;

    $('#previewimg').modal('hide');
}

function loadimages(){

}
</script>
@endpush

