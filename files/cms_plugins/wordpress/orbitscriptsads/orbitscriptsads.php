<?php
/**
 * @package orbitscriptsads
 */
/*
Plugin Name: <%SITE_NAME%> Ads
Plugin URI: <%BASE_URL%>
Description: You CMS system will communicate with <%SITE_NAME%> thru API, so always will have updated information about channels, their positions and color scheme.
Version: 1.0.0
Author: OrbitScripts LLC
Author URI: http://orbitscripts.com/
License: GNU
*/

define('ORBITSCRIPTSADS_VERSION', '1.0.0');
define('ORBITSCRIPTSADS_SITE_NAME','<%SITE_NAME%>');
define('ORBITSCRIPTSADS_DEFAULT_HOST','<%BASE_URL%>');
define('ORBITSCRIPTSADS_DEFAULT_APIKEY','<%API_KEY%>');
define('ORBITSCRIPTSADS_DEFAULT_SITE_ID','<%SITE_ID%>');

function orbitscriptsads_init() {
	global $orbitscriptsads_api_key, $orbitscriptsads_api_host, $orbitscriptsads_site_id, $orbitscriptsads_channels, $orbitscriptsads_palettes;

	$orbitscriptsads_api_host = get_option('orbitscriptsads_api_host');
	$orbitscriptsads_api_key = get_option('orbitscriptsads_api_key');
	
	if (get_option('orbitscriptsads_edited')!='1') {
	    update_option('orbitscriptsads_api_host', ORBITSCRIPTSADS_DEFAULT_HOST);
	    update_option('orbitscriptsads_api_key', ORBITSCRIPTSADS_DEFAULT_APIKEY);
	    update_option('orbitscriptsads_edited', '1');
	    update_option('orbitscriptsads_site_id', ORBITSCRIPTSADS_DEFAULT_SITE_ID);
	    $api = new OrbitAdsApi;
		$api->testconnect();
	}

	add_action('admin_menu', 'orbitscriptsads_config_page');
	orbitscriptsads_admin_warnings();
}

add_action('init', 'orbitscriptsads_init');

if ( !function_exists('wp_nonce_field') ) {
	function orbitscriptsads_nonce_field($action = -1) { return; }
	$orbitscriptsads_nonce = -1;
} else {
	function orbitscriptsads_nonce_field($action = -1) { return wp_nonce_field($action); }
	$orbitscriptsads_nonce = 'orbitscriptsads-update-key';
}

if ( !function_exists('number_format_i18n') ) {
	function number_format_i18n( $number, $decimals = null ) { return number_format( $number, $decimals ); }
}

function orbitscriptsads_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __(ORBITSCRIPTSADS_SITE_NAME.' Config'), __(ORBITSCRIPTSADS_SITE_NAME.' Config'), 'manage_options', 'orbitscriptsads-key-config', 'orbitscriptsads_conf');

}

function orbitscriptsads_conf() {
	global $orbitscriptsads_api_key, $orbitscriptsads_api_host, $orbitscriptsads_site_id;
	$api = new OrbitAdsApi;
	$api->testconnect();	
	
	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

			update_option('orbitscriptsads_api_key', $_POST['api_key']);
			update_option('orbitscriptsads_api_host', $_POST['api_host']);
			update_option('orbitscriptsads_site_id', $_POST['site_id']);
		    $api->testconnect();
		
	} elseif (isset($_POST['check'])) {
		$api->testconnect();
	}
	
	$errors = get_option('orbitscriptsads_api_errors');

	if ( !empty($_POST['submit'] ) ) : ?>

		<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
	<?php endif; ?>

<div class="wrap">
	<h2><?php _e(ORBITSCRIPTSADS_SITE_NAME.' Configuration'); ?></h2>
		<div class="narrow">
			<form action="" method="post" id="orbitscriptsads-conf" style="margin: auto; width: 400px; ">
					<p>To access the API you need a key. You can get it on <?php echo ORBITSCRIPTSADS_DEFAULT_HOST; ?></p>
					<h3><label for="api_key"><?php _e('API Key'); ?></label></h3>
					<p><input id="api_key" name="api_key" type="text" size="15" maxlength="32" value="<?php echo get_option('orbitscriptsads_api_key'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /></p>

					<p>You must set the base url of <?php echo ORBITSCRIPTSADS_SITE_NAME; ?></p>
					<h3><label for="api_host"><?php _e('API Host'); ?></label></h3>
					<p><input id="api_host" name="api_host" type="text" size="15" value="<?php echo get_option('orbitscriptsads_api_host'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /></p>

					<p>If you want to see chsnnels only for 1 site, please, specify this parametr.</p>
					<h3><label for="site_id"><?php _e('Site id'); ?></label></h3>
					<p><input id="site_id" name="site_id" type="text" size="15" maxlength="12" value="<?php echo get_option('orbitscriptsads_site_id'); ?>" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /></p>
				
				<p class="submit"><input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
			</form>

			<form action="" method="post" id="orbitscriptsads-connectivity" style="margin: auto; width: 400px; ">
				<h3><?php _e('API connect status:'); ?></h3>
			<?php 	if (($errors['code'] === '0')) { ?>
				<p style="padding: .5em; background-color: #2d2; color: #fff; font-weight:bold;"><?php  _e('Connection to '.ORBITSCRIPTSADS_SITE_NAME.' thru API established. You can create new widgets.)'); ?></p>				
		  	<?php } else { ?>
				<p style="padding: .5em; background-color: #d22; color: #fff; font-weight:bold;"><?php _e('Unable to load API data. The following error was get:'); echo '<br /><b>'.$errors['code'].' - '.$errors['msg'].'</b>';?></p>
		  	<?php } ?>
		  	<p class="submit"><input type="submit" name="check" value="<?php _e('Update status &raquo;'); ?>" /></p>
</form>

</div>
</div>
<?php
}

function orbitscriptsads_admin_warnings() {
	global $wpcom_api_key;
	$errors = get_option('orbitscriptsads_api_errors');
	if ($errors['code']!=='0' && !isset($_POST['submit']) ) {
		function orbitscriptsads_warning() {
			echo "
			<div id='orbitscriptsads-warning' class='updated fade'><p><strong>".__(ORBITSCRIPTSADS_SITE_NAME.' cannot get access via API.')."</strong> ".sprintf(__('You must <a href="%1$s">check setting</a> on config page.'), "plugins.php?page=orbitscriptsads-key-config")."</p></div>
			";
		}
		add_action('admin_notices', 'orbitscriptsads_warning');
		return;
	} 
}

/*
 * 
 * Widget Functions
 * 
 */

class OrbitAdsWidget extends WP_Widget {
	function OrbitAdsWidget() {
		parent::WP_Widget(false, ORBITSCRIPTSADS_SITE_NAME.' Ads');
	}

	function widget($args, $instance) {
		extract( $args );
		$settings = get_option('orbitscriptsads_channel_settings_'.$instance['channel']);
		echo $before_title.$instance['title'].
		"<script type=\"text/javascript\">
 			var sppc_site      = '".$settings['site']."';
    		var sppc_channel   = '".$instance['channel']."';
    		var sppc_dimension = '".$settings['dimension']."';
    		var sppc_width     = '".$settings['width']."';
    		var sppc_height    = '".$settings['height']."';
    		var sppc_palette   = '".$instance['palette']."';
    		var sppc_user      = '".$settings['user']."';
		</script>
		<script type=\"text/javascript\" src=\"".$settings['baseurl']."/show.js\"></script>"
		
		.$after_title;
	 }

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function form($instance) {
		$api = new OrbitAdsApi;
		$api->testconnect();
		
		$errors = get_option('orbitscriptsads_api_errors');

		if ($errors['code']==='0') {
			
		$title = esc_attr($instance['title']);
		$palette = esc_attr($instance['palette']);
		$channelid = esc_attr($instance['channel']);
		
		if (isset($channelid)) {
		$sites = get_option('orbitscriptsads_api_channels');
		$userid = $sites['userid'];
		unset($sites['userid']);
		foreach ($sites as $idsite=>$channels) {
        	foreach ($channels['channels'] as $id=>$channel) {
				if ($id == $channelid) {
					$keys = array (
        				'site'=>$idsite,
            			'channel'=>$id,
            			'palette'=>$post['palette'],
            			'user'=>$userid,
            			'width'=>$channel['width'],
            			'height'=>$channel['height'],
            			'dimension'=>$channel['dimension'],
						'baseurl' => $channel['siteurl']
       				 );
				}
        	}
        }
        
        update_option('orbitscriptsads_channel_settings_'.$channelid, $keys);
		}
		$api = new OrbitAdsApi;
		
		?>
		<p><label for="orbitscriptsads-title"><?php _e('Title:'); ?> <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" type="text" value="<?php echo $title; ?>" /></label></p>
		<p><label for="orbitscriptsads-channel"><?php _e('Channel:'); ?> <select id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" class="widefat" type="text" value="<?php echo $channel; ?>"><?php echo $api->execute('getChannelsHtml', $instance['channel']);?> </select></label></p>
		<p><label for="orbitscriptsads-palette"><?php _e('Palette:'); ?> <select id="<?php echo $this->get_field_id('palette'); ?>" name="<?php echo $this->get_field_name('palette'); ?>" class="widefat" type="text" value="<?php echo $palette; ?>"><?php echo $api->execute('getPalettesHtml', $instance['palette']);?> </select></label></p>
		<?php 
		} else {
		echo '<p><strong>'.__(ORBITSCRIPTSADS_SITE_NAME.' cannot get access via API.').
		'</strong> '.__('You must <a href="plugins.php?page=orbitscriptsads-key-config">check setting</a> on config page.</p>');
		}
	}
}

add_action('widgets_init', create_function('', 'return register_widget("OrbitAdsWidget");'));

/*
 * 
 * API Functions
 * 
 */

class OrbitAdsApi {
	function execute($task = '', $default = '') {
		if (empty($task)) $task=$_GET['task'];
		switch ($task) {
			case 'getChannelsHtml':
				if (!$this->getChannelsHtml($default)) return false;
				break;
			case 'getPalettesHtml':
				if (!$this->getPalettesHtml($default)) return false;
				break;
		}
	update_option('orbitscriptsads_api_errors', $this->errors);
	}
	
	function redirect() {
		exit;
	}
	
	function testconnect() {
		$data=$this->request('get_sites_channels');
        update_option('orbitscriptsads_api_errors', $this->errors);
		return $this->errors; 
	}
	
	function getChannelsHtml($default = '') {
		$data=$this->request('get_sites_channels');
		if (!$data) return false;
		
		update_option('orbitscriptsads_api_channels', $data);
		
		$response = '';
        foreach ($data as $siteId => $site) {
        	foreach ($site['channels'] as $channelId => $channel) {
        		if ($default ==$channelId) {
        			$response .='<option value="'.$channelId.'" selected>'.$channel['name'].'</option>';
        		} else {
        			$response .='<option value="'.$channelId.'">'.$channel['name'].'</option>';
        		}
        	}
        }
		echo $response;
		return true;
	}
	
	function getPalettesHtml($default = '') {
        $data=$this->request('get_palettes');
        if (!$data) return false;
        $this->data['palettes']=$data;
		$response = '';
        foreach ($data as $id => $name) {
        	    if ($default == $id) {
        			$response .='<option value="'.$id.'" selected>'.$name.'</option>';
        	    } else {
        			$response .='<option value="'.$id.'">'.$name.'</option>';
        	    }
        }
		echo $response;
		return true;
	}
	
	private function setupUrl() {
		$apikey = get_option('orbitscriptsads_api_key');
	    $baseurl = get_option('orbitscriptsads_api_host');
	    $siteid = get_option('orbitscriptsads_site_id');
		$this->siteurl = $baseurl;
		
		$url = $baseurl.'/xmlapi?apiKey='.$apikey;
		if (!empty($siteid)) $url .= '&siteId='.$siteid ;
		return $url.'&action=';
	}
	
	private function request($action) {
		$url=$this->setupUrl();
		$url.=$action;
		$xml = @simplexml_load_file($url);
		if (empty($xml)) {
			$this->errors=array('code' => -1,
						'msg' => 'Empty response'
			);
			return false;
		} else {
			$this->errors=array('code' => strval($xml->response->errors->error[0]->code),
			            'msg' =>  strval($xml->response->errors->error[0]->description)
			);
			if ($this->errors['code'] != 0)	return false;
		} 
		if ($action == 'get_sites_channels') {
			$result['userid']=strval($xml->response->data->sites->userid);
			foreach ($xml->response->data->sites->site as $site) {
				$result[strval($site->id)] = array ('name' => strval($site->name),
										'channels' => '');
				foreach ($site->channels->channel as $channel) {
					$result[strval($site->id)]['channels'][strval($channel->id)]=array (
						'name' => strval($channel->name),
						'dimension' => strval($channel->dimension),
					    'width' => strval($channel->width),
						'height' => strval($channel->height),
						'siteurl' => strval($this->siteurl)
						);
				}
			}
		} elseif($action == 'get_palettes') {
			foreach ($xml->response->data->palettes->palette as $palette) {
				$result[strval($palette->id)]=strval($palette->name);
			}
		}
		return $result;
	}

}


?>