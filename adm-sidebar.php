<?php 
/** Sidebar for admin page **/
function wpsts_admin_sidebar(){
	global $wpsts_pluginpath, $wpsts_donate_link;
	$txtDomain = 'wpsts';
	$plugin_name = 'wp-selected-text-sharer';
	
	echo "
<div id='sidebar'>
	<div class='donateBt'><a href='$wpsts_donate_link' target='_blank'><img src='{$wpsts_pluginpath}images/paypalDonateButton.png' alt='Donate' title='" . __('Donate and support this plugin\'s future', $txtDomain) . "'/></a></div>
	<ul class='admSprites'>
	
		<li><a class='admSpr-support' href='http://www.aakashweb.com/wordpress-plugins/{$plugin_name}/' target='_blank'>
			" . __('Plugin Support', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-documentation' href='http://www.aakashweb.com/faqs/wordpress-plugins/{$plugin_name}/' target='_blank'>
			" . __('Plugin Documentation', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-bug' href='http://www.aakashweb.com/forum/' target='_blank'>
			" . __('Bugs Report', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-faq' href='http://www.aakashweb.com/faqs/wordpress-plugins/{$plugin_name}/' target='_blank'>
			" . __('FAQ\'s', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-feature' href='http://www.aakashweb.com/forum/' target='_blank'>
			" . __('Request a feature', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-author' href='http://www.aakashweb.com/' target='_blank'>
			" . __('Author Website', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-donate' href='$wpsts_donate_link' target='_blank'>
			" . __('Support this plugin', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-twitter' href='http://twitter.com/vaakash' target='_blank'>
			" . __('Follow the author', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-rss' href='#' onclick='openSubForm();'>
			" . __('Subscribe to Updates', $txtDomain) . "
		</a></li>
		
		<li><a class='admSpr-addthis' href='#' onclick='openAddthis();'>
			" . __('Promote this plugin', $txtDomain) . "
		</a></li>
	
	</ul>
	
	<h4>" . __('More plugins', $txtDomain) . "</h4>
	
	<ul class='admSprites' style='margin-top:-6px'>
	
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/wp-socializer/' target='_blank'>
			WP Socializer
		</a></li>
		
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/wp-selected-text-sharer/' target='_blank'>
			WP Selected Text Sharer
		</a></li>
		
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/html-javascript-adder/' target='_blank'>
			HTML Javascript Adder
		</a></li>
		
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/shortcoder/' target='_blank'>
			Shortcoder
		</a></li>
		
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/announcer/' target='_blank'>
			Announcer
		</a></li>
		
		<li><a class='admSpr-star' href='http://www.aakashweb.com/wordpress-plugins/super-rss-reader/' target='_blank'>
			Super RSS Reader
		</a></li>
		
	</ul>
	
</div>
	";
}
?>