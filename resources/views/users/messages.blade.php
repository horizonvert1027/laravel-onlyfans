@extends('layouts.app')

@section('title'){{trans('general.messages')}} -@endsection

@section('content')
<section class="section section-sm pb-0 h-100 section-msg position-fixed">
      <div class="container h-100">
        <div class="row justify-content-center h-100">

          <div class="col-md-4 h-100 p-0 border-left wrapper-msg-inbox" id="messagesContainer">
              @include('includes.sidebar-messages-inbox')
          </div>

        <div class="col-md-8 h-100 p-0">
          <div class="card w-100 rounded-0 h-100 border-top">
            <div class="content px-4 py-3 d-scrollbars container-msg">

              <div class="flex-column d-flex justify-content-center text-center h-100">

                <div class="w-100">
                    
                    <div class="my-5 text-center">
        <span class="btn-block mb-3">
          <i class="feather icon-message-circle ico-no-result"></i>
        </span>
      <h4 style="margin-bottom:10px;" class="font-weight-light">As tuas mensagens</h4>
      <button class="btn btn-primary btn-sm w-small-100" data-toggle="modal" data-target="#newMessageForm">
                    {{trans('general.new_message')}}
                  </button>
      </div>

                </div>

              </div>
            </div><!-- container-msg -->

            </div><!-- card -->
            </div><!-- end col-md-6 -->
          </div><!-- end row -->
        </div><!-- end container -->
</section>

@include('includes.modal-new-message')
@endsection

@section('javascript')
<script src="{{ asset('public/js/messages.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/fileuploader/fileuploader-msg.js') }}?v={{$settings->version}}"></script>
<script src="{{ asset('public/js/paginator-messages.js') }}"></script>
@endsection
