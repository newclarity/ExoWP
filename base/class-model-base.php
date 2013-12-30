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
  function item() {
    return $this->_item;
  }

  /**
   * Returns the default View class name for this Model.
   *
   * Defaults to the Model's class name plus '_View' but can be modified via 'exo_model_view_class' hook.
   *
   * @return string
   */
  function get_view_class() {
    $view_class = Exo::get_class_declaration( 'DEFAULT_VIEW', $model_class = get_class( $this ) );
    if ( ! class_exists( $view_class, false ) ) {
      $view_class = "{$model_class}_View";
      /**
       * @todo Add code to get Post/Taxonomy/etc. specific Default View.
       */
      if ( ! class_exists( $view_class, false ) ) {
        $view_class = "Exo_Simple_View";
      }
    }
    $view_class = apply_filters( 'exo_model_view_class', $view_class, $model_class, $this );
    if ( ! class_exists( $view_class, false ) ) {
      $message = __( 'Default View class %s for Collection class %s not defined.', 'exo' );
      Exo::trigger_warning( $message, $view_class, $model_class );
    }
    return $view_class;
  }


}
