@extends('admin.layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
           <h4>
           {{ trans('admin.admin') }} <i class="fa fa-angle-right margin-separator"></i> {{ trans('general.posts') }} #{{$data->id}}
          </h4>
        </section>

        <!-- Main content -->
        <section class="content">

        	<div class="row">
            <div class="col-xs-12">
              <div class="box">

              	<div class="box-body">
              		<dl class="dl-horizontal">

					  <!-- start -->
					  <dt>ID</dt>
					  <dd>{{$data->id}}</dd>
					  <!-- ./end -->

					  <!-- start -->
					  <dt>{{ trans('admin.description') }}</dt>
					  <dd>{{$data->description}}</dd>
					  <!-- ./end -->

					  <!-- start -->
					  <dt>{{ trans('general.user') }}</dt>
					  <dd><a href="{{url($data->user()->username)}}" target="_blank">{{ $data->user()->name }}</a></dd>
					  <!-- ./end -->

					  <!-- start -->
					  <dt>{{ trans('general.image') }}</dt>
					  <dd>@if($data->image !== '')
            <img src="{{asset('public/updates/images').'/'.$data->image}}" width="500" />
          @else
            {{ trans('general.no') }}
          @endif</dd>
					  <!-- ./end -->

					  <!-- start -->
					  <dt>{{ trans('admin.date') }}</dt>
					  <dd>{{date($settings->date_format, strtotime($data->date))}}</dd>
					  <!-- ./end -->

					</dl>
              	</div><!-- box body -->

              	<div class="box-footer">
                  	 <a href="{{ url('panel/admin/posts') }}" class="btn btn-default">{{ trans('auth.back') }}</a>

                     {!! Form::open([
                       'method' => 'POST',
                       'url' => "panel/admin/posts/delete/$data->id",
                       'class' => 'displayInline'
                     ]) !!}

                     {!! Form::button(trans('admin.delete'), ['class' => 'btn btn-danger pull-right margin-separator actionDelete']) !!}
                     {!! Form::close() !!}

                  </div><!-- /.box-footer -->
              </div><!-- box -->
            </div><!-- col -->
         </div><!-- row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
@endsection
