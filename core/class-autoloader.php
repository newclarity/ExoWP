<?php

/**
 * Class Exo_Autoloader
 *
 * @property Exo_Implementation $owner
 */
class Exo_Autoloader extends Exo_Instance_Base {

  /**
   * @var array List of registered directories in which classes can be found.
   */
  private $_autoload_dirs = array();

  /**
   * @var array List of classes to autoload and their corresponding filepath.
   */
  private $_autoload_classes = array();

  /**
   * @param Exo_Implementation $owner
   */
  function __construct( $owner ) {
    $this->owner = $owner;
    spl_autoload_register( array( $this, '_autoload' ) );
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
  }

  /**
   * Derive class name given a qualifying class filename.
   *
   * @param string $filepath
   * @param string $prefix
   *
   * @return mixed
   */
  function derive_class_name( $filepath, $prefix ) {
    $class_name = str_replace( array( '-', '_' ), ' ', preg_replace( '#^(.*)/((-?)class-)?(.*?)\.php$#', "$3{$prefix}$4", $filepath ) );
    $class_name = str_replace( ' ', '_', ucwords( $class_name ) );
    return $class_name;
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
   * Autoload classes from the registered directories.
   *
   * @param string $class_name
   */
  function _autoload( $class_name ) {
    if ( isset( $this->_autoload_classes[$class_name] ) ) {
      require( $this->_autoload_classes[$class_name] );
    }
  }

  /**
   *
   */
  function autoload_all() {
    foreach( $this->_autoload_classes as $class_name => $filepath ) {
      if ( ! class_exists( $class_name, false ) ) {
        require( $filepath );
      }
    }
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  function _record_class_filepaths() {
    foreach ( $this->_autoload_dirs as $dir => $prefix ) {
      foreach ( glob( "{$dir}/*.php" ) as $filepath ) {
        $filepath = realpath( $filepath );
        $this->_autoload_classes[$class_name = $this->derive_class_name( $filepath, $prefix )] = $filepath;
      }
    }
    /**
     * Clear this list out so we don't processes them again.
     */
    $this->_autoload_dirs = false;
  }

  /**
   * @return array
   */
  function _get_autoload_classes() {
    return $this->_autoload_classes;
  }

  /**
   * @param array $autoload_classes
   */
  function _set_autoload_classes( $autoload_classes ) {
    $this->_autoload_classes = $autoload_classes;
  }

}

