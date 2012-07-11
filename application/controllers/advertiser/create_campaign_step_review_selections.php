<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер просмотра настроек создаваемой кампании
 *
 */
class Create_Campaign_Step_Review_Selections extends Campaign_Wizard {
	
	protected $role = "advertiser";
   
   	protected $menu_item = "Manage Ads";
	
	protected $targeting_all_list;
	
	public function __construct() {
	  	parent::__construct();
	  
	  	$this->cancel_confirmation = __("Are you sure to cancel campaign creation?");
	  	$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  	$this->on_submit = 'onCreateCampaign();';
	  	$this->cancel_creation_controller = 'advertiser/manage_ads';
	  	$this->create_save_button = "Submit Selection";
	  	$this->_add_ajax();
	  
	  	$this->_add_css('design');
	  
	  	$this->load->library('form');
	  	$this->load->library('Table_Builder');
	  
	  	$this->load->model('new_campaign');
	  	$this->load->model('languages');
     	$this->load->model('countries');
     	$this->load->model('campaigns');
     	$this->load->model('targeting_groups');
     
     	$this->targeting_all_list = new Campaign_Targeting();

      	$this->load->library('Plugins', array(
       		'path' => array('advertiser', 'create_campaign_step_review_selections'),
       		'interface' => 'Sppc_Advertiser_CreateCampaignStep_ReviewSelections_Interface'
      	));
   	}
   
   	public function set_targeting_type() {
      	$new_targeting_type = $this->input->post('targeting_type');
      	if($new_targeting_type) {
      		$this->new_campaign->init_storage($this->id_xml);
         	$this->new_campaign->set_targeting_type($new_targeting_type);
         	$this->new_campaign->save_data();
      	}
   	}
	
   	public function save_targeting() {
   		$this->new_campaign->init_storage($this->id_xml);
   		$targeting_id = $this->new_campaign->get_targeting();
      	$targeting_id_temp = $this->new_campaign->get_targeting(true);
      	$this->targeting_groups->copy($targeting_id_temp, $targeting_id);
   	}
   
   	public function schedule_change() {
      	$schedule_params = $this->input->post('schedule_params');
      	if ($schedule_params) {
      		$schedule_params = json_decode($schedule_params);
      		if(!is_null($schedule_params)) {
            	$this->new_campaign->init_storage($this->id_xml);
            	$this->new_campaign->set_schedule($schedule_params);
            	$this->new_campaign->save_data();
            	echo "OK";
      		} else {
      			echo "ERROR: is_null(schedule_params)";
      		}
      	}
   	}
	
	/**
	 * Отображение страницы с предпросмотром созданных объявлений
	 *
	 * @param string $campaign_type тип создаваемой кампании
	 */
	public function index($campaign_type) {
	  	$tmp = $this->campaigns->get_list(array('id_advertiser' => $this->user_id));
      
      	$campaigns_list[''] = __("Select campaign to load targeting from");
      	foreach ($tmp as $key => $value) {
         	$campaigns_list[type_to_str($key,'textcode')] = type_to_str($value,'encode');
      	}
      
		//Рекламодатель может выбирать для рекламы только те страны, которые не заблокированы в системе? тогда get_list(array('filter' => 'enabled'))
      	$this->targeting_all_list->countries = $this->countries->get_list(array('filter' => 'enabled'));
		
		$error_message = '';
		
		$this->set_campaign_type($campaign_type);
		$this->setCurrentStep(uri_string());
		
	   	if (!$this->input->post('confirm_creation')) {
         	$targeting_id = $this->new_campaign->get_targeting();
         	$targeting_id_temp = $this->new_campaign->get_targeting(true);
         	$this->targeting_groups->copy($targeting_id,$targeting_id_temp);
      	}
		
		//изменение типа таргетинга в случае необходимости
		$this->set_targeting_type();
		$this->new_campaign->init_storage($this->id_xml);
      	$ads_list = $this->new_campaign->get_ads_list();
      
      	$ads_preview = '';
		
		$this->db->select('cs.*, ft.name title_font_name, ft2.name text_font_name, ft3.name url_font_name', false);
		$this->db->from('color_schemes cs');
		$this->db->join('fonts ft', 'ft.id_font = cs.title_id_font');
		$this->db->join('fonts ft2', 'ft2.id_font = cs.text_id_font');
		$this->db->join('fonts ft3', 'ft3.id_font = cs.url_id_font');

		$query = $this->db->get();
		$row = $query->row();
		
      	foreach ($ads_list as $ad_id => $ad) {
      		if ('text' == $ad->ad_type) {
            	if ('cpa' != $campaign_type) {
      				$ad_preview = $this->parser->parse(
      					'common/text_ad_example.html',
      					array(
      						'TITLE' => type_to_str($ad->title, 'encode'),
      						'DESCRIPTION' => type_to_str($ad->description1, 'encode'),
      						'DESCRIPTION2' =>  type_to_str($ad->description2, 'encode'),
      						'DISPLAY_URL' => $ad->display_url,
      						'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url,
							'BACKGROUND_COLOR'  => $row->background_color,
							'BORDER_COLOR'  => $row->border_color,
							'TITLE_COLOR'  => $row->title_color,
							'TITLE_FONT_NAME'  => $row->title_font_name,
							'TITLE_FONT_SIZE'  => $row->title_font_size,
							'TITLE_FONT_STYLE'  => $row->title_font_style,
							'TITLE_FONT_WEIGHT'  => $row->title_font_weight,
							'TEXT_COLOR' => $row->text_color,
							'TEXT_FONT_NAME'  => $row->text_font_name,
							'TEXT_FONT_SIZE'  => $row->text_font_size,
							'TEXT_FONT_STYLE'  => $row->text_font_style,
							'TEXT_FONT_WEIGHT'  => $row->text_font_weight,
							'URL_COLOR' => $row->url_color,
							'URL_FONT_NAME'  => $row->url_font_name,
							'URL_FONT_SIZE'  => $row->url_font_size,
							'URL_FONT_STYLE'  => $row->url_font_style,
							'URL_FONT_WEIGHT'  => $row->url_font_weight
      					),
      					FALSE
      				);
      			} else {
               		$ad_preview = $this->parser->parse(
               			'common/text_ad_cpa_example.html',
               			array(
               				'TITLE' => type_to_str($ad->title,'encode'),
               				'QUESTION' => type_to_str($ad->description1,'encode'),
               				'QUESTION2' => type_to_str($ad->description2,'encode'),
               				'EMAIL_TITLE' => $ad->email_title
               			),
               			FALSE
               		);
            	}  
         	} elseif('image' == $ad->ad_type) {
      			list($img_w, $img_h, $type, $attr) = getimagesize($this->config->item('path_to_campaign_creation_images').$ad->image_id);
            	if (13 == $type) { //Для флэша используем специальный шаблон
                	$ad_preview = $this->parser->parse(
                		'common/image_ad_swf_example.html',
                		array(
                			'TITLE' => type_to_str($ad->title,'encode'),
                			'ID_IMAGE' => $ad->image_id,
                			'ID_SWF_CONTAINER' => str_replace('.','',$ad->image_id),
                			'IMG_W' => $img_w,
                			'IMG_H' => $img_h,
                			'BGCOLOR' => $ad->bgcolor,
                			'DISPLAY_URL' => $ad->display_url,
                			'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url
                		),
                		FALSE
                	);
            	} else {
	      			$ad_preview = $this->parser->parse(
	      				'common/image_ad_example.html',
	      				array(
	      					'TITLE' => type_to_str($ad->title, 'encode'),
	      					'ID_IMAGE' => $ad->image_id,
	      					'DISPLAY_URL' => $ad->display_url,
	      					'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url
	      				),
	      				FALSE
	      			);
            	}
      		} 
      		$ads_preview.= $this->parser->parse('advertiser/manage_ads/campaigns/creation/review_selections/ad_box.html',array('AD_PREVIEW' => $ad_preview, 'AD_ID' => $ad_id),FALSE);
      	}
      	$campaign_main_info = $this->new_campaign->get_name_date();

   
 
      	//определение настроек таргетинга
      	$targeting_info = new Campaign_Targeting();
      
      	$id_targeting = $this->new_campaign->get_targeting(true);
      	$targeting_type = $this->new_campaign->get_targeting_type();
          
      	$targeting_group_countries = $this->targeting_groups->get_group_list($id_targeting,'countries');
      	foreach ($targeting_group_countries as $country) {
      		$targeting_info->countries[] = $country['value'];
      	}
	    if (0 == count($targeting_info->countries)) {
	    	foreach ($this->targeting_all_list->countries as $iso => $country) {
	    		$targeting_info->countries[] = $iso;
	       	}
	  	}
           
      	$group_name = $this->new_campaign->get_group_name();
      
      	$schedule = $this->new_campaign->get_schedule();
      	
      	if ('cpm_flatrate' == $campaign_type) {
         	$added_sites_channels = $this->new_campaign->get_sites_channels(array('status' => 'all'));
	      	$added_sites_channels_ids = array();
	      	foreach ($added_sites_channels as $key => $value) {
	         	$added_sites_channels_ids[] = $key;
	      	}
	      
		   	if (count($added_sites_channels_ids) > 0) {
		   		$this->load->model('channel');
	         	$params = array (
	         		'fields' => 'id_site_channel, channels.ad_type, channels.name as channel_name,'.
	         					' sites.url as site_url, sites.name as site_name, sites.id_site,'.
	         					' channels.id_channel, dimensions.name as dimension_name, channels.id_dimension,'.
	         					' dimensions.width, dimensions.height',
	         		'disable_site_ordering' => true,
	         		'order_by' => 'site_url',//$this->table_builder->sort_field,
	         		'order_direction' => 'asc',//$this->table_builder->sort_direction,
	         		'site_channel_id_filter' => $added_sites_channels_ids
	         	);
	         	$sites_channels_array = $this->channel->get_sites_channels($params);
	      	} else {
	      		$error_message = __("At least one channel must be added to campaign.");
	         	$sites_channels_array = array();
	      	}
	      
	      	$this->table_builder->clear ();
	      	$this->table_builder->insert_empty_cells = false;
	      
	      	$data_rows_conut = sizeof ( $sites_channels_array );
	      
	      	$this->table_builder->add_attribute ( 'class', 'xTable' );
	      
	      	$this->table_builder->set_cell_content ( 0, 0, __('Site'));
	      	$this->table_builder->set_cell_content ( 0, 1, __('Channel'));
	      
	      	$this->table_builder->add_row_attribute(0,'class', 'th');
	      	$this->table_builder->add_col_attribute(0, 'class', 'simpleTitle');
	      	$this->table_builder->add_col_attribute(1, 'class', 'simpleTitle');
	      
	      	$row_counter = 0;
	      	for($i = 0; $i < $data_rows_conut; $i ++) {
	         	$row_counter++;
	         	$this->table_builder->set_cell_content ( $row_counter, 0, type_to_str($sites_channels_array [$i] ['site_name'],'encode').' (<a target="_blank" href="http://'.$sites_channels_array [$i] ['site_url'].'">'.$sites_channels_array [$i] ['site_url'].'</a>)');
	         	$this->table_builder->set_cell_content ( $row_counter, 1, type_to_str($sites_channels_array [$i] ['channel_name'],'encode'));
	      	}
	      
	      	if (0 == $data_rows_conut) {
	         	$this->table_builder->set_cell_content (1, 0,__('Records not found'));
	         	$this->table_builder->cell(1, 0)->add_attribute('colspan',2);
	         	$this->table_builder->cell(1, 0)->add_attribute('class','nodata');
	      	}
	      
	      	$channels_table = $this->table_builder->get_html ();
	       
            // Проверяем, все ли нормально у кампании?
         	$error_message = $this->new_campaign->check_ads();
	      
	      	if (('' == $error_message)&&($this->input->post('confirm_creation'))) {
	      		$id_campaign = $this->create_campaign('cpm_flatrate');
	      		if (is_numeric($id_campaign)) {
	      		   redirect('advertiser/manage_ads/campaign/'.type_to_str($id_campaign,'textcode'));
	      		   return;
	      		}
	      		$error_message = $id_campaign;
	      	}
	      
			$data = array(
				'ERROR_MESSAGE' => ('' != $error_message)?$this->parser->parse('advertiser/manage_ads/campaigns/creation/review_selections/error.html',array('ERROR' => $error_message),FALSE):'',
				'CAMPAIGN_NAME'  => htmlentities($campaign_main_info['name'],ENT_COMPAT,"utf-8"),
				'TARGETING_INFO' => json_encode($targeting_info),
				'SCHEDULE'       => json_encode($schedule),
				'TARGETING_ALL_LIST' =>  json_encode($this->targeting_all_list),
				'CAMPAIGNS_LIST' => json_encode($campaigns_list),
				'GROUP_NAME'  => htmlentities($group_name,ENT_COMPAT,"utf-8"),
				'CAMPAIGN_COST'      => type_to_str($this->new_campaign->get_campaign_cost(),'money'),
				'ACTIVE_TARGETING_TYPE' => $targeting_type,
				'CHANNELS_PREVIEW' => $channels_table,
				'EDIT_CAMP_MAIN_URL' => 'advertiser/create_campaign_step_main/index/cpm_flatrate',
				'EDIT_GROUP_MAIN_URL'    => 'advertiser/create_campaign_step_group_name/index/cpm_flatrate',
				'EDIT_SITES_CHANNELS_URL' => 'advertiser/create_campaign_step_choose_sites_channels/index/cpm_flatrate',
				'EDIT_SET_PRICING_URL'   => 'advertiser/create_campaign_step_set_pricing/index/cpm_flatrate',
				'EDIT_AD_URL' => 'advertiser/create_campaign_step_create_ad/index/cpm_flatrate',
				'ADS_PREVIEW' => $ads_preview ,
				'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE),
				'NUMBERFORMAT' => get_number_format(),
				'TARGETING_GROUP_ID_ENCODED' => type_to_str($id_targeting,'textcode'),
            'TIMEZONE' => sprintf(__("Scheduling runs at server time. Server is set for <b>%s</b> time zone. At the moment server time is <b>%s</b>."), gmdate('T') . date('P'), date('H:i')),
				'TIME_HOURS_FORMAT' => substr_count(get_time_format(),'A') > 0?'12':'24'
			);
			$this->_set_content(
				$this->parser->parse('advertiser/manage_ads/campaigns/creation/review_selections/body.html',
				$data,
				FALSE
			));
      	}
		$this->_display();
	}
	
	/**
	 * Создание кампании на основе данных из XML-файла
	 *
	 */
	private function create_campaign($campaign_type) {
		$this->load->model('campaigns');
		$this->load->model('groups');
		$this->load->model('ads');
		$this->load->model('schedule');
		$this->load->model('entity');
		$this->load->model('payment_gateways');
		
		if ('cpm_flatrate' == $campaign_type) {
		 	$this->load->model('sites_channels');
		}
		
		$current_balance = (float) $this->entity->ballance($this->user_id);
		$campaign_cost = $this->new_campaign->get_campaign_cost();
		if($campaign_cost>$current_balance) {
		   return "You can not create a new campaign, because you have insufficient funds in the account. Make a deposit and then try again.";
		}
		
		
		$camp_info = $this->new_campaign->get_name_date();
		
		$campaign_id = $this->campaigns->create(
			array(
				'name' => $camp_info['name'],
				'id_entity_advertiser' => $this->user_id,
				'id_campaign_type' => $campaign_type
			)
		);
		
		$targeting_id = $this->new_campaign->get_targeting();
		$targeting_id_temp = $this->new_campaign->get_targeting(true);
		$this->targeting_groups->copy($targeting_id_temp,$targeting_id);
		$this->targeting_groups->set_status($targeting_id,'active');
		$this->targeting_groups->cancel($this->user_id,$this->role,$targeting_id_temp);
	
		
		$targeting_type = $this->new_campaign->get_targeting_type();
		$this->campaigns->update($campaign_id,array('targeting_type' => $targeting_type, 'id_targeting_group' => $targeting_id));
		
		$schedule = $this->new_campaign->get_schedule();
		
		if ($schedule->schedule_is_set) {
		    $this->campaigns->set_schedule($campaign_id,$this->schedule->set($schedule->schedule));
		} else {
			$this->campaigns->set_schedule($campaign_id,NULL);
		}
		
		//добавление группы в созданную кампанию
		$group_id = $this->groups->add($campaign_id,$this->new_campaign->get_group_name());
		
		if ('cpm_flatrate' == $campaign_type) {
			//добавление каналов/сайтов в кампанию
			$sites_channels_list = $this->new_campaign->get_sites_channels(array('status' => 'new'));
			
			$added_group_sites_channels = $this->groups->add_sites_channels($group_id, $sites_channels_list); //добавили новые сайты-каналы в группу
			
			$current_balance = (float) $this->entity->ballance($this->user_id);
			$current_bonus = (float) $this->entity->bonus($this->user_id);
			$campaign_cost = $this->new_campaign->get_campaign_cost();
         
			//Списание средств за добавленные каналы
			if ($current_balance + $current_bonus >= $campaign_cost) {
				foreach ($sites_channels_list as $id_site_channel => $site_channel_info) {
					$this->sites_channels->renew(
						$added_group_sites_channels[$id_site_channel],
						false,
						$site_channel_info['ad_type'],
						$site_channel_info['id_program']
					);
				}
			} else { //Задача о рюкзаке
				$this->load->helper("knapsack");
				
				$items_costs = $this->new_campaign->get_campaign_detailed_cost();
				$sites_channels_to_pay = knapsack($items_costs,$current_balance);
			   
				//Списание средств за доступные адвертайзеру программы 
				if (count($sites_channels_to_pay) > 0) {
				   	foreach ($sites_channels_to_pay as $arr_index) {
					   	$site_channel_info = $items_costs[$arr_index];
					   	
		               $this->sites_channels->renew(
		               	$added_group_sites_channels[$site_channel_info['id_site_channel']], 
		               	false, 
		               	$site_channel_info['ad_type'],
		               	$site_channel_info['id_program']
		               );
	             	}
				}
			}
		}
		
		//добавление объявлений в группу
		$ads_list = $this->new_campaign->get_ads_list();
		
		foreach ($ads_list as $ad) {
			if ('text' == $ad->ad_type) {
				$this->ads->add(
					$group_id,
					array(
						'title' => (string)$ad->title,
						'description1' => (string)$ad->description1,
						'description2' => (string)$ad->description2,
						'display_url' => (string)$ad->display_url,
						'destination_url' => (string)$ad->destination_url,
						'destination_protocol' => (string)$ad->destination_protocol,
					),
					'text'
            );
			} else{
				$this->ads->add(
					$group_id,
					array(
						'title' => (string)$ad->title,
						'id_image' => (string)$ad->image_id,
						'id_dimension' => (string)$ad->id_dimension,
						'display_url' => (string)$ad->display_url,
						'destination_url' => (string)$ad->destination_url,
						'destination_protocol' => (string)$ad->destination_protocol,
					),
					'image'
				);
			} 
		}
		
		$this->new_campaign->free_storage($this->id_xml);
		$this->session->unset_userdata('id_xml');
		
		return $campaign_id;
	}
}