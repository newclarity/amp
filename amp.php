<?php
/**
 * Plugin Name: NewClarity AMP
 * Description: Google AMP plugin for Professional Publishers and Enterprise Digital Media using WordPress. Optimized for enterprise development and deployment best practices.
 */
namespace NewClarity;

use wp;
use Exception;

class AMP {

	const VERSION = '1.0';

	/**
	 * @var self
	 */
	public $javascript_domain = 'cdn.ampproject.org';

	/**
	 * @var self
	 */
	private static $_instance;

	/**
	 * @var string
	 */
	public $query_var = 'amp';

	/**
	 * @var string
	 */
	public $url_slug = 'amp';

	/**
	 * @var bool
	 */
	public $add_amphtml_link = true;

	/**
	 * @var string
	 */
	public $amphtml_url;

	/**
	 * @var string
	 */
	public $amp_version = 'v0';

	/**
	 * @var bool
	 */
	private $is_amp_request = false;

	/**
	 * @todo Add in all the valid elements
	 *
	 * @var string[]
	 */
	private $_valid_amp_elements = array(
		'amp-iframe' => true,
		'amp-img'    => true,
	);

	/**
	 * Wire up a `after_setup_theme` hook so a plugin or
	 * theme can deactivate this if and when desired.
	 */
	static function on_load() {
		require( __DIR__ . '/includes/functions.php' );
		require( __DIR__ . '/includes/autoload.php' );
		self::$_instance = new self();
		add_action( 'setup_theme', array( self::$_instance, '_setup_theme' ) );
	}

	/**
	 * Checks to see if URL path begins with /amp
	 *
	 * @note: Respects any subdirectory built into the root,
	 * e.g. If home_url() === 'example.com/blog' then AMP URL is 'example.com/blog/amp'
	 */
	function _setup_theme() {
		/**
		 * @var wp $wp
		 */
		global $wp;

		$path = Util::site_directory();

		if ( preg_match( "#^{$path}/{$this->url_slug}(.+)$#", $_SERVER[ 'REQUEST_URI' ], $match ) ) {
			$_SERVER[ 'REQUEST_URI' ] = "{$path}{$match[ 1 ]}";
			$this->is_amp_request = true;
			$wp->set_query_var( $this->query_var, true );
			add_action( 'wp', array( $this, '_wp' ) );
			add_action( 'stylesheet_directory', array( $this, '_stylesheet_directory' ), 10, 3 );

			ob_start();
			add_action( 'shutdown', array( $this, '_shutdown_1' ), 1 );
			/**
			 * Make sure `wp_ob_end_flush_all` is called after $this->_shutdown_1
			 */
			remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
			add_action(  'shutdown', 'wp_ob_end_flush_all', 1 );

		}

	}

	/**
	 * Wire up the appropriate hooks for AMP pages and non-AMP pages
	 */
	function _shutdown_1() {
		$html = ob_get_clean();
		// Convert to AMP here
		echo $html;
	}

	/**
	 * Wire up the appropriate hooks for AMP pages and non-AMP pages
	 */
	function _wp() {

		do {

			if ( is_feed() ) {
				break;
			}

			if ( ! $this->is_amp_request ) {

				/**
				 * Emit <link rel="amphtml" href="..." /> into <head>
				 */
				add_action( 'wp_head', array( $this, '_wp_head_11' ), 11 );

			} else {

				/**
				 * @todo Add hooks for all template_hierarchies here
				 */
				add_action( 'category_template_hierarchy', array( $this, '_template_hierarchy' ) );
				add_action( 'single_template_hierarchy', array( $this, '_template_hierarchy' ) );

			}

		} while ( false );

	}

	/**
	 * Filters the stylesheet directory path for current theme.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_dir Absolute path to the current theme.
	 * @param string $stylesheet     Directory name of the current theme.
	 * @param string $theme_root     Absolute path to themes directory.
	 * @return string
	 *
	 */
	function _stylesheet_directory( $stylesheet_dir, $stylesheet, $theme_root ) {
		if ( $this->is_amp_request ) {
			$stylesheet_dir .= "/{$this->url_slug}";
		}
		return $stylesheet_dir;
	}

	/**
	 *
	 */
	function _wp_head_11() {
		if ( $this->add_amphtml_link ) {
			$amphtml_url = esc_url( $this->amphtml_url() );
			printf( '%s<link rel="amphtml" href="%s" />%s', "\n", $amphtml_url, "\n" );
		}
	}

	/**
	 * Insert /amp/ templates into the template hierarchy for /amp requests
	 *
	 * @param string[] $templates
	 *
	 * @return string[]
	 */
	function _template_hierarchy( $templates ) {

		if ( $this->is_amp_request ) {
			$amp_templates = array();
			foreach( $templates as $template ) {
				$amp_templates[] = "{$this->url_slug}/{$template}";
			}
			$templates = array_merge( $amp_templates, $templates );
		}
		return $templates;

	}

	/**
	 * @param string $element_name
	 *
	 * @throws Exception
	 */
	function check_amp_element( $element_name ) {
		if ( ! isset( $this->_valid_amp_elements[ $element_name ] ) ) {
			$amp_elements = implode( ', ', array_keys( $this->_valid_amp_elements ) );
			$message = sprintf( '%s is not a valid AMP element name. Please select one of: %s', $element_name, $amp_elements );
			throw new Exception( $message );
		};
	}

	/**
	 * Register an AMP element that the plugin does not yet support.
	 *
	 * To be used when Google adds a new element before we implement support for said element.
	 *
	 * @param string $element_name
	 * @param string $class_path
	 *
	 */
	function register_amp_element( $element_name, $class_path ) {
		do {

			if ( $element_name !== strtolower( preg_replace( '#[^\da-z]$#i', '', $element_name ) ) ) {
				break;
			}
			if ( ! preg_match( '#^amp-#', $element_name ) ) {
				break;
			}

			$this->_valid_amp_elements[ $element_name ] = $class_path;

		} while ( false );

		$this->check_amp_element( $element_name );

	}

	/**
	 * @return string
	 */
	function amphtml_url() {
		do {
			if ( isset( $this->amphtml_url ) ) {
				break;
			}

			/**
			 * Get leading slashed subdir, or empty string
			 *
			 * @example `/blog`
			 */
			$subdir = Util::site_directory();

			/**
			 * Strip off $subdir if exists
			 */
			$request_uri = ! empty( $subdir )
				? preg_replace( '#^' . preg_quote( $subdir ) . '(.+)$#', '$1', $_SERVER[ 'REQUEST_URI' ] )
				: $_SERVER[ 'REQUEST_URI' ];


			list( $path, $query ) = explode( '?', "{$request_uri}?" );

			$path = trim( $path, '/' );
			$host = parse_url( home_url(), PHP_URL_HOST );

			$this->amphtml_url = "https://{$host}{$subdir}/{$this->url_slug}/{$path}/";
			if ( ! empty( $query ) ) {
				$this->amphtml_url .= "?{$query}";
			}
			$this->amphtml_url = apply_filters( 'samp:amphtml_url', $this->amphtml_url );
		} while ( false );
		return $this->amphtml_url;
	}

	/**
	 * @return self
	 */
	static function instance() {
		return self::$_instance;
	}

	/**
	 * @return string
	 */
	function query_var() {
		return $this->query_var;
	}

}
AMP::on_load();
