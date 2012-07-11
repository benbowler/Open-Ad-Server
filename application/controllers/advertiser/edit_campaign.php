<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер редактирования основных параметров кампании - название/сроки/таргетинг
 *
 */
class Edit_campaign extends Campaign_Wizard {
	
   protected $role = 'advertiser';
   
	protected $menu_item = 'Manage Ads';
   
   protected $targeting_all_list;
	
   /**
   * конструктор класса
   *
   * @return ничего не возвращает 
   */	
	public function __construct() {
	  parent::__construct();
	  $this->load->model('new_campaign');
	  $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'),__('Edit Campaign'))));
	  $this->set_campaign_type('edit_campaign');
	  $this->cancel_creation_controller = 'advertiser/manage_ads';
	  $this->cancel_confirmation = '';
	  $this->create_save_button = 'Save';
	  $this->on_submit = "OnSubmit();";
	  $this->_add_ajax();
	  
	  $this->load->library('form');
	  
	  $this->targeting_all_list = new Campaign_Targeting();
	} //end __construct()
	
	public function index() {
		//$this->get_current_step(uri_string());
		$this->setCurrentStep(uri_string());
		
	   $code = $this->input->post('id');
      $id_campaign = type_cast($code, 'textcode');	   
		
      $this->load->model('languages');
      $this->load->model('countries');
      $this->load->model('campaigns', '', TRUE);
      $this->load->model('schedule', '', TRUE);

	   $info = $this->campaigns->info($id_campaign);
      if ('cpm_flatrate' == $info['id_campaign_type']) {
         $this->content['CPM_FLATRATE'] = array(array());
      } else {
         $this->content['CPM_FLATRATE'] = array();
      }
      
	   //Рекламодатель может выбирать для рекламы только те страны, которые не заблокированы в системе? тогда get_list(array('filter' => 'enabled'))
      $this->targeting_all_list->countries = $this->countries->get_list(array('filter' => 'enabled'));
      $this->targeting_all_list->languages = $this->languages->get_list(array('filter' => 'enabled'));
      
      $tmp = $this->campaigns->get_list(array('id_advertiser' => $this->user_id));
     
      $campaigns_list[''] = __("Select campaign to load targeting from");
      foreach ($tmp as $key => $value) {
         $campaigns_list[type_to_str($key,'textcode')] = type_to_str($value,'encode');
      }
      
      unset($tmp);
       
      $campaign = type_to_str($this->campaigns->name(type_cast($code, 'textcode')), 'encode');	   
	   
		$form = array(
         "id"          => $code, 
         "name"        => "name_date_form",
         "view"        => "advertiser/manage_ads/campaigns/create_step_main.html",
		   //"redirect"    => 'advertiser/manage_ads',
		   'redirect'    => 'advertiser/edit_campaign/success',
		   'REVIEW_MODE' => false,
		   "vars"         => array(
		      'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE),
		      'NUMBERFORMAT' => get_number_format(),
		      'TIME_HOURS_FORMAT' => substr_count(get_time_format(),'A') > 0?'12':'24',
		      'TARGETING_ALL_LIST' =>  json_encode($this->targeting_all_list),
		      'CAMPAIGNS_LIST' => json_encode($campaigns_list),
            'TIMEZONE' => sprintf(__("Scheduling runs at server time. Server is set for <b>%s</b> time zone. At the moment server time is <b>%s</b>."), gmdate('T') . date('P'), date('H:i')),
		      'PROGRESS_BAR' => '<h1><a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads">'.__('Manage Ads').' </a> &rarr; ' . __('Edit Campaign') . ': <span class="green i">&bdquo;' . $campaign . '&ldquo;</span></h1>'),
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
               "form_field_type"  => "hidden",
               "validation_rules" => "required"                    
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
      
	   $id_campaign = type_cast($id, 'textcode');      
      $this->load->model('campaigns', '', TRUE);
	   $info = $this->campaigns->info($id_campaign);
	   
	   $fields['campaign_name'] = $info['name'];
      $fields['targeting_type'] = $info['targeting_type'];      
      $fields['id_targeting_group'] = type_to_str($info['id_targeting_group'],'textcode');
      
      $this->load->model('targeting_groups');
      
      $id_targeting_group_temp = $this->targeting_groups->copy($info['id_targeting_group']);

      //echo $info['id_targeting_group'];
      
      $fields['id_targeting_group_temp'] = type_to_str($id_targeting_group_temp,'textcode');
      
      
      
	   $targeting_info = new Campaign_Targeting();
      
      $targeting_group_countries = $this->targeting_groups->get_group_list($info['id_targeting_group'],'countries');
      foreach ($targeting_group_countries as $country) {
         $targeting_info->countries[] = $country['value'];
      }
      if (0 == count($targeting_info->countries)) {
	      foreach ($this->targeting_all_list->countries as $iso => $country) {
	         $targeting_info->countries[] = $iso;
	      }
      }
           
      $targeting_group_languages = $this->targeting_groups->get_group_list($info['id_targeting_group'],'languages');
      foreach ($targeting_group_languages as $language) {
         $targeting_info->languages[] = $language['value'];
      }
	   if (0 == count($targeting_info->languages)) {
         foreach ($this->targeting_all_list->languages as $iso => $language) {
            $targeting_info->languages[] = $iso;
         }
      }
      
      $fields['targeting'] = json_encode($targeting_info);
      
      
      $id_schedule = $this->campaigns->schedule($id_campaign);
      
      $schedule = new Campaign_Schedule();
      
      $schedule->schedule_is_set = !is_null($id_schedule);
      
      if (!is_null($id_schedule)) {
         $schedule->schedule = $this->schedule->get($id_schedule);
      }
      
      $fields['schedule'] = json_encode($schedule);
      
      return $fields;
	} //end _load
	
   /**
   * сохраняет настройки кампании
   *
   * @param string $id шифрованный код кампании 
   * @param array $fields массив с полями формы
   * @return string пустая строка - знак успешного сохранения настроек
   */	
   public function _save($id, $fields) {
   	//$targeting = json_decode($fields['targeting']);
      $id_campaign = type_cast($id, 'textcode');      
      $this->campaigns->update($id_campaign, array('campaign_name' => $fields['campaign_name'], 'targeting_type' => $fields['targeting_type']));      
      
      //$this->campaigns->set_countries_by_id($id_campaign, $targeting->countries);
      //$this->campaigns->set_languages_by_id($id_campaign, $targeting->languages);
      
      $this->load->model('targeting_groups'); //копирование таргетинга из временной группы
      $this->targeting_groups->copy(type_cast($fields['id_targeting_group_temp'],'textcode'),type_cast($fields['id_targeting_group'],'textcode'));
      //удаление временной группы
      $this->targeting_groups->cancel($this->user_id,$this->role,type_cast($fields['id_targeting_group_temp'],'textcode'));
      
      $schedule = json_decode($fields['schedule']);
      if ($schedule->schedule_is_set) {
          $this->campaigns->set_schedule($id_campaign,$this->schedule->set($schedule->schedule));
      } else {
      	$this->campaigns->set_schedule($id_campaign,NULL);
      }
      $this->schedule->kill_unused();
      return '';
   } //end _save
   
   public function success() {
   	  $this->setCurrentStep('/advertiser/edit_campaign');
      $data = array(
         'MESSAGE' => 
            __('Congratulations! Campaign was successfully updated!'),
         'REDIRECT' => $this->site_url.$this->index_page.'advertiser/manage_ads'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_approve     
   
} //end class Edit_campaign