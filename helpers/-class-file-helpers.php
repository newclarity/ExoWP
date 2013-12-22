<?php

/**
 * Class _Exo_File_Helpers
 *
 * Helpers that help with WP_Filesystem, etc.
 *
 */
class _Exo_File_Helpers extends Exo_Helpers_Base {

  /**
   * @param bool|array $args (optional) Connection args, These are passed directly to the WP_Filesystem_*() classes.
   * @param bool|string $context (optional) Context for get_filesystem_method(), See function declaration for more information.
   * @return WP_Filesystem_Base
   */
  static function get_wp_filesystem( $args = false, $context = false ) {
    if ( ! function_exists( 'WP_Filesystem' ) ) {
      require_once( ABSPATH . '/wp-admin/includes/file.php' );
    }
    WP_Filesystem( $args, $context );
    return $GLOBALS['wp_filesystem'];
  }

  /**
   * Read contents of named file into a string.
   *
   * @uses WP_Filesystem classes
   *
   * @param string $filepath
   *
   * @return bool|string Returns the read data or false on failure.
   */
  static function get_file_contents( $filepath ) {
    return self::get_wp_filesystem()->get_contents( $filepath );
  }

  /**
   * Writes contents of a string to the named file.
   *
   * @uses WP_Filesystem classes
   *
   * @param string $filepath Remote path to the file where to write the data.
 	 * @param string $contents The data to write.
 	 * @param bool|int $mode (optional) The file permissions as octal number, usually 0644.
 	 * @return bool False on failure.
   */
  static function put_file_contents( $filepath, $contents, $mode = false ) {
    return self::get_wp_filesystem()->put_contents( $filepath, $contents, $mode );
  }

}

