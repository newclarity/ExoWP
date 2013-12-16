<?php

/**
 * Class _Exo_Meta_Helpers
 *
 * Helpers that help with Post meta, User meta, etc.
 *
 */
class _Exo_Meta_Helpers extends Exo_Helpers_Base {

  /**
   * Applies a prefix to post/user/etc meta field names.
   *
   * This is a global function because it should come from the implementation (app/website/plugin/theme.)
   *
   * @param string $meta_name
   * @return string
   *
   * @todo Implement this somehow
   */
  static function apply_meta_prefix( $meta_name ) {

    if ( $short_prefix = Exo::short_prefix() ) {
      $prefix = "_{$short_prefix}";
    } else {
      $prefix = '_';
    }

    if ( isset( $meta_name[0] ) && '_' != $meta_name[0] )
      $meta_name = "{$prefix}{$meta_name}";

    return $meta_name;
  }

}
