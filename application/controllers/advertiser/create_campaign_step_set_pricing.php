<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер задания названия группы в кампании
 *
 */
class Create_Campaign_Step_Set_Pricing extends Campaign_Wizard {

	protected $role = "advertiser";
   
   	protected $menu_item = "Manage Ads";

	protected $breadcrumb = '';

	protected $next_step = NULL;
	
	protected $hide_old_sites_channels = true;
	
	public $temporary = array(
	  	'iframe_added_channels_columns' => 'all',
	  	'iframe_recently_added_channels_columns' => 'all'
	);

	public function __construct() {
	  	parent::__construct();

	  	$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  	$this->on_submit = "onSubmit();";
	  	$this->cancel_creation_controller = 'advertiser/manage_ads';
	  	$this->_add_ajax();

	  	$this->load->library('form');
	  	$this->load->library('Table_Builder');
	  	$this->load->helper('form');
     	$this->load->model('new_campaign');
      $this->load->model('entity');
	}

	/**
	 * Отображение формы задания названия группы в кампании
	 *
	 * @param string $campaign_type тип создаваемой кампании
	 */
	public function index($campaign_type) {
	
		$error_message = '';
		
		$this->review_mode = $this->input->post('review_mode');
		
		$this->new_campaign->init_storage($this->id_xml);

		$this->set_campaign_type($campaign_type);

		$this->setCurrentStep(uri_string());
		
		if (count($this->new_campaign->get_sites_channels(array('status' => 'old'))) + count($this->new_campaign->get_sites_channels(array('status' => 'new'))) == 0) {
			$this->next_step = uri_string();
			$error_message = __('At least one Site/Channel is required');
		}
		
      $cost=$this->new_campaign->get_sites_channels_new_cost();
      $ballance = $this->entity->ballance($this->user_id);
      $temp = $cost - $ballance;
      if ($temp > 0) {
         $this->next_step = uri_string();
         $error_message = __('You can not create a new campaign, because you have insufficient funds in the account. Make a deposit and then try again.');
      }
		
		if (is_null($this->next_step)) {
		   $this->next_step = $this->get_next_step_controller();
		}
		
		$form = array(
         	"id"          => $campaign_type,
			"name"        => "set_pricing_form",
		   	"vars"        => array('REVIEW_MODE' => $this->review_mode,
		    	'HIDE_OLD_SITES_CHANNELS' => $this->hide_old_sites_channels?'style="display:none;"':'',
		      	'NUMBERFORMAT' => get_number_format(),
		      	'MONEYFORMAT' => get_money_format(),
		      	'CAMPAIGN_TYPE' => $campaign_type,
				'ERROR_MESSAGE' => ('' != $error_message)?$this->parser->parse('advertiser/manage_ads/campaigns/creation/set_pricing/error.html',array('ERROR' => $error_message),FALSE):'',
		      	'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE)),
         	"view"        => "advertiser/manage_ads/campaigns/creation/set_pricing/body.html",
		   	"redirect"    => $this->next_step,
         	"fields"      => array(
            	"daily_impressions" => array(
               		"display_name"     => __("Daily Impressions"),
               		"id_field_type"    => "string",
               		"form_field_type"  => "text",
               		"validation_rules" => "positive|integer"
            	)
         	)
      	);
      	$content = $this->form->get_form_content('modify', $form, $this->input, $this);

      $this->_set_content($content);
		$this->_display();
	}

   /**
    * Получение HTML-таблицы, содержащей каналы, ранее добавленные в группу
    *
    */
   	public function get_added_channels() {
   		$this->_get_added_channels('old');
   	}
   
   /**
    * Получение HTML-таблицы, содержащей каналы, добавленные недавно в группу
    *
    */
   	public function get_recently_added_channels() {
      	$this->_get_added_channels('new');
   	}

   /**
    * Получение HTML-таблицы, содержащей каналы, добавленные в группу
    *
    */
   	private function _get_added_channels($status_filter) {
      	$sites_channels_programs = array();
   		$this->load->model('new_campaign');
      	$this->new_campaign->init_storage($this->id_xml);

      	//обновление параметров добавленных ранее сайтов-каналов
      	$modified_sites_channels_ids = $this->input->post('modified_sites_channels_info');
      	$modified_sites_channels_ids = json_decode($modified_sites_channels_ids);
      	if (!is_null($modified_sites_channels_ids)) {
      	   	$this->load->model('channel_program');
      	   
            foreach ($modified_sites_channels_ids as $id) {
               	$site_channel_info = $this->new_campaign->get_site_channel_info($id);
               
               	if ((count($site_channel_info) > 0) && ('old' == $site_channel_info['status'])) {
               		$cost = $site_channel_info['cost'];
               		$volume = $site_channel_info['volume'];
               		$program_option = $site_channel_info['id_program'];
               		$ad_type = $site_channel_info['ad_type'];
               	} else { //для только что добавленных сайтов-каналов берем инфо о цене и объеме из базы
               		$program_option = $this->input->post('site_channel_'.$id.'_program_option');
                  	$ad_type = $this->input->post('site_channel_'.$id.'_ad_type');
                  
                  	$allowedAdTypes = explode(',', $ad_type);
                  	$textAllowed = (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedAdTypes)) ? true : false;
                  	$imageAllowed = (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) ? true : false;
                  	
               		$program_info = $this->channel_program->get_info($program_option);
               	
               		if (!is_null($program_info)) {
               			$volume = $program_info->volume;
               		
               			if ($textAllowed) {
               				$cost = $program_info->cost_text;
               			}
               			
               			if ($imageAllowed) {
               				$cost = $program_info->cost_image;
               			}
               			
               		} else {
               			continue; // не добавляем cайт-канал с неизвестной ценой 
               		}
               	}
               
               	$this->new_campaign->add_site_channel(
               		array(
               			'id_site_channel' => $id,
               			'cost' => $cost,
               			'volume' => $volume,
               			'id_program' => $program_option,
               			'ad_type' => $ad_type
               		)
               	);
            }
         	$this->new_campaign->save_data();
      	}

      	$action = $this->input->post('action');
      	if ($action) {
         	$id_site_channel = $this->input->post('id_site_channel');
         	switch ($action) {
            	case 'delete_site_channel':
               		if ($id_site_channel) {
                  		$this->new_campaign->del_site_channel($id_site_channel);
                  		$this->new_campaign->save_data();
               		}
            		break;
         	}
      	}

      	$added_sites_channels = $this->new_campaign->get_sites_channels(array('status' => $status_filter));

      	$added_sites_channels_ids = array_keys($added_sites_channels);

      	$this->table_builder->clear ();
      	if ('old' == $status_filter) {
         	$this->table_builder->init_sort_vars('iframe_added_channels', 'site_name', 'asc');
         	$delete_js_function = 'deleteSiteChannelFromGroup';
      	} else {
      		$this->table_builder->init_sort_vars('iframe_recently_added_channels', 'site_name', 'asc');
      		$delete_js_function = 'deleteRecentSiteChannelFromGroup';
      	}
      
      	$this->table_builder->sorted_column(0,'site_name',"Site Name",'asc');
      	$this->table_builder->sorted_column(1,'channel_name','Channel','asc');

      	$this->table_builder->set_cell_content(0, 2, __('Format') );
      	$this->table_builder->set_cell_content(0, 3, __('Ad Type') );
      	$this->table_builder->set_cell_content ( 0, 4, __('Cost Model') );
      	$this->table_builder->set_cell_content ( 0, 5, __('Price') );

      	$this->table_builder->sorted_column(7,'impressions','Traffic Volume','asc');

      	$this->table_builder->set_cell_content ( 0, 8, __('Action') );

      	$this->table_builder->add_row_attribute(0,'class', 'th');
      	$this->table_builder->cell(0,2)->add_attribute('class', 'simpleTitle');
      	$this->table_builder->cell(0,4)->add_attribute('class', 'simpleTitle');
      	$this->table_builder->cell(0,5)->add_attribute('class', 'simpleTitle');
      	$this->table_builder->cell(0,8)->add_attribute('class', 'simpleTitle');
      
      	//установка атрибутов таблицы
      	$this->table_builder->add_attribute ( 'class', '"xTable w100p"' ); //or set style here

      	$this->table_builder->add_row_attribute(0, 'class', 'th');

      	$this->table_builder->add_col_attribute(2, 'class', '"w100 center"');
      	$this->table_builder->add_col_attribute(3, 'class', '"w100 center"');
      	$this->table_builder->add_col_attribute(4, 'class', '"w100 center"');
  	   	$this->table_builder->add_col_attribute(5, 'class', '"w300"');
  	   	$this->table_builder->add_col_attribute(7, 'class', '"w100 center"');
      	$this->table_builder->add_col_attribute(8, 'class', '"nowrap center"');

      	if (count($added_sites_channels_ids) > 0) {
      		$this->load->helper('periods_helper');
         	$this->load->model('channel');

         	$params = array (
         		'fields' => 'id_site_channel, channels.ad_type, channels.name as channel_name,'.
         					' sites.url as site_url, sites.name as site_name, sites.id_site,'.
         					' channels.id_channel, channels.id_dimension, dimensions.width,'.
         					' dimensions.height, SUM(impressions) as impressions',
         		'disable_site_ordering' => true,
         		'order_by' => $this->table_builder->sort_field,
         		'order_direction' => $this->table_builder->sort_direction,
         		'site_channel_id_filter' => $added_sites_channels_ids,
         		'date_filter' => data_range(array('mode'=>'select','period'=>'lastmonth'))
         	);
         	$sites_channels_array = $this->channel->get_sites_channels($params);

         	if (is_null($sites_channels_array)) {
            	$sites_channels_array = array();
         	} else { //формирование данных о программах, доступных в каждом канале
         		// для новых сайтов-каналов определяем доступные программы
         		$this->load->model('channel_program');
         	
         		if ('new' == $status_filter) {
	            	$this->load->model('sites_channels');
	            	foreach ($sites_channels_array as $site_channel) {
	               		$available_programs = $this->channel_program->get_list(
	               			array(
	               				'fields' => 'id_program, title, program_type, cost_text, cost_image, '.
	               							'volume',
	                 			'id_channel' => $site_channel['id_channel'], 
	                 			'order_by' => 'title', 
	                 			'order_direction' => 'asc'
	               			)
	               		);
               			
	               		if (!is_null($available_programs)) {
	                 		$slot_info = $this->sites_channels->get_slot_info($site_channel['id_site_channel']);
	                 		$flat_rate_image_is_allowed = false;
	                 
                    		if ($slot_info['free'] == 0) {
                        		$flat_rate_is_allowed = false; 
				        	} else {
				        	  	$flat_rate_is_allowed = true;
				        	  	$flat_rate_image_is_allowed = ($slot_info['free'] == $slot_info['max']);
				        	}  
				            
	               	 		foreach ($available_programs as $key => &$available_program) {
	               	 	   		if (('Flat_Rate' == $available_program['program_type']) && (!$flat_rate_is_allowed)) {
	               	 	   			unset($available_programs[$key]);
	               	 	   		} else {	
		               	   			$available_program['cost_per_volume_text'] = type_to_str($available_program['cost_text'],'money').' / '.type_to_str($available_program['volume'],'integer').' ';
		               	   			$available_program['cost_per_volume_image'] = type_to_str($available_program['cost_image'],'money').' / '.type_to_str($available_program['volume'],'integer').' ';
		               	   	
		               	   			if ('CPM' == $available_program['program_type']) {
		               	   				$available_program['cost_per_volume_text'].= __('impressions');
		               	   				$available_program['cost_per_volume_image'].= __('impressions');
		               	   			} else {
		               	   				$available_program['cost_per_volume_text'].= __('days');
		                           		$available_program['cost_per_volume_image'].= __('days');
		               	   			}
	               	 	   		}
	               	   		}
	               	   
	                     	$sites_channels_programs[$site_channel['id_site_channel']] = array(
	                     		'programs' => $available_programs,
	                     		'restrictions' => array(
	                     			'flat_rate_image_is_allowed' => $flat_rate_image_is_allowed,
	                     			'flat_rate_is_allowed' => $flat_rate_is_allowed
	                     		)
	                     	);
	               		}
	            	}
         		}
         	}
      	} else {
         	$sites_channels_array = array();
      	}

      	$data_rows_conut = sizeof ( $sites_channels_array );

      	$row_counter = 1;
      	$this->table_builder->insert_empty_cells = false;

      	for($i = 0; $i < $data_rows_conut; $i ++) {
         	$this->table_builder->set_cell_content(
         		$row_counter, 
         		0, 
         		limit_str_and_hint($sites_channels_array [$i] ['site_name'],30).
         			' (<a target="_blank" href="http://'.
            		$sites_channels_array [$i] ['site_url'].'">'.$sites_channels_array [$i] ['site_url'].'</a>)'
            );
            
         	$this->table_builder->set_cell_content(
         		$row_counter, 
         		1, 
         		type_to_str($sites_channels_array [$i] ['channel_name'],'encode')
         	);

         	$allowedTypes = explode(',', $sites_channels_array [$i]['ad_type']);
            $sites_channels_programs[$sites_channels_array [$i]['id_site_channel']]['allowed'] = $allowedTypes;
            $sites_channels_programs[$sites_channels_array [$i]['id_site_channel']]['labels'] = array(
              'text' =>   __('Text'),
              'image' =>  __('Image')
              );
      		$this->table_builder->set_cell_content($row_counter, 2, '');
	
      		$selected_ad_type = $added_sites_channels[$sites_channels_array [$i] ['id_site_channel']]['ad_type'];
      		
	   		if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
	   			$ico_path = $this->site_url . 'images/smartppc6/icons/script_code.png';
	   			$hint_title = __('Text Ad') . ' (' . $sites_channels_array[$i]['width'] . '&times;' . $sites_channels_array[$i]['height'] . ')';
	   			$img_prefix = 'txt_';
	   			$this->table_builder->cell($row_counter, 2)->add_content(
	   				array(
	   					'src' => $ico_path,
	   					'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $sites_channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
					),
					'image'
				);
	   		}
	   			
	   		if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
	   			$ico_path = $this->site_url . 'images/smartppc6/icons/image.png';
	   			$hint_title = __('Image Ad') . ' (' . $sites_channels_array[$i]['width'] . '&times;' . $sites_channels_array[$i]['height'] . ')';
	   			$img_prefix = 'img_';
	   			$this->table_builder->cell($row_counter, 2)->add_content(
	   				array(
	   					'src' => $ico_path, 
	   					'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $sites_channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
	   				), 
	   				'image'
	   			);
	   		}
	   			
          	$this->table_builder->cell($row_counter, 2)->add_content($sites_channels_array [$i]['width'].'&times;'.$sites_channels_array [$i]['height'],'','<br/> ');


           	$selected_program_type = $this->channel_program->get_program_type($added_sites_channels[$sites_channels_array [$i] ['id_site_channel']]['id_program']);
			
         	$selected_cost_option = $added_sites_channels[$sites_channels_array [$i] ['id_site_channel']]['id_program'];
         	
         	$program_types_options = array();
         
         	if ('old' == $status_filter) {
         		$channel_program_types = array($selected_program_type);
         		$program_info = $this->channel_program->get_info($selected_cost_option);
       	
         		if (is_null($program_info)) {
         			$program_cost_options = array(
         				$selected_cost_option => array(
         					'title' => 'ERROR GETTING PROGRAM INFO', 
         					'volume' => 0, 
         					'cost' => 0
         				)
         			);
         		} else {
         	  		$cost = $added_sites_channels[$sites_channels_array [$i] ['id_site_channel']] ['cost'];
         	  		$volume = $added_sites_channels[$sites_channels_array [$i] ['id_site_channel']] ['volume'];
         	  		$program_cost_options = array(
         	  			$selected_cost_option => array(
         	  				'title' => $program_info->title, 
         	  				'volume' => $volume, 
         	  				'cost' => $cost
         	  			)
         	  		);
         		}
         	} else {
	         	$channel_program_types = $this->channel_program->get_channel_program_types($sites_channels_array [$i] ['id_channel']);
	            
	         	if (!($sites_channels_programs[$sites_channels_array [$i]['id_site_channel']]['restrictions']['flat_rate_is_allowed'])) {
		         	foreach ($channel_program_types as $key => $program_type) {
	               		if ('Flat_Rate' == $program_type) {
	               			unset($channel_program_types[$key]);
	               			break;
	               		}
	            	}
	         	} else {
	         		//Flat Rate на первое место в списке типов программ
	         		$flat_rate_index = array_search('Flat_Rate',$channel_program_types);
	         	
               		if (FALSE !== $flat_rate_index) {
               			unset($channel_program_types[$flat_rate_index]);
               			array_unshift($channel_program_types,'Flat_Rate');
               		}
	         	}
	         	
	         	$program_cost_options =  $this->channel_program->get_channel_program_options(
	         		$sites_channels_array [$i] ['id_channel'], 
	         		$selected_program_type, 
	         		$selected_ad_type
	         	);
         	}
         
         	foreach ($program_cost_options as &$cost_option) {
         		$cost_option = $cost_option['title'].' ('.type_to_str($cost_option['cost'],'money').' / '.type_to_str($cost_option['volume'],'integer');
         		if ('CPM' == $selected_program_type){
         	  		$cost_option.= ' '.__('impressions').')';
         		} else {
         			$cost_option.= ' '.__('days').')';
         		}
         	}

         	foreach ($channel_program_types as $channel_program_type) {
            	$program_types_options[$channel_program_type] = __($channel_program_type);
         	}

         	$ad_types_options = array();
         	
         	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
         		$ad_types_options['text'] = __('Text');
         	}
         	
         	if ((in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)))
         	{
         		$keys = array();
         		$labels = array();
         		
         		if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
         			$keys[] = 'text';
         			$labels[] = __('Text');
         		}
         		
         		if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
         			$keys[] = 'image';
         			$labels[] = __('Image');
         		}
         		
         		$key = implode(',', $keys);
         		$label = implode(' & ', $labels);
               $programs = $sites_channels_programs[$sites_channels_array[$i]['id_site_channel']];
               if(in_array('CPM', $program_types_options) || (isset($programs['restrictions']) && $programs['restrictions']['flat_rate_image_is_allowed'])){
         		$ad_types_options[$key] = $label;
               }
         	}
         	
         	$js = 'id_site_channel="'.$sites_channels_array [$i] ['id_site_channel'].'" id="site_channel_'.$sites_channels_array [$i] ['id_site_channel'].'_ad_type" class="w200" onchange="onChangeAdType('.$sites_channels_array [$i] ['id_site_channel'].','.$sites_channels_array [$i] ['id_channel'].');"';
         	$this->table_builder->set_cell_content ( 
         		$row_counter, 
         		3, 
         		form_dropdown(
         			'site_channel_'.$sites_channels_array [$i] ['id_site_channel'].'_ad_type',
         			$ad_types_options,
         			$selected_ad_type,
         			$js
         		)
         	);

         	$js = 'id_site_channel="'.$sites_channels_array [$i] ['id_site_channel'].'" id="site_channel_'.$sites_channels_array [$i] ['id_site_channel'].'_program_type" onchange="onChangeProgramType('.$sites_channels_array [$i] ['id_site_channel'].','.$sites_channels_array [$i] ['id_channel'].');"';
         	$this->table_builder->set_cell_content ( 
         		$row_counter, 
         		4, 
         		form_dropdown(
         			'site_channel_'.$sites_channels_array [$i] ['id_site_channel'].'_program_type',
         			$program_types_options,
         			$selected_program_type,
         			$js
         		)
         	);

         	$js  = 	'id_site_channel="'.$sites_channels_array [$i] ['id_site_channel'].
         			'" id_channel="'.$sites_channels_array [$i] ['id_channel'].
         			'" class="site_channel_program w100p" id="site_channel_'.
         			$sites_channels_array [$i] ['id_site_channel'].'_option" onchange="onChangeProgramOption('.
         			$sites_channels_array [$i] ['id_site_channel'].','.$sites_channels_array [$i] ['id_channel'].
         			');"';
         	$this->table_builder->set_cell_content (
         		$row_counter, 
         		5, 
         		form_dropdown(
         			'site_channel_'.$sites_channels_array [$i] ['id_site_channel'].'_program_option',
         			$program_cost_options,
         			$selected_cost_option,
         			$js
         		)
         	);

         	$this->table_builder->set_cell_content ( 
         		$row_counter, 
         		7, 
         		type_to_str($sites_channels_array [$i] ['impressions'],'impressions')
         	);
         
         	$this->table_builder->set_cell_content ( 
         		$row_counter, 
         		8, 
         		'<input type="button" type="button" class="guibutton floatl ico ico-delete" jframe="no" value="' . __('Delete') . '" onclick="return '.$delete_js_function.'(' . $sites_channels_array [$i] ['id_site_channel'] . ')">' 
         	);
         	
         	$this->table_builder->add_row_attribute( 
         		$row_counter, 
         		'id', 
         		"tr{$sites_channels_array [$i] ['id_site_channel']}"
         	);
         	
         	$this->table_builder->add_row_attribute( 
         		$row_counter, 
         		'id_site_channel', 
         		$sites_channels_array [$i] ['id_site_channel']
         	);
         	
         	if ('new' == $status_filter) {
         		$this->table_builder->add_row_attribute($row_counter, 'class','new_row');
         	}
         	
         	$row_counter++;
      	}

      	if (0 == $data_rows_conut) {
      		$this->table_builder->set_cell_content (1, 0,__('Records not found'));
           	$this->table_builder->cell(1, 0)->add_attribute('colspan',9);
           	$this->table_builder->cell(1, 0)->add_attribute('class','nodata');
           	$total_campaign_cost_hint_visibility = 'style="display:none;"';
      	} else { //Тotal row for new sites/channels
      		if ('new' == $status_filter) {
	      		$this->table_builder->set_cell_content ( $row_counter, 0, __('Total').':');
	      		$this->table_builder->set_cell_content ( $row_counter, 1, '&nbsp;');
	      		$this->table_builder->cell($row_counter, 0)->add_attribute('colspan',5);
	      		$this->table_builder->cell($row_counter, 1)->add_attribute('colspan',4);
	      		$this->table_builder->cell($row_counter, 1)->add_attribute('id','total_cost');
	      		$this->table_builder->cell($row_counter, 0)->add_attribute('class', 'b');
	      		$this->table_builder->cell($row_counter, 1)->add_attribute('class', 'b');
	      		$this->table_builder->cell($row_counter, 0)->add_attribute('style', 'border-top:1px solid #000000;');
	      		$this->table_builder->cell($row_counter, 1)->add_attribute('style', 'border-top:1px solid #000000;');
      		}
      		$total_campaign_cost_hint_visibility = '';
      	}

      	// Устанавливаем возможность выбора колонок
      	$this->table_builder->use_select_columns();
      	$invariable_columns = array(
         	0, 1, 3, 4, 5, 8
      	);
      	$this->table_builder->set_invariable_columns($invariable_columns);
      
      	$table = $this->table_builder->get_sort_html ();
      	$columns = $this->table_builder->get_columns_html();

      	$this->template = "common/parent/jq_iframe.html";
      
      	if ('new' == $status_filter) {
         	$iframe_form_template = 'iframe_recently_added_channels_table.html';
      	} else {
      		$iframe_form_template = 'iframe_added_channels_table.html';
      	}
         
      	$this->_set_content($this->parser->parse(
      		'advertiser/manage_ads/campaigns/creation/set_pricing/'.$iframe_form_template, 
      		array(
      			'CHANNELS_TABLE' =>  $table, 
      			'SITES_CHANNELS_PROGRAMS' => json_encode($sites_channels_programs),
      			'TOTAL_CAMPAIGN_COST_HINT_VISIBILITY' => $total_campaign_cost_hint_visibility, 
      			'COLUMNS' => $columns
      		), 
      		TRUE
      	));
      	$this->_display();
   	}

	public function _load($id) {
		//TODO - обработка $id (через него передавать тип создаваемой кампании)
      	$fields = array();
      	$fields['daily_impressions'] = $this->new_campaign->get_daily_impressions();
      	return $fields;
	}

   	public function _save($id,$fields) {
      	//TODO - обработка $id (через него передавать тип создаваемой кампании)
      	$this->new_campaign->set_daily_impressions($fields['daily_impressions']);
      	$this->new_campaign->save_data();
   	}
}