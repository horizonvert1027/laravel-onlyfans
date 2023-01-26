@if ($mediaImageVideoTotal == 1)

@foreach ($mediaImageVideo as $media)
	@php
		$urlImg = url('files/messages', $msg->id).'/'.$media->file;

		if ($media->width && $media->height > $media->width) {
			$styleImgVertical = 'img-vertical-lg';
		} else {
			$styleImgVertical = null;
		}
	@endphp

	@if ($media->type == 'image')
		<div class="media-grid-1">
			<a href="{{ $urlImg }}" class="media-wrapper glightbox {{$styleImgVertical}}" data-gallery="gallery{{$msg->id}}" style="background-image: url('{{$urlImg}}?w=960&h=980')">
					<img src="{{$urlImg}}?w=960&h=980" {!! $media->width ? 'width="'. $media->width .'"' : null !!} {!! $media->height ? 'height="'. $media->height .'"' : null !!} class="post-img-grid">
			</a>
		</div>
@endif

@if ($media->type == 'video')
	<div class="container-media-msg h-auto">
		<video class="js-player {{$classInvisible}}" controls style="height: 400px;" @if ($media->video_poster) data-poster="{{ Helper::getFile(config('path.messages').$media->video_poster) }}" @endif>
		<source src="{{Helper::getFile(config('path.messages').$media->file)}}" type="video/mp4" />
	</video>
</div>
@endif

@endforeach

@endif

@if ($mediaImageVideoTotal >= 2)

	<div class="media-grid-{{ $mediaImageVideoTotal > 4 ? 4 : $mediaImageVideoTotal }}">

@foreach ($mediaImageVideo as $media)
	@php

	if ($media->type == 'video') {
		$urlMedia =  Helper::getFile(config('path.messages').$media->file);
		$videoPoster = $media->video_poster ? Helper::getFile(config('path.messages').$media->video_poster) : false;
	} else {
		$urlMedia =  url("files/messages", $msg->id).'/'.$media->file;
		$videoPoster = null;
	}

		$nth++;
	@endphp

		@if ($media->type == 'image' || $media->type == 'video')

			<a href="{{ $urlMedia }}" class="media-wrapper glightbox" data-gallery="gallery{{$msg->id}}" style="background-image: url('{{ $videoPoster ?? $urlMedia }}?w=960&h=980')">

				@if ($nth == 4 && $mediaImageVideoTotal > 4)
		        <span class="more-media">
							<h2>+{{ $mediaImageVideoTotal - 4 }}</h2>
						</span>
		    @endif

				@if ($media->type == 'video')
					<span class="button-play">
						<i class="bi bi-play-fill text-white"></i>
					</span>
				@endif

				@if (! $videoPoster)
					<video playsinline muted class="video-poster-html">
						<source src="{{ $urlMedia }}" type="video/mp4" />
					</video>
				@endif

				@if ($videoPoster)
					<img src="{{ $videoPoster ?? $urlMedia }}?w=960&h=980" {!! $media->width ? 'width="'. $media->width .'"' : null !!} {!! $media->height ? 'height="'. $media->height .'"' : null !!} class="post-img-grid">
				@endif
			</a>

		@endif

@endforeach

</div><!-- img-grid -->

@endif

@foreach ($msg->media as $media)

	@if ($media->type == 'music')
	<div class="wrapper-media-music @if ($mediaCount >= 2) mt-2 @endif">
		<audio class="js-player {{$classInvisible}}" controls>
		<source src="{{Helper::getFile(config('path.messages').$media->file)}}" type="audio/mp3">
		Your browser does not support the audio tag.
	</audio>
</div>
	@endif

@if ($media->type == 'zip')
	<a href="{{url('download/message/file', $msg->id)}}" class="d-block text-decoration-none @if ($mediaCount >= 2) mt-2 @endif">
	 <div class="card">
		 <div class="row no-gutters">
			 <div class="col-md-3 text-center bg-primary">
				 <i class="far fa-file-archive m-2 text-white" style="font-size: 40px;"></i>
			 </div>
			 <div class="col-md-9">
				 <div class="card-body py-2 px-4">
					 <h6 class="card-title text-primary text-truncate mb-0">
						 {{$media->file_name}}.zip
					 </h6>
					 <p class="card-text">
						 <small class="text-muted">{{$media->file_size}}</small>
					 </p>
				 </div>
			 </div>
		 </div>
	 </div>
	 </a>
	 @endif

 @endforeach

 @if ($msg->tip == 'yes')
	<div class="card">
		 <div class="row no-gutters">
			 <div class="col-md-12">
				 <div class="card-body py-2 px-4">
					 <h6 class="card-title text-primary text-truncate mb-0">
						<i class="feather icon-dollar-sign f-size-25 align-bottom"></i> {{__('general.tip'). ' - ' .Helper::amountWithoutFormat($msg->tip_amount)}}
					 </h6>
				 </div>
			 </div>
		 </div>
	 </div>
	 @endif

@if ($mediaCount == 0)
	{!! $chatMessage !!}
@endif
