@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('general.live_streaming') }}</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

			@if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

              @include('errors.errors-forms')

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-5">

					 <form method="POST" action="{{ url('panel/admin/live-streaming') }}" enctype="multipart/form-data">
						 @csrf

						 <fieldset class="row mb-3">
			         <legend class="col-form-label col-sm-2 pt-0 text-lg-end">{{ __('admin.status') }}</legend>
			         <div class="col-sm-10">
			           <div class="form-check form-switch form-switch-md">
			            <input class="form-check-input" type="checkbox" name="live_streaming_status" @if ($settings->live_streaming_status == 'on') checked="checked" @endif value="on" role="switch">
			          </div>
			         </div>
			       </fieldset><!-- end row -->

						 <fieldset class="row mb-3">
			         <legend class="col-form-label col-sm-2 pt-0 text-lg-end">{{ __('general.live_streaming_free') }}</legend>
			         <div class="col-sm-10">
			           <div class="form-check form-switch form-switch-md">
			            <input class="form-check-input" type="checkbox" name="live_streaming_free" @if ($settings->live_streaming_free) checked="checked" @endif value="1" role="switch">
			          </div>
			         </div>
			       </fieldset><!-- end row -->

						 <div class="row mb-3">
 		          <label class="col-sm-2 col-form-label text-lg-end">Agora APP ID</label>
 		          <div class="col-sm-10">
 		            <input value="{{ $settings->agora_app_id }}" name="agora_app_id" type="text" class="form-control">
 		          </div>
 		        </div>

 						<div class="row mb-3">
 							<label class="col-sm-2 col-form-label text-lg-end">{{ __('general.live_streaming_min_price') }}</label>
 							<div class="col-sm-10">
 								<input value="{{ $settings->live_streaming_minimum_price }}" name="live_streaming_minimum_price" type="number" min="1" class="form-control onlyNumber" autocomplete="off">
 							</div>
 						</div>

						<div class="row mb-3">
							<label class="col-sm-2 col-form-label text-lg-end">{{ __('general.live_streaming_max_price') }}</label>
							<div class="col-sm-10">
								<input value="{{ $settings->live_streaming_max_price }}" name="live_streaming_max_price" type="number" min="1" class="form-control onlyNumber" autocomplete="off">
							</div>
						</div>

						<div class="row mb-3">
		          <label class="col-sm-2 col-form-labe text-lg-end">{{ __('general.limit_live_streaming_paid') }} ({{ __('general.minutes') }})</label>
		          <div class="col-sm-10">
		            <select name="limit_live_streaming_paid" class="form-select">
									<option @if ($settings->limit_live_streaming_paid == 0) selected="selected" @endif value="0">{{ trans('admin.unlimited') }}</option>
									@for ($i = 10; $i <= 100; $i+=10)
										<option @if ($settings->limit_live_streaming_paid == $i) selected="selected" @endif value="{{ $i }}">{{$i}} {{ trans('general.minutes') }}</option>
									@endfor
		           </select>
		          </div>
		        </div>

						<div class="row mb-3">
		          <label class="col-sm-2 col-form-labe text-lg-end">{{ __('general.limit_live_streaming_free') }} ({{ __('general.minutes') }})</label>
		          <div class="col-sm-10">
		            <select name="limit_live_streaming_free" class="form-select">
									<option @if ($settings->limit_live_streaming_free == 0) selected="selected" @endif value="0">{{ trans('admin.unlimited') }}</option>
									@for ($i = 10; $i <= 100; $i+=10)
										<option @if ($settings->limit_live_streaming_free == $i) selected="selected" @endif value="{{ $i }}">{{$i}} {{ trans('general.minutes') }}</option>
									@endfor
		           </select>
		          </div>
		        </div>

						<div class="row mb-3">
		          <div class="col-sm-10 offset-sm-2">
		            <button type="submit" class="btn btn-dark mt-3 px-5">{{ __('admin.save') }}</button>
		          </div>
		        </div>

		       </form>

				 </div><!-- card-body -->
 			</div><!-- card  -->
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection
