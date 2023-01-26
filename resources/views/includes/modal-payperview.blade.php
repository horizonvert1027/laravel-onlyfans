<!-- Start Modal payPerViewForm -->
<div class="modal fade" id="payPerViewForm" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
	<div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body p-0">
				<div class="card bg-white shadow border-0">

					<div class="card-body px-lg-5 py-lg-5 position-relative">

						<div class="mb-3 text-center">
						 <strong>{{trans('general.unlock_content')}}</strong>
						</div>

						<form method="post" action="{{url('send/ppv')}}" id="formSendPPV">

							<input type="hidden" name="id" class="mediaIdInput" value="0" />
							<input type="hidden" name="amount" class="priceInput" value="0" />

							@if (request()->is('messages/*'))
								<input type="hidden" name="isMessage" value="1" />
							@endif

							<input type="hidden" id="cardholder-name-PPV" value="{{ auth()->user()->name }}"  />
							<input type="hidden" id="cardholder-email-PPV" value="{{ auth()->user()->email }}"  />
							@csrf

							@foreach (PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get() as $payment)

								@php

								if ($payment->type == 'card' ) {
									$paymentName = '<i class="far fa-credit-card mr-1"></i> '.trans('general.debit_credit_card') .' <small class="w-100 d-block">'.__('general.powered_by').' '.$payment->name.'</small>';
								} else if ($payment->id == 1) {
									$paymentName = '<img src="'.url('public/img/payments', auth()->user()->dark_mode == 'off' ? $payment->logo : 'paypal-white.png').'" width="70"/> <small class="w-100 d-block">'.trans('general.redirected_to_paypal_website').'</small>';
								} else {
									$paymentName = '<img src="'.url('public/img/payments', $payment->logo).'" width="70"/>';
								}

								$allPayments = PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get();

								@endphp
								<div class="custom-control custom-radio mb-3">
									<input checked name="payment_gateway_ppv" value="{{$payment->id}}" id="ppv_radio{{$payment->id}}" @if ($allPayments->count() == 1 && Helper::userWallet('balance') == 0) checked @endif class="custom-control-input" type="radio">
									<label class="custom-control-label" for="ppv_radio{{$payment->id}}">
										<span><strong>{!!$paymentName!!}</strong></span>
									</label>
								</div>

								@if ($payment->name == 'Stripe')
								<div id="stripeContainerPPV" class="@if ($allPayments->count() != 1) display-none @endif">
									<div id="card-elementPPV" class="margin-bottom-10">
										<!-- A Stripe Element will be inserted here. -->
									</div>
									<!-- Used to display form errors. -->
									<div id="card-errorsPPV" class="alert alert-danger display-none" role="alert"></div>
								</div>
								@endif

							@endforeach

							@if ($settings->disable_wallet == 'on' && Helper::userWallet('balance') != 0 || $settings->disable_wallet == 'off')
							<div class="custom-control custom-radio mb-3">
								<input name="payment_gateway_ppv" @if (Helper::userWallet('balance') == 0) disabled @endif value="wallet" id="ppv_radio0" class="custom-control-input" type="radio">
								<label class="custom-control-label" for="ppv_radio0">
									<span>
										<strong>
										<span class="w-100 d-block font-weight-light">
											{{ __('general.available_balance') }}: <span class="font-weight-bold mr-1 balanceWallet">{{Helper::userWallet()}}</span>

											@if (Helper::userWallet('balance') != 0 && $settings->wallet_format != 'real_money')
												<i class="bi bi-info-circle text-muted" data-toggle="tooltip" data-placement="top" title="{{Helper::equivalentMoney($settings->wallet_format)}}"></i>
											@endif

											@if (Helper::userWallet('balance') == 0)
											<a href="{{ url('my/wallet') }}" class="link-border">{{ __('general.recharge') }}</a>
										@endif
										</span>
									</strong>
									</span>
								</label>
							</div>
						@endif

						@if (auth()->user()->isTaxable()->count())
							@include('includes.modal-taxes')
						@endif

							<div class="alert alert-danger display-none mt-3 mb-0" id="errorPPV">
									<ul class="list-unstyled m-0" id="showErrorsPPV"></ul>
								</div>

							<div class="text-center">
								<button type="submit" id="ppvBtn" class="btn btn-primary mt-4 ppvBtn"><i></i> {{trans('general.pay')}} <span class="pricePPV"></span></button>

								<div class="w-100 mt-2">
									<button type="button" class="btn e-none p-0" data-dismiss="modal">{{trans('admin.cancel')}}</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- End Modal payPerViewForm -->
