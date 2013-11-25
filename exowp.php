<?php

require(__DIR__ . '/classes/base/class-base.php');
require(__DIR__ . '/classes/base/class-static-base.php');
require(__DIR__ . '/classes/base/class-helpers-base.php');
require(__DIR__ . '/classes/base/class-delegating-base.php');
require(__DIR__ . '/classes/base/class-instance-base.php');
require(__DIR__ . '/classes/base/class-singleton-base.php');
require(__DIR__ . '/classes/base/class-model-base.php');
require(__DIR__ . '/classes/base/class-collection-base.php');
require(__DIR__ . '/classes/base/class-view-base.php');
require(__DIR__ . '/classes/base/class-mixin-base.php');

/**
 * Class Exo
 *
 * @foundin Exo_Singleton_Base
 * @method static void register_helper( string $class_name, string $method_name = false, string $alt_method_name = false )
 *
 * @foundin Exo_Mixin_Helpers
 * @method static void register_mixin( string $owner_class, string $mixin_class, string $mixin_name  )
 *
 */
class Exo extends Exo_Singleton_Base {}

/**
 * Class _Exo
 */
class _Exo_Skeleton extends Exo_Delegating_Base {

  /**
   * Initialize _Exo() instance
   */
  static function on_load() {
    Exo::$skeleton = new _Exo_Skeleton();
  }

}
_Exo_Skeleton::on_load();
