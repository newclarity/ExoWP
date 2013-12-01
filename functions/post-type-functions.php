<?php
/*
 * This was written in order to submit to trac and to here: http://wordpress.stackexchange.com/questions/3820/deregister-custom-post-types/3821#3821
 * Note to others: If you are wanting to unregister a post type that a theme or plugin registers this answer doesn't undo everything that the `register_post_type()` function does.
 */

function unregister_post_type( $post_type ) {
  /**
   * @var array $wp_post_types
   */
  global $wp_post_types;

  /**
   * @var array $_wp_post_type_features
   */
  global $_wp_post_type_features;

  $unregistered = false;
  if ( isset( $wp_post_types[$post_type] ) ) {
    unregister_taxonomies_for_object_type( $post_type );
    remove_post_type_query_var( $post_type );
    remove_post_type_rewrite_rules( $post_type );
    remove_post_type_rewrite_permastructs( $post_type );
    remove_post_type_rewrite_tag( $post_type );
    unset( $_wp_post_type_features[$post_type] );
    unset( $wp_post_types[$post_type] );
    $unregistered = true;
  }
  return $unregistered;
}
/**
 * Removes query var added for a post type by register_post_type().
 *
 * register_post_type() adds an array value of "%{$post_type}%" to $wp_rewrite->rewritecode
 * and matching index entries in $wp_rewrite->rewritereplace as well as
 * $wp_rewrite->queryreplace. This function finds and removes them.
 *
 * @param string $post_type
 *
 * @return bool If true => Found and removed; if false, not found.
 */
function remove_post_type_query_var( $post_type ) {
  $removed = false;
  if ( $post_type_object = get_post_type_object( $post_type ) ) {
    /**
     * @var wp $wp
     */
    global $wp;
    $query_var = $post_type_object->query_var;
    if ( $query_var && in_array( $query_var, $wp->public_query_vars ) ) {
      $wp->public_query_vars = array_diff( $wp->public_query_vars, array( $query_var ) );
      $removed = true;
    }
  }
  return $removed;
}

/**
 * Removes rewrite tag added for a post type by register_post_type().
 *
 * register_post_type() adds an array value of "%{$post_type}%" to $wp_rewrite->rewritecode
 * and matching index entries in $wp_rewrite->rewritereplace as well as
 * $wp_rewrite->queryreplace. This function finds and removes them.
 *
 * @param string $post_type
 *
 * @return bool If true => Found and removed; if false, not found.
 */
function remove_post_type_rewrite_tag( $post_type ) {
  /**
   * @var WP_Rewrite $wp_rewrite
   */
  global $wp_rewrite;

  $removed = false;
  if ( $rewrite_index = array_search( "%{$post_type}%", $wp_rewrite->rewritecode ) ) {
    unset( $wp_rewrite->rewritecode[$rewrite_index] );
    unset( $wp_rewrite->rewritereplace[$rewrite_index] );
    unset( $wp_rewrite->queryreplace[$rewrite_index] );
    $removed = true;
  }
  return $removed;
}

/**
 * Removes the association between all assocated taxonomies for the specified post type.
 *
 * This function used the term 'object_type' because it calls the WordPress core function
 * unregister_taxonomy_for_object_type() that uses the same term instead of 'post_type.'
 * @param string $post_type
 *
 * @return bool
 */
function unregister_taxonomies_for_object_type( $post_type ) {
  $unregistered = false;
  if ( $post_type_object = get_post_type_object( $post_type ) ) {
    foreach( $post_type_object->taxonomies as $taxonomy ) {
      unregister_taxonomy_for_object_type( $taxonomy, $post_type );
    }
    $unregistered = true;
  }
  return $unregistered;
}

if ( ! function_exists( 'unregister_taxonomy_for_object_type' ) ) {
  /**
   * Added in WordPress 3.7, this is a copy of that function for < 3.7.
   *
   * @param string $taxonomy
   * @param string $object_type
   *
   * @return bool
   */
  function unregister_taxonomy_for_object_type( $taxonomy, $object_type ) {
    global $wp_taxonomies;

    if ( ! isset( $wp_taxonomies[ $taxonomy ] ) )
      return false;

    if ( ! get_post_type_object( $object_type ) )
      return false;

    $key = array_search( $object_type, $wp_taxonomies[ $taxonomy ]->object_type, true );
    if ( false === $key )
      return false;

    unset( $wp_taxonomies[ $taxonomy ]->object_type[ $key ] );
    return true;
  }
}

/**
 * Removes rewrite rules added for a post type by register_post_type().
 *
 * register_post_type() may add rules in both $wp_rewrite->extra_rules and in
 * $wp_rewrite->extra_rules_top and if so they will contain "post_type={$post_type}"
 * somewhere in their $redirect. This function finds those entries and removes them.
 *
 * @param string $post_type
 *
 * @return bool If true => Found and removed; if false, not found.
 */
function remove_post_type_rewrite_rules( $post_type ) {
  /**
   * @var WP_Rewrite $wp_rewrite
   */
  global $wp_rewrite;

  $removed = false;
  foreach( $wp_rewrite->extra_rules as $rule => $redirect ) {
    if ( false !== strpos( $redirect, "post_type={$post_type}" ) ) {
      unset( $wp_rewrite->extra_rules[$rule] );
      $removed = true;
    }
  }
  foreach( $wp_rewrite->extra_rules_top as $rule => $redirect ) {
    if ( false !== strpos( $redirect, "post_type={$post_type}" ) ) {
      unset( $wp_rewrite->extra_rules_top[$rule] );
      $removed = true;
    }
  }
  return $removed;
}

/**
 * Removes permastructs added for a post type by register_post_type().
 *
 * register_post_type() may add rules in both $wp_rewrite->extra_permastructs where the array key
 * is the post type and if so this function will remove that array element.
 *
 * @param string $post_type
 *
 * @return bool If true => Found and removed; if false, not found.
 */
function remove_post_type_rewrite_permastructs( $post_type ) {
  /**
   * @var WP_Rewrite $wp_rewrite
   */
  global $wp_rewrite;

  $removed = false;
  if ( isset( $wp_rewrite->extra_permastructs[$post_type] ) ) {
    unset( $wp_rewrite->extra_permastructs[$post_type] );
  }
  return $removed;
}
