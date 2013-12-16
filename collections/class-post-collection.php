<?php

/**
 * Class Exo_Post_Collection
 */
class Exo_Post_Collection extends Exo_Post_Collection_Base {
  const MODEL = 'Exo_Post';

  /**
   * @param callable $has_items_callback
   * @param callable $no_items_callback
   * @param array $args
   * @return array
   */
  function each( $has_items_callback, $no_items_callback, $args = array() ) {
    $return = array();
    parent::each(
      function( $item, $index, $args ) use ( $has_items_callback ) {
        $return[] = call_user_func( $has_items_callback, new Exo_Post( $item ), $index, $args );
      },
      $no_items_callback
    );
    return $return;
  }

}




