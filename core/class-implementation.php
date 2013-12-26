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
  private $_helper_callables = array();

  /**
   * @var bool|array
   */
  private $_helper_class_files = false;

  /**
   * @var array
   */
  private $_required_files = array();

  /**
   * @return array
   */
  static function HOOKS() {
    return array(
      array( 'add_static_action', 'exo_scan_class' ),
      array( 'add_static_action', 'exo_init' ),
    );
  }

  /**
   * @param string $main_class
   * @todo Add code to initialize using on-load functionality for is_dev_mode() === false.
   */
  function __construct( $main_class ) {
    parent::__construct();

    $this->main_class = $main_class;

    $this->_dir = _Exo_Helpers::get_class_dir( $main_class );

    $this->full_prefix = _Exo_Helpers::get_class_declaration( 'FULL_PREFIX', $main_class );
    if ( ! $this->full_prefix ) {
      $this->full_prefix ="{$main_class}_";
    }

    $this->short_prefix = _Exo_Helpers::get_class_declaration( 'SHORT_PREFIX', $main_class );
    if ( ! $this->short_prefix ) {
      $this->short_prefix = strtolower( $this->_get_capital_letters( $main_class ) ) . '_';
    }

    if ( $autoloader_class = _Exo_Helpers::get_class_declaration( 'AUTOLOADER', $main_class ) ) {
      $this->autoloader = new $autoloader_class( $this );
    } else {
      $this->autoloader = new Exo_Autoloader( $this );
    }

    $this->_required_files = _Exo_Helpers::get_class_declaration( 'REQUIRED_FILES', $main_class, array() );

    $this->register_autoload_paths( _Exo_Helpers::get_class_declaration( 'AUTOLOAD_PATHS', $main_class, array() ) );

    /*
     * Capture the URI for the root of this plugin. Assumes this plugin is in a subdirectory of the site root.
     */
    $this->_uri = home_url( preg_replace( '#^' . preg_quote( ABSPATH ) . '(.*)$#', '$1', $this->_dir ) );

    /**
     * Ensure we are using the right scheme for the incoming URL (http vs. https)
     */
    $this->_uri = _Exo_Helpers::maybe_adjust_http_scheme( $this->_uri );

    /**
     * Schedule to call _exo_register_helpers defined in this class but also
     * allow someone else to add helpers to this implementation, if needed.
     */
    $this->add_instance_filter( 'exo_register_helpers' );

  }
  /**
   * @return bool
   */
  function short_prefix() {
    return $this->short_prefix;
  }

  /**
   * @return bool
   */
  function full_prefix() {
    return $this->full_prefix;
  }

  /**
   */
  static function _exo_init() {
    self::_fixup_mixins();
  }

  /**
   * Scan the list of $classes from get_declared_classes() and register it's POST_TYPE constant, if one exists
   *
   * @param string $owner_class
   * @note All classes must be loaded to call this.
   */
  static function _exo_scan_class( $owner_class ) {
    if ( $mixins = _Exo_Helpers::get_class_constant( 'MIXINS', $owner_class, array() ) ) {
      if ( is_string( $mixins ) ) {
        $mixins = explode( ',', $mixins );
      }
      $mixins = array_map( 'trim', $mixins );
      foreach( $mixins as $mixin_class ) {
        if ( class_exists( $mixin_class ) ) {
          Exo_Instance_Base::add_class_mixin( $owner_class, $mixin_class );
        }
      }
    }
  }
  /**
   * Return only the capital letters in a string
   *
   * @param $string
   *
   * @return bool|string
   */
  private function _get_capital_letters( $string ) {
    return preg_match_all( '#([A-Z]+)#', $string, $matches ) ? implode( $matches[1] ) : false;
  }

  /**
   * Registers all the helper class files for this implementation.
   *
   * @param array $helper_class_files
   * @return array
   */
  function _exo_register_helpers( $helper_class_files ) {
    foreach ( glob( $this->dir( '/helpers/*.php' ) ) as $filepath ) {
      $class_name = $this->derive_class_name( $filepath );
      $helper_class_files[$class_name] = realpath( $filepath );
    }
    return $helper_class_files;
  }

  /**
   * Registers all the helper classes for this implementation.
   * Called by 'init' hook priority 1 in Exo_Main_Base
   * @param array
   */
  function _register_helpers( $helper_class_files ) {
    if ( ! $this->_helper_class_files ) {
      $this->_helper_class_files = Exo::is_dev_mode() ? $helper_class_files : true;
      foreach ( $helper_class_files as $class_name => $filepath ) {
        $this->register_helper( $class_name, $filepath );
      }
    } else {
      $message = __( '%s::%s cannot be called more than once.', 'exo' );
      Exo::trigger_warning( $message, $this->main_class, __FUNCTION__ );
    }
  }

  /**
   *
   */
  function autoload_all() {
    $this->autoloader->autoload_all();
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
   *
   */
  function _record_autoload_class_filepaths() {
    $this->autoloader->_record_class_filepaths();
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
  function _fixup_registered_helpers() {
    foreach( $this->_helpers as $helper_class => $filepath ) {
      $helper_class::$main_class = $this->main_class;
      $args = array(
        'public' => true,
        'own' => true,
        'internal' => true,
      );
      foreach ( _Exo_Helpers::get_class_methods( $helper_class, $args ) as $method_name ) {
        $this->_helper_callables[$method_name] = array( $helper_class, $method_name );
      }
    }
    /**
     * Clear this vars' memory. We don't need it anymore.
     */
    $this->_helpers = array();
  }

  /**
   * Unregister a Helper Class for the Main class.
   *
   * @param string $class_name
   * @param bool|string $filepath
   */
  function register_helper( $class_name, $filepath = false ) {
    if ( ! $filepath ) {
      $filepath = _Exo_Helpers::get_class_filepath( $class_name );
    }
    $this->_helpers[$class_name] = $filepath;
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
   * @param string $path
   * @param bool|string $prefix
   * @return Exo_Implementation
   * @todo Replace register_autoload_dir() with register_autoload_path() cmpletely.
   */
  function register_autoload_path( $path, $prefix = false ) {
    $dir = isset( $path[0] ) && '/' == $path[0] ? $path : $this->dir( $path );
    if ( false === $prefix ) {
      $prefix = $this->full_prefix;
    }
    $this->autoloader->register_autoload_dir( $dir, $prefix );
    return $this;
  }

  /**
   * @param string $filepath
   * @return Exo_Implementation
   */
  function require_file( $filepath ) {
    $filepath = isset( $filepath[0] ) && '/' == $filepath[0] ? $filepath : $this->dir( $filepath );
    $this->_required_files[] = $filepath;
    require( $filepath );
    return $this;
  }

  /**
   * @param $filepaths
   *
   * @return $this
   */
  function require_files( $filepaths ) {
    foreach( $filepaths as $filepath ) {
      $this->require_file( $filepath );
    }
    return $this;
  }

  /**
   *
   */
  function _load_required_files() {
    foreach( $this->_required_files as $index => $filepath ) {
      if ( '/' != $filepath[0] ) {
        $this->_required_files[$index] = $filepath = $this->dir( $filepath );
      }
      if ( class_exists( $this->derive_class_name( $filepath ) ) ) {
        unset( $this->_required_files[$index] );
        continue;
      }
      if ( is_file( $filepath ) ) {
        require( $filepath );
      }
    }
  }

  /**
   * @param $autoload_paths
   * @param bool $prefix
   *
   * @return $this
   */
  function register_autoload_paths( $autoload_paths, $prefix = false ) {
    if ( false === $prefix ) {
      $prefix = $this->full_prefix;
    }
    foreach( $autoload_paths as $autoload_path ) {
      $this->register_autoload_path( $autoload_path, $prefix );
    }
    return $this;
  }

}
