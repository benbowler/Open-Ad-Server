<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Buy_Site_Channel extends Parent_controller {
	
	protected $role = "advertiser";
	
	protected $menu_item = "Site Directory";
	
	public function __construct() {
		parent::__construct();
		$this->_add_ajax();
		$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __($this->menu_item))));
		
		$this->load->model('site');
		$this->load->model('channel');
		$this->load->model('sites_channels');
		$this->load->helper('form');
	}

	public function index() {
		$site_code    = $this->input->post('site_code');
      $program_code = $this->input->post('program_code');

      if (!$site_code || !$program_code) {
         $this->error_message('Site is not found.');
         return;
      }
      //Стираем данные о ID XML файла (именно здесь - чтобы убить эти данные и в куке)
      $this->session->unset_userdata('id_xml');

      $this->load->model('groups');

      if ($program_code == '') {
      	//код программы не передан. Отправляем на выбор программы
         $this->select_program_type($site_code, $channel_code);
         return;
      } else {
      	$id_program = (int)type_cast( $program_code, 'textcode' );
      	$template_data = array();
      	if (0 < $id_program) {
      	   //Flate Rate/CPM
      	   $campaign_type = 'cpm_flatrate';
            $campaigns_filter = 'cpm_flatrate';
	      	$program_info = $this->sites_channels->get_program_info( $id_program );
            if (is_null($program_info)) {
               $this->error_message('Program is not found.');
               return;
            }
            //получим информацию о сайте и канале
            $id_site = (int)type_cast( $site_code, 'textcode' );
            $id_channel = (int)$program_info->id_channel;

            $site_info = $this->site->get_info ( $id_site );
            $channel_info = $this->channel->get_info ( $id_channel );

            if (is_null($site_info) || is_null($channel_info)) {
               $this->error_message('Site or channel is not found.');
               return;
            }
            //данные о программе
            $template_data['PROGRAM_TYPE'] = $program_info->program_type;
            $template_data['CAMPAIGN_TYPE'] = $campaign_type;
            $template_data['VOLUME'] = $template_data['PROGRAM_TYPE'] == 'cpm' ? type_to_str($program_info->volume, 'integer') : $program_info->volume . ' days';
            $template_data['COST_TEXT'] = type_to_str($program_info->cost_text, 'money');
            $template_data['COST_IMAGE'] = type_to_str($program_info->cost_image, 'money');
            $template_data['SITE_CODE'] = $site_code;
	         $template_data['CHANNEL_CODE'] = type_to_str($id_channel, 'textcode');
	         $template_data['SITE_NAME'] = type_to_str ( $site_info->name, 'encode' );
	         $template_data['SITE_URL'] = $site_info->url;
	         $template_data['CHANNEL_NAME'] = type_to_str ( $channel_info->name, 'encode' );
	         $template_data['CHANNEL_TYPE'] = $channel_info->ad_type;
	         $template_data['USER_CODE'] = type_to_str($this->user_id,'textcode');
	         $template_data['EMPTYTREE'] = $this->groups->EmptyTree?'true':'false';
      	} else {
      	   $this->error_message('Unknown Program.');
            return;
      	}

         $campaigns_tree = $this->groups->get_html_tree($this->user_id, $this->user_name, 0, $campaigns_filter);
         $template_data['CAMPAIGNS_TREEVIEW'] = $campaigns_tree;

         $template = 'advertiser/site_directory/select_group_' . $campaign_type . '.html';

         $content = $this->parser->parse($template, $template_data);
                                      
         $this->_set_content($content);
         $this->_display();         
      }
	}
	
	public function select_program_type($site_code, $channel_code, $selected_program_type = null) {
		$id_site = type_cast($site_code, 'textcode');
      $id_channel = type_cast($channel_code, 'textcode');
		
      $site_info = $this->site->get_info ( $id_site );
      
      $channel_info = $this->channel->get_info ( $id_channel );
      
      if (is_null($site_info) || is_null($channel_info)) {
      	$this->error_message('Site or channel is not found.');
         return;
      }
      
      $site_channel_info =  $this->sites_channels->get_id_site_channel($id_site, $id_channel);
      
      if(is_null($site_channel_info) || ($site_channel_info->status != 'active')) {
      	$this->error_message('Channel is not found on specified site.');
         return;
      }
      
      $available_program_types = $this->get_available_program_types($id_site, $id_channel, $site_channel_info->id_site_channel);
      
      $program_types_options = array();
      
      foreach ($available_program_types as $program_type => $available) {
      	if ($available) {
            $program_types_options[$program_type] = __($program_type);
      	} 	
      }
      if (count($program_types_options)>1) {
         $program_types_options = array("select" => __("select...")) + $program_types_options;
      }
      
      	$allowedAdTypes = explode(',', $channel_info->ad_type);
      	$allowedAdTypeLabels = array();
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedAdTypes)) {
      		$allowedAdTypeLabels[] = __('Text');
      	}
      	if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) {
      		$allowedAdTypeLabels[] = __('Image');
      	}
      	
      	if (count($allowedAdTypeLabels) > 2) {
      		$allowedAdTypeLabels = $allowedAdTypeLabels[0] . ', ' . $allowedAdTypeLabels[1] . ' & ' . $allowedAdTypeLabels[2];
      	} else {
      		$allowedAdTypeLabels = implode(' & ', $allowedAdTypeLabels);
      	}
      	
		$content = $this->parser->parse('advertiser/site_directory/select_program_type.html',
		                                array('SITE_CODE' => $site_code,
		                                      'CHANNEL_CODE' => $channel_code,
		                                      'SITE_NAME' => type_to_str ( $site_info->name, 'encode' ),
		                                      'SITE_URL' => $site_info->url,
		                                      'CHANNEL_NAME' => type_to_str ( $channel_info->name, 'encode' ),
		                                      'PROGRAM_TYPES' => form_dropdown('program_type',$program_types_options, $selected_program_type),
                                              'CHANNEL_TYPE_TEXT' => $allowedAdTypeLabels,
		                                      'CHANNEL_TYPE_CODE' => $channel_info->ad_type,
		                                      'CHANNEL_FORMAT' => $channel_info->width.'&times;'.$channel_info->height,
		                                      'ID_DIMENSION' => $channel_info->id_dimension,
		                                      'MAX_AD_SLOTS' => ('image' != $channel_info->ad_type)?$channel_info->max_ad_slots:'1',
		                                      'PROGRAM_TYPES_LABEL' => (count($program_types_options) > 1)?__('Select payment program type'):__('Campaign Type')
		                                     ));
		                                
		$this->_set_content($content);
		$this->_display();
	}
	
   public function select_group($site_code, $channel_code) {
   	
   	//Стираем данные о ID XML файла (именно здесь - чтобы убить эти данные и в куке)
   	$this->session->unset_userdata('id_xml');
   	

   	$program_type = $this->input->post("program_type");   	
   	
   	switch ($program_type) {
   		case 'cpm_flatrate':
   			$campaigns_filter = 'cpm_flatrate';
   		break;
   		default:
   			$this->error_message('Wrong program type specified!');
   			return;
   	}
   	
   	$id_site = type_cast($site_code, 'textcode');
      $id_channel = type_cast($channel_code, 'textcode');
   	
      $site_info = $this->site->get_info ( $id_site );
      $channel_info = $this->channel->get_info ( $id_channel );
      
      if (is_null($site_info) || is_null($channel_info)) {
         $this->error_message('Site or channel is not found.');
         return;
      }
      
      $this->load->model('groups');
      
      $campaigns_tree = $this->groups->get_html_tree($this->user_id, $this->user_name, 0, $campaigns_filter);
      
      $content = $this->parser->parse('advertiser/site_directory/select_group.html',
                                      array('SITE_CODE' => $site_code,
                                            'CHANNEL_CODE' => $channel_code,
                                            'SITE_NAME' => type_to_str ( $site_info->name, 'encode' ),
                                            'SITE_URL' => $site_info->url,
                                            'CHANNEL_NAME' => type_to_str ( $channel_info->name, 'encode' ),
                                            'PROGRAM_TYPE' => $program_type,
                                            'CAMPAIGNS_TREEVIEW' => $campaigns_tree,
                                            'CHANNEL_TYPE' => $channel_info->ad_type,
                                            'USER_CODE' => type_to_str($this->user_id,'textcode'),
                                            'EMPTYTREE' => $this->groups->EmptyTree?'true':'false' 
                                           ));
                                      
      $this->_set_content($content);
      $this->_display();
   }

   public function add_site_channel_to_existing_campaign($site_code, $channel_code, $campaign_type, $program_type = null) {
   	$reply = array('error_flag' => true, 'error_message' => __('Unknown error'));

   	$group_code = $this->input->post('group_code');
   	$result = $this->sites_channels->check_group_site_channel ( type_cast($site_code, 'textcode'), type_cast($channel_code, 'textcode'), type_cast($group_code, 'textcode') );
   	if ($result == true || $campaign_type == 'cpc') {
   	   switch ($campaign_type) {
	         case 'cpm_flatrate':
	            $reply['redirect'] = $this->site_url.$this->index_page.'advertiser/edit_channels/index/'.$group_code.'/'.$program_type;
	            $reply['error_flag'] = false;
	         break;
	      }
	      
	      if (!$reply['error_flag']) {
	         $this->session->set_userdata('add_site_channel', json_encode(array('site_code' => $site_code,      
	                                                                            'channel_code' => $channel_code,                                                        
	                                                                            'program_type' => $campaign_type,
	                                                                            'program' => $program_type )));
	      }
	      
   	} elseif ($result == false) {
   	   $reply = array('error_flag' => true, 'error_message' => __('This site and channel is added to group'));
   	}
   	echo json_encode($reply);
   }
   
   public function add_site_channel_to_new_campaign($site_code, $channel_code, $program_type) {
      $reply = array('error_flag' => true, 'error_message' => __('Unknown error'));
      
      switch ($program_type) {
         case 'cpm_flatrate':
            $reply['redirect'] = $this->site_url.$this->index_page.'advertiser/create_campaign_step_main/index/cpm_flatrate';
            $reply['error_flag'] = false;
         break;
      }
      
      if (!$reply['error_flag']) {
         $this->session->set_userdata('add_site_channel', json_encode(array('site_code' => $site_code,  
                                                                            'channel_code' => $channel_code,
                                                                            'program_type' => $program_type )));
      }
      
      echo json_encode($reply);
   }
   
   public function check() {  
   	echo json_encode(
   	   array(    
	         'error_flag' => false,
	         'buy_link' => $this->site_url.$this->index_page.$this->role . '/buy_site_channel',
	         'site_code' => $this->input->post('site_code'),
	         'program_code' => $this->input->post('program_code')
         )
      );      
   }
	
	private function get_available_program_types($id_site, $id_channel, $id_site_channel = null) {
		
		$available_program_types = array('cpm_flatrate' => false,
		                                 'cpc' => false);
		
		if (is_null($id_site_channel)) {
			$site_channel_info =  $this->sites_channels->get_id_site_channel($id_site, $id_channel);
	      
	      if(is_null($site_channel_info) || ($site_channel_info->status != 'active')) {
	         return $available_program_types;
	      } else {
		       $id_site_channel = $site_channel_info->id_site_channel;
	      } 	
		}
	
		$this->load->model('channel_program');
		
		$available_programs = $this->channel_program->get_list(array('fields' => 'id_program, title, program_type, cost_text, cost_image, volume',
                    'id_channel' => $id_channel, 'order_by' => 'title', 'order_direction' => 'asc'));
		
		
		if (!is_null($available_programs)) {
            $slot_info = $this->sites_channels->get_slot_info($id_site_channel);
            
		      foreach ($available_programs as $key => $available_program) {
               switch($available_program['program_type']) {
               	case 'CPM':
               	  $available_program_types['cpm_flatrate'] = true;	
               	break;
               	case 'Flat_Rate':
               	  if ($slot_info['free'] != 0) {
                      $available_program_types['cpm_flatrate'] = true;
               	  } 
                  break;
               } 
            }
       
		}

		$site_info = $this->site->get_info($id_site);
		
		return $available_program_types;
	}
	
	private function error_message($message) {
		   $data = array(
           'MESSAGE' => __($message),
           'REDIRECT' => $this->site_url.$this->index_page.'advertiser/site_directory'
         );
         $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
         $this->_set_content($content);
         $this->_display();
	}
}