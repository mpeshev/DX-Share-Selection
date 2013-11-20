<?php
/*
Plugin Name: WP Selected Text sharer
Plugin URI: http://www.aakashweb.com/
Plugin Author: Aakash Chakravarthy
Description: WP Selected Text sharer is a plugin which allows to search, tweet, share selected text of your blog or website. Adds a simple menu above the selected text, enabling to share text, code and promoting your website.
Version: 1.0
Author URI: http://www.aakashweb.com/
*/

define('WPSTS_VERSION', '1.0');
define('WPSTS_AUTHOR', 'Aakash Chakravarthy');

if (!defined('WP_CONTENT_URL')) {
	$wpsts_pluginpath = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) . '/';
} else {
	$wpsts_pluginpath = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)) . '/';
}

$wpsts_donate_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=donations@aakashweb.com&amp;item_name=Donation for WP Selected Text Sharer Plugin&amp;amount=&amp;currency_code=USD';

## Load languages
load_plugin_textdomain('wpsts', false, basename(dirname(__FILE__)) . '/languages/');

## Include the files
require_once('integration.php');
require_once('adm-sidebar.php');

## WPSTS Is active check
function wpsts_is_active(){
    if (get_option("wpsts_active") == 1) {
        return 1;
    } else{
		return 0;
    }
}

## WPSTS plugin activate
function wpsts_plugin_activate(){
	update_option("wpsts_active", 1);
}
register_deactivation_hook(__FILE__, 'wpsts_plugin_activate');

## WPSTS plugin deactivate
function wpsts_plugin_deactivate(){
	update_option("wpsts_active", 0);
}
register_deactivation_hook(__FILE__, 'wpsts_plugin_deactivate');

## Admin Notices
function wpsts_admin_notices(){
	if(!wpsts_is_active() && $_GET['page'] != 'wp-selected-text-sharer/wp-selected-text-sharer.php'){
		echo '<div class="updated fade"><p>' . __('<b>WP Selected Text Sharer</b> plugin is intalled. You should immediately adjust <a href="options-general.php?page=wp-selected-text-sharer/wp-selected-text-sharer.php">the settings</a>', 'wpsts') . '</p></div>';
	}
}
add_action('admin_notices', 'wpsts_admin_notices');

## Action Links
function wpsts_plugin_actions($links, $file){
	static $this_plugin;
	if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=wp-selected-text-sharer/wp-selected-text-sharer.php">' . __('Settings', 'wpsts') . '</a> ' . '|' . ' <a href="http://www.aakashweb.com/">' . __('Support', 'wpsts') . '</a>';
		$links = array_merge( array($settings_link), $links);
	}
	return $links;
}
add_filter('plugin_action_links', 'wpsts_plugin_actions', 10, 2);

## Load the Javascripts
function wpsts_admin_js() {
	global $wpsts_pluginpath;
	$admin_js_url = $wpsts_pluginpath . 'wpsts-admin-js.js';
	$color_url = $wpsts_pluginpath . '/js/farbtastic/farbtastic.js';
	$wpsts_js = $wpsts_pluginpath . '/wpsts/jquery.selected-text-sharer.min.js';
	
	if (isset($_GET['page']) && $_GET['page'] == 'wp-selected-text-sharer/wp-selected-text-sharer.php') {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wp-selected-text-sharer', $admin_js_url, array('jquery'));
		wp_enqueue_script('farbtastic', $color_url, array('jquery', 'wp-selected-text-sharer'));
		wp_enqueue_script('wpsts_js', $wpsts_js, array('jquery', 'wp-selected-text-sharer', 'farbtastic'));
	}
}
add_action('admin_print_scripts', 'wpsts_admin_js');

## Load the admin CSS
function wpsts_admin_css() {
	global $wpsts_pluginpath;
	
	if (isset($_GET['page']) && $_GET['page'] == 'wp-selected-text-sharer/wp-selected-text-sharer.php') {
		wp_enqueue_style('wpsts-admin-css', $wpsts_pluginpath . 'wpsts-admin-css.css'); 
		wp_enqueue_style('farbtastic-css', $wpsts_pluginpath . '/js/farbtastic/farbtastic.css'); 
	}
}

add_action('admin_print_styles', 'wpsts_admin_css');

## Bitly shorten url
function wpsts_shorten_url($url, $format = 'xml'){
	
	## Get the Options
	$wpsts_settings = get_option('wpsts_settings_data');
	$wpsts_bitly = $wpsts_settings['bitly'];
	$bityly_split = explode(',', $wpsts_bitly);
	
	if($bityly_split[0] == '' || $bityly_split[1] ==''){
		return false;
	}
	
	$login = trim($bityly_split[0]);
	$appkey = trim($bityly_split[1]);
	$version = '2.0.1';
	
	$bitly = 'http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login=' . $login . '&apiKey='.$appkey . '&format=' . $format;
	
	$response = file_get_contents($bitly);
	
	if(strtolower($format) == 'json'){
		$json = @json_decode($response,true);
		return $json['results'][$url]['shortUrl'];
	}
	else{
		$xml = simplexml_load_string($response);
		return 'http://bit.ly/' . $xml->results->nodeKeyVal->hash;
	}
}

## One function for getting the url and title of the page
function wpsts_get_post_details(){
	// Get the global variables
	global $post;
	
	// Inside loop
	$permalink_inside_loop = get_permalink($post->ID);
	$title_inside_loop = str_replace('+', '%20', get_the_title($post->ID));
	
	// Outside loop
	$permalink_outside_loop = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$title_outside_loop = str_replace('+', '%20', wp_title('', 0));
	// If title is null
	if($title_outside_loop == ''){
		$title_outside_loop = str_replace('+', '%20', get_bloginfo('name'));
	}
	
	if(in_the_loop()){
		$details = array(
			'permalink' => $permalink_inside_loop,
			'title' => $title_inside_loop
		);
		
	}else{
		$details = array(
			'permalink' => $permalink_outside_loop,
			'title' => $title_outside_loop
		);
	}
	
	return $details;
}

## Get processed list
function wpsts_get_processed_list(){
	global $post;
	
	$info = wpsts_get_post_details();
	$rss = get_bloginfo('rss_url');
	$blogname = urlencode(get_bloginfo('name') . ' ' . get_bloginfo('description'));
	
	$terms = array(
		'{url}', '{title}', 
		'{surl}', '{blogname}', 
		'{rss-url}'
	);
	$replacable = array(
		$info['permalink'], $info['title'], 
		wpsts_shorten_url($info['permalink'], 'json'), $blogname,
		$rss
	);
	
	## Get the Options
	$wpsts_settings = get_option('wpsts_settings_data');
	$wpsts_lists = $wpsts_settings['lists'];
	
	$listExplode = explode("\n", $wpsts_lists);
	$listImplode = implode('|', $listExplode);
	$list = str_replace("\r","", $listImplode);
	
	$listFinal = str_replace($terms, $replacable, $list);
	return $listFinal;
}

## Enqueue Scripts to the wordpress
add_action('wp_enqueue_scripts', 'wpsts_scripts');
function wpsts_scripts() {
	global $wpsts_pluginpath;
	
	## Get the Options
	$wpsts_settings = get_option('wpsts_settings_data');
	
	$wpsts_title = $wpsts_settings['title'];
	$wpsts_lists = $wpsts_settings['lists'];
	
	$wpsts_borderColor = $wpsts_settings['borderColor'];
	$wpsts_bgColor = $wpsts_settings['bgColor'];
	$wpsts_titleColor = $wpsts_settings['titleColor'];
	$wpsts_hoverColor = $wpsts_settings['hoverColor'];
	$wpsts_textColor = $wpsts_settings['textColor'];
	$wpsts_extraClass = $wpsts_settings['extraClass'];
	
	$wpsts_element = $wpsts_settings['element'];
	$wpsts_scriptPlace = $wpsts_settings['scriptPlace'];
	$wpsts_truncateChars = $wpsts_settings['truncateChars'];
	$wpsts_useJquery = $wpsts_settings['useJquery'];

	if ($wpsts_useJquery == 1){
		wp_enqueue_script('wp-selected-text-searcher', $wpsts_pluginpath . 'wpsts/jquery.selected-text-sharer.min.js', array('jquery'), null, $wpsts_scriptPlace);
	}else{
		wp_enqueue_script('wp-selected-text-searcher', $wpsts_pluginpath . 'wpsts/selected-text-sharer.min.js', '', null, $wpsts_scriptPlace);
		wp_localize_script('wp-selected-text-searcher', 'sts_config', array(
			'title' => $wpsts_title,
			'lists' => wpsts_get_processed_list(),
			'truncateChars' => $wpsts_truncateChars,
			'extraClass' => $wpsts_extraClass,
			'borderColor' => $wpsts_borderColor,
			'background' => $wpsts_bgColor,
			'titleColor' => $wpsts_titleColor,
			'hoverColor' => $wpsts_hoverColor,
			'textColor' => $wpsts_textColor
		));
	}
}

## Activate Jquery the Jquery
function wpsts_jquery_plugin_activate(){

	## Get the Options
	$wpsts_settings = get_option('wpsts_settings_data');
	
	$wpsts_title = $wpsts_settings['title'];
	$wpsts_lists = $wpsts_settings['lists'];
	
	$wpsts_borderColor = $wpsts_settings['borderColor'];
	$wpsts_bgColor = $wpsts_settings['bgColor'];
	$wpsts_titleColor = $wpsts_settings['titleColor'];
	$wpsts_hoverColor = $wpsts_settings['hoverColor'];
	$wpsts_textColor = $wpsts_settings['textColor'];
	$wpsts_extraClass = $wpsts_settings['extraClass'];
	
	$wpsts_element = $wpsts_settings['element'];
	$wpsts_scriptPlace = $wpsts_settings['scriptPlace'];
	$wpsts_truncateChars = $wpsts_settings['truncateChars'];
	$wpsts_useJquery = $wpsts_settings['useJquery'];

	if($wpsts_useJquery == 1){
		echo "\n".
"<script type='text/javascript'>
/* <![CDATA[ */
	jQuery(document).ready(function(){
		jQuery('body').selectedTextSharer({
			title : '$wpsts_title',
			lists : '" . wpsts_get_processed_list() . "',
			truncateChars : '$wpsts_truncateChars',
			extraClass : '$wpsts_extraClass',
			borderColor : '$wpsts_borderColor',
			background : '$wpsts_bgColor',
			titleColor : '$wpsts_titleColor',
			hoverColor : '$wpsts_hoverColor',
			textColor : '$wpsts_textColor'
		}); 
	}); 
/* ]]>*/
</script>\n";
	}
}
add_action('wp_footer', 'wpsts_jquery_plugin_activate');

## Add the Admin menu
add_action('admin_menu', 'wpsts_addpage');

function wpsts_addpage() {
    add_submenu_page('options-general.php', 'WP Selected Text Sharer', 'WP Selected Text Sharer', 10, __FILE__, 'wpsts_admin_page');
}

function wpsts_admin_page(){
	global $wpsts_pluginpath;
	$wpsts_updated = false;
	
	$searchUrl = get_bloginfo('url') . '/?s=%s';
	
	$wpsts_listsDefault = "Search, $searchUrl, favicon\nTweet this, http://twitter.com/home?status=%ts {surl}, favicon";
	
	if ($_POST["wpsts_submit"]){
	
		## Get and store options
		$wpsts_settings['title'] = $_POST['wpsts_title'];
		$wpsts_settings['lists'] = preg_replace('/^[ \t]*[\r\n]+/m', "", trim(stripslashes($_POST['wpsts_lists'])));
	
		$wpsts_settings['borderColor'] = $_POST['wpsts_borderColor'];
		$wpsts_settings['bgColor'] = $_POST['wpsts_bgColor'];
		$wpsts_settings['titleColor'] = $_POST['wpsts_titleColor'];
		$wpsts_settings['hoverColor'] = $_POST['wpsts_hoverColor'];
		$wpsts_settings['textColor'] = $_POST['wpsts_textColor'];
		$wpsts_settings['extraClass'] = $_POST['wpsts_extraClass'];
		
		$wpsts_settings['useJquery'] = $_POST['wpsts_useJquery'];
		$wpsts_settings['scriptPlace'] = $_POST['wpsts_scriptPlace'];
		$wpsts_settings['truncateChars'] = $_POST['wpsts_truncateChars'];
		$wpsts_settings['element'] = $_POST['wpsts_element'];
		$wpsts_settings['bitly'] = $_POST['wpsts_bitly'];
		
		$wpsts_settings['wpsts_is_activate'] = 1;
		update_option("wpsts_settings_data", $wpsts_settings);
		$wpsts_updated = true;
		
		if(get_option("wpsts_active") == 0){
			update_option("wpsts_active", 1);
		}
		
	}
	
	if($wpsts_updated == true){
		echo "<div class='message updated'><p>Updated successfully</p></div>";
	}
	
	## Get the Options
	$wpsts_settings = get_option('wpsts_settings_data');
	
	$wpsts_title = $wpsts_settings['title'];
	$wpsts_lists = $wpsts_settings['lists'];
	
	$wpsts_borderColor = $wpsts_settings['borderColor'];
	$wpsts_bgColor = $wpsts_settings['bgColor'];
	$wpsts_titleColor = $wpsts_settings['titleColor'];
	$wpsts_hoverColor = $wpsts_settings['hoverColor'];
	$wpsts_textColor = $wpsts_settings['textColor'];
	$wpsts_extraClass = $wpsts_settings['extraClass'];
	
	$wpsts_element = $wpsts_settings['element'];
	$wpsts_scriptPlace = $wpsts_settings['scriptPlace'];
	$wpsts_truncateChars = $wpsts_settings['truncateChars'];
	$wpsts_useJquery = $wpsts_settings['useJquery'];
	$wpsts_bitly = $wpsts_settings['bitly'];
	
	## Defaults
	$wpsts_title = ($wpsts_title == '') ? __('Share this text ...', 'wpsts') : $wpsts_title;
	$wpsts_lists = ($wpsts_lists == '') ? $wpsts_listsDefault : $wpsts_lists;
	
	$wpsts_borderColor = ($wpsts_borderColor == '') ? '#444' : $wpsts_borderColor;
	$wpsts_bgColor = ($wpsts_bgColor == '') ? '#fff' : $wpsts_bgColor;
	$wpsts_titleColor = ($wpsts_titleColor == '') ? '#f2f2f2' : $wpsts_titleColor;
	$wpsts_hoverColor = ($wpsts_hoverColor == '') ? '#ffffcc' : $wpsts_hoverColor;
	$wpsts_textColor = ($wpsts_textColor == '') ? '#000' : $wpsts_textColor;
	
	$wpsts_element = ($wpsts_element == '') ? 'body' : $wpsts_element;
	$wpsts_scriptPlace = ($wpsts_scriptPlace == '') ? '1' : $wpsts_scriptPlace;
	$wpsts_useJquery = ($wpsts_useJquery == '') ? '1' : $wpsts_useJquery;
	$wpsts_truncateChars = ($wpsts_truncateChars == '') ? '115' : $wpsts_truncateChars;
	
?>

<div class="wrap">
	<h2><img width="32" height="32" src="<?php echo $wpsts_pluginpath; ?>images/wp-selected-text-sharer.png" align="absmiddle"/>&nbsp;WP Selected Text Sharer <span class="smallText">v<?php echo WPSTS_VERSION; ?></span></h2>
	
	<div id="leftContent">
		<form method="post">
			<div class="content">
				<h4><?php _e('General', 'wpsts'); ?></h4>
				<div class="section">
				  <table width="100%" border="0">
                    <tr>
                      <td width="19%" height="32"><?php _e('Widget Title', 'wpsts'); ?></td>
                      <td width="81%"><input name="wpsts_title" id="wpsts_title" type="text" value="<?php echo $wpsts_title; ?>"/></td>
                    </tr>
                    <tr>
                      <td height="33"><?php _e('Share Items', 'wpsts'); ?></td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td height="33" colspan="2"><span class="smallText"><?php _e('Add buttons', 'wpsts'); ?></span>
                        <select name="select" id="addList">
                        </select> 
						<input type="button" id="addCustom" class="toolBt button" value="<?php _e('Add custom button', 'wpsts'); ?>" />
						<input type="button" id="addSearch" class="toolBt button" value="<?php _e('Add search button', 'wpsts'); ?>" />
						<input type="button" class="toolBt openWpsrLinks button" value="<?php _e('More buttons', 'wpsts'); ?>" />
						<input type="button" class="toolBt openHelp button" value="<?php _e('Help', 'wpsts'); ?>" />
					</tr>
                    <tr>
                      <td colspan="2"><textarea name="wpsts_lists" id="wpsts_lists"><?php echo $wpsts_lists; ?></textarea>
					  <span class="smallText"><?php _e('Format : Name, Share/Search URL, Icon URL', 'wpsts'); ?></span>					  </td>
                    </tr>
                  </table>
				  
				</div>
				
				<div id="colorpicker" class="picker"></div>
				
				<h4><?php _e('Customize', 'wpsts'); ?></h4>
				<div class="section">
				  
				  <table width="100%" height="220" border="0">
                        <tr>
                          <td width="22%" height="33"><?php _e('Border Color', 'wpsts'); ?></td>
                          <td width="78%"><input name="wpsts_borderColor" id="wpsts_borderColor" class="color" type="text" value="<?php echo $wpsts_borderColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="37"><?php _e('Background Color', 'wpsts'); ?></td>
                          <td><input name="wpsts_bgColor" id="wpsts_bgColor" class="color" type="text" value="<?php echo $wpsts_bgColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="35"><?php _e('Title Background color', 'wpsts'); ?></td>
                          <td><input name="wpsts_titleColor" id="wpsts_titleColor" class="color" type="text" value="<?php echo $wpsts_titleColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="36"><?php _e('Hover Color', 'wpsts'); ?></td>
                          <td><input name="wpsts_hoverColor" id="wpsts_hoverColor" class="color" type="text" value="<?php echo $wpsts_hoverColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="30"><?php _e('Text Color', 'wpsts'); ?></td>
                          <td><input name="wpsts_textColor" id="wpsts_textColor" class="color" type="text" value="<?php echo $wpsts_textColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td><?php _e('Extra Class', 'wpsts'); ?></td>
                          <td><input name="wpsts_extraClass" type="text" value="<?php echo $wpsts_extraClass; ?>"/></td>
                        </tr>
                  </table>
				</div>
				
				<h4><?php _e('Optional', 'wpsts'); ?></h4>
				<div class="section">
				  <table width="100%" height="162" border="0">
                    <tr>
                      <td width="22%" height="48"><?php _e('Use Jquery Version', 'wpsts'); ?></td>
                      <td width="78%">
			<select id="wpsts_useJquery" name="wpsts_useJquery">
          <option <?php echo $wpsts_useJquery == '1' ? ' selected="selected"' : ''; ?> value="1">Yes</option>
          <option <?php echo $wpsts_useJquery == '0' ? ' selected="selected"' : ''; ?> value="0">No</option>
        </select><br/><small class="smallText"><?php _e('Recommended for fade effects and cross browser support', 'wpsts'); ?></small></td>
                    </tr>
                    <tr>
                      <td height="35"><?php _e('Load scripts in', 'wpsts'); ?></td>
                      <td><select id="wpsts_scriptPlace" name="wpsts_scriptPlace">
          <option <?php echo $wpsts_scriptPlace == '0' ? ' selected="selected"' : ''; ?> value="0"><?php _e('Header', 'wpsts'); ?></option>
          <option <?php echo $wpsts_scriptPlace == '1' ? ' selected="selected"' : ''; ?> value="1"><?php _e('Footer', 'wpsts'); ?></option>
        </select></td>
                    </tr>
                    <tr>
                      <td height="35"><?php _e('Truncate Text', 'wpsts'); ?></td>
                      <td><input name="wpsts_truncateChars" type="text" value="<?php echo $wpsts_truncateChars; ?>"/><br/>
					  <small class="smallText"><?php _e('Selected texts are truncated when <code>%ts</code> is used in the URL', 'wpsts'); ?></small>
					  </td>
                    </tr>
                    <tr>
                      <td height="35"><?php _e('Target Content', 'wpsts'); ?></td>
                      <td><input name="wpsts_element" type="text" value="<?php echo $wpsts_element; ?>"/></td>
                    </tr>
                    <tr>
                      <td><?php _e('Bitly Settings', 'wpsts'); ?></td>
                      <td><input name="wpsts_bitly" type="text"  value="<?php echo $wpsts_bitly; ?>" size="40"/>
                        <br />
					  <small class="smallText"><?php _e('Bitly Username, API key. Used in twitter URL', 'wpsts'); ?></small>					  </td>
                    </tr>
                  </table>
				</div>
				
				<h4><?php _e('Preview', 'wpsts'); ?></h4>
				<div class="section preview">
				<small class="smallText"><?php _e('Select a text to show the widget', 'wpsts'); ?></small><br/>
				Lorem ipsum et natum omnesque vel, id audire repudiandae mei, eirmod tritani ex usu. Ius ex wisi labores nonummy, omnis fuisset persequeris no ius. Eam modus persecuti ex, qui in alienum vulputate, kasd elitr an cum. Corpora molestiae forensibus quo ei, autem dicam vivendo ne eum. Id numquam nominavi similique usu.
				</div>
				
				<input class="button-primary" type="submit" name="wpsts_submit" id="wpsts_submit" value="     <?php _e('Update', 'wpsts'); ?>     " />
			</div>
		</form>
	</div>
		
		<div class="lightBox helpWindow bottomShadow">
			<input type="button" class="closeHelp close button" value="Close" />
			<h3><?php _e('Help', 'wpsts'); ?></h3>
			<hr/>
			<div class="wrap">
				<p><?php _e('The format for adding a custom button to the widget is', 'wpsts'); ?><br />
				<?php _e('<code>Name of the button</code>, <code>Share / Search URL</code>, <code>Icon URL</code>', 'wpsts'); ?></p>
				
				<b><?php _e('Note:', 'wpsts'); ?></b><br/>
				<ol>
				<li><?php _e('Use <code>%s</code> in the Share/Search URL to use the selected text.', 'wpsts'); ?></li>
				<li><?php _e('Use <code>%ts</code> in the URL to use the truncated selected text (115 characters. Used in Twitter URL).', 'wpsts'); ?></li>
				<li><?php _e('Use the text <code>favicon</code> in the Icon URL to automatically get the button icon.', 'wpsts'); ?></li>
				<li><?php _e('Use <code>{url}</code> in the URL to get the current page url.', 'wpsts'); ?></li>
				<li><?php _e('Use <code>{surl}</code> in the URL to get the shortened current page URL.', 'wpsts'); ?></li>
				<li><?php _e('Use <code>{title}</code> in the URL to get the current page title.', 'wpsts'); ?></li>
				</ol>
				<p class="smallText"><?php _e('Popular and recommended buttons are given in by default. Just select the button from the dropdown list. Above settings are required only if you want to add a custom button or change the template', 'wpsts'); ?></p>
			</div>
		</div>
		
  <div class="lightBox wpsrBox bottomShadow">
			<?php if(function_exists('wp_socializer')): ?>
				<input type="button" class="closeLinks close button" value="Close" />
				<h3><?php _e('Insert more social buttons', 'wpsts'); ?></h3>
				<small class="smallText"><?php _e('These buttons are taken from wp-socializer plugin. You can now use these buttons for wp-selected-text-searcher', 'wpsts'); ?></small>
				<div class="listSearch"><input type="text" id="wpsts_list_search" title="<?php _e('Search ...', 'wpsts'); ?>" size="35"/>
				</div>
				<hr/>
				<div class="wrap">
					<ol class="wpsts_wpsr_sites">
						<?php wpsts_wpsr_get_links(); ?>
					</ol>
				</div>
			<?php else: ?>
				<input type="button" class="closeLinks close button" value="Close" />
				<h3><?php _e('Install WP Socializer plugin', 'wpsts'); ?></h3>
				<hr />
				<p><?php _e('Sorry, you need to install <b>WP Socializer plugin</b> to get the additional social buttons links and data.', 'wpsts'); ?></p>
				<p><?php _e('You can install the powerful WP Socializer plugin in one click securely by clicking the Install button below.', 'wpsts'); ?></p>
				<?php
					 $nonce= wp_create_nonce ('wpsts-nonce');
					 $installUrl = 'update.php?action=install-plugin&plugin=wp-socializer&_wpnonce=' . $nonce;
				?>
				<p align="center"><a href="<?php echo $installUrl; ?>" target="_blank" class="button-primary"><?php _e('Install Plugin', 'wpsts'); ?></a></p>
				<b><?php _e('Note:', 'wpsts'); ?></b><br />
	  <small class="smallText"><?php _e('WP Selected text sharer requires to install WP Socializer to link the additional 98 buttons. <a href="http://www.aakashweb.com/wordpress-plugins/wp-socializer/" target="_blank">See here</a> for more info', 'wpsts'); ?></small>
	<?php endif; ?>
	</div>
		
		
	</div>
	
	<?php wpsts_admin_sidebar(); ?><!-- Sidebar -->
	
</div>

<?php
}

?>