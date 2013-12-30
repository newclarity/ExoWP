<?php

/**
 * Class Exo_Post_Collection_Base
 *
 * Base class for Post Collections
 *
 * A Post Collection is an object containing an internal array of models based on a WordPress post type.
 *
 */
abstract class Exo_Post_Collection_Base extends Exo_Collection_Base {
  const MODEL = 'Exo_Simple_Post';

  /**
   * @param callable $has_items_callback
   * @param callable $no_items_callback
   * @param array $args
   * @return array
   */
  function each( $has_items_callback, $no_items_callback, $args = array() ) {
    $return = array();
    $collection = $this;
    parent::each(
      function( $model, $index, $args ) use ( $has_items_callback, $collection ) {
        $return[] = call_user_func( $has_items_callback, $model, $index, $args, $collection );
      },
      $no_items_callback,
      $args
    );
    return $return;
  }

  /**
   * Override 'Exo_Simple_Model' with the first class for this post type.
   *
   * @param WP_Post $post
   *
   * @return string
   */
  function get_item_model_class( $post ) {
    $post_type_classes = Exo::get_post_type_classes( $post->post_type );
    return reset( $post_type_classes );
  }

}
