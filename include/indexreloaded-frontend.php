<?php
/**
 *  Indexreloaded-frontend.
 *
 *  The indexreloaded-frontend.php performs tasks when Indexreloaded is loaded in frontend
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

/**
 * Test of current page if it's loadable for IndexReloaded.
 */
function irld_is_current_page_indexreloadable() {
	$serverrequri = '';
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		if ( isset( $_SERVER['PHP_SELF'] ) ) {
			sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) );
		}
	} else {
		$serverrequri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	$slcurrent_page_name_arr = explode( '?', $serverrequri );
	if ( count( $slcurrent_page_name_arr ) > 1 ) {
		$slcurrent_page_name_arr[1] = preg_replace( '/[0-9]+/', '', $slcurrent_page_name_arr[1] );
		$slcurrent_page_name        = $slcurrent_page_name_arr[0] . implode( '?', $slcurrent_page_name_arr );
	} else {
		$slcurrent_page_name = $serverrequri;
	}

	if ( strpos( $slcurrent_page_name, '+' ) > 0 ) {
		if ( strpos( $slcurrent_page_name, '.' ) > strpos( $slcurrent_page_name, '+' ) ) {
			$tmppagename         = substr( $slcurrent_page_name, 0, ( strpos( $slcurrent_page_name, '+' ) - 1 ) ) . substr( $slcurrent_page_name, strpos( $slcurrent_page_name, '.' ) );
			$slcurrent_page_name = $tmppagename;
		}
	}

	$pagename          = $slcurrent_page_name;
	$optdirectresponse = 'wp-admin,wp-ajax,wp-json,wp-includes,wp-login,author,feed,xmlrpc.php,?rest_route,for=jetpack,&customize_theme,wp-comments-post';
	$arrdirectresponse = explode( ',', $optdirectresponse );
	foreach ( $arrdirectresponse as $directresponse ) {
		if ( ( str_replace( $directresponse, '', $pagename ) !== $pagename ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Make a userrole-dependent md5.
 */
function irld_get_current_user_role_md5() {

	if ( is_user_logged_in() ) {
		$user     = wp_get_current_user();
		$roles    = (array) $user->roles;
		$rolesmd5 = md5( implode( '', $roles ) );
		return $rolesmd5;
	} else {
		return '';
	}
}

/**
 * Trace messages from IndexReloaded - for instance to error_log.
 *
 * @param string $message message to be added to trace_log.
 * @param int    $logtype says where the message should go.
 */
function irld_trace_log( $message, $logtype ) {
	error_log( $message, $logtype ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

/**
 * IndexReloaded callback function.
 *
 * @param string $buffer The final HTML-output by WordPress.
 */
function irld_callback( $buffer ) {
	$relative_path_to_plugin = '';
	$user_id                 = get_current_user_id();
	$userrolemd5             = irld_get_current_user_role_md5();
	require realpath( str_replace( 'include', '', str_replace( 'include', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
			str_replace( '/', DIRECTORY_SEPARATOR, 'Classes/Controller/class-indexreloaded.php' );
	$indexreloaded_instance  = new GiseleWendl\Indexreloaded\Controller\Indexreloaded();
	$relative_path_to_plugin = 'wp-content/plugins/indexreloaded';
	$bufferinarrbasezero     = explode( '<body', $buffer );
	$buffer_body             = false;
	if ( count( $bufferinarrbasezero ) > 1 ) {
			$buffer_body = true;
	} else {
			$bufferinarrbasezero = explode( '<BODY', $buffer );
		if ( count( $bufferinarrbasezero ) > 1 ) {
			$buffer_body = true;
		}
	}

	if ( str_replace( 'error404', '', $buffer ) !== $buffer ) {
		$buffer_body = false;
	}

	if ( ( '' !== trim( $buffer ) ) && ( true === $buffer_body ) ) {
			$bufferout = $indexreloaded_instance->content_post_proc( $buffer, $user_id, $relative_path_to_plugin, $userrolemd5 );
	} else {
			$bufferout = trim( $buffer );
	}

	unset( $indexreloaded_instance );
	return $bufferout;
}

/**
 * IndexReloaded turns on output buffering.
 */
function irld_start_buffering() {

	ob_start( 'irld_callback', 0, PHP_OUTPUT_HANDLER_STDFLAGS );
}

/**
 * IndexReloaded getting output buffer and release it.
 */
function irld_process_and_output() {
	$outputbuffer = ob_get_contents();
	@ob_end_clean(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	echo trim( $outputbuffer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Make safe jquery is loaded.
 */
function irld_load_scripts() {
	wp_enqueue_script( 'jquery' );
}

/**
 * PHP 8.0 backward compat function for str_ends_with.
 *
 * @param string $haystack String to check for ending with $needle.
 * @param string $needle Ending string.
 *
 * @return bool
 */
function irld_str_ends_with( $haystack, $needle ) {
	if ( ( version_compare( PHP_VERSION, '8.0' ) < 0 ) && ( ! function_exists( 'str_ends_with' ) ) ) {
			$len_needle        = strlen( $needle );
			$str_from_haystack = substr( $haystack, -$len_needle );
		if ( $str_from_haystack === $needle ) {
			return true;
		} else {
			return false;
		}
	} else {
		return str_ends_with( $haystack, $needle );
	}
}

/**
 * PHP 8.0 backward compat function for str_starts_with.
 *
 * @param string $haystack String to check for starting with $needle.
 * @param string $needle Starts-string.
 *
 * @return bool
 */
function irld_str_starts_with( $haystack, $needle ) {
	if ( ( version_compare( PHP_VERSION, '8.0' ) < 0 ) && ( ! function_exists( 'str_starts_with' ) ) ) {
		$len_needle        = strlen( $needle );
		$str_from_haystack = substr( $haystack, 0, $len_needle );
		if ( $str_from_haystack === $needle ) {
			return true;
		} else {
			return false;
		}
	} else {
		return str_starts_with( $haystack, $needle );
	}
}


$active = trim( get_option( 'indexreloaded_active' ) );
if ( 'on' === $active ) {
	if ( irld_is_current_page_indexreloadable() ) {
		$runscachify = false;
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		if ( is_plugin_active( 'cachify/cachify.php' ) ) {
			// Cachify.
			$htaccessfile = ABSPATH . '.htaccess';
			if ( file_exists( $htaccessfile ) ) {
				$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( str_replace( '# BEGIN CACHIFY', '', $htaccess_content ) !== $htaccess_content ) {
					$runscachify = true;
					add_action( 'template_redirect', 'irld_start_buffering', 1 );
				}
			}
		}
		if ( is_plugin_active( 'speedycache/speedycache.php' ) ) {
			$speedycache_config = get_option( 'speedycache_options' );
			if ( isset( $speedycache_config['status'] ) ) {
				if ( '1' === trim( $speedycache_config['status'] ) ) {
					$runscachify = true;
					add_action( 'template_redirect', 'irld_start_buffering', 1 );
				}
			}
		}
		if ( false === $runscachify ) {
			add_action( 'plugins_loaded', 'irld_start_buffering' );
		}
		add_action( 'wp_enqueue_scripts', 'irld_load_scripts' );
		add_action( 'wp_after_admin_bar_render', 'irld_process_and_output', 0 );

	}
}
