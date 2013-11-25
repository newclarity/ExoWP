<?php

/**
 * Class Exo_Mixin_Base
 */
abstract class Exo_Mixin_Base extends Exo_Instance_Base {

  /**
   * @var Exo_Instance_Base
   */
  var $owner;

  /**
   * @param $owner
   */
  function __construct( $owner ) {
    $this->owner = $owner;
    parent::__construct();
  }

}
