<?php

/**
 * Class Exo_Collection_Base
 *
 * Base class for Collections
 *
 * A Collection is an object containing an internal array of models.
 *
 */
abstract class Exo_Collection_Base extends Exo_Instance_Base implements ArrayAccess {
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

  /**
   * @return int
   */
  function count() {
    return count( $this->_items );
  }

  /**
   * @param int|string $index
   *
   * @return bool
   */
  function offsetExists( $index ) {
    return isset( $this->_items[$index] );
  }

  /**
   * @param int|string $index
   *
   * @return bool
   */
  function offsetGet( $index ) {
    if ( $this->offsetExists( $index ) ) {
      return $this->_items[$index];
    }
    return false;
  }

  /**
   * @param int|string $index
   * @param mixed $value
   *
   * @return bool
   */
  function offsetSet( $index, $value ) {
    if ( $index ) {
      $this->_items[$index] = $value;
    } else {
      $this->_items[] = $value;
    }
    return true;
  }

  /**
   * @param int|string $index
   *
   * @return bool
   */
  function offsetUnset( $index ) {
    unset( $this->_items[$index] );
    return true;
  }

  /**
   * @return array
   */
  function to_items() {
    return $this->_items;
  }

}
