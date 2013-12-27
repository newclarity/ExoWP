<?php

/**
 * Class _Exo_Hook_Helpers
 *
 * Helpers that help with WP_Filesystem, etc.
 *
 */
class _Exo_Hook_Helpers extends Exo_Helpers_Base {
  /**
   * @var array
   */
  private static $_hooks = array();

  /**
   * @return array
   */
  static function _get_hooks() {
    return self::$_hooks;
  }

  /**
   * @param array $hooks
   */
  static function _set_hooks( $hooks ) {
    self::$_hooks = $hooks;
  }

  /**
   * Collect the hooks registered for each class.
   */
  static function _record_hooks() {
    $self_hooks = self::$_hooks;
    Exo::walk_declared_classes( function( $class_name ) use ( &$self_hooks ) {
      if ( $hooks = _Exo_Helpers::get_class_declaration( 'HOOKS', $class_name, false ) ) {
        foreach( $hooks as $hook_args ) {
          if ( ! preg_match( '#^add_(instance_)?(action|filter)$#', $hook_args[0] ) ) {
            /*
             * Filter out add_action, add_filter, add_instance_action, add_instance_filter
             * Add the classname as the final arg.
             */
            array_push( $hook_args, $class_name );
          }
          $self_hooks[] = $hook_args;
        }
      }
    });
    self::$_hooks = $self_hooks;
  }

  /**
   * Add the hook registered for all classes.
   */
  static function _add_hooks() {
    foreach( self::$_hooks as $args ) {
      $hook_type = array_shift( $args );
      switch ( $hook_type ) {
        case 'add_action':
        case 'add_filter':
          call_user_func_array( $hook_type, $args );
          break;

        case 'add_instance_action':
        case 'add_instance_filter':
          call_user_func_array( array( array_pop( $args ), $hook_type ), $args );
          break;

        case 'add_class_action':
        case 'add_class_filter':
        case 'add_static_action':
        case 'add_static_filter':
          /**
           * Get the class name from the end and put at the beginning.
           */
          array_unshift( $args, array_pop( $args ) );
          call_user_func_array( array( __CLASS__, $hook_type ), $args );
          break;

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
    return self::add_class_filter( $class, $action, $method_or_priority, $priority );
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
    if ( is_string( $method_or_priority ) ) {
      $callable = array( $class, "_{$method_or_priority}" );
    } else {
      $callable = array( $class, "_{$filter}" );
      if ( is_numeric( $method_or_priority ) ) {
        $priority = $method_or_priority;
      }
    }
    if ( 10 <> $priority && isset( $callable[1] ) && ! preg_match( "#_{$priority}$#", $callable[1] ) ) {
      $callable[1] .= "_{$priority}";
    }
    return add_filter( "{$class}::{$filter}()", $callable, $priority );
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
    return self::add_static_filter( $class, $action, $method_or_priority, $priority );
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
    if ( is_string( $method_or_priority ) ) {
      $callable = array( $class, "_{$method_or_priority}" );
    } else {
      $callable = array( $class, "_{$filter}" );
      if ( is_numeric( $method_or_priority ) ) {
        $priority = $method_or_priority;
      }
    }
    if ( 10 <> $priority && isset( $callable[1] ) && ! preg_match( "#_{$priority}$#", $callable[1] ) ) {
      $callable[1] .= "_{$priority}";
    }
    return add_filter( $filter, $callable, $priority );
  }

  /**
   * @param object $instance
   * @param string $action
   * @param bool|int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_instance_action( $instance, $action, $callable_or_priority = false, $priority = 10 ) {
    return self::add_instance_filter( $instance, $action, $callable_or_priority, $priority );
  }

  /**
   * @param object $instance
   * @param string $filter
   * @param bool|int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function add_instance_filter( $instance, $filter, $callable_or_priority = false, $priority = 10 ) {
    if ( false === $callable_or_priority ) {
      $callable = array( $instance, "_{$filter}" );
    } else if ( is_callable( $callable_or_priority ) ) {
      $callable = $callable_or_priority;
    } else if ( is_numeric( $callable_or_priority ) ) {
      $callable = array( $instance, "_{$filter}" );
      $priority = $callable_or_priority;
    }
    if ( 10 <> $priority && isset( $callable[1] ) && ! preg_match( "#_{$priority}$#", $callable[1] ) ) {
      $callable[1] .= "_{$priority}";
    }
    $object_hash = spl_object_hash( $instance );
    return add_filter( "{$object_hash}->{$filter}()", $callable, $priority, 99 );
  }

  /**
   * @param object $instance
   * @param string $action
   * @param bool|int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool
   */
  static function remove_instance_action( $instance, $action, $callable_or_priority = false, $priority = 10 ) {
    return self::remove_instance_filter( $instance, $action, $callable_or_priority, $priority );
  }

  /**
   * @param object $instance
   * @param string $filter
   * @param int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  static function remove_instance_filter( $instance, $filter, $callable_or_priority, $priority = 10 ) {
    if ( is_callable( $callable_or_priority ) ) {
      $callable = $callable_or_priority;
    } else if ( is_numeric( $callable_or_priority ) ) {
      $callable = array( $instance, $filter );
      $priority = $callable_or_priority;
    }
    if ( 10 <> $priority && isset( $callable[1] ) && ! preg_match( "#_{$priority}$#", $callable[1] ) ) {
      $callable[1] .= "_{$priority}";
    }
    $object_hash = spl_object_hash( $instance );
    return remove_filter( "{$object_hash}->{$filter}()", $callable, $priority, 99 );
  }


}


