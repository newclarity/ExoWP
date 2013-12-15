<?php

/**
 * Class Exo_Collection_View_Base
 */
abstract class Exo_Collection_View_Base extends Exo_View_Base {
  const COLLECTION = 'Exo_Collection_Base';

  /**
   * @var Exo_Collection_Base
   */
  var $collection;

  /**
   * @param string $method_name
   * @param array $args
   * @return mixed
   */
  function __call( $method_name, $args = array() ) {
    $value = null;
    $args[] = $this;
    if ( $this->has_mixin( $method_name ) ) {
      $value = $this->__call( $method_name, $args );
    } else if ( ! is_null( $this->collection ) && method_exists( $this->collection, $method_name ) ) {
      $value = call_user_func_array( array( $this->collection, $method_name ), $args );
    } else if ( ! is_null( $this->collection ) && $this->collection->has_mixin( $method_name ) ) {
      $value = $this->collection->__call( $method_name, $args );
    } else {
      $message = __( 'Neither view class %s nor collection class %s has %s() as a direct or mixin method.', 'exo' );
      $collection_class = ! empty( $this->collection ) ? get_class( $this->collection ) : '[n/a]';
      Exo::trigger_warning( $message, get_class( $this ), $collection_class, $method_name );
    }
    return $value;
  }

}




