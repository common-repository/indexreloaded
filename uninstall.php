<?php
/**
 *  Uninstall
 *
 *  The uninstall.php performs tasks when uninstalling Indexreloaded
 *
 *  @package Indexreloaded
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || die;



$staticdir = trim( get_option( 'indexreloaded_pathcssjs' ) );
$home_dir  = rtrim( ABSPATH, '/' );
if ( ( strlen( $staticdir ) >= 2 ) && ( '/' === $staticdir[0] ) ) {
	$staticdir = $home_dir . $staticdir;
	if ( is_dir( $staticdir ) ) {
		irld_rmdir_recursive( $staticdir );
	}
}

// Remove options.
global $wpdb;
if ( $wpdb->options ) {
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			'indexreloaded_%'
		)
	);

} else {
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
			'indexreloaded_%'
		)
	);
}
