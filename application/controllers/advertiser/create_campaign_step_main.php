<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер задания основных параметров кампании - название/сроки/таргетинг
 *
 */
class Create_Campaign_Step_Main extends Campaign_Wizard {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
	protected $targeting_all_list;
	
	public function __construct() {
	  parent::__construct();
	  $this->load->model('new_campaign');
	  $this->load->model('targeting_groups');
	  
	  $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  $this->on_submit = "OnSubmit();";
	  $this->cancel_creation_controller = 'advertiser/manage_ads';
	  $this->_add_ajax();
	  
	  $this->load->helper('periods');
	  $this->load->library('form');
	  
	  $this->targeting_all_list = new Campaign_Targeting();
	}
	
	public function index($campaign_type) {
		
      $this->load->model('languages');
      $this->load->model('countries');
      $this->load->model('campaigns');

      
      if ('cpm_flatrate' == $campaign_type) {
         $this->content['CPM_FLATRATE'] = array(array());
      } else {
         $this->content['CPM_FLATRATE'] = array();
      }
      
	   $add_site_channel = $this->session->userdata('add_site_channel');
      
      if(FALSE !== $add_site_channel) {
      	$this->load->model('site');
      	
         $add_site_channel = json_decode($add_site_channel);
         if ($campaign_type == $add_site_channel->program_type) {
	         switch ($add_site_channel->program_type) {
	         	case 'cpc':
		            $id_site = type_cast($add_site_channel->site_code,'textcode');
		            $site_info = $this->site->get_info($id_site);
		            if (!is_null($site_info)) {
		                 $this->_set_notification(
		                 sprintf(__('Site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory will be added on &bdquo;Select Sites&ldquo; step'),
		                           type_to_str($site_info->name,'encode'), 
		                           $site_info->url));
		              }	           
	         	break;
	         	
	         	case 'cpm_flatrate':
	         		$this->load->model('channel');
	         		
			            $id_site = type_cast($add_site_channel->site_code,'textcode');
			            $id_channel = type_cast($add_site_channel->channel_code,'textcode');
			            $site_info = $this->site->get_info($id_site);
			            $channel_info = $this->channel->get_info($id_channel);
			            
			            if (!is_null($site_info) && !is_null($channel_info)) {
			               $this->load->model('sites_channels');
			               $site_channel_info =  $this->sites_channels->get_id_site_channel($id_site, $id_channel);
			               
			               if (!is_null($site_channel_info)) {
	
			                     $this->_set_notification(
			                     sprintf(__('Channel &bdquo;%s&ldquo; at site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory will be added on &bdquo;Select Sites/Channels&ldquo; step'),
			                           type_to_str($channel_info->name,'encode'), type_to_str($site_info->name,'encode'),  
			                           $site_info->url));            
			               } 
			            }
	         	break;
	         }
         }
      }
   
 
      
      //Рекламодатель может выбирать для рекламы только те страны, которые не заблокированы в системе? тогда get_list(array('filter' => 'enabled'))
      $this->targeting_all_list->countries = $this->countries->get_list(array('filter' => 'enabled'));
      $this->targeting_all_list->languages = $this->languages->get_list(array('filter' => 'enabled'));
      
		$this->set_campaign_type($campaign_type);
      //$this->get_current_step(uri_string());
      $this->setCurrentStep(uri_string());
      
      $tmp = $this->campaigns->get_list(array('id_advertiser' => $this->user_id));
            
      $this->review_mode = $this->input->post('review_mode');
      if ($this->review_mode) {
      	$form_caption = __('Edit Campaign');
      } else {
      	$form_caption = __('Create Campaign');
      }
       
		$form = array(
         "id"          => $campaign_type, 
         "name"        => "name_date_form",
         "view"        => "advertiser/manage_ads/campaigns/create_step_main.html",
		   "redirect"   => $this->get_next_step_controller(),
		   "vars"         => array(
		    'TARGETING_ALL_LIST' =>  json_encode($this->targeting_all_list),		                           		
		    'NUMBERFORMAT' => get_number_format(), 
		    'TIME_HOURS_FORMAT' => substr_count(get_time_format(),'A') > 0?'12':'24',
		    'FORM_CAPTION' => $form_caption, 
		    'REVIEW_MODE' => $this->review_mode,
          'TIMEZONE' => sprintf(__("Scheduling runs at server time. Server is set for <b>%s</b> time zone. At the moment server time is <b>%s</b>."), gmdate('T') . date('P'), date('H:i')),
		    'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE)),
         "fields"      => array(                     
            "campaign_name" => array(
               "display_name"     => __("Campaign Name"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
		         "max" => 50              
            ),
            "targeting" => array(               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden"               
            ),
            "schedule" => array(               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "validation_rules" => "required"                      
            ),
            "id_targeting_group" => array(               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "validation_rules" => "required"                     
            ),
            "id_targeting_group_temp" => array(               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "validation_rules" => "required"                    
            ),
            "targeting_type" => array(
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "validation_rules" => "required"
            )                       
         ) 
      );
	
      $content = $this->form->get_form_content('modify', $form, $this->input, $this);
		$this->_set_content($content);
		$this->_display();
	}
	
	public function _load($id) {
		$fields = array();
		
		$targeting_info = new Campaign_Targeting();
		
		if (1 == $this->new_campaign->init_storage($this->id_xml)) { //загрузка из файла
			 $id_targeting_group = $this->new_campaign->get_targeting();
	       $id_targeting_group_temp = $this->new_campaign->get_targeting(true);
	       $targeting_type = $this->new_campaign->get_targeting_type();
	       
	       
	       
	        $targeting_group_countries = $this->targeting_groups->get_group_list($id_targeting_group,'countries');
	        foreach ($targeting_group_countries as $country) {
	           $targeting_info->countries[] = $country['value'];
	        }
	        
				if (0 == count($targeting_info->countries)) {
		            foreach ($this->targeting_all_list->countries as $iso => $country) {
		               $targeting_info->countries[] = $iso;
		            }
		         }
	        
		     $targeting_group_languages = $this->targeting_groups->get_group_list($id_targeting_group,'languages');
           foreach ($targeting_group_languages as $language) {
              $targeting_info->languages[] = $language['value'];
           }
           
				 if (0 == count($targeting_info->languages)) {
		         foreach ($this->targeting_all_list->languages as $iso => $language) {
		            $targeting_info->languages[] = $iso;
		         }
		      }
	       
		} else { //установка опций таргетинга по-умолчанию (включены все страны, языки и часы)
            //Создание записи о таргетинге новой кампании 
            $id_targeting_group = $this->targeting_groups->create($this->user_id, $this->role, 'adv_'.$this->user_id.'_campaign_targeting');
            $id_targeting_group_temp = $this->targeting_groups->create($this->user_id, $this->role, 'adv_'.$this->user_id.'_campaign_targeting_temp');
            
            foreach ($this->targeting_all_list->countries as $iso => $name) {
              $targeting_info->countries[] = $iso; 
            }
            foreach ($this->targeting_all_list->languages as $id_language => $name) {
              $targeting_info->languages[] = $id_language; 
            }
            
            $targeting_type = 'basic';
         }
         
      $fields['id_targeting_group'] = type_to_str($id_targeting_group,'textcode');
      $fields['id_targeting_group_temp'] = type_to_str($id_targeting_group_temp,'textcode');
      
	   $name_date_info = $this->new_campaign->get_name_date();
	   if ($name_date_info) {
	      	$fields['campaign_name'] = $name_date_info['name'];
	   }

	   $schedule = $this->new_campaign->get_schedule();
	   
	   $fields['schedule'] = json_encode($schedule);
	   
	   $fields['targeting_type'] = is_null($targeting_type)?'basic':$targeting_type; 

	   $fields['targeting'] = json_encode($targeting_info); 
	   
      return $fields;
	}
	
   public function _save($id,$fields) {
   	$this->new_campaign->init_storage($this->id_xml);
   	
   	$schedule = json_decode($fields['schedule']);
      if (is_null($schedule)) {
         return 'Schedule decoding failed';
      }
   	
   	$this->new_campaign->set_targeting(type_cast($fields['id_targeting_group'],'textcode'));
      $this->new_campaign->set_targeting(type_cast($fields['id_targeting_group_temp'],'textcode'),true);
      $this->targeting_groups->copy(type_cast($fields['id_targeting_group_temp'],'textcode'),type_cast($fields['id_targeting_group'],'textcode'));
      
      $this->new_campaign->set_targeting_type($fields['targeting_type']);
      $this->new_campaign->set_schedule($schedule);
   	$this->new_campaign->set_name_date(array('name' => $fields['campaign_name']));
   	$this->new_campaign->save_data();
   }
}