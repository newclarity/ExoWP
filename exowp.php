<?php

define( 'EXO_VERSION', '0.1.7' );

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
 */
class Exo extends Exo_Library_Base {

  static function on_load() {
    /**
     * First we register Exo to use Exo_Implementation.
     */
    self::register_implementation( __DIR__, array( 'short_prefix' => 'exo_' ) );

    /**
     * Register any autoload dirs or helpers here.
     */
    self::enable_mvc();

    /**
     * Finally we run initialization that does all fixups.
     */
    self::initialize();
  }
}
