<?php

/**
 * Class Exo_Model_Base
 */
abstract class Exo_Model_Base extends Exo_Instance_Base {

  /**
   * @var WP_Post
   */
  private $_post;

  /**
   * @param WP_Post $post
   */
  function __construct( $post ) {
    parent::__construct();
    $this->_post = $post;
  }

}
