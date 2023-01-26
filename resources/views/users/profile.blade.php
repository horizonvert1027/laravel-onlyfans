@extends('layouts.app')

@section('title'){{ $user->hide_name == 'yes' ? $mediaTitle.$user->username : $mediaTitle.$user->name }} -@endsection
  @section('description_custom'){{$mediaTitle.$user->username}} - {{strip_tags($user->story)}}@endsection

  @section('css')

  <meta property="og:type" content="website" />
  <meta property="og:image:width" content="200"/>
  <meta property="og:image:height" content="200"/>

  <!-- Current locale and alternate locales -->
  <meta property="og:locale" content="en_US" />
  <meta property="og:locale:alternate" content="es_ES" />

  <!-- Og Meta Tags -->
  <link rel="canonical" href="{{url($user->username.$media)}}"/>
  <meta property="og:site_name" content="{{ $user->hide_name == 'yes' ? $user->username : $user->name }} - Oprivado"/>
  <meta property="og:url" content="{{url($user->username.$media)}}"/>
  <meta property="og:image" content="{{Helper::getFile(config('path.avatar').$user->avatar)}}"/>

  <meta property="og:title" content="{{ $user->hide_name == 'yes' ? $user->username : $user->name }} - Oprivado"/>
  <meta property="og:description" content="{{strip_tags($user->story)}}"/>
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:image" content="{{Helper::getFile(config('path.avatar').$user->avatar)}}" />
  <meta name="twitter:title" content="{{ $user->hide_name == 'yes' ? $user->username : $user->name }}" />
  <meta name="twitter:description" content="{{strip_tags($user->story)}}"/>

  <script type="text/javascript">
      var profile_id = {{$user->id}};
      var sort_post_by_type_media = "{!!$sortPostByTypeMedia!!}";
  </script>
  @endsection

@section('content')
<div class="jumbotron lazyloaded jumbotron-cover-user home m-0 position-relative" style="padding: @if ($user->cover != '') @if (request()->path() == $user->username) 210px @else 125px @endif @else 125px @endif 0; background: #505050 @if ($user->cover != '') url('{{Helper::getFile(config('path.cover').$user->cover)}}') no-repeat center center; background-size: cover; @endif">
  @if (auth()->check() && auth()->user()->status == 'active' && auth()->id() == $user->id)

    <div class="progress-upload-cover"></div>

    <form action="{{url('upload/cover')}}" method="POST" id="formCover" accept-charset="UTF-8" enctype="multipart/form-data">
      @csrf
    <input type="file" name="image" id="uploadCover" accept="image/*" class="visibility-hidden">
  </form>

  <button class="btn btn-cover-upload" id="coverFile" onclick="$('#uploadCover').trigger('click');">
    <i class="fa fa-camera mr-1"></i>  <span class="d-none d-lg-inline">{{trans('general.change_cover')}}</span>
  </button>
@endif
</div>

  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="w-100 text-center py-4 img-profile-user">

          <div @if ($user->isLive() && auth()->check() && auth()->id() != $user->id) data-url="{{ url('live', $user->username) }}" @endif class="text-center position-relative @if ($user->isLive() && auth()->check() && auth()->id() != $user->id) avatar-wrap-live liveLink @else avatar-wrap @endif @if (auth()->check() && auth()->id() != $user->id && Cache::has('is-online-' . $user->id) && $user->active_status_online == 'yes' || auth()->guest() && Cache::has('is-online-' . $user->id) && $user->active_status_online == 'yes') user-online-profile overflow-visible @elseif (auth()->check() && auth()->id() != $user->id && !Cache::has('is-online-' . $user->id) && $user->active_status_online == 'yes' || auth()->guest() && !Cache::has('is-online-' . $user->id) && $user->active_status_online == 'yes') user-offline-profile overflow-visible @endif">

            @if (auth()->check() && auth()->id() != $user->id && $user->isLive())
              <span class="live-span">{{ trans('general.live') }}</span>
              <div class="live-pulse"></div>
            @endif

            <div class="progress-upload">0%</div>

            @if (auth()->check() && auth()->user()->status == 'active' && auth()->id() == $user->id)

              <form action="{{url('upload/avatar')}}" method="POST" id="formAvatar" accept-charset="UTF-8" enctype="multipart/form-data">
                @csrf
              <input type="file" name="avatar" id="uploadAvatar" accept="image/*" class="visibility-hidden">
            </form>

            <a href="javascript:;" class="position-absolute button-avatar-upload" id="avatar_file">
              <i class="fa fa-camera"></i>
            </a>
          @endif
            <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" width="150" height="150" alt="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" class="rounded-circle img-user mb-2 avatarUser @if (auth()->check() && auth()->id() != $user->id && $user->isLive()) border-0 @endif">
          </div><!-- avatar-wrap -->

          <div class="media-body">
            <h4 style=" margin-top: 11px!important; " class="mt-1">
              {{$user->hide_name == 'yes' ? $user->username : $user->name}}

              @if ($user->verified_id == 'yes')
              <small class="verified" title="{{trans('general.verified_account')}}" data-toggle="tooltip" data-placement="top">
                <i class="bi bi-patch-check-fill"></i>
              </small>
            @endif

            @if ($user->featured == 'yes')
               <small class="text-featured" title="{{trans('users.creator_featured')}}" data-toggle="tooltip" data-placement="top">
              <i class="fas fa fa-award"></i>
            </small>
          @endif
          </h4>

            <p>
            <span>
              
               @if ($user->verified_id == 'yes')
              <span style="font-size: 13px;" class="w-100 d-block update-text">
                {!! Helper::checkText($user->story)  !!}
              </span>
            @endif
              
          </span>
            </p>

            <div class="d-flex-user justify-content-center mb-2">
            @if (auth()->check() && auth()->id() == $user->id)
              <a href="{{url('settings/page')}}" class="btn btn-primary btn-profile mr-1"> {{ auth()->user()->verified_id == 'yes' ? trans('general.edit_my_page') : trans('users.edit_profile')}}</a>
            @endif

              @if ($userPlanMonthlyActive
                  && $user->verified_id == 'yes'
                  || $user->free_subscription == 'yes'
                  && $user->verified_id == 'yes')

              @if (auth()->check() && auth()->id() != $user->id
                  && ! $checkSubscription
                  && ! $paymentIncomplete
                  && $user->free_subscription == 'no'
                  && $user->updates()->count() != 0
                  )
                <a href="javascript:void(0);" data-toggle="modal" data-target="#subscriptionForm" class="btn btn-primary btn-profile mr-1">
                  <i class="feather icon-unlock mr-1"></i> {{trans('general.subscribe_month', ['price' => Helper::amountFormatDecimal($user->plan('monthly', 'price'))])}}
                </a>
              @elseif (auth()->check() && auth()->id() != $user->id && ! $checkSubscription && $paymentIncomplete)
                <a href="{{ route('cashier.payment', $paymentIncomplete->last_payment) }}" class="btn btn-warning btn-profile mr-1">
                  <i class="fa fa-exclamation-triangle"></i> {{trans('general.confirm_payment')}}
                </a>
              @elseif (auth()->check() && auth()->id() != $user->id && $checkSubscription)

                @if ($checkSubscription->stripe_status == 'active' && $checkSubscription->stripe_id != '')

                {!! Form::open([
                  'method' => 'POST',
                  'url' => "subscription/cancel/$checkSubscription->stripe_id",
                  'class' => 'd-inline formCancel'
                ]) !!}

                {!! Form::button('<i class="feather icon-user-check mr-1"></i> '.trans('general.your_subscribed'), ['data-expiration' => trans('general.subscription_expire').' '.Helper::formatDate(auth()->user()->subscription('main', $checkSubscription->stripe_price)->asStripeSubscription()->current_period_end, true), 'class' => 'btn btn-success btn-profile mr-1 cancelBtn subscriptionActive']) !!}
                {!! Form::close() !!}

              @elseif ($checkSubscription->stripe_id == '' && $checkSubscription->free == 'yes')
                {!! Form::open([
                  'method' => 'POST',
                  'url' => "subscription/free/cancel/$checkSubscription->id",
                  'class' => 'd-inline formCancel'
                ]) !!}

                {!! Form::button('<i class="feather icon-user-check mr-1"></i> '.trans('general.your_subscribed'), ['data-expiration' => trans('general.confirm_cancel_subscription'), 'class' => 'btn btn-success btn-profile mr-1 cancelBtn subscriptionActive']) !!}
                {!! Form::close() !!}

              @elseif ($paymentGatewaySubscription == 'Paystack' && $checkSubscription->cancelled == 'no')
                {!! Form::open([
                  'method' => 'POST',
                  'url' => "subscription/paystack/cancel/$checkSubscription->subscription_id",
                  'class' => 'd-inline formCancel'
                ]) !!}

                {!! Form::button('<i class="feather icon-user-check mr-1"></i> '.trans('general.your_subscribed'), ['data-expiration' => trans('general.subscription_expire').' '.Helper::formatDate($checkSubscription->ends_at), 'class' => 'btn btn-success btn-profile mr-1 cancelBtn subscriptionActive']) !!}
                {!! Form::close() !!}

              @elseif ($paymentGatewaySubscription == 'Wallet' && $checkSubscription->cancelled == 'no')
                {!! Form::open([
                  'method' => 'POST',
                  'url' => "subscription/wallet/cancel/$checkSubscription->id",
                  'class' => 'd-inline formCancel'
                ]) !!}

                {!! Form::button('<i class="feather icon-user-check mr-1"></i> '.trans('general.your_subscribed'), ['data-expiration' => trans('general.subscription_expire').' '.Helper::formatDate($checkSubscription->ends_at), 'class' => 'btn btn-success btn-profile mr-1 cancelBtn subscriptionActive']) !!}
                {!! Form::close() !!}

              @elseif ($paymentGatewaySubscription == 'PayPal' && $checkSubscription->cancelled == 'no')
                {!! Form::open([
                  'method' => 'POST',
                  'url' => "subscription/paypal/cancel/$checkSubscription->id",
                  'class' => 'd-inline formCancel'
                ]) !!}

                {!! Form::button('<i class="feather icon-user-check mr-1"></i> '.trans('general.your_subscribed'), ['data-expiration' => trans('general.subscription_expire').' '.Helper::formatDate($checkSubscription->ends_at), 'class' => 'btn btn-success btn-profile mr-1 cancelBtn subscriptionActive']) !!}
                {!! Form::close() !!}

              @elseif ($paymentGatewaySubscription == 'CCBill' && $checkSubscription->cancelled == 'no')
                <a href="javascript:void(0);" data-expiration="{{ Helper::formatDate($checkSubscription->ends_at) }}" class="btn btn-success btn-profile mr-1 subscriptionActive subsCCBill">
                  <i class="feather icon-user-check mr-1"></i> {{trans('general.your_subscribed')}}
                </a>

              @elseif ($checkSubscription->cancelled == 'yes' || $checkSubscription->stripe_status == 'canceled')
                <a href="javascript:void(0);" class="btn btn-success btn-profile mr-1 disabled">
                  <i class="feather icon-user-check mr-1"></i> {{trans('general.subscribed_until')}} {{ Helper::formatDate($checkSubscription->ends_at) }}
                </a>
              @endif

              @elseif (auth()->check() && auth()->id() != $user->id && $user->free_subscription == 'yes' && $user->updates()->count() != 0)
                <a href="javascript:void(0);" data-toggle="modal" data-target="#subscriptionFreeForm" class="btn btn-primary btn-profile mr-1">
                  <i class="feather icon-user-plus mr-1"></i> {{trans('general.subscribe_for_free')}}
                </a>
              @elseif (auth()->guest() && $user->updates()->count() != 0)
                <a href="{{url('login')}}" data-toggle="modal" data-target="#loginFormModal" class="btn btn-primary btn-profile mr-1">
                  @if ($user->free_subscription == 'yes')
                    <i class="feather icon-user-plus mr-1"></i> {{trans('general.subscribe_for_free')}}
                  @else
                  <i class="feather icon-unlock mr-1"></i> {{trans('general.subscribe_month', ['price' => Helper::amountFormatDecimal($user->plan('monthly', 'price'))])}}
                @endif
                </a>
            @endif

            @endif

            @if (auth()->check() && auth()->id() != $user->id && $user->updates()->count() <> 0 && $settings->disable_tips == 'off')
              <a href="javascript:void(0);" data-toggle="modal" title="{{trans('general.tip')}}" data-target="#tipForm" class="btn btn-google btn-profile mr-1" data-cover="{{Helper::getFile(config('path.cover').$user->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$user->avatar)}}" data-name="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" data-userid="{{$user->id}}">
                 {{trans('general.tip')}}
              </a>
            @elseif (auth()->guest() && $user->updates()->count() <> 0)
              <a href="{{url('login')}}" data-toggle="modal" data-target="#loginFormModal" class="btn btn-google btn-profile mr-1" title="{{trans('general.tip')}}">
                 {{trans('general.tip')}}
              </a>
            @endif

            @if (auth()->guest() && $user->verified_id == 'yes' || auth()->check() && auth()->id() != $user->id && $user->verified_id == 'yes')
              <button @guest data-toggle="modal" data-target="#loginFormModal" @else id="sendMessageUser" @endguest data-url="{{url('messages/'.$user->id, $user->username)}}" title="{{trans('general.message')}}" class="btn btn-google btn-profile mr-1">
               <span>Enviar mensagem</span>
              </button>
            @endif

            @if ($user->verified_id == 'yes')
            <!-- Share modal -->
          <div class="modal fade share-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
          		<div class="modal-content">
                <div class="modal-header border-bottom-0">
                  <button type="button" class="close close-inherit" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="bi bi-x-lg"></i></span>
                  </button>
                </div>
                <div class="modal-body">

                  @if ($settings->generate_qr_code)
                  <div class="d-block w-100 text-center mb-5" id="QrCode">
                    <img class="mb-2" src="{{ Helper::qrCode(url($user->username).Helper::referralLink()) }}" width="300" height="300" />

                    <div class="d-block w-100 text-center">
                      <a class="btn btn-primary" href="{{ Helper::qrCode(url($user->username).Helper::referralLink()) }}" download="QR {{ '@'.$user->username }}">
                      <i class="bi-download mr-1"></i>  {{ trans('general.download') }}
                      </a>
                    </div>
                  </div>
                @endif

          				<div class="container-fluid">
          					<div class="row">
          						<div class="col-md-4 col-6 mb-3">
          							<a href="https://www.facebook.com/sharer/sharer.php?u={{url($user->username).Helper::referralLink()}}" title="Facebook" target="_blank" class="social-share text-muted d-block text-center h6">
          								<i class="fab fa-facebook-square facebook-btn"></i>
          								<span class="btn-block mt-3">Facebook</span>
          							</a>
          						</div>
          						<div class="col-md-4 col-6 mb-3">
          							<a href="https://twitter.com/intent/tweet?url={{url($user->username).Helper::referralLink()}}&text={{ e( $user->hide_name == 'yes' ? $user->username : $user->name ) }}" data-url="{{url($user->username)}}" class="social-share text-muted d-block text-center h6" target="_blank" title="Twitter">
          								<i class="fab fa-twitter twitter-btn"></i> <span class="btn-block mt-3">Twitter</span>
          							</a>
          						</div>
          						<div class="col-md-4 col-6 mb-3">
          							<a href="whatsapp://send?text={{url($user->username).Helper::referralLink()}}" data-action="share/whatsapp/share" class="social-share text-muted d-block text-center h6" title="WhatsApp">
          								<i class="fab fa-whatsapp btn-whatsapp"></i> <span class="btn-block mt-3">WhatsApp</span>
          							</a>
          						</div>

          						<div class="col-md-4 col-6 mb-3">
          							<a href="mailto:?subject={{ e( $user->hide_name == 'yes' ? $user->username : $user->name ) }}&amp;body={{url($user->username).Helper::referralLink()}}" class="social-share text-muted d-block text-center h6" title="{{trans('auth.email')}}">
          								<i class="far fa-envelope"></i> <span class="btn-block mt-3">{{trans('auth.email')}}</span>
          							</a>
          						</div>
          						<div class="col-md-4 col-6 mb-3">
          							<a href="sms://?body={{ trans('general.check_this') }} {{url($user->username).Helper::referralLink()}}" class="social-share text-muted d-block text-center h6" title="{{ trans('general.sms') }}">
          								<i class="fa fa-sms"></i> <span class="btn-block mt-3">{{ trans('general.sms') }}</span>
          							</a>
          						</div>
          						<div class="col-md-4 col-6 mb-3">
          							<a href="javascript:void(0);" id="btn_copy_url" class="social-share text-muted d-block text-center h6 link-share" title="{{trans('general.copy_link')}}">
          							<i class="fas fa-link"></i> <span class="btn-block mt-3">{{trans('general.copy_link')}}</span>
          						</a>
                      <input type="hidden" readonly="readonly" id="copy_link" class="form-control" value="{{url($user->username).Helper::referralLink()}}">
          					</div>
          					</div>

          				</div>
                </div>
              </div>
            </div>
          </div>
          @endif

          </div><!-- d-flex-user -->

          </div><!-- media-body -->
        </div><!-- media -->
 @if ($user->verified_id == 'yes') 
 @if (auth()->check()
            && auth()->id() == $user->id
            && ! $userPlanMonthlyActive
            && auth()->user()->free_subscription == 'no'
            )
        <div class="alert alert-danger mb-3">
                 <ul class="list-unstyled m-0">
                   <li>{{trans('general.alert_not_subscription')}} <a href="{{url('settings/subscription')}}" class="text-white link-border">{{trans('general.activate')}}</a></li>
                 </ul>
               </div>
               @endif @endif

        @if ($user->verified_id == 'yes')
        <ul class="nav nav-profile justify-content-center nav-fill">

          <li class="nav-link @if (request()->path() == $user->username)active @endif ">
              <a href="{{request()->path() == $user->username ? 'javascript:;' : url($user->username)}}" title="{{trans('general.posts')}}"><i class="feather icon-file-text"></i> <span class="d-lg-inline-block">{{$user->updates()->count()}} {{trans('general.posts')}}</span></a>
            </li>

            <li class="nav-link @if (request()->path() == $user->username.'/photos')active @endif ">
              <a href="{{request()->path() == $user->username.'/photos' ? 'javascript:;' : url($user->username, 'photos')}}" title="{{trans('general.photos')}}"><i class="feather icon-image"></i> <span class="d-lg-inline-block ">{{$user->media()->where('media.image', '<>', '')->count()}} {{trans('general.photos')}}</span></a>
            </li>

            <li class="nav-link @if (request()->path() == $user->username.'/videos')active @endif ">
              <a href="{{request()->path() == $user->username.'/videos' ? 'javascript:;' : url($user->username, 'videos')}}" title="{{trans('general.video')}}"><i class="feather icon-video"></i> <span class="d-lg-inline-block">{{$user->media()->where('media.video', '<>', '')->orWhere('media.video_embed', '<>', '')->where('media.user_id', $user->id)->count()}} {{trans('general.videos')}}</span></a>
              </li>

        </ul>
      @endif

      </div><!-- col-lg-12 -->
    </div><!-- row -->
  </div><!-- container -->

  @if ($user->verified_id == 'yes' && request('media') != 'shop')
  <div class="container py-4 pb-5">
    <div class="row">
      <div class="col-lg-4 mb-3">
          
        <div class="card mb-3 rounded-large shadow-large">
          <div class="card-body">
            <h6 style="font-size:14px!important;" class="card-title">{{ trans('users.about_me') }}</h6>
            <p class="card-text position-relative">

              @if ($likeCount != 0 || $subscriptionsActive != 0)
              <span class="btn-block">
                @if ($likeCount != 0)
                <small class="mr-2"><i class="far fa-heart mr-1"></i> {{ $likeCount }} {{ __('general.likes') }}</small>
                @endif

                @if ($subscriptionsActive != 0 && $user->hide_count_subscribers == 'no')
                    <small><i class="feather icon-users mr-1"></i> {{ Helper::formatNumber($subscriptionsActive) }} {{ trans_choice('general.subscribers', $subscriptionsActive) }}</small>
                @endif
              </span>
            @endif

              @if (isset($user->country()->country_name) && $user->hide_my_country == 'no')
              <small class="btn-block">
                <i class="feather icon-map-pin mr-1"></i> @if ($user->categories_id != '0' && $user->categories_id != '' && $user->verified_id == 'yes') @foreach (Categories::where('mode','on')->orderBy('name')->get() as $category)
                @foreach ($categories as $categoryKey)
                  @if ($categoryKey == $category->id)
                  <a style=" color: #5f5f5f!important; " href="{{url('category', $category->slug)}}">
                    {{ Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug) : $category->name }},
                  </a>
                @endif
                @endforeach
            @endforeach @endif {{$user->country()->country_name}}
              </small>
              @endif

              <small class="btn-block m-0 mb-1">
                <i class="far fa-user-circle mr-1"></i> {{ trans('general.member_since') }} {{ Helper::formatDate($user->date) }}
              </small>
            </p>

              @if ($user->website != '')
                <div class="d-block mb-1 text-truncate">
                  <a style=" font-size: 12px; color: #5f5f5f!important; " href="{{$user->website}}" title="{{$user->website}}" target="_blank" class="text-muted share-btn-user"><i class="fa fa-link mr-1"></i> {{Helper::removeHTPP($user->website)}}</a>
                </div>
              @endif

              @if ($user->facebook != '')
                <a href="{{$user->facebook}}" title="{{$user->facebook}}" target="_blank" class="text-muted share-btn-user"><i class="bi bi-facebook mr-2"></i></a>
              @endif

              @if ($user->twitter != '')
                <a href="{{$user->twitter}}" title="{{$user->twitter}}" target="_blank" class="text-muted share-btn-user"><i class="fab fa-twitter mr-2"></i></a>
              @endif

              @if ($user->instagram != '')
                <a href="{{$user->instagram}}" title="{{$user->instagram}}" target="_blank" class="text-muted share-btn-user"><i class="fab fa-instagram mr-2"></i></a>
              @endif

              @if ($user->youtube != '')
                <a href="{{$user->youtube}}" title="{{$user->youtube}}" target="_blank" class="text-muted share-btn-user"><i class="fab fa-youtube mr-2"></i></a>
              @endif

              @if ($user->snapchat != '')
                <a href="{{$user->snapchat}}" title="{{$user->snapchat}}" target="_blank" class="text-muted share-btn-user"><i class="bi bi-snapchat mr-2"></i></a>
              @endif

              @if ($user->tiktok != '')
                <a href="{{$user->tiktok}}" title="{{$user->tiktok}}" target="_blank" class="text-muted share-btn-user"><i class="bi bi-tiktok mr-2"></i></a>
              @endif
              
              @if ($user->spotify != '')
                <a href="{{$user->spotify}}" title="{{$user->spotify}}" target="_blank" class="text-muted share-btn-user"><i class="bi bi-spotify mr-2"></i></a>
              @endif

          </div><!-- card-body -->
        </div><!-- card -->

        <div class="d-lg-block d-none">
        @include('includes.footer-tiny')
      </div>

        
      </div><!-- col-lg-4 -->

      <div class="col-lg-8 wrap-post">

        @if (auth()->check()
            && auth()->id() == $user->id
            && request()->path() == $user->username
            && auth()->user()->verified_id != 'reject'
            ) @if (auth()->check()
            && auth()->id() == $user->id
            && $userPlanMonthlyActive
            && auth()->user()->free_subscription == 'yes'
            )
          @include('includes.form-post')
        @endif @endif

        @if ($updates->count() == 0 && $findPostPinned->count() == 0)
            <div class="grid-updates"></div>

            <div class="my-5 text-center no-updates">
              <span class="btn-block mb-3">
                <i class="fa fa-photo-video ico-no-result"></i>
              </span>
            <h4 class="font-weight-light">{{trans('general.no_posts_posted')}}</h4>
            </div>
          @else

            @php
              $counterPosts = ($updates->total() - $settings->number_posts_show);
            @endphp

            @if (! request()->get('sort') && $updates->total() > $settings->number_posts_show || request()->get('sort'))
            <div class="w-100 d-flex @if (request()->get('sort') && request()->get('sort') <> 'oldest')justify-content-between @else justify-content-end @endif align-items-center mb-3 px-lg-0 px-3">

              @if (request()->get('sort') && request()->get('sort') <> 'oldest')
                <small>
                  <strong>{{ __('general.results') }} {{ $updates->total() }}</strong>
                </small>
              @endif

              @if (auth()->guest() && $user->posts_privacy || auth()->check())
              <div>
                <i class="bi-filter-right mr-1"></i>

                <select class="@if ($settings->button_style == 'rounded')rounded-pill @endif custom-select w-auto px-4" id="filter">
                    <option @if (! request()->get('sort')) selected @endif value="{{url()->current()}}{{ request()->get('q') ? '?q='.str_replace('#', '%23', request()->get('q')) : null }}">{{trans('general.latest')}}</option>
                    <option @if (request()->get('sort') == 'oldest') selected @endif value="{{url()->current()}}{{ request()->get('q') ? '?q='.str_replace('#', '%23', request()->get('q')).'&' : '?' }}sort=oldest">{{trans('general.oldest')}}</option>
                    <option @if (request()->get('sort') == 'unlockable') selected @endif value="{{url()->current()}}{{ request()->get('q') ? '?q='.str_replace('#', '%23', request()->get('q')).'&' : '?' }}sort=unlockable">{{trans('general.unlockable')}}</option>
                    <option @if (request()->get('sort') == 'free') selected @endif value="{{url()->current()}}{{ request()->get('q') ? '?q='.str_replace('#', '%23', request()->get('q')).'&' : '?' }}sort=free">{{trans('general.free')}}</option>
                  </select>
              </div>
              @endif

          </div>
        @endif

        @if (auth()->guest() && ! $user->posts_privacy)
        <div class="my-5 text-center no-updates">
          <span class="btn-block mb-3">
            <i class="fa fa-lock ico-no-result"></i>
          </span>
        <h4 class="font-weight-light">{{trans('general.alert_posts_privacy', ['user' => '@'.$user->username])}}</h4>
        </div>

        @else

        <div class="grid-updates position-relative" id="updatesPaginator">
          @if ($findPostPinned && ! request('media'))
            @include('includes.updates', ['updates' => $findPostPinned])
          @endif

          @include('includes.updates')
        </div>
        @endif   

          @endif
      </div>
      </div><!-- row -->
    </div><!-- container -->
  @endif

  @if ($user->verified_id == 'yes' && request('media') == 'shop')
    <div class="container py-5">

      @if ($userProducts->count() != 0)
      <div class="@if (auth()->check() && auth()->user()->verified_id == 'yes' && $user->id == auth()->id())d-flex justify-content-between align-items-center @else d-block @endif mb-3 text-right">

        @if (auth()->check() && auth()->user()->verified_id == 'yes' && $user->id == auth()->id())
        <div>
          @if ($settings->digital_product_sale && ! $settings->custom_content)
            <a class="btn btn-primary" href="{{ url('add/product') }}">
              <i class="bi-plus"></i> <span class="d-lg-inline-block d-none">{{ __('general.add_product') }}</span>
            </a>

          @elseif (! $settings->digital_product_sale && $settings->custom_content)
            <a class="btn btn-primary" href="{{ url('add/custom/content') }}">
              <i class="bi-plus"></i> <span class="d-lg-inline-block d-none">{{ __('general.add_custom_content') }}</span>
            </a>

          @else
            <a class="btn btn-primary" href="#" data-toggle="modal" data-target="#addItemForm">
              <i class="bi-plus"></i> <span class="d-lg-inline-block d-none">{{ __('general.add_new') }}</span>
            </a>
          @endif
        </div>
      @endif

        <div>
          <select class="ml-2 custom-select mb-2 mb-lg-0 w-auto" id="filter">
              <option @if (! request()->get('sort')) selected @endif value="{{url($user->username).'/shop'}}">{{trans('general.latest')}}</option>
              <option @if (request()->get('sort') == 'oldest') selected @endif value="{{url($user->username).'/shop?sort=oldest'}}">{{trans('general.oldest')}}</option>
              <option @if (request()->get('sort') == 'priceMin') selected @endif value="{{url($user->username).'/shop?sort=priceMin'}}">{{trans('general.lowest_price')}}</option>
              <option @if (request()->get('sort') == 'priceMax') selected @endif value="{{url($user->username).'/shop?sort=priceMax'}}">{{trans('general.highest_price')}}</option>
              @if ($settings->physical_products)
              <option @if (request()->get('sort') == 'physical') selected @endif value="{{url($user->username).'/shop?sort=physical'}}">{{trans('general.physical_products')}}</option>
              @endif
              <option @if (request()->get('sort') == 'digital') selected @endif value="{{url($user->username).'/shop?sort=digital'}}">{{trans('general.digital_products')}}</option>
              <option @if (request()->get('sort') == 'custom') selected @endif value="{{url($user->username).'/shop?sort=custom'}}">{{trans('general.custom_content')}}</option>
            </select>

            @if ($shopCategories->count())
              <select class="ml-2 custom-select mb-2 mb-lg-0 w-auto filter">
                  <option @if (! request()->get('cat')) selected @endif value="{{url($user->username, 'shop')}}">{{trans('general.all_categories')}}</option>

                    @foreach ($shopCategories as $category)
                      <option @if (request()->get('cat') == $category->slug) selected @endif value="{{url($user->username, 'shop')}}{{ '?cat='.$category->slug }}">
                        {{ Lang::has('shop-categories.' . $category->slug) ? __('shop-categories.' . $category->slug) : $category->name }}
                      </option>
                    @endforeach

                </select>
            @endif
        </div>
      </div>
    @endif

      <div class="row">

        @if ($userProducts->count() != 0)

          @foreach ($userProducts as $product)
          <div class="col-md-4 mb-4">
            @include('shop.listing-products')
          </div><!-- end col-md-4 -->
          @endforeach

          @if ($userProducts->hasPages())
            <div class="w-100 d-block">
              {{ $userProducts->onEachSide(0)->appends(['sort' => request('sort')])->links() }}
            </div>
          @endif

        @else

          <div class="my-5 text-center no-updates w-100">
            <span class="btn-block mb-3">
              <i class="feather icon-shopping-bag ico-no-result"></i>
            </span>
          <h4 class="font-weight-light">{{trans('general.no_results_found')}}</h4>

        @if (auth()->check() && auth()->user()->verified_id == 'yes' && auth()->id() == $user->id)
          <div class="mt-3">
            @if ($settings->digital_product_sale && ! $settings->custom_content && ! $settings->physical_products)
              <a class="btn btn-primary" href="{{ url('add/product') }}">
                <i class="bi-plus"></i> {{ __('general.add_product') }}
              </a>

            @elseif (! $settings->digital_product_sale && $settings->custom_content && ! $settings->physical_products)
              <a class="btn btn-primary" href="{{ url('add/custom/content') }}">
                <i class="bi-plus"></i> {{ __('general.add_custom_content') }}
              </a>

            @elseif (! $settings->digital_product_sale && $settings->physical_products && ! $settings->custom_content)
              <a class="btn btn-primary" href="{{ url('add/physical/product') }}">
                <i class="bi-plus"></i> {{ __('general.add_physical_product') }}
              </a>

            @else
              <a class="btn btn-primary" href="#" data-toggle="modal" data-target="#addItemForm">
                <i class="bi-plus"></i> {{ __('general.add_new') }}
              </a>
            @endif
          </div>
        @endif

          </div>

        @endif
      </div>
    </div><!-- container -->

    @includeWhen(auth()->check() && auth()->user()->verified_id == 'yes', 'shop.modal-add-item')

  @endif


    @if (auth()->check() && auth()->id() != $user->id)
    <div class="modal fade modalReport" id="reportCreator" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-danger modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title font-weight-light" id="modal-title-default"><i class="fas fa-flag mr-1"></i> {{trans('general.report_user')}}</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <i class="fa fa-times"></i>
            </button>
          </div>
     <!-- form start -->
     <form method="POST" action="{{url('report/creator', $user->id)}}" enctype="multipart/form-data">
        <div class="modal-body">
          @csrf
          <!-- Start Form Group -->
          <div class="form-group">
            <label>{{trans('admin.please_reason')}}</label>
              <select name="reason" class="form-control custom-select">
               <option value="spoofing">{{trans('admin.spoofing')}}</option>
                  <option value="copyright">{{trans('admin.copyright')}}</option>
                  <option value="privacy_issue">{{trans('admin.privacy_issue')}}</option>
                  <option value="violent_sexual">{{trans('admin.violent_sexual_content')}}</option>
                  <option value="spam">{{trans('general.spam')}}</option>
                  <option value="fraud">{{trans('general.fraud')}}</option>
                  <option value="under_age">{{trans('general.under_age')}}</option>
                </select>
                </div><!-- /.form-group-->
            </div><!-- Modal body -->

           <div class="modal-footer">
             <button type="button" class="btn border text-white" data-dismiss="modal">{{trans('admin.cancel')}}</button>
             <button type="submit" class="btn btn-xs btn-white sendReport ml-auto"><i></i> {{trans('general.report_user')}}</button>
           </div>

           </form>
          </div><!-- Modal content -->
        </div><!-- Modal dialog -->
      </div><!-- Modal reportCreator -->
    @endif

    @if (auth()->check() && auth()->id() != $user->id && ! $checkSubscription  && $user->verified_id == 'yes')

    @if ($user->free_subscription == 'no')
    <div class="modal fade" id="subscriptionForm" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white shadow border-0">
              <div class="card-body px-lg-5 py-lg-5 position-relative">
                <div class="text-muted text-center mb-3 position-relative modal-offset">
                  <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" width="100" alt="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" class="avatar-modal rounded-circle mb-1">
                </div>

                @if ($updates->total() == 0 && $findPostPinned->count() == 0)
                  <div class="alert alert-warning fade show small" role="alert">
                    <i class="fa fa-exclamation-triangle mr-1"></i> {{ $user->first_name }} {{ trans('general.not_posted_any_content') }}
                  </div>
                @endif

                <form method="post" action="{{url('buy/subscription')}}" id="formSubscription">
                  @csrf

                  <input type="hidden" name="id" value="{{$user->id}}"  />
                  <input name="interval" value="monthly" id="plan-monthly" class="d-none" type="radio">

                  @foreach ($plans as $plan)
                    <input name="interval" value="{{ $plan->interval }}" id="plan-{{ $plan->interval }}" class="d-none" type="radio">
                  @endforeach

                  @foreach ($allPayment as $payment)

                    @php

                    if ($payment->recurrent == 'no') {
                      $recurrent = '<br><small>'.trans('general.non_recurring').'</small>';
                    } else if ($payment->id == 1) {
                      $recurrent = '<br><small>'.trans('general.redirected_to_paypal_website').'</small>';
                    } else {
                      $recurrent = '<br><small>'.trans('general.automatically_renewed').' ('.$payment->name.')</small>';
                    }

                    if ($payment->type == 'card' ) {
                      $paymentName = '<i class="far fa-credit-card mr-1"></i> '.trans('general.debit_credit_card').$recurrent;
                    } else if ($payment->id == 1) {
                      $paymentName = '<img src="'.url('public/img/payments', auth()->user()->dark_mode == 'off' ? $payment->logo : 'paypal-white.png').'" width="70"/> <small class="w-100 d-block">'.trans('general.redirected_to_paypal_website').'</small>';
                    } else {
                      $paymentName = '<img src="'.url('public/img/payments', $payment->logo).'" width="70"/>'.$recurrent;
                    }

                    @endphp

                    <div class="custom-control custom-radio mb-3">
                      <input checked name="payment_gateway" value="{{$payment->id}}" id="radio{{$payment->id}}" @if ($allPayment->count() == 1 && Helper::userWallet('balance') == 0) checked @endif class="custom-control-input" type="radio">
                      <label class="custom-control-label" for="radio{{$payment->id}}">
                        <span><strong>{!!$paymentName!!}</strong></span>
                      </label>
                    </div>

                    @if ($payment->name == 'Stripe' && ! auth()->user()->pm_type != '')
                      <div id="stripeContainer" class="@if ($allPayment->count() == 1 && $payment->name == 'Stripe')d-block @else display-none @endif">
                      <a href="{{ url('settings/payments/card') }}" class="btn btn-secondary btn-sm mb-3 w-100">
                        <i class="far fa-credit-card mr-2"></i>
                        {{ trans('general.add_payment_card') }}
                      </a>
                      </div>
                    @endif

                    @if ($payment->name == 'Paystack' && ! auth()->user()->paystack_authorization_code)
                      <div id="paystackContainer" class="@if ($allPayment->count() == 1 && $payment->name == 'Paystack')d-block @else display-none @endif">
                      <a href="{{ url('my/cards') }}" class="btn btn-secondary btn-sm mb-3 w-100">
                        <i class="far fa-credit-card mr-2"></i>
                        {{ trans('general.add_payment_card') }}
                      </a>
                      </div>
                    @endif

                  @endforeach

                  @if ($settings->disable_wallet == 'on' && Helper::userWallet('balance') != 0 || $settings->disable_wallet == 'off')
                  <div class="custom-control custom-radio mb-3">
                    <input checked name="payment_gateway" @if (Helper::userWallet('balance') == 0) disabled @endif value="wallet" id="radio0" class="custom-control-input" type="radio">
                    <label class="custom-control-label" for="radio0">
                      <span>
                        <strong>
                        <span class="w-100 d-block font-weight-light">
                          {{ __('general.available_balance') }}: <span class="font-weight-bold mr-1">{{Helper::userWallet()}}</span>

                          @if (Helper::userWallet('balance') != 0 && $settings->wallet_format != 'real_money')
                            <i class="bi bi-info-circle text-muted" data-toggle="tooltip" data-placement="top" title="{{Helper::equivalentMoney($settings->wallet_format)}}"></i>
                          @endif

                          @if (Helper::userWallet('balance') == 0)
                          <a href="{{ url('my/wallet') }}" class="link-border">{{ __('general.recharge') }}</a>
                        @endif
                        </span>
                        <span class="w-100 d-block small">{{ trans('general.automatically_renewed_wallet') }}</span>
                      </strong>
                      </span>
                    </label>
                  </div>
                @endif

                  <div class="alert alert-danger display-none" id="error">
                      <ul class="list-unstyled m-0" id="showErrors"></ul>
                    </div>

                  @if (auth()->user()->isTaxable()->count())
                @endif

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-4 w-100 subscriptionBtn" onclick="$('#plan-monthly').trigger('click');">
                      <i></i> {{trans('general.subscribe_month', ['price' => Helper::amountFormatDecimal($user->plan('monthly', 'price'), true)])}}
                    </button>
                    
                    @foreach ($plans as $plan)
                          <button type="submit" class="btn btn-primary mt-2 w-100 subscriptionBtn" onclick="$('#plan-{{$plan->interval}}').trigger('click');">
                            <i></i> {{trans('general.subscribe_'.$plan->interval, ['price' => Helper::amountFormatDecimal($plan->price, true)])}}
                          </button>

                          @if (Helper::calculateSubscriptionDiscount($plan->interval, $user->plan('monthly', 'price'), $plan->price) > 0)
                          @endif

                        @endforeach

                    <div class="w-100 mt-2">
                      <button type="button" class="btn e-none p-0" data-dismiss="modal">{{trans('admin.cancel')}}</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- End Modal Subscription -->
    @endif

    <!-- Subscription Free -->
    <div class="modal fade" id="subscriptionFreeForm" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white shadow border-0">
              <div class="card-body px-lg-5 py-lg-5 position-relative">

                <div class="text-muted text-center mb-3 position-relative modal-offset">
                  <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" width="100" alt="{{$user->hide_name == 'yes' ? $user->username : $user->name}}" class="avatar-modal rounded-circle mb-1">
                </div>

                @if ($updates->total() == 0 && $findPostPinned->count() == 0)
                  <div class="alert alert-warning fade show small" role="alert">
                    <i class="fa fa-exclamation-triangle mr-1"></i> {{ $user->first_name }} {{ trans('general.not_posted_any_content') }}
                  </div>
                @endif

                <div class="w-100 text-center">
                  <a href="javascript:void(0);" data-id="{{ $user->id }}" id="subscribeFree" class="btn btn-primary btn-profile mr-1">
                    <i class="feather icon-user-plus mr-1"></i> {{trans('general.subscribe_for_free')}}
                  </a>
                  <div class="w-100 mt-2">
                    <button type="button" class="btn e-none p-0" data-dismiss="modal">{{trans('admin.cancel')}}</button>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- End Modal Subscription Free -->
  @endif
@endsection

@section('javascript')

@if (auth()->check() && auth()->id() == $user->id)
<script src="{{ asset('public/js/upload-avatar-cover.js') }}?v={{$settings->version}}"></script>
@endif

<script type="text/javascript">

@auth
$('.subsCCBill').on('click', function() {

  $(this).blur();
  var expiration = $(this).attr('data-expiration');
  swal({
    html: true,
    title: "{{ trans('general.unsubscribe') }}",
    text: "{!! trans('general.cancel_subscription_ccbill', ['ccbill' => '<a href=\'https://support.ccbill.com/\' target=\'_blank\'>https://support.ccbill.com</a>']) !!} " + expiration,
    type: "info",
    confirmButtonText: "{{ trans('users.ok') }}"
    });
});
@endauth

 @if (session('noty_error'))
   		swal({
   			title: "{{ trans('general.error_oops') }}",
   			text: "{{ trans('general.already_sent_report') }}",
   			type: "error",
   			confirmButtonText: "{{ trans('users.ok') }}"
   			});
  		 @endif

  @if (session('noty_success'))
   		swal({
   			title: "{{ trans('general.thanks') }}",
   			text: "{{ trans('general.reported_success') }}",
   			type: "success",
   			confirmButtonText: "{{ trans('users.ok') }}"
   			});
  @endif

  $('.dropdown-menu.d-menu').on({
      "click":function(e){
        e.stopPropagation();
      }
  });

  @if (session('subscription_success'))
     swal({
       html:true,
       title: "{{ trans('general.congratulations') }}",
       text: "{!! session('subscription_success') !!}",
       type: "success",
       confirmButtonText: "{{ trans('users.ok') }}"
       });
    @endif

    @if (session('subscription_cancel'))
     swal({
       title: "{{ trans('general.canceled') }}",
       text: "{{ session('subscription_cancel') }}",
       type: "error",
       confirmButtonText: "{{ trans('users.ok') }}"
       });
    @endif

    @if (session('success_verify'))
    	swal({
    		title: "{{ trans('general.welcome') }}",
    		text: "{{ trans('users.account_validated') }}",
    		type: "success",
    		confirmButtonText: "{{ trans('users.ok') }}"
    		});
    	 @endif

    	 @if (session('error_verify'))
    	swal({
    		title: "{{ trans('general.error_oops') }}",
    		text: "{{ trans('users.code_not_valid') }}",
    		type: "error",
    		confirmButtonText: "{{ trans('users.ok') }}"
    		});
    	 @endif
</script>
@endsection
@php session()->forget('subscription_cancel') @endphp
@php session()->forget('subscription_success') @endphp
