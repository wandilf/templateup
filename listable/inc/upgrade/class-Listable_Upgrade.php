<?php
/**
 * Document for class Listable_Upgrade.
 *
 * @package Listable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to do upgrades if necessary.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 */
class Listable_Upgrade {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Listable_Upgrade
	 * @access  protected
	 */
	protected static $_instance = null;

	/**
	 * Theme Slug.
	 * @var     string
	 * @access  public
	 */
	public $theme_slug;

	/**
	 * Theme Name.
	 * @var     string
	 * @access  public
	 */
	public $theme_name;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 */
	public $theme_version;

	/**
	 * New versions installed by this upgrade
	 * @var     array
	 * @access  private
	 */
	private $new_versions;


	/**
	 * Constructor function.
	 * @access  public
	 * @param string $slug The theme slug, used for prefixing
	 * @param string $version The current theme version, after the upgrade.
	 * @param string $name Optional. The theme name.
	 * @return  void
	 */
	public function __construct( $slug, $version, $name = '' ) {

		$this->theme_slug = $slug;
		$this->theme_version = $version;
		$this->theme_name = $name;

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'upgrade' ), 5 );
	}

	/**
	 * Check theme version and do any necessary actions if new version has been installed.
	 */
	public function upgrade() {

		// Make sure the upgrade routines class is available.
		require_once( trailingslashit( get_template_directory() ) . 'inc/upgrade/class-Listable_Upgrade_Routines.php' );

		$upgradeOk    = true;
		$savedVersion = $this->get_version_saved();
		$newVersion   = $this->theme_version;
		$new_versions = array();

		// If the two versions are equal, there is not much to do.
		if ( $savedVersion === $newVersion ) {
			return;
		}

		// If the current version is smaller than the previous one, save it and don't do anything.
		if ( $this->is_version_less_than( $newVersion, $savedVersion ) ) {
			$this->save_version_number();
			return;
		}

		if ( $this->is_version_less_than( $savedVersion, $newVersion ) ) {
			$new_versions[] = $newVersion;

			define( 'PXG_THEME_DOING_UPGRADE', true );
			$upgrade_routines = new Listable_Upgrade_Routines( $savedVersion, $newVersion, trailingslashit( get_template_directory() ) . 'inc/upgrade/migrations');
			$upgrade_routines->run();
		}

		// Post-upgrade, display notices and save the new version in the options.
		if ( $upgradeOk && ! empty( $new_versions ) ) {
			$this->new_versions = $new_versions;
			add_action( 'admin_notices', array( $this, 'notice_new_version' ) );
			$this->save_version_number();
		}

	}

	/**
	 * Compares version numbers and determines if the result is less than zero.
	 *
	 * @param  string $version1 A version string such as '1', '1.1', '1.1.1', '2.0', etc.
	 * @param  string $version2 A version string such as '1', '1.1', '1.1.1', '2.0', etc.
	 *
	 * @return bool true if version_compare of $versions1 and $version2 shows $version1 as earlier.
	 */
	public function is_version_less_than( $version1, $version2 ) {
		return ( version_compare( $version1, $version2 ) < 0 );
	}

	/**
	 * Provide a useful error message if the theme has been updated.
	 */
	public function notice_new_version() {

		foreach ( $this->new_versions as $new_version ) {
			echo '<div class="notice notice-success is-dismissible"><p>' .
			     sprintf( __( 'Your <strong>%s</strong> theme has been updated to <strong>version %s.</strong> Enjoy!', 'listable' ), $this->theme_name, $new_version ) .
			     '</p></div>';
		}
	}

	/**
	 * Get the version string in the options. This is useful if you install upgrade and
	 * need to check if an older version was installed to see if you need to do certain
	 * upgrade housekeeping.
	 *
	 * @access  public
	 *
	 * @return null
	 */
	public function get_version_saved() {

		return get_option( $this->theme_slug . '_theme_version', '0.0.1' );
	}

	/**
	 * Save the theme version number.
	 * @access  public
	 * @return  void
	 */
	public function save_version_number() {
		update_option( $this->theme_slug . '_theme_version', $this->theme_version, false );
	}

	public function debug( $what ) {
		echo '<pre style="margin-left: 160px">';
		var_dump( $what );
		echo '</pre>';
	}

	/**
	 * Main Listable_Upgrade Instance
	 *
	 * Ensures only one instance of Listable_Upgrade is loaded or can be loaded.
	 *
	 * @static
	 *
	 * @param string $slug
	 * @param string $version
	 * @param string $name Optional.
	 *
	 * @return Listable_Upgrade Main Listable_Upgrade instance
	 */
	public static function instance( $slug, $version, $name = '' ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $slug, $version, $name );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'listable' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'listable' ),  null );
	}
}
