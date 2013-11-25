<?php

/**
 * Class Exo_Instance_Base
 */
abstract class Exo_Instance_Base extends Exo_Base {

  /**
   * @var array Classnames for the mixed-in classes.
   */
  private static $_mixins = array();

  /**
   * @var array Current instance hashes and their hooked actions and filters.
   */
  private static $_instance_hooks = array();

  /**
   * @var array Classnames for the mixed-in classes.
   */
  private $_mixin_instances = array();

/**
 *
 */
  function __construct() {
    parent::__construct();
    self::_instantiate_mixins( $this );
  }

  /**
   * Instantiates the mixin class instances for this class.
   */
  private function _instantiate_mixins() {
    /**
     * @todo Handle mixins defined in parent classes too.
     */
    foreach( self::$_mixins[get_class( $this )] as $mixin_name => $class_name ) {
      $this->_mixin_instances[$mixin_name] = $instance = new $class_name( $this );
      if ( property_exists( $class_name, $mixin_name ) ) {
        /*
         * If the developer defined an instance variable for convenience, assign the instance to it.
         */
        $this->$mixin_name = $instance;
      }
    }
  }

  /**
   * Mixin a class to the called class.
   */

  /**
   * @param string $mixin_name Property name or Class name
   *
   * @return Exo_Mixin_Base
   */
  function get_mixin( $mixin_name ) {
    $mixin = false;
    if ( isset( $this->_mixin_instances[$mixin_name] ) ) {
      $mixin = $this->_mixin_instances[get_class( $this )][$mixin_name];
    } else {
      $mixin_class = get_class( $this );
      $mixin_name = array_search( $mixin_class, $this->_mixin_instances[$mixin_class], true );
      if ( false !== $mixin_name ) {
        $mixin = $this->_mixin_instances[get_class( $this )][$mixin_name];
      }
    }
    /**
     * @todo traverse up the parents until one is found or no more parents.
     */
    return $mixin;
  }

  /**
   * Mixin a class to the called class.
   */
  static function mixin( $class_name, $mixin_name ) {
    /*
     * @todo Test to make the the $class_name is an instance of Exo_Mixin_Base
     */
    /*
     * Collect the owner and mixin class names and their property name.
     */
    self::$_mixins[$owner_class = get_called_class()][$mixin_name] = $class_name;
    /*
     * Register them globally as well.
     */
    Exo::register_mixin( $owner_class, $class_name, $mixin_name );
  }

  /**
   * @param string $filter
   * @param int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  function add_instance_filter( $filter, $callable_or_priority, $priority = 10 ) {
    if ( is_callable( $callable_or_priority ) ) {
      $callable = $callable_or_priority;
    } else if ( is_numeric( $callable_or_priority ) ) {
      $callable = array( $this, $filter );
      $priority = $callable_or_priority;
    }
    $object_hash = spl_object_hash( $this );
    self::$_instance_hooks[$filter][$object_hash] = true;
    add_filter( $filter, array( $this, '_monitor_hooks' ), $priority, 99 );
    return add_filter( "{$object_hash}->{$filter}()", $callable, $priority, 99 );
  }

  /**
   * Monitor hooks for any instance hooks and call them if they have been added.
   *
   * @param null $value
   *
   * @return mixed|null
   */
  function _monitor_hooks( $value = null ) {
    $filter = current_filter();
    $object_hash = spl_object_hash( $this );
    if ( isset( self::$_instance_hooks[$filter][$object_hash] ) ) {
      $args = func_get_args();
      $args[0] = "{$object_hash}->{$filter}()";
      $value = call_user_func_array( 'apply_filters', $args );
    }
    return $value;
  }

  /**
   * @param string $action
   * @param int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  function add_instance_action( $action, $callable_or_priority, $priority = 10 ) {
    return $this->add_instance_filter( $action, $callable_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  function remove_instance_filter( $filter, $callable_or_priority, $priority = 10 ) {
    if ( is_callable( $callable_or_priority ) ) {
      $callable = $callable_or_priority;
    } else if ( is_numeric( $callable_or_priority ) ) {
      $callable = array( $this, $filter );
      $priority = $callable_or_priority;
    }
    $object_hash = spl_object_hash( $this );
    unset( self::$_instance_hooks[$filter][$object_hash] );
    return remove_filter( "{$object_hash}->{$filter}()", $callable, $priority, 99 );
  }

  /**
   * @param string $action
   * @param int|callable $callable_or_priority
   * @param int $priority
   *
   * @return bool|void
   */
  function remove_instance_action( $action, $callable_or_priority, $priority = 10 ) {
    return $this->remove_instance_filter( $action, $callable_or_priority, $priority );
  }

  /**
   * @param string $filter
   * @param mixed $arg1
   * @param mixed $arg2
   * @param mixed $arg3
   * @param mixed $arg4
   * @param mixed $arg5
   *
   * @return mixed
   */
  function apply_instance_filters( $filter, $arg1, $arg2, $arg3, $arg4, $arg5 ) {
    $args = func_get_args();
    $args[0] = spl_object_hash( $this ) . "->{$filter}()";
    return call_user_func_array( 'apply_filters', $args );
  }

  /**
   * @param string $action
   * @param mixed $arg1
   * @param mixed $arg2
   * @param mixed $arg3
   * @param mixed $arg4
   * @param mixed $arg5
   *
   * @return mixed
   */
  function do_instance_action( $action, $arg1, $arg2, $arg3, $arg4, $arg5 ) {
    $args = func_get_args();
    $args[0] = spl_object_hash( $this ) . "->{$action}()";
    call_user_func_array( 'do_action', $args );
  }

}
