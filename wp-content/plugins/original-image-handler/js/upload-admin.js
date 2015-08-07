(function ($) {
	"use strict";
	$(function () {
		var resize_option = $( '<option value="resize">' + upload_l10n.bulk_resize + '</option>' );
		$( 'select[name="action"]' ).append( resize_option.clone() );
		$( 'select[name="action2"]' ).append( resize_option );
	});
}(jQuery));