<?php
/**
 * These classes are required to be loaded any time Exo is used.
 */
if ( ! class_exists( 'Exo_Base' ) ) {
  /**
   * Base classes
   */
  require( __DIR__ . '/../base/class-base.php');
  require( __DIR__ . '/../base/class-controller-base.php');
  require( __DIR__ . '/../base/class-instance-base.php');
  require( __DIR__ . '/../base/class-library-base.php');

  /**
   * Core class(es)
   */
  require( __DIR__ . '/../core/class-implementation.php');
}
