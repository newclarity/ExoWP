<?php

require(__DIR__ . '/base/class-base.php');
require(__DIR__ . '/base/class-instance-base.php');
require(__DIR__ . '/base/class-static-base.php');
require(__DIR__ . '/base/class-helpers-base.php');
require(__DIR__ . '/base/class-webapp-base.php');
require(__DIR__ . '/base/class-model-base.php');
require(__DIR__ . '/base/class-collection-base.php');
require(__DIR__ . '/base/class-view-base.php');
require(__DIR__ . '/base/class-mixin-base.php');
require(__DIR__ . '/base/class-post-base.php');

/**
 * Class Exo
 *
 * @mixin _Exo_Helpers
 * @mixin _Exo_Php_Helpers
 *
 */
class Exo extends Exo_Webapp_Base {}

require(__DIR__ . '/helpers/class-php-helpers.php');
require(__DIR__ . '/helpers/class-php-helpers.on-load.php');
require(__DIR__ . '/helpers/class-helpers.php');
require(__DIR__ . '/helpers/class-helpers.on-load.php');
