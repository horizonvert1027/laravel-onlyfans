@extends('layouts.app')

@section('title') {{trans('general.payments')}} -@endsection

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
            
            <h2 class="mb-4 font-montserrat">{{trans('general.payments')}}</h2>

          @if ($transactions->count() != 0 && auth()->user()->verified_id == 'yes')

            <div class="btn-block mb-3 text-right">
              <span>
                {{trans('general.filter_by')}}

                <select class="ml-2 custom-select w-auto" id="filter">
                    <option @if (request()->is('my/payments')) selected @endif value="{{url('my/payments')}}">{{trans('general.payments_made')}}</option>
                    <option @if (request()->is('my/payments/received')) selected @endif value="{{url('my/payments/received')}}">{{trans('general.payments_received')}}</option>
                  </select>
              </span>
            </div>
          @endif

        @if ($transactions->count())
            @if (session('error_message'))
            <div class="alert alert-danger mb-3">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
              </button>

              <i class="fa fa-exclamation-triangle mr-2"></i> {{ trans('general.please_complete_all') }}
              <a href="{{ url('settings/page') }}#billing" class="text-white link-border">{{ trans('general.billing_information') }}</a>
            </div>
            @endif

        <div class="card">
          <div class="table-responsive">
            <table class="table table-striped m-0">
              <thead>
                <tr>
                @if (request()->is('my/payments'))
                  <th scope="col">Para</th>
                @endif
                  <th scope="col">{{trans('admin.amount')}}</th>
                  <th scope="col">{{trans('admin.type')}}</th>
                  <th scope="col">{{trans('admin.date')}}</th>
                  @if (request()->is('my/payments/received'))
                    <th scope="col">Pago</th>
                    <th scope="col">{{trans('general.earnings')}}</th>
                  @endif
                  <th scope="col">{{trans('admin.status')}}</th>
                  @if (request()->is('my/payments'))
                  <th> {{trans('general.invoice')}}</th>
                @endif
                </tr>
              </thead>

              <tbody>

                @foreach ($transactions as $transaction)
                  <tr>
                    @if (request()->is('my/payments'))
                    <td>{{ $transaction->subscribed()->username ?? trans('general.no_available')}}</td>
                    @endif
                    <td>{{ Helper::amountFormatDecimal($transaction->amount) }}</td>
                    <td>{{ __('general.'.$transaction->type) }}</td>
                    <td>{{ Helper::formatDate($transaction->created_at) }}</td>
                    @if (request()->is('my/payments/received'))
                      <td>{{ $transaction->user()->username ?? trans('general.no_available') }}</td>
                    <td>
                      {{ Helper::amountFormatDecimal($transaction->earning_net_user) }}

                      @if ($transaction->percentage_applied)
                        <a tabindex="0" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="{{trans('general.percentage_applied')}} {{ $transaction->percentage_applied }} {{trans('general.platform')}} @if ($transaction->direct_payment) ({{ __('general.direct_payment') }}) @endif">
                          <i class="far fa-question-circle"></i>
                        </a>

                      @endif
                    </td>
                    @endif
                    <td>
                      @if ($transaction->approved == '1')
                        {{trans('general.success')}}
                      @elseif ($transaction->approved == '2')
                        {{trans('general.canceled')}}
                        @if (request()->is('my/payments/received'))
                          <a tabindex="0" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="{{trans('general.payment_canceled')}}">
                      </a>
                        @endif
                      @else
                        {{trans('general.pending')}}
                      @endif
                    </td>
                    @if (request()->is('my/payments'))
                    <td>
                      @if ($transaction->approved == '1')
                      <a href="{{url('payments/invoice', $transaction->id)}}" target="_blank"><i class="far fa-file-alt"></i> {{trans('general.invoice')}}</a>
                    </td>
                  @else
                    {{trans('general.no_available')}}
                      @endif
                    @endif
                  </tr>
                @endforeach

              </tbody>
            </table>
          </div>
          </div><!-- card -->

          @if ($transactions->hasPages())
  			    	{{ $transactions->onEachSide(0)->links() }}
  			    	@endif

        @else
          <div class="my-5 text-center">
            <span class="btn-block mb-3">
              <i class="bi bi-receipt ico-no-result"></i>
            </span>
            @if (request()->is('my/payments'))
            <h4 class="texto font-weight-light">{{trans('general.not_payment_made')}}</h4>
          @else
            <h4 class="texto font-weight-light">{{trans('general.not_payment_received')}}</h4>
          @endif
          </div>
        @endif

        </div><!-- end col-md-6 -->

      </div>
    </div>
  </section>
@endsection
