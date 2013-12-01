<?php


/**
 * Initialize Exo using the 'plugins_loaded' or 'after_setup_theme' hooks, at priority 9
 */
if ( defined( 'EXO_LOAD_IN_THEME' ) && EXO_LOAD_IN_THEME ) {
  add_action( 'after_setup_theme', array( 'Exo', 'on_load' ), 9 );
} else {
  add_action( 'plugins_loaded', array( 'Exo', 'on_load' ), 9 );
}

/**
 * All Exo implementations should load exo-core.php first.
 */
require( __DIR__ . '/core/exo-core.php');

/**
 * Class Exo
 *
 * @method static void register_helper( string $class_name, string $method_name = false, string $alt_method_name = false )
 */
class Exo extends Exo_Library_Base {

  /**
   * @var Exo_Implementation_Base
   *
   * All Exo implementations need to register an instance.
   */
  static $instance;

  static function on_load() {
    /**
     * Create the instance class
     */
    self::$instance = new Exo_Implementation();

    /**
     * Then they should register their instance
     */
    self::register_instance( __CLASS__, self::$instance );

    self::initialize();
  }
}

/**
 * Class Exo_Implementation
 */
class Exo_Implementation extends Exo_Implementation_Base {
  function __construct() {
    parent::__construct();
    $this->enable_mvc();
  }

}


