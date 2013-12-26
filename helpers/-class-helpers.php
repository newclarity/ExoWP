<?php

/**
 * Class _Exo_Helpers
 *
 * Helpers that help with Exo-specific functionality.
 *
 */
class _Exo_Helpers extends Exo_Helpers_Base {

  /**
   * @var array List of public methods for the called class.
   */
  private static $_class_methods;

  /**
   * @param string $declared_name
   * @param bool|string|object $class_name
   * @param mixed $default
   *
   * @return bool|mixed
   */
  static function get_class_declaration( $declared_name, $class_name = false, $default = null ) {
    if ( ! $class_name ) {
      $class_name = get_called_class();
    }
    if ( ! ( $value = self::get_class_constant( $declared_name, $class_name ) ) ) {
      $value = self::call_class_method( $declared_name, $class_name );
    }
    if ( is_null( $value ) ) {
      $value = $default;
    }
    return $value;
  }

  /**
   * @param bool|string $class_name
   *
   * @return bool|mixed
   */
  static function get_class_alias( $class_name = false ) {
    return self::get_class_constant( 'ALIAS', $class_name );
  }

  /**
   * @param string $class_name
   *
   * @return string
   */
  static function get_class_filepath( $class_name ) {
    $reflector = new ReflectionClass( $class_name );
    return $reflector->getFilename();
  }

  /**
   * @param string $class_name
   *
   * @return string
   */
  static function get_class_dir( $class_name ) {
    return dirname( self::get_class_filepath( $class_name ) );
  }

  /**
   * Align the HTTP scheme (SSL vs. non SSL) to be consistent with incoming URL.
   *
   * @param $url
   *
   * @return mixed
   */
  static function maybe_adjust_http_scheme( $url ) {
    $scheme = is_ssl() ? 'https' : 'http';
    return preg_replace( '#^https?://#', "{$scheme}://", $url );
  }

  /**
   * @param string $method_name
   * @param string|object $class_name
   * @param mixed $default
   * @param array $args
   *
   * @return bool|mixed
   */
  static function call_class_method( $method_name, $class_name, $default = null, $args = array() ) {
//  	static $instances = array();
    if ( is_object( $class_name ) ) {
      $class_name = get_class( $class_name );
    } else if ( ! is_string( $class_name ) ) {
      $class_name = false;
    }
    if ( method_exists( $class_name, $method_name ) && is_subclass_of( $class_name, 'Exo_Base' ) ) {
    	$reflector = new ReflectionMethod( $class_name, $method_name );
    	if ( $reflector->isStatic() ) {
    		$context = $class_name;
      } else {
        $constructor = new ReflectionMethod( $class_name, '__construct' );
        if ( 0 < $constructor->getNumberOfRequiredParameters() ) {
          $message = __( "%s::%s() method must be defined as 'static' because %s::__contruct() requires parameters.", 'exo' );
          Exo::trigger_warning( $message, $class_name, $method_name, $class_name );
          $context = false;
        } else {
          if ( ! isset( $instances[$class_name] ) ) {
            $instances[$class_name] = new $class_name();
          }
          $context = $instances[$class_name];
        }
      }
      if ( $context ) {
        $result = call_user_func( array( $context, $method_name ), $args );
      }
    } else {
      $result = $default;
    }
    return $result;
  }

  /**
   * @param string $constant_name
   * @param bool|string|object $class_name
   * @param mixed $default
   *
   * @return bool|mixed
   */
  static function get_class_constant( $constant_name, $class_name = false, $default = null ) {
    if ( ! $class_name ) {
      $class_name = get_called_class();
    } else if ( is_object( $class_name ) ) {
      $class_name = get_class( $class_name );
    }
    return defined( $constant_ref = "{$class_name}::{$constant_name}" ) ? constant( $constant_ref ) : $default;
  }

  /**
   * @param string $message
   * @param bool|string $arg1
   * @param bool|string $arg2
   * @param bool|string $arg3
   * @param bool|string $arg4
   * @param bool|string $arg5
   */
  static function trigger_warning( $message, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false, $arg5 = false ) {
    $args = func_get_args();
    $message = array(
      call_user_func_array( 'sprintf', $args ),
      __( "\nCall Stack:", 'exo' ),
    );
    if ( version_compare( PHP_VERSION, '5.3.6', '>=') ) {
      $backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS );
    } else {
      $backtrace = debug_backtrace();
    }
    for( $i = count( $backtrace ) - 1; $i > 0; $i-- ) {
      $call = $backtrace[$i];
      $function = "{$call['function']}() ";
      if ( isset( $call['object'] ) && isset( $call['type'] ) ) {
        $function = get_class( $call['object'] ) . "{$call['type']}{$function}";
      }
      if ( isset( $call['file'] ) && isset( $call['line'] ) ) {
        $message[] = "\n  " . sprintf( __( 'Called %s in %s on line %s', 'exo' ), $function, $call['file'], $call['line'] );
      }
    }
    $message[] = "\n  " . __( 'Called ' . __CLASS__ . '::trigger_error()', 'exo' );
    trigger_error( implode( $message ), E_USER_WARNING );
  }

  /**
   * Returns the implementation type as a lowercase string.
   *
   * Scans up the parent classes until it finds a parent class name of the form "Exo_{$implementation_type}_Base"
   *
   * @param bool|string $class_name
   *
   * @return string
   */
  static function get_implementation_type( $class_name = false ) {
    $parent_class = get_parent_class( $class_name );
    if ( $parent_class && preg_match( '#^Exo_(Library|Plugin|Theme|Application|Website)_Base$#', $parent_class, $match ) ) {
      $implementation_type = strtolower( $match[1] );
    } else {
      $implementation_type = self::get_implementation_type( $parent_class );
    }
    return $implementation_type;
  }

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

  /**
   * Return array of all permutations key=value for an array, independent of key order.
   *
   * Takes an array of named values (associative array) and provides all permutations.
   *
   * This is used to determine theme template subdirectories and filenames.
   *
   * @note
   *
   *    Method is recursive and leading underscore parameters ($_*)
   *    are used by the recursion and not expected to be passed in.
   *
   * @example
   *
   *   // This call
   *   $permutations = Spark_City::get_array_permutations( array(
   *     'a' => '1',
   *     'b' => '2',
   *     'c' => '3',
   *     'd' => '4',
   *   ));
   *
   *   // Sets $permutations to look like this:
   *   array(
   *     array(),
   *     array( 'a'=>'1' ),
   *     array( 'b'=>'2' ),
   *     array( 'a'=>'1', 'b'=>'2' ),
   *     array( 'c'=>'3' ),
   *     array( 'a'=>'1', 'c'=>'3' ),
   *     array( 'b'=>'2', 'c'=>'3' ),
   *     array( 'a'=>'1', 'b'=>'2', 'c'=>'3' ),
   *     array( 'd'=>'4' ),
   *     array( 'a'=>'1', 'd'=>'4' ),
   *     array( 'b'=>'2', 'd'=>'4' ),
   *     array( 'a'=>'1', 'b'=>'2', 'd'=>'4' ),
   *     array( 'c'=>'3', 'd'=>'4' ),
   *     array( 'a'=>'1', 'c'=>'3', 'd'=>'4' ),
   *     array( 'b'=>'2', 'c'=>'3', 'd'=>'4' ),
   *     array( 'a'=>'1', 'b'=>'2', 'c'=>'3', 'd'=>'4' ),
   *   );
   *
   * @note
   *
   *    The following is used to generate the above example.
   *
   *    function dump_a( $a, $lvl = 0 ) {
   *      $tabs = str_repeat( "  ", $lvl );
   *      echo "{$tabs}array(";
   *      $args = array();
   *      foreach( $a as $i => $e ) {
   *        if ( is_array( $e ) ) {
   *          echo "\n *  ";
   *          dump_a( $e, $lvl+1 );
   *        } else {
   *          $args[] = "'{$i}'=>'{$e}'";
   *        }
   *      }
   *      if ( count( $a ) ) {
   *        echo ' ' . implode( ', ', $args ) . ' ';
   *      }
   *      echo $lvl ? '),' : "\n *  );";
   *    }
   *
   *    dump_a( Spark_City::get_array_permutations( array(
   *      'a' => '1',
   *      'b' => '2',
   *      'c' => '3',
   *      'd' => '4',
   *    )));
   *
   * @param array $named_values
   * @param $_permutations $named_values
   * @param array $_visited
   *
   * @return array
   */
  static function get_array_permutations( $named_values, &$_permutations = array(), &$_visited = array() ) {
    /**
     * Scan through array and arrange to remove one element
     * during each pass and the add it to the permutations array.
     */
    $count = count( $named_values );
    $new_permutations = array();
    for( $i = 0; $i < $count; $i++ ) {
      $left = array_slice( $named_values, 0, $count - $i - 1 );
      $right = array_slice( $named_values, $count - $i );
      $new_permutation = array_merge( $left, $right );
      $hash = md5( serialize( $new_permutation ) );
      if ( ! isset( $_visited[$hash] ) ) {
        $new_permutations[$hash] = $new_permutation;
        $_visited[$hash] = true;
      }
    }
    /**
     * Now scan through the new permutations, find their new permutations
     * and continue to merge them into our new permutations array.
     */
    foreach( $new_permutations as $permutation ) {
      self::get_array_permutations( $permutation, $_permutations, $_visited );
    }

    /**
     * Finally add the original array to the end.
     */
    $_permutations[] = $named_values;

    return $_permutations;
  }

}
