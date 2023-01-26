@foreach ($messagesInbox as $msg)

	@php

	$allMediaMessages = $msg->last()->media()->get();

if ($msg->last()->from_user_id == auth()->user()->id && $msg->last()->to()->id != auth()->user()->id) {
		 $avatar   = $msg->last()->to()->avatar;
		 $name     = $msg->last()->to()->hide_name == 'yes' ? $msg->last()->to()->username : $msg->last()->to()->name;
		 $userID   = $msg->last()->to()->id;
		 $username = $msg->last()->to()->username;
		 $verified_id = $msg->last()->to()->verified_id;
		 $active_status_online = $msg->last()->to()->active_status_online == 'yes' ? true : false;
		 $icon     = $msg->last()->status == 'readed' ? '<span><i class="bi bi-check2-all mr-1"></i></span>' : '<span><i class="bi bi-reply mr-1"></i></span>';

	} else if ($msg->last()->from_user_id == auth()->user()->id){
		 $avatar   = $msg->last()->to()->avatar;
		 $name     = $msg->last()->to()->hide_name == 'yes' ? $msg->last()->to()->username : $msg->last()->to()->name;
		 $userID   = $msg->last()->to()->id;
		 $username = $msg->last()->to()->username;
		 $verified_id = $msg->last()->to()->verified_id;
		 $active_status_online = $msg->last()->to()->active_status_online == 'yes' ? true : false;
		 $icon = null;
	} else {
		 $avatar   = $msg->last()->from()->avatar;
		 $name     = $msg->last()->from()->hide_name == 'yes' ? $msg->last()->from()->username : $msg->last()->from()->name;
		 $userID   = $msg->last()->from()->id;
		 $username = $msg->last()->from()->username;
		 $verified_id = $msg->last()->from()->verified_id;
		 $active_status_online = $msg->last()->from()->active_status_online == 'yes' ? true : false;
		 $icon = null;
	}

	$iconMedia = null;
	$format = null;

	foreach ($allMediaMessages as $media) {

		switch ($media->type) {
			case 'image':
				$iconMedia = '<i class="feather icon-image"></i> ';
				$format = trans('general.image');
				break;
			case 'video':
				$iconMedia = '<i class="feather icon-video"></i> ';
				$format = trans('general.video');
				break;
			case 'music':
				$iconMedia = '<i class="feather icon-mic"></i> ';
				$format = trans('general.music');
				break;
			case 'zip':
				$iconMedia = '<i class="far fa-file-archive"></i> ';
				$format = trans('general.zip');
					break;
		}

	}

	if ($allMediaMessages->count() > 1) {
		$iconMedia = '<i class="bi bi-files"></i> ';
		$format = null;
	}

	if ($msg->last()->tip == 'yes') {
		$iconMedia = '<i class="feather icon-dollar-sign"></i> '.trans('general.tip');
	}

/* New - Readed */
	if ($msg->last()->status == 'new' && $msg->last()->from()->id != auth()->user()->id)  {
	 $styleStatus = ' font-weight-bold unread-chat';
	} else {
		$styleStatus = null;
	}

	// Messages
	$messagesCount = Messages::where('from_user_id', $userID)->where('to_user_id', auth()->user()->id)->where('status','new')->count();

	// Check Pay Per View
	$checkPayPerView = auth()->user()->payPerViewMessages()->where('messages_id', $msg->last()->id)->first();

@endphp

<div class="card msg-inbox border-bottom m-0 rounded-0">
	<div class="list-group list-group-sm list-group-flush rounded-0">

		<a href="{{url('messages/'.$userID, $username)}}" class="item-chat list-group-item list-group-item-action text-decoration-none p-4{{$styleStatus}}  @if (request()->id == $userID) active disabled @endif">
			<div class="media">
			 <div class="media-left mr-3 position-relative @if ($active_status_online) @if (Cache::has('is-online-' . $userID)) user-online @else user-offline @endif @endif">
					 <img class="media-object rounded-circle" src="{{Helper::getFile(config('path.avatar').$avatar)}}"  width="50" height="50">
			 </div>

			 <div class="media-body overflow-hidden">
				 <div class="d-flex justify-content-between align-items-center">
					<h5 class="media-heading mb-2 text-truncate">
							 {{$name}}
							 @if ($verified_id == 'yes')
				         <small class="verified">
				   						<i class="bi bi-patch-check-fill"></i>
				   					</small>
				       @endif
					 </h5>
					 <small class="timeAgo text-truncate mb-2" data="{{ date('c',strtotime( $msg->last()->created_at ) ) }}"></small>
				 </div>

				 <p class="text-truncate m-0">
					 @if ($messagesCount != 0)
					 <span class="badge badge-pill badge-primary mr-1">{{ $messagesCount }}</span>
				 @endif
					 {!! $icon ?? $icon !!} {!! $iconMedia !!} {{ $msg->last()->message == '' ? $format : null }}

					 @if ($msg->last()->price != 0.00
					 		&& $allMediaMessages->count() == 0
							&& $msg->last()->to()->id == auth()->user()->id
							&& !$checkPayPerView
							)

						 <i class="feather icon-lock mr-1"></i> @lang('users.content_locked')

					 @else
						 {{ $msg->last()->message }}
					 @endif

				 </p>
			 </div><!-- media-body -->
	 </div><!-- media -->
		 </a>
	</div><!-- list-group -->
</div><!-- card -->
@endforeach

@if ($messagesInbox->count() == 0)
	<div class="card border-0 text-center">
  <div class="card-body">
    <h4 class="mb-0 font-montserrat mt-2"></h4>
		<p class="lead text-muted mt-0">{{ trans('general.no_chats') }}</p>
  </div>
</div>
@endif

@if ($messagesInbox->hasMorePages())
  <div class="btn-block text-center d-none">
    {{ $messagesInbox->appends(['q' => request('q')])->links('vendor.pagination.loadmore') }}
  </div>
  @endif
