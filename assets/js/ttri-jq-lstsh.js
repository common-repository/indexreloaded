/**
 *  JavaScript for delayed loading of CSS at end of page.
 *
 *	@package Indexreloaded
 */

(function ($) {
	$( document ).ready(
		function () {
			var locajax = 0;
			$.ajax(
				{
					type: 'POST',
					url: rmsite + 'wp-admin/admin-ajax.php' + gtstrng,
					async: true,
					data: '',
					success: function (html) {
						var stringLength = html.length;
						var htmlend      = html.charAt( stringLength - 1 );
						var lastvld      = '';
						var poshedge     = 0;
						if (htmlend == '0') {
							html = html.substring( 0, (stringLength - 1) );
						}

						if (html.substring( 0, 5 ) == 'valid') {
							locajax = 2;

						} else {
							locajax = 1;

						}

						if (locajax == 1) {
							$.ajax(
								{
									type: 'POST',
									url: locsite + 'wp-admin/admin-ajax.php' + gtstrngopt,
									async: true,
									data: '',
									success: function (html) {

									},
								}
							);
						}

						if (locajax == 2) {
							$.ajax(
								{
									type: 'POST',
									url: locsite + 'wp-admin/admin-ajax.php' + gtstrnglvc,
									async: true,
									data: '',
									success: function (html) {

									},
								}
							);
						}
					},
				}
			);

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
