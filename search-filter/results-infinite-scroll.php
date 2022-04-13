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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$template_directory = get_template_directory();
$template_url = get_template_directory_uri();

if ( $query->have_posts() )
{
	?>
	<style>
        button.show-more-button {
            background-color: #F36D3A !important;
            box-shadow: 0px 3px 6px #00000029 !important;
            opacity: 80%;
            color: white !important;
            font-size: 20px;
            height: 70px;
            font-family: Roboto;
        }

        button.show-more-button.show-more-button-contracted::after {
            content: '▼';
            padding-left: 10px;
        }

        button.show-more-button.show-more-button-expanded::after {
            content: '▲';
            padding-left: 10px;
        }

        li.sf-field-search > label {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .audio-tags a::before {
            content: none !important;
        }

        .sf-field-tag ul {
            height: unset !important;
        }

        .share-buttons {
            justify-content: end;
        }

        .share-buttons-loop img {
            filter: invert();
            height: 25px !important;
        }

        .share-buttons-loop a::after {
            content: none !important;
        }
    </style>

	Found <?php echo $query->found_posts; ?> Results<br />
	<div class='search-filter-results-list'>
	<?php
		$searchSuggestions = [];
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
	        $permaLink = get_the_permalink();
	        $postID = get_the_ID();
	        $waText = urlencode("$permaLink&utm_medium=share&utm_content=$postID&utm_source=whatsapp");
	        $fbText = urlencode("$permaLink&utm_medium=share&utm_content=$postID&utm_source=facebook");
	        $twitterText = urlencode($permaLink) . ', via @greenpeace&url=' . urlencode("$permaLink&utm_medium=share&utm_content=$postID&utm_source=twitter") ;
	        $mailText = $title . '&body=' . urlencode("$permaLink?utm_medium=share&utm_content=$postID&utm_source=email");


			
			?>
			<div class='search-filter-result-item'>
				<h2 class="audio-loop-title"><a href="<?php echo $permaLink; ?>"><?php the_title(); ?></a></h2>
			<div class="audio-loop-meta"><?php the_category(); ?><p style="color: #006DFD; padding: 0 20px;">•</p>
                <p class="audio-tags"><?php the_tags(null, ' '); ?></p></div>
			<div class="author-date"><?php
            get_the_author();
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
		<img src="$whatsAppImg"  alt="WhatsApp" >
		<span class="visually-hidden">Share on Whatsapp</span>
	</a>
	<!-- Facebook -->
	<a href="https://www.facebook.com/sharer/sharer.php?u=$fbText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Facebook', 'eventLabel': '$permaLink'});"
		 target="_blank" class="share-btn facebook">
		 <img src="$fbImg"  alt="FaceBook" >
		<span class="visually-hidden">Share on Facebook</span>
	</a>
	<!-- Twitter -->
	<a href="https://twitter.com/intent/tweet?related=greenpeace&text=$twitterText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Twitter', 'eventLabel': '{{ social.link }}'});"
		 target="_blank" class="share-btn twitter">
		 <img src="$twitterImg"  alt="Twitter" >
		<span class="visually-hidden">Share on Twitter</span>
	</a>
	<!-- Email -->
	<a href="mailto:?subject=$mailText"
		 onclick="dataLayer.push({'event' : 'uaevent', 'eventCategory' : 'Social Share', 'eventAction': 'Email', 'eventLabel': '{{ social.link }}'});"
		 target="_blank" class="share-btn email">
		 <img src="$mailImg"  alt="Mail" >
		<span class="visually-hidden">Share via Email</span>
	</a>
</div>
HTML;
                echo $html;
                ?></p>
		</div>

		<hr />
			</div>
			
			<?php
		}
	    $searchSuggestions = base64_encode(json_encode($searchSuggestions));
	?>
	</div>
<?php
}
else
{
	?>
	<div class='search-filter-results-list' data-search-filter-action='infinite-scroll-end'>
		<span>End of Results</span>
	</div>
	<?php
}
?>