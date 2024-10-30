/**
 *  JavaScript for AJAX-backpost of rendered HTML to IndexReloaded backend.
 *
 *	@package Indexreloaded
 */

(function ($) {
	function loadfinalHTML(){
		var locajax    = 0;
		var loadedHTML = $( "html" ).html();
		var posbody    = loadedHTML.indexOf( "<body" );
		loadedHTML     = loadedHTML.substring( posbody );
		loadedHTML     = loadedHTML.replaceAll( "</div >", "</div>" );
		loadedHTML     = loadedHTML.replaceAll( "\n", ' ' );
		loadedHTML     = loadedHTML.replaceAll( "\t", '' );
		loadedHTML     = loadedHTML.replaceAll( "\r", '' );
		loadedHTML     = btoa( encodeURI( loadedHTML ) );
		loadedHTML     = "newbody=" + loadedHTML + "&userrolemd5=" + user_role_md5;
		$.ajax(
			{
				type: 'POST',
				url: locsite + 'wp-admin/admin-ajax.php' + gtstrnghtml,
				async: true,
				data: loadedHTML,
				success: function (html) {
					var stringLength = html.length;
					var htmlend      = html.charAt( stringLength - 1 );
					var lastvld      = '';
					var poshedge     = 0;
					if (htmlend == '0') {
						html = html.substring( 0, (stringLength - 1) );
					}

				},

			}
		);

	}

	setTimeout( loadfinalHTML, delaywait );
})( jQuery, window, document );
