<?php

/**
 * Class Exo_Post_Collection
 */
class Exo_Post_Collection extends Exo_Post_Collection_Base {
  const MODEL = 'Exo_Post';

  function get_model_class( $args = array() ) {
    $args = wp_parse_args( $args, array(
      'default' => self::MODEL,
    ));
    return parent::get_model_class( $args );
  }

}




