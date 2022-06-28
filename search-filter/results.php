<div class="allSearchResults">
<?php
/**
 * Sample Results Template
 *
 * Note: these templates are not full page templates, rather
 * just an encaspulation of the your results loop which should
 * be inserted in to other pages by using a shortcode - think
 * of it as a template part
 *
 * This template is an absolute base example showing you what
 * you can do, for more customisation see the WordPress docs
 * and using template tags -
 *
 * http://codex.wordpress.org/Template_Tags
 *
 */

// If this file is called directly, abort.
if ( ! defined('ABSPATH' ) ) {
	exit;
}

$template_directory = get_template_directory();
$template_url = get_template_directory_uri();

global $wpdb;

$query1 = <<<SQL
select post_title from {$wpdb->prefix}posts
where post_type='audio_post'
and post_status='publish'
SQL;

$audio = $wpdb->get_results($query1, ARRAY_N);

if ( $query->have_posts() )
{
	?>

	<div class="results-number-search"> Found <?php echo $query->found_posts; ?> Results<br />
	Page <?php echo $query->query['paged']; ?> of <?php echo $query->max_num_pages; ?><br /></div>

	<div class="pagination">

		<div class="nav-previous"><?php next_posts_link( 'Older posts', $query->max_num_pages ); ?></div>
		<div class="nav-next"><?php previous_posts_link( 'Newer posts' ); ?></div>
		<?php
			/* example code for using the wp_pagenavi plugin */
			if (function_exists('wp_pagenavi'))
			{
				echo "<br />";
				wp_pagenavi( array( 'query' => $query ) );
			}
		?>
	</div>

	<?php
    $searchSuggestions = [];
    $searchTitles = [];
	while ($query->have_posts())
	{
		$query->the_post();

        // Images
        $whatsAppImg = $template_url . '/assets/src/images/icons/whatsapp.svg';
        $fbImg = $template_url . '/assets/src/images/icons/facebook-f.svg';
        $twitterImg = $template_url . '/assets/src/images/icons/twitter.svg';
        $mailImg = $template_url . '/assets/src/images/icons/envelope.svg';

        $title = get_the_title();
        $searchSuggestions[] = $title;
        $searchTitles[] = html_entity_decode(the_title('', '', false), ENT_NOQUOTES, 'UTF-8');
        $permaLink = get_the_permalink();
        $postID = get_the_ID();
        $waText = 'Listen to this, from Greenpeace Africa!%0D%0D ' . urlencode("$permaLink?utm_medium=share&utm_content=$postID&utm_source=whatsapp");
        $fbText = urlencode("$permaLink?utm_medium=share&utm_content=$postID&utm_source=facebook&quote=Listen to this, from Greenpeace Africa!");
        $twitterText = 'Listen to this from, Greenpeace Africa! ' . 'via @greenpeace&url=' . urlencode("$permaLink?utm_medium=share&utm_content=$postID&utm_source=twitter") ;
        $mailText = 'Listen to this, from Greenpeace Africa! ' . '&body=' . 'Listen to this, from Greenpeace Africa! %0D%0D' . urlencode("$permaLink?utm_medium=share&utm_content=$postID&utm_source=email%0D%0D");

		?>
		<div class="main-page-single-result">
			<h2 class="audio-loop-title"><a href="<?php echo $permaLink; ?>"><?php the_title(); ?></a></h2>
			<div class="audio-loop-meta">
				<?php echo do_shortcode('[gp_sounds_category]'); ?>
				<p id="meta-dot"style="color: #707070; padding: 0 20px;">•</p>
                <p class="audio-tags">
					<?php echo do_shortcode('[gp_sounds_tags]'); ?>
				</p>
			</div>
			<div class="author-date"><?php
            the_author();
            if (get_the_date() != '') {
                echo ' - ' . get_the_date();
            }
            ?></div>
			<p><br /><?php the_content(); ?></p>

            <p><?php
                $html = <<<HTML
<div class="share-buttons share-buttons-loop">
	<!-- Whatsapp -->
	<a href="https://wa.me/?text=$waText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Whatsapp', 'eventLabel': '$permaLink'});"
		 target="_blank" class="share-btn whatsapp">
		<svg viewBox="0 0 32 32" class="icon"><use xlink:href="/africa/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#whatsapp"></use></svg>
		<span class="visually-hidden">Share on Whatsapp</span>
	</a>
	<!-- Facebook -->
	<a href="https://www.facebook.com/sharer/sharer.php?u=$fbText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Facebook', 'eventLabel': '$permaLink'});"
		 target="_blank" class="share-btn facebook">
		 <svg viewBox="0 0 32 32" class="icon"><use xlink:href="/africa/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#facebook-f"></use></svg>
		<span class="visually-hidden">Share on Facebook</span>
	</a>
	<!-- Twitter -->
	<a href="https://twitter.com/intent/tweet?related=greenpeace&text=$twitterText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Twitter', 'eventLabel': '{{ social.link }}'});"
		 target="_blank" class="share-btn twitter">
		 <svg viewBox="0 0 32 32" class="icon"><use xlink:href="/africa/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#twitter"></use></svg>
		<span class="visually-hidden">Share on Twitter</span>
	</a>
	<!-- Email -->
	<a href="mailto:?subject=$mailText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Email', 'eventLabel': '{{ social.link }}'});"
		 target="_blank" class="share-btn email">
		 <svg viewBox="0 0 32 32" class="icon"><use xlink:href="/africa/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#envelope"></use></svg>
		<span class="visually-hidden">Share via Email</span>
	</a>
</div>
HTML;
                echo $html;
                ?></p>
		</div>

		<hr />
		<?php
	}
    $searchSuggestions = base64_encode(json_encode($searchSuggestions));
    $searchTitles = base64_encode(json_encode($audio));
	?>
	<div class="pagination">Page <?php echo $query->query['paged']; ?> of <?php echo $query->max_num_pages; ?><br /></div>

	<div class="pagination">
        <input type="hidden" name="searchSuggestions" style="width: 100%; font-size: 30px;" value="<?php echo $searchSuggestions; ?>">
        <input type="hidden" name="searchTitles" style="width: 100%; font-size: 30px;" value="" data-value="<?php echo $searchTitles; ?>">
		<div class="nav-previous"><?php next_posts_link( 'Older posts', $query->max_num_pages ); ?></div>
		<div class="nav-next"><?php previous_posts_link( 'Newer posts' ); ?></div>
		<?php
			/* example code for using the wp_pagenavi plugin */
			if (function_exists('wp_pagenavi'))
			{
				echo "<br />";
				wp_pagenavi( array( 'query' => $query ) );
			}
		?>
	</div>
	<?php
}
else
{
	echo "No Results Found";
}
?>
</div>
