<?php

/**
 * Class Exo_Autoloader
 *
 */
class Exo_Autoloader extends Exo_Base {

  /**
   * @var Exo_Implementation Class that owns this autoloader
   */
  private $owner;

  /**
   * @var array List of registered directories in which classes can be found.
   */
  private $_autoload_dirs = array();

  /**
   * @var array List of classes to autoload and their corresponding filepath.
   */
  private $_autoload_classes = array();

  /**
   * @var bool Flag variable to track if the 'wp_loaded' hook has fired yet or not.
   */
  private $_is_wp_loaded = false;

  /**
   * @var array
   */
  private $_onload_filepaths;

  /**
   *
   */
  function __construct( $owner ) {
    $this->owner = $owner;

    spl_autoload_register( array( $this, '_autoload' ) );

    /**
     * Add hooks for this class
     */
    add_action( 'wp_loaded', array( $this, '_wp_loaded_0' ), 0 );
    add_action( 'init', array( $this, 'init_9' ), 9 );
    add_action( 'after_setup_theme', array( $this, '_after_setup_theme_9' ), 9 );

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
    if ( ! $prefix && $this->owner->class_prefix ) {
      $prefix = $this->owner->class_prefix;
    }
    $this->_autoload_dirs[$dir] = $prefix;
    /*
     * If we've already reached the 'wp_loaded' hook then we'll need
     * to call the method that loads classnames into the _autoload_classes array.
     */
    if ( $this->_is_wp_loaded )
      $this->_add_autoloader_classes();
  }

  /**
   * Register a directory that contains subdirectories that each containes one of more classes to autoload.
   *
   * @param string $dir
   * @param bool|string $prefix
   */
  function register_autoload_subdir( $dir, $prefix = false ) {
    foreach ( glob( "{$dir}/*", GLOB_ONLYDIR ) as $subdir ) {
      $this->register_autoload_dir( $subdir, $prefix );
    }
  }

  /**
   *
   */
  function init_9() {
    $this->_add_autoloader_classes();
  }

  /**
   *
   */
  function _after_setup_theme_9() {
    $this->_add_autoloader_classes();
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  private function _add_autoloader_classes() {
    foreach ( $this->_autoload_dirs as $dir => $prefix ) {
      foreach ( glob( "{$dir}/*.php" ) as $filepath ) {
        $class_name = $prefix . str_replace( '-', '_', preg_replace( '#^(.*)/(class-)?(.*?)\.php$#', '$3', $filepath ) );
        $this->_autoload_classes[$class_name] = $filepath;
      }
    }
  }

  /**
   * Returns the array of autoload directories.
   *
   * @return array
   */
  function get_autoload_dirs() {
    return $this->_autoload_dirs;
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  function get_onload_files_content() {
    $onload_files_content = array();
    $basepath_regex = preg_quote( $this->owner->dir() );
    foreach ( $this->get_onload_filepaths() as $filepath ) {
      $local_filepath = preg_replace( "#^{$basepath_regex}(.*?)$#", '$1', $filepath = realpath( $filepath ) );
      $onload_files_content[] = "/**\n * File: {$local_filepath}\n */";
      $onload_files_content[] = preg_replace( '#^\s*<\?php\s*(.*?)\s*$#misU', '$1', file_get_contents( $filepath ) ) . "\n";
    }
    return "<?php\n" . implode( $onload_files_content );
  }

  /**
   *  Scans through the autoload dirs and adds the potential classes based on .php file names.
   */
  function get_onload_filepaths() {
    if ( ! isset( $this->_onload_filepaths ) ) {
      $this->_onload_filepaths = array();
      foreach ( $this->_autoload_dirs as $dir => $prefix ) {
        foreach ( glob( "{$dir}/*.on-load.php" ) as $filepath ) {
          $this->_onload_filepaths[] = $filepath;
        }
      }
    }
    return $this->_onload_filepaths;
  }

  /**
   * Autoload classes from the registered directories.
   *
   * @param string $class_name
   */
  function _autoload( $class_name ) {
    $class_name = strtolower( $class_name );
    if ( isset($this->_autoload_classes[$class_name]) ) {
      require($this->_autoload_classes[$class_name]);
      unset($this->_autoload_classes[$class_name]);
    }
  }

  /**
   *
   */
  function _wp_loaded_0() {
    $this->_is_wp_loaded = true;
  }

  /**
   *
   */
  function is_wp_loaded() {
    return $this->_is_wp_loaded;
  }

}
