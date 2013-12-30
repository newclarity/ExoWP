<?php

/**
 * Class _Exo_Mvc_Helpers
 *
 * Helpers that help with MVC-specific functionality.
 *
 */
class _Exo_Mvc_Helpers extends Exo_Helpers_Base {

  /**
   * @var array
   */
  private static $_collections_model_classes = array();

  /**
   * @var array
   */
  private static $_models_collections_classes = array();

  /**
   * Scan the list of $classes from get_declared_classes() and capture collection's model classes.
   *
   * @note All classes must be loaded to call this.
   */
  static function _record_collection_model_classes() {
    $data = array(
      'collections_models_classes' => self::$_collections_model_classes,
      'models_collections_classes' => self::$_models_collections_classes,
    );
    Exo::walk_declared_classes( function( $class_name ) use ( &$data ) {
      if ( is_subclass_of( $class_name, 'Exo_Collection_Base' ) ) {

        if ( $model_class = _Exo_Helpers::get_class_declaration( 'MODEL', $class_name ) ) {
          $data['collections_models_classes'][$class_name] = $model_class;
          if ( ! isset( $data['models_collections_classes'][$model_class] ) ) {
            $data['models_collections_classes'][$model_class] = array( $class_name );
          } else {
            $data['models_collections_classes'][$model_class][] = $class_name;
          }
        }
      }
    });
    self::$_collections_model_classes = $data['collections_models_classes'];
    self::$_models_collections_classes = $data['models_collections_classes'];
  }

  /**
   * @param string $model_class
   *
   * @return string
   */
  static function get_model_collection_classes( $model_class ) {
    $collection_classes = isset( self::$_models_collections_classes[$model_class] )
      ? self::$_models_collections_classes[$model_class]
      : array();
    if ( 0 == count( $collection_classes ) ) {
      if ( ! ( $collection_class = class_exists( "{$model_class}_Collection", false ) ) ) {
        if ( is_subclass_of( $model_class, 'Exo_Post_Base' ) ) {
          $collection_class = 'Exo_Simple_Post_Collection';
        } else {
          $collection_class = 'Exo_Simple_Collection';
        }
      }
      $collection_classes = array( $collection_class );
    }
    /**
     * Filter class names for Model Collection classes given a $model class name.
     * @param array $collection_classes Array of collection class names.
     * @param string $model_class Name of model class.
     */
    return apply_filters( 'exo_model_collection_classes', $collection_classes, $model_class );
  }

  /**
   * @param string $collection_class
   *
   * @return string
   */
  static function get_collection_model_class( $collection_class ) {
    return self::$_collections_model_classes[$collection_class];
  }

  /**
   * @return array
   */
  static function get_collections_model_classes() {
    return self::$_collections_model_classes;
  }

  /**
   * @return array
   */
  static function get_models_collections_classes() {
    return self::$_models_collections_classes;
  }

  /**
   * @param mixed $item
   *
   * @return string
   */
  static function get_collection_item_hash( $item ) {
    return Exo_Collection_Base::get_item_hash( $item );
  }

}
