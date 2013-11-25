<?php

/**
 * Class Exo_Singleton_Base
 *
 * The Singleton Base class in Exo is used to create classes that behave as if they only has static methods when in
 * fact they are delegating their static methods calls to a class that extends from Delegating Instance Class and whose
 * instance is stored in this class' static $singleton property.
 *
 * A Singleton Base class combined with Delegating Base class is perfect for a core Application class.
 * For example, the Exo class is a singleton class, and the _Exo is it's delegating instance.
 *
 */
abstract class Exo_Singleton_Base extends Exo_Instance_Base {

  /**
   * @var Exo_Delegating_Base Singleton
   */
  static $skeleton;

  /**
   * @param string $filter
   * @param bool|int|callable $method_or_priority
   * @param int $priority
   * @return bool
   */
  static function add_singleton_filter( $filter, $method_or_priority = false, $priority = 10 ) {
    if ( is_string( $method_or_priority ) && method_exists( self::$skeleton, $method_or_priority ) ) {
      $callable = array( self::$skeleton, $method_or_priority );
    } else if ( is_callable( $method_or_priority ) ) {
      $callable = $method_or_priority;
    } else {
      $callable = array( self::$skeleton, $filter );
      if ( is_numeric( $method_or_priority ) ) {
        $priority = $method_or_priority;
      }
    }
    return self::$skeleton->add_instance_filter( $filter, $callable, $priority );
  }

  /**
   * @param string $action
   * @param bool|int|callable $method_or_priority
   * @param int $priority
   * @return bool
   */
  static function add_singleton_action( $action, $method_or_priority = false, $priority = 10 ) {
    return self::add_singleton_filter( $action, $method_or_priority, $priority );
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
    if ( method_exists( self::$skeleton, $method_name ) ) {
      $value = call_user_func_array( array( self::$skeleton, $method_name ), $args );
    } else if ( $method = self::$skeleton->get_helper_method( $method_name ) ) {
      $value = call_user_func_array( $method, $args );
    } else {
      $message = __( 'ERROR: Neither %s nor any of it\'s helper classes have the method %s().', 'Exo' );
      trigger_error( sprintf( $message, get_called_class(), $method_name ) );
    }

    return $value;
  }

}
