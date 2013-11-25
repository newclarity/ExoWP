<?php

/**
 * Class Exo_Collection_Base
 *
 * Base class for Collections
 *
 * A Collection is an object containing an internal array of models.
 *
 */
abstract class Exo_Collection_Base extends Exo_Instance_Base {
  /**
   * @var Exo_Model_Base
   */
  var $model;

  function __construct() {

  }
}
