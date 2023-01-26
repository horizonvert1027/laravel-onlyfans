@extends('layouts.app')

@section('title') <title>{{ auth()->check() && User::notificationsCount() ? '('.User::notificationsCount().') ' : '' }}@section('title')@show {{$settings->title.' - '.__('seo.slogan')}}</title> @endsection

 @section('css')
  <meta property="og:title" content="Oprivado - Conecte-se com os seus fãs!"/>
    <meta name="twitter:title" content="Oprivado - Conecte-se com os seus fãs!" />
    <meta property="og:site_name" content="Oprivado - Conecte-se com os seus fãs!"/>
 @endsection

@section('content')
  <div class="jumbotron m-0 fundo">
    <div class="container pt-lg-md">
      <div class="row justify-content-center">
        <div class="col-lg-5">

          <div class="card bg-white borda border-0">

            <div class="card-body px-lg-5 py-lg-5 pt-4">

<div class="text-center d-block w-100">
            <img src="{{url('public/img', $settings->logo_2)}}" alt="{{$settings->title}}" width="50%" class="logo align-baseline mb-1" />
          </div>
          
              <span class="btn-block text-center pb-4">{{ trans('general.title_home_login') }}</span>

        @if (session('login_required'))
    			<div class="alert alert-danger" id="dangerAlert">
                		<i class="fa fa-exclamation-triangle"></i> {{trans('auth.login_required')}}
                		</div>
                	@endif

              @if ($settings->facebook_login == 'on' || $settings->google_login == 'on' || $settings->twitter_login == 'on')
              <div class="mb-2 w-100">

                @if ($settings->facebook_login == 'on')
                  <a href="{{url('oauth/facebook')}}" class="btn btn-facebook auth-form-btn flex-grow mb-2 w-100">
                    <i class="fab fa-facebook mr-2"></i> <span class="loginRegisterWith">{{ __('auth.login_with') }}</span> Facebook
                  </a>
                @endif

                @if ($settings->twitter_login == 'on')
                <a href="{{url('oauth/twitter')}}" class="btn btn-twitter auth-form-btn mb-2 w-100">
                  <i class="fab fa-twitter mr-2"></i> <span class="loginRegisterWith">{{ __('auth.login_with') }}</span> Twitter
                </a>
              @endif

                  @if ($settings->google_login == 'on')
                  <a href="{{url('oauth/google')}}" class="btn btn-google auth-form-btn flex-grow w-100">
                    <img src="{{ url('public/img/google.svg') }}" class="mr-2" width="18" height="18"> <span class="loginRegisterWith">{{ __('auth.login_with') }}</span> Google
                  </a>
                @endif
                </div>

                @if (! $settings->disable_login_register_email)
                  <small class="btn-block text-center my-3 text-uppercase or">{{__('general.or')}}</small>
                @endif

              @endif

          @if (! $settings->disable_login_register_email)
              <form method="POST" action="{{ route('login') }}" data-url-login="{{ route('login') }}" data-url-register="{{ route('register') }}" id="formLoginRegister" enctype="multipart/form-data">
                  @csrf

                  <input type="hidden" name="return" value="{{ count($errors) > 0 ? old('return') : url()->previous() }}">

                  @if ($settings->captcha == 'on')
                    @captcha
                  @endif

                  <div class="form-group mb-3 display-none" id="full_name">
                    <div class="input-group input-group-alternative">
                      <input class="form-control"  value="{{ old('name')}}" placeholder="{{trans('auth.full_name')}}" name="name" type="text">
                    </div>
                  </div>

                <div class="form-group mb-3 display-none" id="email">
                  <div class="input-group input-group-alternative">
                    <input class="form-control" value="{{ old('email')}}" placeholder="{{trans('auth.email')}}" name="email" type="text">
                  </div>
                </div>

                <div class="form-group mb-3" id="username_email">
                  <div class="input-group input-group-alternative">
                    
                    <input class="form-control" value="{{ old('username_email') }}" placeholder="{{ trans('auth.username_or_email') }}" name="username_email" type="text">

                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative" id="showHidePassword">
                    <input name="password" type="password" class="form-control" placeholder="{{ trans('auth.password') }}">
                    <div class="input-group-append">
                      <span class="input-group-text c-pointer"><i class="feather icon-eye-off"></i></span>
                  </div>
                </div>
                <small class="form-text text-muted">
                  <a href="{{url('password/reset')}}" id="forgotPassword">
                    {{trans('auth.forgot_password')}}
                  </a>
                </small>
                </div>

                <div class="form-group d-none">
                  <div class="input-group mb-4">
                  
                  <select name="countries_id" class="form-control custom-select">
                    <option value="">{{trans('general.select_your_country')}}</option>
                        @foreach(  Countries::orderBy('country_name')->get() as $country )
                          <option id="{{$country->country_code}}" value="{{$country->id}}">{{ $country->country_name }}</option>
                          @endforeach
                        </select>
                        </div>
                  </div>

                <div class="custom-control custom-control-alternative custom-checkbox" id="remember">
                  <input class="custom-control-input" id=" customCheckLogin" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                  <label class="custom-control-label" for=" customCheckLogin">
                    <span>{{trans('auth.remember_me')}}</span>
                  </label>
                </div>

                <div class="alert alert-danger display-none mb-0 mt-3" id="errorLogin">
                    <ul class="list-unstyled m-0" id="showErrorsLogin"></ul>
                  </div>

                  <div class="alert alert-success display-none mb-0 mt-3" id="checkAccount"></div>

                <div class="text-center">
                  <button type="submit" id="btnLoginRegister" class="btn btn-primary mt-4 w-100"><i></i> {{trans('auth.login')}}</button>
                </div>
              </form>
              
              <small class="btn-block text-center mt-3">Ao continuar no site você concorda com os <a href="https://oprivado.com/p/termos" target="_blank">Termos</a> e <a href="https://oprivado.com/p/privacidade" target="_blank">Políticas</a> do Oprivado</small>

              @if ($settings->registration_active == '1')
              <div class="row mt-3">
                <div class="col-12 text-center">
                  <a href="javascript:void(0);" id="toggleLogin" data-not-account="{{trans('auth.not_have_account')}}" data-already-account="{{trans('auth.already_have_an_account')}}" data-text-login="{{trans('auth.login')}}" data-text-register="{{trans('auth.sign_up')}}">
                    <strong>{{trans('auth.not_have_account')}}</strong>
                  </a>
                </div>
              </div>
            @endif

          @else
            <div class="row mt-3">
              <div class="col-12 text-center">
                <a href="javascript:void(0);" id="toggleLogin" data-not-account="{{trans('auth.not_have_account')}}" data-already-account="{{trans('auth.already_have_an_account')}}" data-text-login="{{trans('auth.login')}}" data-text-register="{{trans('auth.sign_up')}}">
                  <strong>{{trans('auth.not_have_account')}}</strong>
                </a>
              </div>
            </div>
          @endif

            </div>
          </div>

        </div>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center py-3 px-3">
    @include('includes.footer-tiny')
    <small style="color:#999!important;" class="text-muted">&copy; 2022 - {{date('Y')}} {{$settings->title}} - {{__('emails.rights_reserved')}} </br> ANGOVITECH Platforms (SU) Lda. Luanda, Angola - NIF: 5001277014</small>
   
    <div style="margin-top:10px;"><a target="_blank" href="https://www.angovitech.com/"><img alt="Powered by ANGOVITECH Platforms (SU) Lda." title="Powered by ANGOVITECH Platforms (SU) Lda." src="https://oprivado.com/public/img/angovitechlogo.svg" width="85"></a></div>
  </div>
@endsection

@section('javascript')
<script type="text/javascript">
    $.ajax({
		url: "https://geolocation-db.com/jsonp",
		jsonpCallback: "callback",
		dataType: "jsonp",
		success: function( location ) {
			$('#'+location.country_code).attr('selected', 'selected');
		}
	});

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
