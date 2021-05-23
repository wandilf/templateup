<?php
/**
 * Listable functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Listable
 */

if ( ! function_exists( 'listable_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function listable_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Listable, use a find and replace
		 * to change 'listable' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'listable', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );


		// Used for Listing Cards
		// Max Width of 450px
		add_image_size( 'listable-card-image', 450, 9999, false );

		// Used for Single Listing carousel images
		// Max Height of 800px
		add_image_size( 'listable-carousel-image', 9999, 800, false );

		// Used for Full Width (fill) images on Pages and Listings
		// Max Width of 2700px
		add_image_size( 'listable-featured-image', 2700, 9999, false );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary'            => esc_html__( 'Primary Menu', 'listable' ),
			'secondary'          => esc_html__( 'Secondary Menu', 'listable' ),
			'search_suggestions' => esc_html__( 'Search Menu', 'listable' ),
			'footer_menu'        => esc_html__( 'Footer Menu', 'listable' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		add_theme_support( 'custom-logo' );

		/*
		 * No support for Post Formats.
		 * See https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support( 'post-formats', array() );

		add_theme_support( 'job-manager-templates' );

		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-lightbox' );

		add_post_type_support( 'page', 'excerpt' );

		remove_post_type_support( 'page', 'thumbnail' );

		// custom javascript handlers - make sure it is the last one added
		add_action( 'wp_head', 'listable_load_custom_js_header', 999 );
		add_action( 'wp_footer', 'listable_load_custom_js_footer', 999 );

		/*
		 * Add editor custom style to make it look more like the frontend
		 * Also enqueue the custom Google Fonts and self-hosted ones
		 */
		add_editor_style( array( 'editor-style.css' ) );

		/**
		 * Enable support for the Style Manager Customizer section (via Customify).
		 */
		add_theme_support( 'customizer_style_manager' );
		add_theme_support( 'style_manager_font_palettes' );
	}
endif; // listable_setup
add_action( 'after_setup_theme', 'listable_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function listable_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'listable_content_width', 1050, 0 );
}
add_action( 'after_setup_theme', 'listable_content_width', 0 );

/**
 * Set the gallery widget width in pixels, based on the theme's design and stylesheet.
 */
function listable_gallery_widget_width( $args, $instance ) {
	return '1050';
}

add_filter( 'gallery_widget_content_width', 'listable_gallery_widget_width', 10, 3 );

/**
 * Enqueue scripts and styles.
 */
function listable_scripts() {
	$theme = wp_get_theme();
	$suffix          = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Add an API key if available in Listings -> Settings Google Maps API Key.
	$google_maps_key = get_option( 'job_manager_google_maps_api_key' );

	// back-compat with the old Listable field Google Maps API Key.
	if ( empty( $google_maps_key ) ) {
		$google_maps_key = pixelgrade_option( 'google_maps_api_key' );
	}

	if ( ! empty( $google_maps_key ) ) {
		$google_maps_key = '&key=' . $google_maps_key;
	} else {
		$google_maps_key = '';
	}

	//if there is no mapbox token use Google Maps instead
	if ( '' == pixelgrade_option( 'mapbox_token', '' ) ) {
		wp_deregister_script('google-maps');
		wp_enqueue_script( 'google-maps', '//maps.google.com/maps/api/js?v=3.exp&amp;libraries=places' . $google_maps_key, array(), '3.22', true );
		$listable_scripts_deps[] = 'google-maps';
	} elseif ( wp_script_is( 'google-maps' ) || listable_using_facetwp() ) {
		wp_deregister_script('google-maps');
		wp_enqueue_script( 'google-maps', '//maps.google.com/maps/api/js?v=3.exp&amp;libraries=places' . $google_maps_key, array(), '3.22', false );
		$listable_scripts_deps[] = 'google-maps';
	}

	wp_deregister_style( 'wc-paid-listings-packages' );
	wp_deregister_style( 'wc-bookings-styles' );

	$main_style_deps = array();

	//only enqueue the de default font if Customify is not present
	if ( ! class_exists( 'PixCustomifyPlugin' ) ) {
		wp_enqueue_style( 'listable-default-fonts', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,600,700' );
		$main_style_deps[] = 'listable-default-fonts';
	}

	wp_register_style( 'chosen', '//cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css', array(), null );
	$main_style_deps[] = 'chosen';

	wp_register_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), null );
	$main_style_deps[] = 'select2';

	wp_enqueue_style( 'listable-style', get_template_directory_uri() . '/style.css', $main_style_deps, $theme->get( 'Version' ) );
	wp_style_add_data( 'listable-style', 'rtl', 'replace' );

	if ( class_exists( 'LoginWithAjax' ) ) {
		wp_enqueue_style( 'listable-login-with-ajax', get_template_directory_uri() . '/login-with-ajax.css', $main_style_deps, $theme->get( 'Version' ) );
	}

	global $post;
	$listable_scripts_deps = array('jquery', 'chosen');
	if ( ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'jobs' ) && true === listable_jobs_shortcode_get_show_map_param( $post->post_content ) )
		     || ( is_single() && 'job_listing' == $post->post_type )
		     || is_search()
	         || ( isset( $post->post_content ) && is_archive() && 'job_listing' == $post->post_type )
		     || is_tax( array( 'job_listing_category', 'job_listing_tag', 'job_listing_region' ) )
		     || ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'submit_job_form' ) )
		) {

		wp_enqueue_script( 'leafletjs', get_template_directory_uri() . '/assets/js/plugins/leaflet.bundle' . $suffix . '.js', array( 'jquery' ), '1.1.0', true );
		$listable_scripts_deps[] = 'leafletjs';
	}

	wp_enqueue_script( 'tween-lite', '//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.5/TweenLite.min.js', array( 'jquery' ) );
	$listable_scripts_deps[] = 'tween-lite';
	wp_enqueue_script( 'scroll-to-plugin', '//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.5/plugins/ScrollToPlugin.min.js', array( 'jquery' ) );
	$listable_scripts_deps[] = 'scroll-to-plugin';
	wp_enqueue_script( 'cssplugin', '//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.5/plugins/CSSPlugin.min.js', array( 'jquery' ) );
	$listable_scripts_deps[] = 'cssplugin';
	wp_register_script( 'chosen', '//cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.js', array( 'jquery' ), null );

	wp_enqueue_script( 'listable-scripts', get_template_directory_uri() . '/assets/js/main' . $suffix . '.js', $listable_scripts_deps, $theme->get( 'Version' ), true );

	if ( is_singular( array( 'post', 'job_listing' ) ) && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_localize_script( 'listable-scripts', 'listable_params', apply_filters( 'listable_js_params', array(
		'login_url' => rtrim( esc_url( wp_login_url() ) , '/'),
		'listings_page_url' => listable_get_listings_page_url(),
		'mapbox' => array(
			'maxZoom' => 19
		),
		'strings' => array(
			'wp-job-manager-file-upload' => esc_html__( 'Add Photo', 'listable' ),
			'no_job_listings_found' => esc_html__( 'No results', 'listable' ),
			'results-no' => esc_html__( 'Results', 'listable'), //@todo this is not quite right as it is tied to the number of results - they can 1 or 0
			'select_some_options' => esc_html__( 'Select Some Options', 'listable' ),
			'select_an_option' => esc_html__( 'Select an Option', 'listable' ),
			'no_results_match' => esc_html__( 'No results match', 'listable' ),
			'social_login_string' => esc_html__( 'or', 'listable' ),
		)
	) ) );

}

add_action( 'wp_enqueue_scripts', 'listable_scripts' );

function listable_admin_scripts() {

	if ( listable_is_edit_page() ) {
		wp_enqueue_script( 'listable-admin-edit-scripts', get_template_directory_uri() . '/assets/js/admin/edit-page.js', array( 'jquery' ), '1.0.0', true );

		if ( get_post_type() === 'job_listing' ) {
			wp_enqueue_style( 'listable-admin-edit-styles', get_template_directory_uri() . '/assets/css/admin/edit-listing.css' );
		} elseif ( get_post_type() === 'page' ) {
			wp_enqueue_style( 'listable-admin-edit-styles', get_template_directory_uri() . '/assets/css/admin/edit-page.css' );
		}
	} else if ( is_post_type_archive( 'job_listing' ) ) {
		wp_enqueue_style( 'listable-admin-edit-styles', get_template_directory_uri() . '/assets/css/admin/edit-listing.css' );
	}

	if ( listable_is_nav_menus_page() ) {
		wp_enqueue_script( 'listable-admin-nav-menus-scripts', get_template_directory_uri() . '/assets/js/admin/edit-nav-menus.js', array( 'jquery' ), '1.0.0', true );
	}

	wp_enqueue_script( 'listable-admin-general-scripts', get_template_directory_uri() . '/assets/js/admin/admin-general.js', array( 'jquery' ), '1.0.0', true );

	$translation_array = array (

	);
	wp_localize_script( 'listable-admin-general-scripts', 'listable_admin_js_texts', $translation_array );
}

add_action( 'admin_enqueue_scripts', 'listable_admin_scripts' );

function listable_login_scripts() {
	wp_enqueue_style( 'listable-custom-login', get_template_directory_uri() . '/assets/css/admin/login-page.css' );
}

add_action( 'login_enqueue_scripts', 'listable_login_scripts' );

/**
 * Load custom javascript set by theme options
 * This method is invoked by wpgrade_callback_themesetup
 * The function is executed on wp_enqueue_scripts
 */
function listable_load_custom_js_header() {
	$custom_js = pixelgrade_option( 'custom_js' );
	if ( ! empty( $custom_js ) ) {
		//first lets test is the js code is clean or has <script> tags and such
		//if we have <script> tags than we will not enclose it in anything - raw output
		if ( strpos( $custom_js, '</script>' ) !== false ) {
			echo $custom_js . "\n";
		} else {
			echo "<script type=\"text/javascript\">\n;(function($){\n" . $custom_js . "\n})(jQuery);\n</script>\n";
		}
	}
}

function listable_load_custom_js_footer() {
	$custom_js = pixelgrade_option( 'custom_js_footer' );
	if ( ! empty( $custom_js ) ) {
		//first lets test is the js code is clean or has <script> tags and such
		//if we have <script> tags than we will not enclose it in anything - raw output
		if ( strpos( $custom_js, '</script>' ) !== false ) {
			echo $custom_js . "\n";
		} else {
			echo "<script type=\"text/javascript\">\n;(function($){\n" . $custom_js . "\n})(jQuery);\n</script>\n";
		}
	}
}

/**
 * Implement the Custom Header feature.
 */
// require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */

require get_template_directory() . '/inc/extras.php';

require get_template_directory() . '/inc/widgets.php';

require get_template_directory() . '/inc/tutorials.php';

require get_template_directory() . '/inc/activation.php';

/**
 * Load various plugin integrations
 */
require get_template_directory() . '/inc/integrations.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Recommended/Required plugins notification
 */
require get_template_directory() . '/inc/required-plugins/required-plugins.php';



// Callback function to insert 'styleselect' into the $buttons array
function listable_mce_buttons( $buttons ) {
	array_unshift( $buttons, 'styleselect' );
	return $buttons;
}
// Register our callback to the appropriate filter
add_filter('mce_buttons_2', 'listable_mce_buttons');

// Callback function to filter the MCE settings
function listable_formats( $init_array ) {
	// Define the style_formats array
	$style_formats = array(
		// Each array child is a format with it's own settings
		array(
			'title' => 'Intro',
			'inline' => 'span',
			'classes' => 'intro',
			'wrapper' => true
		),
		array(
			'title' => 'Two Columns',
			'block' => 'div',
			'classes' => 'twocolumn',
			'wrapper' => true
		),
		array(
			'title' => 'Separator',
			'block' => 'hr',
			'classes' => 'clear'
		),
	);
	// Insert the array, JSON ENCODED, into 'style_formats'
	$init_array['style_formats'] = json_encode( $style_formats );

	return $init_array;

}
// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'listable_formats' );

/* Automagical updates */
function wupdates_check_Kv7Br( $transient ) {
	// First get the theme directory name (the theme slug - unique)
	$slug = basename( get_template_directory() );

	// Nothing to do here if the checked transient entry is empty or if we have already checked
	if ( empty( $transient->checked ) || empty( $transient->checked[ $slug ] ) || ! empty( $transient->response[ $slug ] ) ) {
		return $transient;
	}

	// Let's start gathering data about the theme
	// Then WordPress version
	include( ABSPATH . WPINC . '/version.php' );
	$http_args = array (
		'body' => array(
			'slug' => $slug,
			'url' => home_url( '/' ), //the site's home URL
			'version' => 0,
			'locale' => get_locale(),
			'phpv' => phpversion(),
			'child_theme' => is_child_theme(),
			'data' => null, //no optional data is sent by default
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' )
	);

	// If the theme has been checked for updates before, get the checked version
	if ( isset( $transient->checked[ $slug ] ) && $transient->checked[ $slug ] ) {
		$http_args['body']['version'] = $transient->checked[ $slug ];
	}

	// Use this filter to add optional data to send
	// Make sure you return an associative array - do not encode it in any way
	$optional_data = apply_filters( 'wupdates_call_data_request', $http_args['body']['data'], $slug, $http_args['body']['version'] );

	// Encrypting optional data with private key, just to keep your data a little safer
	// You should not edit the code bellow
	$optional_data = json_encode( $optional_data );
	$w=array();$re="";$s=array();$sa=md5('0ab162a893ad8d7cfce46a6569d63b4a1203aeb9');
	$l=strlen($sa);$d=$optional_data;$ii=-1;
	while(++$ii<256){$w[$ii]=ord(substr($sa,(($ii%$l)+1),1));$s[$ii]=$ii;} $ii=-1;$j=0;
	while(++$ii<256){$j=($j+$w[$ii]+$s[$ii])%255;$t=$s[$j];$s[$ii]=$s[$j];$s[$j]=$t;}
	$l=strlen($d);$ii=-1;$j=0;$k=0;
	while(++$ii<$l){$j=($j+1)%256;$k=($k+$s[$j])%255;$t=$w[$j];$s[$j]=$s[$k];$s[$k]=$t;
		$x=$s[(($s[$j]+$s[$k])%255)];$re.=chr(ord($d[$ii])^$x);}
	$optional_data=bin2hex($re);

	// Save the encrypted optional data so it can be sent to the updates server
	$http_args['body']['data'] = $optional_data;

	// Check for an available update
	$url = $http_url = set_url_scheme( 'https://wupdates.com/wp-json/wup/v1/themes/check_version/Kv7Br', 'http' );
	if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {
		$url = set_url_scheme( $url, 'https' );
	}

	$raw_response = wp_remote_post( $url, $http_args );
	if ( $ssl && is_wp_error( $raw_response ) ) {
		$raw_response = wp_remote_post( $http_url, $http_args );
	}
	// We stop in case we haven't received a proper response
	if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
		return $transient;
	}

	$response = (array) json_decode($raw_response['body']);
	if ( ! empty( $response ) ) {
		// You can use this action to show notifications or take other action
		do_action( 'wupdates_before_response', $response, $transient );
		if ( isset( $response['allow_update'] ) && $response['allow_update'] && isset( $response['transient'] ) ) {
			$transient->response[ $slug ] = (array) $response['transient'];
		}
		do_action( 'wupdates_after_response', $response, $transient );
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'wupdates_check_Kv7Br' );

function wupdates_add_id_Kv7Br( $ids = array() ) {
	// First get the theme directory name (unique)
	$slug = basename( get_template_directory() );

	// Now add the predefined details about this product
	// Do not tamper with these please!!!
	$ids[ $slug ] = array( 'name' => 'Listable', 'slug' => 'listable', 'id' => 'Kv7Br', 'type' => 'theme', 'digest' => '4c004db72b5605c95eaeb22966e2de89', );

	return $ids;
}
add_filter( 'wupdates_gather_ids', 'wupdates_add_id_Kv7Br', 10, 1 );

// Adds login buttons to the wp-login.php pages
function add_wc_social_login_buttons_wplogin() {

	// Displays login buttons to non-logged in users + redirect back to login
	if(function_exists("woocommerce_social_login_buttons")) {
		$redirect = null;
		if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
			$redirect = get_permalink( get_option('woocommerce_myaccount_page_id') );
		}
		woocommerce_social_login_buttons( $redirect );
	}

}
add_action( 'login_form', 'add_wc_social_login_buttons_wplogin' );
add_action( 'register_form', 'add_wc_social_login_buttons_wplogin' );

// Changes the login text from what's set in our WooCommerce settings so we're not talking about checkout while logging in.
function change_social_login_text_option( $login_text ) {

	global $pagenow;

	// Only modify the text from this option if we're on the wp-login page
	if( 'wp-login.php' === $pagenow ) {
		// Adjust the login text as desired
		$login_text = esc_html__( 'You can also create an account with a social network.', 'listable' );
	}

 	return $login_text;
}
add_filter( 'pre_option_wc_social_login_text', 'change_social_login_text_option' );

//Enqueue WPJM core's frontend style
add_filter( 'job_manager_enqueue_frontend_style', '__return_true' );
