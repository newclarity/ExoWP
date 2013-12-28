<?php

/**
 * Class _Exo_Taxonomy_Helpers
 *
 * @mixin Exo_Main_Base
 *
 * Helpers that help with Exo-specific functionality.
 *
 */
class _Exo_Taxonomy_Helpers extends Exo_Helpers_Base {

  /**
   * @var array
   */
  private static $_exo_taxonomies = array();

  /**
   * @return array
   */
  static function get_exo_taxonomies() {
    return self::$_exo_taxonomies;
  }

  /**
   * @param array $exo_taxonomies
   */
  static function set_exo_taxonomies( $exo_taxonomies ) {
    self::$_exo_taxonomies = $exo_taxonomies;
  }

  /**
   * @param object $term
   *
   * @return Exo_View_Base
   */
  static function get_taxonomy_term_view( $term ) {
    trigger_error( __CLASS__ . '::' . __FUNCTION__ . '() not yet implemented.', E_USER_ERROR );
    return $view;
  }


//  /**
//   * @var WP_Query
//   */
//  private static $_query;
//
//  /**
//   * @var array
//   */
//  private static $_taxonomies_classes = array();
//
//  /**
//   * @var array
//   */
//  private static $_classes_taxonomy = array();
//
//  /**
//   * Scan the list of $classes from get_declared_classes() and register it's POST_TYPE constant, if one exists
//   *
//   * @note All classes must be loaded to call this.
//   */
//  static function _record_taxonomies() {
//    $data = array(
//      'classes_taxonomy' => self::$_classes_taxonomy,
//      'taxonomies_classes' => self::$_taxonomies_classes,
//      'exo_taxonomies' => self::$_exo_taxonomies,
//    );
//    Exo::walk_declared_classes( function( $class_name ) use ( &$data ) {
//      if ( is_subclass_of( $class_name, 'Exo_Taxonomy_Base' ) ) {
//
//        if ( $taxonomy = _Exo_Helpers::get_class_declaration( 'POST_TYPE', $class_name ) ) {
//          $data['classes_taxonomy'][$class_name] = $taxonomy;
//          if ( ! isset( $data['taxonomies_classes'][$taxonomy] ) ) {
//            $data['taxonomies_classes'][$taxonomy] = array( $class_name );
//          } else {
//            $data['taxonomies_classes'][$taxonomy][] = $class_name;
//          }
//          if ( $taxonomy_args = _Exo_Helpers::get_class_declaration( 'POST_TYPE_ARGS', $class_name ) ) {
//            $data['exo_taxonomies'][$class_name] = $taxonomy_args;
//          }
//        }
//      }
//    });
//    self::$_classes_taxonomy = $data['classes_taxonomy'];
//    self::$_taxonomies_classes = $data['taxonomies_classes'];
//    self::$_exo_taxonomies = $data['exo_taxonomies'];
//  }
//
//  /**
//   * Get an array of WP_Taxonomy objects
//   *
//   * @param array $args
//   *
//   * @return array Array of WP_Taxonomy objects.
//   */
//  static function get_posts( $args = array() ) {
//    $args = wp_parse_args( $args, array(
//      'taxonomy' => 'any',
//      'post_status' => 'publish',
//      'posts_per_page' => 10,
//      'orderby' => 'date',
//      'order' => 'desc'
//    ));
//    self::$_query = new WP_Query( $args );
//    $posts = self::$_query->posts;
//    return $posts;
//  }
//
//  /**
//   * @return bool|WP_Query
//   */
//  static function get_query() {
//    return isset( self::$_query ) ? isset( self::$_query ) : false;
//  }
//
//  /**
//   * @param string $taxonomy
//   * @param array $args
//   */
//  static function register_taxonomy( $taxonomy, $args = array() ) {
//    self::$_exo_taxonomies[$taxonomy] = $args;
//  }
//
//  /**
//   * Takes an array of zero or more Taxonomy Type info arrays and registers them.
//   *
//   * The post type string is the array key and the array value contains the post type $args.
//   */
//  static function _fixup_taxonomies() {
//    foreach( self::$_exo_taxonomies as $taxonomy => $args ) {
//      self::_register_taxonomy( $taxonomy, $args );
//    }
//  }
//
//  /**
//   * @param string $taxonomy
//   * @param array $args
//   */
//  private static function _register_taxonomy( $taxonomy, $args ) {
//    if ( ! is_array( $args ) ) {
//      $args = wp_parse_args( $args, array(
//        'public' => true,
//        'publicly_queryable' => true,
//        'show_ui' => true,
//        'show_in_menu' => true,
//        'show_in_nar_menu' => true,
//        'show_in_admin_bar' => true,
//        'has_archive' => true,
//      ));
//    }
//
//    if ( ! isset( $args['label'] ) ) {
//      $args['label'] = preg_replace( '#^' . preg_quote( self::short_prefix() ) . '(.*)$#', '$1', "{$taxonomy}s" );
//    }
//
//    if ( ! isset( $args['singular_label'] ) ) {
//      $args['singular_label'] = rtrim( $args['label'], 's' );
//    }
//
//    if ( ! isset( $args['labels'] ) ) {
//      $args['labels'] = self::_get_taxonomy_labels( $args['singular_label'], $args['label'] );
//    }
//
//    if ( ! isset( $args['description'] ) ) {
//      $args['description'] = sprintf( __( 'Taxonomy type for %s', 'exo' ), strtolower( $args['label'] ) );
//    }
//
//    if ( isset( $args['supports'] ) && false === $args['supports'] ) {
//      $args['supports'] = array( null );
//    }
//
//    register_taxonomy( $taxonomy, $args );
//  }
//
//  /**
//   * Builds an object with all post type labels out of a post type object
//   *
//   * @param string $singular
//   * @param string $plural
//   * @return object object with all the labels as member variables
//   */
//  private static function _get_taxonomy_labels( $singular, $plural ) {
//    $lowercase_plural = strtolower( $plural );
//    return array(
//      'name' => $plural,
//      'singular_name' => $singular,
//      'add_new_item' => sprintf( __( 'Add New %s', 'exo' ), $singular ),
//      'edit_item' => sprintf( __( 'Edit %s', 'exo' ), $singular ),
//      'new_item' => sprintf( __( 'New %s', 'exo' ), $singular ),
//      'view_item' => sprintf( __( 'View %s', 'exo' ), $singular ),
//      'search_items' => sprintf( __( 'Search %s', 'exo' ), $plural ),
//      'not_found' => sprintf( __( 'No %s found.', 'exo' ), $lowercase_plural ),
//      'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'exo' ), $lowercase_plural ),
//      'parent_item_colon' => sprintf( __('Parent %s:', 'exo'), $singular ),
//      'all_items' => sprintf( __( 'All %s', 'exo' ), $plural ),
//    );
// }
//
//  /**
//   * @param bool|string $class_name
//   *
//   * @return bool|mixed
//   */
//  static function get_class_taxonomy( $class_name = false ) {
//    if ( isset( self::$_classes_taxonomy[$class_name] ) ) {
//      $taxonomy = self::$_classes_taxonomy[$class_name];
//    } else {
//      $taxonomy = _Exo_Helpers::get_class_constant( 'POST_TYPE', $class_name );
//    }
//    return $taxonomy;
//  }
//
//  /**
//   * Get the list of classes for each post type, or
//   * get the class for a post type.
//   *
//   * This function is driven by needed POST_TYPE contants in each class.
//   *
//   * @param bool|int|string $taxonomy
//   * @return array
//   */
//  static function get_taxonomy_classes( $taxonomy = false ) {
//    if ( ! Exo::is_exo_init() ) {
//      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
//      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
//    }
//    return isset( self::$_taxonomies_classes[$taxonomy] ) ? self::$_taxonomies_classes[$taxonomy] : array();
//  }
//
//  /**
//   * Returns array of 'Exo Taxonomy Types'.
//   *
//   * 'Exo Taxonomy Types' are Custom Taxonomy Types registered via Exo functions vs. registered directly via register_taxonomy().
//   *
//   *  Returns the 'raw' $args as registered, not as manipulated by the register_taxonomy() method call.
//   *
//   * @return array Associative array where post type strings are keys and registered $args are their values.
//   */
//  static function get_exo_taxonomies() {
//    if ( ! Exo::is_exo_init() ) {
//      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
//      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
//    }
//    return self::$_exo_taxonomies;
//  }
//
//  /**
//   * Returns array keyed by Class name with a value of associated Taxonomy Type.
//   *
//   * @returns array
//   */
//  static function get_classes_taxonomy() {
//    if ( ! Exo::is_exo_init() ) {
//      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
//      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
//    }
//    return self::$_classes_taxonomy;
//  }
//
//  /**
//   * Returns array keyed by Taxonomy Type Class name with an array value of one or more Class names for that Taxonomy Type.
//   *
//   * @note Does not include "non-Exo Taxonomy Types"; i.e. only includes post types registered via Exo methods.
//   *
//   * @returns array
//   */
//  static function get_taxonomies_classes() {
//    if ( ! Exo::is_exo_init() ) {
//      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
//      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
//    }
//    return self::$_taxonomies_classes;
//  }

}
