@extends('layouts.app')

@section('title') {{$title}} -@endsection

@section('content')
  <section class="section section-sm">
    <div class="container">
      <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-12 py-5">
          <h2 class="mb-0 text-break">{{$title}}</h2>
        </p>
        </div>
      </div>

<div class="row justify-content-center">

@if( $users->total() != 0 )
          <div class="col-md-8 mb-5 mb-lg-0">
            <div class="row">

              @foreach ($users as $response)
              <div class="col-md-6 mb-4">
                @include('includes.listing-creators')
              </div><!-- end col-md-4 -->
              @endforeach

              @if($users->hasPages())
                <div class="w-100 d-block">
                  {{ $users->onEachSide(0)->appends([
                    'q' => request('q'), 
                    'gender' => request('gender'),
                    'min_age' => request('min_age'),
                    'max_age' => request('max_age')
                    ])->links() }}
                </div>
              @endif
            </div><!-- row -->
          </div><!-- col-md-9 -->

        @else
          <div class="col-md-9">
            <div class="my-5 text-center no-updates">
              <span class="btn-block mb-3">
                <i class="fa fa-user-slash ico-no-result"></i>
              </span>
            <h4 class="font-weight-light">{{trans('general.no_results_found')}}</h4>
            </div>
          </div>
        @endif
      </div>
    </div>
  </section>
@endsection
