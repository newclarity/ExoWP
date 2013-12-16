<?php

/**
 * Class Exo_View_Base
 *
 * Base class for Post Views - i.e. an object containing an internal array of models based on a WordPress post type.
 *
 * @mixin Exo_Model_Base
 *
 */
abstract class Exo_View_Base extends Exo_Instance_Base {

  /**
   * @var Exo_Model_Base
   */
  var $model;

  /**
   * @var Exo_View_Base
   */
  var $parent_view = false;

  /**
   * @var string Will contain the filepath of the view's current template file.
   */
  var $template_part_file;

  /**
   * @var int
   */
  private static $_template_part_counter = 1;

  /**
   * @param bool|Exo_Model_Base $model
   */
  function __construct( $model = false ) {
    $this->model = $model;
  }

  /**
   * Mirrors WordPress' get_header() but with support for skins.
   *
   * @param null $header_name
   */
  function the_header( $header_name = null ) {
    do_action( 'get_header', $header_name );

    $templates = array();
    $header_name = (string) $header_name;
    if ( '' !== $header_name )
      $templates[] = "header-{$header_name}.php";

    $templates[] = 'header.php';

    $this->locate_template( $templates, true );
  }

  /**
   * Mirrors WordPress' get_footer() but with support for skins.
   *
   * @param null $footer_name
   */
  function the_footer( $footer_name = null ) {
    do_action( 'get_footer', $footer_name );

    $templates = array();
    $name = (string) $footer_name;
    if ( '' !== $footer_name )
      $templates[] = "footer-{$footer_name}.php";

    $templates[] = 'footer.php';

    $this->locate_template( $templates, true );
  }

  /**
   * Modification of WordPress' locate_template()
   *
   * @param string|array $template_filepaths Template file(s) to search for, in order.
   * @param bool $load If true the template file will be loaded if it is found.
   * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
   * @return string The template filename if one is located.
   */
  function locate_template( $template_filepaths, $load = false, $require_once = true ) {
    $located = false;
    foreach ( (array)$template_filepaths as $template_filepath ) {
      if ( ! $template_filepath )
        continue;
      if ( is_file( $filepath = TEMPLATEPATH . "/{$template_filepath}" ) ) {
        $located = $filepath;
        break;
      }
    }
    if ( $located && $load )
      load_template( $located, $require_once );
    return $located;
  }

  /**
   * Loads Specialized Theme Templates in the /template-parts/ directory of the Theme.
   *
   * @param array $args
   */
  function the_template_part( $args = array() ) {
    static $stylesheet_dir;
    if ( ! isset( $stylesheet_dir ) )
      $stylesheet_dir = get_stylesheet_directory();
    $args = $this->normalize_template_part_args( $args );

    if ( ! isset( $args['view_type'] ) && $view_type = $this->get_view_type() ) {
      $args['view_type'] = $view_type;
    }
    /**
     * Allow a child class to modify the template part $args, if needed.
     */
    $args = $this->get_template_part_args( $args );
    /**
     * 'view' is a shortcut for 'container_view'
     */
    if ( ! empty( $args['view'] ) ) {
      $args['container_view'] = $args['view'];
    }
    $view = isset( $args['container_view'] ) ? $args['container_view'] : $this;
    $args = apply_filters( 'exo_template_part_args', $args, $view );
    unset( $args['container_view'] );
    $view_type = isset( $args['view_type'] ) ? $args['view_type'] : 'site';
    unset( $args['view_type'] );
    $possibilities = $this->get_template_part_possibilities( $view_type, $args );
    $view->template_part_file = locate_template( $possibilities );
    if ( ! $view->template_part_file ) {
      $message = __( 'No template found for view type "%s"', 'exo' );
      if ( 0 == count( $args ) ) {
        $message = sprintf( "{$message}.", $view->template_part_file );
      } else {
        $message .= __( ' and criteria: %s.', 'exo' );
        $message = sprintf( $message, $view_type, http_build_query( $args ) );
      }
      $message .= __( " Possibile template parts included:\n\n", 'exo' );
      ob_start();
      print_r( $possibilities );
      Exo::trigger_warning( $message . ob_get_clean() );
    } else {
      $template_part_path = preg_replace( "#^{$stylesheet_dir}/(template-parts.*?)$#", '$1', $view->template_part_file );
      if ( ! Exo::is_dev_mode() ) {
        $this->_load_template( $view );
      } else {
        $criteria = $args;
        if ( is_array( $args ) && count( $args ) ) {
          $criteria = str_replace( '&', ', ', http_build_query( $args ) );
        }
        if ( $criteria ) {
          $criteria = ", Criteria: {$criteria}";
        }
        else {
          $criteria= ', Criteria: (empty)';
        }
        $counter = self::$_template_part_counter;
        $comment_prefix = "\nTemplate({$counter}) ";
        $comment_suffix = "Template type: {$view_type}{$criteria}\n";
        echo "\n<!--{$comment_prefix}OPTIONS: {$comment_suffix}";
        foreach( $possibilities as $possibility )
          echo "\n - {$possibility}";
        echo "\n{$comment_prefix}MATCH: {$template_part_path} -->\n\n";
        $this->_load_template( $view );
        echo "\n<!--{$comment_prefix}END: {$comment_suffix} -->\n\n";
        self::$_template_part_counter++;
      }
    }
  }

  /**
   * Set the template $args for this view.
   *
   * This method is designed to be overwritten by one in a child class. No need
   * for the child class to call this one as parent as it will not need to ever
   * be updated; we can put whatever updates we need into the_template_part().
   *
   * @param array $args
   * @return array
   */
  function get_template_part_args( $args = array() ) {
    return $args;
  }

  /**
   * Set the template type, if applicable.
   *
   * The template type is a short name intended to be lowercase and using the symbol characters of [a-z0-9_]
   * and it is used to identify which Exo template directory tree to search for template parts.
   *
   * @example This method returns the value of TEMPLATE (i.e. 'brand') on an instance of Spark_City_Brand:
   *
   *   class PM_Shera_Solution {
   *     const TEMPLATE = 'solution';
   *     ...
   *   }
   *
   * @return string
   */
  function get_view_type() {
    return Exo::get_class_constant( 'VIEW_TYPE', get_class( $this ) );
  }

  /**
   * Calls template with $view object visible to the template.
   *
   * Implemented this way so there's only two variables made visible to the template, i.e. $view and $post
   *
   * @param stdClass|Exo_View_Base $view
   */
  private function _load_template( $view ) {
    if ( property_exists( $view, 'model' ) && method_exists( $view->model, 'get_post' ) ) {
      $post = $view->model->get_post();
    } else if ( isset( $GLOBALS['post'] ) ) {
      $post = $GLOBALS['post'];
    } else {
      $post = new WP_Post( new stdClass() );
    }
    require( $view->template_part_file );
  }

  /**
   * @param string $view_type
   * @param string|array $args Criteria
   * @return array
   * @throws Exception
   */
  function get_template_part_possibilities( $view_type, $args = array() ) {
    $args = $this->normalize_template_part_args( $args );
    ksort( $args );
    $args = array_filter( $args, function( $element ) {
      $keep = true;
      if ( ! is_string( $element ) ) {
        $keep = false;
        if ( ! is_object( $element ) && ! is_array( $element ) ) {
          Exo::trigger_warning( 'Array value is not string nor object nor array: %s', $element );
        }
      }
      return $keep;
    });
    $args = explode( '&', http_build_query( $args ) );
    $permutations = Exo::get_array_permutations( $args );
    usort( $permutations, function( $permutation_a, $permutation_b ) {
      return count( $permutation_b ) - count( $permutation_a );
    });
    $possibilities = array();
    $theme_dir = Exo::theme_dir();

    foreach( $permutations as $index => $permutation ) {
      parse_str( implode( '&', $permutation ), $option );
      $permutations[$index] = array(
        'keys'   => implode( ',', array_keys( $option ) ),
        'values' => implode( ',', array_values( $option ) ),
      );
    }

    $base_dir = "template-parts/{$view_type}";

    /**
     * Add a filepath for each permutation, in the following form assuming
     * 'part' is the template type and 'foo' is the part-type:
     *
     *    /template-parts/site/arg/site[foo].php
     *
     * Also, strip off the subdirectory and try there, i.e.
     *
     *    /template-parts/site/site[foo].php
     *
     */
    foreach( $permutations as $permutation ) {
      if ( ! empty( $permutation['keys'] ) ) {
        $possibilities[] = "{$base_dir}/{$permutation['keys']}/{$view_type}[{$permutation['values']}].php";
        $possibilities[] = "{$base_dir}/{$view_type}[{$permutation['values']}].php";
      }
    }

    /**
     * Next test for root options based on criteria.
     *
     *    /template-parts/foo,contact.php
     */
    foreach( $permutations as $permutation ) {
      if ( ! empty( $permutation['keys'] ) ) {
        $possibilities[] = "template-parts/{$view_type}[{$permutation['values']}].php";
      }
    }

    /**
     * Add these for template_parts that don't care about template type but do care about criteria:
     *
     *    /template-parts/[arg]/[foo].php
     *    /template-parts/[foo].php
     *
     */
    foreach( $permutations as $permutation ) {
      if ( ! empty( $permutation['keys'] ) ) {
        $possibilities[] = "template-parts/[{$permutation['keys']}]/[{$permutation['values']}].php";
        $possibilities[] = "template-parts/[{$permutation['values']}].php";
      }
    }

    /**
     * Finally add in the most generic default, i.e.
     *
     *    /template-parts/site/site.php
     *    /template-parts/site.php
     */
    $possibilities[] = "{$base_dir}/{$view_type}.php";

    /**
     * Convert all underscores to dashes. So this:
     *
     *    /template-parts/parts/part_type/part[foo].php
     *
     * Becomes:
     *
     *    /template-parts/parts/part-type/part[foo].php
     *
     */
    foreach( $possibilities as $index => $possibility )
      $possibilities[$index] = str_replace( '_', '-', $possibility );


    return $possibilities;
  }

  /**
   * Allows args to be passed as a "name=value" string or even just a "value" string.
   *
   * If passed as a "value" string name gets 'type'.
   * Included a pass of wp_oarse_arg() if string.
   *
   * @param string|array $args
   *
   * @return array
   */
  function normalize_template_part_args( $args = array() ) {
    if ( is_string( $args ) ) {
      if ( false === strpos( $args, '=' ) ) {
        /**
         * If no key specified, use 'arg'
         */
        $args = array( 'arg' => $args );
      } else {
        $args = wp_parse_args( $args );
      }
    }
    return $args;
  }

  /**
   * @param string $method_name
   * @param array $args
   * @return mixed
   */
  function __call( $method_name, $args = array() ) {
    $value = null;
    $args[] = $this;
    if ( $this->has_mixin( $method_name ) ) {
      $value = $this->__call( $method_name, $args );
    } else if ( ! is_null( $this->model ) && method_exists( $this->model, $method_name ) ) {
      $value = call_user_func_array( array( $this->model, $method_name ), $args );
    } else if ( ! is_null( $this->model ) && $this->model->has_mixin( $method_name ) ) {
      $value = $this->model->__call( $method_name, $args );
    } else {
      $message = __( 'Neither view class %s nor model class %s has %s() as a direct or mixin method.', 'exo' );
      $model_class = ! empty( $this->model ) ? get_class( $this->model ) : '[n/a]';
      Exo::trigger_warning( $message, get_class( $this ), $model_class, $method_name );
    }
    return $value;
  }

}
