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
 * @mixin _Exo_Hook_Helpers
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
   * Scan declared classes and record any children of Exo_Main_Base as an implementation
   *
   * @note: Called by 'init' priority 1 once.
   * @note: By call time all implementations should have been loaded by plugins or themes.
   */
  static function _record_implementations() {
    foreach( get_declared_classes() as $class_name ) {
      if ( is_subclass_of( $class_name, __CLASS__ ) && ! preg_match( '#_Base$#', $class_name ) ) {
        self::_register_implementation( $class_name );
      }
    }
  }
  /**
   * @param string $class
   * @param string $action
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_class_action( $class, $action, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_class_filter( $class, $action, $method_or_priority, $priority );
  }

  /**
   * @param string $class
   * @param string $filter
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_class_filter( $class, $filter, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_class_filter( $class, $filter, $method_or_priority, $priority );
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
   * @param string $class_name
   */
  private static function _register_implementation( $class_name ) {
    self::$_implementations[$class_name] = new Exo_Implementation( $class_name );
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
    array_push( $args, $instance );
    $args[0] = spl_object_hash( $instance ) . "->{$filter}()";
    return call_user_func_array( 'apply_filters', $args );
  }

  /**
   * Sort component type by load precendence
   *
   * Libraries should be loaded before Plugins,
   * Plugins should be loaded before Themes,
   * Themes should be loaded before Applications, and
   * Applications should (probably) be loaded before Websites.
   *
   */
  static function _sort_implementations() {
    static $sort_order = array(
      'library',
      'plugin',
      'theme',
      'application',
      'website',
    );
    $sort_order = array_flip( $sort_order );
    uksort( self::$_implementations, function( $class1, $class2 ) use( $sort_order ) {
      $type1 = _Exo_Helpers::get_implementation_type( $class1 );
      $type2 = _Exo_Helpers::get_implementation_type( $class2 );
      if ( ! $type1 || ! $type2 ) {
        $message = __( '%s is not a valid implementation class; it must be a child class of Exo_{type}_Base ' .
                       'where {type} can be Library, Plugin, Theme, Application or Website.', 'exo' );
        _Exo_Helpers::trigger_warning( $message, $invalid = ! $type1 ? $class1 : $class2 );
      } else if ( $sort_order[$type1] < $sort_order[$type2] ) {
        $result = -1;
      } else if ( $sort_order[$type1] < $sort_order[$type2] ) {
        $result = 0;
      } else {
        $result = 1;
      }
      return $result;
    });
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
