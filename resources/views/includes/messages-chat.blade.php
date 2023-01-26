@if ($allMessages > 10 && $counter >= 1)
<div class="btn-block text-center wrap-container" data-total="{{ $allMessages }}" data-id="{{ $user->id }}">
  <a href="javascript:void(0)" class="loadMoreMessages d-none" id="paginatorChat">
    — {{ trans('general.load_messages') }}
    (<span class="counter">{{$counter}}</span>)
  </a>
</div>
@endif

@foreach ($messages as $msg)

  @php

  $checkPayPerView = auth()->user()->payPerViewMessages()->where('messages_id', $msg->id)->first();

  $mediaCount = $msg->media()->count();

  $allFiles = $msg->media()->groupBy('type')->get();

  $getFirstFile = $allFiles->where('type', '<>', 'music')->where('type', '<>', 'file')->where('video_embed', '')->first();

  if ($getFirstFile && $getFirstFile->type == 'image') {
    $urlMedia =  url('media/storage/focus/message', $getFirstFile->id);
    $backgroundPostLocked = 'background: url('.$urlMedia.') no-repeat center center #b9b9b9; background-size: cover;';
    $textWhite = 'text-white';

  } elseif ($getFirstFile && $getFirstFile->type == 'video' && $getFirstFile->video_poster) {
      $videoPoster = url('media/storage/focus/message', $getFirstFile->id);
      $backgroundPostLocked = 'background: url('.$videoPoster.') no-repeat center center #b9b9b9; background-size: cover;';
      $textWhite = 'text-white';

  } else {
    $backgroundPostLocked = null;
    $textWhite = null;
  }

  $countFilesImage = $msg->media()->whereType('image')->groupBy('type')->count();
  $countFilesVideo = $msg->media()->whereType('video')->groupBy('type')->count();
  $countFilesAudio = $msg->media()->whereType('music')->groupBy('type')->count();

  $mediaImageVideo = $msg->media()
      ->where('type', 'image')
      ->orWhere('messages_id', $msg->id)
      ->where('type', 'video')
      ->get();

  $mediaImageVideoTotal = $mediaImageVideo->count();

  if ($msg->from_user_id  == Auth::user()->id) {
     $avatar   = $msg->to()->avatar;
     $name     = $msg->to()->name;
     $userID   = $msg->to()->id;
     $username = $msg->to()->username;

  } else if ($msg->to_user_id  == Auth::user()->id) {
     $avatar   = $msg->from()->avatar;
     $name     = $msg->from()->name;
     $userID   = $msg->from()->id;
     $username = $msg->from()->username;
  }

  $chatMessage = $msg->message ? Helper::linkText(Helper::checkText($msg->message)) : null;

  $classInvisible = ! request()->ajax() ? 'invisible' : null;
  $nth = 0; // nth foreach nth-child(3n-1)

@endphp

@if ($msg->from()->id == auth()->user()->id)
<div data="{{$msg->id}}" class="media py-2 chatlist">
<div class="media-body position-relative">
  @if ($msg->tip == 'no')
  <a href="javascript:void(0);" class="btn-removeMsg removeMsg" data="{{$msg->id}}" title="{{trans('general.delete')}}">
    <i class="fa fa-trash-alt"></i>
    </a>
  @endif

  <div class="@if ($mediaCount == 0) float-right @else wrapper-msg-left @endif message position-relative text-word-break @if ($mediaCount == 0 && $msg->tip == 'no') bg-primary @else media-container @endif text-white @if ($msg->format == 'zip') w-50 @else w-auto @endif  rounded-bottom-right-0">
      @include('includes.media-messages')
  </div>

  @if ($mediaCount != 0 && $msg->message != '')
    <div class="w-100 d-inline-block">
      <div class="w-auto position-relative text-word-break message bg-primary float-right text-white rounded-top-right-0">
        {!! $chatMessage !!}
      </div>
    </div>
@endif

    <span class="w-100 d-block text-muted float-right text-right pr-1 small">

      @if ($msg->price != 0.00)
        {{ Helper::amountFormatDecimal($msg->price) }} <i class="feather icon-lock mr-1"></i> -
      @endif

      <span class="timeAgo" data="{{ date('c', strtotime($msg->created_at)) }}"></span>
    </span>
</div><!-- media-body -->

<a href="{{url($msg->from()->username)}}" class="align-self-end ml-3 d-none">
  <img src="{{Helper::getFile(config('path.avatar').$msg->from()->avatar)}}" class="rounded-circle" width="50" height="50">
</a>
</div><!-- media -->


@else
<div data="{{$msg->id}}" class="media py-2 chatlist">
<a href="{{url($msg->from()->username)}}" class="align-self-end mr-3">
  <img src="{{Helper::getFile(config('path.avatar').$msg->from()->avatar)}}" class="rounded-circle avatar-chat" width="50" height="50">
</a>

<div class="media-body position-relative">

  @if ($msg->price != 0.00 && ! $checkPayPerView)

    <div class="btn-block p-sm text-center content-locked mb-2 pt-lg pb-lg px-3 {{$textWhite}} custom-rounded float-left" style="{{$backgroundPostLocked}}">
    		<span class="btn-block text-center mb-3">
          <i class="feather ico-no-result border-0 icon-lock {{$textWhite}}"></i></span>
        <a href="javascript:void(0);" data-toggle="modal" data-target="#payPerViewForm" data-mediaid="{{$msg->id}}" data-price="{{Helper::amountFormatDecimal($msg->price, true)}}" data-subtotalprice="{{Helper::amountFormatDecimal($msg->price)}}" data-pricegross="{{$msg->price}}" class="btn btn-primary w-100">
          <i class="feather icon-unlock mr-1"></i> {{ trans('general.unlock_for') }} {{Helper::amountFormatDecimal($msg->price)}}
        </a>


    <ul class="list-inline mt-3">
          @if ($mediaCount == 0)
      			<li class="list-inline-item"><i class="bi bi-file-font"></i> {{ __('admin.text') }}</li>
      		@endif

        @foreach ($allFiles as $media)

          @if ($media->type == 'image')
      			<li class="list-inline-item"><i class="feather icon-image"></i> {{$countFilesImage}}</li>
      		@endif

      		@if ($media->type == 'video')
      			<li class="list-inline-item"><i class="feather icon-video"></i> {{$countFilesVideo}} @if ($media->duration_video && $countFilesVideo == 1 || $media->quality_video && $countFilesVideo == 1) <small><span class="quality-video">{{ $media->quality_video }}</span> {{ $media->duration_video }}</small> @endif</li>
      		@endif

      		@if ($media->type == 'music')
      			<li class="list-inline-item"><i class="feather icon-mic"></i> {{$countFilesAudio}}</li>
      			@endif

      			@if ($media->type == 'file')
      			<li class="list-inline-item"><i class="far fa-file-archive"></i> {{$media->file_size}}</li>
      		@endif

          @endforeach
        </ul>

      </div><!-- btn-block parent -->
    @endif

@if ($msg->price == 0.00 || $msg->price != 0.00 && $checkPayPerView)
  <div class="@if ($mediaCount == 0) float-left @else wrapper-msg-right @endif message position-relative text-word-break @if ($mediaCount == 0 && $msg->tip == 'no') bg-light @else media-container @endif @if ($msg->format == 'zip') w-50 @else w-auto @endif rounded-bottom-left-0">
        @include('includes.media-messages')
  </div>
  @endif

  @if ($mediaCount != 0 && $msg->message != '')
    <div class="w-100 d-inline-block">
      <div class="w-auto position-relative text-word-break message bg-light float-left rounded-top-left-0">
        {!! $chatMessage !!}
      </div>
  </div>
@endif

<span class="w-100 d-block text-muted float-left pl-1 small">

    <span class="timeAgo" data="{{ date('c', strtotime($msg->created_at)) }}"></span>

  @if ($msg->price != 0.00)
    - {{ Helper::amountFormatDecimal($msg->price) }} {{ $checkPayPerView ? __('general.paid') : null }} <i class="feather icon-lock mr-1"></i>
  @endif
</span>
</div><!-- media-body -->
</div><!-- media -->
@endif
@endforeach
