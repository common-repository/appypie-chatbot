 var modal = document.getElementById("myModal");
	var btn = document.getElementById("myBtn");
	var span = document.getElementsByClassName("close")[0];
	if(btn!=null){
		btn.onclick = function() {
		  modal.style.display = "flex";
		}

		span.onclick = function() {
		  modal.style.display = "none";
		}
	}

	window.onclick = function(event) {
	  if (event.target == modal) {
		modal.style.display = "none";
	  }
	}
 
 jQuery(function ($) {
   $('#chatbot').on('change',function(){
	  var chatbot = $(this).val();
	  $(".themeButton").removeClass("not-active");
	   $.ajax({
			method : 'POST',
			dataType: 'json',
			url: wpcb.ajax_url,
			data: {
				action : 'wpcb_selected', 
				selectedCB:chatbot,
			},
			})
		  .done( function( response ) {
		})
		.fail( function() {
			return false;
		})
   });
});
 
  jQuery(function ($) {
   $('.themeButton').click(function(){
	   var chatbot = $('#chatbot').val();
	   var botwidget = $('#botwidget').val();
	   var selectedBot =  $("#chatbot option:selected").text();
	   if(chatbot==""){
		   $('.chatbotempty').show();
		   return false;
	   }
	   $('.themeButton').text('Processing...');
	   $('.chatbotempty').hide();
	   $.ajax({
			method : 'POST',
			url    : wpcb.ajax_url,
			data   : {
				action : 'wpcb_selected', 
				selectedCB:chatbot,
				botwidget:botwidget
			},
			})
		  .done( function( response ) {
			  $('.themeButton').text('Add to your site');
			  $('.success').html("Congratulations, Your bot " +selectedBot+" is connected with your wordpress website.! ");
			  $('.success').show();
			  //setTimeout(function(){ $('.success').hide(); }, 5000);
		})
		.fail( function() {
			return false;
		})
   });
});
 

jQuery(function($) {
    var checkVal = $('#botwidget').val();
    if (checkVal == "") {
        $('#botwidget').attr('value', "off");
    }
    if (checkVal == "on") {
        $('#botwidget').trigger("click");
    }

    $('#botwidget').on('click', function() {
		$(".themeButton").removeClass("not-active");
        var checkVal = $('#botwidget').val();
        if (checkVal == "on") {
            $('#botwidget').attr('value', "off");
        }
        if (checkVal == "off") {
            $('#botwidget').attr('value', "on");
        }
		if(checkVal=="off"){
			var data = "on";
		}else{
			var data = "off";
		}
		if(data){
			$.ajax({
				method : 'POST',
				url    : wpcb.ajax_url,
				data   : {
					action : 'wpcb_enable',
					botvalue:data		
				},
				})
			  .done(function(response){
				})
				.fail( function() {
				return false;
		  });
	   }
    });
});

jQuery(function($) {
$('.disconnectact').on('click', function() {
	var result = confirm("Are you sure, You want to disconnect your account ?");
	var disconnect = $("#disconnect").val();
	if (result){
		$.ajax({
			method : 'POST',
			url: wpcb.ajax_url,
			data: {
				action : 'wpcb_disconnect',
				cb_id:disconnect		
			},
			})
		  .done( function(response){
			 location.reload();
			})
			.fail( function() {
			return false;
	  })
	}
  });
});





