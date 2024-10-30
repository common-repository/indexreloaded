<?php
/**
 * This class is called by the plugins main file indexreloaded.php, it calls all the rest. It also handles cache for CCSS and the plugins links in plugins page.
 *
 * @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );

if ( ! class_exists( 'Indexreloadedroot' ) ) {

	require_once 'class-indexreloadedadminnotices.php';
	require_once 'class-indexreloadedadminmenu.php';
	require_once 'class-indexreloadedsettings.php';
	require_once 'indexreloaded-frontend.php';
	require_once 'indexreloaded-backend.php';
	require_once 'indexreloaded-ajax.php';

	/**
	 * Indexreloadedroot
	 */
	final class Indexreloadedroot {
		/**
		 * Tracelog caching activity.
		 *
		 * @var boolean
		 */
		protected $tracelog_caching = false;
		/**
		 * Notices object.
		 *
		 * @var object
		 */
		private $the_notices;

		/**
		 * Setup class.
		 */
		public function __construct() {
			$this->the_notices = Indexreloadedadminnotices::getthe_instance();
			add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
			add_action( 'wp_after_insert_post', array( $this, 'drop_redis_cache_post' ), 10, 4 );
			add_action( 'activated_plugin', array( $this, 'drop_redis_cache_plg' ), 10, 2 );
			add_action(
				'updated_option',
				function ( $option_name, $old_value, $value ) {
					if ( ( str_replace( 'indexreloaded_', '', $option_name ) !== $option_name ) && ( 'indexreloaded_lvc' !== $option_name ) ) {
						if ( ( 'indexreloaded_tagsToKeepAboveTheFold' === $option_name ) || ( 'indexreloaded_classesToKeepAboveTheFold' === $option_name ) || ( 'indexreloaded_IDsToKeepAboveTheFold' === $option_name ) ) {
							if ( $old_value !== $value ) {
								if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
									if ( wp_cache_supports( 'flush_group' ) ) {
										wp_cache_flush_group( 'indexreloaded' );
										if ( true === $this->tracelog_caching ) {
											irld_trace_log( 'wp_cache_flush_group updated_option ' . $option_name, 0 );
										}
									}
								} elseif ( str_replace( 'indexreloaded_cache_real-page-', '', $option_name ) === $option_name ) {
									$this->delete_option_cache();
								}
							}
						}
					}
				},
				10,
				3
			);
			add_action( 'customize_save_after', array( $this, 'irld_execute_on_customize_save_after_event' ), 10, 1 );
		}

		/**
		 * Fires as the admin screen is being initialized.
		 */
		public function action_admin_init() {
			// Check for PHP version.
			if ( version_compare( PHP_VERSION, '8.0' ) < 0 ) {
				$this->the_notices->info(
					sprintf(
					/* translators: verify the link from time to time */
						__(
							'The PHP version you are using, %s has reached end-of-life! Please talk to your hosting provider or administrator about upgrading to a <a href="https://php.net/supported-versions.php" target="_blank" rel="noopener noreferrer">supported version</a>.',
							'indexreloaded'
						), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
						PHP_VERSION
					),
					'old-php-ver'
				);
			}

			// Check for WP version.
			$wp_version = get_bloginfo( 'version' );
			if ( version_compare( $wp_version, '5.6.0' ) < 0 ) {
				$this->the_notices->info(
					sprintf(
					/* translators: verify required version from time to time */
						__( 'You are using WordPress %1$s. This plugin has been tested with %2$s. Please upgrade to the latest WordPress.', 'indexreloaded' ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
						$wp_version,
						'6.0.0'
					),
					'old-wp-ver'
				);
			}

			// Check for known page cache.
			$pagecacheplugin = irld_page_cache_check();
			if ( '' !== $pagecacheplugin ) {
				$this->the_notices->info(
					sprintf(
						/* translators: verify required version from time to time */
						__( 'You are using page cache plugin %1$s. Please purge the page cache files, before starting with IndexReloaded.', 'indexreloaded' ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
						$pagecacheplugin
					),
					'page-cache'
				);
			}
		}


		/**
		 * Sets up links in plugins screen.
		 *
		 * @param array $links Links of IndexReloaded in plugins screen.
		 *
		 * @return array
		 */
		public function filter_plugin_action_links( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=indexreloaded-menu-main' ) . '">'
					. __( 'IndexReloaded', 'indexreloaded' ) . '</a>';
			return $links;
		}

		/**
		 * Deletes options cache
		 *
		 * @param string $pagename the pagename for which cache needs a purge.
		 */
		public function delete_option_cache( $pagename = '' ) {
			global $wpdb;
			if ( '' !== $pagename ) {
				$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT DISTINCT `option_name` as option_name
						FROM {$wpdb->options}
						WHERE option_name LIKE %s",
						'indexreloaded_cache_real-page-' . $pagename . '%'
					)
				);

			} else {
				$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT DISTINCT `option_name` as option_name
						FROM {$wpdb->options}
						WHERE option_name LIKE %s",
						'indexreloaded_cache_real-page-%'
					)
				);
			}

			if ( is_array( $rows ) ) {
				foreach ( $rows as $postrow ) {
					$option_name = trim( $postrow->option_name );
					delete_option( $option_name );
				}
			}
		}

		/**
		 * Deletes redis cache
		 *
		 * @param string $post_id id the pagename for which cache needs a purge.
		 * @param object $post the post.
		 * @param bool   $update if it is an update.
		 * @param object $post_before the post before the action.
		 */
		public function drop_redis_cache_post( $post_id, $post, $update, $post_before ) {  // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			// dropping indexreloaded-keys in objectcache when a post is saved.
			$post_type = get_post_type( $post_id );
			$pagename  = '';
			$siteurl   = site_url( '/', 'https' );
			if ( ( 'page' === $post_type ) || ( 'post' === $post_type ) ) {
				$pagename = rawurlencode( str_replace( $siteurl, '', get_page_link( $post_id ) ) );
			}

			if ( ! irld_str_starts_with( $pagename, '%2F' ) ) {
				$pagename = '%2F' . $pagename;
			}

			if ( true === $this->tracelog_caching ) {
				irld_trace_log( 'post_cache_deletes for indexreloaded_cache_(real-)page-' . $pagename . ' ???', 0 );
			}

			if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
				if ( '' !== $pagename ) {
					$cached_page_fallback = rawurlencode( rawurldecode( $pagename ) . '/' );
					wp_cache_delete( 'real-page-' . $pagename . '-', 'indexreloaded' );
					wp_cache_delete( 'page-' . $pagename . '-', 'indexreloaded' );
					wp_cache_delete( 'real-page-' . $pagename, 'indexreloaded' );
					wp_cache_delete( 'page-' . $pagename, 'indexreloaded' );
					wp_cache_delete( 'real-page-' . $cached_page_fallback . '-', 'indexreloaded' );
					wp_cache_delete( 'page-' . $cached_page_fallback . '-', 'indexreloaded' );
					wp_cache_delete( 'real-page-' . $cached_page_fallback, 'indexreloaded' );
					wp_cache_delete( 'page-' . $cached_page_fallback, 'indexreloaded' );
					if ( true === $this->tracelog_caching ) {
						irld_trace_log( '8 wp_cache_deletes for (real-)page-' . $pagename . '-', 0 );
					}
				} elseif ( ( 'page' === $post_type ) || ( 'post' === $post_type ) ) {
					if ( wp_cache_supports( 'flush_group' ) ) {
						wp_cache_flush_group( 'indexreloaded' );
						if ( true === $this->tracelog_caching ) {
							irld_trace_log( 'wp_cache_flush_group drop_redis_cache_post', 0 );
						}
					}
				}
			} else {
				$this->delete_option_cache( $pagename );
			}
		}

		/**
		 * Deletes redis cache on plugin activation
		 *
		 * @param string $plugin plugin activated.
		 * @param bool   $network_activation if it is an network activation.
		 */
		public function drop_redis_cache_plg( $plugin, $network_activation ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			// dropping indexreloaded-keys in objectcache when a plugin has been installed/activated.
			if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
				if ( wp_cache_supports( 'flush_group' ) ) {
					wp_cache_flush_group( 'indexreloaded' );
					if ( true === $this->tracelog_caching ) {
						irld_trace_log( 'wp_cache_flush_group drop_redis_cache_plg', 0 );
					}
				}
			} else {
				$this->delete_option_cache();
			}
		}

		/**
		 * Deletes redis cache after theme customize -> save
		 *
		 * @param object $manager handle to WP_Customize_Manager.
		 */
		public function irld_execute_on_customize_save_after_event( $manager ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			// You can write code here to be executed when this action occurs in WordPress. Use the parameters received in the function arguments & implement the required additional custom functionality according to your website requirements.
			// dropping indexreloaded-keys in objectcache when a plugin has been installed/activated.
			if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {
				if ( wp_cache_supports( 'flush_group' ) ) {
					wp_cache_flush_group( 'indexreloaded' );
					if ( true === $this->tracelog_caching ) {
						irld_trace_log( 'irld_execute_on_customize_save_after_event, wp_cache_flush_group drop_redis_cache_plg', 0 );
					}
				}
			} else {
				$this->delete_option_cache();
			}
		}
	}

}

new Indexreloadedroot();
