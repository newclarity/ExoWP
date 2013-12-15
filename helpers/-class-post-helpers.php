<?php

/**
 * Class _Exo_Post_Helpers
 *
 * Helpers that help with Exo-specific functionality.
 *
 */
class _Exo_Post_Helpers extends Exo_Helpers_Base {

  /**
   * @var WP_Query
   */
  private static $_query;

  /**
   * Get an array of WP_Post objects
   *
   * @param array $args
   *
   * @return array Array of WP_Post objects.
   */
  static function get_posts( $args = array() ) {
    $args = wp_parse_args( $args, array(
      'post_type' => 'any',
      'post_status' => 'publish',
      'posts_per_page' => 10,
      'orderby' => 'date',
      'order' => 'desc'
    ));
    self::$_query = new WP_Query( $args );
    $posts = self::$_query->posts;
    return $posts;
  }

  /**
   * @return bool|WP_Query
   */
  static function get_query() {
    return isset( self::$_query ) ? isset( self::$_query ) : false;
  }

}
