<?php
/**
 *  Indexreloaded-backend. excludes by element.
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

$ret = array(
	'Video' => array(
		'exclude' => 'underscore-js,backbone-js,wp-util-js,mediaelement-core-js-before,wp-playlist-script,mediaelement-js,/js/mediaelement/', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'This is optional. Video locally hosted will not load without these excludes', 'indexreloaded' ),
	),
);
return $ret;
