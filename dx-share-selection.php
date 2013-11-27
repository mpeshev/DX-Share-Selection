<?php
/*
Plugin Name: DX Share Selection
Plugin URI: http://www.devwp.eu/
Plugin Author: nofeairnc
Description: DX Share Selection is a fork of WP Selected Text sharer aiming to share your selected text in social networks. Select a text/code snippet from your post/page and share it to various social media websites.
Version: 1.2
Author URI: http://www.devwp.eu/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define('DXSS_VERSION', '1.2');
define('DXSS_AUTHOR', 'Mario Peshev');

if (!defined('WP_CONTENT_URL')) {
	$dxss_pluginpath = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) . '/';
} else {
	$dxss_pluginpath = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)) . '/';
}

## Load languages
load_plugin_textdomain('dxss', false, basename(dirname(__FILE__)) . '/languages/');

## Include the files
require_once( 'integration.php' );
require_once( 'dxss-option-helper.php' );

## WPSTS Is active check
function dxss_is_active(){
    if (get_option("dxss_active") == 1) {
        return 1;
    } else{
		return 0;
    }
}

## WPSTS plugin activate
function dxss_plugin_activate(){
	update_option("dxss_active", 1);
}
register_deactivation_hook(__FILE__, 'dxss_plugin_activate');

## WPSTS plugin deactivate
function dxss_plugin_deactivate(){
	update_option("dxss_active", 0);
}
register_deactivation_hook(__FILE__, 'dxss_plugin_deactivate');

## Admin Notices
function dxss_admin_notices(){
	if( isset( $_GET['page'] ) && !dxss_is_active() && $_GET['page'] != 'wp-selected-text-sharer/wp-selected-text-sharer.php'){
		echo '<div class="updated fade"><p>' . __('<b>DX Share Selection</b> plugin is intalled. You should immediately adjust <a href="options-general.php?page=wp-selected-text-sharer/wp-selected-text-sharer.php">the settings</a>', 'dxss') . '</p></div>';
	}
}
add_action('admin_notices', 'dxss_admin_notices');

## Action Links
function dxss_plugin_actions($links, $file){
	static $this_plugin;
	if(!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=wp-selected-text-sharer/wp-selected-text-sharer.php">' . __('Settings', 'dxss') . '</a> ' . '|' . ' <a href="http://www.aakashweb.com/">' . __('Support', 'dxss') . '</a>';
		$links = array_merge( array($settings_link), $links);
	}
	return $links;
}
add_filter('plugin_action_links', 'dxss_plugin_actions', 10, 2);

## Load the Javascripts
function dxss_admin_js() {
	global $dxss_pluginpath;
	$admin_js_url = $dxss_pluginpath . 'dxss-admin-js.js';
	$color_url = $dxss_pluginpath . '/js/farbtastic/farbtastic.js';
	$dxss_js = $dxss_pluginpath . '/dxss/jquery.selected-text-sharer.min.js';
	
	if (isset($_GET['page']) && $_GET['page'] == 'wp-selected-text-sharer/wp-selected-text-sharer.php') {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wp-selected-text-sharer', $admin_js_url, array('jquery'));
		wp_enqueue_script('farbtastic', $color_url, array('jquery', 'wp-selected-text-sharer'));
		wp_enqueue_script('dxss_js', $dxss_js, array('jquery', 'wp-selected-text-sharer', 'farbtastic'));
	}
}
add_action('admin_print_scripts', 'dxss_admin_js');

## Load the admin CSS
function dxss_admin_css() {
	global $dxss_pluginpath;
	
	if (isset($_GET['page']) && $_GET['page'] == 'wp-selected-text-sharer/wp-selected-text-sharer.php') {
		wp_enqueue_style('dxss-admin-css', $dxss_pluginpath . 'dxss-admin-css.css'); 
		wp_enqueue_style('farbtastic-css', $dxss_pluginpath . '/js/farbtastic/farbtastic.css'); 
	}
}

add_action('admin_print_styles', 'dxss_admin_css');

## Bitly shorten url
function dxss_shorten_url($url, $format = 'xml'){
	
	## Get the Options
	$dxss_settings = DXSS_Option_Helper::fetch_settings_data();
	$dxss_bitly = $dxss_settings['bitly'];
	$bityly_split = explode(',', $dxss_bitly);
	
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
function dxss_get_post_details(){
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
function dxss_get_processed_list(){
	global $post;
	
	$info = dxss_get_post_details();
	$rss = get_bloginfo('rss_url');
	$blogname = urlencode(get_bloginfo('name') . ' ' . get_bloginfo('description'));
	
	$terms = array(
		'{url}', '{title}', 
		'{surl}', '{blogname}', 
		'{rss-url}'
	);
	$replacable = array(
		$info['permalink'], $info['title'], 
		dxss_shorten_url($info['permalink'], 'json'), $blogname,
		$rss
	);
	
	## Get the Options
	$dxss_settings = DXSS_Option_Helper::fetch_settings_data();
	$dxss_lists = $dxss_settings['lists'];
	
	$listExplode = explode("\n", $dxss_lists);
	$listImplode = implode('|', $listExplode);
	$list = str_replace("\r","", $listImplode);
	
	$listFinal = str_replace($terms, $replacable, $list);
	return $listFinal;
}

## Enqueue Scripts to the wordpress
add_action('wp_enqueue_scripts', 'dxss_scripts');
function dxss_scripts() {
	global $dxss_pluginpath;
	
	## Get the Options
	$dxss_settings = DXSS_Option_Helper::fetch_settings_data();
	$dxss_scriptPlace = $dxss_settings['scriptPlace'];
		
	wp_enqueue_script('wp-selected-text-searcher', $dxss_pluginpath . 'dxss/jquery.selected-text-sharer.min.js', array('jquery'), null, $dxss_scriptPlace);
}

## Activate Jquery the Jquery
function dxss_jquery_plugin_activate(){

	## Get the Options
	$dxss_settings = DXSS_Option_Helper::fetch_settings_data();
	
	$dxss_title = $dxss_settings['title'];
	$dxss_lists = $dxss_settings['lists'];
	
	$dxss_borderColor = $dxss_settings['borderColor'];
	$dxss_bgColor = $dxss_settings['bgColor'];
	$dxss_titleColor = $dxss_settings['titleColor'];
	$dxss_hoverColor = $dxss_settings['hoverColor'];
	$dxss_textColor = $dxss_settings['textColor'];
	$dxss_extraClass = $dxss_settings['extraClass'];
	
	$dxss_element = $dxss_settings['element'];
	$dxss_scriptPlace = $dxss_settings['scriptPlace'];
	$dxss_truncateChars = $dxss_settings['truncateChars'];

		echo "\n".
"<script type='text/javascript'>
/* <![CDATA[ */
	jQuery(document).ready(function(){
		jQuery('body').selectedTextSharer({
			title : '$dxss_title',
			lists : '" . dxss_get_processed_list() . "',
			truncateChars : '$dxss_truncateChars',
			extraClass : '$dxss_extraClass',
			borderColor : '$dxss_borderColor',
			background : '$dxss_bgColor',
			titleColor : '$dxss_titleColor',
			hoverColor : '$dxss_hoverColor',
			textColor : '$dxss_textColor'
		}); 
	}); 
/* ]]>*/
</script>\n";
}
add_action('wp_footer', 'dxss_jquery_plugin_activate');

## Add the Admin menu
add_action('admin_menu', 'dxss_addpage');

function dxss_addpage() {
    add_submenu_page('options-general.php', 'DX Share Selection', 'DX Share Selection', 'manage_options', 'wp-selected-text-sharer', 'dxss_admin_page');
}

function dxss_admin_page(){
	global $dxss_pluginpath;
	$dxss_updated = false;
	
	if ( ! empty( $_POST["dxss_submit"] ) ) {
		## Get and store options
		$dxss_settings['title'] = $_POST['dxss_title'];
		$dxss_settings['lists'] = preg_replace('/^[ \t]*[\r\n]+/m', "", trim(stripslashes($_POST['dxss_lists'])));
	
		$dxss_settings['borderColor'] = $_POST['dxss_borderColor'];
		$dxss_settings['bgColor'] = $_POST['dxss_bgColor'];
		$dxss_settings['titleColor'] = $_POST['dxss_titleColor'];
		$dxss_settings['hoverColor'] = $_POST['dxss_hoverColor'];
		$dxss_settings['textColor'] = $_POST['dxss_textColor'];
		$dxss_settings['extraClass'] = $_POST['dxss_extraClass'];
		
		$dxss_settings['scriptPlace'] = $_POST['dxss_scriptPlace'];
		$dxss_settings['truncateChars'] = $_POST['dxss_truncateChars'];
		$dxss_settings['element'] = $_POST['dxss_element'];
		$dxss_settings['bitly'] = $_POST['dxss_bitly'];
		$dxss_settings['grepElement'] = $_POST['dxssgrep_element'];
		
		$dxss_settings['dxss_is_activate'] = 1;
		DXSS_Option_Helper::update_settings_data( $dxss_settings );
		$dxss_updated = true;
		
		if(get_option("dxss_active") == 0){
			update_option("dxss_active", 1);
		}
		
	}
	
	if($dxss_updated == true){
		echo "<div class='message updated'><p>Updated successfully</p></div>";
	}
	
	## Get the Options
	$dxss_settings = DXSS_Option_Helper::fetch_settings_data();
	
	$dxss_title = $dxss_settings['title'];
	$dxss_lists = $dxss_settings['lists'];
	
	$dxss_borderColor = $dxss_settings['borderColor'];
	$dxss_bgColor = $dxss_settings['bgColor'];
	$dxss_titleColor = $dxss_settings['titleColor'];
	$dxss_hoverColor = $dxss_settings['hoverColor'];
	$dxss_textColor = $dxss_settings['textColor'];
	$dxss_extraClass = $dxss_settings['extraClass'];
	$dxssgrep_element = $dxss_settings['grepElement'];
	
	$dxss_element = $dxss_settings['element'];
	$dxss_scriptPlace = $dxss_settings['scriptPlace'];
	$dxss_truncateChars = $dxss_settings['truncateChars'];
	$dxss_bitly = $dxss_settings['bitly'];
?>

<div class="wrap">
	<h2><img width="32" height="32" src="<?php echo $dxss_pluginpath; ?>images/wp-selected-text-sharer.png" align="absmiddle"/>&nbsp;DX Share Selection <span class="smallText">v<?php echo DXSS_VERSION; ?></span></h2>
	
	<div id="leftContent">
		<form method="post">
			<div class="content">
				<h4><?php _e('General', 'dxss'); ?></h4>
				<div class="section">
				  <table width="100%" border="0">
                    <tr>
                      <td width="19%" height="32"><?php _e('Widget Title', 'dxss'); ?></td>
                      <td width="81%"><input name="dxss_title" id="dxss_title" type="text" value="<?php echo $dxss_title; ?>"/></td>
                    </tr>
                    <tr>
                      <td height="33"><?php _e('Share Items', 'dxss'); ?></td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td height="33" colspan="2"><span class="smallText"><?php _e('Add buttons', 'dxss'); ?></span>
                        <select name="select" id="addList">
                        </select> 
						<input type="button" id="addCustom" class="toolBt button" value="<?php _e('Add custom button', 'dxss'); ?>" />
						<input type="button" id="addSearch" class="toolBt button" value="<?php _e('Add search button', 'dxss'); ?>" />
						<input type="button" class="toolBt openWpsrLinks button" value="<?php _e('More buttons', 'dxss'); ?>" />
						<input type="button" class="toolBt openHelp button" value="<?php _e('Help', 'dxss'); ?>" />
					</tr>
                    <tr>
                      <td colspan="2"><textarea name="dxss_lists" id="dxss_lists"><?php echo $dxss_lists; ?></textarea>
					  <span class="smallText"><?php _e('Format : Name, Share/Search URL, Icon URL', 'dxss'); ?></span>					  </td>
                    </tr>
                  </table>
				  
				</div>
				
				<div id="colorpicker" class="picker"></div>
				
				<h4><?php _e('Customize', 'dxss'); ?></h4>
				<div class="section">
				  
				  <table width="100%" height="220" border="0">
                        <tr>
                          <td width="22%" height="33"><?php _e('Border Color', 'dxss'); ?></td>
                          <td width="78%"><input name="dxss_borderColor" id="dxss_borderColor" class="color" type="text" value="<?php echo $dxss_borderColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="37"><?php _e('Background Color', 'dxss'); ?></td>
                          <td><input name="dxss_bgColor" id="dxss_bgColor" class="color" type="text" value="<?php echo $dxss_bgColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="35"><?php _e('Title Background color', 'dxss'); ?></td>
                          <td><input name="dxss_titleColor" id="dxss_titleColor" class="color" type="text" value="<?php echo $dxss_titleColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="36"><?php _e('Hover Color', 'dxss'); ?></td>
                          <td><input name="dxss_hoverColor" id="dxss_hoverColor" class="color" type="text" value="<?php echo $dxss_hoverColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td height="30"><?php _e('Text Color', 'dxss'); ?></td>
                          <td><input name="dxss_textColor" id="dxss_textColor" class="color" type="text" value="<?php echo $dxss_textColor; ?>"/></td>
                        </tr>
                        <tr>
                          <td><?php _e('Extra Class', 'dxss'); ?></td>
                          <td><input name="dxss_extraClass" type="text" value="<?php echo $dxss_extraClass; ?>"/></td>
                        </tr>
                        <tr>
                          <td><?php _e('Grep Element', 'dxss'); ?></td>
                          <td><input name="dxssgrep_element" type="text" value="<?php echo $dxssgrep_element; ?>"/></td>
                        </tr>
                  </table>
				</div>
				
				<h4><?php _e('Optional', 'dxss'); ?></h4>
				<div class="section">
				  <table width="100%" height="162" border="0">
                    <tr>
                      <td height="35"><?php _e('Load scripts in', 'dxss'); ?></td>
                      <td><select id="dxss_scriptPlace" name="dxss_scriptPlace">
          <option <?php echo $dxss_scriptPlace == '0' ? ' selected="selected"' : ''; ?> value="0"><?php _e('Header', 'dxss'); ?></option>
          <option <?php echo $dxss_scriptPlace == '1' ? ' selected="selected"' : ''; ?> value="1"><?php _e('Footer', 'dxss'); ?></option>
        </select></td>
                    </tr>
                    <tr>
                      <td height="35"><?php _e('Truncate Text', 'dxss'); ?></td>
                      <td><input name="dxss_truncateChars" type="text" value="<?php echo $dxss_truncateChars; ?>"/><br/>
					  <small class="smallText"><?php _e('Selected texts are truncated when <code>%ts</code> is used in the URL', 'dxss'); ?></small>
					  </td>
                    </tr>
                    <tr>
                      <td height="35"><?php _e('Target Content', 'dxss'); ?></td>
                      <td><input name="dxss_element" type="text" value="<?php echo $dxss_element; ?>"/></td>
                    </tr>
                    <tr>
                      <td><?php _e('Bitly Settings', 'dxss'); ?></td>
                      <td><input name="dxss_bitly" type="text"  value="<?php echo $dxss_bitly; ?>" size="40"/>
                        <br />
					  <small class="smallText"><?php _e('Bitly Username, API key. Used in twitter URL', 'dxss'); ?></small>					  </td>
                    </tr>
                  </table>
				</div>
				
				<h4><?php _e('Preview', 'dxss'); ?></h4>
				<div class="section preview">
				<small class="smallText"><?php _e('Select a text to show the widget', 'dxss'); ?></small><br/>
				Lorem ipsum et natum omnesque vel, id audire repudiandae mei, eirmod tritani ex usu. Ius ex wisi labores nonummy, omnis fuisset persequeris no ius. Eam modus persecuti ex, qui in alienum vulputate, kasd elitr an cum. Corpora molestiae forensibus quo ei, autem dicam vivendo ne eum. Id numquam nominavi similique usu.
				</div>
				
				<input class="button-primary" type="submit" name="dxss_submit" id="dxss_submit" value="     <?php _e('Update', 'dxss'); ?>     " />
			</div>
		</form>
	</div>
		
		<div class="lightBox helpWindow bottomShadow">
			<input type="button" class="closeHelp close button" value="Close" />
			<h3><?php _e('Help', 'dxss'); ?></h3>
			<hr/>
			<div class="wrap">
				<p><?php _e('The format for adding a custom button to the widget is', 'dxss'); ?><br />
				<?php _e('<code>Name of the button</code>, <code>Share / Search URL</code>, <code>Icon URL</code>', 'dxss'); ?></p>
				
				<b><?php _e('Note:', 'dxss'); ?></b><br/>
				<ol>
				<li><?php _e('Use <code>%s</code> in the Share/Search URL to use the selected text.', 'dxss'); ?></li>
				<li><?php _e('Use <code>%ts</code> in the URL to use the truncated selected text (115 characters. Used in Twitter URL).', 'dxss'); ?></li>
				<li><?php _e('Use the text <code>favicon</code> in the Icon URL to automatically get the button icon.', 'dxss'); ?></li>
				<li><?php _e('Use <code>{url}</code> in the URL to get the current page url.', 'dxss'); ?></li>
				<li><?php _e('Use <code>{surl}</code> in the URL to get the shortened current page URL.', 'dxss'); ?></li>
				<li><?php _e('Use <code>{title}</code> in the URL to get the current page title.', 'dxss'); ?></li>
				</ol>
				<p class="smallText"><?php _e('Popular and recommended buttons are given in by default. Just select the button from the dropdown list. Above settings are required only if you want to add a custom button or change the template', 'dxss'); ?></p>
			</div>
		</div>
		
  <div class="lightBox wpsrBox bottomShadow">
			<?php if(function_exists('wp_socializer')): ?>
				<input type="button" class="closeLinks close button" value="Close" />
				<h3><?php _e('Insert more social buttons', 'dxss'); ?></h3>
				<small class="smallText"><?php _e('These buttons are taken from wp-socializer plugin. You can now use these buttons for wp-selected-text-searcher', 'dxss'); ?></small>
				<div class="listSearch"><input type="text" id="dxss_list_search" title="<?php _e('Search ...', 'dxss'); ?>" size="35"/>
				</div>
				<hr/>
				<div class="wrap">
					<ol class="dxss_wpsr_sites">
						<?php dxss_wpsr_get_links(); ?>
					</ol>
				</div>
			<?php else: ?>
				<input type="button" class="closeLinks close button" value="Close" />
				<h3><?php _e('Install WP Socializer plugin', 'dxss'); ?></h3>
				<hr />
				<p><?php _e('Sorry, you need to install <b>WP Socializer plugin</b> to get the additional social buttons links and data.', 'dxss'); ?></p>
				<p><?php _e('You can install the powerful WP Socializer plugin in one click securely by clicking the Install button below.', 'dxss'); ?></p>
				<?php
					 $nonce= wp_create_nonce ('dxss-nonce');
					 $installUrl = 'update.php?action=install-plugin&plugin=wp-socializer&_wpnonce=' . $nonce;
				?>
				<p align="center"><a href="<?php echo $installUrl; ?>" target="_blank" class="button-primary"><?php _e('Install Plugin', 'dxss'); ?></a></p>
				<b><?php _e('Note:', 'dxss'); ?></b><br />
	  <small class="smallText"><?php _e('DX share Selection requires to install WP Socializer to link the additional 98 buttons. <a href="http://www.aakashweb.com/wordpress-plugins/wp-socializer/" target="_blank">See here</a> for more info', 'dxss'); ?></small>
	<?php endif; ?>
	</div>
		
		
	</div>
<?php
}

?>
