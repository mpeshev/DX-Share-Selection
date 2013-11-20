<?php
/** WP Selected Text Sharer
  * Contains the integration files for WP Selected Text Sharer with WP Socializer
  * v 1.0
  */

## Get links from WP-Socializer
function wpsts_wpsr_get_links(){
	if(function_exists('wp_socializer')){
		global $wpsr_socialsites_list;
		$i = 0;
		foreach ($wpsr_socialsites_list as $key => $value){
			if($i != 0){
				$tempUrl = str_replace('{permalink}', '{url}', $value['url']);
				$tempUrl2 = str_replace('{excerpt}', '%s', $tempUrl);
				echo '<li><a href="#" rel="' . $tempUrl2 . '">' . $key . '</a></li>';
			}
			$i++;
		}
	}
}
?>