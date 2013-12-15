<?php

/**
 * Class Exo_Collection_Base
 *
 * Base class for Collections
 *
 * A Collection is an object containing an internal array of models.
 *
 */
abstract class Exo_Collection_Base extends Exo_Instance_Base {
  const MODEL = 'post';

  /**
   * @var array
   */
  private $_items;

  /**
   * @param array $collection
   */
  function __construct( $items = array() ) {
    $this->_items = is_array( $items ) ? $items : array();
  }

  /**
   * @param callable $has_items_callback
   * @param callable $no_items_callback
   * @param array $args
   * @return array
   */
  function each( $has_items_callback, $no_items_callback, $args = array() ) {
    if ( 0 == count( $this->_items ) ) {
      $return = call_user_func( $no_items_callback, $args );
    } else {
      $return = array();
      foreach( $this->_items as $index => $item ) {
        $return[$index] = call_user_func( $has_items_callback, $item, $index, $args );
      }
    }
    return $return;
  }
}
