<?php

/**
 * Class _Exo_Array_Helpers
 *
 * Helpers that help with Array-specific functionality.
 *
 */
class _Exo_Array_Helpers extends Exo_Helpers_Base {

  /**
   * Collects object propoerty, array element, from an array or objects or arrays, respectively.
   *
   * @param array $array
   * @param string $field
   * @return array
   */
  static function array_collect( $array, $field ) {
    return array_map( function( $element ) use( &$field ){
      $value = null;
      switch ( gettype( $element ) ) {
        case 'object':
          $value = $element->$field;
          break;
        case 'array':
          $value = $element[$field];
          break;
      }
      return $value;
    }, $array );
  }

  /**
   * Collects object propoerty, array element, from an array or objects or arrays, respectively.
   *
   * Same as array_collect() but
   *
   * @param array $array
   * @param string $field
   * @return array
   */
  static function array_collect_unique( $array, $field ) {
    $unique = array();
    $array = array_map( function( $element ) use ( &$field, &$unique ) {
      $value = null;
      switch ( gettype( $element ) ) {
        case 'object':
          $value = $element->$field;
          break;
        case 'array':
          $value = $element[$field];
          break;
      }
      if ( ! isset( $unique[$value] ) ) {
        $unique[$value] = true;
      } else {
        $value = null;
      }
      return $value;
    }, $array );
    return array_filter( $array );
  }


}
