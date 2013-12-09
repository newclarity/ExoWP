<?php

/**
 * Class Exo_Model_Base
 */
abstract class Exo_Model_Base extends Exo_Instance_Base {

  /**
   * @var object
   */
  private $_object;

  /**
   * @param bool|object $object
   */
  function __construct( $object = false ) {
    parent::__construct();
    $this->_object = $object;
  }

  /**
   * @return object
   */
  function to_object() {
    return $this->_object;
  }

}
