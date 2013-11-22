<?php

class DXSS_Option_Helper {
	private static $dxss_settings_data;
	
	private static $option_key = 'dxss_settings_data';
	
	public static function fetch_settings_data() {
		// Return if already set
		if ( ! empty( self::$dxss_settings_data ) ) {
			return self::$dxss_settings_data;
		}
		
		// Fetch from DB
		self::$dxss_settings_data = get_option( self::$option_key );
		
		if ( empty( self::$dxss_settings_data ) ) {
			$searchUrl = get_bloginfo('url') . '/?s=%s';
			
			self::$dxss_settings_data = array(
					'title' => __('Share this text ...', 'dxss'),
					'lists' => "Search, $searchUrl, favicon\nTweet this, http://twitter.com/home?status=%ts {url}, favicon",
					'borderColor' => '#444',
					'bgColor' => '#fff',
					'titleColor' => '#f2f2f2',
					'hoverColor' => '#ffffcc',
					'textColor' => '#000',
					'extraClass' => '',
					'grepElement' => '',
					'element' => 'body',
					'scriptPlace' => '1',
					'truncateChars' => '115',
					'bitly' => ''
			);
		}
		
		return self::$dxss_settings_data;
	}
	
	public static function update_settings_data( $data ) {
		update_option( self::$option_key, $data );
		
		self::$dxss_settings_data = $data;
	}
}