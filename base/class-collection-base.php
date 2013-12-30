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
  const MODEL = 'Exo_Simple';

  /**
   * @var array
   */
  private $_items;

  /**
   * @var array
   */
  private $_models = array();

  /**
   * @param array $items
   *
   * @todo Update to accept an array of Models.
   */
  function __construct( $items = array() ) {
    $this->_items = is_array( $items ) ? $items : array();
  }

  /**
   * Returns the best guess at the Model class to use for this collection.
   *
   * The stored Model overrides because we might be using a generic collection and need a more specific view.
   * If you want the actual declared class name use $this->get_declared_model_class().
   *
   * @var array $args
   * @return string
   */
  function get_model_class( $args = array() ) {
    $args = wp_parse_args( $args, array(
      'default' => self::MODEL,
      'item' => false,
    ));
    if ( $this->_models_insync_with_items() ) {
      $model_class = get_class( reset( $this->_models ) );
    } else if ( $args['item'] ) {
      $model_class = $this->get_item_model_class( $args['item'] );
    }
    return apply_filters( 'exo_collection_model_class', $model_class, $this, $args['default'] );
  }

  /**
   * Meant to be overridden in child classes.
   *
   * @param mixed $item
   *
   * @return string
   */
  function get_item_model_class( $item ) {
    return self::MODEL;
  }

  /**
   * Inspection $this->_models to see if they are in sync with $this->_items.
   *
   * @return bool
   */
  private function _models_insync_with_items() {
    if ( $insync = count( $this->_models ) == count( $this->_items ) ) {
      foreach( $this->_items as $item ) {
        $hash = self::get_item_hash( $item );
        if ( ! isset( $this->_models[$hash] ) || $item !== $this->_models[$hash] ) {
          $insync = false;
          break;
        }
      }
    }
    return $insync;
  }

  /**
   * Returns the model's class as declared in this collection.
   *
   * @var string $default Default Model class name.
   *
   * @return string Model class name
   *
   */
  function get_declared_model_class( $default = 'Exo_Simple' ) {
    return Exo::get_class_declaration( 'MODEL', get_class( $this ), $default );
  }

  /**
   * Return an hash for an item that can be used to index the $this->_models array.
   *
   * @param mixed $item
   *
   * @return string
   *
   */
  static function get_item_hash( $item ) {
    return is_object( $item ) ? spl_object_hash( $item ) : md5( serialize( $item ) );
  }

  /**
   * @param bool|array $items
   *
   * @return array
   */
  function get_model_objects( $items = false ) {
    if ( ! $items ) {
      $items = $this->_items;
    }
    $model_keys = array_flip( array_keys( $this->_models ) );
    foreach( $items as $index => $item ) {
      $hash = self::get_item_hash( $item );
      /**
       * @var Exo_Model_Base $model
       */
      if ( ! isset( $this->_models[$hash] ) ){
        $model_class = $this->get_model_class( array( 'item' => $item ) );
        $this->_models[$hash] = new $model_class( $item );
      }
      unset( $model_keys[$hash] );
    }
    /**
     * Now remove any that had been deleted
     */
    foreach( $model_keys as $hash ) {
      unset( $this->_models[$hash] );
    }
    return array_values( $this->_models );
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
      $models = $this->get_model_objects();
      foreach( $this->_items as $index => $item ) {
        $return[$index] = call_user_func( $has_items_callback, $models[$index], $index, $args, $this );
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
  function items() {
    return $this->_items;
  }

  /**
   * Returns the default View class name for this Collection.
   *
   * Defaults to the Collection's class name plus '_View'  but can be modified via 'exo_collection_view_class' hook.
   *
   * @return string
   */
  function get_view_class() {
    $view_class = Exo::get_class_declaration( 'DEFAULT_VIEW', $collection_class = get_class( $this ) );
    if ( ! class_exists( $view_class, false ) ) {
      $view_class = "{$collection_class}_View";
    }
    if ( ! class_exists( $view_class, false ) ) {
      $view_class = "Exo_Simple_Collection_View";
    }
    $view_class = apply_filters( 'exo_collection_view_class', $view_class, $collection_class, $this );
    if ( ! class_exists( $view_class, false ) ) {
      $message = __( 'Default View class %s for Collection class %s not defined.', 'exo' );
      Exo::trigger_warning( $message, $view_class, $collection_class );
    }
    return $view_class;
  }


}
