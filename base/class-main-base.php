<?php

/**
 * Class Exo_Main_Base
 *
 * Base classes for Main Classes for an implementation.
 *
 * This class SHOULD NOT have any static or instance variables besides $_instances.
 * All values needed to be managed should instead be places in the instannce's class.
 */
abstract class Exo_Main_Base extends Exo_Base {

  /**
   * @var array
   */
  private static $_instances = array();

  /**
   * Allows a class that extends from Exo_Main_Base to register an instance of Exo_Instance_Core
   *
   * @param string $class_name
   * @param Exo_Implementation_Base $instance
   * @param array $args
   */
  static function register_instance( $class_name, $instance, $args = array() ) {
    if ( ! isset( self::$_instances[$class_name] ) ) {
      $args = wp_parse_args( $args, array(
        'make_global' => false,
      ));
      $instance->class_prefix = "{$class_name}_";
      self::$_instances[$class_name] = $instance;
      if ( $args['make_global'] ) {
        $GLOBALS[$class_name] = $instance;
      }
    }
  }

  /**
   * Allows a class that extends from Exo_Main_Base to register an instance of Exo_Instance_Core
   *
   * @param string $class_prefix
   */
  static function register_class_prefix( $class_prefix ) {
    if ( isset( self::$_instances[$called_class = get_called_class()] ) ) {
      self::$_instances[$called_class]->class_prefix = $class_prefix;
    }
  }

  /**
   * Initialize the Main and Implementation classes.
   *
   * To be called after all the other code is called.
   *
   * @param array $args
   */
  static function initialize( $args = array() ) {
    if ( isset( self::$_instances[$called_class = get_called_class()] ) ) {
      /**
       * @var Exo_Implementation_Base $instance
       */
      $instance = self::$_instances[$called_class];
      $instance->initialize( $args );
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
    $value = null;
    if ( ! isset( self::$_instances[$called_class = get_called_class()] ) ) {
      $error = true;
    } else {
      $error = false;
      if ( method_exists( self::$_instances[$called_class], $method_name ) ) {
        $value = call_user_func_array( array( self::$_instances[$called_class], $method_name ), $args );
      } else {
        /**
         * @var Exo_Implementation_Base $instance
         */
        $instance = self::$_instances[$called_class];
        if ( $method = $instance->get_helper_method( $method_name ) ) {
          $value = call_user_func_array( $method, $args );
        } else if ( method_exists( $autoloader = $instance->autoloader, $method_name ) ) {
          $value = call_user_func_array( array( $autoloader, $method_name ), $args );
        } else {
          $error = true;
        }
      }
    }
    if ( $error ) {
      $message = __( 'ERROR: Neither %s nor any of it\'s registered helper classes have the method %s().', 'exo' );
      _Exo_Helpers::trigger_warning( sprintf( $message, $called_class, $method_name ) );
    }
    return $value;
  }

}

