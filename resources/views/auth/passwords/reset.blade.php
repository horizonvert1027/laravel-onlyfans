@extends('layouts.app')

@section('css')
  <script type="text/javascript">
      var error_scrollelement = {{ count($errors) > 0 ? 'true' : 'false' }};
  </script>
@endsection

@section('content')
  <div class="jumbotron home m-0 fundo">
    <div class="container pt-lg-md">
      <div class="row justify-content-center">
        <div class="col-lg-5">
          <div class="card bg-white borda border-0">

            <div class="p-4">
              <h4 class="text-center mb-0 font-weight-bold">
                {{trans('auth.reset_password')}}
              </h4>
              <small class="btn-block text-center mt-2">{{ trans('auth.reset_pass_subtitle') }}</small>
            </div>

            <div class="card-body px-lg-5 py-lg-5">

              @if (session('status'))
                      <div class="alert alert-success">
                        {{ session('status') }}
                      </div>
                    @endif

              @include('errors.errors-forms')

              <form method="POST" action="{{url('password/reset')}}">
                  @csrf

                  @if($settings->captcha == 'on')
                    @captcha
                  @endif

                  <input type="hidden" name="token" value="{{$token}}">

                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <input class="form-control" value="{{ old('email')}}" placeholder="{{trans('auth.email')}}" name="email" required type="text">
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group input-group-alternative" id="showHidePassword">
                    <input name="password" type="password" class="form-control" required placeholder="{{trans('auth.password')}}">
                    <div class="input-group-append">
                      <span class="input-group-text c-pointer"><i class="feather icon-eye-off"></i></span>
                  </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <input name="password_confirmation" type="password" class="form-control" required placeholder="{{trans('auth.confirm_password')}}">
                  </div>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn btn-primary my-4 w-100">{{trans('auth.reset_password')}}</button>
                </div>
              </form>

              @if ($settings->captcha == 'on')
                <small class="btn-block text-center">{{trans('auth.protected_recaptcha')}} <a href="https://policies.google.com/privacy" target="_blank">{{trans('general.privacy')}}</a> - <a href="https://policies.google.com/terms" target="_blank">{{trans('general.terms')}}</a></small>
              @endif

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
