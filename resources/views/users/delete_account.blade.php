@extends('layouts.app')

@section('title') {{trans('general.delete_account')}} -@endsection

@section('content')
<section class="section section-sm">
    <div class="container">
      <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-8 py-5">
          <h2 class="mb-0 font-montserrat"> {{trans('general.delete_account')}}</h2>
        </div>
      </div>
      <div class="row justify-content-center">

        <div class="col-md-7 mb-5 mb-lg-0">


      @if (session('incorrect_pass'))
 			<div class="alert alert-danger">
 				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <i class="bi bi-x"></i>
        </button>
             		{{ session('incorrect_pass') }}
          </div>
        @endif

          @include('errors.errors-forms')

          <div class="alert alert-warning" role="alert">
          <i class="fa fa-exclamation-triangle"></i> {{ trans('general.notice_delete_account') }}
          </div>

          <form method="POST" id="formSend" action="{{ url()->current() }}">

            @csrf
              <div class="form-group">
                  <div class="input-group mb-4">
                    <input class="form-control" name="password" placeholder="{{trans('general.enter_password')}}" type="password" required>
                  </div>
                </div>

                <button class="btn btn-1 btn-primary btn-block" id="buttonDeleteAccount" type="submit">{{trans('general.delete_account')}}</button>

                <div class="text-center mt-3">
                  <a href="{{ url('privacy/security') }}">{{ trans('admin.cancel') }}</a>
                </div>

          </form>

        </div><!-- end col-md-6 -->

      </div>
    </div>
  </section>
@endsection
