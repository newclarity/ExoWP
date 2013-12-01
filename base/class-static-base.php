<?php

/**
 * Class Exo_Static_Base
 *
 * Static Base classes for used to create classes that only contain static methods and static properties.
 *
 */
abstract class Exo_Static_Base extends Exo_Base {

  /**
   * @var array
   */
  private static $_helpers = array();

  /**
   * @var array
   */
  private static $_helper_instances = array();

  /**
   * @var array
   */
  private static $_helper_callables = array();

  /**
   *
   */
  static function on_load() {
    add_action( 'plugins_loaded', array( __CLASS__, '_plugins_loaded_9' ), 9 );
  }

  /**
   * Fixup the registered helpers after the theme loads but before the default priority 10 hook after_setup_theme.
   */
  static function _plugins_loaded_9() {
    static $CLASS_METHOD_FILTERS = array(
      'public' => true,
      'static' => true,
    );
    foreach( self::$_helpers as $helper ) {
      list( $class_name, $method_name, $alt_method_name ) = $helper;
      if ( is_object( $class_name ) ) {
        $instance = $class_name;
        self::$_helper_instances[$class_name = get_class( $instance )] = $instance;
      } else if ( ! isset(self::$_helper_instances[$class_name]) ) {
        self::$_helper_instances[$class_name] = $instance = new $class_name();
      } else {
        $instance = self::$_helper_instances[$class_name];
      }
      if ( $method_name ) {
        if ( $alt_method_name ) {
          self::$_helper_callables[$method_name] = array( $instance, $method_name );
        } else {
          self::$_helper_callables[$alt_method_name] = array( $instance, $method_name );
        }
      } else {
        foreach ( _Exo_Php_Helpers::get_class_methods( $class_name, $CLASS_METHOD_FILTERS ) as $method_name ) {
          self::$_helper_callables[$method_name] = array( $instance, $method_name );
        }
      }
    }
    /**
     * Clear this vars' memory. We don't need it anymore.
     */
    self::$_helpers = null;
  }

  /**
   * Unregister a Helper Class or Class Method for the App object.
   *
   * @param string|object $class_name
   * @param bool|string $method_name
   * @param bool|string $alt_method_name
   */
  static function register_helper( $class_name, $method_name = false, $alt_method_name = false ) {
    self::$_helpers[] = array( $class_name, $method_name, $alt_method_name );
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  static function has_method( $method_name ) {
    return method_exists( __CLASS__, $method_name ) || isset( self::$_helper_callables[$method_name] );
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  static function has_helper_method( $method_name ) {
    return isset( self::$_helper_callables[$method_name] );
  }

  /**
   * @param $method_name
   *
   * @return bool|callable
   */
  static function get_helper_method( $method_name ) {
    return isset( self::$_helper_callables[$method_name] ) ? self::$_helper_callables[$method_name] : false;
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
    if ( method_exists( __CLASS__, $method_name ) ) {
      $value = call_user_func_array( array( __CLASS__, $method_name ), $args );
    } else if ( $method = self::get_helper_method( $method_name ) ) {
      $value = call_user_func_array( $method, $args );
    } else {
      $message = __( 'ERROR: Neither %s nor any of it\'s helper classes have the method %s().', 'exo' );
      _Exo_Helpers::trigger_warning( sprintf( $message, get_called_class(), $method_name ) );
    }

    return $value;
  }

}
Exo_Static_Base::on_load();
