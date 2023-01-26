@extends('layouts.app')

@section('title') {{trans('auth.password')}} -@endsection

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
            
                      <h2 class="mb-4 font-montserrat"> {{trans('auth.password')}}</h2>


          @if (session('status'))
                  <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<span aria-hidden="true">×</span>
                			</button>

                    {{ session('status') }}
                  </div>
                @endif

                @if (session('incorrect_pass'))
 			<div class="alert alert-danger">
 				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
             		{{ session('incorrect_pass') }}
             		</div>
             	@endif

          @include('errors.errors-forms')

          <form method="POST" action="{{ url('settings/password') }}">

            @csrf

            @if (auth()->user()->password != '')
            <div class="form-group">
                <div class="input-group mb-4">
                  
                  <input class="form-control" name="old_password" placeholder="{{trans('general.old_password')}}" type="password" required>
                </div>
              </div>
              @endif

              <div class="form-group">
                  <div class="input-group mb-4" id="showHidePassword">
                  
                    <input class="form-control" name="new_password" placeholder="{{trans('general.new_password')}}" type="password" required>
                    <div class="input-group-append">
                      <span class="input-group-text c-pointer"><i class="feather icon-eye-off"></i></span>
                  </div>
                  </div>
                </div>

                <button class="btn btn-1 btn-success" type="submit">{{trans('general.save_changes')}}</button>

          </form>
        </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection
