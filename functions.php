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
function wpb_get_category($atts = 0){

	$theid = isset( $atts['theid'] ) ? $atts['theid'] : 0;

	if ($theid != 0) {
		return gp_get_the_category_list($theid);
	} else if ($post_id) {
		global $post;
        $post_id = $post->ID;
		return gp_get_the_category_list($post_id);
	}
}
// register shortcode
add_shortcode('gp_sounds_category', 'wpb_get_category');

/** function get_tags **/
function wpb_get_tags($atts = 0){
	if ( null === $before ) {
		$before = __( '' );
	}

	$sep   = ', ';
	$after = '';
	$theid = isset( $atts['theid'] ) ? $atts['theid'] : 0;

	$the_tags = gp_get_the_tag_list( $before, $sep, $after, $theid );

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
		$tag_link_include = "/en/sounds/?_sft_post_tag=$termslug";
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
			$cat_url_include = "/en/sounds/?_sft_category=";
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
			$cat_url_include = "/en/sounds/?_sft_category=";
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

function gp_sanitize_classes( $classes ) {
	if ( ! is_string( $classes ) ) {
		return '';
	}

	$classes = esc_attr( trim( $classes ) );
	$classes = preg_replace( '/\s+/', ' ', $classes );
	$classes = array_map( 'sanitize_html_class', explode( ' ', $classes ) );
	$classes = array_unique( $classes );

	return implode( ' ', $classes );
}

function gp_get_post_classes( $post = null, $args = '' ) {
	$classes = '';

	if ( isset( $post->rpbt_post_class ) && is_string( $post->rpbt_post_class ) ) {
		$classes = $post->rpbt_post_class;
	}

	$is_args    = is_array( $args ) && isset( $args['post_class'] );
	$post_class = $is_args ? $args['post_class'] : $args;

	if ( is_string( $post_class ) && $post_class ) {
		$classes .= ' ' . $post_class;
	}

	$classes = gp_sanitize_classes( $classes );
	$classes = explode( ' ', $classes );

	// Backwards compatibility for filter
	$index = 0;

	/**
	 * Filter CSS classes used in related posts display templates.
	 *
	 * @since 2.4.0
	 *
	 * @param array        $classes Array with post classes.
	 * @param object       $post    Current related post object.
	 * @param array|string $args    Widget or shortcode arguments or string with post classes.
	 * @param int          $index   Deprecated. Default 0
	 */
	$classes = apply_filters( 'related_posts_by_taxonomy_post_class', $classes, $post, $args, $index );

	return gp_sanitize_classes( implode( ' ', $classes ) );
}

function gp_validate_booleans( $args, $defaults ) {

	// The include_self argument can be a boolean or string 'regular_order'.
	if ( isset( $args['include_self'] ) && ( 'regular_order' === $args['include_self'] ) ) {
		// Do not check this value as a boolean
		$defaults['include_self'] = 'regular_order';
	}

	// If deprecated argument is not null treat it like a boolean (back compat).
	if ( isset( $args['related'] ) && ! is_null( $args['related'] ) ) {
		$defaults['related'] = true;
	}

	$booleans = array_filter( (array) $defaults, 'is_bool' );

	foreach ( array_keys( $booleans ) as $key ) {
		if ( isset( $args[ $key ] ) ) {
			$args[ $key ] = gp_validate_boolean( $args[ $key ] );
		}
	}
	return $args;
}

function gp_validate_boolean( $value ) {
	return (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}

function gp_get_permalink( $post = null, $args = '' ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$permalink = esc_url( apply_filters( 'the_permalink', get_permalink( $post ), $post ) );

	/**
	 * Filter the permalink used for related posts.
	 *
	 * @since 2.5.1
	 *
	 * @param string Permalink.
	 */
	return apply_filters( 'related_posts_by_taxonomy_the_permalink', $permalink, $post, $args );
}

function gp_get_post_date( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$time_string = '<time class="rpbt-post-date" datetime="%1$s">%2$s</time>';

	$time_string = sprintf(
		$time_string,
		get_the_date( DATE_W3C, $post ),
		get_the_date( '', $post )
	);

	return $time_string;
}

function gp_get_post_link( $post = null, $args = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$defaults = array(
		'show_date'  => false,
		'title_attr' => false,
		'type'       => '',
	);

	// Backwards compatibility
	$args = is_bool( $args ) ? array( 'title_attr' => $args ) : $args;

	$args = wp_parse_args( $args, $defaults );
	$args = gp_validate_booleans( $args, $defaults );

	$title      = get_the_title( $post );
	$link       = '';
	$title_attr = '';

	if ( ! $title ) {
		$title = get_the_ID();
	}

	if ( $args['title_attr'] && $title ) {
		$title_attr = ' title="' . esc_attr( $title ) . '"';
	}

	$permalink  = gp_get_permalink( $post, $args );
	if ( $permalink && $title ) {
		$link = '<a href="' . $permalink . '"' . $title_attr . '>' . $title . '</a>';
		$link .= $args['show_date'] ? ' ' . gp_get_post_date( $post ) : '';
	}

	$link_attr = compact( 'title', 'permalink' );

	/**
	 * Filter related post link HTML.
	 *
	 * @since 2.4.0
	 * @param string $link Related post link HTML.
	 * @param Object $post Post object.
	 * @param array  $attr Link attributes.
	 */
	return apply_filters( 'related_posts_by_taxonomy_post_link', $link, $post, $link_attr, $args );
}

function gp_construct_card($class = '', $title = '', $cats = '', $tags = '', $author = '', $date = '', $content = ''){
	return <<<HTML
			<div class="each_related_post">
			<div $class>
				<h2 class="audio-loop-title">$title</h2>
				<div class="audio-loop-meta">
					$cats
					<p id="meta-dot" style="color: #707070; padding: 0 20px;">•</p><p class="audio-tags">
					$tags
					</p>
				</div>
				<div class="author-date"> $author  $date</div>
				<p><br />$content</p>
			</div>
		</div>
HTML;
}

// function that runs when shortcode is called
function gp_related_posts() {

// Things that you want to do.

	global $post;

	$post_id = $post->ID;

	$addedPosts[] = $post_id;

 	$data = '<br><br><h2 class="related_podcasts_title">Related Podcasts</h2>';

	//ADD USER DEFINED RELATED POSTS

	$custom_post_ids = get_field( "related_podcasts_manually_added" );

	foreach ( $custom_post_ids as $custom_post_id )
	{
		$newPost = get_post($custom_post_id);

		if (!in_array($newPost->ID, $addedPosts)) {

			$addedPosts[] = $newPost->ID;

			$author = get_the_author_meta('display_name', $newPost->post_author);

			$date = ' - ' . date( 'F j, Y', strtotime( $newPost->post_date ) );

			$cats = do_shortcode("[gp_sounds_category theid='$custom_post_id']");
			$tags = do_shortcode("[gp_sounds_tags theid='$custom_post_id']");
			$content = apply_filters( 'the_content', $newPost->post_content );
			$title = gp_get_post_link($newPost, args);
			$class = gp_get_post_classes($newPost, args);

			$data .= gp_construct_card($class, $title, $cats, $tags, $author, $date, $content);
		}
	}

	//ADD RELATED POSTS BY TAGS & CATEGORIES

	$post_categories = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );

	$tag_ids = array();
	foreach( get_the_tags($post_id) as $tag ) {
		$tag_ids[] = $tag->term_id;
	}

	$args = array(
		'post_type' => 'audio_post',
		'post_status' => 'publish',
		'posts_per_page' => 10,
		'orderby' => 'title',
		'order' => 'ASC',
		'tax_query' => array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $post_categories,
			),
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
				'operator' => 'IN',
			),
		)
	);

	$featured_query = new WP_Query( $args );

	if( $featured_query->have_posts() ):

		while( $featured_query->have_posts() ) : $featured_query->the_post();

			if (!in_array($post->ID, $addedPosts)) {

				$addedPosts[] = $post->ID;

				$author = get_the_author_meta('display_name');

				$date = '';
				if (get_the_date() != '') {
					$date =  ' - ' . get_the_date();
				}

					$cats = do_shortcode('[gp_sounds_category]');
					$tags = do_shortcode('[gp_sounds_tags]');
					$content = apply_filters( 'the_content', $post->post_content );
					$title = gp_get_post_link($post, args);
					$class = gp_get_post_classes($post, args);

				$data .= gp_construct_card($class, $title, $cats, $tags, $author, $date, $content);
				}

		endwhile;

	endif; wp_reset_query();

    // Output needs to be return
	return $data;

}

// register shortcode
add_shortcode('gp_related_posts', 'gp_related_posts');
