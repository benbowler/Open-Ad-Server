<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер задания названия группы в кампании
 *
 */
class Create_Campaign_Step_Group_Name extends Campaign_Wizard {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
	public function __construct() {
	  parent::__construct();
	  $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  $this->on_submit = "$('#group_form').submit();";
	  $this->cancel_creation_controller = 'advertiser/manage_ads';
	  $this->load->library('form');
	}
	
	/**
	 * Отображение формы задания названия группы в кампании
	 *
	 * @param string $campaign_type тип создаваемой кампании
	 */
	public function index($campaign_type) {
		$this->load->model('new_campaign');
		
		$this->set_campaign_type($campaign_type);
		//$this->get_current_step(uri_string());
		$this->setCurrentStep(uri_string());
		
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
		
		
		$this->review_mode = $this->input->post('review_mode');
		
		if ($this->review_mode) {
         $form_caption = __('Edit Group');
      } else {
         $form_caption = __('Create Group');
      }
      
      switch ($campaign_type) {
      	case 'cpm_flatrate':
      	 $form_template = "advertiser/manage_ads/campaigns/create_step_group_name.html";
      	break;
      	case 'cpc':
          $form_template = "advertiser/manage_ads/campaigns/create_step_group_name_cpc.html";
         break;
         case 'cpa':
          $form_template = "advertiser/manage_ads/campaigns/create_step_group_name_cpa.html";
         break;         
      	default:
      		echo "UNKNOWN CAMPAIGN TYPE";
      		return;
      	break;
      }
      
		
		$form = array(
         "id"          => $campaign_type, 
         "name"        => "group_form",
		   "vars"        => array('FORM_CAPTION' => $form_caption, 'REVIEW_MODE' => $this->review_mode,'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE)),
         "view"        => $form_template,
		   "redirect"   => $this->get_next_step_controller(),
         "fields"      => array(                     
            "group_name" => array(
               "display_name"     => __("Group Name"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
		         'max' => 50              
            )                    
         ) 
      );
      
	  switch ($campaign_type) {
         case 'cpc':
          /*$form['fields']['default_bid'] = array(
               "display_name"     => __("Default Bid"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required|float|positive");
          $form['fields']['default_bid_image'] = array(
               "display_name"     => __("Default Bid Image"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required|float|positive");*/
          $form['fields']['budget'] = array(
               "display_name"     => __("Dayly Budget"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "float[2]|positive");
         case 'cpa':
          $form['fields']['budget'] = array(
               "display_name"     => __("Dayly Budget"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "float[2]|positive"); 
         break;
      }
	
      $content = $this->form->get_form_content('modify', $form, $this->input, $this);
		$this->_set_content($content);
		$this->_display();
		
	}
	
	public function _load($id) {
      $fields = array();
   
      $this->new_campaign->init_storage($this->id_xml);
      
      $fields['group_name'] = $this->new_campaign->get_group_name();
  
	   switch ($id) {
         case 'cpc':
          /*$fields['default_bid'] = $this->new_campaign->get_default_bid('text');
          if (!is_null($fields['default_bid'])) {
          	$fields['default_bid'] = type_to_str($fields['default_bid'],'float');
          } else {
          	$fields['default_bid'] = type_to_str($this->global_variables->get('DefaultTextBid'), 'float');
          }
	       $fields['default_bid_image'] = $this->new_campaign->get_default_bid('image');
          if (!is_null($fields['default_bid_image'])) {
            $fields['default_bid_image'] = type_to_str($fields['default_bid_image'],'float');
          } else {
            $fields['default_bid'] = type_to_str($this->global_variables->get('DefaultImageBid'), 'float');
          }*/
          $fields['budget'] = $this->new_campaign->get_daily_budget();
          if (!is_null($fields['budget'])) {
            $fields['budget'] = type_to_str($fields['budget'],'float');
          }
         break;
         case 'cpa':
          $fields['budget'] = $this->new_campaign->get_daily_budget();
          if (!is_null($fields['budget'])) {
            $fields['budget'] = type_to_str($fields['budget'],'float');
          }          
          break;
         
      }
      
      return $fields;
	}
	
   public function _save($id, $fields) {
      $this->new_campaign->init_storage($this->id_xml);
      $this->new_campaign->set_group_name($fields['group_name']);

      switch ($id) {
         case 'cpc':
           if ($fields['budget']) {
	           $daily_budget = type_cast($fields['budget'],'float');
           } else {
           	  $daily_budget = NULL;
           }
	        $this->new_campaign->set_daily_budget($daily_budget);
           
	        $bid_value = $this->new_campaign->get_default_bid('text');
	        if(is_null($bid_value)) {
	        	  $bid_value = type_to_str($this->global_variables->get('DefaultTextBid',0,'0.01'), 'float'); 
	        }
	        
           //$bid_value = type_cast($fields['default_bid'],'float');
           
           if (!is_null($daily_budget) && ($daily_budget < $bid_value)) {
           	 return __('Daily Budget cannot be less than Default Bid!');
           }
           
           $this->new_campaign->set_default_bid($bid_value, 'text');
           
           //$bid_value = type_cast($fields['default_bid_image'],'float');
           $bid_value = $this->new_campaign->get_default_bid('image');
           if(is_null($bid_value)) {
              $bid_value = type_to_str($this->global_variables->get('DefaultImageBid',0,'0.01'), 'float'); 
           }
           
           if (!is_null($daily_budget) && ($daily_budget < $bid_value)) {
             return __('Daily Budget cannot be less than Default Bid Image!');
           }
           
           $this->new_campaign->set_default_bid($bid_value, 'image');
           
         break;
         
         case 'cpa':
           if ($fields['budget']) {
              $daily_budget = type_cast($fields['budget'],'float');  
           } else {
               $daily_budget = NULL;
           }
          $this->new_campaign->set_daily_budget($daily_budget);
            
         break;        
      }
      
      $this->new_campaign->save_data();
   }
}