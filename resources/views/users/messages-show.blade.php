@extends('layouts.app')

@section('title'){{trans('general.messages')}} -@endsection

@section('css')
  <script type="text/javascript">
      var subscribed_active = {{ $subscribedToYourContent || $subscribedToMyContent || auth()->user()->isSuperAdmin() || $user->isSuperAdmin() ? 'true' : 'false' }};
      var user_id_chat = {{ $user->id }};
      var msg_count_chat = {{ $allMessages }};
  </script>
@endsection

@section('content')
<section class="section section-sm pb-0 h-100 section-msg position-fixed">
    <div class="container h-100">
      <div class="row justify-content-center h-100">

        <div class="col-md-4 h-100 p-0 border-left second wrapper-msg-inbox" id="messagesContainer">
          @include('includes.sidebar-messages-inbox')
        </div>

  <div class="col-md-8 h-100 p-0 first">

  <div class="card w-100 rounded-0 h-100 border-top">
    <div class="card-header bg-white pt-4">
      <div class="media">
        <a href="{{url()->previous()}}" class="mr-3"><i class="fa fa-arrow-left"></i></a>
        <a href="{{url($user->username)}}" class="mr-3">
          <span class="position-relative user-status @if ($user->active_status_online == 'yes') @if (Cache::has('is-online-' . $user->id)) user-online @else user-offline @endif @endif d-block">
            <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" class="rounded-circle" width="40" height="40">
          </span>
      </a>

        <div class="media-body">
          <h6 class="m-0">
            <a style="color: #495057!important;" href="{{url($user->username)}}">
              {{$user->hide_name == 'yes' ? $user->username : $user->name}}
            </a>

            @if ($user->verified_id == 'yes')
              <small class="verified">
                   <i class="bi bi-patch-check-fill"></i>
                 </small>
            @endif
          </h6>

        @if ($user->active_status_online == 'yes')

          @if ($user->hide_last_seen == 'no')
           <small>{{ trans('general.active') }}</small>

           <span id="timeAgo">
             <small class="timeAgo @if (Cache::has('is-online-' . $user->id)) display-none @endif" id="lastSeen" data="{{ date('c', strtotime($user->last_seen ?? $user->date)) }}"></small>
            </span>
          @else
            {{'@'.$user->username}}
            @endif

          @else
            {{'@'.$user->username}}
            @endif

        </div>


        <a href="javascript:void(0);" class="text-muted float-right" id="dropdown_options" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<i class="fa fa-ellipsis-h"></i>
				</a>

        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_options">

        @if ($messages->count() != 0)
					{!! Form::open([
						'method' => 'POST',
						'url' => "conversation/delete/$user->id",
						'class' => 'd-inline'
					]) !!}

					{!! Form::button('<i class="feather icon-trash-2 mr-2"></i> '.trans('general.delete'), ['class' => 'dropdown-item actionDelete']) !!}
					{!! Form::close() !!}

          @endif

          @if (auth()->user()->isRestricted($user->id))
            <button type="button" class="dropdown-item removeRestriction" data-user="{{$user->id}}" id="restrictUser">
              <i class="fas fa-ban mr-2"></i> {{trans('general.remove_restriction')}}
            </button>

          @else
            <button type="button" class="dropdown-item" data-user="{{$user->id}}" id="restrictUser">
              <i class="fas fa-ban mr-2"></i> {{trans('general.restrict')}}
            </button>
          @endif
	      </div>

      </div>

    </div>

    <div class="content px-4 py-3 d-scrollbars container-msg" id="contentDIV" data="{{$user->id}}">

      @if ($allMessages != 0)
      <div class="flex-column d-flex justify-content-center text-center h-100">
        <div class="w-100" id="loadAjaxChat">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    @endif
      </div><!-- contentDIV -->

      @if (! auth()->user()->checkRestriction($user->id))
          <div class="card-footer bg-white position-relative">

          @if ($subscribedToYourContent || $subscribedToMyContent || auth()->user()->isSuperAdmin() || $user->isSuperAdmin())

            <div class="w-100 display-none" id="previewFile">
              <div class="previewFile d-inline"></div>
              <a href="javascript:;" class="text-danger" id="removeFile"><i class="fa fa-times-circle"></i></a>
            </div>

            <div class="progress-upload-cover" style="width: 0%; top:0;"></div>

            <div class="blocked display-none"></div>

            <!-- Alert -->
            <div class="alert alert-danger my-3" id="errorMsg" style="display: none;">
             <ul class="list-unstyled m-0" id="showErrorMsg"></ul>
           </div><!-- Alert -->

            <form action="{{url('message/send')}}" method="post" accept-charset="UTF-8" id="formSendMsg" enctype="multipart/form-data">
              <input type="hidden" name="id_user" id="id_user" value="{{$user->id}}">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="file" name="zip" id="zipFile" accept="application/x-zip-compressed" class="visibility-hidden">

              <div class="w-100 mr-2 position-relative">
                <div>
                <span class="triggerEmoji" data-toggle="dropdown">
                  <i class="bi-emoji-smile"></i>
                </span>

                <div class="dropdown-menu dropdown-menu-right dropdown-emoji custom-scrollbar" aria-labelledby="dropdownMenuButton">
                  @include('includes.emojis')
                </div>
              </div>
                <textarea class="form-control textareaAutoSize emojiArea border-0" data-post-length="{{$settings->update_length}}" rows="1" placeholder="{{trans('general.write_something')}}" id="message" name="message"></textarea>
              </div>

              <div class="form-group display-none mt-2" id="price">
                <div class="input-group mb-2">
                <div class="input-group-prepend">
                  <span class="input-group-text">{{$settings->currency_symbol}}</span>
                </div>
                    <input class="form-control isNumber" autocomplete="off" name="price" placeholder="{{trans('general.price')}}" type="text">
                </div>
              </div><!-- End form-group -->

              <div class="w-100">
                <span id="previewImage"></span>
                <a href="javascript:void(0)" id="removePhoto" class="text-danger p-1 px-2 display-none btn-tooltip" data-toggle="tooltip" data-placement="top" title="{{trans('general.delete')}}"><i class="fa fa-times-circle"></i></a>
              </div>

              <input type="file" name="media[]" id="file" accept="image/*,video/mp4,video/x-m4v,video/quicktime,audio/mp3" multiple class="visibility-hidden filepond">

              <div class="justify-content-between align-items-center">

                    <button type="button" class="btnMultipleUpload btn btn-upload btn-tooltip e-none align-bottom @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="tooltip" data-placement="top" title="{{trans('general.upload_media')}} ({{ trans('general.media_type_upload') }})">
                      <i class="feather icon-image f-size-25"></i>
                    </button>
                    
                     @if (auth()->user()->verified_id == 'yes')
                  <button type="button" id="setPrice" class="btn btn-upload btn-tooltip e-none align-bottom @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="tooltip" data-placement="top" title="{{trans('general.set_price_for_msg')}}">
                    <i class="feather icon-dollar-sign f-size-25 align-bottom"></i> <h6 class="d-inline font-weight-lighter">Preço</h6>
                  </button>
                @endif

                @if ($user->verified_id == 'yes' && $settings->disable_tips == 'off')
                  <button type="button" class="btn btn-upload btn-tooltip e-none align-bottom @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="modal" title="{{trans('general.tip')}}" data-target="#tipForm" data-cover="{{Helper::getFile(config('path.cover').$user->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$user->avatar)}}" data-name="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" data-userid="{{$user->id}}">
                    <i class="feather icon-dollar-sign f-size-25 align-bottom"></i> <h6 class="d-inline font-weight-lighter">Gorjeta</h6>
                  </button>
                @endif

          <div class="d-inline-block float-right rounded-pill mt-3 position-relative">
            <div class="btn-blocked display-none"></div>
            <button type="submit" id="button-reply-msg" disabled data-send="{{ trans('auth.send') }}" data-wait="{{ trans('general.send_wait') }}" class="btn btn-sm btn-primary rounded-pill float-right e-none">Enviar</button>
            </div>

          </div><!-- media -->
        </form>
      @else
        <div class="alert alert-primary m-0 alert-dismissible fade show" role="alert">
          @php
            $nameUser = $user->hide_name == 'yes' ? $user->username : $user->first_name;
          @endphp
        {!! trans('general.show_form_msg_error_subscription_', ['user' => '<a href="'.url($user->username).'" class="link-border text-white">'.$nameUser.'</a>']) !!}
      </div>
        @endif

      </div><!-- card footer -->
    @endif

    </div><!-- card -->
  </div><!-- end col-md-8 -->

  </div><!-- end row -->
</div>
<!-- end container -->
</section>

@include('includes.modal-new-message')
@endsection

@section('javascript')
<script src="{{ asset('public/js/messages.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/fileuploader/fileuploader-msg.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/paginator-messages.js') }}"></script>
@endsection
