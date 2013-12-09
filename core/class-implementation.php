<?php

/**
 * Class Exo_Implementation
 */
class Exo_Implementation extends Exo_Instance_Base {

  /**
   * @var string Prefix for Class name for child class.
   */
  var $class_prefix = false;

  /**
   * @var Exo_Autoloader
   */
  var $autoloader;

  /**
   * @var string Directory for this entity.
   */
  private $_dir;

  /**
   * @var string URL for the top directory for this plugin.
   */
  private $_uri;

  /**
   * @var string Directory for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   */
  private $_theme_dir;

  /**
   * @var string URL for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   */
  private $_theme_uri;

    /**
   * @var string Target environment, must be one of 'dev', 'test', 'stage' or 'live.'
   *             Defaults to 'live' because that's the safest default.
   */
  private $_runmode = 'live';

  /**
   * @var array
   */
  private $_helpers = array();

  /**
   * @var array
   */
  private $_helper_instances = array();

  /**
   * @var array
   */
  private $_helper_callables = array();

  /**
   *
   */
  function __construct( $dir ) {

    if ( defined( 'EXO_RUNMODE' ) ) {
      /*
       * This is a fallback so it can be set when using require( 'wp-load.php' );
       */
      $this->set_runmode( EXO_RUNMODE );
    }

    /*
     * Capture the URI for the root of this plugin. Assumes this plugin is in a subdirectory of the site root.
     */
    $this->_uri       = home_url( preg_replace( '#^' . preg_quote( ABSPATH ) . '(.*)$#', '$1', $dir ) );
    $this->_dir       = $dir;
    $this->_theme_dir = get_stylesheet_directory();
    $this->_theme_uri = get_stylesheet_directory_uri();

    /**
     * Ensure we are using the right scheme for the incoming URL (http vs. https)
     */
    $this->_uri       = $this->maybe_adjust_http_scheme( $this->_uri );
    $this->_theme_uri = $this->maybe_adjust_http_scheme( $this->_theme_uri );

    if ( ! class_exists( 'Exo_Autoloader' ) ) {
      $this->require_exo_autoloader();
      $this->require_exo_base_classes();
      $this->register_exo_autoload_dirs();
    }

  }

  /**
   * Align the HTTP scheme (SSL vs. non SSL) to be consistent with incoming URL.
   *
   * @param $url
   *
   * @return mixed
   */
  function maybe_adjust_http_scheme( $url ) {
    $scheme = is_ssl() ? 'https' : 'http';

    return preg_replace( '#^https?://#', "{$scheme}://", $url );
  }

  /**
   * Returns the URL for the top directory for this plugin.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   *
   * @return string
   */
  function uri( $path = false ) {
    return $path ? "{$this->_uri}/" . ltrim( $path, '/' ) : $this->_uri;
  }

  /**
   * Returns the directory for the top directory for this plugin.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   *
   * @return string
   */
  function dir( $path = false ) {
    return $path ? "{$this->_dir}/" . ltrim( $path, '/' ) : $this->_dir;
  }

  /**
   * Returns the directory for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   *
   * @return string
   */
  function theme_dir( $path = false ) {
    return $path ? "{$this->_theme_dir}/" . ltrim( $path, '/' ) : $this->_theme_dir;
  }

  /**
   * Returns the URI/URL for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   *
   * @return string
   */
  function theme_uri( $path = false ) {
    return $path ? "{$this->_theme_uri}/" . ltrim( $path, '/' ) : $this->_theme_uri;
  }

  /**
   * Fixup the registered helpers after the theme loads but before the default priority 10 hook after_setup_theme.
   */
  function fixup_registered_helpers() {
    foreach( $this->_helpers as $helper ) {
      list( $class_name, $method_name, $alt_method_name ) = $helper;
      if ( is_object( $class_name ) ) {
        $instance = $class_name;
        $this->_helper_instances[$class_name = get_class( $instance )] = $instance;
      } else if ( ! isset( $this->_helper_instances[$class_name]) ) {
        $this->_helper_instances[$class_name] = $instance = new $class_name();
      } else {
        $instance = $this->_helper_instances[$class_name];
      }
      if ( $method_name ) {
        if ( $alt_method_name ) {
          $this->_helper_callables[$method_name] = array( $instance, $method_name );
        } else {
          $this->_helper_callables[$alt_method_name] = array( $instance, $method_name );
        }
      } else {
        foreach ( _Exo_Helpers::get_class_methods( $class_name, array( 'public' => true ) ) as $method_name ) {
          $this->_helper_callables[$method_name] = array( $instance, $method_name );
        }
      }
    }
    /**
     * Clear this vars' memory. We don't need it anymore.
     */
    $this->_helpers = null;
  }

  /**
   * Unregister a Helper Class or Class Method for the App object.
   *
   * @param string|object $class_name
   * @param bool|string $method_name
   * @param bool|string $alt_method_name
   */
  function register_helper( $class_name, $method_name = false, $alt_method_name = false ) {
    $this->_helpers[] = array( $class_name, $method_name, $alt_method_name );
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  function has_method( $method_name ) {
    return method_exists( __CLASS__, $method_name ) || isset( $this->_helper_callables[$method_name] );
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  function has_helper_callable( $method_name ) {
    return isset( $this->_helper_callables[$method_name] );
  }

  /**
   * @param string $method_name
   *
   * @return bool|callable
   */
  function get_helper_callable( $method_name ) {
    return isset( $this->_helper_callables[$method_name] ) ? $this->_helper_callables[$method_name] : false;
  }

  /**
   * Load the autoloader. Implmented as a method so it can be overridden in child class.
   */
  function require_exo_autoloader() {
    require(__DIR__ . '/../core/class-autoloader.php');
    $this->autoloader = new Exo_Autoloader( $this );
  }

  /**
   * Make the autoloadered an Exo helper to make it simplier for themers
   * We called register_autoload_dir() directly using Exo_Autoloader for (tiny) performance improvement.
   */
  function register_exo_mvc_autoload_dirs() {
    $autoloader = $this->autoloader;
    $autoloader->register_autoload_subdir( __DIR__ . '/../models', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../mixins', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../collections', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../views', 'Exo_' );
  }

  /**
   * Make the autoloadered an Exo helper to make it simplier for themers
   * We called register_autoload_dir() directly using Exo_Autoloader for (tiny) performance improvement.
   */
  function register_exo_autoload_dirs() {
    $autoloader = $this->autoloader;
    $autoloader->register_autoload_dir( __DIR__ . '/../base', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../helpers', 'Exo_' );
  }

  function enable_mvc() {
    $this->require_exo_mvc_classes();
    $this->register_exo_mvc_autoload_dirs();
  }

  /**
   * Load the MVC classes.
   * Don't use autoloader if we know we need these.
   */
  function require_exo_mvc_classes() {

    require(__DIR__ . '/../base/class-mixin-base.php');
    require(__DIR__ . '/../base/class-model-base.php');
    require(__DIR__ . '/../base/class-collection-base.php');
    require(__DIR__ . '/../base/class-view-base.php');
    require(__DIR__ . '/../base/class-post-base.php');
  }

  /**
   * Load the core classes for Exo, ones that Exo cannot otherwise function without.
   * Don't use autoloader because we always need these.
   */
  function require_exo_base_classes() {
    /**
     * Load the base class(es) that are always needed
     */
    require(__DIR__ . '/../base/class-helpers-base.php');

    /**
     * Now load the always loaded helper classes
     */
    require(__DIR__ . '/../helpers/class-helpers.php');

  }

  /**
   * Returns true if a Development Deployment.
   *
   * @return string
   */
  function is_dev_mode() {
    return 'dev' == $this->_runmode;
  }

  /**
   * Returns true if a Testing Deployment.
   *
   * @return string
   */
  function is_test_mode() {
    return 'test' == $this->_runmode;
  }

  /**
   * Returns true if a Staging Deployment.
   *
   * @return string
   */
  function is_stage_mode() {
    return 'stage' == $this->_runmode;
  }

  /**
   * Returns true if a Live Deployment, i.e. Production.
   *
   * @return string
   */
  function is_live_mode() {
    return 'live' == $this->_runmode;
  }
    // @todo More to come here...

  /**
   * Returns the Run Mode, one of: 'dev', 'test', 'stage' or 'live.'
   *
   * @return string
   */
  function get_runmode() {
    return $this->_runmode;
  }

  /**
   * @param $runmode
   *
   * @throws Exception
   */
  function set_runmode( $runmode ) {
    if ( ! WP_DEBUG ) {
      $this->_runmode = strtolower( $runmode );
    } else {
      switch ( $runmode ) {
        case 'dev':
        case 'test':
        case 'stage':
        case 'live':
          $this->_runmode = strtolower( $runmode );
          break;
        default:
          $message = __( 'ERROR: Neither Exo nor any of it\'s helper classes have the method %s().', 'exo' );
          trigger_error( sprintf( $message, $method_name ), E_USER_WARNING );
          break;
      }
    }
  }
}
