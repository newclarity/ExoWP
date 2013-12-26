<?php

define( 'EXO_VERSION', '0.1.8' );

/**
 * All Exo implementations should load exo-core.php first.
 */
require( __DIR__ . '/core/exo-core.php');

/**
 * Class Exo
 */
class Exo extends Exo_Library_Base {
  const SHORT_PREFIX = 'exo_';

  /**x
   * @var bool Flag variable to track if the 'wp_loaded' hook has fired yet or not.
   */
  private static $_is_wp_loaded = false;

  /**x
   * @var bool Flag variable to track if the 'exo_init' hook has fired yet or not.
   */
  private static $_is_exo_init = false;

  /**
   * @var string Directory for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   */
  private static $_theme_dir;

  /**
   * @var string URL for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   */
  private static $_theme_uri;

  /**
   * @var string Target environment, must be one of 'dev', 'test', 'stage' or 'live.'
   *             Defaults to 'live' because that's the safest default.
   */
  private static $_runmode = 'live';

  /**
   * @var string
   */
  private static $_included_template;

  /**
   * Return the files required to be require()d by Exo itself.
   *
   * Exo looks for a uppercase method of this name to get a list of files.
   *
   * @return array
   */
  function REQUIRED_FILES() {
    return array(
      'core/class-autoloader.php',
      'base/class-mixin-base.php',
      'base/class-model-base.php',
      'base/class-collection-base.php',
      'base/class-view-base.php',
      'base/class-post-base.php',
    );
  }

  /**
   * Return the local paths that autoloadable files can be found for Exo itself.
   *
   * Exo looks for a uppercase method of this name to get a list of paths.
   *
   * @return array
   */
  function AUTOLOAD_PATHS() {
    return array(
      'base',
      'helpers',
      'models/post-types',
      'models/taxonomies',
      'collections',
      'views',
    );
  }

  /**
   * Boostrap Exo
   */
  static function on_load() {
    if ( defined( 'EXO_RUNMODE' ) ) {
      /*
       * This is a fallback so it can be set when using require( 'wp-load.php' );
       */
      self::set_runmode( EXO_RUNMODE );
    }

    /*
     * Grabs the theme directory for convenience (and so the name isn't confusing!)
     */
    self::$_theme_dir = get_stylesheet_directory();

    /**
     * Ensure we are using the right scheme for the incoming URL (http vs. https)
     */
    self::$_theme_uri = _Exo_Helpers::maybe_adjust_http_scheme( get_stylesheet_directory_uri() );

    /*
     * Records the fact that the 'wp_loaded' hook as indeed been reached.
     */
    add_action( 'wp_loaded', array( __CLASS__, '_wp_loaded_0' ), 0 );

    /*
     * Call as late as possible so that no other hooks modify after since I goal is to just capture the value.
     */
    add_action( 'template_include', array( __CLASS__, '_template_include_9999999' ), 9999999 );

    /**
     * Initializes and 'fixes up' all things registered in plugins and themes.
     */
    self::add_static_action( 'after_setup_theme', 11 );
  }

  /**
   * Initialize the Main and Implementation classes.
   *
   * To be called after all the other code is called.
   */
  static function _after_setup_theme_11() {

    if ( Exo::is_dev_mode() ) {

      /*
       * Convert class_names in self::$_implementations to actual Exo_Implementations.
       */
      Exo_Main_Base::_record_implementations();

      /**
       * Sort component type by load precendence
       */
      self::_sort_implementations();

      /**
       * @var Exo_Implementation $implementation
       */
      foreach( self::implementations() as $implementation ) {

        /*
         * Scan the autoload directories and for each file record an associated class.
         */
        $implementation->_load_required_files();

        /*
         * Scan the autoload directories and for each file record an associated class.
         */
        $implementation->_record_autoload_class_filepaths();

        /**
         * Collect up registered helpers for this implementation
         */
        $implementation->_register_helpers( $implementation->apply_instance_filters( 'exo_register_helpers', array() ) );
        /**
         * Generate list of $this->_helper_callables[$method_name] from shorter list of this->_helpers[$class_name].
         */
        $implementation->_fixup_registered_helpers();

        /*
         * Autoload all class files found in the autoload directories.
         */
        $implementation->autoload_all();

      }
      /**
       * Add in this hook to that _Exo_Hook_Helpers::_exo_scan_class() will
       */
      add_action( 'exo_scan_class_hooks', array( '_Exo_Hook_Helpers', '_exo_scan_class_hooks' ) );
      Exo::_scan_classes( 'hooks' );
      Exo::_add_hooks();
      Exo::_scan_classes();

    }
    foreach( self::implementations() as $implementation ) {
      /*
       * Allow something else to do something at this point.
       */
      $implementation->do_instance_action( 'exo_implementation_init' );
    }
    self::$_is_exo_init = true;
    do_action( 'exo_init' );
  }

  /**
   *
   */
  static function is_exo_init() {
    return self::$_is_exo_init;
  }

  /**
   * Scan the list of $classes from get_declared_classes() and first 'exo_scan_class' hook.
   *
   * @param bool|string $scan_type
   * @note All classes must be loaded to call this.
   */
  static function _scan_classes( $scan_type = false ) {
    $action = 'exo_scan_class' . ( $scan_type ? "_{$scan_type}" : false );
    foreach( get_declared_classes() as $class_name ) {
      do_action( $action, $class_name );
    }
  }

  /**
   *
   */
  static function _wp_loaded_0() {
    self::$_is_wp_loaded = true;
  }

  /**
   *
   */
  static function is_wp_loaded() {
    return self::$_is_wp_loaded;
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
  static function theme_dir( $path = false ) {
    return $path ? "{self::$_theme_dir}/" . ltrim( $path, '/' ) : self::$_theme_dir;
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
  static function theme_uri( $path = false ) {
    return $path ? "{self::$_theme_uri}/" . ltrim( $path, '/' ) : self::$_theme_uri;
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
  static function theme_url( $path = false ) {
    return self::theme_uri( $path );
  }

  /**
   * Echos the URI/URL for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   */
  static function the_theme_uri( $path = false ) {
    echo self::theme_uri( $path );
  }

  /**
   * Echos the URI/URL for the theme dir for this site. If it has a parent theme, it returns the child theme's dir.
   *
   * @note Does not contain a trailing slash if no $path is passed.
   *
   * @param bool|string $path
   */
  static function the_theme_url( $path = false ) {
    echo self::theme_uri( $path );
  }

  /**
   * Returns true if a Development Deployment.
   *
   * @return string
   */
  static function is_dev_mode() {
    return 'dev' == self::$_runmode;
  }

  /**
   * Returns true if a Testing Deployment.
   *
   * @return string
   */
  static function is_test_mode() {
    return 'test' == self::$_runmode;
  }

  /**
   * Returns true if a Staging Deployment.
   *
   * @return string
   */
  static function is_stage_mode() {
    return 'stage' == self::$_runmode;
  }

  /**
   * Returns true if a Live Deployment, i.e. Production.
   *
   * @return string
   */
  static function is_live_mode() {
    return 'live' == self::$_runmode;
  }

  /**
   * Returns the Run Mode, one of: 'dev', 'test', 'stage' or 'live.'
   *
   * @return string
   */
  static function runmode() {
    return self::$_runmode;
  }

  /**
   * @param $runmode
   *
   * @throws Exception
   */
  static function set_runmode( $runmode ) {
    if ( ! WP_DEBUG ) {
      self::$_runmode = strtolower( $runmode );
    } else {
      switch ( $runmode ) {
        case 'dev':
        case 'test':
        case 'stage':
        case 'live':
          self::$_runmode = strtolower( $runmode );
          break;
        default:
          $message = __( 'ERROR: Neither Exo nor any of it\'s helper classes have the method %s().', 'exo' );
          Exo::trigger_warning( $message, $method_name );
          break;
      }
    }
  }

  /**
  * Capture filepath of the theme template file that was loaded by WordPress' template-loader.php into a static var.
   *
  * @return bool
  */
  static function _template_include_9999999() {
    self::$_included_template = func_get_arg( 0 );

    if ( isset( $GLOBALS['posts'] ) && is_array( $GLOBALS['posts'] ) && 1 < $GLOBALS['posts'] ) {
      $view = new Exo_Post_Collection_View( new Exo_Post_Collection( $GLOBALS['posts'] ) );
    } else if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
      $view = new Exo_Post_View( new Exo_Post( $GLOBALS['post'] ) );
    }
    if ( $view ) {
      require( self::$_included_template );
    }

    return self::implementation()->dir( 'templates/empty.php' );
  }

  /**
  * Returns filepath of the theme template file that was loaded by WordPress' template-loader.php
   *
  * @return string
  */
  static function included_template() {
    return self::$_included_template;
  }

}

/**
 * Initialize Exo using the 'plugins_loaded' priority 5, or load directly assumed require()d in function.php file.
 */
if ( defined( 'EXO_LOAD_IN_THEME' ) && EXO_LOAD_IN_THEME ) {
  if ( defined( 'EXO_THEME_LOAD_CALLABLE' ) ) {
  	call_user_func( EXO_THEME_LOAD_CALLABLE );
  } else {
  	Exo::on_load();
  }
} else {
  add_action( 'plugins_loaded', array( 'Exo', 'on_load' ), 5 );
}

