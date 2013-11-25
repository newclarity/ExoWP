<?php

/**
 * Class Exo_Base
 */
abstract class Exo_Base {

  /**
   * Placeholder for potential future use.
   */
  function __construct() {
  }

  /**
   * @param string $action
   * @param bool|int|string $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_action( $action, $method_or_priority = false, $priority = 10 ) {
    return self::add_static_filter( $action, $method_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param bool|int|string $method_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_static_filter( $filter, $method_or_priority = false, $priority = 10 ) {
    if ( is_string( $method_or_priority ) ) {
      $callable = array( $class = get_called_class(), $method_or_priority );
    } else {
      $callable = array( $class = get_called_class(), $filter );
      if ( is_numeric( $callable_or_priority ) ) {
        $priority = $callable_or_priority;
      }
    }
    return add_filter( "{$class}::{$filter}()", $callable, $priority );
  }
}
