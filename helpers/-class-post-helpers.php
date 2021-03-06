<?php

/**
 * Class _Exo_Post_Helpers
 *
 * @mixin Exo_Main_Base
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
   * @var array
   */
  private static $_post_types_classes = array();

  /**
   * @var array
   */
  private static $_classes_post_type = array();

  /**
   * @var array
   */
  private static $_exo_post_types = array();

  /**
   * @return array
   */
  static function _get_exo_post_types() {
    return self::$_exo_post_types;
  }

  /**
   * @param array $exo_post_types
   */
  static function _set_exo_post_types( $exo_post_types ) {
    self::$_exo_post_types = $exo_post_types;
  }

  /**
   * @param WP_Post|Exo_Post $post_or_model
   * @param array $args
   * @return Exo_Post_View_Base
   * @todo Cache views so only one is created given the same parameter and $args.
   */
  static function get_post_view( $post_or_model, $args = array() ) {
    $post_model = self::get_post_model( $post_or_model, $args );
    $args['view_class'] = isset( $args['view_class'] ) ? $args['view_class'] : $post_model->get_view_class();
    $view_class = apply_filters( 'exo_post_view_class', $args['view_class'], $args );
    return new $view_class( $post_model );
  }

  /**
   * @param int|string|WP_Post|Exo_Post_Base $post_or_model
   * @param array $args
   * @return Exo_Post_Base
   * @todo Cache models so only one is created given the same parameter and $args.
   */
  static function get_post_model( $post_or_model, $args = array() ) {
    $post_model = false;
    $post_type = false;
    if ( is_subclass_of( $post_or_model, 'Exo_Post_Base' )  ) {
      $post_model = $post_or_model;
    } else if ( $post_or_model instanceof WP_Post ) {
      $post = $post_or_model;
      $post_type = $post->post_type;
    } else if ( is_numeric( $post_or_model ) ) {
      $post = get_post( $post_or_model );
      $post_type = $post->post_type;
    }
    if ( $post_type ) {
      $model_classes = self::get_post_model_classes( $post_type, $args );
      if ( isset( $model_classes[0] ) ) {
        $model_class = $model_classes[0];
        $post_model = new $model_class( $post );
      }
    }
    return $post_model;
  }


  /**
   * Return list of class names for Post Model classes given a $post type.
   *
   * @param string $post_type  // @todo Enable passing $post, $post_id or Model.
   *
   * @return array
   */
  static function get_post_model_classes( $post_type ) {
    /**
     * Filter class names for Post Model classes given a $post type.
     * @param array $post_type_classes Array of post type class names, unique.
     */
    return apply_filters( 'exo_post_model_classes', Exo::get_post_type_classes( $post_type ) );
  }

  /**
   * @param array $posts
   * @return Exo_Post_Collection_Base
   */
  static function get_post_collection( $posts ) {
    $model_class = $no_items = false;
    if ( ! is_array( $posts ) || 0 == count( $posts ) ) {
      $no_items = true;
    } else {
      $post = reset( $posts );
      if ( $post instanceof WP_Post ) {
        $post_types = Exo::array_collect_unique( $posts, 'post_type' );
        /**
         * @var WP_Post $post
         */
        if ( 1 < count( $post_types ) ) {
          $message = '%s::%s not implemented yet for multiple Collection classes per Model class.';
          Exo::trigger_warning( $message, __CLASS__, __FUNCTION__ );
        } else if ( 0 < count( $model_classes = self::get_post_model_classes( $post->post_type ) ) ) {
          $model_class = reset( $model_classes );
        }
      } else if ( $item instanceof Exo_Model_Base ) {
        $model_class = get_class( $post );
      }
      if ( $no_items || ! $model_class ) {
        $collection_class = 'Exo_Simple_Post_Collection';
      } else {
        $collection_classes = Exo::get_model_collection_classes( $model_class );
        $collection_class = reset( $collection_classes );
      }
    }
    $collection = new $collection_class( $posts );
    return $collection;
  }

  /**
   * @param array|Exo_Collection_Base $posts
   * @return Exo_Post_Collection_View_Base
   */
  static function get_post_collection_view( $posts ) {
    if ( is_array( $posts ) ) {
      $collection = self::get_post_collection( $posts );
    } else if ( $posts instanceof Exo_Post_Collection_Base ) {
      $collection = $posts;
    } else {
      $collection = self::get_post_collection( array() );
    }
    $collection_view_class = $collection->get_view_class();
    $collection_view = new $collection_view_class( $collection );
    return $collection_view;
  }

  /**
   * Scan the list of $classes from get_declared_classes() and register it's POST_TYPE constant, if one exists
   *
   * @note All classes must be loaded to call this.
   */
  static function _record_post_types() {
    $data = array(
      'classes_post_type' => self::$_classes_post_type,
      'post_types_classes' => self::$_post_types_classes,
      'exo_post_types' => self::$_exo_post_types,
    );
    Exo::walk_declared_classes( function( $class_name ) use ( &$data ) {
      if ( is_subclass_of( $class_name, 'Exo_Post_Base' ) ) {

        if ( $post_type = _Exo_Helpers::get_class_declaration( 'POST_TYPE', $class_name ) ) {
          $data['classes_post_type'][$class_name] = $post_type;
          if ( ! isset( $data['post_types_classes'][$post_type] ) ) {
            $data['post_types_classes'][$post_type] = array( $class_name );
          } else {
            $data['post_types_classes'][$post_type][] = $class_name;
          }
          if ( $post_type_args = _Exo_Helpers::get_class_declaration( 'POST_TYPE_ARGS', $class_name ) ) {
            $data['exo_post_types'][$post_type] = $post_type_args;
          }
        }
      }
    });
    self::$_classes_post_type = $data['classes_post_type'];
    self::$_post_types_classes = $data['post_types_classes'];
    self::$_exo_post_types = $data['exo_post_types'];
  }

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
   */
  static function register_post_type( $post_type, $args = array() ) {
    self::$_exo_post_types[$post_type] = $args;
  }

  /**
   * Takes an array of zero or more Post Type info arrays and registers them.
   *
   * The post type string is the array key and the array value contains the post type $args.
   */
  static function _fixup_post_types() {
    foreach( self::$_exo_post_types as $post_type => $args ) {
      self::_register_post_type( $post_type, $args );
    }
  }

  /**
   * @param string $post_type
   * @param array $args
   */
  private static function _register_post_type( $post_type, $args ) {
    $args = wp_parse_args( $args, array(
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nar_menu' => true,
      'show_in_admin_bar' => true,
      'has_archive' => true,
    ));

    if ( ! isset( $args['label'] ) ) {
      $args['label'] = preg_replace( '#^' . preg_quote( self::short_prefix() ) . '(.*)$#', '$1', "{$post_type}s" );
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

    register_post_type( $post_type, $args );
  }

  /**
   * Builds an object with all post type labels out of a post type object
   *
   * @param string $singular
   * @param string $plural
   * @return object object with all the labels as member variables
   */
  private static function _get_post_type_labels( $singular, $plural ) {
    $lowercase_plural = strtolower( $plural );
    return array(
      'name' => $plural,
      'singular_name' => $singular,
      'add_new_item' => sprintf( __( 'Add New %s', 'exo' ), $singular ),
      'edit_item' => sprintf( __( 'Edit %s', 'exo' ), $singular ),
      'new_item' => sprintf( __( 'New %s', 'exo' ), $singular ),
      'view_item' => sprintf( __( 'View %s', 'exo' ), $singular ),
      'search_items' => sprintf( __( 'Search %s', 'exo' ), $plural ),
      'not_found' => sprintf( __( 'No %s found.', 'exo' ), $lowercase_plural ),
      'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'exo' ), $lowercase_plural ),
      'parent_item_colon' => sprintf( __('Parent %s:', 'exo'), $singular ),
      'all_items' => sprintf( __( 'All %s', 'exo' ), $plural ),
    );
 }

  /**
   * @param bool|string $class_name
   *
   * @return bool|mixed
   */
  static function get_class_post_type( $class_name = false ) {
    if ( isset( self::$_classes_post_type[$class_name] ) ) {
      $post_type = self::$_classes_post_type[$class_name];
    } else {
      $post_type = _Exo_Helpers::get_class_constant( 'POST_TYPE', $class_name );
    }
    return $post_type;
  }

  /**
   * Get the list of classes for each post type, or
   * get the class for a post type.
   *
   * This function is driven by needed POST_TYPE contants in each class.
   *
   * @param bool|int|string $post_type
   * @return array
   */
  static function get_post_type_classes( $post_type = false ) {
    if ( ! Exo::is_exo_init() ) {
      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
    }
    return isset( self::$_post_types_classes[$post_type] ) ? self::$_post_types_classes[$post_type] : array();
  }

  /**
   * Returns array of 'Exo Post Types'.
   *
   * 'Exo Post Types' are Custom Post Types registered via Exo functions vs. registered directly via register_post_type().
   *
   *  Returns the 'raw' $args as registered, not as manipulated by the register_post_type() method call.
   *
   * @return array Associative array where post type strings are keys and registered $args are their values.
   */
  static function get_exo_post_types() {
    if ( ! Exo::is_exo_init() ) {
      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
    }
    return self::$_exo_post_types;
  }

  /**
   * Returns array keyed by Class name with a value of associated Post Type.
   *
   * @returns array
   */
  static function get_classes_post_type() {
    if ( ! Exo::is_exo_init() ) {
      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
    }
    return self::$_classes_post_type;
  }

  /**
   * Returns array keyed by Post Type Class name with an array value of one or more Class names for that Post Type.
   *
   * @note Does not include "non-Exo Post Types"; i.e. only includes post types registered via Exo methods.
   *
   * @returns array
   */
  static function get_post_types_classes() {
    if ( ! Exo::is_exo_init() ) {
      $message = _( "The method %s::%s() cannot be called until after the 'exo_init' hook has started.", 'exo' );
      Exo::trigger_warning( $message, get_called_class(), __FUNCTION__ );
    }
    return self::$_post_types_classes;
  }

}
