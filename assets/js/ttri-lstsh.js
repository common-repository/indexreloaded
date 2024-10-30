/**
 *  JavaScript for standard delayed loading of CSS at end of page without optional jQuery-code.
 *
 *	@package Indexreloaded
 */

(function ($) {
	$( document ).ready(
		function () {

			$.fn.loadRiStyleSheets = function () {
				var lStSh_length = lStSh.length;
				for (i = 0; i < lStSh_length; i++) {
					$.fn.loadRiStyleSheet( lStSh[i] );
				}
			}

			$.fn.loadRiStyleSheet = function (src) {
				if (document.createStyleSheet) {
					document.createStyleSheet( src );
				} else {
					var stylesheet  = document.createElement( 'link' );
					stylesheet.href = src;
					stylesheet.rel  = 'stylesheet';
					stylesheet.type = 'text/css';
					document.getElementsByTagName( 'body' )[0].appendChild( stylesheet );
				}
			}

			if ( slowmotion == 1 ) {
				setTimeout( $.fn.loadRiStyleSheets, 2000 );
			} else {
				setTimeout( $.fn.loadRiStyleSheets, 5 );
			}
		}
	);
})( jQuery, window, document );