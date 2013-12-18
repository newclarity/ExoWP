<?php

/**
 * Class Exo_Implementation
 */
class Exo_Implementation extends Exo_Instance_Base {

  /**
   * @var string Prefix for Class name for child class.
   */
  var $full_prefix = false;

  /**
   * @var bool
   */
  var $short_prefix = false;

  /**
   * @var Exo_Controller_Base Class that "owns" this implementation.
   */
  var $controller_class;

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
   * @param string $dir
   * @param array $args
   */
  function __construct( $dir, $args = array() ) {
    parent::__construct( $args );
    /*
     * Capture the URI for the root of this plugin. Assumes this plugin is in a subdirectory of the site root.
     */
    $this->_dir       = $dir;
    $this->_uri       = home_url( preg_replace( '#^' . preg_quote( ABSPATH ) . '(.*)$#', '$1', $dir ) );

    /**
     * Ensure we are using the right scheme for the incoming URL (http vs. https)
     */
    $this->_uri       = Exo::maybe_adjust_http_scheme( $this->_uri );

    if ( class_exists( 'Exo_Autoloader' ) ) {
      $this->autoloader = new Exo_Autoloader( $this );
    } else {
      $this->require_exo_autoloader();
      $this->autoloader = new Exo_Autoloader( $this );
      $this->register_exo_autoload_dirs();
      $this->require_exo_base_classes();
    }
    $this->add_instance_filter( 'exo_generate_onload' );

  }

  /**
   * @param $onload_code
   *
   * @return array
   */
  function _exo_generate_onload( $onload_code ) {
    return $this->autoloader->generate_onload( $onload_code );
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
   * Load the autoloader.
   * Implemented as a method so it can be overridden in child class if needed.
   */
  function require_exo_autoloader() {
    require(__DIR__ . '/../core/class-autoloader.php');
  }

  /**
   * Make the autoloadered an Exo helper to make it simplier for themers
   * We called register_autoload_dir() directly using Exo_Autoloader for (tiny) performance improvement.
   * Implemented as a method so it can be overridden in child class if needed.
   */
  function register_exo_mvc_autoload_dirs() {
    $autoloader = $this->autoloader;

    $autoloader->register_autoload_dir( __DIR__ . '/../models/post-types', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../models/taxonomies', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../mixins', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../collections', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../views', 'Exo_' );
    // @todo More to come here...

  }

  /**
   * Make the autoloadered an Exo helper to make it simplier for themers
   * We called register_autoload_dir() directly using Exo_Autoloader for (tiny) performance improvement.
   * Implemented as a method so it can be overridden in child class if needed.
   */
  function register_exo_autoload_dirs() {
    $autoloader = $this->autoloader;
    $autoloader->register_autoload_dir( __DIR__ . '/../base', 'Exo_' );
    $autoloader->register_autoload_dir( __DIR__ . '/../helpers', 'Exo_' );
  }

  /**
   * Enable MVC classes.
   * Implemented as a method so it can be overridden in child class if needed.
   */
  function enable_mvc() {
    $this->require_exo_mvc_classes();
    $this->register_exo_mvc_autoload_dirs();
  }

  /**
   * Load the MVC classes.
   * Don't autoload these as we already know we always need these.
   * Implemented as a method so it can be overridden in child class if needed.
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
   * Don't autoload these as we already know we always need these.
   * Implemented as a method so it can be overridden in child class if needed.
   */
  function require_exo_base_classes() {
    /**
     * Load the base class(es) that are always needed
     */
    require(__DIR__ . '/../base/class-helpers-base.php');

    /**
     * Now load the always loaded helper classes
     */
    require(__DIR__ . '/../helpers/-class-helpers.php');
    require(__DIR__ . '/../helpers/-class-meta-helpers.php');
    require(__DIR__ . '/../helpers/-class-post-helpers.php');
    // @todo More to come here...

  }

}
