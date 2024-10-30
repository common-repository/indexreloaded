<?php
/**
 * This class Provides an easy way to enqueue and display dismissible admin notices in the WordPress admin interface
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );

if ( ! class_exists( 'IndexReloadedadminnotices' ) ) {

	/**
	 * IndexReloadedadminnotices
	 */
	class IndexReloadedadminnotices {

		/**
		 * The instance of this class
		 *
		 * @var object
		 */
		private static $the_instance;

		/**
		 * New stdClass of this class
		 *
		 * @var object
		 */
		private $admin_notices;
		const IRLD_TYPES = 'error,warning,info,success';

		/**
		 * Setup class.
		 */
		private function __construct() {
			$this->admin_notices = new stdClass();
			foreach ( explode( ',', self::IRLD_TYPES ) as $type ) {
				$this->admin_notices->{$type} = array();
			}

			add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
			add_action( 'admin_notices', array( &$this, 'action_admin_notices' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
		}

		/**
		 * Create and return the instance.
		 */
		public static function getthe_instance() {
			if ( ! ( self::$the_instance instanceof self ) ) {
				self::$the_instance = new self();
			}

			return self::$the_instance;
		}

		/**
		 * Fires as the admin screen is being initialized.
		 */
		public function action_admin_init() {
			if ( isset( $_GET['indexreloaded_dismiss'] ) ) {
				$dismiss_option = sanitize_text_field( wp_unslash( $_GET['indexreloaded_dismiss'] ) );
				if ( is_string( $dismiss_option ) ) {
					if ( isset( $_GET['_wpnonce'] ) ) {
						if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'indexreloaded_dismiss' ) ) { // WPCS: input var ok, CSRF ok.
							wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'indexreloaded' ) );
						}

						update_option( "indexreloaded_dismissed_$dismiss_option", true );
					}
				}
			}
		}

		/**
		 * Fires when enqueuing scripts for all admin pages.
		 */
		public function action_admin_enqueue_scripts() {

			if ( file_exists( IRLD_PATH . '/assets/js/indexreloaded-notify.min.js' ) ) {
				$script = 'indexreloaded-notify.min.js';
			} else {
				$script = 'indexreloaded-notify.js';
			}

			wp_enqueue_script(
				'indexreloaded-notify',
				plugins_url( "assets/js/$script", IRLD_PATH . '/indexreloaded.php' ),
				array( 'jquery' ),
				'1.2.1',
				array( 'in_footer' => true )
			);
		}

		/**
		 * Prints admin screen notices.
		 */
		public function action_admin_notices() {

			foreach ( explode( ',', self::IRLD_TYPES ) as $type ) {
				foreach ( $this->admin_notices->{$type} as $admin_notice ) {

					// hide dismissible nag notices if DISABLE_NAG_NOTICES is set.
					// see: https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices.
					if ( 'error' !== $type && $admin_notice->dismiss_option && defined( 'DISABLE_NAG_NOTICES' ) && constant( 'DISABLE_NAG_NOTICES' ) ) {
						continue;
					}

					$dismiss_url = add_query_arg(
						array(
							'indexreloaded_dismiss' => $admin_notice->dismiss_option,
							'_wpnonce'              => wp_create_nonce( 'indexreloaded_dismiss' ),
						),
						call_user_func( is_plugin_active_for_network( 'indexreloaded/indexreloaded.php' ) ? 'network_admin_url' : 'admin_url' )
					);

					$dismiss_url = esc_url( wp_nonce_url( add_query_arg( 'indexreloaded_dismiss', $admin_notice->dismiss_option ), 'indexreloaded_dismiss', '_wpnonce' ) );

					if ( ! get_option( "indexreloaded_dismissed_$admin_notice->dismiss_option" ) ) {
						?><div class="notice indexreloaded-notice notice-
							<?php
							echo esc_html( $type );

							if ( $admin_notice->dismiss_option ) {
								echo ' is-dismissible" data-dismiss-url="' . esc_url( $dismiss_url );
							}
							?>
						">

							<h2><?php echo 'IndexReloaded ' . esc_html( $type ); ?></h2>
							<p><?php echo esc_html( $admin_notice->message ); ?></p>

						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Prints an error.
		 *
		 * @param string $message message to display.
		 * @param bool   $dismiss_option if message is dismissable.
		 */
		public function error( $message, $dismiss_option = false ) {
			$this->notice( 'error', $message, $dismiss_option );
		}

		/**
		 * Prints a warning.
		 *
		 * @param string $message message to display.
		 * @param bool   $dismiss_option if message is dismissable.
		 */
		public function warning( $message, $dismiss_option = false ) {
			$this->notice( 'warning', $message, $dismiss_option );
		}

		/**
		 * Prints a succes-message.
		 *
		 * @param string $message message to display.
		 * @param bool   $dismiss_option if message is dismissable.
		 */
		public function success( $message, $dismiss_option = false ) {
			$this->notice( 'success', $message, $dismiss_option );
		}

		/**
		 * Prints an info.
		 *
		 * @param string $message message to display.
		 * @param bool   $dismiss_option if message is dismissable.
		 */
		public function info( $message, $dismiss_option = false ) {
			$this->notice( 'info', $message, $dismiss_option );
		}

		/**
		 * Adds a notice of a given type.
		 *
		 * @param string $type type of the message to display.
		 * @param string $message message to display.
		 * @param bool   $dismiss_option if message is dismissable.
		 */
		private function notice( $type, $message, $dismiss_option ) {
			$notice                         = new stdClass();
			$notice->message                = $message;
			$notice->dismiss_option         = $dismiss_option;
			$this->admin_notices->{$type}[] = $notice;
		}
	}

}
