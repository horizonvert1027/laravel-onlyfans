@extends('layouts.app')

@section('title') {{trans('general.privacy_security')}} -@endsection

@section('content')
<section class="section section-sm">
    <div class="container">
      <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-8 py-5">
        </div>
      </div>
      <div class="row">

        @include('includes.cards-settings')

        <div class="col-md-6 col-lg-9 mb-5 mb-lg-0">
            
            <h2 class="mb-4 font-montserrat">{{trans('general.privacy_security')}}</h2>

          @if (session('status'))
                  <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<span aria-hidden="true">×</span>
                			</button>

                    {{ session('status') }}
                  </div>
                @endif

          @include('errors.errors-forms')

          @if (auth()->user()->verified_id == 'yes')

            <h5>{{ __('general.privacy') }}</h5>

            <form method="POST" action="{{ url('privacy/security') }}">

              @csrf

              <div class="form-group">
                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="hide_profile" value="yes" @if (auth()->user()->hide_profile == 'yes') checked @endif id="customSwitch1">
                    <label class="custom-control-label switch" for="customSwitch1">{{ __('general.hide_profile') }} {{ __('general.info_hide_profile') }}</label>
                  </div>
                </div>

                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="hide_last_seen" value="yes" @if (auth()->user()->hide_last_seen == 'yes') checked @endif id="customSwitch2">
                    <label class="custom-control-label switch" for="customSwitch2">{{ __('general.hide_last_seen') }}</label>
                  </div>
                </div>

                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="active_status_online" value="yes" @if (auth()->user()->active_status_online == 'yes') checked @endif id="customSwitch6">
                    <label class="custom-control-label switch" for="customSwitch6">{{ __('general.active_status_online') }}</label>
                  </div>
                </div>

                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="hide_count_subscribers" value="yes" @if (auth()->user()->hide_count_subscribers == 'yes') checked @endif id="customSwitch3">
                    <label class="custom-control-label switch" for="customSwitch3">{{ __('general.hide_count_subscribers') }}</label>
                  </div>
                </div>

                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="hide_my_country" value="yes" @if (auth()->user()->hide_my_country == 'yes') checked @endif id="customSwitch4">
                    <label class="custom-control-label switch" for="customSwitch4">{{ __('general.hide_my_country') }}</label>
                  </div>
                </div>

                <div class="btn-block mb-4">
                  <div class="custom-control custom-switch custom-switch-lg">
                    <input type="checkbox" class="custom-control-input" name="posts_privacy" value="1" @if (auth()->user()->posts_privacy) checked @endif id="posts_privacy">
                    <label class="custom-control-label switch" for="posts_privacy">{{ __('general.posts_privacy') }}</label>
                  </div>
                </div>

              </div><!-- End form-group -->

              <button class="btn mb-5 btn-1 btn-success" onClick="this.form.submit(); this.disabled=true; this.innerText='{{ __('general.please_wait')}}';" type="submit">{{ __('general.save_changes')}}</button>

            </form>
          @endif
 
        </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection
