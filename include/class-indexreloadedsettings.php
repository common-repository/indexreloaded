<?php
/**
 * This is the backends "IndexReloaded" settings screen
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );

if ( ! class_exists( 'Indexreloadedsettings' ) ) {

	/**
	 * Indexreloadedsettings
	 */
	class Indexreloadedsettings {


		/**
		 * Setup class.
		 */
		public function __construct() {
			add_action( 'indexreloaded_admin_menu', array( &$this, 'action_admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
		}

		/**
		 * Fires as the settings screen is being initialized.
		 */
		public function action_admin_init() {
			$lic        = trim( get_option( 'indexreloaded_APIkey' ) );
			$licdate    = trim( get_option( 'indexreloaded_APIkey_lastvalidate' ) );
			$licvdtd    = trim( get_option( 'indexreloaded_APIkey_vdtd' ) );
			$licact     = trim( get_option( 'indexreloaded_LicActive' ) );
			$licdateexp = false;
			if ( '' !== $licdate ) {
				$licvaldiff = intval( $licdate ) - time();
				if ( $licvaldiff < 0 ) {
					$licdateexp = true;
				}
			} else {
				$licdateexp = true;
			}

			$enableopt = 'disabled';
			if ( 'on' === $licact ) {
				$enableopt = 'enabled';
			}

			$licmsg = '';
			if ( '' !== $lic ) {
				if ( $lic !== $licvdtd ) {
					update_option( 'indexreloaded_LicActive', '' );
					$licmsg = __( 'Licence key not validated.', 'indexreloaded' );
				} elseif ( 'on' === $licact ) {
					if ( true === $licdateexp ) {
						$licmsg = __( 'Licence key did expire', 'indexreloaded' );
						update_option( 'indexreloaded_LicActive', '' );
					} else {
						$licmsg = __( 'Licence key is valid.', 'indexreloaded' );
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

			$tabhead        = $this->add_tab_header();
			$tabsectionhead = $this->add_sectionhead( 'Generalsettings', true, false );

			add_settings_section(
				'indexreloaded_section',
				__( 'General settings', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabhead . $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_active',
				__( 'Activation', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section',
				array(
					'label_for'   => 'indexreloaded_active',
					'description' => __( 'Controls weather IndexReloaded is enabled.', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_active'
			);

			add_settings_field(
				'indexreloaded_APIkey',
				__( 'Licence key', 'indexreloaded' ),
				array( &$this, 'textlic_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section',
				array(
					'label_for'   => 'indexreloaded_APIkey',
					'description' => __( 'Enter your licence key obtained on toctoc.ch here.', 'indexreloaded' ),
					'placeholder' => __( 'Licence key', 'indexreloaded' ),
					'hint'        => __( 'A licence key is needed for creation of CSS above and below the fold. You can <a href="https://www.toctoc.ch/en/getindexreloaded">get it here on our site</a><br />Status: ', 'indexreloaded' ) . $licmsg,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_APIkey'
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_APIkey_lastvalidate'
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_APIkey_vdtd'
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_LicActive'
			);

			add_settings_field(
				'indexreloaded_pathcssjs',
				__( 'Path to css/js-output', 'indexreloaded' ),
				array( &$this, 'text_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section',
				array(
					'label_for'   => 'indexreloaded_pathcssjs',
					'description' => __( 'Use relative path from website-root for this', 'indexreloaded' ),
					'placeholder' => 'wp-content/uploads/cssjs',
					'hint'        => __( 'By default this is set to wp-content/uploads/cssjs.', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_pathcssjs'
			);

			add_settings_field(
				'indexreloaded_cssjs_keeptime',
				__( 'Time to keep files in cache', 'indexreloaded' ),
				array( &$this, 'number_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section',
				array(
					'label_for'   => 'indexreloaded_cssjs_keeptime',
					'description' => __( 'Controls how many days css/js-files are kept on disk', 'indexreloaded' ),
					'min'         => 0,
					'max'         => 60,
					'step'        => 1,
					'default'     => 0,
					'required'    => true,
					'hint'        => __( 'If set to 0, files will not be deleted automatically', 'indexreloaded' ),

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_cssjs_keeptime'
			);

			$tabsectionhead = $this->add_sectionhead( 'CSSandJSprocessing', false, false );
			add_settings_section(
				'indexreloaded_section_generalcssjs',
				__( 'Rules for CSS and JS processing', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_generalcssjs_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_dontmod',
				__( 'Do not modify pages', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_dontmod',
					'description' => __( 'Controls weather IndexReloaded will modify CSS/JS of your pages.', 'indexreloaded' ),
					'default'     => 0,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_dontmod'
			);

			add_settings_field(
				'indexreloaded_dontmodJS',
				__( 'Do not modify Javascript', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_dontmodJS',
					'description' => __( 'When modifying CSS/JS is allowed, then here you can exclude JS from processing.', 'indexreloaded' ),
					'default'     => 0,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_dontmodJS'
			);

			add_settings_field(
				'indexreloaded_dontmodCSS',
				__( 'Do not modify CSS', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_dontmodCSS',
					'description' => __( 'When modifying CSS/JS is allowed, then here you can exclude CSS from processing. Excluding both CSS and JS from processing is same as enabling "Do not modify pages".', 'indexreloaded' ),
					'default'     => 0,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_dontmodCSS'
			);

			add_settings_field(
				'indexreloaded_excludesarr',
				__( 'Exclude list', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_excludesarr',
					'description' => __( 'Parts of JS or CSS-filenames or paths, Ids of or parts of inline CSS or JS that must be excluded from processing, comma-separated list', 'indexreloaded' ),
					'placeholder' => 'javascript-id, plugins/plugin/js/,',
					'class'       => 'irld-ta',
					'hint'        => __( 'If this is empty and recommended excludes are found (see Overview), then the recommended excludes are automatically pushed into this option', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_excludesarr'
			);

			add_settings_field(
				'indexreloaded_forceNewFiles',
				__( 'Force creation of new files', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_forceNewFiles',
					'description' => __( 'You can force creation new files (CSS and JS), alternatively URL-Parameter ?forceNewFiles=1 does the same. Normally IndexReloaded detects if new files must be created.', 'indexreloaded' ),
					'default'     => 0,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_forceNewFiles'
			);

			add_settings_field(
				'indexreloaded_productionMode',
				__( 'Production mode', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_productionMode',
					'description' => __( 'In production mode URL-Parameters ?forceNewFiles=1, ?dontModIndex=1 and ?showDebugWindow=1 are disabled.', 'indexreloaded' ),
					'default'     => 0,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_productionMode'
			);

			add_settings_field(
				'indexreloaded_deactivateOnPages',
				__( 'Deactivate on pages', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_deactivateOnPages',
					'description' => __( 'List of pages that should not be touched by IndexReloaded.', 'indexreloaded' ),
					'placeholder' => '/somepage/',
					'class'       => 'irld-ta',

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_deactivateOnPages'
			);

			add_settings_field(
				'indexreloaded_asynchLastJS',
				__( 'Load last JS file asynchronous', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_asynchLastJS',
					'description' => __( 'Must be disabled when in code of the last JS file "document.write" is used.', 'indexreloaded' ),
					'default'     => 1,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_asynchLastJS'
			);

			add_settings_field(
				'indexreloaded_deferAllJS',
				__( 'Defer remaining JS after processing', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_deferAllJS',
					'description' => __( 'All external JS-files will be loaded deferred.', 'indexreloaded' ),
					'default'     => 1,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_deferAllJS'
			);

			add_settings_field(
				'indexreloaded_dontDeferJquery',
				__( 'Do not defer Jquery', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_dontDeferJquery',
					'description' => __( 'Jquery will not be loaded deferred.', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_dontDeferJquery'
			);

			// Settings for compression.
			add_settings_field(
				'indexreloaded_doCrunchCSS',
				__( 'CSS compression', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_doCrunchCSS',
					'description' => __( 'When enabled CSS output will be compressed.', 'indexreloaded' ),
					'default'     => 1,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_doCrunchCSS'
			);

			add_settings_field(
				'indexreloaded_optMinifyjsfiles',
				__( 'Minify JS-files', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_optMinifyjsfiles',
					'description' => __( 'Enable minify of JS-files with JSMin.php - modified PHP implementation of Douglas Crockford\'s JSMin', 'indexreloaded' ),
					'default'     => 1,

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_optMinifyjsfiles'
			);

			add_settings_field(
				'indexreloaded_noMinifyjsList',
				__( 'Minify exclude list', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_noMinifyjsList',
					'description' => __( 'Comma-separated list with parts of JS-filenames, that must not be minified.', 'indexreloaded' ),
					'placeholder' => '',
					'class'       => 'irld-ta',

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_noMinifyjsList'
			);

			add_settings_field(
				'indexreloaded_externally_hosted_host_locally',
				__( 'Host external JS and CSS files locally', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_externally_hosted_host_locally',
					'description' => __( 'Download external CSS and JS to local disk and serve code from locally hosted source.', 'indexreloaded' ),
					'default'     => 0,
					'enabled'     => $enableopt,
					'hint'        => __( 'Downloaded files will be saved to subfolder "external" in path to css/js-output', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_externally_hosted_host_locally'
			);

			add_settings_field(
				'indexreloaded_externally_hosted_keeptime',
				__( 'Time to leave locally hosted external files unchecked', 'indexreloaded' ),
				array( &$this, 'number_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_generalcssjs',
				array(
					'label_for'   => 'indexreloaded_externally_hosted_keeptime',
					'description' => __( 'Controls how many days locally hosted external files remain unchecked', 'indexreloaded' ),
					'min'         => 0,
					'max'         => 60,
					'step'        => 0,
					'default'     => 7,
					'required'    => true,
					'hint'        => __( 'If set to 0, files will not be deleted automatically', 'indexreloaded' ),

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_externally_hosted_keeptime'
			);

			// CSS above and below the fold options.
			$tabsectionhead = $this->add_sectionhead( 'CriticalCSS', false, false );
			add_settings_section(
				'indexreloaded_section_folding',
				__( 'CSS above and below the fold', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_folding_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_generateCSSbelowTheFold',
				__( 'Enable folding', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_generateCSSbelowTheFold',
					'description' => __( 'Split CSS into critical CSS above the fold and non-critical CSS below the fold (requires licence key).', 'indexreloaded' ),
					'default'     => 0,
					'enabled'     => $enableopt,
					'hint'        => __( 'Use Chrome Dev Tools feature Coverage to find tags, IDs and classes to keep above the fold', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_generateCSSbelowTheFold'
			);

			add_settings_field(
				'indexreloaded_inlineCCSS',
				__( 'Inline critical CSS', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_inlineCCSS',
					'description' => __( 'Either load critical CSS inline or by links to files', 'indexreloaded' ),
					'default'     => 0,
					'enabled'     => $enableopt,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_inlineCCSS'
			);

			add_settings_field(
				'indexreloaded_tagsToKeepAboveTheFold',
				__( 'Tags to keep above the fold', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_tagsToKeepAboveTheFold',
					'description' => __( 'List of tags that always remain in the CSS above the fold.', 'indexreloaded' ),
					'placeholder' => 'nav',
					'class'       => 'irld-ta',
					'enabled'     => $enableopt,
					'hint'        => __( 'Apart from classic tags, here you can add all that is not class or id, like exotic CSS instructions', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_tagsToKeepAboveTheFold'
			);

			add_settings_field(
				'indexreloaded_classesToKeepAboveTheFold',
				__( 'Classes to keep above the fold', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_classesToKeepAboveTheFold',
					'description' => __( 'List of CSS classes that always remain in the CSS above the fold.', 'indexreloaded' ),
					'placeholder' => 'displayed,wcfm_vendor_badges_a,wcfm_vendor_badges',
					'class'       => 'irld-ta',
					'enabled'     => $enableopt,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_classesToKeepAboveTheFold'
			);

			add_settings_field(
				'indexreloaded_IDsToKeepAboveTheFold',
				__( 'Ids to keep above the fold', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_IDsToKeepAboveTheFold',
					'description' => __( 'List of CSS ids that always remain in the CSS above the fold.', 'indexreloaded' ),
					'placeholder' => 'scroll-to-top',
					'class'       => 'irld-ta',
					'enabled'     => $enableopt,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_IDsToKeepAboveTheFold'
			);

			add_settings_field(
				'indexreloaded_removeUnusedCSS',
				__( 'Remove CSS from CSS below if it doesn\'t sort out CCSS', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_removeUnusedCSS',
					'description' => __( 'CSS-files or inline CSS that does not result in Critical CSS (CCSS) will not be present in CSS below the fold.', 'indexreloaded' ),
					'default'     => 0,
					'hint'        => __( 'Enable this option only after having found tags, IDs and classes to keep above the fold', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_removeUnusedCSS'
			);

			// Load cssbelow in slowmotion.
			add_settings_field(
				'indexreloaded_load_cssbelow_in_slowmotion',
				__( 'Load CSS below in slowmotion', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_folding',
				array(
					'label_for'   => 'indexreloaded_load_cssbelow_in_slowmotion',
					'description' => __( 'Delays loading of CSS below the fold by 2 seconds', 'indexreloaded' ),
					'default'     => 0,
					'enabled'     => $enableopt,
					'hint'        => __( 'This is useful for detection of tags, classes or ids that must stay above the fold', 'indexreloaded' ),

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_load_cssbelow_in_slowmotion'
			);

			// Preloading.
			$tabsectionhead = $this->add_sectionhead( 'Preloading', false, false );
			add_settings_section(
				'indexreloaded_section_preload',
				__( 'Preloading of CSS and JS', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_preload_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_PreloadTag',
				__( 'Prepend-to-tag', 'indexreloaded' ),
				array( &$this, 'text_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_preload',
				array(
					'label_for'   => 'indexreloaded_PreloadTag',
					'description' => __( 'Tag (must not be complete tag, but identifyable) where the preloads are preprended before.', 'indexreloaded' ),
					'placeholder' => '<meta name="viewport"',
					'hint'        => __( 'The preload-Tags should appear as the first links to ressources in the HTML. They can contain the largest image, important fonts and large stylesheets that are loaded in CSS files or jquery', 'indexreloaded' ),

				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_PreloadTag'
			);

			add_settings_field(
				'indexreloaded_PreloadImage',
				__( 'Image files', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_preload',
				array(
					'label_for'   => 'indexreloaded_PreloadImage',
					'description' => __( 'Comma-separated list of image files that should be preloadeded with <link rel="preload" fetchpriority="high" as="image" href="/path/to/hero-image.ext" type="image/ext">.', 'indexreloaded' ),
					'placeholder' => '',
					'class'       => 'irld-ta',
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_PreloadImage'
			);

			add_settings_field(
				'indexreloaded_PreloadCSSList',
				__( 'CSS files', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_preload',
				array(
					'label_for'   => 'indexreloaded_PreloadCSSList',
					'description' => __( 'Comma-separated list of CSS files that should be preloadeded with <link rel="preload" href="styles.css" as="style">.', 'indexreloaded' ),
					'placeholder' => '',
					'class'       => 'irld-ta',
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_PreloadCSSList'
			);

			add_settings_field(
				'indexreloaded_PreloadJSList',
				__( 'JS files', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_preload',
				array(
					'label_for'   => 'indexreloaded_PreloadJSList',
					'description' => __( 'Comma-separated list of JS files that should be preloadeded with <link rel="preload" href="file.js" as="script">.', 'indexreloaded' ),
					'placeholder' => '/wp-content/plugins/jquery-manager/assets/js/jquery-3.5.1.min.js',
					'class'       => 'irld-ta',
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_PreloadJSList'
			);

			add_settings_field(
				'indexreloaded_PreloadFontsList',
				__( 'Font-files', 'indexreloaded' ),
				array( &$this, 'textarea_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_preload',
				array(
					'label_for'   => 'indexreloaded_PreloadFontsList',
					'description' => __( 'Comma-separated list of Font-files that should be preloadeded with <link rel="preload" href="file.woff2" as="font" type="font/woff2" crossorigin="anonymous">.', 'indexreloaded' ),
					'placeholder' => '/wp-content/themes/storefront/assets/fonts/fa-solid-900.woff2',
					'class'       => 'irld-ta',
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_PreloadFontsList'
			);

			// Cleaning HTML.
			$tabsectionhead = $this->add_sectionhead( 'HTMLCleanup', false, false );
			add_settings_section(
				'indexreloaded_section_cleaning',
				__( 'Cleaning of HTML', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_cleaning_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_cleanArchiveStringsInPagetitle',
				__( 'Remove "Archive"-strings in page title', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_cleaning',
				array(
					'label_for'   => 'indexreloaded_cleanArchiveStringsInPagetitle',
					'description' => __( 'Tries to remove "Archive"-strings from page title. Works for English, French and German', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_cleanArchiveStringsInPagetitle'
			);

			add_settings_field(
				'indexreloaded_cleanPluginNotes',
				__( 'Remove HTML-comments and generator meta', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_cleaning',
				array(
					'label_for'   => 'indexreloaded_cleanPluginNotes',
					'description' => __( 'Removes identifying generator meta tags and all HTML-comments', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_cleanPluginNotes'
			);

			add_settings_field(
				'indexreloaded_removePingbacks',
				__( 'Remove pingbacks', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_cleaning',
				array(
					'label_for'   => 'indexreloaded_removePingbacks',
					'description' => __( 'Remove pingbacks', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_removePingbacks'
			);

			add_settings_field(
				'indexreloaded_removeRSS',
				__( 'Remove RSS', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_cleaning',
				array(
					'label_for'   => 'indexreloaded_removeRSS',
					'description' => __( 'Remove RSS-links', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_removeRSS'
			);

			add_settings_field(
				'indexreloaded_removeShortlink',
				__( 'Remove short link', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_cleaning',
				array(
					'label_for'   => 'indexreloaded_removeShortlink',
					'description' => __( 'Remove shortlink to the page', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_removeShortlink'
			);

			$tabsectionhead = $this->add_sectionhead( 'Monitoring', false, false );

			add_settings_section(
				'indexreloaded_section_debug',
				__( 'Debugging and monitoring', 'indexreloaded' ),
				array( &$this, 'indexreloaded_section_debug_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				array(
					'before_section' => $tabsectionhead,
					'after_section'  => '</div>',
				)
			);

			add_settings_field(
				'indexreloaded_showDebugWindow',
				__( 'Activate debugging and monitoring', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_debug',
				array(
					'label_for'   => 'indexreloaded_showDebugWindow',
					'description' => __( 'When debugging and monitoring is active, the IndexReloaded monitor shows up on the webpage.', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_showDebugWindow'
			);

			add_settings_field(
				'indexreloaded_DebugIP',
				__( 'IP allowed for using the debug feature', 'indexreloaded' ),
				array( &$this, 'text_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_debug',
				array(
					'label_for'   => 'indexreloaded_DebugIP',
					'description' => __( 'The debug-windows only shows up if your local IP matches this IP, \'*\' allows all.', 'indexreloaded' ),
					'placeholder' => '127.0.0.1',
					'hint'        => __( 'Your current IP is', 'indexreloaded' ) . ' ' . $this->get_ip_address(),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_DebugIP'
			);

			add_settings_field(
				'indexreloaded_showDebugWindowBodyTag',
				__( 'Append HTML to tag', 'indexreloaded' ),
				array( &$this, 'text_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_debug',
				array(
					'label_for'   => 'indexreloaded_showDebugWindowBodyTag',
					'description' => __( 'The output of Debugging is appended to the specified tag.', 'indexreloaded' ),
					'placeholder' => '<header id="masthead" class="site-header">',
					'hint'        => __( 'Please inspect the HTML of your page for this. &lt;header&gt;-tag near the body-tag is appropriate.', 'indexreloaded' ) . '<br />' .
					__( 'If not specified, the &lt;body&gt;-tag will be used for this.', 'indexreloaded' ),
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_showDebugWindowBodyTag'
			);

			add_settings_field(
				'indexreloaded_CSSFoldingReport',
				__( 'Monitor folding', 'indexreloaded' ),
				array( &$this, 'checkbox_cb' ),
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_section_debug',
				array(
					'label_for'   => 'indexreloaded_CSSFoldingReport',
					'description' => __( 'Adds a little report for CSS above and below the fold, when this is created.', 'indexreloaded' ),
					'hint'        => __( 'You can force creation of above and below CSS with URL parameter ?ForceNewFiles=1.<br />Note that option "Enable folding" needs to be active.', 'indexreloaded' ),
					'default'     => 0,
				)
			);

			register_setting(
				'indexreloaded-menu-indexreloaded-settings',
				'indexreloaded_CSSFoldingReport'
			);
		}


		/**
		 * Fires as the settings screen is being initialized.
		 */
		public function action_admin_menu() {
			add_submenu_page(
				'indexreloaded-menu',
				__( 'IndexReloaded Settings', 'indexreloaded' ),
				__( 'Settings', 'indexreloaded' ),
				'manage_options',
				'indexreloaded-menu-settings',
				array( &$this, 'indexreloaded_settings_page_cb' )
			);
		}

		/**
		 * Prints settings page head.
		 */
		public function indexreloaded_settings_page_cb() {
			?>
			<h1>
			<?php
			esc_html_e( 'IndexReloaded settings', 'indexreloaded' );
			?>
			</h1><p>
			<?php
			esc_html_e( 'The settings enable or disable IndexReloaded functions.', 'indexreloaded' );
			echo ' | ';
			$linkto_overview = '<a href="admin.php?page=indexreloaded-menu">' . __( 'Back to Overview', 'indexreloaded' ) . '</a>';
			$allowed_html    = array(
				'a' => array(
					'href'  => array(),
					'class' => array(),
				),
			);
			echo wp_kses( $linkto_overview, $allowed_html );
			?>
			</p>
				<form method="post" action="
					<?php
					if ( is_plugin_active_for_network( 'indexreloaded/indexreloaded.php' ) ) {
						echo esc_url(
							add_query_arg(
								'action',
								'indexreloaded-menu-indexreloaded-settings',
								network_admin_url( 'edit.php' )
							)
						);
					} else {
						echo 'options.php';
					}
					?>
					">
					<?php
						settings_fields( 'indexreloaded-menu-indexreloaded-settings' );
						do_settings_sections( 'indexreloaded-menu-indexreloaded-settings' );
						submit_button();
					?>
				</form>
			<?php
		}

		/**
		 * Prints checkbox form field.
		 *
		 * @param string $arg Current option .
		 */
		public function checkbox_cb( $arg ) {
			?>
			<input name="<?php echo esc_attr( $arg['label_for'] ); ?>" id="<?php echo esc_attr( $arg['label_for'] ); ?>" type="hidden" value="
									<?php
									$enabled = '';
									if ( isset( $arg['enabled'] ) ) {
										if ( 'disabled' === $arg['enabled'] ) {
											$enabled = ' disabled';
										}
									}

									$hint = '';
									if ( isset( $arg['hint'] ) ) {
										$hint = '<p class="irld_hint" id="hnt_' . $arg['label_for'] . '">' . $arg['hint'] . '</p>';
									}

									$savedoption = trim( get_option( $arg['label_for'] ) );
									if ( 'on' === $savedoption ) {
										if ( isset( $arg['enabled'] ) ) {
											if ( 'disabled' === $arg['enabled'] ) {
												$savedoption = 'off';
											}
										}
									}

									$default       = $arg['default'];
									$defaultoption = 'off';
									if ( 1 === $default ) {
										$defaultoption = 'on';
									}

									if ( '' === trim( $savedoption ) ) {
										$savedoption = $defaultoption;
									}

									echo esc_html( trim( $savedoption ) ) . '" />';
									if ( 'on' === trim( $savedoption ) ) {
										$switchback_class = '';
										$switchoff_class  = '';
										$switchon_class   = '';
									} else {
										$switchback_class = ' inactive';
										$switchoff_class  = ' inactive';
										$switchon_class   = ' inactive';
									}

									$switch = '<span class="irld-switchback' . $switchback_class . $enabled . '" id="swbk' . $arg['label_for'] . '">
			 </span>
			 <span class="irld-switchoff' . $switchoff_class . $enabled . '" id="swof' . $arg['label_for'] . '">
			 </span>
			 <span class="irld-switchon' . $switchon_class . $enabled . '"  id="swon' . $arg['label_for'] . '">
			 </span>';

									$allowed_html = array(
										'span' => array(
											'id'    => array(),
											'class' => array(),
										),
									);
									echo wp_kses( $switch, $allowed_html );
									?>
			<p class="irld-description">
			<?php
			echo esc_html( $arg['description'] ) . '</p>';
			$allowed_html = array(
				'br'   => array(),
				'a'    => array(
					'href'  => array(),
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
			echo wp_kses( $hint, $allowed_html );
			?>
			<?php
		}

		/**
		 * Prints number form field.
		 *
		 * @param string $arg Current option .
		 */
		public function number_cb( $arg ) {
			$hint = '';
			if ( isset( $arg['hint'] ) ) {
				$hint = '<p class="irld_hint" id="hnt_' . $arg['label_for'] . '">' . $arg['hint'] . '</p>';
			}
			?>
			<input
				type="number"
				required="required"
				name="<?php echo esc_attr( $arg['label_for'] ); ?>"
				value="<?php echo esc_attr( absint( get_option( $arg['label_for'], $arg['default'] ) ) ); ?>"
				<?php
				if ( isset( $arg['required'] ) && $arg['required'] ) :
					?>
					required="required"<?php endif; ?>
				min="<?php echo floatval( $arg['min'] ); ?>"
				max="<?php echo floatval( $arg['max'] ); ?>"
				step="<?php echo floatval( $arg['step'] ); ?>" />

			<p class="irld-description">
			<?php
			echo esc_html( $arg['description'] );
			$allowed_html = array(
				'br'   => array(),
				'a'    => array(
					'href'  => array(),
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
			echo wp_kses( $hint, $allowed_html );
			?>
				</p>
			<?php
		}


		/**
		 * Prints text form field.
		 *
		 * @param string $arg Current option.
		 */
		public function text_cb( $arg ) {
			$hint = '';
			if ( isset( $arg['hint'] ) ) {
				$hint = '<p class="irld_hint" id="hnt_' . $arg['label_for'] . '">' . $arg['hint'] . '</p>';
			}

			?>
					<input
						type="text"
						style="width: 60%;"
						name="<?php echo esc_attr( $arg['label_for'] ); ?>"
						id="<?php echo esc_attr( $arg['label_for'] ); ?>"
						placeholder="<?php echo esc_attr( $arg['placeholder'] ); ?>"
						value="<?php echo esc_attr( get_option( $arg['label_for'] ) ); ?>" />
		
					<p id="<?php echo esc_attr( $arg['label_for'] ); ?>-description" class="irld-description">
						<?php
						echo esc_html( $arg['description'] ) . '</p>';
						$allowed_html = array(
							'br'   => array(),
							'a'    => array(
								'href'  => array(),
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
						echo wp_kses( $hint, $allowed_html );
						?>
			<?php
		}


		/**
		 * Prints licence form field.
		 *
		 * @param string $arg Current option.
		 */
		public function textlic_cb( $arg ) {
			$hint = '';
			if ( isset( $arg['hint'] ) ) {
				$hint = '<p class="irld_hint" id="hnt_' . $arg['label_for'] . '">' . $arg['hint'] . '</p>';
			}

			$color         = '#888';
			$value         = '';
			$displaybutton = ' irld-inline-block';
			if ( 'on' === get_option( 'indexreloaded_LicActive' ) ) {
				$displaybutton = ' irld-display-none';
			}

			$button  = '<a role="button" id="btn-' . esc_attr( $arg['label_for'] ) . '" class="irld-button' . $displaybutton . ' button button-primary">' . __( 'Verify key', 'indexreloaded' ) . '</a>';
			$removal = '<p id="area-removal" class="irld-description" class="irld-display-none"><span id="area-removal-select" class="irld-display-none">' .
							__( 'Select a site with this licence key for deactivation', 'indexreloaded' ) . ':<br />' .
							'<select name="selected_lics" id="selected_lics" class="irld-select">' .
							'</select></span>' .
							'<a role="button" id="btn-' . esc_attr( $arg['label_for'] ) . '-removal" class="irld-button button button-primary">' .
							__( 'Remove key', 'indexreloaded' ) . '</a>' .
						'</p>';
			if ( 'on' === get_option( 'indexreloaded_LicActive' ) ) {
				$color   = 'green';
				$removal = '<p id="area-removal" class="irld-description">' .
								'<a role="button" id="btn-' . esc_attr( $arg['label_for'] ) . '-removal" class="irld-button button button-primary">' .
								__( 'Remove this key', 'indexreloaded' ) . '</a>' .
							'</p>';
			}

			$licdate = trim( get_option( 'indexreloaded_APIkey_lastvalidate' ) );
			$licvdtd = trim( get_option( 'indexreloaded_APIkey_vdtd' ) );
			$licact  = trim( get_option( 'indexreloaded_LicActive' ) );
			?>
			<input
				type="text"
				style="width: 60%;border-width: 3px; border-color: <?php echo esc_html( $color ); ?>;"
				name="<?php echo esc_attr( $arg['label_for'] ); ?>"
				id="<?php echo esc_attr( $arg['label_for'] ); ?>"
				placeholder="<?php echo esc_attr( $arg['placeholder'] ); ?>"
				value="<?php echo esc_attr( get_option( $arg['label_for'] ) ); ?>" />
			<input
				type="hidden"
				name="indexreloaded_APIkey_lastvalidate"
				id="indexreloaded_APIkey_lastvalidate"	
				value="<?php echo esc_html( $licdate ); ?>" 
			/>
			<input
				type="hidden"
				name="indexreloaded_APIkey_vdtd"
				id="indexreloaded_APIkey_vdtd"	
				value="<?php echo esc_html( $licvdtd ); ?>" 
			/>
			<input
				type="hidden"
				name="indexreloaded_LicActive"
				id="indexreloaded_LicActive"	
				value="<?php echo esc_html( $licact ); ?>" 
			/>						
			<?php

			$allowed_html = array(
				'br'     => array(),
				'b'      => array(),
				'a'      => array(
					'role'  => array(),
					'id'    => array(),
					'class' => array(),
				),
				'span'   => array(
					'id'    => array(),
					'class' => array(),
				),
				'p'      => array(
					'id'    => array(),
					'class' => array(),
				),
				'select' => array(
					'name'  => array(),
					'id'    => array(),
					'class' => array(),
				),
			);
			echo wp_kses( $button . $removal, $allowed_html );
			?>
			<p id="desc-<?php echo esc_attr( $arg['label_for'] ); ?>-description" class="irld-description">
			<?php
			echo esc_html( $arg['description'] ) . '</p>';
			$allowed_html = array(
				'br'   => array(),
				'a'    => array(
					'href'  => array(),
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
			echo wp_kses( $hint, $allowed_html );
			?>
			<?php
		}

		/**
		 * Prints textarea form field.
		 *
		 * @param string $arg Current option.
		 */
		public function textarea_cb( $arg ) {
			$enabled = '';
			if ( isset( $arg['enabled'] ) ) {
				if ( 'disabled' === $arg['enabled'] ) {
					$enabled = ' disabled';
				}
			}

			$optval = trim( get_option( $arg['label_for'] ) );
			if ( isset( $arg['default'] ) ) {
				if ( ' disabled' !== $enabled ) {
					if ( '' === $optval ) {
						$optval = $arg['default'];
					}
				}
			}

			$hint = '';
			if ( isset( $arg['hint'] ) ) {
				$hint = '<p class="irld_hint" id="hnt_' . $arg['label_for'] . '">' . $arg['hint'] . '</p>';
			}
			?>
							<textarea
								class= "<?php echo esc_attr( $arg['class'] ); ?>"
								name="<?php echo esc_attr( $arg['label_for'] ); ?>"
								id="<?php echo esc_attr( $arg['label_for'] ); ?>"
								placeholder="<?php echo esc_attr( $arg['placeholder'] ) . '"' . esc_html( $enabled ); ?>
								><?php echo esc_attr( $optval ); ?></textarea>
				
							<p id="<?php echo esc_attr( $arg['label_for'] ); ?>-description" class="irld-description">
								<?php
								echo esc_html( $arg['description'] ) . '</p>';
								$allowed_html = array(
									'br'   => array(),
									'a'    => array(
										'href'  => array(),
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
								echo wp_kses( $hint, $allowed_html );
		}

		/**
		 * Prints hidden form field.
		 *
		 * @param string $arg Current option.
		 */
		public function hidden_cb( $arg ) {
			?>
			<input
					type="hidden"
					name="<?php echo esc_attr( $arg['label_for'] ); ?>"
					id="<?php echo esc_attr( $arg['label_for'] ); ?>"
					value="<?php echo esc_attr( get_option( $arg['label_for'] ) ); ?>" />
			<?php
		}

		/**
		 * Return HTML for entire header.
		 */
		public function add_tab_header() {
			$ret  = '<div class="irld_tab_header">';
			$ret .= '	<span id="tabGeneralsettings" class="irld_tab_head active">' . __( 'General settings', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '	<span id="tabCSSandJSprocessing" class="irld_tab_head">' . __( 'CSS and JS processing', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '	<span id="tabCriticalCSS" class="irld_tab_head">' . __( 'Critical CSS', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '	<span id="tabPreloading" class="irld_tab_head">' . __( 'Preloading', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '	<span id="tabHTMLCleanup" class="irld_tab_head">' . __( 'HTML Cleanup', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '	<span id="tabMonitoring" class="irld_tab_head">' . __( 'Monitoring', 'indexreloaded' );
			$ret .= '	</span>';
			$ret .= '</div>';
			return $ret;
		}

		/**
		 * Return HTML for a section head.
		 *
		 * @param int  $id CSS-id of the section head.
		 * @param bool $active Is section head active.
		 * @param bool $closeprev Add a closing </div> before section head.
		 */
		public function add_sectionhead( $id, $active = false, $closeprev = true ) {
			$ret    = '';
			$actcss = '';
			if ( true === $active ) {
				$actcss = ' active';
			}

			if ( true === $closeprev ) {
				$ret .= '</div>';
			}

			$ret .= '<div class="irld_tab_content' . $actcss . '" id="tabContent' . $id . '">';
			return $ret;
		}

		/**
		 * Print section text for section "General settings".
		 */
		public function indexreloaded_section_cb() {
			echo '<p class="irld_section_title">';
			esc_html_e( 'Basic settings', 'indexreloaded' );
			echo '<br /></p>';
		}

		/**
		 * Print section text for section "Debugging and monitoring".
		 */
		public function indexreloaded_section_debug_cb() {
			echo '<p class="irld_section">';
			esc_html_e( 'Settings for debugging and monitoring', 'indexreloaded' );
			echo '<br /></p>';
		}

		/**
		 * Print section text for section "Rules for CSS and JS processing".
		 */
		public function indexreloaded_section_generalcssjs_cb() {
			echo '<p class="irld_section">';
			esc_html_e( 'Rules for CSS and JS over all pages', 'indexreloaded' );
			echo '<br /></p>';
		}

		/**
		 * Print section text for section "CSS above and below the fold".
		 */
		public function indexreloaded_section_folding_cb() {
			echo '<p class="irld_section">';
			esc_html_e( 'Rules for Critical CSS (folding)', 'indexreloaded' );
			echo '<br /></p>';
		}

		/**
		 * Print section text for section "Preloading of CSS and JS".
		 */
		public function indexreloaded_section_preload_cb() {
			echo '<p class="irld_section">';
			esc_html_e( 'Rules for preloading', 'indexreloaded' );
			echo '<br /></p>';
		}

		/**
		 * Print section text for section "Cleaning of HTML".
		 */
		public function indexreloaded_section_cleaning_cb() {
			echo '<p class="irld_section">';
			esc_html_e( 'Rules for removing particular, potentially undesired, stuff from HTML', 'indexreloaded' );
			echo '<br /></p>';
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
			$actionwp   = 'checkixrdkey';
			$getp       = '&action=' . $actionwp . '&remoteadr=' . $curip . '&remotesite=' . $siteurl . '&lang=' . $langreq;
			$urltofetch = 'https://www.toctoc.ch/wp-admin/admin-ajax.php';
			$gtstrng    = add_query_arg( '_wpspecnonce', irld_get_special_nonce( 'checkixrdkey' ), $urltofetch ) . $getp;
			$gtstrng    = str_replace( 'https://www.toctoc.ch/wp-admin/admin-ajax.php', '', $gtstrng );

			$actionwp   = 'removeixrdkey';
			$getprem    = '&action=' . $actionwp . '&remoteadr=' . $curip . '&remotesite=' . $siteurl . '&lang=' . $langreq;
			$urltofetch = 'https://www.toctoc.ch/wp-admin/admin-ajax.php';
			$gtstrngrem = add_query_arg( '_wpspecnonce', irld_get_special_nonce( 'removeixrdkey' ), $urltofetch ) . $getprem;
			$gtstrngrem = str_replace( 'https://www.toctoc.ch/wp-admin/admin-ajax.php', '', $gtstrngrem );

			$actionwp   = 'irld_upd_opts';
			$getprem    = '&action=' . $actionwp;
			$urltofetch = $siteurl . 'wp-admin/admin-ajax.php';
			$gtstrngopt = add_query_arg( '_wpnonce', wp_create_nonce( 'irld_upd_opts' ), $urltofetch ) . $getprem;
			$gtstrngopt = str_replace( $siteurl . 'wp-admin/admin-ajax.php', '', $gtstrngopt );

			$inlinejs  = 'var gtstrng = "' . $gtstrng . '";var gtstrngrem = "' . $gtstrngrem . '";var locsite = "' . $siteurl . '";var gtstrngopt = "' . $gtstrngopt . '";';
			$inlinejs .= 'var la = []; la["License_key"] = "' . __( 'License key', 'indexreloaded' ) . '";' .
						'la["is_valid"] = "' . __( 'is valid', 'indexreloaded' ) . '";' .
						'la["is_deleted"] = "' . __( 'is deleted', 'indexreloaded' ) . '";';
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
		 * Get client IP.
		 */
		private function get_ip_address() {
			foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
						$ip = trim( $ip );
						if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
							return $ip;
						}
					}
				}
			}
		}
	}

	new Indexreloadedsettings();
}
