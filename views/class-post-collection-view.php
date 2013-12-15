<?php

/**
 * Class Exo_Post_Collection_View
 *
 * @mixin Exo_Post_Collection
 */
class Exo_Post_Collection_View extends Exo_Collection_View_Base {
  const COLLECTION = 'Exo_Post_Collection';
  const VIEW_TYPE = 'post';


  /**
   * @param bool|Exo_Post_Collection $collection
   */
  function __construct( $collection = false ) {
    parent::__construct();
    $this->collection = $collection instanceof Exo_Post_Collection ? $collection : new Exo_Post_Collection();
  }

}




