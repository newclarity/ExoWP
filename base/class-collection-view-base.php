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
   * @var array
   */
  private $_views = array();

  /**
   *
   */
  function get_view_objects() {
    $views = $this->_views;
    $view_keys = array_flip( array_keys( $views ) );
    $collection_view = $this;
    $this->collection->each(
      function( $model, $index, $args ) use ( &$views, &$view_keys, $collection_view ) {
        /**
         * @var Exo_Model_Base $model
         */
        if ( ! isset( $views[$hash = spl_object_hash( $model )] ) ){
          $view_class = $model->get_view_class();
          $views[$hash] = new $view_class( $model );
        }
        unset( $view_keys[$hash] );
      },
      function( $collection ) {}
    );
    /**
     * Now remove any that had been deleted
     */
    foreach( $view_keys as $hash ) {
      unset( $views[$hash] );
    }
    return array_values( $this->_views = $views );
  }

  /**
   * @param callable $has_items_callback
   * @param callable $no_items_callback
   * @param array $args
   * @return array
   */
  function each( $has_items_callback, $no_items_callback, $args = array() ) {
    $return = array();
    $views = $this->get_view_objects();
    $collection = $this;
    $this->collection->each(
      function( $item, $index, $args ) use ( &$return, $has_items_callback, $views, $collection ) {
        $return[] = call_user_func( $has_items_callback, $views[$index], $index, $args, $collection );
      },
      $no_items_callback,
      $args
    );
    return $return;
  }

  /**
   * @param string $method_name
   * @param array $args
   * @return mixed
   */
  function __call( $method_name, $args = array() ) {
    $value = null;
    if ( $this->has_mixin( $method_name ) ) {
      $value = $this->__call( $method_name, $args );
    } else if ( ! is_null( $this->collection ) && method_exists( $this->collection, $method_name ) ) {
      $args[] = array( 'collection_view' => $this );
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




