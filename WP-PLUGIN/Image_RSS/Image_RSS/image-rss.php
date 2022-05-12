<?php
/*
Plugin Name: Image rss plugin
Plugin URI: http://studio76.ru
Description: This plugin will add post thumbnail to RSS feed items. 
Author: Studio76.ru
Author URI: http://studio76.ru/
Author Email: info@studio76.ru
License:

  Copyright 2013 Studio76.ru

 
*/

class Image_RSS {
	private $plugin_path;
    private $wpsf;
	private $CFG;
	public $cfg_version = '1.1.1';
	private $update_warning = false;
	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		
		// Load plugin text domain
		load_plugin_textdomain( 'Image_RSS', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		/* admin options */
		require_once( $this->plugin_path .'wp-settings-framework.php' );
        $this->wpsf = new WordPressSettingsFramework( $this->plugin_path .'settings/imagerss-cfg.php' );
		
		/* load CFG */
		$this->CFG = wpsf_get_settings( $this->plugin_path .'settings/imagerss-cfg.php' );  // print_r($this->CFG);
		
		if ( $this->CFG['sbrssfeedcfg_info_version'] !== $this->cfg_version ) {
			// SET defaults, mark this version as current
			if (!isset($this->CFG['sbrssfeedcfg_tags_addTag_enclosure'])) $this->CFG['sbrssfeedcfg_tags_addTag_enclosure'] = 1;
			if (!isset($this->CFG['sbrssfeedcfg_tags_addTag_mediaContent'])) $this->CFG['sbrssfeedcfg_tags_addTag_mediaContent'] = 1;
			if (!isset($this->CFG['sbrssfeedcfg_description_extend_description'])) $this->CFG['sbrssfeedcfg_description_extend_description'] = 1;
			if (!isset($this->CFG['sbrssfeedcfg_description_extend_content'])) $this->CFG['sbrssfeedcfg_description_extend_content'] = 1;
			if (!isset($this->CFG['sbrssfeedcfg_signature_addSignature'])) $this->CFG['sbrssfeedcfg_signature_addSignature'] = 0;
			if (!isset($this->CFG['sbrssfeedcfg_fulltext_fulltext_override'])) $this->CFG['sbrssfeedcfg_fulltext_fulltext_override'] = 0;
			
			$this->update_warning = false;
			//add_action( 'admin_notices', array( $this, "addAdminAlert" ) );
		}
		add_action( 'wpsf_before_settings_fields', array( $this, 'update_current_version' ) );
		
		
		// add admin menu item
		add_action( 'admin_menu', array(&$this, 'admin_menu') );
		
		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
		
		// add_action( "rss2_ns", array( $this, "feed_addNameSpace") );
		add_action( "rss_item", array( $this, "feed_addMeta" ), 5, 1 );
		add_action( "rss2_item", array( $this, "feed_addMeta" ), 5, 1 );
		
		if ( $this->CFG['sbrssfeedcfg_description_extend_description'] == 1 )
			add_filter('the_excerpt_rss', array( $this, "feed_update_content") );
		
		if ( $this->CFG['sbrssfeedcfg_description_extend_content'] == 1 )
			add_filter('the_content_feed', array ( $this, "feed_update_content") );
		
		if ( $this->CFG['sbrssfeedcfg_inrssAd_inrssAd_enabled'] == 1 )
			add_filter('the_content_feed', array ( $this, "feed_update_content_injectAd") );
		
		if ( $this->CFG['sbrssfeedcfg_fulltext_fulltext_override'] == 1 )
			$this->fulltext_override();
		
	} // end constructor
	
	public function addAdminAlert() {
		if ( current_user_can( 'install_plugins' ) ) { ?>
		<div class="updated">
			<p>
				<?php _e( '<b>Image RSS Warning</b>: Settings needs to be updated...', 'Image_RSS' ); ?>
				&nbsp;&nbsp;
				<a href="options-general.php?page=image_rss" class="button"><?php _e( 'Update settings', 'Image_RSS' ); ?></a>
			</p>
		</div>
		<?php }
	}
	
	public function update_current_version() {
		echo '<input type="hidden" name="sbrssfeedcfg_settings[sbrssfeedcfg_info_version]" id="sbrssfeedcfg_info_version" value="'.$this->cfg_version.'" />';
	}
	
	
	
	public function admin_menu()
    {
		if ( $this->update_warning === true ) {
			$menu_label = __( 'Image RSS +', 'Image_RSS' ) . "<span class='update-plugins count-1' title=''><span class='update-count'>!</span></span>";
		} else {
			$menu_label = __( 'Image RSS +', 'Image_RSS' );
		}
		add_submenu_page( 'options-general.php', __( 'Image RSS', 'Image_RSS' ), $menu_label, 'update_core', 'image_rss', array(&$this, 'settings_page') );
    }
	
	public function settings_page() { ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e( 'Image RSS - Settings', 'Image_RSS' ); ?></h2>
            <?php 
            // Output your settings form
            $this->wpsf->settings(); 
            ?>
        </div>
        
	<?php }
    
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function activate( $network_wide ) {
		
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {
		
	} // end deactivate
	
	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function uninstall( $network_wide ) {
		
	} // end uninstall
	
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	
	public function feed_getImage() {
		global $post;
		$image = false;
		$size = null;
		
		if( function_exists ('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if(!empty($thumbnail_id)) {
				$image = wp_get_attachment_image_src( $thumbnail_id, $size );
				$image[4] = @filesize( get_attached_file( $thumbnail_id ) ); // add file size
			}
		}
		
		return ($image);
	}
	
	public function feed_addNameSpace() {
		echo 'xmlns:media="http://search.yahoo.com/mrss/"';
	}
	
	public function feed_addMeta($for_comments) {
		global $post;
		
		if(!$for_comments) {
			$image = $this->feed_getImage();
			if ($image !== false) {
				
				if ( $this->CFG['sbrssfeedcfg_tags_addTag_enclosure'] == 1 ) {
					echo '<imgsrss url="' . $image[0] . '" length="' . $image[4] . '" type="image/jpg" />' . "\n";
				}
				
				if ( $this->CFG['sbrssfeedcfg_tags_addTag_mediaContent'] == 1 ) {
					echo '<media:thumbnail xmlns:media="http://search.yahoo.com/mrss/" url="'. $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '"/>' . "\n";
					echo '<media:content xmlns:media="http://search.yahoo.com/mrss/" url="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" medium="image" type="image/jpeg">' . "\n";
					echo '<media:copyright>' . get_bloginfo( 'name' ) . '</media:copyright>' . "\n";
					echo '</media:content>' . "\n";
				}
				
			}
		}
	}
	
	public function feed_update_content($content) {
		global $post;
		
		$content_new = '';
		
		if(has_post_thumbnail($post->ID)) {
			$image = $this->feed_getImage();
			$content_new .= '<div style="margin: 5px 5% 10px 5%;"><img src="' . $image[0] . '" width="90%" /></div>';
		}
		
		$content_new .= '<div>' . $content . '</div>';
		
		if ( $this->CFG['sbrssfeedcfg_signature_addSignature'] == 1 ) {
			$content_new .= '<div>&nbsp;</div><div><em>';
			$content_new .=  __( 'Source: ', 'Image_RSS' );
			$content_new .= '<a href="' . get_permalink($post->ID) . '" target="_blank">' . get_bloginfo( 'name' ) . '</a>';
			$content_new .= '</em></div>';
		}
		
		return $content_new;
	}
	
	public function feed_update_content_injectAd( $content ) {
		global $post;
		$content_ad = '';
		$content_new = '';
		
		$split_after = $this->CFG['sbrssfeedcfg_inrssAd_inrssAd_injectAfter'];
		if ( ($split_after < 1) || ($split_after > 8) ) $split_after = 2;
		
		$content_ad .= '<br/><div style="margin: 10px 5%; text-align: center;">';
		$content_ad .= '<em style="display: block; text-align: right;">' . __( 'advertisement: ', 'Image_RSS' ) . '</em><br/>';
		$content_ad .= '<a href="' . $this->CFG['sbrssfeedcfg_inrssAd_inrssAd_link'] . '" target="_blank" style="text-decoration: none;">';
		$content_ad .= '<img src="' . $this->CFG['sbrssfeedcfg_inrssAd_inrssAd_img'] . '" width="90%" style="width: 90%; max-width: 700px;" />';
		$content_ad .= '<br/><em style="display: block; text-align: center;">' . $this->CFG['sbrssfeedcfg_inrssAd_inrssAd_title'] . '</em>';
		$content_ad .= '</a>';
		$content_ad .= '</div><br/>';
		
		$tmp = $content;
		$tmp = str_replace('</p>', '', $tmp); // drop all </p> - we don't need them ;)
		$array = explode('<p>', $tmp); // split by <p> tag
		$tmp = '';
		$max = sizeof( $array );
		
		if ($max > ( $split_after + 1 )) {
			// add after nth <p>
			for ($loop=0; $loop<( $split_after + 1 ); $loop++) {
				$content_new .= '<p>' . $array[$loop] . '</p>';
			}
			$content_new .= $content_ad;
			for ($loop=( $split_after + 1 ); $loop<( $max + 1 ); $loop++) {
				$content_new .= '<p>' . $array[$loop] . '</p>';
			}
		} else {
			// add to end of post...
			$content_new = $content;
			$content_new .= $content_ad;
		}
		
		return $content_new;
	}
	
	public function fulltext_override() {
		$secret = $this->CFG['sbrssfeedcfg_fulltext_fulltext_override_secrete'];
		$passed_secret = $_GET['fsk'];
		
		if ( $secret == $passed_secret ) {
			add_filter('pre_option_rss_use_excerpt', array( $this, 'fulltext_override_filter' ) );
		}
	}
	public function fulltext_override_filter() {
		return 0;
	}
} // end class

$Image_RSS = new Image_RSS();