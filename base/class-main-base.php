<?php

/**
 * Class Exo_Main_Base
 *
 * Base classes for Main Classes for an implementation.
 *
 * This class SHOULD NOT have any static or instance variables besides $_implementations.
 * All values needed to be managed should instead be places in the instannce's class.
 *
 * @mixin _Exo_Helpers
 * @mixin _Exo_Post_Helpers
 * @mixin _Exo_Meta_Helpers
 * @mixin _Exo_File_Helpers
 *
 * @mixin Exo_Implementation
 * @method static string uri( string $path = false )
 * @method static string dir( string $path = false )
 * @method static string get_helper_callable( string $method_name )
 * @method static bool has_method( string $method_name )
 * @method static bool has_helper_callable( string $method_name )
 * @method static void register_helper( string $class_name, string $method_name = false, string $alt_method_name = false )
 * @method static void require_exo_autoloader()
 * @method static void require_exo_mvc_classes()
 * @method static void require_exo_base_classes()
 * @method static void register_exo_mvc_autoload_dirs()
 * @method static void register_exo_autoload_dirs()
 * @method static void fixup_registered_helpers()
 * @method static void enable_mvc()
 *
 * @mixin Exo_Autoloader
 * @method static void register_autoload_classes( array $classes )
 * @method static void register_autoload_class( string $class_name, string $dir )
 * @method static void register_autoload_dir( string $dir, string $prefix = false )
 * @method static void register_autoload_subdir( string $dir, string $prefix = false )
 * @method static array get_autoload_dirs()
 * @method static array get_onload_files_content()
 * @method static array get_onload_filepaths()
 *
 */
abstract class Exo_Main_Base extends Exo_Base {

  /**
   * @var array
   */
  private static $_implementations = array();

  /**
   * @var bool Flag variable to track if the 'wp_loaded' hook has fired yet or not.
   */
  private static $_is_wp_loaded = false;

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
   *
   */
  static function on_load() {

    if ( defined( 'EXO_RUNMODE' ) ) {
      /*
       * This is a fallback so it can be set when using require( 'wp-load.php' );
       */
      self::set_runmode( EXO_RUNMODE );
    }

    self::$_theme_dir = get_stylesheet_directory();
    /**
     * Ensure we are using the right scheme for the incoming URL (http vs. https)
     */
    self::$_theme_uri = self::maybe_adjust_http_scheme( get_stylesheet_directory_uri() );

    add_action( 'wp_loaded', array( __CLASS__, '_wp_loaded_0' ), 0 );

    /*
     * Call as late as possible so that no other hooks modify after since I goal is to just capture the value.
     */
    add_action( 'template_include', array( __CLASS__, '_template_include_9999999' ), 9999999 );

  }

  /**
   * @param string $class
   * @param string $action
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_action( $class, $action, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_static_filter( $class, $action, $method_or_priority, $priority );
  }

  /**
   * @param string $class
   * @param string $filter
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_filter( $class, $filter, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_static_filter( $class, $filter, $method_or_priority, $priority );
  }

  /**
   *
   */
  static function autoload_all() {
    /**
     * @var Exo_Implementation $implementation
     */
    foreach( self::$_implementations as $implementation ) {
      $implementation->autoload_all();
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

    return dirname( __DIR__ ) . '/templates/empty.php';
  }

  /**
  * Returns filepath of the theme template file that was loaded by WordPress' template-loader.php
   *
  * @return string
  */
  static function included_template() {
    return self::$_included_template;
  }

  /**
   * Align the HTTP scheme (SSL vs. non SSL) to be consistent with incoming URL.
   *
   * @param $url
   *
   * @return mixed
   */
  static function maybe_adjust_http_scheme( $url ) {
    $scheme = is_ssl() ? 'https' : 'http';
    return preg_replace( '#^https?://#', "{$scheme}://", $url );
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
   * @return Exo_Implementation
   */
  static function implementation() {
    return isset( self::$_implementations[$class_name = get_called_class()] ) ? self::$_implementations[$class_name] : false;
  }

  /**
   * @return array
   */
  static function implementations() {
    return self::$_implementations;
  }

  /**
   * Registers a class to start being extended by helpers.
   *
   * @param string|Exo_Implementation $dir_or_implementation
   * @param array $args
   */
  static function register_implementation( $dir_or_implementation, $args = array() ) {
    if ( ! isset( self::$_implementations[$class_name = get_called_class()] ) ) {
      $args = wp_parse_args( $args, array(
        'make_global'  => false,
        'full_prefix'  => "{$class_name}_",
        'short_prefix' => strtolower( self::_get_capital_letters( $class_name ) ) . '_',
        'main_class'   => $class_name,
      ));
      if ( is_string( $dir_or_implementation ) ) {
        $implementation = new Exo_Implementation( $dir_or_implementation, $args );
      } else {
        $implementation = $dir_or_implementation;
        $implementation->apply_args( $args );
      }
      self::$_implementations[$class_name] = $implementation;
      if ( $args['make_global'] ) {
        $GLOBALS[$class_name] = $implementation;
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
  private static function _get_capital_letters( $string ) {
    return preg_match_all( '#([A-Z]+)#', $string, $matches ) ? implode( $matches[1] ) : false;
  }

  /**
   * @return bool
   */
  static function short_prefix() {
    return Exo::implementation()->short_prefix;
  }

  /**
   * @return bool
   */
  static function full_prefix() {
    return Exo::implementation()->full_prefix;
  }

  /**
   * @param object $instance
   * @param string $action
   * @param mixed $arg1
   * @param bool|mixed $arg2
   * @param bool|mixed $arg3
   * @param bool|mixed $arg4
   * @param bool|mixed $arg5
   *
   * @return mixed
   */
  static function do_instance_action( $instance, $action, $arg1, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    return self::apply_instance_filters( $instance, $action, $arg1, $arg2 , $arg3 , $arg4 , $arg5 );
  }

  /**
   * @param object $instance
   * @param string $filter
   * @param mixed $arg1
   * @param bool|mixed $arg2
   * @param bool|mixed $arg3
   * @param bool|mixed $arg4
   * @param bool|mixed $arg5
   *
   * @return mixed
   */
  static function apply_instance_filters( $instance, $filter, $arg1, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    $args = func_get_args();
    array_shift( $args );
    $args[0] = spl_object_hash( $instance ) . "->{$filter}()";
    return call_user_func_array( 'apply_filters', $args );
  }

  /**
   * Initialize the Main and Implementation classes.
   *
   * To be called after all the other code is called.
   * Load the 'on-load' files.
   *
   */
  static function initialize() {

    /**
     * @var Exo_Implementation $implementation
     */
    if ( $implementation = self::implementation() ) {

      $implementation->do_instance_action( 'exo_autoloader_classes' );

      if ( ! self::is_dev_mode() ) {
        require( $implementation->get_onload_filepath() );
      } else {
        $implementation->do_instance_action( 'exo_bypass_onload_file' );
        $implementation->add_action( 'shutdown' );
      }

      $implementation->fixup_registered_helpers();

    }

  }

  /**
   * Delegate calls to other classes.
   *
   * This allows us to document a single "API" for the sunrise class yet
   * structure the code more conveniently in multiple class files.
   *
   * @param string $method_name
   * @param array $args
   *
   * @return mixed
   *
   * @throws Exception
   */
  static function __callStatic( $method_name, $args ) {
    if ( 'register_post_type' == $method_name ) {
      echo '';
    }
    $value = null;
    if ( ! isset( self::$_implementations[$called_class = get_called_class()] ) ) {
      $error = true;
    } else {
      $error = false;
      /**
       * @var Exo_Implementation $implementation
       */
      $implementation = self::$_implementations[$called_class];
      if ( method_exists( $implementation, $method_name ) ) {
        /**
         * If Exo_Implementation has this method
         */
        $value = call_user_func_array( array( $implementation, $method_name ), $args );
      } else if ( $callable = $implementation->get_helper_callable( $method_name ) ) {
        /**
         * If Exo_Implementation has this method
         */
        $value = call_user_func_array( $callable, $args );
      } else if ( method_exists( $autoloader = $implementation->autoloader, $method_name ) ) {
        /**
         * If Exo_Autoloader has this method
         */
        $value = call_user_func_array( array( $autoloader, $method_name ), $args );
      } else {
        $error = true;
      }
    }
    if ( $error ) {
      $message = __( 'ERROR: Neither %s nor any of it\'s registered helper classes have the method %s().', 'exo' );
      _Exo_Helpers::trigger_warning( sprintf( $message, $called_class, $method_name ) );
    }
    return $value;
  }

}
Exo_Main_Base::on_load();
