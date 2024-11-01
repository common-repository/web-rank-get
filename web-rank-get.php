<?php
/*
Plugin Name: Web Rank Get
Plugin URI: http://www.hostinginfo360.com/web-rank-get/
Description: The Web Rank Get plugin will retrieve your website's Google Page Rank and Alexa Rank and display it in the footer.
Version: 1.0
Author: Hosting Info 360
Author URI: http://www.hostinginfo360.com/
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$ver= '1.0';

require_once('google_pagerank.class.php');
require_once('alexa.class.php');

register_activation_hook( __FILE__, 'webrankget_on_activate');
register_deactivation_hook( __FILE__, 'webrankget_on_deactivate');
register_uninstall_hook( __FILE__, 'webrankget_on_uninstall');
add_action( 'admin_init', 'webrankget_init' );
add_action('admin_menu', 'webrankget_setup_menu');
add_filter('plugin_action_links', 'webrankget_add_action_link', 10, 2 );
add_action( 'admin_print_styles', 'webrankget_styles' );
add_action( 'admin_print_scripts', 'webrankget_scripts' );

function webrankget_on_activate() {
	$options = get_option('web_rank_get_option');
	$options['web_rank_get_gpr_footer'] = "Enable";
	$options['web_rank_get_ar_footer'] = "Enable";
	update_option('web_rank_get_option', $options);
}

function webrankget_on_deactivate() {
	$options = get_option('web_rank_get_option');
	$options['web_rank_get_gpr_footer'] = "Disable";
	$options['web_rank_get_ar_footer'] = "Disable";
	update_option('web_rank_get_option', $options);
}

function webrankget_on_uninstall() {
	delete_option('web_rank_get_option');
}

function webrankget_setup_menu() {
	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) return;
	} else {
		$current_user = wp_get_current_user();
                if ($current_user->user_level < 8) return;	}
	if (function_exists('add_options_page')) {
		add_options_page(__('Web Rank Get'), __('Web Rank Get'), 1, __FILE__, 'webrankget_setup_page');
	}
} 

function webrankget_add_action_link( $links, $file ) {
   static $this_plugin;
   if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
   if( $file == $this_plugin ){
	$settings_link = '<a href="' . webrankget_plugin_options_url() . '">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
   }
   return $links;
}
function webrankget_plugin_options_url() {
        return admin_url( 'options-general.php?page=web-rank-get/web-rank-get.php');
}

function webrankget_styles() {
	wp_enqueue_style('dashboard');
	wp_enqueue_style('thickbox');
	wp_enqueue_style('global');
	wp_enqueue_style('wp-admin');
	wp_enqueue_style( 'web-rank-get-stylesheet' );
}

function webrankget_scripts() {
	wp_enqueue_script('postbox');
	wp_enqueue_script('dashboard');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('media-upload');
}

function webrankget_init() {
	wp_register_style( 'web-rank-get-stylesheet', plugins_url('web-rank-get.css', __FILE__) );
}

function postbox($id, $title, $content) {
?>
	<div id="<?php echo $id; ?>" class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div>
		<h3 class="hndle"><span><?php echo $title; ?></span></h3>
		<div class="inside">
			<?php echo $content; ?>
		</div>
	</div>
<?php
}	

function webrankget_setup_page_content($options) {
	$gprFooterDisableSelected = '';
	$gprFooterEnableSelected = '';
	if($options['web_rank_get_gpr_footer']=='Disable') { $gprFooterDisableSelected = 'selected="selected"';}
	if($options['web_rank_get_gpr_footer']=='Enable') { $gprFooterEnableSelected = 'selected="selected"';}
	$arFooterDisableSelected = '';
	$arFooterEnableSelected = '';
	if($options['web_rank_get_ar_footer']=='Disable') { $arFooterDisableSelected = 'selected="selected"';}
	if($options['web_rank_get_ar_footer']=='Enable') { $arFooterEnableSelected = 'selected="selected"';}
	if(!isset($options['web_rank_get_text_color'])) { $options['web_rank_get_text_color'] = '#000000';}
	if(!isset($options['web_rank_get_text_size'])) { $options['web_rank_get_text_size'] = '14px';}
	if(!isset($options['web_rank_get_text_family'])) { $options['web_rank_get_text_family'] = "inherit";}
	$content = '<form method="post" action="">
		
		<table class="form-table">
			<tr>
				<th scope="row">'. __("Show Google Page Rank in Footer") .'</th>
				<td>
				<select name="web_rank_get_gpr_footer">
				<option value="Disable" '. $gprFooterDisableSelected.'>Disable</option>
				<option value="Enable" '. $gprFooterEnableSelected.'>Enable</option>
				</select>
				</td>
			</tr>
		
			<tr>
				<th scope="row">'. __("Show Alexa Rank in Footer") .'</th>
				<td>
				<select name="web_rank_get_ar_footer">
				<option value="Disable" '. $arFooterDisableSelected.'>Disable</option>
				<option value="Enable" '. $arFooterEnableSelected.'>Enable</option>
				</select>
				</td>
			</tr>
			<tr>
				<th scope="row">'. __("Text font color") .'</th>
				<td>
					<input type="text" name="web_rank_get_text_color" value="'.$options['web_rank_get_text_color'].'"/>
				</td>
			</tr>
			<tr>
				<th scope="row">'. __("Text font size") .'</th>
				<td>
					<input type="text" name="web_rank_get_text_size" value="'.$options['web_rank_get_text_size'].'"/>
				</td>
			</tr>
			<tr>
				<th scope="row">'. __("Text font family") .'</th>
				<td>
					<input type="text" size="50" name="web_rank_get_text_family" value="'.$options['web_rank_get_text_family'].'"/>
				</td>
			</tr>
			<tr>
				<th scope="row">'. __("Footer content") .'</th>
				<td>
					'.webRankGetFooterContent().'
				</td>
			</tr>
		</table>
	
		<div class="submit">
		<input type="submit" name="update" value="'. __("Update") .'"  style="font-weight:bold;" />
		</div>
		</form>';
	return $content;
}

function webrankget_setup_page(){
	$options = get_option('web_rank_get_option');
	if (isset($_POST['update'])) {
		$options['web_rank_get_gpr_footer']=$_POST['web_rank_get_gpr_footer'];
		$options['web_rank_get_ar_footer']=$_POST['web_rank_get_ar_footer'];
		$options['web_rank_get_text_color']=$_POST['web_rank_get_text_color'];
		$options['web_rank_get_text_size']=$_POST['web_rank_get_text_size'];
		$options['web_rank_get_text_family']=$_POST['web_rank_get_text_family'];
		update_option('web_rank_get_option', $options);
		echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Saved settings</p></div>\n";
		echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
	}
	?>
		<div class="wrap">
		<h2><?php echo __('Web Rank Get'); ?></h2>
		<div class="postbox-container" style="width:65%;">
		<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
		<?php
			postbox('webrankget_settings',__('Settings'), webrankget_setup_page_content($options));
		?>
		</div>
		</div>
		</div>

		</div>
	<?php	
}

function webRankGetFooterContent() {
	$options = get_option('web_rank_get_option');
	$url = get_bloginfo('siteurl');
	$text_color = $options['web_rank_get_text_color'];
	if(!isset($text_color) or is_null($text_color)) {
		$text_color = "#000000";
	}
	$text_size = $options['web_rank_get_text_size'];
	if(!isset($text_size) or is_null($text_size)) {
		$text_size= "14px";
	}
	$text_family = $options['web_rank_get_text_family'];
	if(!isset($text_family) or is_null($text_family)) {
		$text_family= "inherit";
	}
	if($options['web_rank_get_gpr_footer'] == 'Enable') {
	  $gpr = new GooglePageRank($url);
	  $rankOutput[] = '<div style="text-align: center; margin: 5px; width: 150px; display: inline;">Google PR: '.$gpr->pagerank.'</div>';
	}
	if($options['web_rank_get_ar_footer'] == 'Enable') {
	  $ar = new AlexaRank($url);
          $rankOutput[] = '<div style="text-align: center; margin: 5px; width: 150px; display: inline;">Alexa Rank: '.$ar->rank.'</div>';
	}
	if(count($rankOutput)>0) {
	  $output = "";
	  $total = count($rankOutput);
	  $counter = 1;
	  foreach ($rankOutput as $key => $value) {
		if($counter < $total) {
			$output .= $value . "&nbsp;&middot;&nbsp;";
		} else {
			$output .= $value;
		}
		$counter++;
	  }

  	  $output = '<div style="text-align: center; margin: auto; margin-bottom: 10px; width: 400px; font-family: '.$text_family.'; font-size: '.$text_size.'; color: '.$text_color.';" id="webrankget">'.$output.'</div>';
  	    //$output = '<div style="text-align: left; margin: 0px; margin-bottom: 10px; width: 400px; font-family: '.$text_family.'; font-size: '.$text_size.'; color: '.$text_color.';" id="webrankget">'.$output.'</div>';

	  return $output;
	}
}

function webRankGetFooterPrint() {
	echo webRankGetFooterContent();
}

function webRankGetAddActionFooter() {
	$options = get_option('web_rank_get_option');
	if($options['web_rank_get_gpr_footer']=='Enable' or $options['web_rank_get_ar_footer']=='Enable') {
		add_action('wp_footer', 'webRankGetFooterPrint');
	}
}
webRankGetAddActionFooter();
?>
