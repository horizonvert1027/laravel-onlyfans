@extends('layouts.app')

@section('title') {{trans('general.withdrawals')}} -@endsection

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
            
                      <h2 class="mb-4 font-montserrat">Levantamentos</h2>

          @include('errors.errors-forms')

        @if (auth()->user()->payment_gateway == '')
          <div class="alert alert-warning alert-dismissible" role="alert">
          <span class="alert-inner--text">Selecione uma
            <a href="{{url('settings/payout/method')}}" class="link-border">conta bancária</a>
          </span>
        </div>
        @endif

            <div class="row">
              <div class="col-md-12">

                @php
                  $datePaid = Withdrawals::select('date')
                      ->where('user_id', auth()->user()->id)
                      ->where('status','pending')
                      ->orderBy('id','desc')
                      ->first();
                @endphp

                <div class="alert alert-primary overflow-hidden" role="alert">

                  <div class="inner-wrap">
                        <h2><strong>{{Helper::amountFormatDecimal(auth()->user()->balance)}}</strong>
                        
                    <h4>
                      <small>{{trans('general.balance')}} disponíveis</small>  
                    </h4>

                    @if (! $datePaid)
                      <small style="color: #fff!important;" class="form-text text-muted btn-block">
                        O montante mínimo de levatamento é 10.000 KZ
                  @endif

                  @if ($datePaid)
                    @if (! $settings->specific_day_payment_withdrawals)
                     <small style="color: #fff!important;" class="form-text text-muted">O seu pagamento estará na sua conta dentro de 2 dias *</small>

                    @else
                      {{ trans('users.date_paid') }} {{ Helper::formatDate(Helper::paymentDateOfEachMonth($settings->specific_day_payment_withdrawals)) }}
                    @endif
                  @endif
                  </small>
                  </div>

                <span class="icon-wrap"><i class="bi bi-arrow-left-right"></i></span>

              </div><!-- /alert -->

                <h5>

                  @if (auth()->user()->balance >= $settings->amount_min_withdrawal
                      && auth()->user()->payment_gateway != ''
                      && auth()->user()->withdrawals()
                      ->where('status','pending')
                      ->count() == 0)

                  {!! Form::open([
                   'method' => 'POST',
                   'url' => "settings/withdrawals",
                   'class' => 'd-inline'
                 ]) !!}

                 @if ($settings->type_withdrawals == 'custom')
                   <div class="form-group mt-3">
                     <div class="input-group mb-2">
                     <div class="input-group-prepend">
                       <span class="input-group-text">{{$settings->currency_symbol}}</span>
                     </div>
                         <input class="form-control form-control-lg isNumber" autocomplete="off" name="amount" placeholder="{{trans('admin.amount')}}" type="text">
                     </div>
                   </div><!-- End form-group -->
                 @endif

                  {!! Form::submit(trans('general.make_withdrawal'), ['class' => 'btn btn-1 btn-success mb-2 saveChanges']) !!}
                  {!! Form::close() !!}

                @else
                  <button class="btn btn-1 btn-success mb-2 disabled e-none">{{trans('general.make_withdrawal')}}</button>
                @endif
                </h5>

              </div><!-- col-md-12 -->
            </div>

          @if ($withdrawals->count() != 0)
          <h2 style="margin-top: 50px;margin-bottom: 20px!important;" class="mb-0 font-montserrat">Últimas transações</h2>
          <div class="card">
          <div class="table-responsive">
            <table class="table table-striped m-0">
              <thead>
                <tr>
                  <th scope="col">{{trans('admin.amount')}}</th>
                  <th scope="col">{{trans('admin.date')}}</th>
                  <th scope="col">{{trans('admin.status')}}</th>
                </tr>
              </thead>

              <tbody>

                @foreach ($withdrawals as $withdrawal)
                  <tr>
                    <td>{{Helper::amountFormatDecimal($withdrawal->amount)}}</td>
                   
                    <td>{{Helper::formatDate($withdrawal->date)}}
                    </td>
                    <td>@if ( $withdrawal->status == 'paid' )
                    {{trans('general.paid')}} @if ($withdrawal->status != 'paid' && Carbon\Carbon::parse($withdrawal->estimated_payment)->shortAbsoluteDiffForHumans() == '5d')

                    {{ trans('general.in_process') }}
                  @else

                  ({{Helper::formatDate($withdrawal->date_paid)}})

                  @endif
                    @else
                  {{trans('general.pending_to_pay')}} @if ($withdrawal->status != 'paid' && Carbon\Carbon::parse($withdrawal->estimated_payment)->shortAbsoluteDiffForHumans() <> '5d')
                      {!! Form::open([
                        'method' => 'POST',
                        'url' => "delete/withdrawal/$withdrawal->id",
                        'class' => 'd-inline'
                      ]) !!}

                      {!! Form::button(trans('general.cancelar'), ['class' => 'btn btn-success btn-sm deleteW p-1 px-2']) !!}
                      {!! Form::close() !!}  @endif
                    @endif
                  </td>
                </tr>
                @endforeach

              </tbody>
            </table>
          </div>
          </div><!-- card -->

          @if ($withdrawals->hasPages())
            {{ $withdrawals->links() }}
          @endif

        @endif
        </div><!-- end col-md-6 -->

      </div>
    </div>
  </section>
@endsection
