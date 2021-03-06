<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.greentreelabs.net
 * @since      1.0.0
 *
 * @package    Everlightbox
 * @subpackage Everlightbox/admin
 */

require_once dirname( __FILE__ ) . "/../includes/class-everlightbox_admintab.php";

if ( ! class_exists( 'CMB2', false ) ) {
	if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
		require_once dirname( __FILE__ ) . '/cmb2/init.php';
	} elseif ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
		require_once dirname( __FILE__ ) . '/cmb2/init.php';
	}
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Everlightbox
 * @subpackage Everlightbox/admin
 * @author     GreenTreeLabs <diego@greentreelabs.net>
 */
class Everlightbox_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The metabox id
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $mbid    The metabox id
	 */
	private $metabox_id = 'everlightbox_options';

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $option_key = 'everlightbox_options';

	/**
	 * Option page
	 */
	private $options_page = null;

	/**
	 * Default values
	 */
	private $default_values = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $default_values ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->default_values = $default_values;
	}

	/**
	 * @since    1.0.0
	 */
	private function check_default_values()
	{
		$oldvalues = get_option('everlightbox_options');

		if(! $oldvalues)
		{
			add_option('everlightbox_options', $this->default_values);
		}

		$newvalues = get_option('everlightbox_options');

		$update = false;
		foreach($this->default_values as $k => $v) {
			if (!array_key_exists($k, $newvalues) && $v != 'on') {
				$newvalues[ $k ] = $v;
				$update = true;
			}
		}
		if($update)
			update_option('everlightbox_options', $newvalues);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/everlightbox-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/everlightbox-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Welcome page redirect.
	 *
	 * @since 1.1.3
	 */
	function welcome() {
		if (get_option('everlightbox_welcome' ) ) {	
			return;
		}
	
		update_option('everlightbox_welcome', true);
		
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {	
			return;
		}
		
		wp_safe_redirect( add_query_arg( array( 'page' => 'everlightbox_welcome_page' ), admin_url( 'admin.php' ) ) );
	
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		global $evebox_fs;

		register_setting( $this->option_key, $this->option_key );
		$this->check_default_values();		

		if($evebox_fs->is_free_plan()) {
			add_action( 'admin_bar_menu', function( $bar ) {
				$bar->add_menu( array(
					'id'     => 'wpse',
					'parent' => null,
					'group'  => null,
					'title'  => "<img title='Upgrade EverlightBox' class='wpbar-everlightbox' src='". plugin_dir_url( __FILE__ ) . "/icon-alert.png'>",
					'href'   => "admin.php?page=everlightbox_welcome_page"
				) );
			}, 100 );
		}			
	}


	/**
	 * Welcome page
	 *
	 * @since    1.1.3
	 */
	public function welcome_page()
	{
		global $evebox_fs; 
		include "partials/welcome.php";
	}

	/**
	 * Add menu
	 *
	 * @since    1.0.0
	 */
	public function menu() {

		$this->options_page = add_menu_page('Settings', 'EverlightBox', 'manage_options', $this->option_key, array($this, 'page_settings'), plugin_dir_url( __FILE__ ) . '/icon.png');
		
		add_submenu_page(
			$this->option_key, // The slug name for the parent menu (or the file name of a standard WordPress admin page).
			__( 'Welcome Page', 'everlightbox' ), // The text to be displayed in the title tags of the page when the menu is selected.
			__( 'Welcome Page', 'everlightbox' ), // The text to be used for the menu.
			'manage_options', // The capability required for this menu to be displayed to the user.
			'everlightbox_welcome_page', // The slug name to refer to this menu by (should be unique for this menu).
			array($this, "welcome_page") // The function to be called to output the content for this page.
		);

		do_action('everlightbox_menu');
	}

	/**
	 * Settings page
	 *
	 * @since    1.0.0
	 */
	public function page_settings()
	{
		global $evebox_fs;
		$tabs = new EverlightBox_AdminTab();
		$tabs->add("1", "Aspect");
		$tabs->add("2", "Features");
		$tabs->add("3", "Social");

		apply_filters('everlightbox_additional_tabs', $tabs);
		
		$tabs->add("galleries", "Galleries");		

		include "partials/everlightbox-admin-display.php";
	}

	/**
	 * Settings notices
	 *
	 * @since    1.0.0
	 */
	public function settings_notices( $object_id, $updated ) {
	
		if ( $object_id !== $this->option_key || empty( $updated ) ) {
			return;
		}
		add_settings_error( $this->option_key . '-notices', '', __( 'Settings updated.', 'everlightbox' ), 'updated' );
		settings_errors( $this->option_key . '-notices' );
	}

	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'option_key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		throw new Exception( 'Invalid property: ' . $field );
	}

	//TODO lang
	public function settings_form()
	{		
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$themes = array(
				'dark' => __( 'Dark night', 'cmb2' ),
				'white-splash' => __( 'White splash', 'cmb2' ),
			);
		$themes = apply_filters('everlightbox_theme_options', $themes);

		$cmb_options = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'title'   => __( 'EverlightBox settings', 'cmb2' ),
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->option_key )
			),
		) );

		$cmb_options->add_field( array(
			'name'     => __( 'Theme', 'cmb2' ),
			'desc'     => '',
			'id'       => 'theme',
			'type'     => 'select',
			'default'  => 'custom',	
			'row_classes' => 'el-tab-1',
			'options'          => $themes,
		) );

		$cmb_options->add_field( array(
			'name' => __('Anchor buttons to edges','everlightbox'),
			'desc' => '',
			'id'   => 'buttons_edges',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			'name'     => __( 'Image max width (%)', 'cmb2' ),
			'desc'     => '',
			'id'       => 'max_width',
			'type'     => 'text_small',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			 'name'     => __( 'Image max height (%)', 'cmb2' ),
			 'desc'     => '',
			 'id'       => 'max_height',
			 'type'     => 'text_small',
			 'row_classes' => 'el-tab-1'
		) );
		
		$cmb_options->add_field( array(
			'name' => __('Sticky buttons','everlightbox'),
			'desc' => __('Keep buttons (sharing, close, download) always visible','everlightbox'),
			'id'   => 'sticky_buttons',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-1'
		) );
		
		$cmb_options->add_field( array(
			'name' => __('Sticky caption','everlightbox'),
			'desc' => __('Keep caption always visible','everlightbox'),
			'id'   => 'sticky_caption',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			'name' => __('Round corners','everlightbox'),
			'desc' => '',
			'id'   => 'round_corners',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			'name' => __('Disable buttons background','everlightbox'),
			'desc' => __('Disable the background behind buttons','everlightbox'),
			'id'   => 'disable_buttons_background',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			'name' => __('Icon size','everlightbox'),
			'desc' => __('Size in pixel of the icons inside the buttons','everlightbox'),
			'id'   => 'icons_size',
			'type' => 'text_small',
			'default' => '24',
			'row_classes' => 'el-tab-1'
		) );

		$cmb_options->add_field( array(
			'name'    => __('Sharing','everlightbox'),
			'desc'    => __('Choose on which social networks users can share the photos','everlightbox'),
			'id'      => 'social',
			'type'    => 'multicheck',
			'select_all_button' => false,
			'options' => array(
				'facebook'      => 'Facebook',
				'twitter'       => 'Twitter',
				'pinterest'     => 'Pinterest',
				'houzz'         => 'Houzz',
				'googleplus'    => 'Google+',
				'tumblr'        => 'Tumblr'
			),
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => __('Don\'t include Facebook scripts','everlightbox'),
			'desc' => __('Check this box if your theme, or other plugins, already include Facebook scripts','everlightbox'),
			'id'   => 'no_facebook_scripts',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => 'Facebook App ID',
			'desc' => '<br><br>By default EverlightBox uses its own App ID.<br>It\'s highly recommended you add your own App ID: <a href="http://www.hyperarts.com/blog/how-to-create-facebook-application-to-get-an-app-id-for-your-website/" target="_blank">Read more on using your own Facebook App ID</a>',
			'id'   => 'facebook_app_id',
			'type' => 'text_small',
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => __('Enable Facebook comments','everlightbox'),
			'desc' => __('Let users comments your photos','everlightbox'),
			'id'   => 'facebook_comments',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => __('Show Facebook comment count','everlightbox'),
			'desc' => __('Show comment count for each image','everlightbox'),
			'id'   => 'facebook_comment_count',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => __('Enable Facebook like button','everlightbox'),
			'desc' => __('Let users like your photos','everlightbox'),
			'id'   => 'facebook_like',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-3'
		) );

		$cmb_options->add_field( array(
			'name' => __('Download button','everlightbox'),
			'desc' => __('Let users download the photo','everlightbox'),
			'id'   => 'download',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('Close with background click','everlightbox'),
			'desc' => __('Close lightbox by clicking the background','everlightbox'),
			'id'   => 'close_bg',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('Show fullscreen icon','everlightbox'),
			'desc' => __('Let users maximize the browser window','everlightbox'),
			'id'   => 'fullscreen_icon',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('Loop at end','everlightbox'),
			'desc' => __('Go back to the first image after the last one','everlightbox'),
			'id'   => 'loop',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );		

		$cmb_options->add_field( array(
			'name' => __('Disable keyboard navigation','everlightbox'),
			'desc' => '',
			'id'   => 'disable_keyb_nav',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('WordPress native linked images with captions','everlightbox'),
			'desc' => __('"Native linked images" are the images<br>you add through the WP Media Panel.<br>EverlightBox can recognize them only if<br>they have a caption, otherwise check the "All images" option','everlightbox'),
			'id'   => 'wp_images',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('WordPress native galleries','everlightbox'),
			'desc' => __('Enable the lightbox on all native galleries','everlightbox'),
			'id'   => 'wp_galleries',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );		

		$cmb_options->add_field( array(
			'name' => __('Custom CSS selector','everlightbox'),
			'desc' => __('Activate EverlightBox on images using your own CSS selector','everlightbox'),
			'id'   => 'custom_selector',
			'type' => 'text',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			'name' => __('All linked images','everlightbox'),
			'desc' => __('Activate EverlightBox on all linked images','everlightbox'),
			'id'   => 'all_links',
			'type' => 'checkbox',
			'row_classes' => 'el-tab-2'
		) );

		$cmb_options->add_field( array(
			 'name' => __('Custom CSS','everlightbox'),
			 'desc' => __('Customize the looking of the lightbox with your own CSS','everlightbox'),
			 'id'   => 'custom_css',
			 'type' => 'textarea',
			 'row_classes' => 'el-tab-2'
		 ) );

		apply_filters('everlightbox_additional_fields', $cmb_options);

		///TODO
		/*
		$cmb_options->add_field( array(
			'name' => 'Excluded images',
			'desc' => 'Add a CSS selector for the images to exclude',
			'id'   => 'custom_selector',
			'type' => 'text',
		) );
		*/
	}

	/**
	 * Register links
	 *
	 * @since    1.0.0
	 */
	public function register_links($links, $file) {

		$base = "everlightbox/everlightbox.php";
        if ( $file == $base ) {
            $links[] = '<a href="admin.php?page=ftg-lite-gallery-admin" title="Final Tiles Grid Gallery Dashboard">Dashboard</a>';
            $links[] = '<a href="https://www.machothemes.com/" title="MachoThemes website">MachoThemes</a>';
            $links[] = '<a href="https://twitter.com/machothemes" title="@MachoThemes on Twitter">Twitter</a>';
            $links[] = '<a href="https://www.facebook.com/machothemes" title="MachoThemes on Facebook">Facebook</a>';
        }
		return $links;
	}

}
