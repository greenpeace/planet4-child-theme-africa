<?php
/**
 * Widget and shortcode template: links template.
 *
 * This template is used by the plugin: Related Posts by Taxonomy.
 *
 * plugin:        https://wordpress.org/plugins/related-posts-by-taxonomy
 * Documentation: https://keesiemeijer.wordpress.com/related-posts-by-taxonomy/
 *
 * Only edit this file after you've copied it to your (child) theme's related-post-plugin folder.
 * See: https://keesiemeijer.wordpress.com/related-posts-by-taxonomy/templates/
 *
 * @package Related Posts by Taxonomy
 * @since 0.1
 *
 * The following variables are available:
 *
 * @var array $related_posts Array with related post objects or related post IDs.
 *                           Empty array if no related posts are found.
 * @var array $rpbt_args     Widget or shortcode arguments.
 */

?>

<?php
/**
 * Note: global $post; is used before this template by the widget and the shortcode.
 */
?>

<?php if ( $related_posts ) : ?>

	
		<?php foreach ( $related_posts as $post ) : ?>
			<?php
				// Set up postdata so we can use WordPress functions like the_content().
				setup_postdata( $post );

				// The plugin functions below can be found in the /includes/template-tags.php file.
			?>

			<div class="each_related_post">
				<div<?php km_rpbt_post_class( $post, $rpbt_args ); ?>>
			<h2  class="audio-loop-title"><?php km_rpbt_post_link( $post, $rpbt_args ); ?></h2>
			<div class="audio-loop-meta"><?php the_category(); ?><p style="color: #006DFD; padding: 0 20px;">•</p><p class="audio-tags"><?php the_tags(); ?></p></div>
			<div class="author-date"><?php
            the_author();
            if (get_the_date() != '') {
                echo ' - ' . get_the_date();
            }
            ?></div>
			<p><br /><?php the_content(); ?></p>
		</div>
	</div>
			
		<?php endforeach; ?>


<?php endif ?>

<?php
/**
 * Note: wp_reset_postdata(); is used after this template by the widget and the shortcode.
 */
?>
