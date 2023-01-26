@extends('layouts.app')

@section('title'){{trans('general.live_streaming')}} {{trans('general.by')}} {{ '@'.$creator->username }} -@endsection

  @section('css')
    <script type="text/javascript">
        var liveOnline = {{ $live ? 'true' : 'false' }};
        @if ($live)
          var appIdAgora = '{{ $settings->agora_app_id }}'; // set app id
          var agorachannelName = '{{ $live->channel }}'; // set channel name
          var liveMode = true;
          var liveCreator = {{ $creator->id == auth()->id() ? 'true' : 'false' }};
          var role = "{{ $creator->id == auth()->id() ? 'host' : 'audience' }}";
          var availability = '{{ $live->availability }}';
          var textMuteAudio = "{{ __('general.mute_audio') }}";
          var textUnmuteAudio = "{{ __('general.unmute_audio') }}";
          var textMuteVideo = "{{ __('general.mute_video') }}";
          var textUnmuteVideo = "{{ __('general.unmute_video') }}";
        @endif
    </script>

    @if ($live)
      <script src="{{ asset('public/js/agora/AgoraRTCSDK-v4.js') }}"></script>
    @endif
  @endsection

@section('content')
<section class="section section-sm pt-0 pb-0 h-100 section-msg position-fixed live-data" @if ($live) data="{{ $live->id}}" data-creator="{{ $creator->id}}" @endif>
      <div class="container mw-100 h-100">
        <div class="row justify-content-center h-100 position-relative">

          <div class="col-md-9 h-100 p-0 liveContainerFullScreen" @if ($live) data-id="{{ $live->id }}" @endif>
            <div class="card w-100 rounded-0 h-100 border-0 liveContainer @if (! $live) live_offline @endif" @if (! $live) style="background:url('{{Helper::getFile(config('path.avatar').$creator->avatar)}}') no-repeat center center; background-size: cover;" @endif>

              <div class="content @if (! $live) px-4 py-3 @endif d-scrollbars container-msg">
                @if (! $live)
                  <div class="flex-column d-flex justify-content-center text-center h-100 text-content-live">
                    <div class="w-100">

                      @if (! $live && $creator->id == auth()->id())
                        <h2 class="mb-0 font-montserrat"><i class="bi bi-broadcast mr-2"></i> {{trans('general.stream_live')}}</h2>
                        <p class="lead mt-0">{{trans('general.create_live_stream_subtitle')}}</p>
                        <button class="btn btn-primary btn-sm w-small-100 btnCreateLive">
                          <i class="bi bi-plus-lg mr-1"></i> {{trans('general.create_live_stream')}}
                        </button>

                        <div class="mt-3 d-block ">
                          <a href="{{ url('/') }}" class="text-white"><i class="bi bi-arrow-left"></i> {{ trans('error.go_home') }}</a>
                        </div>

                      @elseif (! $live && $creator->id != auth()->id())

                        <h2 class="mb-0 font-montserrat"><i class="bi bi-broadcast mr-2"></i> {{trans('general.welcome_live_room')}}</h2>
                        @if ($checkSubscription)
                          <p class="lead mt-0">{{trans('general.info_offline_live')}}</p>

                          <div class="mt-3 d-block ">
                            <a href="{{ url('/') }}" class="text-white"><i class="bi bi-arrow-left"></i> {{ trans('error.go_home') }}</a>
                          </div>

                        @else
                          <p class="lead mt-0">{{trans('general.info_offline_live_non_subscribe')}}</p>
                          <a href="{{url($creator->username)}}" class="btn btn-primary btn-sm w-small-100">
                            <i class="feather icon-unlock mr-1"></i> {{trans('general.subscribe_now')}}
                          </a>

                          <div class="mt-3 d-block ">
                            <a href="{{ url('/') }}" class="text-white"><i class="bi bi-arrow-left"></i> {{ trans('error.go_home') }}</a>
                          </div>

                        @endif

                      @endif
                    </div>
                  </div><!-- flex-column -->
                @else

                  <div class="live-top-menu">
                  	<div class="w-100">
                      <img src="{{Helper::getFile(config('path.avatar').$creator->avatar)}}" class="rounded-circle avatar-live mr-2" width="40" height="40">
                  		<span class="font-weight-bold text-white text-shadow-sm d-lg-inline-block d-none">{{ $creator->username }}</span>
                      <span class="font-weight-bold text-white text-shadow-sm d-lg-none d-inline-block">{{ str_limit($creator->username, 7, '...') }}</span>

                      @if ($live && ! $paymentRequiredToAccess && $limitLiveStreaming)
                        <small class="text-white text-shadow-sm limitLiveStreaming">
                          <i class="bi bi-clock mr-1"></i> <span>{{ $limitLiveStreaming }}</span> {{ trans('general.minutes') }}
                        </small>
                      @endif


                      <div class="float-right">
                        <span class="live text-uppercase mr-2">{{ trans('general.live') }}</span>
                        <span class="live-views text-uppercase mr-2">
                          <i class="bi bi-eye mr-2"></i> <span id="liveViews">{{ $live->onlineUsers->count() }}</span>
                        </span>

                        @if ($creator->id != auth()->id())
                          <button class="live-views text-uppercase mr-2" id="liveAudio">
                            <i class="fas fa-volume-mute"></i>
                          </button>
                        @endif

                        @if ($creator->id == auth()->id())
                          <span class="live-options text-shadow-sm mr-2" id="optionsLive" role="button" data-toggle="dropdown">
                            <i class="bi bi-gear"></i>
                          </span>

                          <div class="dropdown-menu dropdown-menu-right menu-options-live mb-1" aria-labelledby="optionsLive">
                            <div id="mute-audio">
                              <a class="dropdown-item"><i class="bi-mic-mute mr-1"></i> {{ __('general.mute_audio') }}</a>
                            </div>
                            <div id="mute-video">
                              <a class="dropdown-item"><i class="bi-camera-video-off mr-1"></i> {{ __('general.mute_video') }}</a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div id="camera-list"></div>
                            <div id="mic-list"></div>
                          </div>

                          <form method="POST" action="{{ url('end/live/stream', $live->id) }}" accept-charset="UTF-8" class="d-none" id="formEndLive">
                            @csrf
                            </form>
                        <span class="close-live text-shadow-sm" id="endLive" data-toggle="tooltip" data-placement="top" title="{{ trans('general.end_live') }}">
                          <i class="bi bi-x-lg"></i>
                        </span>

                      @else
                        <a href="javascript:void(0);" class="exit-live text-shadow-sm" id="exitLive" data-toggle="tooltip" data-placement="top" title="{{ trans('general.exit_live_stream') }}">
                          <i class="bi bi-x-lg"></i>
                        </a>
                      @endif

                      </div>
                    </div>
                  </div>

                  <div id="full-screen-video"></div>

                @endif

              </div><!-- container-msg -->

              </div><!-- card -->
            </div><!-- end col-md-8 -->

          <!-- Chat Box -->
          <div class="col-md-3 h-100 p-0 border-right wrapper-msg-inbox wrapper-live-chat">

          <div class="card w-100 rounded-0 h-100 border-0">

            <div class="w-100 p-3 border-bottom titleChat">
            	<div class="w-100">
            		<span class="h5 align-top font-weight-bold">{{ trans('general.chat') }}</span>
              </div>
            </div>

            <div class="content px-4 py-3 d-scrollbars container-msg chat-msg" id="contentDIV">

              <div class="div-flex"></div>

              @if ($live && ! $paymentRequiredToAccess)
              <ul class="list-unstyled mb-0" id="allComments">
                @include('includes.comments-live')
              </ul>
              @endif


            </div>

        <div class="card-footer bg-transparent position-relative @if (! $live) offline-live @endif">

            <!-- Alert -->
            <div class="alert alert-danger my-3 display-none" id="errorMsg">
             <ul class="list-unstyled m-0" id="showErrorMsg"></ul>
           </div><!-- Alert -->

            <form action="{{ url('comment/live') }}" method="post" accept-charset="UTF-8" id="formSendCommentLive" enctype="multipart/form-data">

              @if ($live)
                <input type="hidden" name="live_id" value="{{ $live->id }}">
              @endif

              @csrf

                  <div class="d-flex">

                    <div class="w-100 h-100 position-relative">
                      <div class="live-blocked rounded-pill blocked @if ($live && ! $paymentRequiredToAccess) display-none @endif"></div>
                      <input type="text" class="form-control border-0 emojiArea" id="commentLive" placeholder="{{ trans('general.write_something') }}" name="comment" />
                    </div>

                    @if (! $paymentRequiredToAccess)
                      <div class="dropdown-menu dropdown-menu-right dropdown-emoji custom-scrollbar" aria-labelledby="dropdownEmoji">
                        @include('includes.emojis')
                      </div>
                    @endif

                    @if ($creator->id != auth()->id())
                      @if ($live && ! $paymentRequiredToAccess)
                      <button type="button" class="btn btn-upload btn-tooltip e-none align-bottom buttons-live @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="modal" data-target="#tipForm" title="{{trans('general.tip')}}" data-cover="{{Helper::getFile(config('path.cover').$creator->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$creator->avatar)}}" data-name="{{$creator->hide_name == 'yes' ? $creator->username : $creator->name}}" data-userid="{{$creator->id}}">
                        <i class="feather icon-dollar-sign f-size-25"></i><h6 class="d-inline font-weight-lighter">Gorjeta</h6>
                      </button>
                      @endif
                    @endif

                    @if (! $paymentRequiredToAccess)
                    <span class="btn btn-upload e-none align-bottom buttons-live {{ $likeActive ? 'active' : null }} button-like-live @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill">
                      <i class="bi f-size-25 bi-heart{{ $likeActive ? '-fill' : null }}"></i>
                    </span>

                    <div class="py-3">
                      <small id="counterLiveLikes">
                        @if ($live && $likes != 0)
                          {{ $likes }}
                        @endif
                      </small>
                    </div>
                    @endif

                  </div><!-- justify-content-between -->
                </form>
              </div>

            </div><!-- end card -->

          </div><!-- end col-md-3 -->

          </div><!-- end row -->
        </div><!-- end container -->
</section>

@if ($live && $paymentRequiredToAccess)
  @include('includes.modal-pay-live')
@endif

@endsection

@section('javascript')

  @if ($live && $paymentRequiredToAccess)
    <script>
    // Payment Required
  		$('#payLiveForm').modal({
  				 backdrop: 'static',
  				 keyboard: false,
  				 show: true
  		 });

       //<---------------- Pay Live ----------->>>>
 			 $(document).on('click','#payLiveBtn',function(s) {

 				 s.preventDefault();
 				 var element = $(this);
 				 element.attr({'disabled' : 'true'});
 				 element.find('i').addClass('spinner-border spinner-border-sm align-middle mr-1');

 				 (function(){
 						$('#formPayLive').ajaxForm({
 						dataType : 'json',
 						success:  function(result) {

 							if (result.success) {
 								window.location.reload();
 							} else {

 								if (result.errors) {

 									var error = '';
 									var $key = '';

 									for ($key in result.errors) {
 										error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
 									}

 									$('#showErrorsPayLive').html(error);
 									$('#errorPayLive').show();
 									element.removeAttr('disabled');
 									element.find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
 								}
 							}

 						 },
 						 error: function(responseText, statusText, xhr, $form) {
 								 // error
 								 element.removeAttr('disabled');
 								 element.find('i').removeClass('spinner-border spinner-border-sm align-middle mr-1');
 								 swal({
 										 type: 'error',
 										 title: error_oops,
 										 text: error_occurred+' ('+xhr+')',
 									 });
 						 }
 					 }).submit();
 				 })(); //<--- FUNCTION %
 			 });//<<<-------- * END FUNCTION CLICK * ---->>>>
    </script>
  @endif

  @if ($live && ! $paymentRequiredToAccess)
    <script src="{{ asset('public/js/live.js') }}?v={{$settings->version}}"></script>
    <script src="{{ asset('public/js/agora/agora-broadcast-client-v4.js') }}?v={{$settings->version}}"></script>

    @if ($creator->id == auth()->id() || ! $paymentRequiredToAccess)
      <script>
      // Start Live
      $(document).ready(async function() {
        await joinChannel();
      });
    	</script>
      @endif

  @endif
@endsection
