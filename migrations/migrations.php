<?php
/*
 * We are saving this for potential later use. It is not per se part of Exo.
 */

/**
 * Class Exo_Migrations
 */
class Exo_Migrations {

  private static function _name_of_options_key() {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;
    $wpdb->query( "UPDATE {$wpdb->posts} SET post_name=post_name WHERE 1=0;" );

  }

  /*--- GENERIC STUFF BELOW ---*/

  static function on_load() {
    foreach( get_class_methods( __CLASS__ ) as $method ) {
      if ( ! preg_match( '#^on_load|migrate$#', $method ) ) {
        self::migrate( $method );
      }
    }
  }

  /**
   * @param string $migrate_class
   */
  static function migrate( $migrate_class ) {
    $migrate_key = ltrim( $migrate_class, '_' );
    if ( ! get_option( $migrate_key ) ) {
      call_user_func( array( __CLASS__, $migrate_class ) );
      update_option( $migrate_key, 'This record can be deleted once the associated migration code in migrations.php is removed.' );
    }
  }

}
Exo_Migrations::on_load();
