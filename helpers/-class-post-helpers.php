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

  /**
   * @param string $post_type
   * @param array $args
   * @return array
   */
  static function register_post_type( $post_type, $args = array() ) {

    if ( ! is_array( $args ) ) {
      $args = wp_parse_args( $args, array(
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nar_menu' => true,
        'show_in_admin_bar' => true,
        'has_archive' => true,
      ));
    }

    if ( ! isset( $args['label'] ) ) {
      $args['label'] = preg_replace( '#^' . preg_quote( Exo::short_prefix() ) . '(.*)$#', '$1', "{$post_type}s" );
    }

    if ( ! isset( $args['singular_label'] ) ) {
      $args['singular_label'] = rtrim( $args['label'], 's' );
    }

    if ( ! isset( $args['labels'] ) ) {
      $args['labels'] = self::_get_post_type_labels( $args['singular_label'], $args['label'] );
    }

    if ( ! isset( $args['description'] ) ) {
      $args['description'] = sprintf( __( 'Post type for %s', 'exo' ), strtolower( $args['label'] ) );
    }

    if ( isset( $args['supports'] ) && false === $args['supports'] ) {
      $args['supports'] = array( null );
    }

    $args = register_post_type( $post_type, $args );

    return $args;
  }


  /**
   * Builds an object with all post type labels out of a post type object
   *
   * @param string $singular
   * @param string $plural
   * @return object object with all the labels as member variables
   */
  private static function _get_post_type_labels( $singular, $plural ) {
    $lc_singular = strtolower( $singular );
    $lc_plural = strtolower( $plural );
    return array(
      'name' => $plural,
      'singular_name' => $singular,
      'add_new_item' => sprintf( __( 'Add New %s', 'exo' ), $singular ),
      'edit_item' => sprintf( __( 'Edit %s', 'exo' ), $singular ),
      'new_item' => sprintf( __( 'New %s', 'exo' ), $singular ),
      'view_item' => sprintf( __( 'View %s', 'exo' ), $singular ),
      'search_items' => sprintf( __( 'Search %s', 'exo' ), $plural ),
      'not_found' => sprintf( __( 'No %s found.', 'exo' ), $lc_plural ),
      'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'exo' ), $lc_plural ),
      'parent_item_colon' => sprintf( __('Parent %s:', 'exo'), $singular ),
      'all_items' => sprintf( __( 'All %s', 'exo' ), $plural ),
    );
 }

}

