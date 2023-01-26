@extends('admin.layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h4>
           {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i> {{ trans('general.donations') }} ({{$data->total()}})
          </h4>

        </section>

        <!-- Main content -->
        <section class="content">

        	<div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">
                  		{{ trans('general.donations') }}
                  	</h3>
                </div><!-- /.box-header -->

                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
               <tbody>

               	@if( $data->total() !=  0 && $data->count() != 0 )
                   <tr>
                      <th class="active">ID</th>
                      <th class="active">{{ trans('auth.full_name') }}</th>
                      <th class="active">{{ trans('general.user') }}</th>
                      <th class="active">{{ trans('auth.email') }}</th>
                      <th class="active">{{ trans('general.donation') }}</th>
                      <th class="active">{{ trans('general.payment_gateway') }}</th>
                      <th class="active">{{ trans('admin.date') }}</th>
                      <th class="active">{{ trans('admin.actions') }}</th>
                    </tr><!-- /.TR -->

                  @foreach( $data as $donation )
                    <tr>
                      <td>{{ $donation->id }}</td>
                      <td>{{$donation->fullname ? $donation->fullname : trans('general.someone')}}</td>
                      <td><a href="{{url($donation->user()->username)}}" target="_blank">{{$donation->user()->name}} <i class="fa fa-external-link-square"></i></a></td>
                      <td>{{ $donation->email }}</td>
                      <td>{{ App\Helper::amountFormat($donation->donation) }}</td>
                      <td>{{ $donation->payment_gateway }}</td>
                      <td>{{ date($settings->date_format, strtotime($donation->date)) }}</td>

                      <td> <a href="{{ url('panel/admin/donations',$donation->id) }}" class="btn btn-success btn-xs padding-btn">
                      		{{ trans('admin.view') }}
                      	</a>

                        @if($donation->approved == 0  )
                          <span class="label label-warning">{{trans('admin.pending')}}</span>
                        @endif

                      </td>
                    </tr><!-- /.TR -->
                    @endforeach

                    @else
                    <hr />
                    	<h3 class="text-center no-found">{{ trans('general.no_results_found') }}</h3>

                    @endif
                  </tbody>

                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
              @if( $data->lastPage() > 1 )
             {{ $data->links() }}
             @endif
            </div>
          </div>
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
@endsection
