@extends('layouts.app')

@section('title') {{trans('general.subscription_price')}} -@endsection

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
                    <h2 class="mb-4 font-montserrat"> {{trans('general.subscription_price')}}</h2>

          @if (session('status'))
                  <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<i class="bi bi-x-lg"></i>
                			</button>

                    {{ session('status') }}
                  </div>
                @endif

                @if (count($errors) > 0)
                  <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<i class="bi bi-x-lg"></i>
                			</button>

                    <i class="far fa-times-circle mr-2"></i> {{trans('auth.error_desc')}}
                  </div>
                @endif

    @if (auth()->user()->verified_id == 'no' && $settings->requests_verify_account == 'on')
    <div class="alert alert-danger mb-3">
             <ul class="list-unstyled m-0">
               <li><i class="fa fa-exclamation-triangle"></i> {{trans('general.verified_account_info')}} <a href="{{url('settings/verify/account')}}" class="text-white link-border">{{trans('general.verify_account')}}</a></li>
             </ul>
           </div>
           @endif

          <form method="POST" action="{{ url('settings/subscription') }}">

            @csrf
            
              <div class="form-group">
                  
                  <label class="mt-4"><strong>Preço por mês *</strong></label>
              <div class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg isNumber subscriptionPrice" id="onlyNumber" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject' || auth()->user()->free_subscription == 'yes') disabled @endif name="price" placeholder="0" value="{{$settings->currency_code == 'JPY' ? round(auth()->user()->plan('monthly', 'price')) : auth()->user()->plan('monthly', 'price')}}"  type="text">
                    @error('price')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
              </div>
              <small class="form-text text-muted mb-4">(Mínimo 700 KZ - Máximo 19.000 KZ)</small>

              <label><strong>Preço por semana</strong></label>
              <div class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg isNumber subscriptionPrice" id="onlyNumber" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject' || auth()->user()->free_subscription == 'yes') disabled @endif name="price_weekly" placeholder="0" value="{{$settings->currency_code == 'JPY' ? round(auth()->user()->plan('weekly', 'price')) : auth()->user()->plan('weekly', 'price')}}"  type="text">
                    @error('price_weekly')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
              </div>
              <small style="margin-bottom: 5px;" class="form-text text-muted">(Mínimo 700 KZ - Máximo 19.000 KZ)</small>

                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif name="status_weekly" value="1" @if (auth()->user()->plan('weekly', 'status')) checked @endif id="customSwitchWeekly">
                  <label class="custom-control-label switch" for="customSwitchWeekly">Mostrar no perfil</label>
                </div>

              <label style="display:none!important;" class="mt-4"><strong>{{trans('general.subscription_price_quarterly')}}</strong></label>
              <div style="display:none!important;" class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg isNumber subscriptionPrice" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject' || auth()->user()->free_subscription == 'yes') disabled @endif name="price_quarterly" placeholder="0.00" value="{{$settings->currency_code == 'JPY' ? round(auth()->user()->plan('quarterly', 'price')) : auth()->user()->plan('quarterly', 'price')}}"  type="text">
                    @error('price_quarterly')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
              </div>

                <div style="display:none!important;" class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif name="status_quarterly" value="1" @if (auth()->user()->plan('quarterly', 'status')) checked @endif id="customSwitchQuarterly">
                  <label class="custom-control-label switch" for="customSwitchQuarterly">{{ trans('general.status') }}</label>
                </div>

             
              <div style="display:none!important;" class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg isNumber subscriptionPrice" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject' || auth()->user()->free_subscription == 'yes') disabled @endif name="price_biannually" placeholder="0.00" value="{{$settings->currency_code == 'JPY' ? round(auth()->user()->plan('biannually', 'price')) : auth()->user()->plan('biannually', 'price')}}"  type="text">
                    @error('price_biannually')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
              </div>

                <div style="display:none!important;" class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif name="status_biannually" value="1" @if (auth()->user()->plan('biannually', 'status')) checked @endif id="customSwitchBiannually">
                  <label class="custom-control-label switch" for="customSwitchBiannually">{{ trans('general.status') }}</label>
                </div>

              
              <div style="display:none!important;" class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text">{{$settings->currency_symbol}}</span>
              </div>
                  <input class="form-control form-control-lg isNumber subscriptionPrice" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject' || auth()->user()->free_subscription == 'yes') disabled @endif name="price_yearly" placeholder="0.00" value="{{$settings->currency_code == 'JPY' ? round(auth()->user()->plan('yearly', 'price')) : auth()->user()->plan('yearly', 'price')}}"  type="text">
                    @error('price_yearly')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
              </div>

                <div style="display:none!important;" class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif name="status_yearly" value="1" @if (auth()->user()->plan('yearly', 'status')) checked @endif id="customSwitchYearly">
                  <label class="custom-control-label switch" for="customSwitchYearly">{{ trans('general.status') }}</label>
                </div>

              <div class="text-muted btn-block mb-4 mt-4">
                <div class="custom-control custom-switch custom-switch-lg">
                  <input type="checkbox" class="custom-control-input" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif name="free_subscription" value="yes" @if (auth()->user()->free_subscription == 'yes') checked @endif id="customSwitchFreeSubscription">
                  <label style="color: #5f5f5f !important;" class="custom-control-label switch" for="customSwitchFreeSubscription"><strong>Deixar grátis</strong></label>
                </div>

                @if (auth()->user()->totalSubscriptionsActive() != 0)

                  @if (auth()->user()->free_subscription == 'yes')
                    <div class="alert alert-warning display-none mt-3" role="alert" id="alertDisableFreeSubscriptions">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      <span>{{ trans('general.alert_disable_free_subscriptions') }}</span>
                    </div>

                  @else
                    <div class="alert alert-warning display-none mt-3" role="alert" id="alertDisablePaidSubscriptions">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      <span>{{ trans('general.alert_disable_paid_subscriptions') }}</span>
                    </div>
                  @endif

                @endif
              </div>
            </div><!-- End form-group -->

            <button class="btn btn-1 btn-success" @if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') disabled @endif onClick="this.form.submit(); this.disabled=true; this.innerText='{{trans('general.please_wait')}}';" type="submit">
              {{trans('general.save_changes')}}
            </button>

          </form>
        </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection

@section('javascript')

<script type="text/javascript">

   @if ($settings->currency_code == 'JPY')
  $decimal = 0;
  @else
  $decimal = 2;
  @endif

  function toFixed(number, decimals) {
        var x = Math.pow(10, Number(decimals) + 1);
        return (Number(number) + (1 / x)).toFixed(decimals);
      }

  $('input[name=price_quarterly]').on('click', function() {

    var valueOriginal = $('#onlyNumber').val();
    var value = parseFloat($('#onlyNumber').val());
    var element = $(this).val();

});
</script>

<script type="text/javascript">

@if ($settings->currency_code == 'JPY')
  $decimal = 0;
  @else
  $decimal = 2;
  @endif

  function toFixed(number, decimals) {
        var x = Math.pow(10, Number(decimals) + 1);
        return (Number(number) + (1 / x)).toFixed(decimals);
      }

  $('input[name=price]').on('click', function() {

    var valueOriginal = $('#onlyNumber').val();
    var value = parseFloat($('#onlyNumber').val());
    var element = $(this).val();

});

</script>
@endsection
