<?php

/**
 * Class Exo_Post_View_Base
 *
 * @mixin Exo_Post
 */
class Exo_Post_View_Base extends Exo_View_Base {
  const MODEL = 'Exo_Post_Base';

  /**
   * Meant to be overridden in child classes.
   *
   * @return array()
   */
  function get_template_part_fallbacks() {
    return array( 'post-fallback.php' );
  }

}
