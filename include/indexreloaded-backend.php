<?php
/**
 *  Indexreloaded-backend
 *
 *  The indexreloaded-backend.php performs tasks when Indexreloaded is loaded in backend
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

/**
 * Deletes all files in a directory recursively.
 *
 * @param string $dir directory to clear.
 */
function irld_rmdir_recursive( $dir ) {
	$entries = scandir( $dir, SCANDIR_SORT_NONE );
	foreach ( $entries as $entry ) {
		if ( ( '.' === $entry ) || ( '..' === $entry ) ) {
			continue;
		}
		$path = $dir . DIRECTORY_SEPARATOR . $entry;
		if ( is_file( $path ) || is_link( $path ) ) {
			unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		} else {
			irld_rmdir_recursive( $path );
		}
	}
	rmdir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
}
/**
 * Purges active page cache.
 *
 * @return void
 */
function irld_page_cache_delete() {
	$pagecachedir = irld_page_cache_check( true );
	if ( '' !== $pagecachedir ) {
		if ( '*litespeed-cache*' !== $pagecachedir ) {
			irld_rmdir_recursive( $pagecachedir );
		} else {
			\LiteSpeed\Core::cls( 'Purge' )->purge_all();
		}
	}
}

/**
 * Purges active page cache for an url.
 *
 * @param string $pagepostname url for wchich page cache should be removed.
 * @param string $cache_type type of cache that has been set (database or redis).
 *
 * @return string
 */
function irld_page_cache_purge_page( $pagepostname, $cache_type = 'Redis' ) {
	$echo_text = '';
	$site      = site_url();
	$site      = str_replace( 'https://', '', $site );
	$site      = str_replace( 'http://', '', $site );
	$site      = substr( $site, 0, strlen( $site ) );
	if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
		if ( class_exists( 'WpFastestCache' ) ) {
			$cache_config     = get_option( 'WpFastestCache' );
			$cache_config     = str_replace( '{"wpFastestCacheStatus":"', '', $cache_config );
			$arr_cache_config = explode( '"', $cache_config );
			$cache_status     = $arr_cache_config[0];
			if ( 'on' === $cache_status ) {
				// WP Fastest Cache.
				$cachedir  = ABSPATH . 'wp-content/cache/all';
				$cachefile = $cachedir . $pagepostname . 'index.html';
				$cache_del = '';
				if ( file_exists( $cachefile ) ) {
					unlink( $cachefile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					$cache_del = 'ok';
				}
				if ( 'ok' === $cache_del ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared wpfastestcache';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no wpfastestcache found in "' . esc_html( $cachefile ) . '"';
				}
			}
		} else {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page but didnt find class WpFastestCache';
		}
	}

	if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
		// W3 Total Cache.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'W3TC Page Cache core', '', $htaccess_content ) !== $htaccess_content ) {
				$cachedir  = ABSPATH . 'wp-content/cache/page_enhanced/' . $site;
				$cachefile = $cachedir . $pagepostname . '_index_slash_ssl.html_gzip';
				$cache_del = '';
				if ( file_exists( $cachefile ) ) {
					unlink( $cachefile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					$cachefile = $cachedir . $pagepostname . '_index_slash_ssl.html';
					if ( file_exists( $cachefile ) ) {
						unlink( $cachefile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						$cachefile = $cachedir . $pagepostname . '_index_slash_ssl.html';
						$cache_del = 'ok';
					}
				}
				if ( 'ok' === $cache_del ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared W3 total cache';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no W3 total cache found: "' . esc_html( $cachefile ) . '"';
				}
			}
		}
	}

	if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
		// WP Super Cache.
		$configdir  = ABSPATH . 'wp-content/';
		$configfile = $configdir . 'wp-cache-config.php';
		if ( file_exists( $configfile ) ) {
			include $configfile;
		} else {
			$cache_path = $configfile . ' not found';
		}

		$cachedir  = $cache_path . 'supercache/' . $site;
		$files1    = scandir( $cachedir . $pagepostname );
		$cachefile = $cachedir . $pagepostname;
		$cache_del = '';
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $cachedir . $pagepostname . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				$cachefile = $cachedir . $pagepostname . $file;
				$cache_del = 'ok';
			}
		}
		if ( 'ok' === $cache_del ) {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared WP Super cache';
		} else {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no WP Super cache found: "' . esc_html( $cachefile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
		}
	}

	if ( is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) {
		// WP Optimize.

		$wpo_cache_config = get_option( 'wpo_cache_config' );
		if ( isset( $wpo_cache_config['enable_page_caching'] ) ) {
			if ( '1' === trim( $wpo_cache_config['enable_page_caching'] ) ) {
				$cachedir  = ABSPATH . 'wp-content/cache/wpo-cache/' . $site;
				$files1    = scandir( $cachedir . $pagepostname );
				$cachefile = $cachedir . $pagepostname;
				$cache_del = '';
				foreach ( $files1 as $file ) {
					if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
						unlink( $cachedir . $pagepostname . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						$cachefile = $cachedir . $pagepostname . $file;
						$cache_del = 'ok';
					}
				}
				if ( 'ok' === $cache_del ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared WP Optimize';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no WP Optimize cache found: "' . esc_html( $cachefile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
				}
			} else {
				$echo_text = 'enable_page_caching is "' . $wpo_cache_config['enable_page_caching'] . '" so caching not enabled in WP Optimize';
			}
		} else {
			$echo_text = 'enable_page_caching not set, WP Optimize';
		}
	}

	if ( is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ) ) {
		// Hummingbird.
		$cachedir  = ABSPATH . 'wp-content/wphb-cache/cache/' . $site;
		$files1    = scandir( $cachedir . $pagepostname );
		$cachefile = $cachedir . $pagepostname;
		$cache_del = '';
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $cachedir . $pagepostname . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				$cachefile = $cachedir . $pagepostname . $file;
				$cache_del = 'ok';
			}
		}
		if ( 'ok' === $cache_del ) {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Hummingbird';
		} else {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Hummingbird cache found: "' . esc_html( $cachefile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
		}
	}

	if ( is_plugin_active( 'hyper-cache/plugin.php' ) ) {
		// Hyper Cache.
		$cachedir  = ABSPATH . 'wp-content/cache/hyper-cache/' . $site;
		$files1    = scandir( $cachedir . $pagepostname );
		$cachefile = $cachedir . $pagepostname;
		$cache_del = '';
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $cachedir . $pagepostname . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				$cachefile = $cachedir . $pagepostname . $file;
				$cache_del = 'ok';
			}
		}
		if ( 'ok' === $cache_del ) {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Hyper Cache';
		} else {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Hyper Cache cache found: "' . esc_html( $cachefile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
		}
	}

	if ( is_plugin_active( 'cache-enabler/cache-enabler.php' ) ) {
		// Cache Enabler.
		$cachedir  = ABSPATH . 'wp-content/cache/cache-enabler/' . $site;
		$files1    = scandir( $cachedir . $pagepostname );
		$cachefile = $cachedir . $pagepostname;
		$cache_del = '';
		foreach ( $files1 as $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
				unlink( $cachedir . $pagepostname . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				$cachefile = $cachedir . $pagepostname . $file;
				$cache_del = 'ok';
			}
		}
		if ( 'ok' === $cache_del ) {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Cache Enabler';
		} else {
			$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Cache Enabler cache found: "' . esc_html( $cachefile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
		}
	}

	if ( is_plugin_active( 'comet-cache/comet-cache.php' ) ) {
		// Comet Cache.
		$filesize = 0;
		// check if file advanced-cache.php exists in wp-content.
		if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
			// if so, then check if filesize is bigger than 1kb.
			$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
		}
		if ( $filesize > 10 ) {
			$comet_cache_config = get_option( 'comet_cache_options' );
			$cache_path         = '';
			if ( isset( $comet_cache_config['base_dir'] ) ) {
				$cache_path = trim( $comet_cache_config['base_dir'] );
			}
			$triggerfile = '';
			$triggerpath = '/';
			if ( '/' === $pagepostname ) {
				$triggerfile = 'index.html';
			} else {
				$pagepostnamearr = explode( '/', $pagepostname );
				$cntppna         = count( $pagepostnamearr );
				if ( 2 === $cntppna ) {
					$triggerfile = str_replace( '_', '-', str_replace( '/', '', $pagepostname ) ) . '.html';
				} else {
					$possfilename = array_pop( $pagepostnamearr );
					if ( 0 === strlen( trim( $possfilename ) ) ) {
						$possfilename = array_pop( $pagepostnamearr );
					}
					$triggerpath = implode( '/', $pagepostnamearr ) . '/';
					$triggerfile = str_replace( '_', '-', $possfilename ) . '.html';

				}
			}

			$cachedir  = ABSPATH . 'wp-content/' . $cache_path . '/cache/https/' . str_replace( '.', '-', $site );
			$files1    = scandir( $cachedir . $triggerpath );
			$cachefile = $cachedir . $triggerpath . $triggerfile;
			$cache_del = '';
			foreach ( $files1 as $file ) {
				if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
					if ( $triggerfile === $file ) {
						unlink( $cachedir . $triggerpath . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						$cachefile = $cachedir . $triggerpath . $file;
						$cache_del = 'ok';
					}
				}
			}
			if ( 'ok' === $cache_del ) {
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Comet Cache';
			} else {
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Comet Cache cache found: "' . esc_html( $cachedir . '/' . $triggerfile ) . '", cache_path:"' . esc_html( $cache_path ) . '"';
			}
		}
	}

	if ( is_plugin_active( 'rapid-cache/rapid-cache.php' ) ) {
		// Rapid Cache.
		$filesize = 0;
		// check if file advanced-cache.php exists in wp-content.
		if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
			// if so, then check if filesize is bigger than 1kb.
			$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
		}
		if ( $filesize > 10 ) {
			$rapid_cache_config = get_option( 'rapid_cache_options' );
			$cache_path         = '';
			if ( isset( $rapid_cache_config['base_dir'] ) ) {
				$cache_path = trim( $rapid_cache_config['base_dir'] );
			}

			$triggerfile = '';
			$triggerpath = '/';
			if ( '/' === $pagepostname ) {
				$triggerfile = 'index.html';
			} else {
				$pagepostnamearr = explode( '/', $pagepostname );
				$cntppna         = count( $pagepostnamearr );
				if ( 2 === $cntppna ) {
					$triggerfile = str_replace( '_', '-', str_replace( '/', '', $pagepostname ) ) . '.html';
				} else {
					$possfilename = array_pop( $pagepostnamearr );
					if ( 0 === strlen( trim( $possfilename ) ) ) {
						$possfilename = array_pop( $pagepostnamearr );
					}
					$triggerpath = implode( '/', $pagepostnamearr ) . '/';
					$triggerfile = str_replace( '_', '-', $possfilename ) . '.html';
				}
			}

			$cachedir  = ABSPATH . 'wp-content/' . $cache_path . '/cache/https/' . str_replace( '.', '-', $site );
			$files1    = scandir( $cachedir . $triggerpath );
			$cachefile = $cachedir . $triggerpath . $triggerfile;
			$cache_del = '';
			foreach ( $files1 as $file ) {
				if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
					if ( $triggerfile === $file ) {
						unlink( $cachedir . $triggerpath . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						$cachefile = $cachedir . $triggerpath . $file;
						$cache_del = 'ok';
					}
				}
			}
			if ( 'ok' === $cache_del ) {
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Rapid Cache for file "' . esc_html( $cachefile ) . '"';
			} else {
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Rapid Cache cache found: "' . esc_html( $cachefile ) . '"';
			}
		}
	}

	if ( is_plugin_active( 'borlabs-cache/borlabs-cache.php' ) ) {
		// Borlabs Cache.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'BEGIN Borlabs Cache', '', $htaccess_content ) !== $htaccess_content ) {
				$url = 'https://' . $site . $pagepostname;
				global $wpdb;

				$url_info = wp_parse_url( $url );
				$is_https = 'https' === $url_info['scheme'] ? 1 : 0;

				if ( empty( $url_info['path'] ) ) {
					$url_info['path'] = '/';
				}

				$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						'UPDATE
				                ' . $wpdb->prefix . 'borlabs_cache_pages
				            SET
				                last_updated=\'0000-00-00 00:00:00\',
				                next_update=\'0000-00-00 00:00:00\',
				                runtime_with_cache=0
				            WHERE
				                domain=%s
				                AND
				                https=%d
				                AND
				                prefix=%s
				                AND
				                url=%s
				        	',
						array( $url_info['host'], $is_https, $prefix, $url_info['path'] )
					)
				);
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and refreshed Borlabs Cache page cache database';
			}
		}
	}

	if ( is_plugin_active( 'cachify/cachify.php' ) ) {
		// Cachify.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( '# BEGIN CACHIFY', '', $htaccess_content ) !== $htaccess_content ) {
				$cachedir    = ABSPATH . 'wp-content/cache/cachify/https-' . trim( $site );
				$triggerpath = '/';
				if ( '/' !== $pagepostname ) {
					$triggerpath = $pagepostname;
				}

				$files1    = scandir( $cachedir . $triggerpath );
				$cache_del = '';
				foreach ( $files1 as $file ) {
					if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
						if ( file_exists( $cachedir . $triggerpath . $file ) ) {
							unlink( $cachedir . $triggerpath . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
							$cachefile = $cachedir . $triggerpath . $file;
							$cache_del = 'ok';
						}
					}
				}
				if ( 'ok' === $cache_del ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Cachify for file "' . esc_html( $cachefile ) . '"';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Cachify cache found in path: "' . esc_html( $cachedir . $triggerpath ) . '"';
				}
			}
		}
	}

	if ( is_plugin_active( 'swift-performance-lite/performance.php' ) ) {
		// Swift Performance Lite.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'END Swift Performance', '', $htaccess_content ) !== $htaccess_content ) {

				$triggerpath = '/';
				if ( '/' !== $pagepostname ) {
					$triggerpath = $pagepostname;
				}
				$cachedir  = SWIFT_PERFORMANCE_CACHE_DIR . $triggerpath . 'desktop/unauthenticated/';
				$files1    = scandir( $cachedir );
				$cache_del = '';
				foreach ( $files1 as $file ) {
					if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
						if ( file_exists( $cachedir . $file ) ) {
							unlink( $cachedir . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
							$cachefile = $cachedir . $file;
							$cache_del = 'ok';
						}
					}
				}
				if ( 'ok' === $cache_del ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared Swift Performance Lite for file "' . esc_html( $cachefile ) . '"';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no Swift Performance Lite cache found in path: "' . esc_html( $cachedir ) . '"';
				}
			}
		}
	}

	if ( is_plugin_active( 'wp-cloudflare-page-cache/wp-cloudflare-super-page-cache.php' ) ) {
		// Super Page Cache.
		// This is the only plugin able to detect the activities of IndexReloaded.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'WP Cloudflare Super Page Cache', '', $htaccess_content ) !== $htaccess_content ) {
				$filesize = 0;
				// check if file advanced-cache.php exists in wp-content.
				if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
					// if so, then check if filesize is bigger than 1kb.
					$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
				}
				if ( $filesize > 10 ) {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page, but there is no need to purge any page cache files by Super Page Cache';
				}
			}
		}
	}

	// LiteSpeed Cache.
	if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( '## LITESPEED WP CACHE PLUGIN', '', $htaccess_content ) !== $htaccess_content ) {
				\LiteSpeed\Core::cls( 'Purge' )->purge_url( $pagepostname );
				$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page, and purges page cache files by Litespeed Cache';

			}
		}
	}

	// SpeedyCache.
	if ( is_plugin_active( 'speedycache/speedycache.php' ) ) {
		$speedycache_config = get_option( 'speedycache_options' );
		if ( isset( $speedycache_config['status'] ) ) {
			if ( '1' === trim( $speedycache_config['status'] ) ) {
				$plugin_pagecache_file = ABSPATH . 'wp-content/cache/speedycache/' . trim( $site ) . '/all' . $pagepostname . 'index.html';
				if ( file_exists( $plugin_pagecache_file ) ) {
					unlink( $plugin_pagecache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and prepared SpeedyCache "' . esc_html( $pagepostname ) . '"';
				} else {
					$echo_text = 'did set ' . esc_html( $cache_type ) . '-cache for page and no SpeedyCache-file found: "' . esc_html( $plugin_pagecache_file ) . '"';
				}
			}
		}
	}

	return $echo_text;
}
/**
 * Checks for active page cache plugins.
 *
 * @param bool $returncachedir Return the cache directory instead of plugin name.
 *
 * @return string
 */
function irld_page_cache_check( $returncachedir = false ) {
	$plugin_name          = '';
	$plugin_pagecache_dir = '';
	$site                 = site_url();
	$site                 = str_replace( 'https://', '', $site );
	$site                 = str_replace( 'http://', '', $site );
	$site                 = substr( $site, 0, strlen( $site ) );
	if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
		if ( class_exists( 'WpFastestCache' ) ) {
			$cache_config     = get_option( 'WpFastestCache' );
			$cache_config     = str_replace( '{"wpFastestCacheStatus":"', '', $cache_config );
			$arr_cache_config = explode( '"', $cache_config );
			$cache_status     = $arr_cache_config[0];
			if ( 'on' === $cache_status ) {
				// WP Fastest Cache.
				$plugin_name          = 'WP Fastest Cache';
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/all';
			}
		}
	}

	if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
		// W3 Total Cache.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'W3TC Page Cache core', '', $htaccess_content ) !== $htaccess_content ) {
				$plugin_name          = 'W3 Total Cache';
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/page_enhanced/' . $site;
			}
		}
	}

	if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
		// WP Super Cache.
		$configdir  = ABSPATH . 'wp-content/';
		$configfile = $configdir . 'wp-cache-config.php';
		if ( file_exists( $configfile ) ) {
			$plugin_name = 'WP Super Cache';
			include $configfile;
			$plugin_pagecache_dir = $cache_path . 'supercache/' . $site;
		}
	}
	if ( is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) {
		// WP Optimize.
		$wpo_cache_config = get_option( 'wpo_cache_config' );
		if ( isset( $wpo_cache_config['enable_page_caching'] ) ) {
			if ( '1' === trim( $wpo_cache_config['enable_page_caching'] ) ) {
				$plugin_name          = 'WP Optimize';
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/wpo-cache/' . $site;
			}
		}
	}

	if ( is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ) ) {
		// Hummingbird.
		$plugin_name          = 'Hummingbird';
		$plugin_pagecache_dir = ABSPATH . 'wp-content/wphb-cache/cache/' . $site;

	}

	if ( is_plugin_active( 'hyper-cache/plugin.php' ) ) {
		// Hyper Cache.
		$plugin_name          = 'Hyper Cache';
		$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/hyper-cache/' . $site;
	}

	if ( is_plugin_active( 'cache-enabler/cache-enabler.php' ) ) {
		// Cache Enabler.
		$plugin_name          = 'Cache Enabler';
		$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/cache-enabler/' . $site;
	}

	if ( is_plugin_active( 'comet-cache/comet-cache.php' ) ) {
		// Comet Cache.
		$filesize = 0;
		// check if file advanced-cache.php exists in wp-content.
		if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
			// if so, then check if filesize is bigger than 1kb.
			$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
		}
		if ( $filesize > 10 ) {
			$plugin_name        = 'Comet Cache';
			$comet_cache_config = get_option( 'comet_cache_options' );

			if ( isset( $comet_cache_config['base_dir'] ) ) {
				$cache_path           = trim( $rapid_cache_config['base_dir'] );
				$plugin_pagecache_dir = ABSPATH . 'wp-content/' . $cache_path . '/cache/https/';
			}
		}
	}

	if ( is_plugin_active( 'rapid-cache/rapid-cache.php' ) ) {
		// Rapid Cache.
		$filesize = 0;
		// check if file advanced-cache.php exists in wp-content.
		if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
			// if so, then check if filesize is bigger than 1kb.
			$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
		}
		if ( $filesize > 10 ) {
			$plugin_name        = 'Rapid Cache';
			$rapid_cache_config = get_option( 'rapid_cache_options' );
			if ( isset( $rapid_cache_config['base_dir'] ) ) {
				$cache_path           = trim( $rapid_cache_config['base_dir'] );
				$plugin_pagecache_dir = ABSPATH . 'wp-content/' . $cache_path . '/cache/https/';
			}
		}
	}

	if ( is_plugin_active( 'borlabs-cache/borlabs-cache.php' ) ) {
		// Borlabs Cache.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'BEGIN Borlabs Cache', '', $htaccess_content ) !== $htaccess_content ) {
				$plugin_name          = 'Borlabs Cache';
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/borlabs_cache/';
			}
		}
	}

	if ( is_plugin_active( 'cachify/cachify.php' ) ) {
		// Cachify.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( '# BEGIN CACHIFY', '', $htaccess_content ) !== $htaccess_content ) {
				$plugin_name          = 'Cachify';
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/cachify/https-' . trim( $site ) . '/';
			}
		}
	}

	if ( is_plugin_active( 'swift-performance-lite/performance.php' ) ) {
		// Swift Performance Lite.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'END Swift Performance', '', $htaccess_content ) !== $htaccess_content ) {
				$plugin_name          = 'Swift Performance Lite';
				$plugin_pagecache_dir = SWIFT_PERFORMANCE_CACHE_DIR . 'desktop/unauthenticated/';
			}
		}
	}

	if ( is_plugin_active( 'wp-cloudflare-page-cache/wp-cloudflare-super-page-cache.php' ) ) {
		// Super Page Cache.
		// Even if this is the only plugin able to detect the activities of IndexReloaded, on complete delete of the CSS/JS files, we need to crush the cache.
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( 'WP Cloudflare Super Page Cache', '', $htaccess_content ) !== $htaccess_content ) {
				$filesize = 0;
				// check if file advanced-cache.php exists in wp-content.
				if ( file_exists( ABSPATH . 'wp-content/advanced-cache.php' ) ) {
					// if so, then check if filesize is bigger than 1kb.
					$filesize = filesize( ABSPATH . 'wp-content/advanced-cache.php' );
				}
				if ( $filesize > 10 ) {
					$advanced_cache                 = file_get_contents( ABSPATH . 'wp-content/advanced-cache.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$advanced_cache_arr             = explode( '$swcfpc_fallback_cache_path        = ', $advanced_cache );
					$swcfpc_fallback_cache_path_arr = explode( '";', $advanced_cache_arr[1] );
					$swcfpc_fallback_cache_path_raw = $swcfpc_fallback_cache_path_arr[0];
					$swcfpc_fallback_cache_path_raw = str_replace( 'WP_CONTENT_DIR . "', ABSPATH . 'wp-content', $swcfpc_fallback_cache_path_raw );
					$swcfpc_fallback_cache_path     = str_replace( '{$_SERVER[\'HTTP_HOST\']}', $site, $swcfpc_fallback_cache_path_raw );
				} else {
					$swcfpc_fallback_cache_path = '';
				}

				$plugin_pagecache_dir = $swcfpc_fallback_cache_path;
				$plugin_name          = 'Super Page Cache';
			}
		}
	}

	// LiteSpeed Cache.
	if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
		$htaccessfile = ABSPATH . '.htaccess';
		if ( file_exists( $htaccessfile ) ) {
			$htaccess_content = file_get_contents( $htaccessfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( str_replace( '## LITESPEED WP CACHE PLUGIN', '', $htaccess_content ) !== $htaccess_content ) {
				$plugin_pagecache_dir = '*litespeed-cache*';
				$plugin_name          = 'Litespeed Cache';

			}
		}
	}

	// SpeedyCache.
	if ( is_plugin_active( 'speedycache/speedycache.php' ) ) {
		$speedycache_config = get_option( 'speedycache_options' );
		if ( isset( $speedycache_config['status'] ) ) {
			if ( '1' === trim( $speedycache_config['status'] ) ) {
				$plugin_pagecache_dir = ABSPATH . 'wp-content/cache/speedycache/' . trim( $site ) . '/all/';
				$plugin_name          = 'SpeedyCache';
			}
		}
	}

	if ( false === $returncachedir ) {
		return $plugin_name;
	} else {
		return $plugin_pagecache_dir;
	}
}
/**
 * Checks for excludes depending system configuration.
 *
 * @return array
 */
function irld_excludes_check() {
	global $wpdb;
	$apl               = get_option( 'active_plugins' );
	$plugins           = get_plugins();
	$activated_plugins = array();
	foreach ( $apl as $p ) {
		if ( isset( $plugins[ $p ] ) ) {
			array_push( $activated_plugins, $plugins[ $p ] );
		}
	}

	$plugtext            = '';
	$recommendedexcludes = '';
	if ( isset( $fromserver_opts['indexreloaded_excludesbyplugin'] ) ) {
		$opts     = $fromserver_opts['indexreloaded_excludesbyplugin'];
		$optsfile = require realpath( str_replace( 'include', '', str_replace( 'include', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
		str_replace( '/', DIRECTORY_SEPARATOR, 'include/tooldata/excludesbyplugin.php' );
		if ( count( $opts ) < count( $optsfile ) ) {
			$opts = $optsfile;
		}
	} else {
		$opts = require realpath( str_replace( 'include', '', str_replace( 'include', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
		str_replace( '/', DIRECTORY_SEPARATOR, 'include/tooldata/excludesbyplugin.php' );
	}

	$allactivatedplugins = '';
	$pluginmatchfound    = false;
	$plugincnt           = 0;
	$pluginexclcnt       = 0;

	$cnt_activated_plugins = count( $activated_plugins );
	foreach ( $activated_plugins as $activeplugin ) {
		if ( isset( $activeplugin['Name'] ) ) {
			$allactivatedplugins .= $activeplugin['Name'];
			foreach ( $opts as $pluginname => $plugininfo ) {
				if ( isset( $activeplugin['Name'] ) ) {
					if ( $activeplugin['Name'] === $pluginname ) {
						$pluginmatchfound = true;
						if ( isset( $plugininfo['exclude'] ) ) {
							if ( 2 === $pluginexclcnt ) {
								$plugtext .= '</p><a role="button" class="idrd-show-more button irld-button" id="btn-show-more-exp-block-3">' .
								__( 'Show more', 'indexreloaded' ) . '</a><br /><p class="irld-hide irld-hidden-part" id="hidden-exp-block-3">';
							}

							$plugtext            .= '<b>' . $activeplugin['Name'] . ':</b> ' . __( 'The following exclude(s) are needed', 'indexreloaded' ) . ':<br />';
							$plugtext            .= '&quot;' . $plugininfo['exclude'] . '&quot; - <b>' . __( 'Reason', 'indexreloaded' ) . ':</b> ' . $plugininfo['reason'] . '<br />';
							$recommendedexcludes .= $plugininfo['exclude'] . ',';
							$allactivatedplugins .= ' *';
							++$pluginexclcnt;
						}

						break;
					}
				}
			}

			$allactivatedplugins .= '<br />';
			++$plugincnt;
			if ( ( 4 === $plugincnt ) && ( $cnt_activated_plugins > 4 ) ) {
				$allactivatedplugins .= '</p><a role="button" class="idrd-show-more button irld-button" id="btn-show-more-exp-block-2">' .
					__( 'Show more', 'indexreloaded' ) . '</a><br />';
				$allactivatedplugins .= '<p class="irld-hide irld-hidden-part" id="hidden-exp-block-2">';
			}
		}
	}

	if ( $pluginexclcnt > 2 ) {
		$plugtext = '<p class="irld-fade" id="visible-exp-block-3">' . $plugtext .
		'</p><a role="button" class="idrd-show-less irld-hide button irld-button" id="btn-show-less-exp-block-3">' .
		__( 'Show less', 'indexreloaded' ) . '</a><br />';
	}
	// Plugins.
	// Elements used.
	$elemarr     = array();
	$elemarrpage = array();
	$rows        = $wpdb->get_results(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT DISTINCT `ID` as ID, `post_title` as post_title, `post_type` as post_type, `post_type` as post_type, `post_content` as post_content 
											FROM {$wpdb->posts}
											WHERE post_type IN ('page','post') AND post_status = %s ORDER BY post_title",
			'publish'
		)
	);
	$countregs   = 0;
	if ( is_array( $rows ) ) {
		$countregs = count( $rows );
		foreach ( $rows as $postrow ) {
			$post_content     = $postrow->post_content;
			$post_content_arr = explode( '<!-- wp', $post_content );
			if ( count( $post_content_arr ) > 1 ) {
				$pick = 1;
				foreach ( $post_content_arr as $post_content_arrrow ) {
					$post_content_arrarr = explode( ' ', $post_content_arrrow );
					$elementfound        = '';
					if ( count( $post_content_arrarr ) > 1 ) {
						if ( 1 === $pick ) {
							$elementfound = $post_content_arrarr[0];
							$pick         = 0;
						} else {
							$pick = 1;
						}
					}

					if ( $elementfound ) {
						if ( 0 === count( $elemarrpage ) ) {
							$elemarrpage[ $elementfound ]        = array();
							$elemarrpage[ $elementfound ]['nbr'] = 1;
						} else {
							$iselfound = 0;
							foreach ( $elemarrpage as $elfound => &$elfoundnbr ) {
								if ( $elfound === $elementfound ) {
									++$elemarrpage[ $elfound ]['nbr'];
									$iselfound = 1;
									break;
								}
							}

							if ( 0 === $iselfound ) {
								$elemarrpage[ $elementfound ]        = array();
								$elemarrpage[ $elementfound ]['nbr'] = 1;
							}
						}
					}
				}

				if ( 0 === count( $elemarr ) ) {
					$elemarr = $elemarrpage;
				} else {
					foreach ( $elemarrpage as $elfound => $elfoundnbr ) {
						$iselfound = 0;
						foreach ( $elemarr as $efound => &$efoundnbr ) {
							if ( $elfound === $efound ) {
								++$elemarr[ $efound ]['nbr'];
								$iselfound = 1;
								break;
							}
						}

						if ( 0 === $iselfound ) {
							$elemarr[ $elfound ]        = array();
							$elemarr[ $elfound ]['nbr'] = $elfoundnbr['nbr'];
						}
					}
				}

				$elemarrpage = array();
			}

			// playlist type="video".
			$post_content_arr = explode( '[playlist type="video"', $post_content );
			if ( count( $post_content_arr ) > 1 ) {
				$elemarrpage['Video']        = array();
				$elemarrpage['Video']['nbr'] = count( $post_content_arr ) - 1;
				if ( 0 === count( $elemarr ) ) {
					$elemarr = $elemarrpage;
				} else {
					foreach ( $elemarrpage as $elfound => $elfoundnbr ) {
						$iselfound = 0;
						foreach ( $elemarr as $efound => &$efoundnbr ) {
							if ( $elfound === $efound ) {
								++$elemarr[ $efound ]['nbr'];
								$iselfound = 1;
								break;
							}
						}

						if ( 0 === $iselfound ) {
							$elemarr[ $elfound ]        = array();
							$elemarr[ $elfound ]['nbr'] = $elfoundnbr['nbr'];
						}
					}
				}

				$elemarrpage = array();
			}
		}
	}

	$elemret = '';
	if ( count( $elemarr ) > 0 ) {
		if ( isset( $fromserver_opts['indexreloaded_excludesbyelement'] ) ) {
			$opts = $fromserver_opts['indexreloaded_excludesbyelement'];
		} else {
			$opts = require realpath( str_replace( 'include', '', str_replace( 'include', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
			str_replace( '/', DIRECTORY_SEPARATOR, 'include/tooldata/excludesbyelement.php' );
		}

		foreach ( $elemarr as $efound => $efoundnbr ) {
			foreach ( $opts as $elementname => $elementinfo ) {
				if ( $efound === $elementname ) {
					$plugtext            .= '<b>' . $efound . ':</b> ' . __( 'The following exclude(s) are needed', 'indexreloaded' ) . ':<br />';
					$plugtext            .= '&quot;' . $elementinfo['exclude'] . '&quot; - <b>' . __( 'Reason', 'indexreloaded' ) . ':</b> ' . $elementinfo['reason'] . '<br />';
					$recommendedexcludes .= $elementinfo['exclude'] . ',';
				}
			}
		}
	}

	if ( '' !== trim( $elemret ) ) {
		$elemret = '<br><span class="irld-headdetails">' . __( 'Elements identified in pages', 'indexreloaded' ) . ':</span><br>' . trim( $elemret );
	} else {
		$elemret = '';
	}

	if ( '' !== $allactivatedplugins ) {
		$expandhead = '<p>';
		$expandfoot = '</p>';
		if ( $cnt_activated_plugins > 4 ) {
			$expandhead = '<p class="irld-fade" id="visible-exp-block-2">';
			$expandfoot = '</p><a role="button" class="idrd-show-less irld-hide button irld-button" id="btn-show-less-exp-block-2">' .
					__( 'Show less', 'indexreloaded' ) . '</a>';
		}

		$allactivatedplugins = '<br><span class="irld-headdetails">' . __( 'List of active plugins', 'indexreloaded' ) . '</span>:' . $expandhead . '' . $allactivatedplugins . '<span>' . $expandfoot;
		if ( true === $pluginmatchfound ) {
			$allactivatedplugins .= '<br /><span>' . __( '* Plugin needs attention', 'indexreloaded' ) . '</span>';
		}
	}

	// check theme.
	$themeret   = '';
	$my_theme   = wp_get_theme();
	$themefound = $my_theme->get( 'Name' );
	if ( '' !== trim( $themefound ) ) {
		$themeret = $themefound;
		if ( isset( $fromserver_opts['indexreloaded_excludesbytheme'] ) ) {
			$opts = $fromserver_opts['indexreloaded_excludesbytheme'];
		} else {
			$opts = require realpath( str_replace( 'include', '', str_replace( 'include', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
			str_replace( '/', DIRECTORY_SEPARATOR, 'include/tooldata/excludesbytheme.php' );
		}

		foreach ( $opts as $elementname => $elementinfo ) {
			if ( $themefound === $elementname ) {
				if ( isset( $elementinfo['exclude'] ) ) {
					$plugtext            .= '<b>' . $themefound . ':</b> ' . __( 'The following exclude(s) are needed for current theme', 'indexreloaded' ) . ':<br />';
					$plugtext            .= '&quot;' . $elementinfo['exclude'] . '&quot; - <b>' . __( 'Reason', 'indexreloaded' ) . ':</b> ' . $elementinfo['reason'] . '<br />';
					$recommendedexcludes .= $elementinfo['exclude'] . ',';
				}
			}
		}
	}

	if ( '' !== trim( $themeret ) ) {
		$themeret = '<br /><span class="irld-headdetails">' . __( 'Current theme', 'indexreloaded' ) . ':</span><br>' . trim( $themeret ) . '<br />';
	} else {
		$themeret = '';
	}

	$ret                        = array();
	$ret['themeret']            = $themeret;
	$ret['recommendedexcludes'] = $recommendedexcludes;
	$ret['plugtext']            = $plugtext;
	$ret['allactivatedplugins'] = $allactivatedplugins;
	$ret['elemret']             = $elemret;
	return $ret;
}
