<?php

/**
 * Class Exo_Custom_Post
 *
 * Generic class for Models where post_type is a custom post type.
 *
 * This class can be used by themers who are not comfortable with defining their own classes.
 */
class Exo_Custom_Post extends Exo_Post_Base {
  const POST_TYPE = false;

  /**
   * Return the post type for this instance's post.
   *
   * Since we can't derive the post type from the POST_TYPE constant, look in the field post_type instead.
   *
   * @return bool|string
   */
  function get_post_type() {
    if ( ! ( $post_type = parent::get_post_type() ) && $this->has_post() ) {
      $post_type = $this->get_field_value( 'post_type' );
    };
    return $post_type;
  }

}
