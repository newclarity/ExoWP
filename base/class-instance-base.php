<?php

/**
 * Class Exo_Instance_Base
 */
abstract class Exo_Instance_Base extends Exo_Base {
  const METHOD_ECHO = true;
  const METHOD_RETURN = false;

  /**
   * @var Exo_Instance_Base
   */
  var $owner;

  /**
   * @var array Classnames for the mixed-in classes.
   */
  private static $_mixins = array();

  /**
   * @var array Classnames for the mixed-in classes.
   */
  private $_mixin_instances = array();

  /**
   * @var array Same as $this->_mixin_instances except keyed by classname.
   */
  private $_mixin_instances_by_classname = array();

  /**
   * @var array Current instance hashes and their hooked actions and filters.
   */
  private static $_instance_hooks = array();

  static function on_load() {
    /**
     * @todo Change these to use self::add_static_action() once we test that and get it working.
     */
    add_action( 'after_setup_theme', array( __CLASS__, '_after_setup_theme_9' ), 9 );
  }

  /**
   * Fixup mixins that have been added to classes.
   */
  static function _after_setup_theme_9() {
    /**
     * @todo Add conditionals to load mixins when not in dev runmode
     *       and to generate fixed PHP/JSON code when in dev runmode.
     */
    self::_exo_fixup_mixins();
  }
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
    if ( isset( self::$_mixins[$owner_class = get_class( $this )] ) ) {
      foreach( self::$_mixins[$owner_class]->mixins as $mixin_class => $mixin ) {
        $this->_mixin_instances[$mixin->var_name] = $instance = new $mixin_class( $this );
        $this->_mixin_instances_by_classname[$mixin_class] = $instance;
        $instance->owner = $this;
        /*
         * If the developer defined an instance variable for convenience, assign the instance to it.
         */
        if ( property_exists( $mixin_class, $mixin->var_name ) ) {
          $this->{$mixin->var_name} = $instance;
        }
      }
    }
  }

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
     *       Need use-case first to be able to test it.
     */
    return $mixin;
  }

  /**
   * Mixin a class to the called class.
   */
  /**
   * @param string $mixin_class Class to mixin to the calling class.
   * @param bool|string $mixin_var Variable/array element name for the mixin instance.
   */
  static function mixin( $mixin_class, $mixin_var = false ) {
    /*
     * @todo Test to make the the $class_name is an instance of Exo_Mixin_Base
     * @todo derive $mixin_name from $mixin_class, but do in fixup.
     */
    /*
     * Collect the owner and mixin class names and their property name.
     */
    self::$_mixins[get_called_class()][$mixin_class] = $mixin_var;
  }

  /**
   *
   */
  static function _exo_fixup_mixins() {
    /**
     * @todo I feel this this could be done with a more efficient algorithm,
     *       but it's beyond me to improve at this point. -mikeschinkel
     */
    self::_fixup_mixins( self::_semi_normalize_mixins() );
    self::_assign_callable_templates();
  }

  /**
   * Fixup mixins after all have been registered.
   *
   * Fixup the mixins such that all classes that participate in mixins (owners and mixins) get registered in an array
   * that for any class you could traverse the array to find it's mixins, recursively.
   *
   * @param array $registered_mixins
   *
   * @return mixed
   */
  static function _fixup_mixins( $registered_mixins ) {
    foreach( $registered_mixins as $class_name => $mixin ) {
      if ( is_array( $mixin ) ) {
        $var_name = false;
        $mixins = self::_fixup_mixins( $mixin );
      } else {
        /*
         * Getting ALIAS here was done inline vs. calling Exo::get_class_alias() for performance reasons.
         */
        $var_name = defined( $constant_ref = "{$class_name}::ALIAS" ) ? constant( $constant_ref ) : false;
        $mixins = array();
      }
      if ( isset( self::$_mixins[$class_name] ) && is_object( self::$_mixins[$class_name] ) ) {
        if ( count( $mixins ) ) {
          self::$_mixins[$class_name] = array_merge( $mixins, self::$_mixins[$class_name] );
        }
        if ( $var_name && ! self::$_mixins[$class_name]->var_name ) {
          self::$_mixins[$class_name]->var_name = $var_name;
        }
      } else {
        self::$_mixins[$class_name] = (object)array(
          'class_name' => $class_name,
          'var_name' => $var_name,
          'mixins' => $mixins,
          'parent_class' => get_parent_class( $class_name ),
          'method_names' => _Exo_Helpers::get_class_methods( $class_name, array(
            'own' => true,
            'public' => true,
            'instance' => true,
          )),
          'owners' => array(),
          'callable_templates' => array(
            'mixins' => array(),
            'owners' => array(),
          ),
        );
      }
      $registered_mixins[$class_name] = self::$_mixins[$class_name];
    }
    return $registered_mixins;
  }

  /**
   *  Make sure every class that participates in mixins has an entry in the self::$_mixins array.
   *  Not fully normalized as some classes as array keys will have  values of an array of subclasses
   *  while other array key classes will just have the variable name if passed, or false if not.
   *
   *  This is a first step. 2nd step is self::_fixup_mixins().
   */
  private static function _semi_normalize_mixins() {
    $normalized_mixins = array();
    foreach( self::$_mixins as $owner_class => $mixins ) {
      $normalized_mixins[$owner_class] = $mixins;
      if ( is_array( $mixins ) ) {
        foreach( $mixins as $mixin_class => $mixin_var ) {
          if ( ! isset( self::$_mixins[$mixin_class] ) ) {
            $normalized_mixins[$mixin_class] = $mixin_var;
          }
        }
        $child_class = $owner_class;
        while ( $parent_class = get_parent_class( $child_class ) ) {
          if ( ! isset( self::$_mixins[$parent_class] ) && ! isset( $normalized_mixins[$parent_class] ) ) {
            $normalized_mixins[$parent_class] = false;
          }
          $child_class = $parent_class;
        }
      }
    }
    return $normalized_mixins;
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

  /**
   * @param string $method_name
   * @param array $args
   * @return mixed
   */
  function __call( $method_name, $args ) {
    $value = null;
    if ( isset( self::$_mixins[$class_name = get_class( $this )]->callable_templates['mixins'][$method_name] ) ) {
      $callable = self::$_mixins[$class_name]->callable_templates['mixins'][$method_name];
      $callable[0] = $this->_mixin_instances_by_classname[$callable[0]];
      if ( self::METHOD_ECHO == array_pop( $callable ) ) {
        echo call_user_func_array( $callable, $args );
      } else {
        $value = call_user_func_array( $callable, $args );
      }
    } else if ( isset( self::$_mixins[$class_name]->callable_templates['owners'][$method_name] ) ) {
      $callable = self::$_mixins[$class_name]->callable_templates['owners'][$method_name];
      $callable[0] = $this->_get_owner_by_class( $callable[0] );
      if ( self::METHOD_ECHO == array_pop( $callable ) ) {
        echo call_user_func_array( $callable, $args );
      } else {
        $value = call_user_func_array( $callable, $args );
      }
    } else {
      $message = __( 'ERROR: The class %s does not have a callable instance method %s().', 'exo' );
      _Exo_Helpers::trigger_warning( $message, $class_name, $method_name );
    }
    return $value;
  }

  /**
   * Traverse up the owners and return the owner that matches the passed class, or the first owner if no
   *
   * @param bool|string $class_name
   *
   * @return bool|Exo_Instance_Base
   */
  private function _get_owner_by_class( $class_name = false ) {
    $owner = false;
    if ( ! $class_name ) {
      $owner = $this->owner;
    } else if ( ! is_null( $this->owner ) ) {
      if ( $class_name == get_class( $this->owner ) ) {
        $owner = $this->owner;
      } else {
        $owner = $this->owner->_get_owner_by_class( $class_name );
      }
    }
    return $owner;
  }

  /**
   * Loop through self::$_mixins for each class and assign the list of callable method templates.
   */
  private static function _assign_callable_templates() {
    foreach( self::$_mixins as $class_name => $class_info ) {
      //self::_fixup_mixin_callable_templates( $class_name, $class_info );
      self::_fixup_callable_templates( 'mixins', $class_name, $class_info );
    }
    foreach( self::$_mixins as $class_name => $class_info ) {
      // self::_fixup_owner_callable_templates( $class_name, $class_info );
      self::_fixup_callable_templates( 'owners', $class_name, $class_info );
    }
  }

  /**
   * Return the callable templates for a class that are from it's mixins.
   *
   * @param string $template_type
   * @param string $class_name
   * @param stdClass $class_info
   *
   * @return array
   */
  private static function _fixup_callable_templates( $template_type, $class_name, $class_info ) {
    if ( ! self::$_mixins[$class_name]->callable_templates[$template_type] ) {
      $delegated_templates = array();
      if ( 0 < count( $class_info->$template_type ) ) {
        foreach( $class_info->$template_type as $delegator_class => $delegator_info ) {
          $delegated_templates[] = call_user_func( array( __CLASS__, __FUNCTION__ ), $template_type, $delegator_class, $delegator_info );
          if ( ! isset( self::$_mixins[$delegator_class]->owners[$class_name] ) && $delegator_class != $class_name ) {
            self::$_mixins[$delegator_class]->owners[$class_name] = $class_info;
          }
        }
        switch ( $count = count( $delegated_templates ) ) {
          case '0':
            $temp = array();
            break;
          case '1':
            $temp = $delegated_templates[0];
            break;
          default:
            $temp = $delegated_templates[0];
            for( $i = 1; $i < $count; $i++ ) {
              /**
               * Earlier declared methods come 1st hence $callable_templates is 2nd param to array_merge().
               */
              $temp += $delegated_templates[$i];
            }
        }
        $delegated_templates = $temp;
      }
      $own_templates = array();
      foreach( self::$_mixins[$class_name]->method_names as $method_name ) {
        if ( preg_match( '#^get_(.+?)$#', $method_name, $match ) ) {
          $own_templates["the_{$match[1]}"] = array( $class_name, $method_name, self::METHOD_ECHO );
        }
        $own_templates[$method_name] = array( $class_name, $method_name, self::METHOD_RETURN );
      }
      if ( 0 == count( $delegated_templates ) ) {
        self::$_mixins[$class_name]->callable_templates[$template_type] = $own_templates;
      } else {
        /**
         * Own methods come 1st hence $own_templates is 1st arg for "+".
         */
        self::$_mixins[$class_name]->callable_templates[$template_type] = $own_templates + $delegated_templates;
      }
    }
    return self::$_mixins[$class_name]->callable_templates[$template_type];
  }

}
Exo_Instance_Base::on_load();
