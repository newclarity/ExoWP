<?php
/**
 * Class Exo_Helpers_Base
 *
 * Base class for classes designed to be 'helper' classes for any child of an Exo_Static_Base class.  Helper classes
 * are essentially mixin classes containing all static methods that allow a developer or a website, webapp, plugin or
 * theme child class of an Exo_Static_Base to invoke the helper's methods as if they were the methods of the main class.
 */
abstract class Exo_Helpers_Base extends Exo_Base {
  static $main_class;

  /**
   * Delegate unknown method calls up to Main class.
   *
   * @param string $method_name
   * @param array $args
   *
   * @return mixed
   */
  static function __callStatic( $method_name, $args ) {
    return call_user_func_array( array( self::$main_class, $method_name ), $args );
  }

}
