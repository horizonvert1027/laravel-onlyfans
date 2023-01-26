<div class="modal fade" id="tipForm" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
	<div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body p-0">
				<div class="card bg-white border-0">
					<div class="card-body px-lg-5 py-lg-5 position-relative">

						<div class="text-muted text-center mb-3 position-relative modal-offset">
							<img src="{{Helper::getFile(config('path.avatar').auth()->user()->avatar)}}" width="100" class="avatar-modal rounded-circle mb-1">
							<h6 style="font-size:14px;">
								{{trans('general.send_tip')}} <span class="userNameTip"></span>
							</h6>
						</div>

						<form method="post" action="{{url('send/tip')}}" id="formSendTip">

							<input type="hidden" name="id" class="userIdInput" value="{{auth()->user()->id}}"  />

							@if (request()->is('messages/*'))
								<input type="hidden" name="isMessage" value="1" />
							@endif

							@if (request()->route()->named('live'))
								<input type="hidden" name="isLive" value="1" />

								@if ($live)
									<input type="hidden" name="liveID" value="{{ $live->id }}"  />
								@endif

							@endif

							<input type="hidden" id="cardholder-name" value="{{ auth()->user()->name }}"  />
							<input type="hidden" id="cardholder-email" value="{{ auth()->user()->email }}"  />
							<input type="number" min="{{$settings->min_donation_amount}}" data-min-tip="{{$settings->min_tip_amount}}" data-max-tip="{{$settings->max_tip_amount}}"  autocomplete="off" id="onlyNumber" class="form-control mb-3 tipAmount" name="amount" placeholder="{{trans('general.tip_amount')}} ({{ __('general.minimum') }} {{ Helper::amountWithoutFormat($settings->min_tip_amount) }})">
							@csrf

							@if (! request()->route()->named('live'))

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
									<input checked name="payment_gateway_tip" value="{{$payment->id}}" id="tip_radio{{$payment->id}}" @if ($allPayments->count() == 1 && Helper::userWallet('balance') == 0) checked @endif class="custom-control-input" type="radio">
									<label class="custom-control-label" for="tip_radio{{$payment->id}}">
										<span><strong>{!!$paymentName!!}</strong></span>
									</label>
								</div>

								@if ($payment->name == 'Stripe')
								<div id="stripeContainerTip" class="@if ($allPayments->count() != 1) display-none @endif">
									<div id="card-element" class="margin-bottom-10">
										<!-- A Stripe Element will be inserted here. -->
									</div>
									<!-- Used to display form errors. -->
									<div id="card-errors" class="alert alert-danger display-none" role="alert"></div>
								</div>
								@endif

							@endforeach

						@endif {{-- Disable Paymetns on Live streaming --}}

							@if ($settings->disable_wallet == 'on' && Helper::userWallet('balance') != 0 || $settings->disable_wallet == 'off')
							<div class="custom-control custom-radio mb-3">
								<input name="payment_gateway_tip" @if (Helper::userWallet('balance') == 0) disabled @endif value="wallet" id="tip_radio0" class="custom-control-input" type="radio">
								<label class="custom-control-label" for="tip_radio0">
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

							<div class="alert alert-danger display-none" id="errorTip">
									<ul class="list-unstyled m-0" id="showErrorsTip"></ul>
								</div>

							<div class="text-center">
								<button type="button" class="btn e-none mt-4" data-dismiss="modal">{{trans('admin.cancel')}}</button>
								<button type="submit" id="tipBtn" class="btn btn-primary mt-4 tipBtn"><i></i> {{trans('auth.send')}}</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- End Modal Tip -->
