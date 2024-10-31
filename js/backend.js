(function( $ ) {
	
	/*DATEPICKER*/
	
    $('.not_woo_customers_datepicker').datepicker({
	   dateFormat: 'yy-mm-dd',
       showButtonPanel: true
    });

				
	function delay(callback, ms) {
		  var timer = 0;
		  return function() {
			var context = this, args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
			  callback.apply(context, args);
			}, ms || 0);
		  };
	}
	
	$(".not_woo_customers_send_email").prop('checked',false);
	
	
	$(".not_woo_customers_send_email").on('click',function(){
		
		if ( $( '.not_woo_customers_send_email' ).is(':checked')) {
			
			$(".not_woo_customers_meta_box div").css('opacity','1');
			$(".not_woo_customers_meta_box div").css('height','initial');
		}else {
			$(".not_woo_customers_meta_box div").css('height','0px');
			$(".not_woo_customers_meta_box div").css('opacity','0');
		}
		
	});

	$(".getEmails").on('click', function (e) {
		
		if ( $( '.not_woo_customers_send_email' ).is(':checked')) {
			
				if( $(".not_woo_customers_from").val() != '' ){
					
					from =  $(".not_woo_customers_from").val();
					to =  $(".not_woo_customers_to").val();
					product_id =  $("#product_id").val();	
					
					$.ajax({
						type: 'POST',
						url: not_woo_customers.ajax_url,
						data: { 
							"action": "getOrderEmails",
							"from": from,
							"to": to,
							"product_id": product_id
						},							
						 beforeSend: function(data) {								
							$('.not_woo_customers_meta_box').addClass('not_woo_customers_loading');
							
						},								
						success: function (response) {
							$('.not_woo_customers_meta_box').removeClass('not_woo_customers_loading');
							
							if( response !='' ){
								
								alert( "Emails found: "+response );
								$( ".not_woo_customers_user_emails" ).html(response);

							}else {
								$( ".not_woo_customers_user_emails" ).html('');
							
							}
						},
						error:function(response){
							console.log('ERROR');
						}
					});
				
				}else alert( 'You need to first fill From Date at least.' );
		
		}
		
	});	
				

		//EXTENSIONS
		$(".not_woo_customers .wp_extensions").click(function(e){
			
			e.preventDefault();
			
			if( $('#not_woo_customers_extensions_popup').length > 0 ) {
			
				$(".not_woo_customers .get_ajax #not_woo_customers_extensions_popup").fadeIn();
				
				$("#not_woo_customers_extensions_popup .not_woo_customersclose").click(function(e){
					e.preventDefault();
					$("#not_woo_customers_extensions_popup").fadeOut();
				});		
				var extensions = document.getElementById('not_woo_customers_extensions_popup');
				window.onclick = function(event) {
					if (event.target === extensions) {
						extensions.style.display = "none";
						localStorage.setItem('hideIntro', '1');
					}
				}					
			}else{
				
				
				
				var action = 'not_woo_customers_extensions';
				$.ajax({
					type: 'POST',
					url: not_woo_customers.ajax_url,
					data: { 
						"action": action
					},							
					 beforeSend: function(data) {								
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.not_woo_customers').addClass('not_woo_customers_loading');
						
					},								
					success: function (response) {
						$('.not_woo_customers').removeClass('not_woo_customers_loading');
						if( response !='' ){
							$('.not_woo_customers .get_ajax' ).css('visibility','hidden');
							$('.not_woo_customers .get_ajax' ).append( response );
							$('.not_woo_customers .get_ajax #not_woo_customers_extensions_popup' ).css('visibility','visible');
							$(".not_woo_customers .get_ajax #not_woo_customers_extensions_popup").fadeIn();
							
							$("#not_woo_customers_extensions_popup .not_woo_customersclose").click(function(e){
								e.preventDefault();
								$("#not_woo_customers_extensions_popup").fadeOut();
							});		
							var extensions = document.getElementById('not_woo_customers_extensions_popup');
							window.onclick = function(event) {
								if (event.target === extensions) {
									extensions.style.display = "none";
									localStorage.setItem('hideIntro', '1');
								}
							}							
						}
					},
					error:function(response){
						console.log('error');
					}
				});			
			}
		});	
		

})( jQuery )	