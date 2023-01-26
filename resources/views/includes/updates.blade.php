@foreach ($updates as $response)

	@php
		if (auth()->check()) {
			$checkUserSubscription = auth()->user()->checkSubscription($response->user());
			$checkPayPerView = auth()->user()->payPerView()->where('updates_id', $response->id)->first();
		}

		$totalLikes = number_format($response->likes()->count());
		$totalComments = $response->totalComments();
		$mediaCount = $response->media()->count();
		$allFiles = $response->media()->groupBy('type')->get();
		$getFirstFile = $allFiles->where('type', '<>', 'music')->where('type', '<>', 'file')->where('video_embed', '')->first();

		if ($getFirstFile && $getFirstFile->type == 'image') {
			$urlMedia =  url('media/storage/focus/photo', $getFirstFile->id);
			$backgroundPostLocked = 'background: url('.$urlMedia.') no-repeat center center #b9b9b9; background-size: cover;';
			$textWhite = 'text-white';

		} elseif ($getFirstFile && $getFirstFile->type == 'video' && $getFirstFile->video_poster) {
				$videoPoster = url('media/storage/focus/video', $getFirstFile->video_poster);
				$backgroundPostLocked = 'background: url('.$videoPoster.') no-repeat center center #b9b9b9; background-size: cover;';
				$textWhite = 'text-white';

		} else {
			$backgroundPostLocked = null;
			$textWhite = null;
		}

		$countFilesImage = $response->media()->where('image', '<>', '')->groupBy('type')->count();
		$countFilesVideo = $response->media()->where('video', '<>', '')->orWhere('video_embed', '<>', '')->where('updates_id', $response->id)->groupBy('type')->count();
		$countFilesAudio = $response->media()->where('music', '<>', '')->groupBy('type')->count();

		$mediaImageVideo = $response->media()
				->where('image', '<>', '')
				->orWhere('updates_id', $response->id)
				->where('video', '<>', '')
				->get();

		$mediaImageVideoTotal = $mediaImageVideo->count();

		$videoEmbed = $response->media()->where('video_embed', '<>', '')->get();
		$isVideoEmbed = false;

		if ($videoEmbed->count() != 0) {
			foreach ($videoEmbed as $media) {
				$isVideoEmbed = $media->video_embed;
			}
		}
		$nth = 0; // nth foreach nth-child(3n-1)
		
	@endphp
	<div class="card mb-3 card-updates views rounded-large shadow-large card-border-0 @if ($response->status == 'pending') post-pending @endif @if ($response->fixed_post == '1' && request()->path() == $response->user()->username || auth()->check() && $response->fixed_post == '1' && $response->user()->id == auth()->user()->id) pinned-post @endif" data="{{$response->id}}">
	<div class="card-body">
		<div class="pinned_post text-muted small w-100 mb-2 {{ $response->fixed_post == '1' && request()->path() == $response->user()->username || auth()->check() && $response->fixed_post == '1' && $response->user()->id == auth()->user()->id ? 'pinned-current' : 'display-none' }}">
			<i class="bi bi-pin mr-2"></i> {{ trans('general.pinned_post') }}
		</div>

		@if ($response->status == 'pending')
			<h6 class="text-muted w-100 mb-4">
				<i class="bi bi-eye-fill mr-1"></i> <em>{{ trans('general.post_pending_review') }}</em>
			</h6>
		@endif

	<div class="media">
		<span class="rounded-circle mr-3 position-relative">
			<a href="{{$response->user()->isLive() ? url('live', $response->user()->username) : url($response->user()->username)}}">

				@if (auth()->check() && $response->user()->isLive())
					<span class="live-span">{{ trans('general.live') }}</span>
				@endif

				<img src="{{ Helper::getFile(config('path.avatar').$response->user()->avatar) }}" alt="{{$response->user()->hide_name == 'yes' ? $response->user()->username : $response->user()->name}}" class="rounded-circle avatarUser" width="60" height="60">
				</a>
		</span>

		<div class="media-body">
				<h5 class="mb-0">
					<a style="font-size: 15px;color: #5f5f5f!important;" href="{{url($response->user()->username)}}">
					{{$response->user()->hide_name == 'yes' ? $response->user()->username : $response->user()->name}}
				</a>

				@if($response->user()->verified_id == 'yes')
					<small class="verified" title="{{trans('general.verified_account')}}"data-toggle="tooltip" data-placement="top">
						<i class="bi bi-patch-check-fill"></i>
					</small>
				@endif

				<small style="font-size: 12px!important;" class="text-muted font-14">{{'@'.$response->user()->username}}</small>

				@if (auth()->check() && auth()->user()->id == $response->user()->id)
				<a href="javascript:void(0);" class="text-muted float-right" id="dropdown_options" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<i class="fa fa-ellipsis-h"></i>
				</a>

				<!-- Target -->
				<button class="d-none copy-url" id="url{{$response->id}}" data-clipboard-text="{{url($response->user()->username.'/post', $response->id)}}">{{trans('general.copy_link')}}</button>

				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_options">
					@if (request()->path() != $response->user()->username.'/post/'.$response->id)
						<a class="dropdown-item mb-1" href="{{url($response->user()->username.'/post', $response->id)}}"><i class="bi bi-box-arrow-in-up-right mr-2"></i> {{trans('general.go_to_post')}}</a>
					@endif

					@if ($response->status == 'active')
						<a class="dropdown-item mb-1 pin-post" href="javascript:void(0);" data-id="{{$response->id}}">
							<i class="bi bi-pin mr-2"></i> {{$response->fixed_post == '0' ? trans('general.pin_to_your_profile') : trans('general.unpin_from_profile') }}
						</a>
					@endif

					<button class="dropdown-item mb-1" onclick="$('#url{{$response->id}}').trigger('click')"><i class="feather icon-link mr-2"></i> {{trans('general.copy_link')}}</button>

					<button type="button" class="dropdown-item mb-1" data-toggle="modal" data-target="#editPost{{$response->id}}">
						<i class="bi bi-pencil mr-2"></i> {{trans('general.edit_post')}}
					</button>

					{!! Form::open([
						'method' => 'POST',
						'url' => "update/delete/$response->id",
						'class' => 'd-inline'
					]) !!}

					@if (isset($inPostDetail))
					{!! Form::hidden('inPostDetail', 'true') !!}
				@endif

					{!! Form::button('<i class="feather icon-trash-2 mr-2"></i> '.trans('general.delete_post'), ['class' => 'dropdown-item mb-1 actionDelete']) !!}
					{!! Form::close() !!}
	      </div>

				<div class="modal fade modalEditPost" id="editPost{{$response->id}}" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header border-bottom-0">
							<h5 class="modal-title">{{trans('general.edit_post')}}</h5>
							<button type="button" class="close close-inherit" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">
									<i class="bi bi-x-lg"></i>
								</span>
							</button>
						</div>
						<div class="modal-body">
							<form method="POST" action="{{url('update/edit')}}" enctype="multipart/form-data" class="formUpdateEdit">
								@csrf
								<input type="hidden" name="id" value="{{$response->id}}" />
							<div class="card mb-4">
								<div class="blocked display-none"></div>
								<div class="card-body pb-0">

									<div class="media">
										<div class="media-body">
										<textarea name="description" rows="{{ mb_strlen($response->description) >= 500 ? 10 : 5 }}" cols="40" placeholder="{{trans('general.write_something')}}" class="form-control border-0 updateDescription custom-scrollbar">{{$response->description}}</textarea>
									</div>
								</div><!-- media -->

										<input class="custom-control-input d-none customCheckLocked" type="checkbox" {{$response->locked == 'yes' ? 'checked' : ''}}  name="locked" value="yes">

										<!-- Alert -->
										<div class="alert alert-danger my-3 display-none errorUdpate">
										 <ul class="list-unstyled m-0 showErrorsUdpate small"></ul>
									 </div><!-- Alert -->

								</div><!-- card-body -->

								<div class="card-footer bg-white border-0 pt-0">
									<div class="justify-content-between align-items-center">

										<div class="form-group @if ($response->price == 0.00) display-none @endif price">
											<div class="input-group mb-2">
											<div class="input-group-prepend">
												<span class="input-group-text">{{$settings->currency_symbol}}</span>
											</div>
													<input class="form-control isNumber" value="{{$response->price != 0.00 ? $response->price : null}}" autocomplete="off" name="price" placeholder="{{trans('general.price')}}" type="text">
											</div>
										</div><!-- End form-group -->

										@if ($mediaCount == 0 && $response->locked == 'yes')
										<div class="form-group @if (! $response->title) display-none @endif titlePost">
											<div class="input-group mb-2">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="bi-type"></i></span>
											</div>
													<input class="form-control @if ($response->title) active @endif" value="{{$response->title ? $response->title : null}}" maxlength="100" autocomplete="off" name="title" placeholder="{{trans('admin.title')}}" type="text">
											</div>
											<small class="form-text text-muted mb-4 font-13">
				                {{ __('general.title_post_info', ['numbers' => 100]) }}
				              </small>
										</div><!-- End form-group -->
									@endif

										@if ($response->price == 0.00)
										<button type="button" class="btn btn-upload btn-tooltip e-none align-bottom setPrice @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="tooltip" data-placement="top" title="{{trans('general.price_post_ppv')}}">
											<i class="feather icon-dollar-sign f-size-25"></i><h6 class="d-inline font-weight-lighter">Preço</h6>
										</button>
									@endif

									@if ($response->price == 0.00)
										<button type="button" class="contentLocked btn e-none align-bottom @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill btn-upload btn-tooltip {{$response->locked == 'yes' ? '' : 'unlock'}}" data-toggle="tooltip" data-placement="top" title="{{trans('users.locked_content')}}">
											<i class="feather icon-{{$response->locked == 'yes' ? '' : 'un'}}lock f-size-25"></i>
										</button>
									@endif

								@if ($mediaCount == 0 && $response->locked == 'yes')
									<button type="button" class="btn btn-upload btn-tooltip e-none align-bottom @if ($response->title) btn-active-hover @endif setTitle @if (auth()->user()->dark_mode == 'off') text-primary @else text-white @endif rounded-pill" data-toggle="tooltip" data-placement="top" title="{{trans('general.title_post_block')}}">
										<i class="bi-type f-size-25"></i>
									</button>
								@endif

										<div class="d-inline-block float-right mt-3">
											<button type="submit" class="btn btn-sm btn-primary rounded-pill float-right btnEditUpdate"><i></i> {{trans('users.save')}}</button>
										</div>

									</div>
								</div><!-- card footer -->
							</div><!-- card -->
						</form>
					</div><!-- modal-body -->
					</div><!-- modal-content -->
				</div><!-- modal-dialog -->
			</div><!-- modal -->
			@endif

				@if(auth()->check()
					&& auth()->user()->id != $response->user()->id
					&& $response->locked == 'yes'
					&& $checkUserSubscription && $response->price == 0.00

					|| auth()->check()
						&& auth()->user()->id != $response->user()->id
						&& $response->locked == 'yes'
						&& $checkUserSubscription
						&& $response->price != 0.00
						&& $checkPayPerView

					|| auth()->check()
						&& auth()->user()->id != $response->user()->id
						&& $response->price != 0.00
						&& ! $checkUserSubscription
						&& $checkPayPerView

					|| auth()->check() && auth()->user()->id != $response->user()->id && auth()->user()->role == 'admin' && auth()->user()->permission == 'all'
					|| auth()->check() && auth()->user()->id != $response->user()->id && $response->locked == 'no'
					)
					<a href="javascript:void(0);" class="text-muted float-right" id="dropdown_options" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<i class="fa fa-ellipsis-h"></i>
					</a>

					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_options">

						<!-- Target -->
						<button class="d-none copy-url" id="url{{$response->id}}" data-clipboard-text="{{url($response->user()->username.'/post', $response->id).Helper::referralLink()}}">
							{{trans('general.copy_link')}}
						</button>

						@if (request()->path() != $response->user()->username.'/post/'.$response->id)
							<a class="dropdown-item" href="{{url($response->user()->username.'/post', $response->id)}}">
								<i class="bi bi-box-arrow-in-up-right mr-2"></i> {{trans('general.go_to_post')}}
							</a>
						@endif

						<button class="dropdown-item" onclick="$('#url{{$response->id}}').trigger('click')">
							<i class="feather icon-link mr-2"></i> {{trans('general.copy_link')}}
						</button>

						<button type="button" class="dropdown-item" data-toggle="modal" data-target="#reportUpdate{{$response->id}}">
							<i class="bi bi-flag mr-2"></i>  {{trans('admin.report')}}
						</button>

					</div>

			<div class="modal fade modalReport" id="reportUpdate{{$response->id}}" tabindex="-1" role="dialog" aria-hidden="true">
     		<div class="modal-dialog modal-danger modal-sm">
     			<div class="modal-content">
						<div class="modal-header">
              <h6 class="modal-title font-weight-light" id="modal-title-default">
								<i class="fas fa-flag mr-1"></i> {{trans('admin.report_update')}}
							</h6>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="fa fa-times"></i>
              </button>
            </div>

					<!-- form start -->
					<form method="POST" action="{{url('report/update', $response->id)}}" enctype="multipart/form-data">
				  <div class="modal-body">
						@csrf
				    <!-- Start Form Group -->
            <div class="form-group">
              <label>{{trans('admin.please_reason')}}</label>
              	<select name="reason" class="form-control custom-select">
                    <option value="copyright">{{trans('admin.copyright')}}</option>
                    <option value="privacy_issue">{{trans('admin.privacy_issue')}}</option>
                    <option value="violent_sexual">{{trans('admin.violent_sexual_content')}}</option>
                  </select>
                  </div><!-- /.form-group-->
				      </div><!-- Modal body -->

							<div class="modal-footer">
								<button type="button" class="btn border text-white" data-dismiss="modal">{{trans('admin.cancel')}}</button>
								<button type="submit" class="btn btn-xs btn-white sendReport ml-auto"><i></i> {{trans('admin.report_update')}}</button>
							</div>
							</form>
     				</div><!-- Modal content -->
     			</div><!-- Modal dialog -->
     		</div><!-- Modal -->
				@endif
			</h5>

				<small class="timeAgo text-muted" data="{{date('c', strtotime($response->date))}}"></small>

				@if ($response->locked == 'no')
				<small class="text-muted type-post" title="{{trans('general.public')}}">
					<i class="iconmoon icon-WorldWide mr-1"></i>
				</small>
				@endif

			@if ($response->locked == 'yes')

				<small class="text-muted type-post" title="{{trans('users.content_locked')}}">

					<i class="feather icon-lock mr-1"></i>

					@if (auth()->check() && $response->price != 0.00
							&& $checkUserSubscription
							&& ! $checkPayPerView
							|| auth()->check() && $response->price != 0.00
							&& ! $checkUserSubscription
							&& ! $checkPayPerView
						)
						{{ Helper::amountFormatDecimal($response->price) }}

					@elseif (auth()->check() && $checkPayPerView)
						{{ __('general.paid') }}
					@endif
				</small>
			@endif
		</div><!-- media body -->
	</div><!-- media -->
</div><!-- card body -->

@if (auth()->check() && auth()->user()->id == $response->user()->id
	|| $response->locked == 'yes' && $mediaCount != 0

	|| auth()->check() && $response->locked == 'yes'
	&& $checkUserSubscription
	&& $response->price == 0.00

	|| auth()->check() && $response->locked == 'yes'
	&& $checkUserSubscription
	&& $response->price != 0.00
	&& $checkPayPerView

	|| auth()->check() && $response->locked == 'yes'
	&& $response->price != 0.00
	&& ! $checkUserSubscription
	&& $checkPayPerView

	|| auth()->check() && auth()->user()->role == 'admin' && auth()->user()->permission == 'all'
	|| $response->locked == 'no'
	)
	<div class="card-body pt-0 pb-3">
		<p class="mb-0 update-text position-relative text-word-break">
			{!! Helper::linkText(Helper::checkText($response->description, $isVideoEmbed ?? null)) !!}
		</p>
	</div>

@else
	@if ($response->title)
	<div class="card-body pt-0 pb-3">
		<p class="mb-0 update-text position-relative text-word-break font-weight-bold">
			{!! Helper::linkText($response->title) !!}
		</p>
	</div>
	@endif
@endif

		@if (auth()->check() && auth()->user()->id == $response->user()->id

		|| auth()->check() && $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price == 0.00

		|| auth()->check() && $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price != 0.00
		&& $checkPayPerView

		|| auth()->check() && $response->locked == 'yes'
		&& $response->price != 0.00
		&& ! $checkUserSubscription
		&& $checkPayPerView

		|| auth()->check() && auth()->user()->role == 'admin' && auth()->user()->permission == 'all'
		|| $response->locked == 'no'
		)

	<div class="btn-block">

		@if ($mediaImageVideoTotal <> 0)
			@include('includes.media-post')
		@endif

		@foreach ($response->media as $media)
			@if ($media->music != '')
			<div class="mx-3 border rounded @if ($mediaCount > 1) mt-3 @endif">
				<audio id="music-{{$media->id}}" class="js-player w-100 @if (!request()->ajax())invisible @endif" controls>
					<source src="{{ Helper::getFile(config('path.music').$media->music) }}" type="audio/mp3">
					Your browser does not support the audio tag.
				</audio>
			</div>
			@endif

			@if ($media->file != '')
			<a href="{{url('download/file', $response->id)}}" class="d-block text-decoration-none @if ($mediaCount > 1) mt-3 @endif">
				<div class="card mb-3 mx-3">
					<div class="row no-gutters">
						<div class="col-md-2 text-center bg-primary">
							<i class="far fa-file-archive m-4 text-white" style="font-size: 48px;"></i>
						</div>
						<div class="col-md-10">
							<div class="card-body">
								<h5 class="card-title text-primary text-truncate mb-0">
									{{ $media->file_name }}.zip
								</h5>
								<p class="card-text">
									<small class="text-muted">{{ $media->file_size }}</small>
								</p>
							</div>
						</div>
					</div>
				</div>
				</a>
			@endif
		@endforeach

		@if ($isVideoEmbed)

				@if (in_array(Helper::videoUrl($isVideoEmbed), array('youtube.com','www.youtube.com','youtu.be','www.youtu.be', 'm.youtube.com')))
					<div class="embed-responsive embed-responsive-16by9 mb-2">
						<iframe class="embed-responsive-item" height="360" src="https://www.youtube.com/embed/{{ Helper::getYoutubeId($isVideoEmbed) }}" allowfullscreen></iframe>
					</div>
				@endif

				@if (in_array(Helper::videoUrl($isVideoEmbed), array('vimeo.com','player.vimeo.com')))
					<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" src="https://player.vimeo.com/video/{{ Helper::getVimeoId($isVideoEmbed) }}" allowfullscreen></iframe>
					</div>
				@endif

		@endif

	</div><!-- btn-block -->

@else

	<div class="btn-block p-sm text-center content-locked pt-lg pb-lg px-3 {{$textWhite}}" style="{{$backgroundPostLocked}}">
		<span class="btn-block text-center mb-3"><i class="feather icon-lock ico-no-result border-0 {{$textWhite}}"></i></span>

		@if ($response->user()->planActive() && $response->price == 0.00
				|| $response->user()->free_subscription == 'yes' && $response->price == 0.00)
			<a href="javascript:void(0);" @guest data-toggle="modal" data-target="#loginFormModal" @else @if ($response->user()->free_subscription == 'yes') data-toggle="modal" data-target="#subscriptionFreeForm" @else data-toggle="modal" data-target="#subscriptionForm" @endif @endguest class="btn btn-primary w-100">
				{{ trans('general.content_locked_user_logged') }}
			</a>
		@elseif ($response->user()->planActive() && $response->price != 0.00
				|| $response->user()->free_subscription == 'yes' && $response->price != 0.00)
				<a href="javascript:void(0);" @guest data-toggle="modal" data-target="#loginFormModal" @else @if ($response->status == 'active') data-toggle="modal" data-target="#payPerViewForm" data-mediaid="{{$response->id}}" data-price="{{Helper::amountFormatDecimal($response->price, true)}}" data-subtotalprice="{{Helper::amountFormatDecimal($response->price)}}" data-pricegross="{{$response->price}}" @endif @endguest class="btn btn-primary w-100">
					@guest
						{{ trans('general.content_locked_user_logged') }}
					@else

						@if ($response->status == 'active')
								<i class="feather icon-unlock mr-1"></i> {{ trans('general.unlock_post_for') }} {{Helper::amountFormatDecimal($response->price)}}

							@else
								{{ trans('general.post_pending_review') }}
						@endif
						@endguest
				</a>
		@else
			<a href="javascript:void(0);" class="btn btn-primary disabled w-100">
				{{ trans('general.subscription_not_available') }}
			</a>
		@endif

		<ul class="list-inline mt-3">

		@if ($mediaCount == 0)
			<li class="list-inline-item"><i class="bi bi-file-font"></i> {{ __('admin.text') }}</li>
		@endif

@if ($mediaCount != 0)
	@foreach ($allFiles as $media)

		@if ($media->type == 'image')
			<li class="list-inline-item"><i class="feather icon-image"></i> {{$countFilesImage}}</li>
		@endif

		@if ($media->type == 'video')
			<li class="list-inline-item"><i class="feather icon-video"></i> {{$countFilesVideo}} @if ($media->duration_video && $countFilesVideo == 1 || $media->quality_video && $countFilesVideo == 1) <small class="ml-1">@if ($media->quality_video)<span class="quality-video">{{ $media->quality_video }}</span>@endif {{ $media->duration_video }}</small> @endif</li>
		@endif

		@if ($media->type == 'music')
			<li class="list-inline-item"><i class="feather icon-mic"></i> {{$countFilesAudio}}</li>
			@endif

			@if ($media->type == 'file')
			<li class="list-inline-item"><i class="far fa-file-archive"></i> {{$media->file_size}}</li>
		@endif

	@endforeach
	@endif
</ul>

</div><!-- btn-block parent -->

	@endif

@if ($response->status == 'active')
<div style="margin-top:10px;" class="card-footer bg-white border-top-0 rounded-large">
    <h4 class="mb-2">
			@php
			$likeActive = auth()->check() && auth()->user()->likes()->where('updates_id', $response->id)->where('status','1')->first();
			$bookmarkActive = auth()->check() && auth()->user()->bookmarks()->where('updates_id', $response->id)->first();

			if(auth()->check() && auth()->user()->id == $response->user()->id

			|| auth()->check() && $response->locked == 'yes'
			&& $checkUserSubscription
			&& $response->price == 0.00

			|| auth()->check() && $response->locked == 'yes'
			&& $checkUserSubscription
			&& $response->price != 0.00
			&& $checkPayPerView

			|| auth()->check() && $response->locked == 'yes'
			&& $response->price != 0.00
			&& ! $checkUserSubscription
			&& $checkPayPerView

			|| auth()->check() && auth()->user()->role == 'admin' && auth()->user()->permission == 'all'
			|| auth()->check() && $response->locked == 'no') {
				$buttonLike = 'likeButton';
				$buttonBookmark = 'btnBookmark';
			} else {
				$buttonLike = null;
				$buttonBookmark = null;
			}
			@endphp

			<a class="pulse-btn btnLike @if ($likeActive)active @endif {{$buttonLike}} text-muted mr-14px" href="javascript:void(0);" @guest data-toggle="modal" data-target="#loginFormModal" @endguest @auth data-id="{{$response->id}}" @endauth>
				<i class="@if($likeActive)fas @else far @endif fa-heart"></i>
			</a>

			<span class="text-muted mr-14px @auth @if (! isset($inPostDetail) && $buttonLike) pulse-btn toggleComments @endif @endauth">
				<i class="feather icon-message-circle"></i>
			</span>
			
	@auth
		@if (auth()->user()->id != $response->user()->id
					&& $checkUserSubscription && $response->price == 0.00
					&& $settings->disable_tips == 'off'

					|| auth()->user()->id != $response->user()->id
					&& $checkUserSubscription
					&& $response->price != 0.00
					&& $checkPayPerView
					&& $settings->disable_tips == 'off'

					|| auth()->check() && $response->locked == 'yes'
					&& $response->price != 0.00
					&& ! $checkUserSubscription
					&& $checkPayPerView
					&& $settings->disable_tips == 'off'

					|| auth()->user()->id != $response->user()->id
					&& $response->locked == 'no'
					&& $settings->disable_tips == 'off'
					)
<a href="javascript:void(0);" data-toggle="modal" title="{{trans('general.tip')}}" data-target="#tipForm" class="pulse-btn text-muted text-decoration-none" @auth data-id="{{$response->id}}" data-cover="{{Helper::getFile(config('path.cover').$response->user()->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$response->user()->avatar)}}" data-name="{{$response->user()->hide_name == 'yes' ? $response->user()->username : $response->user()->name}}" data-userid="{{$response->user()->id}}" @endauth>
<i class="feather icon-dollar-sign"></i>
				<h6 class="d-inline font-weight-lighter">@lang('general.tip')</h6>
			</a>
		@endif
	@endauth

			<a href="javascript:void(0);" @guest data-toggle="modal" data-target="#loginFormModal" @endguest class="pulse-btn @if ($bookmarkActive) text-primary @else text-muted @endif float-right {{$buttonBookmark}}" @auth data-id="{{$response->id}}" @endauth>
				<i class="@if ($bookmarkActive)fas @else far @endif fa-bookmark"></i>
			</a>
		</h4>

		<div class="w-100 mb-3 containerLikeComment">
			<span class="countLikes text-muted dot-item">
				{{ trans_choice('general.like_likes', $totalLikes, ['total' => number_format($totalLikes)]) }}
			</span> 
			<span class="text-muted totalComments dot-item @auth @if (! isset($inPostDetail) && $buttonLike)toggleComments @endif @endauth">
				{{ trans_choice('general.comment_comments', $totalComments, ['total' => number_format($totalComments)]) }}
			</span>

			@if ($response->video_views)
			<span class="text-muted dot-item">
				<i class="bi-eye mr-1"></i> {{ Helper::formatNumber($response->video_views) }}
			</span>
			@endif
		</div>

@auth

<style>.ico-no-result {
    color: #fff!important;
    border: 2px solid #fff!important;
}</style>

@if (! auth()->user()->checkRestriction($response->user()->id))
<div class="container-comments @if ( ! isset($inPostDetail)) display-none @endif">

<div class="container-media">
@if($response->comments()->count() != 0)

	@php
	  $comments = $response->comments()->take($settings->number_comments_show)->orderBy('id', 'DESC')->get();
	  $data = [];

	  if ($comments->count()) {
	      $data['reverse'] = collect($comments->values())->reverse();
	  } else {
	      $data['reverse'] = $comments;
	  }

	  $dataComments = $data['reverse'];
	  $counter = ($response->comments()->count() - $settings->number_comments_show);
	@endphp

	@if (auth()->user()->id == $response->user()->id

		|| $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price == 0.00

		|| $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price != 0.00
		&& $checkPayPerView

		|| auth()->check() && $response->locked == 'yes'
		&& $response->price != 0.00
		&& ! $checkUserSubscription
		&& $checkPayPerView

		|| auth()->user()->role == 'admin'
		&& auth()->user()->permission == 'all'
		|| $response->locked == 'no')

		@include('includes.comments')

@endif

@endif
	</div><!-- container-media -->

	@if (auth()->user()->id == $response->user()->id

		|| $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price == 0.00

		|| $response->locked == 'yes'
		&& $checkUserSubscription
		&& $response->price != 0.00
		&& $checkPayPerView

		|| auth()->check() && $response->locked == 'yes'
		&& $response->price != 0.00
		&& ! $checkUserSubscription
		&& $checkPayPerView

		|| auth()->user()->role == 'admin'
		&& auth()->user()->permission == 'all'
		|| $response->locked == 'no')

		<div class="alert alert-danger alert-small dangerAlertComments display-none">
			<ul class="list-unstyled m-0 showErrorsComments"></ul>
		</div><!-- Alert -->

		<div class="isReplyTo display-none w-100 bg-light py-2 px-3 mb-3 rounded">
			{{ __('general.replying_to') }} <span class="username-reply"></span>

			<span class="float-right c-pointer cancelReply" title="{{ __('admin.cancel') }}">
				<i class="bi-x-lg"></i>
			</span>
		</div>

		<div class="media position-relative pt-3 border-top">
			<div class="blocked display-none"></div>
			<span href="#" class="float-left">
				<img src="{{ Helper::getFile(config('path.avatar').auth()->user()->avatar) }}" class="rounded-circle mr-1 avatarUser" width="40">
			</span>
			<div class="media-body">
				<form action="{{url('comment/store')}}" method="post" class="comments-form">
					@csrf
					<input type="hidden" name="update_id" value="{{$response->id}}" />
					<input class="isReply" type="hidden" name="isReply" value="" />

					<div>
					<span class="triggerEmoji" data-toggle="dropdown">
						<i class="bi-emoji-smile"></i>
					</span>

					<div class="dropdown-menu dropdown-menu-right dropdown-emoji custom-scrollbar" aria-labelledby="dropdownMenuButton">
				    @include('includes.emojis')
				  </div>
				</div>

				<input type="text" name="comment" class="form-control comments inputComment emojiArea border-0" autocomplete="off" placeholder="{{trans('general.write_comment')}}"></div>
				</form>
			</div>
			@endif

			</div><!-- container-comments -->
		@endif

			@endauth
  </div><!-- card-footer -->
	@endif
</div><!-- card -->

@if (request()->is('/') && $loop->first && $users->total() != 0
	|| request()->is('explore') && $loop->first && $users->total() != 0
	|| request()->is('my/bookmarks') && $loop->first && $users->total() != 0
	|| request()->is('my/purchases') && $loop->first && $users->total() != 0
	|| request()->is('my/likes') && $loop->first && $users->total() != 0
	)
	<div class="p-3 d-lg-none">
		@include('includes.explore_creators')
	</div>
@endif

@endforeach

@if (! isset($singlePost))
<div class="card mb-3 pb-4 loadMoreSpin d-none rounded-large shadow-large">
	<div class="card-body">
		<div class="media">
		<span class="rounded-circle mr-3">
			<span class="item-loading position-relative loading-avatar"></span>
		</span>
		<div class="media-body">
			<h5 class="mb-0 item-loading position-relative loading-name"></h5>
			<small class="text-muted item-loading position-relative loading-time"></small>
		</div>
	</div>
</div>
	<div class="card-body pt-0 pb-3">
		<p class="mb-1 item-loading position-relative loading-text-1"></p>
		<p class="mb-1 item-loading position-relative loading-text-2"></p>
		<p class="mb-0 item-loading position-relative loading-text-3"></p>
	</div>
</div>
@endif

@php
	if (isset($ajaxRequest)) {
		$totalPosts = $total;
	} else {
		$totalPosts = $updates->total();
	}
@endphp

@if ($totalPosts > $settings->number_posts_show && $counterPosts >= 1)
	<button rel="next" class="btn btn-primary w-100 text-center loadPaginator d-none" id="paginator">
		{{trans('general.loadmore')}}
	</button>
@endif