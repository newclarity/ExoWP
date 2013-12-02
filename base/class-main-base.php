<?php

/**
 * Class Exo_Main_Base
 *
 * Base classes for Main Classes for an implementation.
 *
 * This class SHOULD NOT have any static or instance variables besides $_implementations.
 * All values needed to be managed should instead be places in the instannce's class.
 *
 * @mixin _Exo_Helpers
 *
 * @mixin Exo_Implementation
 * @method static string uri( string $path = false )
 * @method static string dir( string $path = false )
 * @method static string theme_dir( string $path = false )
 * @method static string theme_uri( string $path = false )
 * @method static string get_helper_callable( string $method_name )
 * @method static string get_runmode()
 * @method static bool is_dev_mode()
 * @method static bool is_test_mode()
 * @method static bool is_stage_mode()
 * @method static bool is_live_mode()
 * @method static bool has_method( string $method_name )
 * @method static bool has_helper_callable( string $method_name )
 * @method static void register_helper( string $class_name, string $method_name = false, string $alt_method_name = false )
 * @method static void require_exo_autoloader()
 * @method static void require_exo_mvc_classes()
 * @method static void require_exo_base_classes()
 * @method static void register_exo_mvc_autoload_dirs()
 * @method static void register_exo_autoload_dirs()
 * @method static void maybe_adjust_http_scheme( string $url )
 * @method static void fixup_registered_helpers()
 * @method static void enable_mvc()
 * @method static void set_runmode(string $runmode)
 *
 * @mixin Exo_Autoloader
 * @method static void register_autoload_classes( array $classes )
 * @method static void register_autoload_class( string $class_name, string $dir )
 * @method static void register_autoload_dir( string $dir, string $prefix = false )
 * @method static void register_autoload_subdir( string $dir, string $prefix = false )
 * @method static array get_autoload_dirs()
 * @method static array get_onload_files_content()
 * @method static array get_onload_filepaths()
 * @method static bool is_wp_loaded()
 *
 */
abstract class Exo_Main_Base extends Exo_Base {

  /**
   * @var array
   */
  private static $_implementations = array();

  /**
   * Registers a class to start being extended by helpers.
   *
   * @param string $class_name
   * @param string|Exo_Implementation $dir_or_implementation
   * @param array $args
   */
  static function register_implementation( $class_name, $dir_or_implementation, $args = array() ) {
    if ( ! isset( self::$_implementations[$class_name] ) ) {
      $args = wp_parse_args( $args, array(
        'make_global' => false,
      ));
      $implementation = is_string( $dir_or_implementation ) ? new Exo_Implementation( $dir_or_implementation ) : $dir_or_implementation;
      $implementation->class_prefix = "{$class_name}_";
      self::$_implementations[$class_name] = $implementation;
      if ( $args['make_global'] ) {
        $GLOBALS[$class_name] = $instance;
      }
    }
  }

  /**
   * Allows a class that extends from Exo_Main_Base to register an instance of Exo_Instance_Core
   *
   * @param string $class_prefix
   */
  static function register_class_prefix( $class_prefix ) {
    if ( isset( self::$_implementations[$called_class = get_called_class()] ) ) {
      self::$_implementations[$called_class]->class_prefix = $class_prefix;
    }
  }

  /**
   * Initialize the Main and Implementation classes.
   *
   * To be called after all the other code is called.
   * Load the 'on-load' files.
   *
   */
  static function initialize() {
    if ( isset( self::$_implementations[$called_class = get_called_class()] ) ) {
      /**
       * @var Exo_Implementation $implementation
       */
      $implementation = self::$_implementations[$called_class];

      $onload_php = $implementation->dir( '/on-load.php' );
      if ( $implementation->is_dev_mode() ) {
        $autoloader = $implementation->autoloader;
        foreach( $autoloader->get_onload_filepaths() as $filepath ) {
          require( $filepath );
        }
        /**
         * Now generate the new /on-load.php, if content has been updated.
         */
        $old_content = is_file( $onload_php ) ? file_get_contents( $onload_php ) : false;
        $new_content = $autoloader->get_onload_files_content();
        if ( $new_content != $old_content ) {
          file_put_contents( $onload_php, $new_content );
        }
      } else {
        require( $onload_php );
      }

      $implementation->fixup_registered_helpers();
    }
  }

  /**
   * Delegate calls to other classes.
   *
   * This allows us to document a single "API" for the sunrise class yet
   * structure the code more conveniently in multiple class files.
   *
   * @param string $method_name
   * @param array $args
   *
   * @return mixed
   *
   * @throws Exception
   */
  static function __callStatic( $method_name, $args ) {
    $value = null;
    if ( ! isset( self::$_implementations[$called_class = get_called_class()] ) ) {
      $error = true;
    } else {
      $error = false;
      /**
       * @var Exo_Implementation $implementation
       */
      $implementation = self::$_implementations[$called_class];
      if ( method_exists( $implementation, $method_name ) ) {
        /**
         * If Exo_Implementation has this method
         */
        $value = call_user_func_array( array( $implementation, $method_name ), $args );
      } else if ( $callable = $implementation->get_helper_callable( $method_name ) ) {
        /**
         * If Exo_Implementation has this method
         */
        $value = call_user_func_array( $callable, $args );
      } else if ( method_exists( $autoloader = $implementation->autoloader, $method_name ) ) {
        /**
         * If Exo_Autoloader has this method
         */
        $value = call_user_func_array( array( $autoloader, $method_name ), $args );
      } else {
        $error = true;
      }
    }
    if ( $error ) {
      $message = __( 'ERROR: Neither %s nor any of it\'s registered helper classes have the method %s().', 'exo' );
      _Exo_Helpers::trigger_warning( sprintf( $message, $called_class, $method_name ) );
    }
    return $value;
  }

}

