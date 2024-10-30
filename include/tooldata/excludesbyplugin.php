<?php
/**
 *  Indexreloaded-backend. excludes by plugin.
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( -1 );

$ret = array(
	'CookieYes | GDPR Cookie Consent'              => array(
		'exclude' => 'cky-style-inline', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'CSS needs to be present on page when JS loads', 'indexreloaded' ),
	),
	'Elementor Pro'                                => array(
		'exclude' => 'elementor-pro-frontend-js-before', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Contains a nonce', 'indexreloaded' ),
	),
	'hCaptcha for WP'                              => array(
		'exclude' => 'onload=hCaptchaOnLoad', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'hCaptcha does not show up, if the inline JS, inline CSS and JS-files are touched', 'indexreloaded' ),
	),
	'hCaptcha for WordPress'                       => array(
		'exclude' => 'onload=hCaptchaOnLoad', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'hCaptcha does not show up, if the inline JS, inline CSS and JS-files are touched', 'indexreloaded' ),
	),
	'Newsletter, SMTP, Email marketing and Subscribe forms by Brevo' => array(
		'exclude' => 'sib-front-js-js-extra', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Contains a nonce', 'indexreloaded' ),
	),
	'Page Builder by SiteOrigin'                   => array(
		'exclude' => 'siteorigin-panels-front-styles-js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript does not trigger', 'indexreloaded' ),
	),
	'Photo Gallery'                                => array(
		'exclude' => 'twb-open-sans-css', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'This is a recommendation. It is external CSS, if regrouped above the fold it may increase number of IndexReloaed-CSS files by one', 'indexreloaded' ),
	),
	'Responsive Lightbox & Gallery'                => array(
		'exclude'    => 'responsive-lightbox-js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'reason' => __( 'Javascript-event does not trigger', 'indexreloaded' ),
	),
	'Social Login'                                 => array(
		'exclude' => 'oneall_social_login_providers', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Inline-JS contains values changing on every page load', 'indexreloaded' ),
	),
	'WooCommerce'                                  => array(
		'exclude' => 'wcStoreApiNonceTimestamp', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript-errors and inline-JS containing values changing on every page load', 'indexreloaded' ),
	),
	'WooCommerce Frontend Manager'                 => array(
		'exclude' => 'var sales_data = {,jquery-chart_,', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Charts do not show up', 'indexreloaded' ),
	),
	'Wordfence Security'                           => array(
		'exclude' => 'WordfenceTestMonBot', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Inline-JS contains values changing on every page load', 'indexreloaded' ),
	),
	'WP Geoloc'                                    => array(
		'exclude' => 'wpsl-gmap.min.js,wpslSettings,wpsl-gmap-js,initAutocomplete,rangeslider.min.js', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript-error', 'indexreloaded' ),
	),
	'WPForms Lite'                                 => array(
		'exclude' => 'wpforms-lite/assets/js/', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript-error', 'indexreloaded' ),
	),
	'WPFront Scroll Top'                           => array(
		'exclude' => 'wpfront-scroll-top-js-extra', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript-error', 'indexreloaded' ),
	),
	'WP Dark Mode'                                 => array(
		'exclude' => 'wp-dark-mode-js-js,wp-dark-mode-frontend-js-extra,wp-dark-mode-frontend-js,window.wpDarkMode', // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
		'reason'  => __( 'Javascript-event does not trigger', 'indexreloaded' ),
	),
	'Age Gate'                                     => array(),
	'WooCommerce Frontend Manager - Ultimate'      => array(),
	'Super Socializer'                             => array(),
	'User Login History'                           => array(),
	'Classic Editor'                               => array(),
	'Contact Form 7'                               => array(),
	'Disable Comments'                             => array(),
	'File Upload Types'                            => array(),
	'IndexReloaded'                                => array(),
	'Meta Tag Manager'                             => array(),
	'Open Graph and Twitter Card Tags'             => array(),
	'Responsive WordPress Slider - Soliloquy Lite' => array(),
	'Site Kit by Google'                           => array(),
	'SiteOrigin CSS'                               => array(),
	'WP-Optimize - Clean, Compress, Cache'         => array(),
	'WP Markdown Editor (Formerly Dark Mode)'      => array(),
	'Yoast Duplicate Post'                         => array(),
	'Yoast SEO'                                    => array(),
);
return $ret;
