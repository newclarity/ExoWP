<?php

/**
 * Class Exo_Simple_Post_Collection
 */
class Exo_Simple_Post_Collection extends Exo_Post_Collection_Base {
  const MODEL = 'Exo_Simple_Post';

  /**
   * @param array $args
   *
   * @return string
   */
  function get_model_class( $args ) {
    $args = wp_parse_args( $args, array(
      'default' => self::MODEL,
    ));
    return parent::get_model_class( $args );
  }

}




