@extends('layouts.app')

@section('title') {{__('users.payout_method')}} -@endsection

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
            
                      <h2 class="mb-4 font-montserrat">{{trans('users.payout_method')}}</h2>

          @if (session('status'))
                  <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<span aria-hidden="true">×</span>
                			</button>
                    <i class="bi-check2 mr-2"></i> {{ session('status') }}
                  </div>
                @endif

                @if (session('error'))
                        <div class="alert alert-danger">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      			<span aria-hidden="true">×</span>
                      			</button>
                          <i class="bi-exclamation-triangle mr-2"></i> {{ session('error') }}
                        </div>
                      @endif

          @include('errors.errors-forms')

      @if (auth()->user()->verified_id != 'yes' && auth()->user()->balance == 0.00)
      <div class="alert alert-danger mb-3">
               <ul class="list-unstyled m-0">
                 <li><i class="fa fa-exclamation-triangle"></i> {{trans('general.verified_account_info')}} <a href="{{url('settings/verify/account')}}" class="text-white link-border">{{trans('general.verify_account')}}</a></li>
               </ul>
             </div>
             @endif

      @if (auth()->user()->verified_id == 'yes' || auth()->user()->balance != 0.00)
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
          <span> {{ trans('general.payout_method_info') }}
          <small class="btn-block">
            @if (! $settings->specific_day_payment_withdrawals)
              * Certifique que está tudo correcto

            @else
              * {{ trans('users.date_paid') }} {{ Helper::formatDate(Helper::paymentDateOfEachMonth($settings->specific_day_payment_withdrawals)) }}
            @endif
          </small>
            </span>
          </div>

            @if( $settings->payout_method_bank == 'on' )
            <!--============ START BANK TRANSFER ============-->
              <div class="custom-control custom-radio mb-3 mt-3">
                    <input name="payment_gateway" value="Bank" id="radio4" class="custom-control-input" @if (auth()->user()->payment_gateway == 'Bank') checked @endif type="radio">
                    <label class="custom-control-label" for="radio4">
                      <span><strong>Titular da conta e o IBAN </strong></span>
                    </label>
                  </div>

                  <form method="POST"  action="{{ url('settings/payout/method/bank') }}" id="Bank" @if (auth()->user()->payment_gateway != 'Bank') class="display-none" @endif>

                    @csrf
                      <div class="form-group">
                        <textarea name="bank_details" rows="5" cols="40" class="form-control" required placeholder="">{{auth()->user()->bank == '' ? old('bank_details') : auth()->user()->bank}}</textarea>
                        </div>

                        <button class="btn btn-1 btn-success" type="submit">{{trans('general.save_payout_method')}}</button>
                  </form>
                  <!--============ END BANK TRANSFER ============-->
                @endif

      @endif

</div><!-- end col-md-6 -->

      </div>
    </div>
  </section>
@endsection

@section('javascript')
  <script type="text/javascript">

  $('input[name=payment_gateway]').on('click', function() {

		if($(this).val() == 'PayPal') {
			$('#PayPal').slideDown();
		} else {
				$('#PayPal').slideUp();
		}

    if($(this).val() == 'Payoneer') {
      $('#Payoneer').slideDown();
    } else {
      $('#Payoneer').slideUp();
    }

    if($(this).val() == 'Zelle') {
      $('#Zelle').slideDown();
    } else {
      $('#Zelle').slideUp();
    }

    if($(this).val() == 'Western') {
      $('#Western').slideDown();
    } else {
      $('#Western').slideUp();
    }

    if($(this).val() == 'Bank') {
      $('#Bank').slideDown();
    } else {
      $('#Bank').slideUp();
    }

  });
  </script>
@endsection
