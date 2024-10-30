<?php
/**
 *  Indexreloaded-ajax
 *
 *  The indexreloaded-ajax.php performs ajax-calls to Indexreloaded
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

add_action( 'wp_ajax_irld_del_cacheitem', 'irld_del_cacheitem' );

/**
 * Update of last time server side excludes were catched
 */
function irld_del_cacheitem() {
	check_ajax_referer( 'irld_del_cacheitem' );
	if ( isset( $_GET ) ) {
		if ( isset( $_POST ) ) {
			if ( isset( $_POST['page'] ) ) {
				$cached_page = sanitize_textarea_field( wp_unslash( $_POST['page'] ) );
				$cached_page = base64_decode( $cached_page ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
					if ( '' !== $cached_page ) {
						$cached_page_fallback = rawurlencode( rawurldecode( $cached_page ) . '/' );
						wp_cache_delete( 'real-page-' . $cached_page . '-', 'indexreloaded' );
						wp_cache_delete( 'page-' . $cached_page . '-', 'indexreloaded' );
						wp_cache_delete( 'real-page-' . $cached_page, 'indexreloaded' );
						wp_cache_delete( 'page-' . $cached_page, 'indexreloaded' );
						wp_cache_delete( 'real-page-' . $cached_page_fallback . '-', 'indexreloaded' );
						wp_cache_delete( 'page-' . $cached_page_fallback . '-', 'indexreloaded' );
						wp_cache_delete( 'real-page-' . $cached_page_fallback, 'indexreloaded' );
						wp_cache_delete( 'page-' . $cached_page_fallback, 'indexreloaded' );
						$wp_version = get_bloginfo( 'version' );
						if ( version_compare( $wp_version, '6.3.0' ) > 0 ) {
							wp_cache_set_last_changed( 'indexreloaded' );
						}
						echo esc_html( rawurldecode( $cached_page ) . ' ' . __( 'removed from cache', 'indexreloaded' ) );

					}
				}
			}
		}
	}

	wp_die();
}

add_action( 'wp_ajax_irld_upd_excludelist', 'irld_upd_excludelist' );

/**
 * Update of last time server side excludes were catched
 */
function irld_upd_excludelist() {
	check_ajax_referer( 'irld_upd_excludelist' );
	if ( isset( $_GET ) ) {
		if ( isset( $_GET['excludejson'] ) ) {
			$excludejson = sanitize_textarea_field( wp_unslash( $_GET['excludejson'] ) );
		}

		update_option( 'indexreloaded_serversideexcludes', $excludejson );
		update_option( 'indexreloaded_serversideexcludes_lastrefreshed', trim( time() ) );
		echo 'ok';
	}

	wp_die();
}

add_action( 'wp_ajax_irld_upd_opts', 'irld_upd_opts' );

/**
 *  Update of licencing state from frontend.
 */
function irld_upd_opts() {
	check_ajax_referer( 'irld_upd_opts' );
	if ( isset( $_GET ) ) {
			update_option( 'indexreloaded_LicActive', '' );
			update_option( 'indexreloaded_APIkey_lastvalidate', '' );
			update_option( 'indexreloaded_APIkey_vdtd', '' );
			update_option( 'indexreloaded_lvc', '' );
			update_option( 'indexreloaded_generateCSSbelowTheFold', 'off' );
		if ( isset( $_GET['page'] ) ) {
			$pagename     = rawurlencode( sanitize_text_field( wp_unslash( $_GET['page'] ) ) );
			$pagepostname = rawurldecode( $pagename );
			$echo_text    = '';
			$echo_text    = irld_page_cache_purge_page( $pagepostname );
		}
			echo 'ok';

	}

	wp_die();
}

add_action( 'wp_ajax_nopriv_irld_upd_ccss', 'nopriv_irld_upd_ccss' );
add_action( 'wp_ajax_irld_upd_ccss', 'irld_upd_ccss' );


/**
 *  Update of cache for client based HTML, nopriv.
 */
function nopriv_irld_upd_ccss() {
	irld_upd_ccss();
}
/**
 *  Update of cache for client based HTML.
 */
function irld_upd_ccss() {
	check_ajax_referer( 'irld_upd_ccss' );
	if ( isset( $_GET ) ) {
		if ( isset( $_POST ) ) {
			if ( isset( $_POST['newbody'] ) ) {
				$posted_newbody = sanitize_textarea_field( wp_unslash( $_POST['newbody'] ) );
				$newbody        = urldecode( base64_decode( $posted_newbody ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				if ( isset( $_POST['userrolemd5'] ) ) {
					$userrolemd5 = sanitize_textarea_field( wp_unslash( $_POST['userrolemd5'] ) );
					if ( isset( $_GET['page'] ) ) {
						$pagename = rawurlencode( sanitize_text_field( wp_unslash( $_GET['page'] ) ) );
						if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
							$object_cache_group      = 'indexreloaded';
							$opt_object_cache_expire = intval( get_option( 'indexreloaded_ObjectCacheExpire' ) );
							if ( 0 === $opt_object_cache_expire ) {
								$opt_object_cache_expire = 5184000;
							}

							$object_cache_expire             = $opt_object_cache_expire;
							$keyrequest                      = 'real-page-' . $pagename . '-' . $userrolemd5;
							$resultcache                     = array();
							$resultcache['modelbuffer_body'] = $newbody;
							$resultcache                     = wp_json_encode( $resultcache );
							wp_cache_set( $keyrequest, $resultcache, $object_cache_group, $object_cache_expire );
							$wp_version = get_bloginfo( 'version' );
							if ( version_compare( $wp_version, '6.3.0' ) > 0 ) {
								wp_cache_set_last_changed( $object_cache_group );
							}
							$cache_type = 'redis';

						} else {
							// we need the result of the ajax-request even without redis.
							$option_name                     = 'indexreloaded_cache_real-page-' . $pagename . '-' . $userrolemd5;
							$resultcache                     = array();
							$resultcache['modelbuffer_body'] = $newbody;
							$resultcache                     = wp_json_encode( $resultcache );
							$option_value                    = $resultcache;
							update_option( $option_name, $option_value );
							$cache_type = 'database';

						}

						// page cache compatibility. If a page cache is active, then the cached version of the URL should be removed.
						// CSSS only loads on 2nd load of the page, that's the reason why page cache needs a  precise purge here.
						$pagepostname = rawurldecode( $pagename );
						$echo_text    = '';
						$echo_text    = irld_page_cache_purge_page( $pagepostname, $cache_type );

						if ( '' === $echo_text ) {
							$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page';
						}

						echo esc_html( $echo_text );

					}
				}
			}
		}
	}

	wp_die();
}

add_action( 'wp_ajax_nopriv_irld_upd_optsnp', 'irld_nopriv_upd_optsnp' );
add_action( 'wp_ajax_irld_upd_optsnp', 'irld_upd_optsnp' );

/**
 *  Update of licencing state from frontend, nopriv.
 */
function irld_nopriv_upd_optsnp() {
	irld_upd_optsnp();
}

/**
 *  Update of licencing state from frontend.
 */
function irld_upd_optsnp() {
	check_ajax_referer( 'irld_upd_optsnp' );
	if ( isset( $_GET ) ) {
			update_option( 'indexreloaded_LicActive', '' );
			update_option( 'indexreloaded_APIkey_lastvalidate', '' );
			update_option( 'indexreloaded_APIkey_vdtd', '' );
			update_option( 'indexreloaded_lvc', '' );
			update_option( 'indexreloaded_generateCSSbelowTheFold', 'off' );
		if ( isset( $_GET['page'] ) ) {
			$pagename     = rawurlencode( sanitize_text_field( wp_unslash( $_GET['page'] ) ) );
			$pagepostname = rawurldecode( $pagename );
			$echo_text    = '';
			$echo_text    = irld_page_cache_purge_page( $pagepostname );
		}

			echo 'ok';
	}

	wp_die();
}

add_action( 'wp_ajax_nopriv_irld_upd_lvcnp', 'nopriv_irld_upd_lvcnp' );
add_action( 'wp_ajax_irld_upd_lvcnp', 'irld_upd_lvcnp' );

/**
 * Update of licencing time from frontend, nopriv.
 */
function nopriv_irld_upd_lvcnp() {
	irld_upd_lvcnp();
}

/**
 * Update of licencing time from frontend.
 */
function irld_upd_lvcnp() {
	check_ajax_referer( 'irld_upd_lvcnp' );
	if ( isset( $_GET ) ) {
			update_option( 'indexreloaded_lvc', trim( intval( time() + 86370 ) ) );
		if ( isset( $_GET['page'] ) ) {
			$pagename     = rawurlencode( sanitize_text_field( wp_unslash( $_GET['page'] ) ) );
			$pagepostname = rawurldecode( $pagename );
			$echo_text    = '';
			$echo_text    = irld_page_cache_purge_page( $pagepostname );
		}

			echo 'ok';
	}

	wp_die();
}

/**
 *  Creates a nonce useable from site to site. Will be decoded on other server.
 *  time has to fit day and nonce_action has to fit.
 *  basically we encrypt nonce_action with current unix-day.
 *
 *  @param string $nonce_action nonce_key.
 */
function irld_get_special_nonce( $nonce_action ) {
	$res     = '';
	$getpass = round( ( time() / ( 60 * 60 * 24 ) ), 0 );
	$input   = $nonce_action;
	if ( ! isset( $getpass ) ) {
		$res = 'passwort missing';
	} elseif ( '' === trim( $getpass ) ) {
			$res = 'passwort empty';
	} else {
		$respass          = bin2hex( base64_encode( $getpass ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$respasschunks    = intval( strlen( $respass ) / 4 );
		$arrrespasschunks = array();
		for ( $irp = 0; $irp <= $respasschunks + 1; $irp++ ) {
			if ( ( $respasschunks + 1 ) === $irp ) {
				$arrrespasschunks[ $irp ] = substr( $respass, $irp * 4, 40 );
			} else {
				$arrrespasschunks[ $irp ] = substr( $respass, $irp * 4, 4 );
			}
		}

		$strrespasstest = implode( '', $arrrespasschunks );
		if ( isset( $input ) ) {
			$result          = bin2hex( base64_encode( $input ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$res             = 'passwort not complex enough. Please improve complexity and try again.';
			$lenresult       = strlen( $result );
			$resultchunks    = intval( strlen( $result ) / 4 );
			$arrresultchunks = array();
			for ( $irp = 0; $irp < $resultchunks + 1; $irp++ ) {
				if ( $irp === $resultchunks ) {
					if ( '' !== trim( substr( $result, $irp * 4, 40 ) ) ) {
						$arrresultchunks[ $irp ] = substr( $result, $irp * 4, 40 );
					}
				} else {
						$arrresultchunks[ $irp ] = substr( $result, $irp * 4, 4 );
				}
			}

			$strrestest       = implode( '.', $arrresultchunks );
			$arrmergechunks   = array();
			$loopcnt          = 0;
			$respasschunkswrk = $respasschunks;
			for ( $irp = 0; $irp < $resultchunks; $irp++ ) {
				if ( $irp > $respasschunkswrk ) {
					++$loopcnt;
					$respasschunkswrk = $respasschunkswrk + $respasschunks;
				}

				$arrmergechunks[ $irp ] = dechex( hexdec( '00' . $arrresultchunks[ $irp ] ) + hexdec( '00' . $arrrespasschunks[ $irp - $respasschunks * $loopcnt ] ) );
			}

			$strrespasstestw = implode( '.', $arrmergechunks );
			$strrespasstest  = implode( '', $arrmergechunks );
			$res             = $strrespasstest;

		}
	}

	return trim( $res );
}

add_action( 'wp_ajax_irld_del_files', 'irld_del_files' );

/**
 *  Delete CSS and JS files.
 */
function irld_del_files() {
	check_ajax_referer( 'irld_del_files' );
	$cssjsoptionpath = get_option( 'indexreloaded_pathcssjs' );
	if ( '' === trim( $cssjsoptionpath ) ) {
		$cssjsoptionpath = 'wp-content/uploads/cssjs';
	}

	if ( '' !== $cssjsoptionpath ) {
		$cssjspath = realpath(
			str_replace(
				'wp-content\plugins\indexreloaded\include',
				'',
				str_replace( 'wp-content/plugins/indexreloaded/include', '', __DIR__ )
			)
		) .
				DIRECTORY_SEPARATOR .
				str_replace( '/', DIRECTORY_SEPARATOR, $cssjsoptionpath );

		$directory = $cssjspath . '/css/';
		// Returns array of files.
		$files1        = scandir( $directory );
		$deleted_files = 0;
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				++$deleted_files;
			}
		}

		$directory = $cssjspath . '/js/';
		$files1    = scandir( $directory );
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				++$deleted_files;
			}
		}

		$directory = $cssjspath . '/external/';
		$files1    = scandir( $directory );
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				++$deleted_files;
			}
		}

		if ( $deleted_files > 0 ) {
			$ret = $deleted_files . ' ' . __( 'files deleted', 'indexreloaded' );
		} else {
			$ret = __( 'No files found', 'indexreloaded' );
		}

		irld_page_cache_delete();
		echo esc_html( trim( $ret ) );
	} else {
		$ret = __( 'No files present', 'indexreloaded' );
		echo esc_html( trim( $ret ) );
	}

	wp_die();
}

add_action( 'wp_ajax_irld_del_cache', 'irld_del_cache' );

/**
 *  Delete Cache.
 */
function irld_del_cache() {
	check_ajax_referer( 'irld_del_cache' );
	if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
		if ( wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'indexreloaded' );
			$ret = __( 'Cache deleted', 'indexreloaded' );
			echo esc_html( trim( $ret ) );
		} else {
			$ret = __( 'Cache could not be deleted', 'indexreloaded' );
			echo esc_html( trim( $ret ) );
		}
	} else {
		global $wpdb;

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT DISTINCT `option_name` as option_name
				FROM {$wpdb->options}
				WHERE option_name LIKE %s",
				'indexreloaded_cache_real-page-%'
			)
		);
		if ( is_array( $rows ) ) {
			foreach ( $rows as $postrow ) {
				$option_name = trim( $postrow->option_name );
				delete_option( $option_name );
			}
		}
		$ret = __( 'Cache deleted', 'indexreloaded' );
		echo esc_html( trim( $ret ) );
	}

	wp_die();
}
