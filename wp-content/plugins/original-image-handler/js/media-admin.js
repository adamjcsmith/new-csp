(function ($) {
	"use strict";
	$(function () {
		$( '#oih_rem_orig' ).change( function(e) {
			if ( uploader !== undefined ) {
				uploader.settings.multipart_params.remove_original = ( $( '#oih_rem_orig' ).is( ':checked' ) ? 1 : 0 );
			}
		});

		if ( typeof uploader == "undefined" ) {
			$( '#oih_upload_settings' ).hide();
		}
	});
}(jQuery));