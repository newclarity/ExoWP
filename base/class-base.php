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
      $method = 10 == $priority ? $filter : "{$filter}_{$priority}";
      $callable = array( $class = get_called_class(), $method );
      if ( is_numeric( $method_or_priority ) ) {
        $priority = $method_or_priority;
      }
    }
    return add_filter( "{$class}::{$method}()", $callable, $priority );
  }

}
