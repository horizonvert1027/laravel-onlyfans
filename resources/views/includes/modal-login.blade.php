<div class="modal fade" id="loginFormModal" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-sm modal-login" role="document">
		<div class="modal-content">
			<div class="modal-body p-0">
					<div class="card-body px-lg-5 py-lg-5 position-relative">

						<h6 class="modal-title text-center mb-3" id="loginRegisterContinue">{{ __('general.login_continue') }}</h6>

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

								@if (request()->route()->named('profile'))
									<input type="hidden" name="isProfile" value="{{ $user->username }}">
								@endif

								<input type="hidden" name="isModal" id="isModal" value="true">

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

								<div class="w-100 mt-2">
									<button type="button" class="btn e-none p-0" data-dismiss="modal">{{ __('admin.cancel') }}</button>
								</div>
							</div>
						</form>

						@if ($settings->captcha == 'on')
							<small class="btn-block text-center mt-3">{{trans('auth.protected_recaptcha')}} <a href="https://policies.google.com/privacy" target="_blank">{{trans('general.privacy')}}</a> - <a href="https://policies.google.com/terms" target="_blank">{{trans('general.terms')}}</a></small>
						@endif

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

					</div><!-- ./ card-body -->
				</div>
			</div>
		</div>
	</div>
