<?php

/**
 * Class Exo_Helpers
 *
 * Helpers that help with Exo-specific functionality.
 *
 */
class _Exo_Helpers extends Exo_Helpers_Base {

  /**
   * @param bool|string $class_name
   *
   * @return bool|mixed
   */
  static function get_class_slug( $class_name = false ) {
    return self::get_class_constant( 'SLUG', $class_name );
  }

  /**
   * @param bool|string $class_name
   *
   * @return bool|mixed
   */
  static function get_class_post_type( $class_name = false ) {
    return self::get_class_constant( 'POST_TYPE', $class_name );
  }

  /**
   * @param string $constant_name
   * @param bool|string $class_name
   *
   * @return bool|mixed
   */
  static function get_class_constant( $constant_name, $class_name = false ) {
     if ( ! $class_name ) {
      $class_name = get_called_class();
    }
    return defined( $constant_ref = "{$class_name}::{$constant_name}" ) ? constant( $constant_ref ) : false;
  }

  /**
   * @param string $message
   * @param bool|string $arg1
   * @param bool|string $arg2
   * @param bool|string $arg3
   * @param bool|string $arg4
   * @param bool|string $arg5
   */
  static function trigger_warning( $message, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    $args = func_get_args();
    $message = array(
      call_user_func_array( 'sprintf', $args ),
      __( "\nCall Stack:", 'exo' ),
    );
    if ( version_compare( PHP_VERSION, '5.3.6', '>=') ) {
      $backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS );
    } else {
      $backtrace = debug_backtrace();
    }
    for( $i = count( $backtrace ) - 1; $i > 0; $i-- ) {
      $call = $backtrace[$i];
      $function = "{$call['function']}() ";
      if ( isset( $call['object'] ) && isset( $call['type'] ) ) {
        $function = get_class( $call['object'] ) . "{$call['type']}{$function}";
      }
      $message[] = "\n  " . sprintf( __( 'Called %sin %s on line %s', 'exo' ), $function, $call['file'], $call['line'] );
    }
    $message[] = "\n  " . __( 'Called ' . __CLASS__ . '::trigger_error()', 'exo' );
    trigger_error( implode( $message ), E_USER_WARNING );
  }
}
