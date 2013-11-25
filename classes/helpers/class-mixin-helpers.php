<?php

/**
 * Class Exo_Mixin_Helpers
 */
class _Exo_Mixin_Helpers extends Exo_Helpers_Base {
  private static $_mixins = array();

  /**
   * @param string $owner_class
   * @param string $mixin_class
   * @param string $mixin_name
   */
  function register_mixin( $owner_class, $mixin_class, $mixin_name ) {
    self::$_mixins[$owner_class][$mixin_name] = $mixin_class;
  }

}
