<ul style="display:none!important;" class="list-group list-group-flush border-dashed-radius">

	<li class="list-group-item py-1 list-taxes">
    <div class="row">
      <div class="col">
        <small>{{trans('general.subtotal')}}:</small>
      </div>
      <div class="col-auto">
        <small class="subtotal font-weight-bold">
        {{ $settings->currency_position == 'left'  ? $settings->currency_symbol : (($settings->currency_position == 'left_space') ? $settings->currency_symbol.' ' : null) }}<span class="subtotalTip">0</span>{{ $settings->currency_position == 'right' ? $settings->currency_symbol : (($settings->currency_position == 'right_space') ? ' '.$settings->currency_symbol : null) }}
        </small>
      </div>
    </div>
  </li>

	@php
		$number = 0;
	@endphp

	@foreach (auth()->user()->isTaxable() as $tax)
		@php
			$number++;
		@endphp
		<li class="list-group-item py-1 list-taxes isTaxable">
	    <div class="row">
	      <div class="col">
	        <small>{{ $tax->name }} {{ $tax->percentage }}%:</small>
	      </div>
	      <div class="col-auto percentageAppliedTax{{$number}}" data="{{ $tax->percentage }}">
	        <small class="font-weight-bold">
	        {{ $settings->currency_position == 'left'  ? $settings->currency_symbol : (($settings->currency_position == 'left_space') ? $settings->currency_symbol.' ' : null) }}<span class="amount{{$number}}">0</span>{{ $settings->currency_position == 'right' ? $settings->currency_symbol : (($settings->currency_position == 'right_space') ? ' '.$settings->currency_symbol : null) }}
	        </small>
	      </div>
	    </div>
	  </li>
	@endforeach

	<li class="list-group-item py-1 list-taxes">
    <div class="row">
      <div class="col">
        <small>{{trans('general.total')}}:</small>
      </div>
      <div class="col-auto">
        <small class="totalPPV font-weight-bold">
        {{ $settings->currency_position == 'left'  ? $settings->currency_symbol : (($settings->currency_position == 'left_space') ? $settings->currency_symbol.' ' : null) }}<span class="totalTip">0</span>{{ $settings->currency_position == 'right' ? $settings->currency_symbol : (($settings->currency_position == 'right_space') ? ' '.$settings->currency_symbol : null) }}
        </small>
      </div>
    </div>
  </li>

</ul>
