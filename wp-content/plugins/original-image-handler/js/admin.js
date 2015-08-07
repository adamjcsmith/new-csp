(function ($) {
	"use strict";
	$(function () {
		$( ".oih-tab" ).click(function(event) {
			event.preventDefault();
			$('.oih-tab-section').addClass('closed');
			$( $(this).attr('href') ).removeClass('closed');
			$('.oih-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
		});

	    $('.datepicker').datepicker({
	        dateFormat : 'dd-mm-yy'
	    });

	    $( "#oih-slider-range" ).slider({
	      range: "min",
	      value: $('#oih_bmp_conversion_quality').val(),
	      min: 1,
	      max: 100,
	      slide: function( event, ui ) {
	        $( '#oih_bmp_conversion_quality' ).val( ui.value );
	      }
	    });

		$('#oih_bmp_conversion_quality').keyup(function() {
			$("#oih_bmp_conversion_quality").change();
		});

		$( "#oih_bmp_conversion_quality" ).change(function() {
		    $( "#oih-slider-range" ).slider( "value", $(this).val() );
		});

		$( "#oih_auto_convert_bmp" ).change(function() {
			if ( $('#oih_auto_convert_bmp').is(":checked") ) {
				$('#oih-slider-range').slider('enable');
				 $("#oih_bmp_conversion_quality").attr("readonly", false);
				 $("#oih_bmp_conversion_quality").removeClass('disabled');
			} else {
				$('#oih-slider-range').slider('disable');
				$("#oih_bmp_conversion_quality").attr("readonly", true);
				$("#oih_bmp_conversion_quality").addClass('disabled');
			}
		});

		$( '#calculate_difference' ).on( 'click', function( e ) {
			e.preventDefault();
			jQuery.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'recalculate_difference'
				},
				complete: function( jqXHR, responseText ) {
					var data = jQuery.parseJSON( jqXHR.responseText );
					jQuery( 'td#calculated_difference' ).html( data.difference );
					jQuery( 'td#current_upload_size' ).html( data.current_size );
				}
			});
		});

		$('#oih_auto_convert_bmp').trigger('change');

	});

}(jQuery));