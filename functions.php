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
	if ( null === $before ) {
		$before = __( '' );
	}
	
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

// Install required plugins

$requiredPlugins = array(
	"advanced-custom-fields",
	"clear-cache-for-timber",
	"custom-post-type-ui",
	"related-posts-by-taxonomy",
	"search-filter-pro",
	"svg-support",
	"wp-show-posts"
);

foreach ($requiredPlugins as $requiredPlugin) {
	$source         = WP_CONTENT_DIR . '/themes/planet4-child-theme-africa/required-plugins/' . $requiredPlugin;
	$target         = WP_PLUGIN_DIR . '/' . $requiredPlugin;
	if (file_exists($source)) {
	    rename($source, $target);
	}
}