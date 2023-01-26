@extends('layouts.app')

@section('title') {{trans('general.wallet')}} -@endsection

@section('content')
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/google-libphonenumber@3.2.17/dist/libphonenumber.js" integrity="sha256-y7g6xQm+MB2sFTvdhBwEMDWg9sAUz9msCc2973e0wjg=" crossorigin="anonymous"></script>
<section class="section section-sm">
    <div class="container">
      <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-8 py-5">
          <h2 class="mb-0 font-montserrat">{{trans('general.wallet')}}</h2>
        </div>
      </div>
      <div class="row justify-content-center">

        <div class="col-md-8 mb-5 mb-lg-0">

          @if (session('error_message'))
          <div class="alert alert-danger mb-3">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
            </button>

            {{ session('error_message') }}
          </div>
          @endif

          @if (session('success_message'))
          <div class="alert alert-success mb-3">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
            </button>

            {{ session('success_message') }}
          </div>
          @endif

          <div class="alert alert-primary overflow-hidden" role="alert">

            <div class="inner-wrap">
              <span>
                <h2><strong>{{ Helper::userWallet() }}</strong>
                </h2>

                <span class="w-100 d-block">
                {{trans('general.funds_available')}}
                </span>

                @if ($equivalent_money)
                  <span>
                    <strong>{{ $equivalent_money }}</strong>
                  </span>
                @endif
                
                <small style="color: #fff!important;" class="form-text text-muted">Para comprar contéudos, seguir e dar gorjetas *</small>

              </span>
            </div>

            <span class="icon-wrap"><i class="iconmoon icon-Wallet"></i></span>

        </div><!-- /alert -->

          <form method="POST" action="{{ url('add/funds') }}" id="formAddFunds">

            @csrf

        <span class="category-filter d-lg-block">Montante</span>
    
            <div class="form-group mb-4">
              <div class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg" id="onlyNumber" name="amount" min="{{ $settings->min_deposits_amount }}" max="{{ $settings->max_deposits_amount }}" autocomplete="off" placeholder="" type="number">

            </div> 
            
             <small class="form-text text-muted mb-4">* Não fazemos reembolso</small>
             
             <span class="category-filter d-lg-block">Método de pagamento</span>
            
            @foreach (PaymentGateways::where('enabled', '1')->orderBy('type', 'DESC')->get() as $payment)
              @php

              if ($payment->type == 'card' ) {
                $paymentName = '<i class="far fa-credit-card mr-1 icon-sm-radio"></i> '. trans('general.debit_credit_card') .' ('.$payment->name.')';
              } else if ($payment->type == 'bank') {
                $paymentName = '<i class="fa fa-university mr-1 icon-sm-radio"></i> '.trans('general.bank_transfer');
              } else if ($payment->name == 'Vpos') {
                $paymentName = '<img src="https://oprivado.com/public/images/mcx-logo.svg" width="100"/>';
              } else if ($payment->name == 'PayPal') {
                $paymentName = '<img src="'.url('https://oprivado.com/public/images/PayPal.svg').'" width="70"/>';
              } else {
                $paymentName = '<img src="https://oprivado.com/public/images/mcx-logo.svg" width="100"/>';
              }

              @endphp
              <style>
                  .custom-control-label:not(.switch)::before {
                      left: 0.25rem;
                   }
                   .custom-control-label::after {
                      left: 0.25rem;
                   }
              </style>
              
              <div class="custom-control custom-radio mb-3" style="padding: 10px; background: #fff; border-radius: 5px; border: 1px solid #dfdfdf;">
                <input style="left: 16px;" name="payment_gateway" value="{{$payment->name}}" id="tip_radio{{$payment->name}}" @if (PaymentGateways::where('enabled', '1')->count() == 1) @endif class="custom-control-input" checked type="radio">
                <label class="custom-control-label" for="tip_radio{{$payment->name}}">
                  <span style="margin-left: 35px;"><strong>{!!$paymentName!!}</strong></span>
                  <small class="w-100 d-block">{{ $payment->fee != 0.00 || $payment->fee_cents != 0.00 ? '* '.trans('general.transaction_fee').':' : null }} {{ $payment->fee != 0.00 ? $payment->fee.'%' : null }} {{ $payment->fee_cents != 0.00 ? '+ '. Helper::amountFormatDecimal($payment->fee_cents) : null }}</small>
                </label>
                
                @if ($payment->type == 'vpos')
                  <div style="margin-left: 38px;">
                      <p style="margin: 5px 0 0 0;">Multicaixa Express</p>
                      <span style="font-size: 12px; color: #afadad;">O número deve estar registado no Multicaixa Express</span>
                      <div class="input-container" style="margin: 10px 0 0px 0; max-width: 300px;"> <input oninput="checkMobileNumber()" value="" class="form-control float" id="mobile" name="telephone" type="text" placeholder="Digite o número de telemóvel" maxlength="9" required="required"> </div>
                  </div>
                @endif
              </div>
              <small style="font-size: 11px;margin-top: -8px!important;" class="w-100 d-block mt-2">* Todos os pagamentos via Multicaixa Express são seguros <img style="width: 8px;" src="http://oprivado.com/public/img/mcx-lock.svg"></small>

              @if ($payment->type == 'bank')

                <div class="btn-block @if (PaymentGateways::where('enabled', '1')->count() != 1) display-none @endif" id="bankTransferBox">
                  <div class="alert alert-default border">
                  <h5 class="font-weight-bold"><i class="fa fa-university mr-1 icon-sm-radio"></i> {{trans('general.make_payment_bank')}}</h5>
                  <ul class="list-unstyled">
                      <li>
                        {!!nl2br($payment->bank_info)!!}

                        <hr />
                        <span class="d-block w-100 mt-2">
                        {{ trans('general.total') }}: <strong>{{ $settings->currency_position == 'left'  ? $settings->currency_symbol : (($settings->currency_position == 'left_space') ? $settings->currency_symbol.' ' : null) }}<span id="total2">0</span>{{ $settings->currency_position == 'right' ? $settings->currency_symbol : (($settings->currency_position == 'right_space') ? ' '.$settings->currency_symbol : null) }}</strong>
                        <span>

                      </li>
                  </ul>
                </div>

                <div class="mb-3 text-center">
                  <span class="btn-block mb-2" id="previewImage"></span>

                    <input type="file" name="image" id="fileBankTransfer" accept="image/*" class="visibility-hidden">
                    <button class="btn btn-1 btn-block btn-outline-primary mb-2 border-dashed" onclick="$('#fileBankTransfer').trigger('click');" type="button" id="btnFilePhoto">{{trans('general.upload_image')}} (JPG, PNG, GIF) {{trans('general.maximum')}}: {{Helper::formatBytes($settings->file_size_allowed_verify_account * 1024)}}</button>

                  <small class="text-muted btn-block">{{trans('general.info_bank_transfer')}}</small>
                </div>
                </div><!-- Alert -->
              @endif

            @endforeach

            <div class="alert alert-danger display-none" id="errorAddFunds">
                <ul class="list-unstyled m-0" id="showErrorsFunds"></ul>
            </div>
            
            <button class="btn btn-1 btn-success mt-4" id="addFundsBtn" type="submit"><i></i> {{trans('general.add_funds')}}</button>
          </form>
          
         

          @if ($data->count() != 0)

<h2 style="margin-top: 50px;margin-bottom: 20px!important;"  class="mb-0 font-montserrat">Últimas transações</h2>

          <div class="card">
            <div class="table-responsive">
              <table class="table table-striped m-0">
                <thead>
                  <th scope="col">{{ trans('admin.amount') }}</th>
                  <th scope="col">{{ trans('admin.date') }}</th>
                  <th scope="col">{{ trans('admin.status') }}</th>
                </thead>

                <tbody>
                  @foreach ($data as $deposit)

                    <tr>
                      <td>{{ App\Helper::amountFormat($deposit->amount) }}</td>
                      <td>{{ date('d M, Y', strtotime($deposit->date)) }}</td>

                      @php

                      if ($deposit->status == 'pending' ) {
                       			$mode    = 'warning';
             								$_status = trans('admin.naopago');
                          } else {
                            $mode = 'success';
             								$_status = trans('admin.pago');
                          }

                       @endphp

                       <td>{{ $_status }} @if ($deposit->status == 'active')
                         <a href="{{url('deposits/invoice', $deposit->id)}}" target="_blank">({{trans('general.invoice')}})</a>
                       </td>
                     @endif</td>
                    </tr><!-- /.TR -->
                    @endforeach
                </tbody>
              </table>
            </div><!-- table-responsive -->
          </div><!-- card -->
          
          @if ($data->hasPages())
  			    	<div class="mt-3">
                {{ $data->links() }}
              </div>
  			    	@endif
        @endif

        </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection

@section('javascript')

<script type="text/javascript">

    const ONE_SECOND = 1000;
    var mobile = "";
    var state = "initial";
    var timer = null;
    var numberIsAdded = false;
  
    function isValidPhoneNumber(mobile) {
      if (isSandboxNumber(mobile)) {
        return true;
      } else {
        var phoneUtil = libphonenumber.PhoneNumberUtil.getInstance();
        var number = phoneUtil.parse("+244" + mobile);
        return phoneUtil.isValidNumberForRegion(number, "AO");
      }
    }

    function isSandboxNumber(mobile) {
      switch (mobile) {
        case '900000000':
          return true;
        case '900002004':
          return true;
        case '900003000':
          return true;
        default:
          return false;
      }
    }

    function checkMobileNumber() {
      this.mobile = document.getElementById("mobile").value;
      if (this.isValidPhoneNumber(this.mobile) == true) {
        $("#addFundsBtn").attr("disabled",true);
        $("#addFundsBtn").removeAttr("disabled");
      } else {
        $("#addFundsBtn").removeAttr("disabled");
        $("#addFundsBtn").attr("disabled",true);
      }
    }


  
    function formatMobileNumber(mobile) {
        let formattedMobile = mobile.match(/.{1,3}/g);
        return formattedMobile.join(' ');
    }




@if ($settings->currency_code == 'JPY')
  $decimal = 0;
  @else
  $decimal = 2;
  @endif

  function toFixed(number, decimals) {
        var x = Math.pow(10, Number(decimals) + 1);
        return (Number(number) + (1 / x)).toFixed(decimals);
      }

  $('input[name=payment_gateway]').on('click', function() {

    var valueOriginal = $('#onlyNumber').val();
    var value = parseFloat($('#onlyNumber').val());
    var element = $(this).val();

    //==== Start Taxes
    var taxes = $('span.isTaxableWallet').length;
    var totalTax = 0;

    if (valueOriginal.length == 0
				|| valueOriginal == ''
				|| value < {{ $settings->min_deposits_amount }}
				|| value > {{$settings->max_deposits_amount}}
      ) {
        // Reset
  			for (var i = 1; i <= taxes; i++) {
  				$('.percentageTax'+i).html('0');
  			}
        $('#handlingFee, #total, #total2').html('0');
      } else {
        // Taxes
        for (var i = 1; i <= taxes; i++) {
          var percentage = $('.percentageAppliedTaxWallet'+i).attr('data');
          var valueFinal = (value * percentage / 100);
          $('.percentageTax'+i).html(toFixed(valueFinal, $decimal));
          totalTax += valueFinal;
        }
        var totalTaxes = (Math.round(totalTax * 100) / 100).toFixed(2);
      }
      //==== End Taxes

    if (element != ''
        && value <= {{ $settings->max_deposits_amount }}
        && value >= {{ $settings->min_deposits_amount }}
        && valueOriginal != ''
      ) {
      // Fees
      switch (element) {
        @foreach (PaymentGateways::where('enabled', '1')->get(); as $payment)
        case '{{$payment->name}}':
          $fee   = {{$payment->fee}};
          $cents =  {{$payment->fee_cents}};
          break;
        @endforeach
      }

      var amount = (value * $fee / 100) + $cents;
      var amountFinal = toFixed(amount, $decimal);

      var total = (parseFloat(value) + parseFloat(amountFinal) + parseFloat(totalTaxes));

      if (valueOriginal.length != 0
  				|| valueOriginal != ''
  				|| value >= {{ $settings->min_deposits_amount }}
  				|| value <= {{$settings->max_deposits_amount}}
        ) {
        $('#handlingFee').html(amountFinal);
        $('#total, #total2').html(total.toFixed($decimal));
      }
    }

});

//<-------- * TRIM * ----------->

$('#onlyNumber').on('keyup', function() {

    var valueOriginal = $(this).val();
    var value = parseFloat($(this).val());
    var paymentGateway = $('input[name=payment_gateway]:checked').val();

    if (value > {{ $settings->max_deposits_amount }} || valueOriginal.length == 0) {
      $('#handlingFee').html('0');
      $('#total, #total2').html('0');
    }

    //==== Start Taxes
    var taxes = $('span.isTaxableWallet').length;
    var totalTax = 0;

    if (valueOriginal.length == 0
				|| valueOriginal == ''
				|| value < {{ $settings->min_deposits_amount }}
				|| value > {{$settings->max_deposits_amount}}
      ) {
        // Reset
  			for (var i = 1; i <= taxes; i++) {
  				$('.percentageTax'+i).html('0');
  			}
        $('#handlingFee, #total, #total2').html('0');
      } else {
        // Taxes
        for (var i = 1; i <= taxes; i++) {
          var percentage = $('.percentageAppliedTaxWallet'+i).attr('data');
          var valueFinal = (value * percentage / 100);
          $('.percentageTax'+i).html(toFixed(valueFinal, $decimal));
          totalTax += valueFinal;
        }
        var totalTaxes = (Math.round(totalTax * 100) / 100).toFixed(2);
      }
      //==== End Taxes

    if (paymentGateway
        && value <= {{ $settings->max_deposits_amount }}
        && value >= {{ $settings->min_deposits_amount }}
        && valueOriginal != ''
      ) {

      switch(paymentGateway) {
        @foreach (PaymentGateways::where('enabled', '1')->get(); as $payment)
        case '{{$payment->name}}':
          $fee   = {{$payment->fee}};
          $cents =  {{$payment->fee_cents}};
          break;
        @endforeach
      }

      var amount = (value * $fee / 100) + $cents;
      var amountFinal = toFixed(amount, $decimal);

      var total = (parseFloat(value) + parseFloat(amountFinal) + parseFloat(totalTaxes));

      if (valueOriginal.length != 0
  				|| valueOriginal != ''
  				|| value >= {{ $settings->min_deposits_amount }}
  				|| value <= {{$settings->max_deposits_amount}}
        ) {
        $('#handlingFee').html(amountFinal);
        $('#total, #total2').html(total.toFixed($decimal));
      } else {
        $('#handlingFee, #total, #total2').html('0');
        }
    }
});

@if (session('payment_process'))
   swal({
     html:true,
     title: "{{ trans('general.congratulations') }}",
     text: "{!! trans('general.payment_process_wallet') !!}",
     type: "success",
     confirmButtonText: "{{ trans('users.ok') }}"
     });
  @endif

</script>
@endsection
