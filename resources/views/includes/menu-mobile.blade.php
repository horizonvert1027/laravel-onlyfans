<div class="menuMobile w-100 bg-white shadow-lg p-3 border-top">
	<ul class="list-inline d-flex bd-highlight m-0 text-center">

				<li class="flex-fill bd-highlight">
					<a class="p-3 btn-mobile" href="{{url('/')}}" title="{{trans('admin.home')}}">
						<i class="feather icon-home icon-navbar"></i>
					</a>
				</li>

				<li class="flex-fill bd-highlight">
					<a class="p-3 btn-mobile" href="{{url('explore')}}" title="{{trans('general.explore')}}">
						<i class="feather icon-compass icon-navbar"></i>
					</a>
				</li>

			
				<li class="flex-fill bd-highlight">
					<a class="p-3 btn-mobile" href="https://oprivado.com/creators/more-active" title="Explore criadores">
						<i class="feather icon-heart icon-navbar"></i>
					</a>
				</li>
		

			<li class="flex-fill bd-highlight">
				<a href="{{url('messages')}}" class="p-3 btn-mobile position-relative" title="{{ trans('general.messages') }}">

					<span class="noti_msg notify @if (auth()->user()->messagesInbox() != 0) d-block @endif">
						{{ auth()->user()->messagesInbox() }}
						</span>

					<i class="feather icon-message-circle icon-navbar"></i>
				</a>
			</li>

			<li class="flex-fill bd-highlight">
				<a href="{{url('notifications')}}" class="p-3 btn-mobile position-relative" title="{{ trans('general.notifications') }}">
					<span class="noti_notifications notify @if (auth()->user()->notifications()->where('status', '0')->count()) d-block @endif">
						{{ auth()->user()->notifications()->where('status', '0')->count() }}
						</span>
					<i class="feather icon-bell icon-navbar"></i>
				</a>
			</li>
			</ul>
</div>
