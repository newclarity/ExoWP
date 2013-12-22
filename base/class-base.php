<?php

/**
 * Class Exo_Base
 */
abstract class Exo_Base {

  /**
   * @param array $args
   */
  function __construct( $args = array() ) {
    $this->apply_args( $args );
  }

  /**
   * @param array $args
   */
  function apply_args( $args ) {
    foreach( $args as $name => $value ) {
      if ( property_exists( $this, $name ) || property_exists( $this, $name = "_{$name}" ) ) {
        $this->$name = $value;
      }
    }
  }

  /**
   * @param string $action
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_action( $action, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_static_filter( get_called_class(), $action, $method_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_filter( $filter, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_static_filter( get_called_class(), $filter, $method_or_priority, $priority );
  }

  /**
   * @param string $action
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_class_action( $action, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_class_filter( get_called_class(), $action, $method_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param bool|int|string|array $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_class_filter( $filter, $method_or_priority = false, $priority = 10 ) {
    return _Exo_Hook_Helpers::add_class_filter( get_called_class(), $filter, $method_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param bool|mixed $arg1
   * @param bool|mixed $arg2
   * @param bool|mixed $arg3
   * @param bool|mixed $arg4
   * @param bool|mixed $arg5
   *
   * @return mixed
   */
  static function apply_static_filters( $filter, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    $args = func_get_args();
    $args[0] = get_called_class() ."::{$filter}()";
    return call_user_func_array( 'apply_filters', $args );
  }

  /**
   * @param string $action
   * @param bool|mixed $arg1
   * @param bool|mixed $arg2
   * @param bool|mixed $arg3
   * @param bool|mixed $arg4
   * @param bool|mixed $arg5
   *
   * @return mixed
   */
  static function do_static_action( $action, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    $args = func_get_args();
    $args[0] = get_called_class() ."::{$action}()";
    return call_user_func_array( 'do_action', $args );
  }

}
