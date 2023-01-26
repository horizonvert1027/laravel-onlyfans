@extends('admin.layout')

@section('content')
	<h5 class="mb-4 fw-light">
    <a class="text-reset" href="{{ url('panel/admin') }}">{{ __('admin.dashboard') }}</a>
      <i class="bi-chevron-right me-1 fs-6"></i>
      <span class="text-muted">{{ __('admin.members') }} ({{$data->total()}})</span>
  </h5>

<div class="content">
	<div class="row">

		<div class="col-lg-12">

      @if (session('info_message'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <i class="bi-exclamation-triangle me-1"></i>	{{ session('info_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

			@if (session('success_message'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check2 me-1"></i>	{{ session('success_message') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
                </div>
              @endif

			<div class="card shadow-custom border-0">
				<div class="card-body p-lg-4">

          @if ($data->count() != 0)
						<div class="d-lg-flex justify-content-lg-between align-items-center mb-2 w-100">

							<form action="{{ url('panel/admin/members') }}" id="formSort" method="get">
								<select name="sort" id="sort" class="form-select d-inline-block w-auto filter">
									<option @if ($sort == '') selected="selected" @endif value="">{{ trans('admin.sort_id') }}</option>
									<option @if ($sort == 'admins') selected="selected" @endif value="admins">{{ trans('users.admin') }}</option>
										<option @if ($sort == 'creators') selected="selected" @endif value="creators">{{ trans('general.creators') }}</option>
									<option @if ($sort == 'email_pending') selected="selected" @endif value="email_pending">{{ trans('general.verification_pending') }} ({{trans('auth.email')}})</option>
		        		</select>
							</form><!-- form -->

						<!-- form -->
            <form class="mt-lg-0 mt-2 position-relative" role="search" autocomplete="off" action="{{ url('panel/admin/members') }}" method="get">
							<i class="bi bi-search btn-search bar-search"></i>
             <input type="text" name="q" class="form-control ps-5 w-auto" value="" placeholder="{{ __('general.search') }}">
          </form><!-- form -->
				</div>

            @endif

					<div class="table-responsive p-0">
						<table class="table table-hover">
						 <tbody>

               @if ($data->total() !=  0 && $data->count() != 0)
                  <tr>
                     <th class="active">ID</th>
										 <th class="active">{{ trans('auth.full_name') }}</th>
										 <th class="active">{{ trans('general.balance') }}</th>
										 <th class="active">{{ trans('general.wallet') }}</th>
										 <th class="active">{{ trans('general.posts') }}</th>
										 <th class="active">{{ trans('admin.date') }}</th>
										 <th class="active">IP</th>
										 <th class="active">{{ trans('admin.role') }}</th>
										 <th class="active">{{ trans('admin.verified') }}</th>
										 <th class="active">{{ trans('admin.status') }}</th>
										 <th class="active">{{ trans('admin.actions') }}</th>
                   </tr>

                 @foreach ($data as $user)
                   <tr>
                     <td>{{ $user->id }}</td>
                     <td title="{{$user->name}}">
                       <a href="{{ url($user->username) }}" target="_blank">
                         <img src="{{ Helper::getFile(config('path.avatar').$user->avatar) }}" width="40" height="40" class="rounded-circle me-1" />
												 {{ str_limit($user->name, 20, '...') }} <i class="bi-box-arrow-up-right"></i>
                       </a>
                     </td>
                     <td>{{ Helper::amountFormatDecimal($user->balance)}}</td>
                     <td>{{ Helper::amountFormatDecimal($user->wallet)}}</td>
                     <td>{{ $user->updates()->count() }}</td>
                     <td>{{ Helper::formatDate($user->date) }}</td>
                     <td>{{ $user->ip ? $user->ip : trans('general.no_available') }}</td>
                     <td>
											 @if ($user->role == 'admin' && $user->permissions == 'full_access')
 												<span class="rounded-pill badge bg-primary">{{ trans('general.super_admin') }}</span>
 											@elseif ($user->role == 'admin' && $user->permissions != 'full_access')
 													<span class="rounded-pill badge bg-primary">{{ trans('admin.role_admin') }}</span>
 											@else
 												<span class="rounded-pill badge bg-secondary">{{ trans('admin.normal') }}</span>
 											@endif
                     </td>

										 @php

										 if ($user->verified_id == 'no' ) {
		                        			$verified    = 'warning';
		  								$_verified = trans('admin.pending');
		               } elseif ($user->verified_id == 'yes' ) {
		                        			$verified = 'success';
		  								$_verified = trans('admin.verified');
		                        		} else {
		                        			$verified = 'danger';
		  								$_verified = trans('admin.reject');
		                        		}
		                   @endphp

		                        <td><span class="rounded-pill badge bg-{{$verified}}">{{ $_verified }}</span></td>

                    @php

										if ($user->status == 'pending') {
					                  $mode    = 'info';
					                  $_status = trans('admin.pending');
					                           } elseif ($user->status == 'active') {
					                  $mode = 'success';
					                  $_status = trans('admin.active');
					                           } else {
					                 $mode = 'warning';
					                 $_status = trans('admin.suspended');
                         }
                    @endphp

                     <td><span class="rounded-pill badge bg-{{$mode}}">{{ $_status }}</span></td>
                     <td>

								<div class="d-flex">
                    @if ($user->id <> auth()->user()->id && $user->id <> 1)

                  <a href="{{ url('panel/admin/members/edit', $user->id) }}" class="btn btn-success rounded-pill btn-sm me-2">
                         <i class="bi-pencil"></i>
                       </a>

                  {!! Form::open([
                 'method' => 'POST',
                 'url' => ['panel/admin/members', $user->id],
                 'id' => 'form'.$user->id,
                 'class' => 'd-inline-block align-top'
               ]) !!}
               {!! Form::button('<i class="bi-trash-fill"></i>', ['data-url' => $user->id, 'class' => 'btn btn-danger rounded-pill btn-sm actionDelete']) !!}
           {!! Form::close() !!}
					 </div>

        @else
         ------------
                         @endif
                       </td>

                   </tr><!-- /.TR -->
                   @endforeach

									@else
										<h5 class="text-center p-5 text-muted fw-light m-0">{{ trans('general.no_results_found') }}

                      @if (isset($query))
                        <div class="d-block w-100 mt-2">
                          <a href="{{url('panel/admin/members')}}"><i class="bi-arrow-left me-1"></i> {{ trans('auth.back') }}</a>
                        </div>
                      @endif
                    </h5>
									@endif

								</tbody>
								</table>
							</div><!-- /.box-body -->

				 </div><!-- card-body -->
 			</div><!-- card  -->

			@if ($data->lastPage() > 1)
			{{ $data->appends(['q' => $query, 'sort' => $sort])->onEachSide(0)->links() }}
		@endif
 		</div><!-- col-lg-12 -->

	</div><!-- end row -->
</div><!-- end content -->
@endsection
