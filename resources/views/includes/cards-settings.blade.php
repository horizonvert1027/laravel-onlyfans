<div class="col-md-6 col-lg-3 mb-3">

<button type="button" class="btn-menu-expand btn btn-primary btn-block mb-2 d-lg-none" type="button" data-toggle="collapse" data-target="#navbarUserHome" aria-controls="navbarCollapse" aria-expanded="false">
		<i class="fa fa-bars mr-2"></i> {{trans('general.menu')}}
	</button>

	<div class="navbar-collapse collapse d-lg-block" id="navbarUserHome">

		<!-- Start Account -->
		<div class="card card-settings mb-3">
				<div class="list-group list-group-sm list-group-flush">

			<a href="{{url('settings/page')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/page')) active @endif">
							<div>
								
									<span>{{ auth()->user()->verified_id == 'yes' ? trans('general.edit_my_page') : trans('users.edit_profile')}}</span>
							</div>
							<div>
								
							</div>
					</a>
					
					<a href="{{url('settings/verify/account')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/verify/account')) active @endif">
							<div>
									<span>{{ auth()->user()->verified_id == 'yes' ? trans('general.verified_account') : trans('general.become_creator')}}</span> <small style="display: block;">(Ganhar dinheiro)</small>
							</div>
							<div>
								
							</div>
					</a>
					
          @if ($settings->referral_system == 'on' || auth()->user()->referrals()->count() != 0)
  					<a href="{{url('my/referrals')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('my/referrals')) active @endif">
  							<div>
  								
  									<span>{{trans('general.referrals')}}</span>
  							</div>
  							<div>
  								
  							</div>
  					</a>
  				@endif
					
					@if (auth()->user()->verified_id == 'yes')
			<a href="{{url('settings/subscription')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/subscription')) active @endif">
					<div>
							<span>{{trans('general.subscription_price')}}</span>
					</div>
					<div>
						
					</div>
			</a>
		@endif

				@if (auth()->user()->verified_id == 'yes' || $settings->referral_system == 'on' || auth()->user()->balance != 0.00)
				<a href="{{url('settings/payout/method')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/payout/method')) active @endif">
						<div>
								<span>{{trans('users.payout_method')}}</span>
						</div>
						<div>
							
						</div>
				</a>

				<a href="{{url('settings/withdrawals')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/withdrawals')) active @endif">
						<div>
								
								<span>{{trans('general.withdrawals')}}</span>
						</div>
						<div>
							
						</div>
				</a>
			@endif
			
				<a href="{{url('my/payments')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('my/payments')) active @endif">
					<div>
						
							<span>{{trans('general.payments')}} feitos</span>
					</div>
					<div>
						
					</div>
			</a>
			
				@if (auth()->user()->verified_id == 'yes')
			<a href="{{url('my/payments/received')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('my/payments/received')) active @endif">
					<div>
						
							<span>{{trans('general.payments_received')}}</span>
					</div>
					<div>
							
					</div>
			</a>
		@endif
		
			<a href="{{url('settings/restrictions')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/restrictions')) active @endif">
		<div>
				<span>{{trans('general.restricted_users')}}</span>
		</div>
		<div>
		</div>
</a>

	<a href="{{url('settings/password')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('settings/password')) active @endif">
			<div>
				
					<span>{{trans('auth.password')}}</span>
			</div>
			<div>
					
			</div>
	</a>
	
		@if (auth()->user()->verified_id == 'yes')
	
		<a href="{{url('privacy/security')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if (request()->is('privacy/security')) active @endif">
			<div>
				
					<span>{{trans('general.privacy_security')}}</span>
			</div>
			<div>
				
			</div>
	</a> @endif
				</div>
			</div>

	</div>
</div>
