//<--------- Messages -------//>
(function($) {
	"use strict";

	//<-------- * TRIM Space * ----------->
	function trim(string) {
		return string.replace(/^\s+/g,'').replace(/\s+$/g,'');
	}

		// Scroll to paginator chat
		var scr = $('#contentDIV')[0].scrollHeight;
		$('#contentDIV').animate({scrollTop: scr},100);

	$(document).on('click','#button-reply-msg',function(s) {

	 s.preventDefault();

	 var element = $(this);

});//<<<-------- * END FUNCTION CLICK * ---->>>>

	//<----- Chat Live
	var request = false;

	function Chat() {

		var param   = /^[0-9]+$/i;
		var lastID  = $('li.chatlist:last').attr('data');
		var liveID  = $('.live-data').attr('data');
		var creator = $('.live-data').attr('data-creator');

		if (! liveOnline) {
			return false;
		}

		if (! request) {
			request = true;

			//****** COUNT DATA
			request = $.ajax({
			  method: "GET",
			  url: URL_BASE+"/get/data/live",
			  data: {
					last_id:lastID ? lastID : 0,
					live_id: liveID,
					creator: creator
				},
				complete: function() { request = false; }
			}).done(function(response) {

			if (response) {

				// Live end
				if (response.status == 'offline') {
					window.location.reload();
					return false;
				}

				// Session Null
				if (response.session_null) {
					window.location.reload();
				}

				// Comments
				if (response.total !== 0) {

					// Scroll to paginator chat
					var scr = $('#contentDIV')[0].scrollHeight;
					$('#contentDIV').animate({scrollTop: scr},100);

				var total_data = response.comments.length;

				for (var i = 0; i < total_data; ++i) {
					$(response.comments[i]).hide().appendTo('#allComments').fadeIn(250);
					}
				} // response.total !== 0

				// Online users
				$('#liveViews').html(response.onlineUsers);

				// Likes
				if (response.likes !== 0) {
					$('#counterLiveLikes').html(response.likes);
				} else {
					$('#counterLiveLikes').html('');
				}

				if (response.time) {
					$('.limitLiveStreaming > span').html(response.time);
				}

			}//<-- response

			},'json');

			}// End Request

	}//End Function TimeLine

	setInterval(Chat, 1000);

	// End Live Stream
	$(document).on('click','#endLive', function(e){

	   e.preventDefault();

	   var element = $(this);
	   element.blur();

	 swal(
	   {
			 title: delete_confirm,
		   text: confirm_end_live,
		   type: "error",
		   showLoaderOnConfirm: true,
		   showCancelButton: true,
		   confirmButtonColor: "#DD6B55",
		   confirmButtonText: yes_confirm_end_live,
		   cancelButtonText: cancel_confirm,
	     closeOnConfirm: false,
	       },
	       function(isConfirm){

					 if (isConfirm) {
						 (function() {
				        $('#formEndLive').ajaxForm({
				        dataType : 'json',
				        success:  function(response) {
				          // Exit
				        },
				        error: function(responseText, statusText, xhr, $form) {
				             // error
				             swal({
				                 type: 'error',
				                 title: error_oops,
				                 text: ''+error_occurred+' ('+xhr+')',
				               });
				         }
				       }).submit();
				     })(); //<--- FUNCTION %
					 } // isConfirm
	        });
	    });// End live

			// Exit Live
			$(document).on('click','#exitLive', function(e) {
				e.preventDefault();
		 	  var element = $(this);
		 	  element.blur();

	 	 swal(
	 	   {
	 			 title: delete_confirm,
	 		   text: confirm_exit_live,
	 		   type: "error",
	 		   showLoaderOnConfirm: true,
	 		   showCancelButton: true,
	 		   confirmButtonColor: "#DD6B55",
	 		   confirmButtonText: yes_confirm_exit_live,
	 		   cancelButtonText: cancel_confirm,
	 	     closeOnConfirm: false,
	 	       },
	 	       function(isConfirm){

	 					 if (isConfirm) {
	 						 (function() {
	 				        window.location.href = URL_BASE;
	 				     })(); //<--- FUNCTION %
	 					 } // isConfirm
	 	        });
			});// Exit live

			//============= Comments
			$(document).on('keypress','#commentLive',function(e) {

				if (e.which == 13) {

				e.preventDefault();

				var element = $(this);
				element.blur();

				$('.blocked').show();

					 $.ajax({
					 	headers: {
			        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    		},
					   type: "POST",
					   url: URL_BASE+"/comment/live",
					   dataType: 'json',
					   data: $("#formSendCommentLive").serialize(),
					   success: function(result){

					   	if (result.success) {
								element.val('');
								$('.blocked').hide();
								$('#showErrorMsg').html('');
								$('#errorMsg').hide();
							} else {

					   		var error = '';
								var $key  = '';

				            for ($key in result.errors) {
				            	error += '<li><i class="fa fa-times-circle"></i> ' + result.errors[$key] + '</li>';
				            }

										$('#showErrorMsg').html(error);
										$('#errorMsg').fadeIn(500);
										$('.blocked').hide();
						}
					 }//<-- RESULT
				   }).fail(function(jqXHR, ajaxOptions, thrownError)
					 {
						 $('.popout').removeClass('popout-success').addClass('popout-error').html(error_occurred).slideDown('500').delay('5000').slideUp('500');
						 $('.blocked').hide();
					 });//<--- AJAX

				 }//e.which == 13
			});//<----- CLICK

			// Hide Top Menu y Chat
			$(document).on('click', '#full-screen-video', function(e) {

				if ($(window).width() <= 767) {
					$('.liveContainerFullScreen').toggleClass('controls-hidden');

					if ($('.liveContainerFullScreen').hasClass('controls-hidden')) {
						$(".live-top-menu").animate({"top": "-80px" }, "fast");
						$(".wrapper-live-chat").animate({"bottom": "-250px" }, "fast");

					} else {
						$(".live-top-menu" ).animate({"top": "0" }, "slow");
						$(".wrapper-live-chat" ).animate({"bottom": "0" }, "slow");
					}
				}
			});

			/*========= Like ==============*/
			$(document).on('click','.button-like-live',function(e) {
				var element     = $(this);
				var id          = $('.liveContainerFullScreen').attr("data-id");
				var data        = 'id=' + id;

				e.preventDefault();

				element.blur();

				if (! id) {
					return false;
				}

					 $.ajax({
					 	headers: {
			        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    		},
					   type: 'POST',
					   url: URL_BASE+"/live/like",
						 dataType: 'json',
					   data: data,
					   success: function(result) {

					   	if (result.success) {

								if (result.likes !== 0) {
									$('#counterLiveLikes').html(result.likes);
								} else {
									$('#counterLiveLikes').html('');
								}

								if (element.hasClass('active')) {
										element.removeClass('active');
										element.find('i').removeClass('bi-heart-fill').addClass('bi-heart');

										if (result.likes !== 0) {
											$('#counterLiveLikes').html(result.likes);
										} else {
											$('#counterLiveLikes').html('');
										}

									} else {
										element.addClass('active');
										element.find('i').removeClass('bi-heart').addClass('bi-heart-fill');
									}

					   	} else {
								window.location.reload();
								element.removeClass('button-like-live');
								element.removeClass('active');
					   	}
						}//<-- RESULT
				   }).fail(function(jqXHR, ajaxOptions, thrownError)
					 {
						 $('.popout').removeClass('popout-success').addClass('popout-error').html(error_occurred).slideDown('500').delay('5000').slideUp('500');
					 });//<--- AJAX

			});//<----- LIKE

})(jQuery);
