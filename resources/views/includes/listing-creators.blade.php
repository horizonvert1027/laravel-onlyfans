<div class="w-100 h-100 d-block" style="background: @if ($response->cover != '') url({{ Helper::getFile(config('path.cover').$response->cover) }})  @endif #505050 center center; border-radius: 6px; background-size: cover;">
    
<div class="card-cover position-relative" style="height: 30px">
    @if ($response->plans()->whereStatus('1')->first() && $response->free_subscription == 'no')
				<span class="badge-free px-2 py-1 position-absolute rounded">{{ __('general.price_per_month', ['price' => Helper::amountFormatDecimal($response->plan('monthly', 'price'))]) }}</span> 	
				@endif
				
    @if ($response->free_subscription == 'yes')
    <span class="badge-free px-2 py-1 position-absolute rounded">{{ __('general.free') }}</span>
  @endif
  </div>
	
	 <li class="list-group-item mb-2 border-0" style="background: rgba(0,0,0,.35);">
         <div class="media">
          <div class="media-left mr-3">
              <div class="media-left mr-3 @if ($response->isLive())liveLink @endif" @if ($response->isLive()) data-url="{{ url('live', $response->username) }}" @endif>
              
              	@if ($response->isLive())
			<span class="live-span">{{ trans('general.live') }}</span>
			<div class="live-pulse"></div>
		@endif
		
              <img class="media-object rounded-circle avatar-user-home" src="{{Helper::getFile(config('path.avatar').$response->avatar)}}"  width="95" height="95">  </div>
          </div>
          <div class="media-body">
            <h6 class="media-heading mb-1">
              <a href="{{url($response->username)}}" class="stretched-link text-white">
                <strong>{{$response->hide_name == 'yes' ? $response->username : $response->name}}</strong>
              </a>
              @if ($response->verified_id == 'yes')
                 <small class="verified mr-1 text-white" title="{{trans('general.verified_account')}}"data-toggle="tooltip" data-placement="top">
                   <i class="bi bi-patch-check"></i>
                 </small>
               @endif

               @if ($response->featured == 'yes')
              <small class="text-featured" title="{{trans('users.creator_featured')}}" data-toggle="tooltip" data-placement="top">
                <i class="fas fa fa-award"></i>
              </small>
              @endif

<small class=" text-white w-100 d-block">{{'@'.$response->username}}</small>
               
            </h6>

            <ul class="list-inline text-white">
              <li class="list-inline-item small"><i class="feather icon-file-text"></i> {{ Helper::formatNumber($response->updates()->count()) }}</li>
              <li class="list-inline-item small"><i class="feather icon-image"></i> {{ Helper::formatNumber($response->media()->where('media.image', '<>', '')->count()) }}</li>
              <li class="list-inline-item small"><i class="feather icon-video"></i> {{ Helper::formatNumber($response->media()->where('media.video', '<>', '')->orWhere('media.video_embed', '<>', '')->where('media.user_id', $response->id)->count()) }}</li>
            </ul>
          </div>
      </div>
  </li> 
</div><!-- End Card -->
