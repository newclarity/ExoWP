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
   * @var Exo_Main_Base Class that "owns" this implementation.
   */
  var $main_class;

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
    }
    $this->add_instance_action( 'exo_bypass_onload_file' );
    $this->add_instance_filter( 'exo_onload_snippets' );
    $this->add_instance_action( 'exo_autoloader_classes' );

  }

  /**
   * Action hook attached in Exo_Main_Base
   */
  function _shutdown() {
    $this->generate_onload_file();
  }

  /**
   *
   */
  function generate_onload_file() {
    $new_code = implode( "\n", $this->apply_instance_filters( 'exo_onload_snippets', array( "<?php\n" ), $this ) );
    if ( $new_code != $this->get_onload_code() ) {
      $this->put_onload_code( $new_code );
    }
  }

  /**
   *
   */
  function autoload_all() {
    $this->autoloader->autoload_all();
  }

  /**
   * @return string
   */
  function get_onload_filepath() {
    return $this->dir( '/on-load.php' );
  }

  /**
   * @return string
   */
  function get_onload_code() {
    return Exo::get_file_contents( $this->get_onload_filepath() );
  }

  /**
   * @param string $onload_code
   * @return string
   */
  function put_onload_code( $onload_code ) {
    return Exo::put_file_contents( $this->get_onload_filepath(), $onload_code );
  }

  /**
   * Action hook to fire when runmode=='dev' to bypass the /on-load.php file.
   *
   * This method registers all the helper classes for this implementation.
   * @return array
   */
  function _exo_bypass_onload_file() {
    $this->_register_helpers();
    $this->autoloader->load_onload_filepaths();
  }

  /**
   * Registers all the helper classes for this implementation.
   *
   * @return array
   */
  private function _register_helpers() {
    foreach ( glob( $this->dir( '/helpers/*.php' ) ) as $filepath ) {
      $this->register_helper( $this->derive_class_name( realpath( $filepath ) ) );
    }
  }

  /**
   * Filter hook that adds to the array of PHP snippets that will be added to /on-load.php for this implementation.
   *
   * This function adds the contents of all the *.on-load.php files contained in the autoload directories.
   *
   * @param array $onload_snippets
   *
   * @return array
   */
  function _exo_onload_snippets( $onload_snippets ) {
    return array_merge(
      $onload_snippets,
      $this->autoloader->get_onload_snippets( $this->dir() ),
      $this->get_helper_onload_snippets()
    );

  }

  function _exo_autoloader_classes() {
    $this->autoloader->register_autoloader_classes();
  }

  /**
   * @return array
   */
  function get_helper_onload_snippets() {
    $helper_onload_snippets = array();
    foreach ( glob( $this->dir( '/helpers/*.php' ) ) as $filepath ) {
      $filepath = realpath( $filepath );
      $class_name = $this->derive_class_name( $filepath );
      $helper_onload_snippets[] = "{$this->main_class}::register_helper( '{$class_name}' );";
    }
    return $helper_onload_snippets;
  }

  /**
   * Derives a classname given a filepath to the class.
   *
   * Follows Exo's rules for converting between class name and filename.
   *
   * Delegates to it's autoloader but passes the Full Prefix for this implementation's class.
   *
   * @param $filepath
   *
   * @return mixed
   */
  function derive_class_name( $filepath ) {
    return $this->autoloader->derive_class_name( $filepath, $this->full_prefix );
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
      $class_name::$main_class = $this->main_class;
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
    $this->_helpers = array();

    do_action( 'exo_after_helper_fixup', $this->main_class );
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


}
