<?php

/**
 * Class Exo_Model_Base
 */
abstract class Exo_Model_Base extends Exo_Instance_Base {

  /**
   * @var object
   */
  private $_item;

  /**
   * @param bool|object $object
   */
  function __construct( $object = false ) {
    parent::__construct();
    $this->_item = $object;
  }

  /**
   * @return object
   */
  function to_item() {
    return $this->_item;
  }

}
