<?php
/**
 *  Indexreloaded-backend. excludes by theme.
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

$ret = array(
	'Astra'              => array(
		'exclude' => 'astra-theme-js-js-extra,astra-theme-js-js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Navigationmenu does not show up', 'indexreloaded' ),
	),
	'Twenty Twenty-Four' => array(
		'exclude' => 'global-styles-inline-css,interactivity.min.js,wp-dom-ready-js,starter-templates-zip-preview-js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Display problems and JS-Errors', 'indexreloaded' ),
	),
	'Vantage'            => array(
		'exclude' => 'vantage-main-js,jquery-flexslider-js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Search-window-Popup fails to show up', 'indexreloaded' ),
	),
);
return $ret;
