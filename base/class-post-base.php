<?php

/**
 * Base class for Post Models - i.e. models based on a WordPress post type.
 *
 * Class Exo_Post_Base
 */
abstract class Exo_Post_Base extends Exo_Model_Base {

  /**
   * The string value stored to identify a post type in the $post->post_type field.
   * Child classes should override this value.
   */
  const POST_TYPE = null;

  /**
   * @var WP_Post
   */
  private $_post;

  /**
   * @var WP_Query
   */
  private static $_query;

  /**
   * @param int|string|WP_Post $post
   */
  function __construct( $post ) {
    if ( $post instanceof WP_Post ) {
      $this->_post = $post;
    } else if ( is_numeric( $post ) ) {
      $this->_post = get_post( $post );
    } else if ( is_string( $post ) ) {
      $post_type = self::get_post_type();
      $this->_post = self::get_post_by( 'slug', $post, "post_type={$post_type}" );
    }
    parent::__construct( $post );
  }

  /**
   * @return bool|string
   */
  function get_post_type() {
    $post_type = $this->get_field_value( 'post_type' );
    if ( ! $post_type && defined( $constant_ref = get_class( $this ) . '::POST_TYPE' ) ) {
      $post_type = constant( $constant_ref );
    }
    return $post_type;
  }

  /**
   * Retrieve the value of a field and to provide a default value if no _post is set.
   *
   * @param string $field_name
   * @param mixed $default
   * @return mixed
   */
  function get_field_value( $field_name, $default = false ) {
    return isset( $this->_post->$field_name ) ? $this->_post->$field_name : $this->get_meta_value( $field_name, $default );
  }

  /**
   * Internal function to retrieve the integer value of a field and to provide a default value if no _post is set.
   *
   * @param string $field_name
   * @param mixed $default
   * @return mixed
   */
  function get_field_intval( $field_name, $default = 0 ) {
    return intval( $this->get_field_value( $field_name, $default = 0 ) );
  }

  /**
   * Retrieve the value of post meta and to provide a default value if no meta is set.
   *
   * @param string $meta_name
   * @param mixed $default
   * @return mixed
   *
   */
  function get_meta_value( $meta_name, $default = false ) {

    $meta_name = Exo::apply_meta_prefix( $meta_name );

    if ( $this->has_post() )
      $meta_value = get_post_meta( $this->_post->ID, $meta_name, true );

    if ( empty( $meta_value ) )
      $meta_value = $default;

    return $meta_value;

  }

  /**
   * @return bool
   */
  function has_post() {
    return $this->_post instanceof WP_Post && 0 != intval( $this->_post->ID );
  }

  /**
   * @return bool
   */
  function has_parent() {
    return 0 < $this->get_parent_id();
  }

  /**
   * @return bool
   */
  function is_valid_post() {
    return $this->has_post() && 0 < intval( $this->_post->ID );
  }

  /**
   * @return string
   */
  function get_ID() {
    return $this->get_field_intval( 'ID', 0 );
  }

  /**
   * @return int
   */
  function get_parent_id() {
    return $this->get_field_intval( 'post_parent' );
  }

  /**
   * @return int
   */
  function get_menu_order() {
    return $this->get_field_intval( 'menu_order', 0 );
  }

  /**
   * @param array $args
   * @return string
   */
  function get_title() {
    $title = $this->has_post() ? get_the_title( $this->_post->ID ) : false;
    return $title;
  }

  /**
   * @return string
   */
  function get_content() {
    $content = false;
    if ( $this->has_post() ) {
      global $post;
      $save_post = $post;
      $post = $this->_post;
      $content = apply_filters( 'the_content', $this->_post->post_content );
      $post = $save_post;
    }
    return $content;
  }

  /**
   * @return string
   */
  function get_excerpt() {
    $excerpt = false;
    if ( $this->has_post() ) {
      global $post;
      $save_post = $post;
      $post = $this->_post;
      $excerpt = ! empty( $post->post_excerpt ) ? apply_filters( 'get_the_excerpt', $post->post_excerpt ) : false;
      $post = $save_post;
    }
    return $excerpt;
  }

  /**
   * @param string $by
   * @param int|string $value
   * @param array $args
   *
   * @return WP_Post
   */
  static function get_post_by( $by, $value, $args = array() ) {
    $post = false;
    $criteria = array(
      'post_status' => 'publish',
    );
    switch ( $by ) {
      case 'slug':
        $criteria['name'] = trim( $value );
        break;

      case 'post_id':
      case 'post_ID':
      case 'id':
      case 'ID':
        $criteria['p'] = intval( $value );
        break;
    }
    self::$_query = new WP_Query( wp_parse_args( $args, $criteria ) );
    if ( count( self::$_query->posts ) ) {
      $post = self::$_query->post;
    }
    return $post;
  }

  /**
   * @param array $args
   * @return array
   */
  function get_posts( $args = array() ) {
    $args = wp_parse_args( $args );
    $args['post_type'] = ( $post_type = $this->get_post_type() ) ? $post_type : 'post';
    $posts = Exo::get_posts( $args );
    return $posts;
  }
}
