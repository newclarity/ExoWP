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
   * @var array
   */
  private $_onload_filepaths = array();

  /**
   * @param Exo_Implementation $owner
   */
  function __construct( $owner ) {
    $this->owner = $owner;

    spl_autoload_register( array( $this, '_autoload' ) );

    $this->add_instance_action( 'exo_bypass_onload_file' );

  }

  /**
   * Action hook to fire when runmode=='dev' to bypass the /on-load.php file.
   *
   * This method loads all potential autoload classes
   *
   * @return array
   */
  function _exo_bypass_onload_file() {
    $this->autoload_all();
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
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  function register_autoloader_classes() {
    foreach ( $this->_autoload_dirs as $dir => $prefix ) {
      foreach ( glob( "{$dir}/*.php" ) as $filepath ) {
        if ( preg_match( '#\.on-load\.php$#', $filepath ) ) {
          $this->_onload_filepaths[$this->derive_class_name( $filepath, $this->owner->full_prefix )] = $filepath;
        } else if ( ! class_exists( $class_name = $this->derive_class_name( $filepath, $prefix ), false ) ) {
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
   * @param string $filepath
   * @param string $prefix
   *
   * @return mixed
   */
  function derive_class_name( $filepath, $prefix ) {
    $class_name = str_replace( array( '-', '_' ), ' ', preg_replace( '#^(.*)/((-?)class-)?(.*?)(\.on-load)?\.php$#', "$3{$prefix}$4", $filepath ) );
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
    if ( isset( $this->_autoload_classes[$class_name] ) && ! class_exists( $class_name, false ) ) {
      require( $this->_autoload_classes[$class_name] );
    }
    unset( $this->_autoload_classes[$class_name] );
  }

  /**
   *
   */
  function autoload_all() {
    foreach( $this->_autoload_classes as $class_name ) {
      $this->_autoload( $class_name );
    }
  }

  /**
   *
   */
  function load_onload_filepaths() {
    foreach( $this->_onload_filepaths as $filepath ) {
      require( $filepath );
    }
  }

  /**
   * Return array of onload snippets from all the *.on-load.php files contained in the autoload directories.
   *
   * @param string $directory
   *
   * @return array
   */
  function get_onload_snippets( $directory ) {
    $onload_snippets = array();
    $basepath_regex = preg_quote( $directory );
    foreach ( $this->_onload_filepaths as $filepath ) {
      $filepath = realpath( $filepath );
      $local_filepath = preg_replace( "#^{$basepath_regex}(.*?)$#", '$1', $filepath );
      $onload_snippets[$filepath] =
        "/**\n * File: {$local_filepath}\n */\n"
        . trim( preg_replace( '#^\s*<\?php\s*(.*?)\s*$#misU', '$1', Exo::get_file_contents( $filepath ) ) );
    }
    return $onload_snippets;
  }



}

