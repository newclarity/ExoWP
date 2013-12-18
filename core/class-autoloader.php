<?php

/**
 * Class Exo_Autoloader
 *
 * @property Exo_Implementation $owner
 */
class Exo_Autoloader extends Exo_Base {

  /**
   * @var array List of registered directories in which classes can be found.
   */
  private $_autoload_dirs = array();

  /**
   * @var array List of classes to autoload and their corresponding filepath.
   */
  private $_autoload_classes = array();

  /**
   * @var array
   */
  private $_onload_filepaths = array();

  /**
   *
   */
  function __construct( $owner ) {
    $this->owner = $owner;

    spl_autoload_register( array( $this, '_autoload' ) );

    /**
     * Add hooks for this class
     */
    add_action( 'init', array( $this, '_init_9' ), 9 );
    add_action( 'exo_autoloader_classes', array( $this, '_exo_autoloader_classes' ) );

  }
  function generate_onload( $onload_code ) {
    foreach( $this->_get_onload_filepaths() as $filepath ) {
      require( $filepath );
    }
    $onload_code = $this->_get_onload_files_content( $onload_code );
    foreach( $helper_onloaders = $this->_get_helper_onloaders() as $filepath => $php_code ) {
      $class_name = $this->derive_class_name( $this->owner->full_prefix, $filepath );
      Exo::register_helper( $class_name );
      $onload_code[] = $php_code;
    }
    return $onload_code;
  }

  /**
   * Register a list of classes and their filenames
   *
   * @param array $classes
   */
  function register_autoload_classes( $classes ) {
    $this->_autoload_classes = array_merge( $this->_autoload_classes, array_change_key_case( $classes, CASE_LOWER ) );
  }

  /**
   * Register a class and it's corresponding dir for autoloading.
   *
   * @param string $class_name
   * @param string $dir
   */
  function register_autoload_class( $class_name, $dir ) {
    $this->_autoload_classes[$class_name] = $dir;
  }

  /**
   * Register a directory containing one of more classes to autoload.
   *
   * @param string $dir
   * @param bool|string $prefix
   */
  function register_autoload_dir( $dir, $prefix = false ) {
    if ( ! $prefix && $this->owner->full_prefix ) {
      $prefix = $this->owner->full_prefix;
    }
    $this->_autoload_dirs[realpath( $dir )] = $prefix;
    /*
     * If we've already reached the 'wp_loaded' hook then we'll need
     * to call the method that loads classnames into the _autoload_classes array.
     */
    if ( Exo::is_wp_loaded() )
      $this->_add_autoloader_classes();
  }

  /**
   *
   */
  function _init_9() {
    $this->_add_autoloader_classes();
  }

  /**
   *
   */
  function _exo_autoloader_classes( $called_class ) {
    if ( $called_class == $this->owner->controller_class ) {
      $this->_add_autoloader_classes();
    }
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  private function _add_autoloader_classes() {
    foreach ( $this->_autoload_dirs as $dir => $prefix ) {
      foreach ( glob( "{$dir}/*.php" ) as $filepath ) {
        if ( preg_match( '#\.on-load\.php$#', $filepath ) ) {
          $this->_onload_filepaths[] = $filepath;
        } else if ( ! class_exists( $class_name = $this->derive_class_name( $prefix, $filepath ) ) ) {
          $this->_autoload_classes[$class_name] = $filepath;
        }
      }
    }
    /**
     * Clear this list out so we don't processes them again.
     */
    $this->_autoload_dirs = array();
  }

  /**
   * Derive class name given a qualifying class filename.
   *
   * @param string $prefix
   * @param string $filepath
   *
   * @return mixed
   */
  function derive_class_name( $prefix, $filepath ) {
    $class_name = str_replace( array( '-', '_' ), ' ', preg_replace( '#^(.*)/((-?)class-)?(.*?)(\.on-load)?\.php$#', "$3{$prefix}$4", $filepath ) );
    return str_replace( ' ', '_', ucwords( $class_name ) );
  }

  /**
   * Returns the array of autoload directories.
   *
   * @todo Add check to ensure not called before it's valid.
   *
   * @return array
   */
  function get_autoload_dirs() {
    return $this->_autoload_dirs;
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  private function _get_onload_files_content( $onload_files_content ) {
    $basepath_regex = preg_quote( $this->owner->dir() );
    foreach ( $this->_get_onload_filepaths() as $filepath ) {
      $local_filepath = preg_replace( "#^{$basepath_regex}(.*?)$#", '$1', $filepath = realpath( $filepath ) );
      $onload_files_content[] = "/**\n * File: {$local_filepath}\n */";
      $onload_files_content[] = trim( preg_replace( '#^\s*<\?php\s*(.*?)\s*$#misU', '$1', file_get_contents( $filepath ) ) );
    }
    return $onload_files_content;
  }

  private function _get_helper_onloaders() {
    $helpers_php = array();
    $implementation = $this->owner;
    foreach ( glob( $implementation->dir( '/helpers/*.php' ) ) as $filepath ) {
      $filepath = realpath( $filepath );
      $class_name = $this->derive_class_name( $implementation->full_prefix, $filepath );
      $helpers_php[$class_name] = "{$implementation->controller_class}::register_helper( '{$class_name}' );";
    }
    return $helpers_php;
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   *
   * This MUST be called after hook _after_setup_theme_8() is run.
   * @todo Add check to ensure not called before it's valid.
   */
  private function _get_onload_filepaths() {
    return $this->_onload_filepaths;
  }

  /**
   * Autoload classes from the registered directories.
   *
   * @param string $class_name
   */
  function _autoload( $class_name ) {
    if ( isset( $this->_autoload_classes[$class_name] ) ) {
      require($this->_autoload_classes[$class_name]);
      unset($this->_autoload_classes[$class_name]);
    }
  }

}
