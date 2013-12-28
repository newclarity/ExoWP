<?php

/**
 * Class Exo_Post_Collection_View_Base
 *
 * @mixin Exo_Post_Collection
 */
class Exo_Post_Collection_View_Base extends Exo_Collection_View_Base {
  const COLLECTION = 'To be set in child class';
  const VIEW_TYPE = 'post';

  /**
   * @param bool|Exo_Post_Collection $collection
   */
  function __construct( $collection = false ) {
    parent::__construct();
    $this->collection = $collection instanceof Exo_Post_Collection_Base ? $collection : new Exo_Post_Collection();
  }

  /**
   * @param callable $has_items_callback
   * @param callable $no_items_callback
   * @param array $args
   * @return array
   */
  function each( $has_items_callback, $no_items_callback, $args = array() ) {
    $return = array();
    $this->collection->each(
      function( $model, $index, $args ) use ( $has_items_callback ) {
        $return[] = call_user_func( $has_items_callback, new Exo_Post_View( $model ), $index, $args );
      },
      $no_items_callback
    );
    return $return;
  }

}




