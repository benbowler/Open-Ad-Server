<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Родительский контроллер для получения CMS плагина
 *
 * @author Владимир Янц
 * @project SmartPPC6
 * @version 1.0.0
 */
class Parent_get_plugin extends Parent_controller {
   
   protected $role = "admin";
   
   protected $menu_item = "Manage Sites/Channels";
   
   protected $cms = '';
   
   public function Parent_get_plugin() {
      parent::Parent_controller();
      $this->_add_css('cms_plugins/cms_plugins');
   }
  
   public function index() {
   	$this->load->library("form");
   	$this->load->model( 'pagination_post' );
   	$this->load->model('site');
      $this->load->library('Table_Builder');
    
		$apikey = $this->global_variables->get('ApiKey',$this->user_id);
    	if (empty($apikey)) {
    		$this->new_apikey(false);
    		$apikey = $this->global_variables->get('ApiKey',$this->user_id);
		} 
		$view_data['API_KEY'] = $apikey;
   		$view_data['CMS'] = $this->cms;
   		
   		if ($this->cms == 'joomla') $view_data['EXTENSION'] = 'Component';
   		if ($this->cms == 'drupal') $view_data['EXTENSION'] = 'Module';
   		if ($this->cms == 'wordpress') $view_data['EXTENSION'] = 'Plug-in';

   	$params = array (
               'fields' =>    'sites.id_site, sites.url, sites.name',
               'id_entity' => $this->user_id,
               'status' =>    'active');
   	
   	$sites_array = $this->site->get_list($params);
	$sites[0]='{@Choose a site@} ...';
   	
	if (is_array($sites_array)) {
		foreach ($sites_array as $site) {
   			$sites[$site['id_site']]=$site['name'].' ('.$site['url'].')';
   		}
	}
   	$form = array(
         "name"         => "sites",
         "view"         => 'common/get_plugin/form.html',               
         "action"       => $this->site_url.$this->index_page.$this->role.'/get_plugin/howto/'.$this->cms,
         "fields"       => array(                     
            "post_id" => array(
               "id_field_type"    => "string",
               "form_field_type"  => "select",      
               "validation_rules" => "required",      
               "options" => $sites,
            ),
         ),
      );
      
	$form_content = $this->form->get_form_content("create", $form, $this->input, $this);
   	
    $view_data['SITES_LIST'] = $form_content;
   	 
   	$content = $this->parser->parse('common/get_plugin/body.html', $view_data, FALSE);
	$this->_set_title("Get Plugin");
	$this->_set_content($content);
    $this->_display();
    
  }

   public function _load($id) {
      return;
   } //end _load
   
   public function _create() {
   	
   }
   
    public function howto ($cms){
    	if (!empty($cms)) {
			$view_data['CMS'] = $cms;
			$view_data['SITE_NAME'] = $this->global_variables->get('SiteName');
			if (isset($_POST['post_id'])) { 
				$view_data['ID'] = $_POST['post_id'];
			} else {
				$view_data['ID'] = '';
			}
			$view_data['STEP'] = $this->parser->parse('common/get_plugin/howto/step1/'.$cms.'.html', $view_data, FALSE);
			switch ($cms) {
				case 'joomla':
					$view_data['STEP1_NAME'] = "Uploading and installation";
					$view_data['STEP2_NAME'] = "Add new channels";
					$view_data['STEP3_NAME'] = "Change Settings";
					$view_data['EXTENSION'] = 'Component';
					break;
				case 'wordpress':
					$view_data['STEP1_NAME'] = "Uploading and installation";
					$view_data['STEP2_NAME'] = "Add new channels";
					$view_data['STEP3_NAME'] = "Change Settings";
					$view_data['EXTENSION'] = 'Plug-In';
					break;
				case 'drupal':
					$view_data['STEP1_NAME'] = "Uploading and installation";
					$view_data['STEP2_NAME'] = "Add new channels";
					$view_data['STEP3_NAME'] = "Change Settings";
					$view_data['EXTENSION'] = 'Module';
					break;
			}
			$content = $this->parser->parse('common/get_plugin/howto/body.html', $view_data, FALSE);
			$this->_set_title("Get Plugin");
			$this->_set_content($content);
    		$this->_display();
    	} else {
    		redirect($this->role.'/get_plugin');
    	}
    }
   	
    public function step1($cms, $id = '') {
    		$view_data = array('SITE_NAME'=>$this->global_variables->get('SiteName'));
    		$content = $this->parser->parse('common/get_plugin/howto/step1/'.$cms.'.html', $view_data, FALSE);
    }
    
    public function step2($cms, $id = '') {
    		$view_data = array('SITE_NAME'=>$this->global_variables->get('SiteName'));
    		$content = $this->parser->parse('common/get_plugin/howto/step2/'.$cms.'.html', $view_data, FALSE);
    }

    public function step3($cms, $id = '') {
    		$view_data = array('SITE_NAME'=>$this->global_variables->get('SiteName'));
    		$content = $this->parser->parse('common/get_plugin/howto/step3/'.$cms.'.html', $view_data, FALSE);
    }
    public function download() {
    	$this->load->helper('download');
    	$this->load->library('zip');
    	$cms = $_POST['cms'];

    	$view_data = array(
    		'SITE_NAME'=>$this->global_variables->get('SiteName'),
    		'BASE_URL'=> $this->site_url,
    		'API_KEY'=>$this->global_variables->get('ApiKey',$this->user_id),
    		'SITE_ID'=>$_POST['id']
    	);
    	
    	$plugin_dir = BASEPATH.'files/cms_plugins/'.$cms;

		switch ($cms) {
			//If Joomla
			case 'joomla' :
				//prepare module.zip
				$plugin_dir_mod = $plugin_dir.'/module';
			    $list =  $this->dir_list($plugin_dir_mod, $plugin_dir_mod);
	    		
			    foreach ($list as $item) {
	    			if ($item != 'mod_orbitscripts_ads.xml') {
	    				$this->zip_item($item, $plugin_dir_mod);
	    			} else {
	    				$content = $this->parser->parse('../../files/cms_plugins/'.$cms.'/module/'.$item, $view_data, FALSE);
	    				$this->zip->add_data($item, $content);
	    			}
	    		}
	    		
	    		$module = $this->zip->get_zip();
	    		$this->zip->clear_data();
	    		
	    		//prepare component.zip
				$plugin_dir_com = $plugin_dir.'/component';
			    $list =  $this->dir_list($plugin_dir_com, $plugin_dir_com);
				
	    		foreach ($list as $item) {
	    			if ($item != 'install.xml' && $item != 'admin/views/orbitscripts/view.html.php') {
	    				$this->zip_item($item, $plugin_dir_com);
	    			} else {
	    				$content = $this->parser->parse('../../files/cms_plugins/'.$cms.'/component/'.$item, $view_data, FALSE);
	    				$this->zip->add_data($item, $content);
	    			}
	    		}
	    		
	    		$component = $this->zip->get_zip();
	    		$this->zip->clear_data();
	    		
	    		//pack component.zip && module.zip to final archive
	    		$this->zip->add_data('module.zip',$module);
	    		$this->zip->add_data('component.zip',$component);
				break;
				
			//If Worpress				
			case 'wordpress' :
			    $list =  $this->dir_list($plugin_dir, $plugin_dir);
			    foreach ($list as $item) {
	    			if ($item != 'orbitscriptsads/orbitscriptsads.php') {
	    				$this->zip_item($item, $plugin_dir);
	    			} else {
	    				$content = $this->parser->parse('../../files/cms_plugins/'.$cms.'/'.$item, $view_data, FALSE);
	    				$this->zip->add_data($item, $content);
	    			}
	    		}
				break;
				
			//If Drupal
			case 'drupal' :
			    $list =  $this->dir_list($plugin_dir, $plugin_dir);
				foreach ($list as $item) {
	    			if ($item != 'orbitscriptsads/orbitscriptsads.module' && $item != 'orbitscriptsads/orbitscriptsads.info' && $item != 'orbitscriptsads/managed/orbitscriptsads_managed.info') {
	    				$this->zip_item($item, $plugin_dir);
	    			} else {
	    				$content = $this->parser->parse('../../files/cms_plugins/'.$cms.'/'.$item, $view_data, FALSE);
	    				$this->zip->add_data($item, $content);
	    			}
	    		}
				break;
		}

    	$this->zip->download($cms.'.zip');
    }
    
	/*
	 * Get files list
	 */
	function dir_list($path, $basepath) {
		$list = scandir($path);
			unset($list[0]);
			unset($list[1]);
		foreach ($list as $id => $item) {
         if ($item != '.svn'){
			if (!strpos($item,'.')) {
				$dir_list = $this->dir_list($path.'/'.$item, $basepath);
	    		foreach ($dir_list as $one) {
	    			$list[]=$one;
	    		} 
	    		unset($list[$id]);
			} else {
	    		$list[$id] = $path.'/'.$item;
	    	}
	    } else {
         unset($list[$id]);
       }
       }
		foreach ($list as $id => $item) {
	    	$list[$id] = str_replace($basepath.'/','',$item);
		}
  //    Zend_Debug::dump($list);
		return $list;
	}
	
	/*
	 * Zip one file
	 */
	function zip_item($path, $base_path) {
		if (FALSE !== ($data = @file_get_contents($base_path.'/'.$path))) $this->zip->add_data($path, $data);
	}
	
	/*
	 * New api key
	 */
	function new_apikey($output = true) {
		$newkey = md5(time());
		$this->global_variables->set('ApiKey',$newkey,$this->user_id);
		if ($output) {
			echo $newkey;
		} else {
			return $newkey;
		}
	}
	
	function drupal() {
		$this->cms='drupal';
		$this->index();
	}
	
	function joomla() {
		$this->cms='joomla';
		$this->index();
	}
	function wordpress() {
		$this->cms='wordpress';
		$this->index();
	}
}

?>