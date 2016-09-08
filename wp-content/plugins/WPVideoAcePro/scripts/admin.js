jQuery( function ( $ )
{
	'use strict';
	
	$(document).ready( function() {
		var frame = $("#cytpro_frame");
		initFrameSelector( frame );

		frame = $("#cytpro_m_frame");
		initFrameSelector( frame );
	})

	function initFrameSelector( frame ) {
		if ( frame.length > 0 ) {
			if ( frame.parent().find( "img.cytp-frame-placeholder" ) ) {
				frame.after( '<img class="cytp-frame-placeholder" src="" alt="Frame" style="display: block; width: 400px; height: 225px; margin-top: 10px;">' );
			}

			frame.on('change', function() {
				var img = frame.parent().find("img.cytp-frame-placeholder");

				if ( $(this).val().length == 0 ) img.hide();
				else img.show();

				img.attr('src', CYTPROPluginURL.url + "/images/frames/" + $(this).val() + ".png" );
			});

			frame.trigger('change');
		}
	}
} );