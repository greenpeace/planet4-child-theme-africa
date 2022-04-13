<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
	$css_creation = filectime(get_stylesheet_directory() . '/style.css');

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation );
}

/** function get_category by post_id **/
function wpb_get_category(){
	
	global $post;
    $post_id = $post->ID;
	
	if($post_id){
		return gp_get_the_category_list($post_id);
	}
} 
// register shortcode
add_shortcode('gp_sounds_category', 'wpb_get_category'); 

/** function get_tags **/
function wpb_get_tags(){
	$before = __( '' );
	
	$sep = ', ';
	$after = '';
 
	$the_tags = gp_get_the_tag_list( $before, $sep, $after );
 
	if ( ! is_wp_error( $the_tags ) ) {
		return $the_tags;
	}
}

function gp_get_the_tag_list( $before = '', $sep = '', $after = '', $post_id = 0 ) {
	$tag_list = gp_get_the_term_list( $post_id, 'post_tag', $before, $sep, $after );

	return apply_filters( 'the_tags', $tag_list, $before, $sep, $after, $post_id );
}

function gp_get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '' ) {
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( is_wp_error( $terms ) ) {
		return $terms;
	}

	if ( empty( $terms ) ) {
		return false;
	}

	$links = array();
	
	$base_url = get_site_url();
	
	foreach ( $terms as $term ) {
		$termslug = $term->slug;
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) ) {
			return $link;
		}
		$tag_link_include = "/?_sft_post_tag=$termslug";
		$tag_url = $base_url . $tag_link_include;
		$links[] = '<a href="' . esc_url( $tag_url ) . '" rel="tag">' . $term->name . '</a>';
	}

	$term_links = apply_filters( "term_links-{$taxonomy}", $links );

	return $before . implode( $sep, $term_links ) . $after;
}
// register shortcode
add_shortcode('gp_sounds_tags', 'wpb_get_tags'); 


function gp_get_the_category_list($post_id) {
	
	$separator = '';
	$parents = '';
	
	global $wp_rewrite;

	if ( ! is_object_in_taxonomy( get_post_type( $post_id ), 'category' ) ) {
		/** This filter is documented in wp-includes/category-template.php */
		return apply_filters( 'the_category', '', $separator, $parents );
	}

	/**
	 * Filters the categories before building the category list.
	 *
	 * @since 4.4.0
	 *
	 * @param WP_Term[] $categories An array of the post's categories.
	 * @param int|bool  $post_id    ID of the post we're retrieving categories for.
	 *                              When `false`, we assume the current post in the loop.
	 */
	$categories = apply_filters( 'the_category_list', get_the_category( $post_id ), $post_id );

	if ( empty( $categories ) ) {
		/** This filter is documented in wp-includes/category-template.php */
		return apply_filters( 'the_category', __( 'Uncategorized' ), $separator, $parents );
	}

	$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

	$thelist = '';
	$base_url = get_site_url();
	if ( '' === $separator ) {
		$thelist .= '<ul class="post-categories">';
		foreach ( $categories as $category ) {
			$cat_url_include = "/?_sft_category=";
			$cat_url = $base_url . $cat_url_include . $category->slug;
			
			$thelist .= "\n\t<li>";
			switch ( strtolower( $parents ) ) {
				case 'multiple':
					if ( $category->parent ) {
						$thelist .= get_category_parents( $category->parent, true, $separator );
					}
					$thelist .= '<a href="' . esc_url( $cat_url ) . ' " ' . $rel . '>' . $category->name . '</a></li>';
					break;
				case 'single':
					$thelist .= '<a href="' . esc_url( $cat_url ) . '"  ' . $rel . '>';
					if ( $category->parent ) {
						$thelist .= get_category_parents( $category->parent, false, $separator );
					}
					$thelist .= $category->name . '</a></li>';
					break;
				case '':
				default:
					$thelist .= '<a href="' . esc_url( $cat_url ) . '" ' . $rel . '>' . $category->name . '</a></li>';
			}
		}
		$thelist .= '</ul>';
	} else {
		$i = 0;
		foreach ( $categories as $category ) {
			$cat_url_include = "/?_sft_category=";
			$cat_url = $base_url . $cat_url_include . $category->slug;
			
			if ( 0 < $i ) {
				$thelist .= $separator;
			}
			switch ( strtolower( $parents ) ) {
				case 'multiple':
					if ( $category->parent ) {
						$thelist .= get_category_parents( $category->parent, true, $separator );
					}
					$thelist .= '<a href="' . esc_url( $cat_url ) . '" ' . $rel . '>' . $category->name . '</a>';
					break;
				case 'single':
					$thelist .= '<a href="' . esc_url( $cat_url ) . '" ' . $rel . '>';
					if ( $category->parent ) {
						$thelist .= get_category_parents( $category->parent, false, $separator );
					}
					$thelist .= "$category->name</a>";
					break;
				case '':
				default:
					$thelist .= '<a href="' . esc_url( $cat_url ) . '" ' . $rel . '>' . $category->name . '</a>';
			}
			++$i;
		}
	}

	/**
	 * Filters the category or list of categories.
	 *
	 * @since 1.2.0
	 *
	 * @param string $thelist   List of categories for the current post.
	 * @param string $separator Separator used between the categories.
	 * @param string $parents   How to display the category parents. Accepts 'multiple',
	 *                          'single', or empty.
	 */
	return apply_filters( 'the_category', $thelist, $separator, $parents );
}

// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

  global $wp_version;
  if ( $wp_version !== '4.7.1' ) {
     return $data;
  }

  $filetype = wp_check_filetype( $filename, $mimes );

  return [
      'ext'             => $filetype['ext'],
      'type'            => $filetype['type'],
      'proper_filename' => $data['proper_filename']
  ];

}, 10, 4 );

function cc_mime_types( $mimes ){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action( 'admin_head', 'fix_svg' );

function cptui_register_my_cpts() {

	/**
	 * Post Type: Audio Posts.
	 */

	$labels = [
		"name" => __( "Audio Posts", "planet4-child-theme-africa" ),
		"singular_name" => __( "Audio Post", "planet4-child-theme-africa" ),
		"menu_name" => __( "My Audio Posts", "planet4-child-theme-africa" ),
		"all_items" => __( "All Audio Posts", "planet4-child-theme-africa" ),
		"add_new" => __( "Add new", "planet4-child-theme-africa" ),
		"add_new_item" => __( "Add new Audio Post", "planet4-child-theme-africa" ),
		"edit_item" => __( "Edit Audio Post", "planet4-child-theme-africa" ),
		"new_item" => __( "New Audio Post", "planet4-child-theme-africa" ),
		"view_item" => __( "View Audio Post", "planet4-child-theme-africa" ),
		"view_items" => __( "View Audio Posts", "planet4-child-theme-africa" ),
		"search_items" => __( "Search Audio Posts", "planet4-child-theme-africa" ),
		"not_found" => __( "No Audio Posts found", "planet4-child-theme-africa" ),
		"not_found_in_trash" => __( "No Audio Posts found in trash", "planet4-child-theme-africa" ),
		"parent" => __( "Parent Audio Post:", "planet4-child-theme-africa" ),
		"featured_image" => __( "Featured image for this Audio Post", "planet4-child-theme-africa" ),
		"set_featured_image" => __( "Set featured image for this Audio Post", "planet4-child-theme-africa" ),
		"remove_featured_image" => __( "Remove featured image for this Audio Post", "planet4-child-theme-africa" ),
		"use_featured_image" => __( "Use as featured image for this Audio Post", "planet4-child-theme-africa" ),
		"archives" => __( "Audio Post archives", "planet4-child-theme-africa" ),
		"insert_into_item" => __( "Insert into Audio Post", "planet4-child-theme-africa" ),
		"uploaded_to_this_item" => __( "Upload to this Audio Post", "planet4-child-theme-africa" ),
		"filter_items_list" => __( "Filter Audio Posts list", "planet4-child-theme-africa" ),
		"items_list_navigation" => __( "Audio Posts list navigation", "planet4-child-theme-africa" ),
		"items_list" => __( "Audio Posts list", "planet4-child-theme-africa" ),
		"attributes" => __( "Audio Posts attributes", "planet4-child-theme-africa" ),
		"name_admin_bar" => __( "Audio Post", "planet4-child-theme-africa" ),
		"item_published" => __( "Audio Post published", "planet4-child-theme-africa" ),
		"item_published_privately" => __( "Audio Post published privately.", "planet4-child-theme-africa" ),
		"item_reverted_to_draft" => __( "Audio Post reverted to draft.", "planet4-child-theme-africa" ),
		"item_scheduled" => __( "Audio Post scheduled", "planet4-child-theme-africa" ),
		"item_updated" => __( "Audio Post updated.", "planet4-child-theme-africa" ),
		"parent_item_colon" => __( "Parent Audio Post:", "planet4-child-theme-africa" ),
	];

	$args = [
		"label" => __( "Audio Posts", "planet4-child-theme-africa" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "audio_post", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail" ],
		"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "audio_post", $args );
}

add_action( 'init', 'cptui_register_my_cpts' );

function cptui_register_my_cpts_audio_post() {

	/**
	 * Post Type: Audio Posts.
	 */

	$labels = [
		"name" => __( "Audio Posts", "planet4-child-theme-africa" ),
		"singular_name" => __( "Audio Post", "planet4-child-theme-africa" ),
		"menu_name" => __( "My Audio Posts", "planet4-child-theme-africa" ),
		"all_items" => __( "All Audio Posts", "planet4-child-theme-africa" ),
		"add_new" => __( "Add new", "planet4-child-theme-africa" ),
		"add_new_item" => __( "Add new Audio Post", "planet4-child-theme-africa" ),
		"edit_item" => __( "Edit Audio Post", "planet4-child-theme-africa" ),
		"new_item" => __( "New Audio Post", "planet4-child-theme-africa" ),
		"view_item" => __( "View Audio Post", "planet4-child-theme-africa" ),
		"view_items" => __( "View Audio Posts", "planet4-child-theme-africa" ),
		"search_items" => __( "Search Audio Posts", "planet4-child-theme-africa" ),
		"not_found" => __( "No Audio Posts found", "planet4-child-theme-africa" ),
		"not_found_in_trash" => __( "No Audio Posts found in trash", "planet4-child-theme-africa" ),
		"parent" => __( "Parent Audio Post:", "planet4-child-theme-africa" ),
		"featured_image" => __( "Featured image for this Audio Post", "planet4-child-theme-africa" ),
		"set_featured_image" => __( "Set featured image for this Audio Post", "planet4-child-theme-africa" ),
		"remove_featured_image" => __( "Remove featured image for this Audio Post", "planet4-child-theme-africa" ),
		"use_featured_image" => __( "Use as featured image for this Audio Post", "planet4-child-theme-africa" ),
		"archives" => __( "Audio Post archives", "planet4-child-theme-africa" ),
		"insert_into_item" => __( "Insert into Audio Post", "planet4-child-theme-africa" ),
		"uploaded_to_this_item" => __( "Upload to this Audio Post", "planet4-child-theme-africa" ),
		"filter_items_list" => __( "Filter Audio Posts list", "planet4-child-theme-africa" ),
		"items_list_navigation" => __( "Audio Posts list navigation", "planet4-child-theme-africa" ),
		"items_list" => __( "Audio Posts list", "planet4-child-theme-africa" ),
		"attributes" => __( "Audio Posts attributes", "planet4-child-theme-africa" ),
		"name_admin_bar" => __( "Audio Post", "planet4-child-theme-africa" ),
		"item_published" => __( "Audio Post published", "planet4-child-theme-africa" ),
		"item_published_privately" => __( "Audio Post published privately.", "planet4-child-theme-africa" ),
		"item_reverted_to_draft" => __( "Audio Post reverted to draft.", "planet4-child-theme-africa" ),
		"item_scheduled" => __( "Audio Post scheduled", "planet4-child-theme-africa" ),
		"item_updated" => __( "Audio Post updated.", "planet4-child-theme-africa" ),
		"parent_item_colon" => __( "Parent Audio Post:", "planet4-child-theme-africa" ),
	];

	$args = [
		"label" => __( "Audio Posts", "planet4-child-theme-africa" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "audio_post", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail" ],
		"taxonomies" => [ "category", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "audio_post", $args );
}

add_action( 'init', 'cptui_register_my_cpts_audio_post' );
