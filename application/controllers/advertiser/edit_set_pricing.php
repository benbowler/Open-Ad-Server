<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/advertiser/create_campaign_step_set_pricing.php';

/**
 * Контроллер задания названия группы в кампании
 *
 */
class Edit_set_pricing extends Create_Campaign_Step_Set_Pricing {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
   protected $id_group;
   
   public function Edit_set_pricing() {
      parent::__construct();
      $this->cancel_confirmation = '';
      $this->create_save_button = 'Submit Selection';
      $this->on_submit = "onSubmit();";   
      $this->hide_old_sites_channels = false;   
   } //end Edit_set_pricing
   
   public function index() {
      $this->load->model('groups', '', TRUE);
      $this->new_campaign->init_storage($this->id_xml);         
      $code = $this->new_campaign->get_group_name();
      $this->id_group = type_cast($code, 'textcode');      
      $this->cancel_creation_controller = 'advertiser/manage_ads/group/'.$code.'/channels';      
      $this->load->model('campaigns', '', TRUE);
      $campaign_name = type_to_str($this->groups->parent_campaign($this->id_group), 'encode');
      
      $id_campaign = type_to_str($this->groups->id_parent_campaign($this->id_group), 'textcode');
      
      $group_name = type_to_str($this->groups->name($this->id_group), 'encode');
      
      $group_code = type_to_str($this->id_group, 'textcode');

      $this->form_title = '<h1><a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads">{@Manage Ads@}</a> &rarr; '.
                          '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads/campaign/'.$id_campaign.'">{@Campaign@}:</a> <span class="green i">&bdquo;'.$campaign_name.'&ldquo;</span> &rarr; '.
                          '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads/group/'.$group_code.'/channels">{@Group@}:</a> <span class="green i">&bdquo;'.$group_name.'&ldquo;</span></h1>';
             
      $this->next_step = 'advertiser/edit_set_pricing/success/'.$code;       
      parent::index('edit_channels');
   } //end index

   public function _save($id, $fields) {

      if (count($this->new_campaign->get_sites_channels(array('status' => 'old'))) + count($this->new_campaign->get_sites_channels(array('status' => 'new'))) == 0) {
   		return '';
      }
   $cost=$this->new_campaign->get_sites_channels_new_cost();
      $ballance = $this->entity->ballance($this->user_id);
      if ($cost > $ballance){
   		return '';
      }
	  
      $this->new_campaign->set_daily_impressions($fields['daily_impressions']);
      $this->new_campaign->save_data();
      
      $payment_trouble_flag = false;
      
      if ($this->input->post('form_type') == 'save') {     
      	if (0 == $fields['daily_impressions']) {
            $this->groups->set_frequency_coup($this->id_group, NULL);
      	} else {
      		$this->groups->set_frequency_coup($this->id_group, $fields['daily_impressions']);
      	}

         //Удаление старых сайтов-каналов в случае необходимости
	      $xml_sites_channels = $this->new_campaign->get_sites_channels(array('status' => 'old'));
	      $mysql_sites_channels = $this->groups->get_site_channels($this->id_group);
	      
	      $sites_channels_to_delete = array();
	      
	      foreach ($mysql_sites_channels as $site_channel_info) {
	      	if (!array_key_exists($site_channel_info['id_site_channel'], $xml_sites_channels)) {
	      	 $sites_channels_to_delete[] = $site_channel_info['id_site_channel'];
	      	}
	      }
	      
	      if (count($sites_channels_to_delete) > 0) {
	      	$this->groups->del_sites_channels($this->id_group, $sites_channels_to_delete);
	      }
	      
	      //добавление новых сайтов-каналов
	      $xml_sites_channels = $this->new_campaign->get_sites_channels(array('status' => 'new'));
	      if (count($xml_sites_channels) > 0) {
            $this->load->model('entity');
            $this->load->model('sites_channels');
	      	
	      	$added_group_sites_channels = $this->groups->add_sites_channels($this->id_group, $xml_sites_channels);
         
            $campaign_cost = $this->new_campaign->get_campaign_cost();
            $current_balance = $this->entity->ballance($this->user_id);
          
	         if ($current_balance >= $campaign_cost) {
	            foreach ($xml_sites_channels as $id_site_channel => $site_channel_info) {
	               $payment_trouble_flag|= in_array($this->sites_channels->renew($added_group_sites_channels[$id_site_channel], false, $site_channel_info['ad_type'], 
	               $site_channel_info['id_program']),array(2,3));
	            }
	         } else {
	            $this->load->helper("knapsack");
	         	//Оплата доступных добавленных сайтов-каналов
               $items_costs = $this->new_campaign->get_campaign_detailed_cost();

               $sites_channels_to_pay = knapsack($items_costs,$current_balance);

	            //Списание средств за доступные адвертайзеру программы 
               if (count($sites_channels_to_pay) > 0) {
	               foreach ($sites_channels_to_pay as $arr_index) {
	                  $site_channel_info = $items_costs[$arr_index];
	               
	                  $payment_trouble_flag|= in_array($this->sites_channels->renew($added_group_sites_channels[$site_channel_info['id_site_channel']], false, $site_channel_info['ad_type'], 
	                                            $site_channel_info['id_program']),array(2,3));
	               }
               }
	         }
	      }         
      }    
      $this->new_campaign->free_storage($this->id_xml); 
      $this->session->unset_userdata('id_xml');
   } //end _save
   
   public function success($code) {
      $data = array(
         'MESSAGE' => 
            __('Congratulations! Group channels was successfully updated!'),
         'REDIRECT' => $this->site_url.$this->index_page.'advertiser/manage_ads/group/'.$code.'/channels'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success
   
} //end class Edit_set_pricing