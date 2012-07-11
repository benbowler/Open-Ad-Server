<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/advertiser/create_campaign_step_choose_sites_channels.php';

/**
 * Контроллер редактирования сайтов и каналов для группы
 *
 */
class Edit_channels extends Create_Campaign_Step_Choose_Sites_Channels {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
   public function __construct() {
      parent::__construct();
      $this->hide_old_sites_channels = false;
      $this->cancel_confirmation = '';
   } //end Edit_channels
   
   public function index($group_code = FALSE, $program_type = null) {
   	$this->set_campaign_type('edit_channels');
      $this->setCurrentStep('/advertiser/edit_channels');
      $this->load->model('groups', '', TRUE);
      $code = $this->input->post('group');
      if (FALSE === $code) {
         $code = $group_code;
      } else {
      	//Если код группы есть в POST, то на страницу пришли из раздела Manage Ads (производим очистку XML) 
         $this->new_campaign->free_storage($this->id_xml);
         $this->id_xml = uniqid();
         $this->session->set_userdata('id_xml', $this->id_xml);
      }
      if (2 == $this->new_campaign->init_storage($this->id_xml)) { //на первом шаге создаем xml-файл и наполняем его данными выбранной группы
      	$id_group = type_cast($code, 'textcode');
         //$this->new_campaign->free_storage($this->id_xml);         
         //$this->new_campaign->init_storage($this->id_xml);
         $this->new_campaign->set_group_name($code);
         /*$channels = $this->groups->get_channels($id_group);
         foreach ($channels as $channel) {
         	$this->new_campaign->add_channel($channel);
         }*/
         $this->new_campaign->set_program_type($program_type);
         
         $site_channels = $this->groups->get_site_channels($id_group, false);
         
         foreach ($site_channels as $site_channel) {
            $this->new_campaign->add_site_channel($site_channel);
         }

         $this->new_campaign->apply_sites_channels();

         $frequency_coup = $this->groups->get_frequency_coup($id_group);
         $this->new_campaign->set_daily_impressions(($frequency_coup > 0)?$frequency_coup:'');
         $this->new_campaign->save_data();
      } else { //на остальных шагах работаем с xml-файлом
         //$this->new_campaign->init_storage($this->id_xml);         
         $code = $this->new_campaign->get_group_name();
         $id_group = type_cast($code, 'textcode');      
      }
      $this->cancel_creation_controller = 'advertiser/manage_ads/group/'.$code.'/channels';      

      $this->load->model('campaigns', '', TRUE);
      $campaign_name = type_to_str($this->groups->parent_campaign($id_group), 'encode');
      
      $id_campaign = type_to_str($this->groups->id_parent_campaign($id_group), 'textcode');
      
      $group_name = type_to_str($this->groups->name($id_group), 'encode');
      
      $group_code = type_to_str($id_group, 'textcode');

      $this->form_title = '<h1><a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads">{@Manage Ads@}</a> &rarr; '.
                          '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads/campaign/'.$id_campaign.'">{@Campaign@}:</a> <span class="green i">&bdquo;'.$campaign_name.'&ldquo;</span> &rarr; '.
                          '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads/group/'.$group_code.'/channels">{@Group@}:</a> <span class="green i">&bdquo;'.$group_name.'&ldquo;</span></h1>';
      
      parent::index('edit_channels');
   } //end index
   
   public function _create($fields) {
         $this->new_campaign->init_storage($this->id_xml);
         if ((count($this->new_campaign->get_sites_channels())<1) && (count($this->new_campaign->get_sites_channels(array('status' => 'new')))<1)) {
            return "At least one channel must be added";
         } else {
            return "";
         }
   }
   
} //end class Edit_channels