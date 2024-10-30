<?php
/**
 *  Configuration setup for frontend of Indexreloaded.
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );
$baseincludes = trim( get_option( 'indexreloaded_includesarr' ) );
if ( '' === $baseincludes ) {
	$baseincludes = '*';
}

$baseexcludes = trim( get_option( 'indexreloaded_excludesarr' ) );
if ( '' === $baseexcludes ) {
	$baseexcludes = '*';
}

if ( ( '*' === $baseexcludes ) && ( '*' === $baseincludes ) ) {
	// typically after initial setup .
	$info_array = array();
	$info_array = excludes_check();
	if ( isset( $info_array['recommendedexcludes'] ) ) {
		if ( '' !== trim( $info_array['recommendedexcludes'] ) ) {
			update_option( 'indexreloaded_excludesarr', $info_array['recommendedexcludes'] );
			$baseexcludes = $info_array['recommendedexcludes'];
		}
	}

	if ( '*' === $baseexcludes ) {
		$baseexcludes = '';
	}
}

$siteurl = site_url( '/', 'https' );
$lvc     = 1;
if ( 'on' === get_option( 'indexreloaded_generateCSSbelowTheFold' ) ) {
	$lvc = intval( trim( get_option( 'indexreloaded_lvc' ) ) );
	if ( ( ( time() - $lvc ) < 0 ) && ( 0 !== $lvc ) ) {
		$lvc = 0;
	} else {
		$lvc = 1;
	}
}

$ret = array(
	'0'                              => array(
		// Dont modify index.php at all.
			'dontmod'                            => ( 'on' === get_option( 'indexreloaded_dontmod' ) ) ? 1 : 0,
		// Dont modify JS-files.
			'dontmodJS'                          => ( 'on' === get_option( 'indexreloaded_dontmodJS' ) ) ? 1 : 0,
		// Dont modify CSS-files.
			'dontmodCSS'                         => ( 'on' === get_option( 'indexreloaded_dontmodCSS' ) ) ? 1 : 0,
		// Exclude list: parts of JS or CSS-filenames, that should be excluded when dontmod=0, dontmodjs=0 or/and dontmodcss=0 for inline scripts/styles a part of the text is ok for identification.
			'excludesarr'                        => $baseexcludes,
		// Include list: parts of JS or cCSS-filenames, that must be included when dontmod=0, dontmodjs=0 or/and dontmodcss=0. includes overwrite the excludes. This good for more detailed filtering.
			'includesarr'                        => $baseincludes,
		// Force creation of new files: You can force new files (CSS and JS) with URL-Parameter ?forceNewFiles=1 as well.
			'force_new_files'                    => ( 'on' === get_option( 'indexreloaded_forceNewFiles' ) ) ? 1 : 0,
		// in production mode URL-Parameters ?forceNewFiles=1, ?dontModIndex=1 and ?showDebugWindow =1 are disabled.
			'production_mode'                    => ( 'on' === get_option( 'indexreloaded_productionMode' ) ) ? 1 : 0,
		// Deactivate on pages: List of pages that should not be touched by indexreloaded.
			'deactivate_on_pages'                => trim( get_option( 'indexreloaded_deactivateOnPages' ) ),
		// Enable file processing.
			'opt_processfiles'                   => ( 'on' === get_option( 'indexreloaded_dontmod' ) ) ? 0 : 1,
		// Enable JS-file processing.
			'opt_processjsfiles'                 => ( 'on' === get_option( 'indexreloaded_dontmodJS' ) ) ? 0 : 1,
		// Load last JS file asynchronous. Must be set to 0 when code in last JS file attemps to make document.write.
			'asynch_last_js'                     => ( 'on' === get_option( 'indexreloaded_asynchLastJS' ) ) ? 1 : 0,
		'defer_all_js'                           => ( 'on' === get_option( 'indexreloaded_deferAllJS' ) ) ? 1 : 0,
		'dont_defer_jquery'                      => ( 'on' === get_option( 'indexreloaded_dontDeferJquery' ) ) ? 1 : 0,
		// Enable CSS-file processing.
			'opt_processcssfiles'                => ( 'on' === get_option( 'indexreloaded_dontmodCSS' ) ) ? 0 : 1,
		// Try to fix bad CSS, changes ' to ", removes bad // in links, eliminates bad typo in CSS: Check out the PHP-code for try_fix_bad_css if you want to add your own cleansing code .
			'try_fix_bad_css'                    => 1,
		// Removes default type-declarations in style and script-tags, depends on try_fix_bad_css = 1.
			'fix_html_defaults'                  => 1,
		// Exclude list for Processing: parts of JS or CSS-filenames, that should be excluded for processing, for inline scripts/styles a part of the text is ok for identification.
			'excludes_processing'                => $baseexcludes,
		// Include list for Processing: parts of JS or CSS-filenames, that must be included for processing anyway. includes overwrite the excludes. This good for more detailed filtering.
			'includes_processing'                => $baseincludes,
		// CSS will be compressed.
			'do_crunch_css'                      => ( 'on' === get_option( 'indexreloaded_doCrunchCSS' ) ) ? 1 : 0,
		// Enable minify of JS-files with JSMin.php - modified PHP implementation of Douglas Crockford's JSMin.
			'opt_minify_js_files'                => ( 'on' === get_option( 'indexreloaded_optMinifyjsfiles' ) ) ? 1 : 0,
		// Exclude list: parts of JS-filenames, that must not be minified.
			'no_minify_js_list'                  => trim( get_option( 'indexreloaded_noMinifyjsList' ) ),
		// Split CSS into CSS above the fold and CSS below the fold (requires APIKey).
			'generate_css_below_the_fold'        => ( 'on' === get_option( 'indexreloaded_generateCSSbelowTheFold' ) ) ? 1 : 0,
		'inline_ccss'                            => ( 'on' === get_option( 'indexreloaded_inlineCCSS' ) ) ? 1 : 0,
		'lvc'                                    => $lvc,
		'cssjsfolder'                            => trim( get_option( 'indexreloaded_pathcssjs' ) ),
		// The tags in this list always remain in the CSS above the fold.
			'tags_to_keep_above_the_fold'        => trim( get_option( 'indexreloaded_tagsToKeepAboveTheFold' ) ),
		// The class names in this list always remain in the CSS above the fold.
			'classes_to_keep_above_the_fold'     => trim( get_option( 'indexreloaded_classesToKeepAboveTheFold' ) ),
		// The CSS-Ids in this list always remain in the CSS above the fold.
			'ids_to_keep_above_the_fold'         => trim( get_option( 'indexreloaded_IDsToKeepAboveTheFold' ) ),
		// Show the debug window by default: You can force the debug window with URL-Parameter ?showDebugWindow=1 as well. SYS.devIPmask must match your IP.
			'show_debug_window'                  => ( 'on' === get_option( 'indexreloaded_showDebugWindow' ) ) ? 1 : 0,
		// The html of the debug windows is appended to this tag.
			'show_debug_window_body_tag'         => trim( get_option( 'indexreloaded_showDebugWindowBodyTag' ) ),
		// Create a little report on activities during creation of above/below the fold-CSS in the debug window.
			'css_folding_report'                 => ( 'on' === get_option( 'indexreloaded_CSSFoldingReport' ) ) ? 1 : 0,
		// The debug-windows only shows up if your IP matches this debugIP, '*' allows all.
			'debug_ip'                           => trim( get_option( 'indexreloaded_DebugIP' ) ),
		// Forces a baseURL for the site, used to identify links to external files a hosted on same server.
			'force_base_url'                     => $siteurl,
		// Preloads.
		// tag where the preloads are preprended before.
			'preload_tag'                        => trim( get_option( 'indexreloaded_PreloadTag' ) ),
		// image file to preload.
			'preload_image'                      => trim( get_option( 'indexreloaded_PreloadImage' ) ),
		// comma-separated list of CSS files that should be preloadeded with <link rel="preload" href="styles.css" as="style">.
			'preload_css_list'                   => trim( get_option( 'indexreloaded_PreloadCSSList' ) ),
		// comma-separated list of JS files that should be preloadeded with <link rel="preload" href="ui.js" as="script">.
			'preload_js_list'                    => trim( get_option( 'indexreloaded_PreloadJSList' ) ),
		// comma-separated list of font-files that should be preloadeded with <link rel="preload" href="font.woff" crossorigin="anonymous"> .
			'preload_fonts_list'                 => trim( get_option( 'indexreloaded_PreloadFontsList' ) ),
		// Licence.
		// Key provided by toctoc.ch.
			'api_key'                            => trim( get_option( 'indexreloaded_APIKey' ) ),
		// API-Server: Server hosting the APIkeys, normally www.toctoc.ch.
			'api_server'                         => 'www.toctoc.ch',
		// Cleaning HTML.
		// clean_archive_strings_in_pagetitle.
			'clean_archive_strings_in_pagetitle' => ( 'on' === get_option( 'indexreloaded_cleanArchiveStringsInPagetitle' ) ) ? 1 : 0,
		// clean_plugin_notes.
			'clean_plugin_notes'                 => ( 'on' === get_option( 'indexreloaded_cleanPluginNotes' ) ) ? 1 : 0,
		// remove pingbacks.
			'remove_pingbacks'                   => ( 'on' === get_option( 'indexreloaded_removePingbacks' ) ) ? 1 : 0,
		// remove rss.
			'remove_rss'                         => ( 'on' === get_option( 'indexreloaded_removeRSS' ) ) ? 1 : 0,
		// remove shortlink.
			'remove_shortlink'                   => ( 'on' === get_option( 'indexreloaded_removeShortlink' ) ) ? 1 : 0,
		// load cssbelow in slowmotion.
			'load_cssbelow_in_slowmotion'        => ( 'on' === get_option( 'indexreloaded_load_cssbelow_in_slowmotion' ) ) ? 1 : 0,
		// indexreloaded_removeUnusedCSS.
			'remove_unused_css'                  => ( 'on' === get_option( 'indexreloaded_removeUnusedCSS' ) ) ? 1 : 0,
		// External-links jsonencoded.
			'externally_hosted'                  => trim( get_option( 'indexreloaded_externally_hosted' ) ),
		// Host external scripts and css locally.
			'externally_hosted_host_locally'     => ( 'on' === get_option( 'indexreloaded_externally_hosted_host_locally' ) ) ? 1 : 0,
		// days locally hosted external scripts and css are rechecked.
			'externally_hosted_keeptime'         => trim( get_option( 'indexreloaded_externally_hosted_keeptime' ) ),

	),

	// settings overwrite for particular pages.
	'/somevendorstore/, /otherpage/' => array(
		'dontmod'             => 1,
		'force_new_files'     => 0,
		'includesarr'         => $baseincludes,
		'includes_processing' => $baseincludes,
	),

);
return $ret;
