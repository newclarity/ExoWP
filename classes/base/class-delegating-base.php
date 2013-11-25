<?php
/**
 * Class Exo_Delegating_Base
 *
 * The Delegating Base class supports the Singleton Base class by providing it with helper functionality.
 *
 * A Delegating Base class combined with Singleton Base class is perfect for a core Application class.
 * For example, the Exo class is a singleton class, and the _Exo is it's delegating instance.
 */
abstract class Exo_Delegating_Base extends Exo_Instance_Base {

  /**
   * @var array
   */
  private $_helper_instances = array();

  /**
   * @var array
   */
  private $_helper_callables = array();

  /**
   * Unregister a Helper Class or Class Method for the App object.
   *
   * @param string|object $class_name
   * @param bool|string $method_name
   * @param bool|string $alt_method_name
   */
  function register_helper( $class_name, $method_name = false, $alt_method_name = false ) {
    if ( is_object( $class_name ) ) {
      $instance = $class_name;
      $this->_helper_instances[$class_name = get_class( $instance )] = $instance;
    } else if ( ! isset($this->_helper_instances[$class_name]) ) {
      $this->_helper_instances[$class_name] = $instance = new $class_name();
    } else {
      $instance = $this->_helper_instances[$class_name];
    }
    if ( $method_name ) {
      if ( $alt_method_name ) {
        $app->_helper_callables[$method_name] = array( $instance, $method_name );
      } else {
        $app->_helper_callables[$alt_method_name] = array( $instance, $method_name );
      }
    } else {
      foreach ( get_class_methods( $class_name ) as $method_name ) {
        $app->_helper_callables[$method_name] = array( $instance, $method_name );
      }
    }
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  function has_method( $method_name ) {
    return method_exists( $this, $method_name ) || isset( $this->_helper_callables[$method_name] );
  }

  /**
   * @param $method_name
   *
   * @return bool
   */
  function has_helper_method( $method_name ) {
    return isset( $this->_helper_callables[$method_name] );
  }

  /**
   * @param $method_name
   *
   * @return bool|callable
   */
  function get_helper_method( $method_name ) {
    return isset( $this->_helper_callables[$method_name]) ? $this->_helper_callables[$method_name] : false;
  }


}
