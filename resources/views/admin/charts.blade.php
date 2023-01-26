<script type="text/javascript">
(function($) {
"use strict";

  function decimalFormat(nStr)
  {
    @if ($settings->decimal_format == 'dot')
     var $decimalDot = '.';
     var $decimalComma = ',';
     @else
     var $decimalDot = ',';
     var $decimalComma = '.';
     @endif

     switch ('{{$settings->currency_position}}') {
       case 'left':
       var currency_symbol_left = '{{$settings->currency_symbol}}';
       var currency_symbol_right = '';
       break;

       case 'left_space':
       var currency_symbol_left = '{{$settings->currency_symbol}} ';
       var currency_symbol_right = '';
       break;

       case 'right':
       var currency_symbol_right = '{{$settings->currency_symbol}}';
       var currency_symbol_left = '';
       break;

       case 'right_space':
       var currency_symbol_right = ' {{$settings->currency_symbol}}';
       var currency_symbol_left = '';
       break;

       default:
       var currency_symbol_right = '{{$settings->currency_symbol}}';
       var currency_symbol_left = '';
       break;
     }// End switch

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
    function transparentize(color, opacity) {
			var alpha = opacity === undefined ? 0.5 : 1 - opacity;
			return Color(color).alpha(alpha).rgbString();
		}

    var init = document.getElementById("ChartSales").getContext('2d');

    const gradient = init.createLinearGradient(0, 0, 0, 300);
                      gradient.addColorStop(0, '#00a65a');
                      gradient.addColorStop(1, '#00a65a2b');

    const lineOptions = {
                          pointRadius: 4,
                          pointHoverRadius: 6,
                          hitRadius: 5,
                          pointHoverBorderWidth: 3
                      }

    var ChartEarnings = new Chart(init, {
        type: 'line',
        data: {
            labels: [{!!$label!!}],
            datasets: [{
                label: '{{trans('general.earnings')}} ',
                backgroundColor: gradient,
                borderColor: '#00a65a',
                data: [{!!$data!!}],
                borderWidth: 2,
                fill: true,
                lineTension: 0.4,
                ...lineOptions
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        min: 0, // it is for ignoring negative step.
                        display: true,
                         maxTicksLimit: 8,
                         padding: 10,
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return '@if($settings->currency_position == 'left'){{ $settings->currency_symbol }}@elseif ($settings->currency_position == 'left_space'){{ $settings->currency_symbol }} @endif' + value + '@if($settings->currency_position == 'right'){{ $settings->currency_symbol }}@elseif ($settings->currency_position == 'right_space'){{ ' '.$settings->currency_symbol }}@endif';
                        }
                    },
                    gridLines: {
                      display:true
                    }
          }],
            xAxes: [{
              gridLines: {
                display:false
              },
              display: true,
              ticks: {
                maxTicksLimit: 15,
                padding: 5,
              }
            }]
          },
            tooltips: {
              mode: 'index',
              intersect: false,
              reverse: true,
              backgroundColor: '#000',
              xPadding: 16,
              yPadding: 16,
              cornerRadius: 4,
              caretSize: 7,
                callbacks: {
                    label: function(t, d) {
                        var xLabel = d.datasets[t.datasetIndex].label;
                        var yLabel = t.yLabel == 0 ? decimalFormat(t.yLabel) : decimalFormat(t.yLabel.toFixed(2));
                        return xLabel + ': ' + yLabel;
                    }
                }
            },
            hover: {
              mode: 'index',
              intersect: false
            },
            legend: {
                display: false
            },
            responsive: true,
						maintainAspectRatio: false
        }
    });

		var sales = document.getElementById("ChartSubscriptions").getContext('2d');

		const gradientSales = sales.createLinearGradient(0, 0, 0, 300);
											gradientSales.addColorStop(0, '#268707');
											gradientSales.addColorStop(1, '#2687072e');

		const lineOptionsSales = {
													pointRadius: 4,
													pointHoverRadius: 6,
													hitRadius: 5,
													pointHoverBorderWidth: 3
											}

		var ChartArea = new Chart(sales, {
				type: 'bar',
				data: {
						labels: [{!!$label!!}],
						datasets: [{
								label: '{{__('admin.subscriptions')}}',
								backgroundColor: '#268707',
								borderColor: '#268707',
								data: [{!!$datalastSales!!}],
								borderWidth: 2,
								fill: true,
								lineTension: 0.4,
								...lineOptionsSales
						}]
				},
				options: {
						scales: {
								yAxes: [{
										ticks: {
											min: 0, // it is for ignoring negative step.
											 display: true,
												maxTicksLimit: 8,
												padding: 10,
												beginAtZero: true,
												callback: function(value, index, values) {
														return value;
												}
										}
								}],
								xAxes: [{
									gridLines: {
										display:false
									},
									display: true,
									ticks: {
										maxTicksLimit: 15,
										padding: 5,
									}
								}]
						},
						tooltips: {
							mode: 'index',
							intersect: false,
							reverse: true,
							backgroundColor: '#000',
							xPadding: 16,
							yPadding: 16,
							cornerRadius: 4,
							caretSize: 7,
								callbacks: {
										label: function(t, d) {
												var xLabel = d.datasets[t.datasetIndex].label;
												var yLabel = t.yLabel;
												return xLabel + ': ' + yLabel;
										}
								},
						},
						hover: {
							mode: 'index',
							intersect: false
						},
						legend: {
								display: false
						},
						responsive: true,
						maintainAspectRatio: false
				}
		});

    //jvectormap data
    var visitorsData = {
    <?php

    $users_countries = User::where('countries_id', '<>', '')->groupBy('countries_id')->get();
    foreach ( $users_countries as $key ) {
      $total = Countries::find($key->countries_id);
    ?>
    "{{ $key->country()->country_code }}": {{ $total->users()->count() }}, <?php } ?>
    };

    //World map by jvectormap
    $('#world-map').vectorMap({
      map: 'world_mill_en',
      backgroundColor: "#ffffff",
      regionStyle: {
        initial: {
          fill: '#9491c4',
          "fill-opacity": 1,
          stroke: 'none',
          "stroke-width": 0,
          "stroke-opacity": 1
        }
      },
      series: {
        regions: [{
            values: visitorsData,
            scale: ["#92c1dc", "#00a65a"],
            normalizeFunction: 'polynomial'
          }]
      },
      onRegionLabelShow: function (e, el, code) {
        if (typeof visitorsData[code] != "undefined")
          el.html(el.html() + ': ' + visitorsData[code] + ' {{ trans("admin.registered_members") }}');
      }
    });

    //<<======= Get data Earnings Dashboard Admin
  $(document).on('change','.filterEarnings', function(e) {
  var range = $(this).val();

  $(this).blur();
  
  $('#loadChart').show();

  $.ajaxSetup({
		 headers: {
			 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 }
	 });

  $.ajax({
    type : 'post',
    url: URL_BASE+'/get/earnings/admin/' + range,
    success: function(data) {

      // Empty any previous chart data
      ChartEarnings.data.labels = [];
      ChartEarnings.data.datasets[0].data = [];
      
      ChartEarnings.data.labels = data.labels;
      ChartEarnings.data.datasets.forEach((dataset) => {
          dataset.data = data.datasets;
      });

      // Re-render the chart
      ChartEarnings.update();

      $('#loadChart').hide();
    }
  }).fail(function(jqXHR, ajaxOptions, thrownError) {
    swal({
    			title: "{{ trans('general.error_oops') }}",
    			text: "{{ trans('general.error') }}",
    			type: "error",
    			confirmButtonText: "{{ trans('users.ok') }}"
    			});
    $('#loadChart').hide();
  });

});

})(jQuery);
</script>
