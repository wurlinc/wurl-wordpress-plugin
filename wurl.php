<?php
/*
 * Plugin Name: Wurl Publisher
 * Plugin URI: http://www.wurl.com
 * Description: The Wurl.com publisher widgets and interfrace.
 * Version: 1.0
 * Author: David Martinez and the Wurl Team
 * Author URI: http://www.wurl.com
 * License: GPL
 * */

add_action('init','wurl_init');
add_action('widgets_init', 'register_widgets' );
add_action('wp_enqueue_scripts', 'enqueue_sdk_scripts');
/* add_action( 'wp_print_scripts', 'print_sdk_scripts', 1); */

function wurl_init() {
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  $src = $protocol."//wrl.it/widgets/all.js";
  wp_register_script( 'wurl_sdk_all', $src, '', '', true); # In the footer
}

function enqueue_sdk_scripts() {
  wp_enqueue_script( 'wurl_sdk_all' );
}

/* function print_sdk_scripts() {} */

function register_widgets() {
  register_widget( 'WurlFeedWidget' );
}

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'wurl_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'wurl_remove' );

function wurl_install() {
  add_option("wurl_publisher_id", '', '', 'yes');
}

function wurl_remove() {
  delete_option('wurl_publisher_id');
}

class WurlFeedWidget extends WP_Widget {

	function WurlFeedWidget() {
		$widget_ops = array( 'classname' => 'wurl_feed', 'description' => __('Displays the wurl feed', 'wurl_feed') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wurl-feed' );
		
		$this->WP_Widget( 'wurl-feed', __('Wurl Widget', 'wurl_feed'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$site_name = apply_filters('widget_title', $instance['site_name'] );
		$site_url  = apply_filters('widget_title', $instance['site_url'] );
    $theme     = apply_filters('widget_title', $instance['theme'] );
    $plugin_id = apply_filters('widget_title', $instance['plugin_id'] );

		echo $before_widget;

/*
		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;
*/

		//Display the name 
    print( '<wurl:feed');
    if ($site_name) { printf(' data-site_name="%s"', $site_name); }
    if ($site_url)  { printf(' data-site_url="%s"', $site_url);   }
    if ($theme)     { printf(' data-theme="%s"', $theme);         }
    if ($theme)     { printf(' data-plugin_id="%s"', $plugin_id);         }
    printf( '></wurl:feed>');
		
		echo $after_widget;
	}

	function form( $instance ) {

		//Set up some default widget settings.
		
		$defaults = array( 
		  'site_name' => __('Wurl Feed', 'wurl_feed'), 
		  'site_url'  => __(get_site_url(), 'site_url'),
      'theme'  => __("", 'theme'),
      'plugin_id' => __("", 'plugin_id')
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

<p>See <a target="_sdk" href="http://wrl.it/sdkdocs/widgets/feed">docs</a></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'site_name' ); ?>"><?php _e('Site Name:', 'site_name'); ?></label>
			<input id="<?php echo $this->get_field_id( 'site_name' ); ?>" name="<?php echo $this->get_field_name( 'site_name' ); ?>" value="<?php echo $instance['site_name']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'site_url' ); ?>"><?php _e('Site URL:', 'site_url'); ?></label>
			<input id="<?php echo $this->get_field_id( 'site_url' ); ?>" name="<?php echo $this->get_field_name( 'site_url' ); ?>" value="<?php echo $instance['site_url']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'plugin_id' ); ?>"><?php _e('Plugin ID:', 'plugin_id'); ?></label>
			<input id="<?php echo $this->get_field_id( 'plugin_id' ); ?>" name="<?php echo $this->get_field_name( 'plugin_id' ); ?>" value="<?php echo $instance['plugin_id']; ?>" style="width:100%;" />
    </p>

		<p>
			<label for="<?php echo $this->get_field_id( 'theme' ); ?>"><?php _e('Theme (e.g. dark):', 'theme'); ?></label>
			<input id="<?php echo $this->get_field_id( 'theme' ); ?>" name="<?php echo $this->get_field_name( 'theme' ); ?>" value="<?php echo $instance['theme']; ?>" style="width:100%;" />
		</p>

	<?php
	}
	
	// Update the widget settings
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['site_name'] = strip_tags( $new_instance['site_name'] );
		$instance['site_url'] = strip_tags( $new_instance['site_url'] );
		$instance['theme'] = $new_instance['theme'];
		$instance['plugin_id'] = $new_instance['plugin_id'];

		return $instance;
	}

	
}


if ( is_admin() ){

  /* Call the html code */
  add_action('admin_menu', 'wurl_admin_menu');

  function wurl_admin_menu() {
    add_options_page('Wurl Publisher', 'Wurl Publisher', 'administrator',
      'wurl', 'wurl_html_page');
  }
}
function wurl_html_page() {
?>
<div>
<h2>Wurl Publisher Options</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table width="510">
  <tr valign="top">
  <th width="92" scope="row">Publisher ID</th>
  <td width="406">
<input name="wurl_publisher_id" type="text" id="wurl_publisher_id"
value="<?php echo get_option('wurl_publisher_id'); ?>" />
(as provided to you by Wurl)</td>
</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="wurl_publisher_id" />

<p>
<input type="submit" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>
<?php
}
?>
