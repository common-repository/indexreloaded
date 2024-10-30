<?php
/**
 * This is the backends "IndexReloaded" admin screen
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );

if ( ! class_exists( 'Indexreloadedadminmenu' ) ) {

	/**
	 * Indexreloadedadminmenu
	 */
	class Indexreloadedadminmenu {

		/**
		 * Setup class.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
			$page = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( 'indexreloaded-menu' === $page ) {
				add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
			}

			if ( 'indexreloaded-menu' === substr( $page, 0, 18 ) ) {
				add_action( 'in_admin_footer', array( &$this, 'footer' ) );
			}

			add_action( 'admin_print_scripts', array( $this, 'hide_admin_notices' ) );
			add_filter( 'plugin_action_links_' . IRLD_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		}

		/**
		 * Fires when enqueuing scripts for all admin pages.
		 */
		public function action_admin_enqueue_scripts() {
			if ( file_exists( IRLD_PATH . '/assets/css/indexreloaded-admin.min.css' ) ) {
				$front_styles = 'indexreloaded-admin.min.css';
			} else {
				$front_styles = 'indexreloaded-admin.css';
			}

			wp_enqueue_style(
				'indexreloaded-adminstyles',
				plugins_url( $front_styles, "indexreloaded/assets/css/$front_styles" ),
				array(),
				'1.2.1'
			);

			$curip = '';
			if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
				$curip = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
			}

			$langreq    = 'en';
			$siteurl    = site_url( '/', 'https' );
			$actionwp   = 'serversideexcludes';
			$getp       = '&action=' . $actionwp . '&remoteadr=' . $curip . '&remotesite=' . $siteurl . '&lang=' . $langreq;
			$urltofetch = 'https://www.toctoc.ch/wp-admin/admin-ajax.php';
			$gtstrng    = add_query_arg( '_wpspecnonce', irld_get_special_nonce( 'serversideexcludes' ), $urltofetch ) . $getp;
			$gtstrng    = str_replace( 'https://www.toctoc.ch/wp-admin/admin-ajax.php', '', $gtstrng );

			$actionwp   = 'irld_upd_excludelist';
			$getprem    = '&action=' . $actionwp;
			$urltofetch = $siteurl . 'wp-admin/admin-ajax.php';
			$gtstrngopt = add_query_arg( '_wpnonce', wp_create_nonce( 'irld_upd_excludelist' ), $urltofetch ) . $getprem;
			$gtstrngopt = str_replace( $siteurl . 'wp-admin/admin-ajax.php', '', $gtstrngopt );

			$actionwp   = 'irld_del_files';
			$getprem    = '&action=' . $actionwp;
			$urltofetch = $siteurl . 'wp-admin/admin-ajax.php';
			$gtstrngdel = add_query_arg( '_wpnonce', wp_create_nonce( 'irld_del_files' ), $urltofetch ) . $getprem;
			$gtstrngdel = str_replace( $siteurl . 'wp-admin/admin-ajax.php', '', $gtstrngdel );

			$actionwp     = 'irld_del_cache';
			$getprem      = '&action=' . $actionwp;
			$urltofetch   = $siteurl . 'wp-admin/admin-ajax.php';
			$gtstrngcache = add_query_arg( '_wpnonce', wp_create_nonce( 'irld_del_cache' ), $urltofetch ) . $getprem;
			$gtstrngcache = str_replace( $siteurl . 'wp-admin/admin-ajax.php', '', $gtstrngcache );

			$actionwp         = 'irld_del_cacheitem';
			$getprem          = '&action=' . $actionwp;
			$urltofetch       = $siteurl . 'wp-admin/admin-ajax.php';
			$gtstrngcacheitem = add_query_arg( '_wpnonce', wp_create_nonce( 'irld_del_cacheitem' ), $urltofetch ) . $getprem;
			$gtstrngcacheitem = str_replace( $siteurl . 'wp-admin/admin-ajax.php', '', $gtstrngcacheitem );

			$inlinejs = 'var gtstrngexcl = "' . $gtstrng . '";var locsite = "' . $siteurl . '";var gtstrngoptexcl = "' .
							$gtstrngopt . '";var gtstrngdelexcl = "' . $gtstrngdel . '";var gtstrngdelcache = "' . $gtstrngcache . '";var gtstrngdelcacheitem = "' . $gtstrngcacheitem . '";';
			wp_register_script(
				'indexreloaded-inl-admin-js',
				'',
				array( 'jquery' ),
				'1.2.1',
				array( 'in_footer' => true )
			);
			wp_enqueue_script( 'indexreloaded-inl-admin-js' );
			wp_add_inline_script( 'indexreloaded-inl-admin-js', $inlinejs );

			if ( file_exists( IRLD_PATH . '/assets/js/indexreloaded-admin.min.js' ) ) {
				$admscrt = 'indexreloaded-admin.min.js';
			} else {
				$admscrt = 'indexreloaded-admin.js';
			}

			wp_enqueue_script(
				'indexreloaded-adminscript',
				plugins_url( $admscrt, "indexreloaded/assets/js/$admscrt" ),
				array( 'jquery' ),
				'1.2.1',
				array( 'in_footer' => true )
			);
		}

		/**
		 * Fires as the overview screen is being initialized.
		 */
		public function action_admin_menu() {
			add_menu_page(
				'IndexReloaded',
				'IndexReloaded',
				'manage_options',
				'indexreloaded-menu',
				array( &$this, 'indexreloaded_page_cb' ),
				plugins_url( 'assets/sprites/indexreloaded-icon.png', IRLD_PATH . '/indexreloaded.php' )
			);

			add_submenu_page(
				'indexreloaded-menu',
				__( 'IndexReloaded Overview', 'indexreloaded' ),
				__( 'Overview', 'indexreloaded' ),
				'manage_options',
				'indexreloaded-menu',
				array( &$this, 'indexreloaded_page_cb' )
			);

			do_action( 'indexreloaded_admin_menu' );

			$baseincludes = trim( get_option( 'indexreloaded_includesarr' ) );
			if ( '' === $baseincludes ) {
				$baseincludes = '*';
			}

			$baseexcludes = trim( get_option( 'indexreloaded_excludesarr' ) );
			if ( '' === $baseexcludes ) {
				$baseexcludes = '*';
			}

			if ( ( '*' === $baseexcludes ) && ( '*' === $baseincludes ) ) {
				// Typically after initial setup.
				$info_array = array();
				$info_array = irld_excludes_check();
				if ( isset( $info_array['recommendedexcludes'] ) ) {
					if ( '' !== trim( $info_array['recommendedexcludes'] ) ) {
						update_option( 'indexreloaded_excludesarr', $info_array['recommendedexcludes'] );
						$baseexcludes = $info_array['recommendedexcludes'];
					}
				}
			}
		}

		/**
		 * Content of admin overview-screen.
		 */
		public function indexreloaded_page_cb() {
			global $wpdb;
			?>
			<h1><?php echo 'IndexReloaded'; ?></h1>
			<div class="card">
				<p>
				<?php
				esc_html_e(
					'This plugin implements specific transformations to CSS and JS in the final output of the webpage.',
					'indexreloaded'
				);
				echo '<br /><img src="/wp-content/plugins/indexreloaded/assets/sprites/indexreloaded2023.png" class="irld-hero" alt="How IndexReloaaded works" loading="lazy" 
				decoding="asyc" fetchpriority="low" width="1172" height="303"></p><h3>';
				esc_html_e(
					'Required skills:',
					'indexreloaded'
				);
				echo '</h3><p>';
				esc_html_e(
					'Access, read and understand HTML of your website.',
					'indexreloaded'
				);
				echo '</p><h3>';
				esc_html_e(
					'Challenge:',
					'indexreloaded'
				);
				echo '</h3><p class="irld-fade" id="visible-exp-block-1">';
				esc_html_e(
					'Identify CSS and JS that needs to stay untouched by IndexReloaded.',
					'indexreloaded'
				);
				echo '</p><a role="button" class="idrd-show-more button irld-button" id="btn-show-more-exp-block-1">';
				esc_html_e( 'Show more', 'indexreloaded' );
				echo '</a><br />';
				echo '</h3><p class="irld-hide irld-hidden-part" id="hidden-exp-block-1">';
				esc_html_e(
					'IndexReloaded initially only excludes JS jquery, all inline-JS containing nonces and externally hosted CSS and JS from processing.',
					'indexreloaded'
				);
				echo ' ';
				esc_html_e(
					'The webpage will most probably encounter problems like elements not showing up and JS errors.',
					'indexreloaded'
				);
				echo ' ';
				esc_html_e(
					'Now you must inspect your unmodified page source and identify the JS elements that cause the problem, if touched by IndexReloaded.',
					'indexreloaded'
				);
				echo ' ';
				esc_html_e(
					'A part of the filepath or a part of the Inline JS content serves to identify - In "Settings -> CSS and JS Processing" these identifiers go to "Exclude List", separated by commas.',
					'indexreloaded'
				);
				echo '<br />';
				esc_html_e(
					'Below in tab "Exclude list" you can find a recommendation for the Exclude List based on our own experiences with plugins you use on this site.',
					'indexreloaded'
				);
				echo '</p><a role="button" class="idrd-show-less irld-hide button irld-button" id="btn-show-less-exp-block-1">';
				esc_html_e( 'Show less', 'indexreloaded' );
				echo '</a><br />';
				?>
			</div>
			<div class="card">
				<h2>
				<?php
				esc_html_e( 'Overview', 'indexreloaded' );
				echo '</h2>';
				$lic_valid = false;
				if ( 'on' === trim( get_option( 'indexreloaded_active' ) ) ) {
					echo '<p>';
					esc_html_e( 'Plugin is active', 'indexreloaded' );
					$valid_to = trim( get_option( 'indexreloaded_APIkey_lastvalidate' ) );
					if ( '' !== $valid_to ) {
						echo ', ';
						esc_html_e( 'licence is valid until', 'indexreloaded' );
						$lic_valid = true;
						echo ' ';
						echo esc_html( gmdate( 'd.m.Y', intval( $valid_to ) ) );
					}

					echo '.</p>';
				} else {
					echo '<p>';
					esc_html_e( 'Plugin is not active', 'indexreloaded' );
					echo '.</p>';
				}

				global $wpdb;
				echo '<div class="irld_tab_header">';
				echo '	<span id="tabCSSJSFiles" class="irld_tab_head active">';
				$folding_active = trim( get_option( 'indexreloaded_generateCSSbelowTheFold' ) );
				if ( ( 'on' === $folding_active ) && ( true === $lic_valid ) ) {
					echo '	';
					esc_html_e( 'CSS and JS files', 'indexreloaded' );
					echo '/';
					esc_html_e( 'Cache', 'indexreloaded' );
				} else {
					echo '	';
					esc_html_e( 'CSS and JS files', 'indexreloaded' );
				}

				echo '	</span>';
				echo '	<span id="tabLicence" class="irld_tab_head">';
				esc_html_e( 'Licence key', 'indexreloaded' );
				echo '	</span>';
				echo '	<span id="tabExcludes" class="irld_tab_head">';
				esc_html_e( 'Exclude list', 'indexreloaded' );
				echo '	</span>';
				echo '</div>';
				echo '<div class="irld_tab_content active" id="tabContentCSSJSFiles">';
				if ( ( 'on' === $folding_active ) && ( true === $lic_valid ) ) {
					echo '<div class="ttirl_box"><div class="ttirl_doublebox" id="ttirl_doublebox_one">';
				} else {
					echo '<div class="ttirl_box"><div class="ttirl_singlebox">';
				}

				$cssjsoptionpath = get_option( 'indexreloaded_pathcssjs' );
				if ( '' === trim( $cssjsoptionpath ) ) {
					$cssjsoptionpath = 'wp-content/uploads/cssjs';
				}

				$cssjsoptiondaysold = get_option( 'indexreloaded_cssjs_keeptime' );
				if ( '' === trim( $cssjsoptiondaysold ) ) {
					$cssjsoptiondaysold = 14;
				}
				$cssjsoptionexternalsdaysold = get_option( 'indexreloaded_externally_hosted_keeptime' );
				if ( '' === trim( $cssjsoptionexternalsdaysold ) ) {
					$cssjsoptionexternalsdaysold = 7;
				}

				$cssjsoptionsecondssold               = intval( $cssjsoptiondaysold ) * 24 * 60 * 60;
				$cssjsoptionexternalsdays_seconds_old = intval( $cssjsoptionexternalsdaysold ) * 24 * 60 * 60;
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
					echo '<h3>';
					esc_html_e( 'Files in', 'indexreloaded' );
					echo ' "';
					echo esc_html( $cssjsoptionpath );
					echo '"</h3><p>';
					// check if diectories exist, if not mkdir them.
					if ( ! is_dir( $cssjspath ) ) {
						mkdir( $cssjspath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
						mkdir( $cssjspath . '/js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
						mkdir( $cssjspath . '/css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
						mkdir( $cssjspath . '/external' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
					}
					if ( ! is_dir( $cssjspath . '/external' ) ) {
						mkdir( $cssjspath . '/external' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
					}

					// Set the current working directory.
					$directory = $cssjspath . '/css/';
					// Returns array of files.
					$files1     = scandir( $directory );
					$file_dates = array();
					$file_sizes = array();
					if ( '' !== $cssjsoptiondaysold ) {
						foreach ( $files1 as $file ) {
							if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
								if ( file_exists( $directory . $file ) ) {
									$current_modified = filectime( $directory . $file );
									$filesize         = filesize( $directory . $file );
									$file_sizes[]     = $filesize;
									if ( 0 !== intval( $cssjsoptionsecondssold ) ) {
										if ( ( $current_modified + $cssjsoptionsecondssold ) < time() ) {
											$file_dates[] = $current_modified;
											unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink											
										}
									}
								}
							}
						}
					}

					$countpointerfiles = 0;
					foreach ( $file_sizes as $file_size ) {
						if ( $file_size < 70 ) {
							++$countpointerfiles;
						}
					}
					$should_purge_page_cache = false;
					echo '</p><p id="messagefiles">';
					$num_oldfiles = count( $file_dates );
					// Count number of files and store them to variable.
					$num_files = count( $files1 ) - 2;
					$brnext    = '';
					if ( $num_files > 0 ) {
						echo esc_html( ( $num_files - $countpointerfiles ) . ' ' . __( 'CSS-files', 'indexreloaded' ) . ', ' . $countpointerfiles . ' ' . __( 'pointer CSS-files found', 'indexreloaded' ) );
						echo '<br />';
						echo esc_html( __( 'deleted old files', 'indexreloaded' ) . ': ' . $num_oldfiles );
						$brnext                  = '<br>';
						$should_purge_page_cache = true;
					}

					$total_files = $num_files;
					$directory   = $cssjspath . '/js/';
					// Returns array of files.
					$files1     = scandir( $directory );
					$file_dates = array();
					if ( '' !== $cssjsoptiondaysold ) {
						foreach ( $files1 as $file ) {
							if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
								if ( file_exists( $directory . $file ) ) {
									$current_modified = filectime( $directory . $file );
									if ( 0 !== intval( $cssjsoptionsecondssold ) ) {
										if ( ( $current_modified + $cssjsoptionsecondssold ) < time() ) {
											$file_dates[] = $current_modified;
											unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
										}
									}
								}
							}
						}
					}

					$num_oldfiles = count( $file_dates );
					// Count number of files and store them to variable.
					$num_files = count( $files1 ) - 2;
					if ( $num_files > 0 ) {
						if ( '<br>' === $brnext ) {
							echo '<br />';
						}

						echo esc_html( $num_files . ' ' . __( 'JS-files found, deleted old files', 'indexreloaded' ) . ': ' . $num_oldfiles );
						$should_purge_page_cache = true;
					}

					$total_files += $num_files;

					$directory = $cssjspath . '/external/';
					// Returns array of files.
					$files1     = scandir( $directory );
					$file_dates = array();
					if ( '' !== $cssjsoptionexternalsdays_seconds_old ) {
						foreach ( $files1 as $file ) {
							if ( ( '.' !== $file ) && ( '..' !== $file ) && ( 'robots.txt' !== $file ) && ( '.htaccess' !== $file ) ) {
								if ( file_exists( $directory . $file ) ) {
									$current_modified = filectime( $directory . $file );
									if ( 0 !== intval( $cssjsoptionexternalsdays_seconds_old ) ) {
										if ( ( $current_modified + $cssjsoptionexternalsdays_seconds_old ) < time() ) {
											$file_dates[] = $current_modified;
											unlink( $directory . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
										}
									}
								}
							}
						}
					}

					$num_oldfiles = count( $file_dates );
					// Count number of files and store them to variable.
					$num_files = count( $files1 ) - 2;
					if ( $num_files > 0 ) {
						if ( '<br>' === $brnext ) {
							echo '<br />';
						}

						echo esc_html( $num_files . ' ' . __( 'Files from external souces hosted locally, deleted old files', 'indexreloaded' ) . ': ' . $num_oldfiles );
						$should_purge_page_cache = true;
					}

					$total_files += $num_files;
					echo '</p><p>';
					if ( 0 !== intval( $cssjsoptiondaysold ) ) {
						echo '<br>' . esc_html( __( 'JS and CSS Files older than', 'indexreloaded' ) ) . ' ' . esc_html( $cssjsoptiondaysold ) . ' ' . esc_html( __( 'days are removed when this page here opens', 'indexreloaded' ) );
					}
					if ( 0 !== count( $file_dates ) ) {
						if ( 0 !== intval( $cssjsoptionexternalsdays_seconds_old ) ) {
							echo '<br>' . esc_html( __( 'Locally hosted files from external sources older than', 'indexreloaded' ) ) . ' ' . esc_html( $cssjsoptionexternalsdaysold ) . ' ' . esc_html( __( 'days are removed when this page here opens', 'indexreloaded' ) );
						}
					}
					if ( $total_files > 0 ) {
						$button       = '<br /><a role="button" id="btn-del-files" 
								class="irld-button button">' . __( 'Delete files', 'indexreloaded' ) . '</a><br />' .
							'<span id="btn-del-files-text" class="irld-inline-block">' . __( 'Delete all files in folder ', 'indexreloaded' ) . '&quot;' . $cssjsoptionpath . '&quot;</span>';
						$allowed_html = array(
							'br'   => array(),
							'a'    => array(
								'role'  => array(),
								'id'    => array(),
								'class' => array(),
							),
							'span' => array(
								'id'    => array(),
								'class' => array(),
							),
						);
						echo wp_kses( $button, $allowed_html );

						$active_page_cache = irld_page_cache_check();
						if ( '' !== $active_page_cache ) {
							echo '<br>' . esc_html( __( 'Found probably active page cache', 'indexreloaded' ) ) . ' (' . esc_html( $active_page_cache ) . ')<br>' . esc_html( __( 'This cache will get purged after deleting IndexReloaded-files', 'indexreloaded' ) );
						}
					}
				}

				if ( ( 'on' === $folding_active ) && ( true === $lic_valid ) ) {
					echo '</div><div class="ttirl_doublebox" id="ttirl_doublebox_two">';
					echo '<h3>';
					esc_html_e( 'Cache usage', 'indexreloaded' );
					echo '</h3><p>';
					if ( ( defined( 'WP_REDIS_CLIENT' ) ) && ( is_plugin_active( 'redis-cache/redis-cache.php' ) ) ) {

						echo '</h3><p>';
						if ( WP_REDIS_CLIENT === 'predis' ) {
							$client        = new Predis\Client(
								array(
									'scheme'   => 'tcp',
									'host'     => '127.0.0.1',
									'port'     => 6379,
									'database' => WP_REDIS_DATABASE,
									'password' => WP_REDIS_PASSWORD,
								)
							);
							$table_prefixx = str_replace( '_', '', $wpdb->prefix );
							$keys          = $client->keys( WP_CACHE_KEY_SALT . $table_prefixx . ':indexreloaded:page-*' );
							$countkeys     = count( $keys );
							$len_keys      = 0;
							$pages         = array();
							sort( $keys );
							foreach ( $keys as $key ) {
								$len_keys += strlen( $client->get( $key ) );
								$pagename  = rawurldecode(
									str_replace( WP_CACHE_KEY_SALT . $table_prefixx . ':indexreloaded:page-', '', $key )
								);
								if ( irld_str_ends_with( $pagename, '-' ) ) {
									$pagename = substr( $pagename, 0, ( strlen( $pagename ) - 2 ) );
								}
								$pages[] = $pagename;
							}
							esc_html_e( 'Redis is active', 'indexreloaded' );
							echo '. ';
							esc_html_e( 'Predis client for redis is active', 'indexreloaded' );
							echo '.<br />';
							$info = array();
							try {
								$info = $client->info();
							} catch ( Predis\Response\ServerException $e ) {
								echo esc_html( __( 'An error occurred', 'indexreloaded' ) . ": {$e->getMessage()}" );
							}

							esc_html_e( 'Total number of keys present in database', 'indexreloaded' );
							echo esc_html( ' ' . WP_REDIS_DATABASE . ': ' . $info['Keyspace'][ 'db' . WP_REDIS_DATABASE ]['keys'] . ' ' . __( 'keys', 'indexreloaded' ) );
							echo '.</p><p id="messagecache">';
							esc_html_e( 'Cache used for models', 'indexreloaded' );
							echo esc_html( ': ' . $countkeys . ' ' . __( 'keys', 'indexreloaded' ) . '. ' . __( 'Size', 'indexreloaded' ) . ': ' . $len_keys . ' ' . __( 'Bytes', 'indexreloaded' ) );
							echo '<br />';

							$keys          = $client->keys( WP_CACHE_KEY_SALT . $table_prefixx . ':indexreloaded:real-*' );
							$countkeys_p   = count( $keys );
							$len_keys      = 0;
							$realpages     = array();
							$realpagenames = array();
							foreach ( $keys as $key ) {
								$len_keys += strlen( $client->get( $key ) );
								$rpagename = rawurldecode(
									str_replace( WP_CACHE_KEY_SALT . $table_prefixx . ':indexreloaded:real-page-', '', $key )
								);
								if ( irld_str_ends_with( $rpagename, '-' ) ) {
									$rpagename = substr( $rpagename, 0, ( strlen( $rpagename ) - 2 ) );
								}
								if ( '' === $rpagename ) {
									$rpagename = '/';
								}
								$realpages[]     = '<span id="cache' . str_replace( '%', '_pcnt_', rawurlencode( $rpagename ) ) . '"><span>' . $rpagename . '</span><span title="' .
								__( 'Remove cache entry', 'indexreloaded' ) . '" class="ird_del_cache dashicons dashicons-dismiss" id="delcache-' . rawurlencode( $rpagename ) . '"></span></span>';
								$realpagenames[] = $rpagename;
							}

							$wrkpages = array();
							foreach ( $pages as $page_cached ) {
								$page_cached_from_realpage = false;
								$wrkpage                   = $page_cached;
								if ( '' === $page_cached ) {
									$wrkpage = '/';
								}
								foreach ( $realpagenames as $realpage_cached ) {
									if ( $wrkpage === $realpage_cached ) {
										$page_cached_from_realpage = true;
										break;
									}
								}
								if ( false === $page_cached_from_realpage ) {
									$wrkpages[] = $wrkpage;
								}
							}
							sort( $wrkpages );
							$str_pages = implode( '<br>', $wrkpages );
							$cnt_pages = count( $wrkpages );
							sort( $realpages );
							$str_real_pages = implode( '<br>', $realpages );
							$cnt_real_pages = count( $realpages );

							esc_html_e( 'Cache used as HTML for models', 'indexreloaded' );
							echo esc_html( ': ' . $countkeys_p . ' ' . __( 'keys', 'indexreloaded' ) . '. ' . __( 'Size', 'indexreloaded' ) . ': ' . $len_keys . ' ' . __( 'Bytes', 'indexreloaded' ) );
							echo '<br />';
							$lastcachechange_ts_arr = explode( ' ', wp_cache_get_last_changed( 'indexreloaded' ) );
							$lastcachechange_ts     = $lastcachechange_ts_arr[1];
							esc_html_e( 'Last change of indexreloaded cache group', 'indexreloaded' );
							echo ': ' . esc_html( gmdate( 'd.m.Y H:i:s', intval( $lastcachechange_ts ) ) );

							echo '.</p><p id="revealcachearea">';
							if ( ( $countkeys_p + $countkeys ) > 0 ) {
								$button_cache = '<br /><a role="button" id="btn-rev-cache"
								class="irld-button button">' . __( 'Reveal cached pages', 'indexreloaded' ) . '</a><br />' .
								'<p id="btn-rev-cache-text" class="irld-display-none"><b>' . __( 'Cached pages used by Indexreloaded', 'indexreloaded' ) . '<br />' .
								$cnt_real_pages . ' ' . __( 'pages with CCSS', 'indexreloaded' ) . '</b><br />' .
								$str_real_pages . '<br />';
								if ( $cnt_pages > 0 ) {
									$button_cache .= '<b>' . $cnt_pages . ' ' . __( 'Pages prepared for CCSS', 'indexreloaded' ) . '</b><br />' .
									$str_pages . '<br />';
								}

								$button_cache .= '</p>';
								$button_cache  = str_replace( '<br><br>', '<br>/<br>', $button_cache );
								$allowed_html  = array(
									'br'   => array(),
									'b'    => array(),
									'a'    => array(
										'role'  => array(),
										'id'    => array(),
										'class' => array(),
									),
									'p'    => array(
										'id'    => array(),
										'class' => array(),
									),
									'span' => array(
										'id'    => array(),
										'class' => array(),
										'title' => array(),
									),
								);
								echo wp_kses( $button_cache, $allowed_html );
								$button       = '<br /><a role="button" id="btn-del-cache"
								class="irld-button button">' . __( 'Delete cache', 'indexreloaded' ) . '</a><br />' .
															'<span id="btn-del-cache-text" class="irld-inline-block">' . __( 'Delete cache used by Indexreloaded', 'indexreloaded' ) . '</span>';
								$allowed_html = array(
									'br'   => array(),
									'a'    => array(
										'role'  => array(),
										'id'    => array(),
										'class' => array(),
									),
									'span' => array(
										'id'    => array(),
										'class' => array(),
									),
								);
								echo wp_kses( $button, $allowed_html );
								echo '</p><p>';
							}
						} else {
							esc_html_e( 'Redis is active, but as you use', 'indexreloaded' );
							echo esc_html( ' ' . WP_REDIS_CLIENT . ' ' );
							esc_html_e( 'we cannot gather stats on cache usage', 'indexreloaded' );
							echo '. ';
							esc_html_e( 'Please use the predis client for redis', 'indexreloaded' );

						}
					} else {
						esc_html_e( 'Redis is not active', 'indexreloaded' );
						echo '. ';
						esc_html_e( 'Database is used for caching', 'indexreloaded' );
						echo '.</p><p id="messagecache">';

						$sql = 'SELECT DISTINCT `' . $wpdb->prefix . 'options`.`option_name` as option_name, `' . $wpdb->prefix . 'options`.`option_value` as option_value ' .
									'FROM `' . $wpdb->prefix . 'options` ' .
									'WHERE `' . $wpdb->prefix . 'options`.`option_name` LIKE "indexreloaded_cache_real-page-%"';

						$rows         = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->prepare(
								"SELECT DISTINCT `option_name` as option_name, `option_value` as option_value 
											FROM {$wpdb->options}
											WHERE option_name LIKE %s",
								'indexreloaded_cache_real-page-%'
							)
						);
						$len_keys     = 0;
						$countkeys_db = 0;
						if ( is_array( $rows ) ) {
							$countkeys_db = count( $rows );
							foreach ( $rows as $postrow ) {
								$len_keys += strlen( trim( $postrow->option_value ) );
							}
						}
						esc_html_e( 'Cache used for models', 'indexreloaded' );
						echo esc_html( ': ' . $countkeys_db . ' ' . __( 'keys', 'indexreloaded' ) . '. ' . __( 'Size', 'indexreloaded' ) . ': ' . $len_keys . ' ' . __( 'Bytes', 'indexreloaded' ) );
						echo '.</p><p>';
						if ( ( $countkeys_db ) > 0 ) {
							$button       = '<br /><a role="button" id="btn-del-cache"
								class="irld-button button">' . __( 'Delete cache', 'indexreloaded' ) . '</a><br />' .
														'<span id="btn-del-cache-text" class="irld-inline-block">' . __( 'Delete cache used by Indexreloaded', 'indexreloaded' ) . '</span>';
							$allowed_html = array(
								'br'   => array(),
								'a'    => array(
									'role'  => array(),
									'id'    => array(),
									'class' => array(),
								),
								'span' => array(
									'id'    => array(),
									'class' => array(),
								),
							);
							echo wp_kses( $button, $allowed_html );

						}
					}

					echo '</p>';
				}
				echo '</div></div>';

				?>
				</p>
				<?php
					$lic        = trim( get_option( 'indexreloaded_APIkey' ) );
					$licdate    = trim( get_option( 'indexreloaded_APIkey_lastvalidate' ) );
					$licvdtd    = trim( get_option( 'indexreloaded_APIkey_vdtd' ) );
					$licact     = trim( get_option( 'indexreloaded_LicActive' ) );
					$licdateexp = false;
					echo '</div><div class="irld_tab_content" id="tabContentLicence"><h3>';
					esc_html_e( 'Licence key', 'indexreloaded' );
					echo '</h3><p>';
				if ( '' !== $licdate ) {
					$licvaldiff = intval( $licdate ) - time();
					if ( $licvaldiff < 0 ) {
						$licdateexp = true;
					}
				} else {
					$licdateexp = true;
				}

				$licmsg = '';
				if ( '' !== $lic ) {
					if ( $licvdtd !== $lic ) {
						update_option( 'indexreloaded_LicActive', '' );
						$licmsg = __( 'Licence key not validated.', 'indexreloaded' );
					} elseif ( 'on' === $licact ) {
						if ( true === $licdateexp ) {
							$licmsg = __( 'Licence key did expire', 'indexreloaded' );
							update_option( 'indexreloaded_LicActive', '' );
						} else {
							$licmsg = __( 'Licence key', 'indexreloaded' ) . ' "' . $lic . '" ' . __( 'is valid.', 'indexreloaded' );
						}
					} else {
						$licmsg = __( 'This licence key is not validated.', 'indexreloaded' );
					}

					if ( ( 20 !== strlen( $lic ) ) && ( 19 !== strlen( $lic ) ) ) {
						$licmsg = __( 'licence key has no valid format.', 'indexreloaded' );
					}
				} else {
					$licmsg = __( 'No licence key present.', 'indexreloaded' );
				}

				echo esc_html( $licmsg );

				?>
				</p>
						<?php

						$displaybutton = ' irld-inline-block';
						if ( 'on' !== get_option( 'indexreloaded_LicActive' ) ) {
							$displaybutton = ' irld-display-none';
						}

						$fromserver_opts = array();
						if ( '' !== get_option( 'indexreloaded_serversideexcludes' ) ) {
							$fromserver_opts = json_decode( get_option( 'indexreloaded_serversideexcludes' ) );
						}

						$time_diff_message = '';

						if ( '' !== get_option( 'indexreloaded_serversideexcludes_lastrefreshed' ) ) {
							$time_diff       = time() - intval( get_option( 'indexreloaded_serversideexcludes_lastrefreshed' ) );
							$time_diff_hours = round( $time_diff / ( 60 * 60 ), 0 );
							if ( $time_diff_hours < 24 ) {
								if ( 0 === $time_diff_hours ) {
									$time_diff_message = __( 'You received the latest information on excludes less than an hour ago', 'indexreloaded' ) . '.';
								} elseif ( 1 === $time_diff_hours ) {
									$time_diff_message = __( 'You received the latest information on excludes about an hour ago', 'indexreloaded' ) . '.';
								} else {
									$time_diff_message = __( 'You received the latest information on excludes about', 'indexreloaded' ) . ' ' .
														$time_diff_hours . ' ' . __( 'hours ago', 'indexreloaded' ) . '.';
								}

								$time_diff_message .= '<br />' . __( 'Please wait 24 hours until next Refresh', 'indexreloaded' );

							}
						}

						$btn_id = 'indexreloaded-exclude-refresh';
						if ( '' === $time_diff_message ) {
							$button = '<br /><br /><a role="button" id="btn-' .
										$btn_id . '" class="irld-button' . $displaybutton . ' button button-primary">' . __( 'Refresh exclude info', 'indexreloaded' ) . '</a><br />' .
										'<span class="' . trim( $displaybutton ) . '">' . __( 'Get and use the latest information on excludes from toctoc.ch', 'indexreloaded' ) . '</span>' .
										'<span id="irld-refreshcontrol" class="irld-display-none">0</span>';
						} else {
							$button = '<br /><br /><span>' . $time_diff_message . '</span>';
						}

						$info_array = irld_excludes_check();
						if ( '' !== $info_array['plugtext'] ) {
							echo '</div><div class="irld_tab_content" id="tabContentExcludes"><h3>';

							esc_html_e( 'Recommended excluding Identifiers for your active plugins', 'indexreloaded' );

							echo '</h3><p>';

							esc_html_e( 'Exclude list', 'indexreloaded' );
							echo ': <br /><code class="irld-code">';
							echo esc_html( $info_array['recommendedexcludes'] );
							echo '</code><br /><br />';
							echo '<span class="irld-headdetails" id="irld-headdetails">';
							esc_html_e( 'Details', 'indexreloaded' );
							echo '<br /></span></p>';
							echo '<p class="irld-details" id="irld-details">';
							$allowed_html = array(
								'br'   => array(),
								'b'    => array(),
								'a'    => array(
									'role'  => array(),
									'id'    => array(),
									'class' => array(),
								),
								'span' => array(
									'id'    => array(),
									'class' => array(),
								),
								'p'    => array(
									'id'    => array(),
									'class' => array(),
								),
							);
							echo wp_kses( $info_array['plugtext'], $allowed_html );
							echo wp_kses( $info_array['elemret'] . $info_array['themeret'] . $info_array['allactivatedplugins'], $allowed_html );
							$allowed_html = array(
								'br'   => array(),
								'a'    => array(
									'role'  => array(),
									'id'    => array(),
									'class' => array(),
								),
								'span' => array(
									'id'    => array(),
									'class' => array(),
								),
							);
							echo wp_kses( $button, $allowed_html );
							echo '</p></div>';
						} else {
							echo '</div><div class="irld_tab_content" id="tabContentExcludes"><h3>';
							echo esc_html_e( 'Recommended excluding Identifiers for your active plugins', 'indexreloaded' );
							echo '</h3><p>';
							esc_html_e( 'Exclude list', 'indexreloaded' );
							echo ': <br /><code class="irld-code">';
							esc_html_e( 'no excludes detected', 'indexreloaded' );
							echo '</code></p>';
							echo '</div>';
						}
						?>
				<div><a class="irld-button irld-button-showsettings button" href="/wp-admin/admin.php?page=indexreloaded-menu-settings")>
					<?php
					esc_html_e( 'Show settings', 'indexreloaded' );
					?>
				</a></div>
			</div>
			<div style="clear: left;"></div>
				<?php
		}

		/**
		 * Fires for  customization of the admin footer.
		 */
		public function footer() {
			?>
			<div class="card indexreloaded-footer-message">
				<p>
				<?php
					esc_html_e( 'Found', 'indexreloaded' );
					echo ' <strong>IndexReloaded</strong> ';
					esc_html_e( 'useful? Want to help the project? ', 'indexreloaded' );
					echo ' ';
					esc_html_e( 'Please leave a', 'indexreloaded' );
					echo ' <a href="https://wordpress.org/support/view/plugin-reviews/indexreloaded?filter=5#postform">★★★★★</a> ';
					esc_html_e( 'rating on WordPress.org!', 'indexreloaded' );

				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Hide all the unrelated notices from plugin page.
		 *
		 * @return void
		 */
		public function hide_admin_notices() {
			// Bail if we're not on a IndexReloaded screen.
			if ( empty( $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			} elseif ( ! preg_match( '/indexreloaded/', sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			global $wp_filter;
			$notices_type = array(
				'user_admin_notices',
				'admin_notices',
				'all_admin_notices',
			);

			foreach ( $notices_type as $type ) {
				if ( empty( $wp_filter[ $type ]->callbacks ) || ! is_array( $wp_filter[ $type ]->callbacks ) ) {
					continue;
				}

				foreach ( $wp_filter[ $type ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						if ( is_object( $arr['function'] ) && $arr['function'] instanceof \Closure ) {
							unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
							continue;
						}

						$class = ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ? strtolower( get_class( $arr['function'][0] ) ) : '';
						if ( ! empty( $class ) && preg_match( '/^(?:irld)/', $class ) ) {
							continue;
						}

						if ( ! empty( $name ) && ! preg_match( '/^(?:irld)/', $name ) ) {
							unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}

		/**
		 * Modify plugin action links on plugin listing page.
		 *
		 * @param array $links Existing links.
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$links[] = '<a href="https://www.toctoc.ch/indexreloaded#support/" target="_blank">' . esc_html__( 'Support', 'indexreloaded' ) . '</a>';
			$links[] = '<a href="' . get_admin_url( null, 'admin.php?page=indexreloaded-menu-settings' ) . '">' . esc_html__( 'Settings', 'indexreloaded' ) . '</a>';
			return array_reverse( $links );
		}
	}

	new Indexreloadedadminmenu();
}

