<?php

/**
 * Class Exo_Php_Helpers
 */
class _Exo_Php_Helpers extends Exo_Helpers_Base {

  /**
   * @var array List of public methods for the called class.
   */
  private static $_class_methods;

  /**
   * Returns an array of method names for a class, object or the called class respecting a set of filters.
   *
   * Caches the result into a static array so it only has to run once per class & filter set.
   * Does NOT return mixed-in methods.
   *
   * @param bool|string|object $class
   * @param array $filters
   *
   * @return array
   */
  static function get_class_methods( $class = false, $filters = array() ) {
    $filters = wp_parse_args( $filters, array(
      /*
       * A class' 'internal' methods; if true return only non-internal methods
       * ('internal'is an Exo concept meaning 'public, but you still shouldn't call it.')
       */
      'internal' => false,
      /*
       * A class' public methods; if true return only the public methods.
       */
      'public' => false,
      /*
       * A class' 'own' methods; if true return only the ones non-inherited.
       */
      'own' => false,
      /*
       * A class' 'instance' methods; if true return only non-static methods
       */
      'instance' => false,
      /*
       * A class' static methods; if true return only non-static methods
       */
      'static' => false,
    ));
    if ( is_string( $class ) ) {
      $class_name = $class;
    } else if ( is_object( $class ) ) {
      $class_name = get_class( $class );
    } else {
      $class_name = get_called_class();
    }
    if ( 0 == count( $filters ) ) {
      if ( ! isset( self::$_class_methods[$class_name]['all'] ) ) {
        self::$_class_methods[$class_name]['all'] = get_class_methods( $class_name );
      }
    } else {
      ksort( $filters ); // Sort so serialize will always return same for same set of filters, no matter the order
      if ( ! isset( self::$_class_methods[$class_name][$hash = serialize( $filters )] ) ) {
        $class_reflector = new ReflectionClass( $class_name );
        $methods = array();
        foreach( get_class_methods( $class_name ) as $method_name ) {
          /**
           * Methods prefixed with underscores and the on_load() method
           * are not "public" as far aa Exo is concerned.
           */
          if ( ! $filters['internal'] && '_' == $method_name[0] || 'on_load' == $method_name ) {
            continue;
          }
          if ( $filters['public'] && ! $class_reflector->getMethod( $method_name )->isPublic() ) {
            continue;
          }
          $method_reflector = new ReflectionMethod( $class_name, $method_name );
          if ( $filters['own'] && $class_name != $method_reflector->getDeclaringClass()->getName() ) {
            continue;
          }
          if ( $is_static = $method_reflector->isStatic() && $filters['instance'] ) {
            continue;
          }
          if ( $filters['static'] && ! $is_static ) {
            continue;
          }
          $methods[] = $method_name;
        }
        self::$_class_methods[$class_name][$hash] = $methods;
      }
    }
    return self::$_class_methods[$class_name][$hash];
  }

}
