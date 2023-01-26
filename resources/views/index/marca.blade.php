@extends('layouts.app')

@section('title') {{trans('general.marca')}} -@endsection

@section('css')
  <script type="text/javascript">
      var error_scrollelement = {{ count($errors) > 0 ? 'true' : 'false' }};
  </script>
@endsection

@section('content')
  <section class="section section-sm">
    <div class="container pt-lg-md">
        <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-12 py-5">
          <h2 class="mb-0 font-montserrat">
            Recursos da marca
          </h2>
          <p>
       Logotipos, ícones sociais e outros modelos ajudarão você a usar nossa marca.
      </p>
        </div>
      </div>
      <div class="row justify-content-center">
          <div class="col-lg-10">
          <div class="container mb-4"> <div class="bundles-about__wrapper">
					<div class="bundles-about__content">
						<h2 style=" font-size: 20px!important; font-weight: 600!important; ">Diretrizes da marca</h2><p></p>
						<p>Adoramos que você ame o Oprivado. Queremos tornar mais fácil para você usar nossa marca da maneira certa. Explore este guia rápido de nossos elementos básicos de design para ver como fazer isso.</p><p></p>
						<a href="https://blog.oprivado.com/wp-content/uploads/2023/01/Icon.zip" class="btn-arrow btn btn-lg btn-main btn-outline-primary btn-w px-4">
                         Baixar</a>
					</div>
					<div class="imagem1 bundles-about__img">
					</div>
				</div></div></div> </div> 
				
			<div class="row justify-content-center">
          <div class="col-lg-10">
          <div class="container mb-4"> <div class="bundles-about__wrapper">
					<div class="bundles-about__content">
						<h2 style=" font-size: 20px!important; font-weight: 600!important; ">Logotipo</h2><p></p>
						<p>Quer baixar o logotipo do Oprivado? Arquivos de arte (.PNG) podem ser baixados daqui. Para um uso adequado, consulte a seção Logotipo em nossas Diretrizes de marca.</p><p></p>
						<a href="https://blog.oprivado.com/wp-content/uploads/2023/01/Logotipos-Oprivado.zip" class="btn-arrow btn btn-lg btn-main btn-outline-primary btn-w px-4">
                         Baixar</a>
					</div>
					<div class="imagem2 bundles-about__img">
					</div>
				</div></div></div> </div> 
				
					<div class="row justify-content-center">
          <div class="col-lg-10">
          <div class="container mb-4"> <div class="bundles-about__wrapper">
					<div class="bundles-about__content">
						<h2 style=" font-size: 20px!important; font-weight: 600!important; ">Ícones sociais</h2><p></p>
						<p>Quer representar a sua presença no Oprivado, sozinho ou ao lado de outros ícones sociais? É preferível que nosso logotipo esteja livre de um contêiner, mas você também encontrará as opções para usar um círculo, quadrado ou quadrado com cantos arredondados.</p><p></p>
						<a href="https://blog.oprivado.com/wp-content/uploads/2023/01/Icones-Oprivado.zip" class="btn-arrow btn btn-lg btn-main btn-outline-primary btn-w px-4">
                         Baixar</a>
					</div>
					<div class="imagem3 bundles-about__img">
					</div>
				</div></div></div> </div>

</div>
</section>
   
@endsection

@section('javascript')

  @if ($settings->earnings_simulator == 'on')
  <script type="text/javascript">

  function decimalFormat(nStr)
  {
    @if ($settings->decimal_format == 'dot')
     var $decimalDot = '.';
     var $decimalComma = ',';
     @else
     var $decimalDot = ',';
     var $decimalComma = '.';
     @endif

     @if ($settings->currency_position == 'left')
     var currency_symbol_left = '{{$settings->currency_symbol}}';
     var currency_symbol_right = '';
     @else
     var currency_symbol_right = '{{$settings->currency_symbol}}';
     var currency_symbol_left = '';
     @endif

      nStr += '';
      var x = nStr.split('.');
      var x1 = x[0];
      var x2 = x.length > 1 ? $decimalDot + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
          var x1 = x1.replace(rgx, '$1' + $decimalComma + '$2');
      }
      return currency_symbol_left + x1 + x2 + currency_symbol_right;
    }

    function earnAvg() {
      var fee = {{ $settings->fee_commission }};
      @if($settings->currency_code == 'JPY')
       $decimal = 0;
      @else
       $decimal = 2;
      @endif

      var monthlySubscription = parseFloat($('#rangeMonthlySubscription').val());
      var numberFollowers = parseFloat($('#rangeNumberFollowers').val());

      var estimatedFollowers = (numberFollowers * 5 / 100)
      var followersAndPrice = (estimatedFollowers * monthlySubscription);
      var percentageAvgFollowers = (followersAndPrice * fee / 100);
      var earnAvg = followersAndPrice - percentageAvgFollowers;

      return decimalFormat(earnAvg.toFixed($decimal));
    }
   $('#estimatedEarn').html(earnAvg());

   $("#rangeNumberFollowers, #rangeMonthlySubscription").on('change', function() {

     $('#estimatedEarn').html(earnAvg());

   });
  </script>
@endif

@if (session('success_verify'))
  <script type="text/javascript">

	swal({
		title: "{{ trans('general.welcome') }}",
		text: "{{ trans('users.account_validated') }}",
		type: "success",
		confirmButtonText: "{{ trans('users.ok') }}"
		});
    </script>
	 @endif

	 @if (session('error_verify'))
   <script type="text/javascript">
	swal({
		title: "{{ trans('general.error_oops') }}",
		text: "{{ trans('users.code_not_valid') }}",
		type: "error",
		confirmButtonText: "{{ trans('users.ok') }}"
		});
    </script>
	 @endif

@endsection
