<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер выбора сайтов для группы в кампании
 *
 */
class Create_Campaign_Step_Select_Sites extends Campaign_Wizard {

	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
	protected $next_step = NULL;
	
   public $temporary = array (
      'iframe_channels_columns' => 'all'
   );  
    
	public function __construct() {
		parent::__construct();

		$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
		$this->on_submit = "onSubmit();";
		$this->cancel_creation_controller = 'advertiser/manage_ads';
		$this->_add_ajax();
		
		$this->_add_java_script('jquery.simpledialog.0.1.min');
		

		$this->load->library('form');
		$this->load->library('Table_Builder');
		$this->load->model('new_campaign');
		$this->load->model("category_model", "", TRUE);
		$this->load->model("site", "", TRUE);
		$this->load->helper('form');
		
		$this->load->library('Plugins', array(
		 	'path' => array('advertiser', 'create_campaign_step_select_sites'),
		 	'interface' => 'Sppc_Advertiser_CreateCampaignStep_SelectSites_Interface'
		));
	}

	/**
	 * Отображение формы выбора сайтов/каналов группы в кампании
	 *
	 * @param string $campaign_type тип создаваемой кампании
	 */
	public function index($campaign_type) {
		$this->set_campaign_type($campaign_type);
		$this->setCurrentStep(uri_string());
		$this->review_mode = $this->input->post('review_mode');

		$this->new_campaign->init_storage($this->id_xml);

		$add_site_channel = $this->session->userdata('add_site_channel');
		
		if(FALSE !== $add_site_channel) {
			$add_site_channel = json_decode($add_site_channel);
			if ('cpc' == $add_site_channel->program_type) {
				$id_site = type_cast($add_site_channel->site_code,'textcode');
				$site_info = $this->site->get_info($id_site);
				if (!is_null($site_info)) {
				  	if (FALSE != $this->new_campaign->add_site($id_site)) {
				  		$places = $this->new_campaign->get_places();
				  		if (false !== ($index = array_search('allsites', $places))) {
				  			unset($places[$index]);
				  		}
				  		if (!in_array('sites', $places)) {
				  			$places[] = 'sites';
				  		}
				  		$this->new_campaign->set_places($places);
				  		
	              		$this->new_campaign->save_data();
	              		$this->_set_notification(sprintf(
	              			__('Site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory was successfully appended to &bdquo;Added Sites&ldquo; list'),
	                        type_to_str($site_info->name,'encode'), 
	                        $site_info->url
	                   	));
				  	} else {
				  	  	$this->_set_notification(sprintf(
				  	  		__('Due to error site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory was not appended to &bdquo;Added Sites&ldquo; list'),
                           	type_to_str($site_info->name,'encode'), 
                           	$site_info->url), 
                       	'error' );
				  	}
				}
			}
			
			$this->session->unset_userdata('add_site_channel');
		}
		
		$categories_tree = $this->category_model->get_html_tree();
		
	   	if (is_null($this->next_step)) {
			$hide_navbar = false;
         	$this->next_step = $this->get_next_step_controller();
      	} else {
      		$hide_navbar = true;
      	}
		
		$form_data = array(
			'id'          => $campaign_type,
      		'name' => 'select_sites_form',
      		'view' => 'advertiser/manage_ads/campaigns/creation/select_sites/body.html',
      		'redirect' => $this->next_step,
      		'vars' => array(
      			'REVIEW_MODE' => $this->review_mode,
      			'CATEGORIES_TREE' => $categories_tree,
      			'MONEYFORMAT' => get_money_format(), 
      			'NUMBERFORMAT' => get_number_format()
			),
      		"fields"      => array(
            	"dummy" => array(
               		"id_field_type"    => "hidden",
               		"form_field_type"  => "text"
               	),
            	"sites_range" => array(
               		"id_field_type"    => "string",
               		"form_field_type"  => "radio"
               	)
         	)
   		);

               
      	if ($hide_navbar) {
       		$form_data['vars']['PROGRESS_BAR'] = '';
      	}
       
	   	$places = $this->new_campaign->get_places();

	   	$all_places = array_merge(array('allsites'),$this->plugins->run('getPlace',$this));
	   	
      	/*
         * По умолчанию places установлено как allsites,
         * но нужно поставить все галочки на этой странице,
         * поэтому если в плэйсах только оллсайт - убираем его и
         * заполняем всеми доступными местами
         */
      	if((!isset($this->edit_step) || !$this->edit_step) && count($places) == 1 && in_array('allsites',$places)){
        	$places = $all_places;
        	$this->new_campaign->set_places($places);
        	$this->new_campaign->save_data();
      	}
	   
	   	$sites = $this->new_campaign->get_sites();
	   
	   	$sites_count = count($sites);
	   
	   	$plugin_places = $this->plugins->run('getPlaceHtml', $this);
	   
	   	$plugin_places_html = implode($plugin_places);
   
	   	$this->places_count = count($plugin_places)+1; 	   

	   	$allplaces = ((count($places)==count($all_places)) && ($sites_count==0))?'true':'false';

	   	$content = $this->form->get_form_content('modify', $form_data, $this->input, $this);
      	$content = str_replace('<%PLUGINPLACES%>', $plugin_places_html, $content);
      	$content = str_replace('<%YOUR_BID_VALUE%>',type_to_str($this->new_campaign->get_default_bid('text'),'float'),$content);
      	$content = str_replace('<%YOUR_BID_IMAGE_VALUE%>',type_to_str($this->new_campaign->get_default_bid('image'),'float'),$content);
      	$content = str_replace('<%SITESCHECKED%>', (in_array('sites', $places) || in_array('allsites', $places))?'checked="checked"':'' ,$content);
      	$content = str_replace('<%SEARCHESCHECKED%>', in_array('searches', $places)?'checked="checked"':'' ,$content);
      
      	/* PLUGINS */
      	$content = str_replace('<%DOMAINSCHECKED%>', in_array('domains', $places)||$this->checkAllPlace($places)?'checked="checked"':'' ,$content);
      	$content = str_replace('<%INTEXTCHECKED%>', in_array('intext', $places)||$this->checkAllPlace($places)?'checked="checked"':'' ,$content);
      
      	$content = str_replace('<%ALLSITESCHECKED%>', $sites_count?'':'checked="checked"' ,$content);
      	$content = str_replace('<%CHOOSEDSITESCHECKED%>', $sites_count?'checked="checked"':'' ,$content);
      	$content = str_replace('<%ALLPLACES%>', $allplaces ,$content);
      
      
      	// есть ли сайты
      	$network_sites = $this->new_campaign->get_sites();
      	$content = str_replace('<%HAVESITES_NETWORK%>', count($network_sites)?'true':'false' ,$content);
      	$content = str_replace('<%HAVESITES_NETWORK_QTY%>', count($network_sites) ,$content);
      	$content = str_replace('<%HAVESITES_INTEXT%>', 'false' ,$content);
      	$content = str_replace('<%HAVESITES_INTEXT_QTY%>', 0 ,$content);
       
	   	$daily_budget = $this->new_campaign->get_daily_budget();
      	if (is_null($daily_budget)) {
         	$daily_budget = __('undefined');
      	} else {
         	$daily_budget = type_to_str($daily_budget,'float');
      	}
      	
       	$this->_set_content($content);
       	$this->_display();
	}
	
	public function update_default_bid() {
		$response = array(
			'messages' => array(),
			'textBid' => array(
				'status' => 'ok',
				'value' => ''
			),
			'imgBid'  => array(
				'status' => 'ok',
				'value' => ''
			),
			'sites'	  => array(
				'textBid' => array(),
				'imgBid' => array()
			)
		);
		
		$textBid = $this->input->post('text_bid');
		$imgBid = $this->input->post('img_bid');
		
		$this->new_campaign->init_storage($this->id_xml);
		$dailyBudget = $this->new_campaign->get_daily_budget();
		
		$minimalTextBid = $this->global_variables->get('DefaultTextBid', 0, '0.01');
		$minimalImgBid = $this->global_variables->get('DefaultIMageBid', 0, '0.01');
		
		if ($textBid !== false) {
			try {
				$textBid = (float) $textBid;
				
				if (!is_null($dailyBudget) && ($dailyBudget < $textBid)) {
					throw new Exception(sprintf(__("Default Bid can't be greater than Daily Budget (%s)!"),type_to_str($dailyBudget,'money')));	
				}
				
				if ($textBid < $minimalTextBid) {
					throw new Exception(sprintf(__("Default Bid for Text Ads can't be lower than minimal bid (%s)!"),type_to_str($minimalTextBid,'money')));	
				}
				
				$this->new_campaign->set_default_bid($textBid, 'text');
				
				$response['textBid']['value'] = type_to_str($textBid,'money');
				$response['messages'][] = __('Default Bid was updated succesfully!');
				
			} catch (Exception $e) {
				$response['textBid']['status'] = 'error';
				$response['messages'][] = $e->getMessage();	
			}
		}
		
		if ($imgBid !== false) {
			try {
				$imgBid = (float) $imgBid;
				
				if (!is_null($dailyBudget) && ($dailyBudget < $imgBid)) {
					throw new Exception(sprintf(__("Default Bid for Images can't be greater than Daily Budget (%s)!"),type_to_str($dailyBudget,'money')));	
				}
				
				if ($imgBid < $minimalImgBid) {
					throw new Exception(sprintf(__("Default Bid for Image Ads can't be lower than minimal bid (%s)!"),type_to_str($minimalImgBid,'money')));	
				}
				
				$this->new_campaign->set_default_bid($imgBid, 'image');
				
				$response['imgBid']['value'] = type_to_str($imgBid,'money');
				$response['messages'][] = __('Default Bid for Images was updated succesfully!');
			} catch (Exception $e) {
				$response['imgBid']['status'] = 'error';
				$response['messages'][] = $e->getMessage();	
			}
		}
		
		$this->new_campaign->save_data();
		
		$xml_sites = $this->new_campaign->get_sites();
		$added_sites_ids = array_keys($xml_sites);
		
		if ($response['textBid']['status'] == 'ok') {
			foreach ($added_sites_ids as $id_site) {
	      		if (is_null($xml_sites[$id_site]['bid'])) {
	        		$bid_position = $this->site->get_bid_position($id_site, $textBid, 'text');
	        		$response['sites']['textBid'][type_to_str($id_site,'textcode')] = array(
	            		'cell_position_text' => type_to_str($bid_position,'integer'), 
	            		'cell_position_value' => $bid_position
	            	);
	         	}
	     	}
		}
		
		if ($response['imgBid']['status'] == 'ok') {
			foreach ($added_sites_ids as $id_site) {
	      		if (is_null($xml_sites[$id_site]['bid_image'])) {
	        		$bid_position = $this->site->get_bid_position($id_site, $imgBid, 'image');
	        		$response['sites']['imgBid'][type_to_str($id_site,'textcode')] = array(
	            		'cell_position_text' => type_to_str($bid_position,'integer'), 
	            		'cell_position_value' => $bid_position
	            	);
	         	}
	     	}
		}

     	$response['messages'] = implode("\n", $response['messages']);
     	echo json_encode($response);
	}
	
	public function update_sites_bid(){
		$reply = array(
			'result' => 'ERROR', 
			'message' => '', 
			'updated_values' => array()
		);
		
	   	$updated_values = array();
		$error_flag = false;
		
		$id_sites = $this->input->post('id_sites');
		$new_bid = $this->input->post('new_bid');
		$bid_type = $this->input->post('bid_type');
		
		switch($bid_type) {
			case 'text':
			case 'image':
				break;
			default:
				$bid_type = false;
		}
		
		if ($id_sites && ($new_bid !== false) && $bid_type) {
			$this->new_campaign->init_storage($this->id_xml);
	      	$default_bid = $this->new_campaign->get_default_bid($bid_type);
			
			if ('' == $new_bid) {
				$new_bid = null;
			} else {
				$new_bid = type_cast($new_bid,'float');
			}
			
			$id_sites = json_decode($id_sites);
			
			foreach ($id_sites as $id_site) {
				$id_site_decoded = type_cast($id_site,'textcode');
				if (!$this->new_campaign->update_site_bid($id_site_decoded, $new_bid, $bid_type)) {
					$reply['message'] = __("Bid for some sites is not updated");   
				} else {
					$bid_position = $this->site->get_bid_position(
						$id_site_decoded, 
						is_null($new_bid)?$default_bid:$new_bid, 
						$bid_type
					);
					$updated_values[$id_site] = array(
						'cell_value' => is_null($new_bid)?'':type_to_str($new_bid,'float'),
						'is_default_bid' => is_null($new_bid),
						'cell_text' => is_null($new_bid)?type_to_str($default_bid,'money'):type_to_str($new_bid,'money'),
						'cell_position_value' => $bid_position,
						'cell_position_text' => type_to_str($bid_position,'integer')
					);
				}
			}
			$this->new_campaign->save_data();
			$reply['updated_values'] = $updated_values;
		} else {
			$error_flag = true;
		    $reply['message'] = __("Site, bid or bid type are not specified");	
		}
		
		if (!$error_flag) {
			$reply['result'] = 'OK';
		}
		
		echo json_encode($reply);
	}
	
	
	public function update_sites_position() {
		$reply = array(
			'result' => 'ERROR', 
			'message' => '', 
			'updated_values' => array()
		);
		
      	$updated_values = array();
      	$error_flag = false;
      	
      	$id_sites = $this->input->post('id_sites');
      	$new_position = $this->input->post('new_position');
      	$position_type = $this->input->post('position_type');
      
		switch ($position_type) {
			case 'text':
			case 'image':
				break;
			default:
				$position_type = false;
				break;
		}
      
      	$position_mismatch_flag = false;
      
      	if (is_numeric($new_position)) {
      		$new_position = intval($new_position);
      	}
      
      	if($id_sites && is_int($new_position) && $position_type) {
         	$this->new_campaign->init_storage($this->id_xml);
         	$id_sites = json_decode($id_sites);
         	$sites_count = count($id_sites);
         
         	foreach ($id_sites as $id_site) {
            	$id_site_decoded = type_cast($id_site,'textcode');
            
            	//Определение величины бида для достижения заданной позиции
            	$min_site_bid = $this->site->get_min_bid($id_site_decoded, $position_type);
            
            	$top_bids = $this->site->get_top_bids($id_site_decoded, $position_type, $new_position);
            
            	$top_bids_count = count($top_bids);
             
            	for ($i = $top_bids_count; $i > 0; $i--) { //отсеивание существующих бидов со ставкой меньше минимума для сайта
            		if ($top_bids[$i-1] >= $min_site_bid) {
            			break; 
            		} else {
            			unset($top_bids[$i-1]);
            		}
            	}
            
            	$top_bids_count = count($top_bids);
            
            	if ($top_bids_count > 0) {
               		if ($top_bids_count < $new_position) {
               			if ($top_bids[$top_bids_count-1] > $min_site_bid) {
               	   			$new_bid = $top_bids[$top_bids_count-1] - 0.01;
               	   			$bid_position = $top_bids_count+1;
               	   		} else { //определение существующего бида, со ставкой выше минимальной для сайта
               	   			$new_bid = $min_site_bid;
               	   			$bid_position = $top_bids_count;
               	   		}
               		} else {
               	  		$new_bid = $top_bids[$new_position - 1];
               	  		$bid_position = $new_position;
               		}
            	} else {
            		$new_bid = $min_site_bid; //Min site Bid
            		$bid_position = 1; //Наша ставка - единственная
            	}
            
             	if ($bid_position != $new_position) {
             		$position_mismatch_flag = true;
             	}
            
            	if (!$this->new_campaign->update_site_bid($id_site_decoded, $new_bid, $position_type)) {
               		$reply['message'] = __("Bid for some sites is not updated. ");   
            	} else {
               		$updated_values[$id_site] = array(
               			'cell_value' => type_to_str($new_bid,'float'),
               			'cell_text' => type_to_str($new_bid,'money'),
               			'cell_position_value' => $bid_position,
               			'cell_position_text' => type_to_str($bid_position,'integer')
               		);
            	}
         	}
         	$this->new_campaign->save_data();
         	$reply['updated_values'] = $updated_values;
      	} else {
          	$error_flag = true;
          	$reply['message'] = __("Site, position or position type are not specified"); 
      	}
      
      	if (!$error_flag) {
         	$reply['result'] = 'OK';
      	}
      
      	if ($position_mismatch_flag) {
      		if ($sites_count > 1) {
      	  		$reply['message'].= __("Position for some sites will not match requested due to one of the following reasons:") ."\n1. ".__("Competitors count for this site is limit your position")."; \n2. ".__("Minimal site bid and other competitors bids for this site are limit your position").".";
      		} else {
      		 	$reply['message'].= __("Position for site will not match requested due to one of the following reasons:") ."\n1. ".__("Competitors count for this site is limit your position")."; \n2. ".__("Minimal site bid and other competitors bids for this site are limit your position").".";
      		}
      	}
      
      	echo json_encode($reply);
	}
	
	/*
	 * Получение списка сайтов, добавленных в группу кампании
	 */
	public function get_added_sites() {
		$this->load->helper('periods_helper');
		
		$this->new_campaign->init_storage($this->id_xml);
		
		$default_bid = strval($this->new_campaign->get_default_bid('text'));
		$default_bid_image = strval($this->new_campaign->get_default_bid('image')); 
		
		$xml_sites = $this->new_campaign->get_sites();

		$added_sites_ids = array_keys($xml_sites);
		
		$this->table_builder->clear ();
		$this->table_builder->insert_empty_cells = false;
      	$this->table_builder->init_sort_vars('added_sites_cpc', 'url', 'asc');
		
      	$col_index = 0;
      	$col_alias = array(
      		'url' => $col_index++,
      		'impressions' => $col_index++,
      		'clicks' => $col_index++,
      		'competitors' => $col_index++,
      		'min_bid' => $col_index++,
      		'top_bid_1' => $col_index++,
      		'top_bid_2' => $col_index++,
      		'top_bid_3' => $col_index++,
      		'your_bid' => $col_index++,
      		'your_position' => $col_index++,
      		'min_bid_image' => $col_index++,
      		'top_bid_1_image' => $col_index++,
      		'top_bid_2_image' => $col_index++,
      		'top_bid_3_image' => $col_index++,
      		'your_bid_image' => $col_index++,
      		'your_position_image' => $col_index++,
      		'action' => $col_index++
      	);
      
      	$this->table_builder->sorted_column($col_alias['url'],'url',"Site",'asc');
      	$this->table_builder->sorted_column($col_alias['impressions'],'impressions',"Monthly Traffic Volume",'asc');
      	$this->table_builder->sorted_column($col_alias['clicks'],'clicks',"Clicks",'asc');
      	$this->table_builder->sorted_column($col_alias['competitors'],'competitors',"Competitors",'asc');
      	$this->table_builder->sorted_column($col_alias['min_bid'],'min_cpc',"Min Bid",'asc',1);
      	$this->table_builder->cell(1,$col_alias['min_bid'])->setRowspan(2);
      	$this->table_builder->sorted_column($col_alias['min_bid_image'],'min_cpc_image',"Min Bid",'asc',1);
      	$this->table_builder->cell(1,$col_alias['min_bid_image'])->setRowspan(2);
      
      	$this->table_builder->set_cell_content ( 0, $col_alias['min_bid'],__("Text Ads"));
      	$this->table_builder->cell(0,$col_alias['min_bid'])->setColspan(6);
  
      	$this->table_builder->set_cell_content ( 0, $col_alias['min_bid_image'],__("Image Ads"));
      	$this->table_builder->cell(0,$col_alias['min_bid_image'])->setColspan(6);
      
      	$this->table_builder->set_cell_content ( 1, $col_alias['top_bid_1'],__("Top 3 Bids"));
      	$this->table_builder->cell(1,$col_alias['top_bid_1'])->setColspan(3);
  
      	$this->table_builder->set_cell_content ( 1, $col_alias['top_bid_1_image'],__("Top 3 Bids"));
      	$this->table_builder->cell(1,$col_alias['top_bid_1_image'])->setColspan(3);
  
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_1'],"1");
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_2'],"2");
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_3'],"3");
      
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_1_image'],"1");
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_2_image'],"2");
      	$this->table_builder->set_cell_content( 2, $col_alias['top_bid_3_image'],"3");
      
      	$this->table_builder->set_cell_content ( 1, $col_alias['your_bid'],__("Your Bid"));
      	$this->table_builder->cell(1, $col_alias['your_bid'])->setRowspan(2);
      	$this->table_builder->set_cell_content ( 1, $col_alias['your_position'],__("Your Position"));
      	$this->table_builder->cell(1, $col_alias['your_position'])->setRowspan(2);
      
      	$this->table_builder->set_cell_content ( 1, $col_alias['your_bid_image'],__("Your Bid"));
      	$this->table_builder->cell(1, $col_alias['your_bid_image'])->setRowspan(2);
      	$this->table_builder->set_cell_content ( 1, $col_alias['your_position_image'],__("Your Position"));
      	$this->table_builder->cell(1, $col_alias['your_position_image'])->setRowspan(2);
      
      	$this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );
      
      	foreach ($col_alias as $alias => $index) {
      		if (!in_array($alias,array('top_bid_1','top_bid_2','top_bid_3','min_bid','your_bid','your_position',
      	                           'top_bid_1_image','top_bid_2_image','top_bid_3_image','min_bid_image','your_bid_image','your_position_image'))) 
      		{
      	  		$this->table_builder->cell(0,$index)->setRowspan(3);
      		}
      	}
      
      	//установка атрибутов таблицы
      	$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
      	$this->table_builder->add_row_attribute(0,'class', 'th f9px');
      	$this->table_builder->add_row_attribute(1,'class', 'th f9px');
      	$this->table_builder->add_row_attribute(2,'class', 'th f9px');
      	$this->table_builder->add_col_attribute($col_alias['competitors'],'class', 'center');
      
      	$row_counter = 2;
      
      	if (count($added_sites_ids) > 0) {
      		$params = array (
      			'fields' => 'sites.id_site, gs.comp_count as competitors, SUM(stat_sites.clicks) as clicks, '.
      				'SUM(stat_sites.impressions) as impressions, sites.name, sites.url, min_cpc, min_cpc_image',
            	'order_by' => $this->table_builder->sort_field,
            	'order_direction' => $this->table_builder->sort_direction, 
            	'site_id_filter' => $added_sites_ids,
            	'date_filter' => data_range(array('mode'=>'select','period'=>'lastmonth')),
            	'join_tables' => array('group_sites')
      		);
         	$sites_array = $this->site->get_list($params);
		 
         	foreach ($sites_array as $site_row) {
         		$row_counter++;

         		$this->table_builder->set_cell_content ( $row_counter, $col_alias['url'], array('name' => $site_row ['url'], 'href' => 'http://'.$site_row ['url'],'extra' => 'target="_blank"'),'link');
         		$this->table_builder->cell( $row_counter, $col_alias['url'])->add_content(limit_str_and_hint($site_row ['name'],30),'','<br>');
         		$this->table_builder->set_cell_content ( $row_counter, $col_alias['impressions'], type_to_str($site_row ['impressions'],'impressions'));
            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['clicks'], type_to_str($site_row ['clicks'],'clicks'));
            
            	if ($site_row ['competitors'] > 0){
               		$this->table_builder->set_cell_content ( $row_counter, $col_alias['competitors'], type_to_str($site_row ['competitors'],'integer'));
            	} else {
            		$this->table_builder->set_cell_content ( $row_counter, $col_alias['competitors'], '&mdash;');
            	}
            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['min_bid'], '<span class="min_bid_text">'.type_to_str($site_row ['min_cpc'],'money').'<span class="hide min_bid_value">'.type_to_str($site_row ['min_cpc'],'float').'</span></span>');
            	$this->table_builder->cell($row_counter, $col_alias['min_bid'])->add_attribute('class','border_left');
            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['min_bid_image'], '<span class="min_image_bid_text">'.type_to_str($site_row ['min_cpc_image'],'money').'<span class="hide min_image_bid_value">'.type_to_str($site_row ['min_cpc_image'],'float').'</span></span>');
            	$this->table_builder->cell($row_counter, $col_alias['min_bid_image'])->add_attribute('class','border_left');
            
            	$top_bids = $this->site->get_top_bids($site_row ['id_site'], 'text');
            	$bids_count = count($top_bids);
            	for ($i = 0; $i < 3; $i++) {
	            	if ($bids_count > $i) {             
	               		$this->table_builder->set_cell_content ( $row_counter, $col_alias['top_bid_'.($i+1)], type_to_str($top_bids[$i],'money'));
	            	} else {
	            		$this->table_builder->set_cell_content ( $row_counter, $col_alias['top_bid_'.($i+1)], '&mdash;');
	            	}
            	}
            
            	$top_image_bids = $this->site->get_top_bids($site_row ['id_site'], 'image');
            	$image_bids_count = count($top_image_bids);
            
            	for ($i = 0; $i < 3; $i++) {
               		if ($image_bids_count > $i) {             
                  		$this->table_builder->set_cell_content ( $row_counter, $col_alias['top_bid_'.($i+1).'_image'], type_to_str($top_image_bids[$i],'money'));
               		} else {
                  		$this->table_builder->set_cell_content ( $row_counter, $col_alias['top_bid_'.($i+1).'_image'], '&mdash;');
               		}
            	}
            
            	$edit_bid_action = "showSiteBidEditor('".type_to_str($site_row ['id_site'],'textcode')."','text',this);";
            
            	if (is_null($xml_sites[$site_row ['id_site']]['bid'])) {
            		$bid_for_current_site = strval($default_bid);
            		$this->table_builder->set_cell_content ( $row_counter, $col_alias['your_bid'], '<span onclick='.$edit_bid_action.' class="default_value cell_bid_text editable"> '.type_to_str(strval($bid_for_current_site),'money').'</span><span class="hide cell_bid_value"></span>');  
            	} else {
            		$bid_for_current_site = strval($xml_sites[$site_row ['id_site']]['bid']); 
            		$this->table_builder->set_cell_content ( $row_counter, $col_alias['your_bid'], '<span onclick='.$edit_bid_action.' class="custom_value cell_bid_text editable"> '.type_to_str(strval($bid_for_current_site),'money').'</span><span class="hide cell_bid_value">'.type_to_str($bid_for_current_site,'float').'</span>');
            	}
            
            
            	$edit_bid_action = "showSiteBidEditor('".type_to_str($site_row ['id_site'],'textcode')."','image',this);";
               
            	if (is_null($xml_sites[$site_row ['id_site']]['bid_image'])) {
               		$image_bid_for_current_site = strval($default_bid_image);
               		$this->table_builder->set_cell_content($row_counter, $col_alias['your_bid_image'], '<span onclick='.$edit_bid_action.' class="default_value cell_image_bid_text editable">'.type_to_str(strval($image_bid_for_current_site),'money').'</span><span class="hide cell_image_bid_value"></span>');
            	} else {
               		$image_bid_for_current_site = strval($xml_sites[$site_row ['id_site']]['bid_image']); 
               		$this->table_builder->set_cell_content($row_counter, $col_alias['your_bid_image'], '<span onclick='.$edit_bid_action.' class="custom_value cell_image_bid_text editable">'.type_to_str(strval($image_bid_for_current_site),'money').'</span><span class="hide cell_image_bid_value">'.type_to_str($image_bid_for_current_site,'float').'</span>');
            	}
            
            	$this->table_builder->cell($row_counter, $col_alias['your_bid'])->add_content('<a jframe="no" value="" onclick="updateAllSitesBid(\''.type_to_str($site_row ['id_site'],'textcode').'\',\'text\');" class="setAll" title="'. __('Apply to all sites').'"></a>');
            	$this->table_builder->cell($row_counter, $col_alias['your_bid_image'])->add_content('<a jframe="no" value="" onclick="updateAllSitesBid(\''.type_to_str($site_row ['id_site'],'textcode').'\',\'image\');" class="setAll" title="'. __('Apply to all sites').'"></a>');
            
            	//Определение позиции на основе бида для текста
            	$position_for_current_site = 1;
            	if ($bids_count > 0) {
	            	foreach ($top_bids as $bid) {
	            		if ($bid > $bid_for_current_site) {
	            			$position_for_current_site++;
	            		} else {
	            			break;
	            		}
	            	}
            	}
            	$edit_position_action = "showSitePostionEditor('".type_to_str($site_row ['id_site'],'textcode')."','text',this);";
            
            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['your_position'], '<span onclick='.$edit_position_action.' class="cell_position_text editable">'.type_to_str($position_for_current_site,'integer').'</span><span class="hide cell_position_value">'.type_to_str($position_for_current_site,'integer').'</span>'); 
            
            	$this->table_builder->cell($row_counter, $col_alias['your_position'])->add_content('<a jframe="no" value="" onclick="updateAllSitesPosition(\''.type_to_str($site_row ['id_site'],'textcode').'\',\'text\');" class="setAll" title="'. __('Apply to all sites').'"></a>');
            	$this->table_builder->cell($row_counter, $col_alias['your_position'])->add_attribute('class','border_right');
            
            	//Определение позиции на основе бида для изображения
            	$position_for_current_site = 1;
            	if ($image_bids_count > 0) {
               		foreach ($top_image_bids as $bid) {
                  		if ($bid > $image_bid_for_current_site) {
                     		$position_for_current_site++;
                  		} else {
                     		break;
                  		}
               		}
            	}
            	$edit_position_action = "showSitePostionEditor('".type_to_str($site_row ['id_site'],'textcode')."','image',this);";
            
            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['your_position_image'], '<span onclick='.$edit_position_action.' class="cell_image_position_text editable">'.type_to_str($position_for_current_site,'integer').'</span><span class="hide cell_image_position_value">'.type_to_str($position_for_current_site,'integer').'</span>'); 
            
            	$this->table_builder->cell($row_counter, $col_alias['your_position_image'])->add_content('<a jframe="no" value="" onclick="updateAllSitesPosition(\''.type_to_str($site_row ['id_site'],'textcode').'\',\'image\');" class="setAll" title="'. __('Apply to all sites').'"></a>');
            	$this->table_builder->cell($row_counter, $col_alias['your_position_image'])->add_attribute('class','border_right');

            	$this->table_builder->set_cell_content ( $row_counter, $col_alias['action'], array ('name' => __('Delete'), 'href' => '#', 'extra' => 'value="{@Delete@}" title="{@Delete@}" class="guibutton floatl ico ico-delete" jframe="no" onclick="return delSiteFromGroup(\''.type_to_str($site_row ['id_site'],'textcode').'\');"' ), 'link' );
            	$this->table_builder->add_row_attribute( $row_counter, 'id_site', type_to_str($site_row ['id_site'],'textcode'));
         	}
      	}
      
      	$this->table_builder->set_cell_content ($row_counter+1, 0,__('Records not found'));
      	$this->table_builder->cell($row_counter+1, 0)->add_attribute('colspan',count($col_alias));

      	if (count($added_sites_ids) > 0) {
      		$this->table_builder->cell($row_counter+1, 0)->add_attribute('class', 'nodata hide');
      	} else {
      		$this->table_builder->cell($row_counter+1, 0)->add_attribute('class', 'nodata');
      	}
      
		$this->template = "common/parent/jq_iframe.html";
		
		
		$form_data = array( 
			"name" => "added_sites_cpc_form",
			"view" => "advertiser/manage_ads/campaigns/creation/select_sites/jframe_added_sites.html",
			"action" => $this->site_url.$this->index_page."advertiser/create_campaign_step_select_sites/get_added_sites",
			'fields' => array()
      	);
		$form_content = $this->form->get_form_content('create', $form_data, $this->input, $this);
      
      	$form_content = str_replace('<%SITES_TABLE%>', $this->table_builder->get_sort_html (), $form_content);
      	$form_content = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $form_content);
      
      	$this->_set_content($form_content);
      	$this->_display();
	}
	

	/**
	 * Добавление сайта в группу
	 *
	 */
	public function add_site_to_group() {
		$id_site = $this->input->post('id_site');
		if (!$id_site) {
			echo json_encode(array('message' => 'Site is not specified!')); 
		} else {
			$id_site = type_cast($id_site,'textcode');
			$this->new_campaign->init_storage($this->id_xml);
			if ( FALSE == $this->new_campaign->add_site($id_site) ) {
				echo json_encode(array('message' => 'Cannot add site to group!')); 
				return;
			}
			
			/* Убрать нужно allsites*/
			$places = $this->new_campaign->get_places();
			if(in_array('allsites',$places)){
			   	unset($places[array_search('allsites',$places)]);
            	$this->new_campaign->set_places($places);
			}
			/* */
			
			$this->new_campaign->save_data();
			echo json_encode(array('message' => 'OK'));
		}
	}
	
	/**
    * Добавление сайтов в группу
    *
    */
	public function add_sites_to_group() {
	   	$sites_id = $this->input->post('sites_id');
      	if (!$sites_id) {
         	echo json_encode(array('message' => 'Sites is not specified!')); 
      	} else {
      		$sites_id = json_decode($sites_id);
      		$error_flag = false;
      	
      		$this->new_campaign->init_storage($this->id_xml);
      		foreach ($sites_id as $id_site) {
	      		$id_site = type_cast($id_site,'textcode');
	         	if ( FALSE == $this->new_campaign->add_site($id_site) ) {
	            	$error_flag = true;
	         	}
      		}
         	$this->new_campaign->save_data();
         	if ($error_flag) {
         		echo json_encode(array('message' => __('Some Sites were not added!')));
         	} else {
            	echo json_encode(array('message' => 'OK'));
         	}
      	}
	}
   /**
    * Удаление сайта из группы
    *
    */
   public function del_site_from_group() {
      $id_site = $this->input->post('id_site');
      if (!$id_site) {
         echo json_encode(array('message' => 'Site is not specified!')); 
      } else {
         $id_site = type_cast($id_site,'textcode');
         $this->new_campaign->init_storage($this->id_xml);
         $this->new_campaign->del_site($id_site);
         $this->new_campaign->save_data();
         echo json_encode(array('message' => 'OK'));
      }
   }
   
	/**
	 * Ответ на AJAX-запрос поиска сайтов по имени
	 *
	 */
	public function search_sites_by_name() {
		$search_mask = $this->input->post('mask');

		$this->load->model('site');

		$search_result = $this->site->get_list(array(
			'fields' => 'sites.id_site, url, sites.name', 
			'status' => 'active', 
			'order_by' => 'sites.name', 
			'title_hostname_filter' => $search_mask, 
			'has_cpc' => true) 
		);
		if (!is_null($search_result)) {
		 foreach ($search_result as &$site) {
		 	$site['id_site'] = type_to_str($site['id_site'],'textcode');
		 }
		}
		echo json_encode(array('list' => $search_result));
	}

	/**
	 * Search sites according to specified criteria
	 *
	 * @return void
	 */
	public function search_sites() {
		//load nessesary components
		$this->load->model( 'pagination_post' );
		
		$filter_mode = $this->input->post('filter_mode');
		$this->load->helper('periods_helper');
		
		$siteModel = new Sppc_SiteModel();
		
		//initialize table builder
		$this->table_builder->clear ();
		$this->table_builder->init_sort_vars('search_sites_cpc', 'url', 'asc');
		
		$col_index = 0;
		$col_alias = array(
			'url' => $col_index++,
			'impressions' => $col_index++,
			'clicks' => $col_index++,
			'min_bid' => $col_index++,
			'min_bid_image' => $col_index++,
			'action' => $col_index++
		);

		$this->table_builder->sorted_column($col_alias['url'],'url',"Site",'asc');
		$this->table_builder->sorted_column($col_alias['impressions'],'impressions',"Monthly Traffic Volume",'asc');
		$this->table_builder->sorted_column($col_alias['clicks'],'clicks',"Clicks",'asc');
		$this->table_builder->sorted_column($col_alias['min_bid'],'min_cpc',"Min Bid Text",'asc');
		$this->table_builder->sorted_column($col_alias['min_bid_image'],'min_cpc_image',"Min Bid Image",'asc');
		$this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );

		$this->table_builder->cell(0,$col_alias['action'])->add_attribute('class', 'simpleTitle');
		
		// set table attributes
		$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
        $this->table_builder->add_row_attribute(0,'class', 'th f9px');
        $this->table_builder->add_col_attribute($col_alias['action'],'class', 'center');
        
        $this->table_builder->insert_empty_cells = false;

        // initialize page browser
		$this->pagination_post->clear();
        $this->pagination_post->set_form_name('search_sites_cpc');
		
		//initialize from config
		$form_data = array( 
			"name" => "search_sites_cpc_form",
			"view" => "advertiser/manage_ads/campaigns/creation/select_sites/jframe_search_sites.html",
			"action" => $this->site_url.$this->index_page."advertiser/create_campaign_step_select_sites/search_sites",
			'fields' => array(
				'filter_mode' => array(
					'id_field_type' => 'string',
					'form_field_type' => 'hidden',
					'default' => $filter_mode
				)
			)
		);

		//initialize search filter object
		$dateRange = data_range(array('mode'=>'select','period'=>'lastmonth'));
		$statStartDate = new Zend_Date($dateRange['from'], Zend_Date::TIMESTAMP);
		$statEndDate = new Zend_Date($dateRange['to'], Zend_Date::TIMESTAMP);
		
		$searchFilter = new Sppc_Site_SearchFilter();
		$searchFilter->setConnectToStats(true, $statStartDate, $statEndDate)
			->setHasCpc(true);
		
		// according to filter mode configure search filter object
		switch ($filter_mode) {
			case 'all': //show all active sites
				;
            	break;
			case 'by_category': // search sites by category
				$categories = array();
				
				$id_category = $this->input->post('id_category');
				$include_subcats = $this->input->post('include_subcats');
				
				
				if ($id_category) {
					$categoryModel = new Sppc_CategoryModel();
					$category = $categoryModel->findObjectById($id_category);
					
					if (!is_null($category)) {
						$categories[] = $category;
						
						if ($include_subcats == 'true') {
							$categories = array_merge($categories, $category->getChildCategories(true));
						}
					}
					$this->table_builder->insert_empty_cells = false;
					$form_data['fields']['id_category'] = array(
						'id_field_type' => 'int',
						'form_field_type' => 'hidden',
						'default' => $id_category
					);
					
					$form_data['fields']['include_subcats'] = array(
						'id_field_type' => 'string',
						'form_field_type' => 'hidden',
						'default' => $include_subcats
					);
				} 
				
				$searchFilter->setCategories($categories);
				break;
			case 'by_name': // search sites by id
				$siteIds = array();
				
				$id_site = $this->input->post('id_site');
				
				if ($id_site) {
					$form_data['fields']['id_site'] = array(
						'id_field_type' => 'string',
						'form_field_type' => 'hidden',
						'default' => $id_site
					);
					
					$id_site = explode(',',$id_site);
					foreach ($id_site as $site) {
						$siteIds[] = type_cast($site,'textcode');
					}
					
				} 
				$searchFilter->setSiteId($siteIds);
				break;
			case 'by_price': // search sites by bid
				$bid = $this->input->post('price');
				
				$cpcBidType = $this->input->post('cpc_bid_type');
				$cpcBidType = (in_array($cpcBidType, array('text', 'image'))) ? $cpcBidType : false;
				 

				if ($bid && $cpcBidType) {
					$form_data['fields']['price'] = array(
						'id_field_type' => 'positive_float',
						'form_field_type' => 'hidden',
						'default' => type_to_str($bid, 'float')
					);
					
					$form_data['field']['cpc_bid_type'] = array(
						'id_field_type' => 'string',
						'form_field_type' => 'hidden',
						'default' => $cpcBidType
					);

					switch ($cpcBidType) {
						case 'text':
							$searchFilter->setMaximumTextBid($bid);
							break;
						case 'image':
							$searchFilter->setMaximumImageBid($bid);
							break;
					}
				}
				break;
		}
		
		$sitesCount = $siteModel->getCount($searchFilter);
		
		$this->pagination_post->set_total_records ($sitesCount);
		$this->pagination_post->read_variables('search_sites_cpc', 1, 10);
		
		$sites  = $siteModel->search(
			$searchFilter, 
			$this->table_builder->sort_field . ' ' . $this->table_builder->sort_direction,
			$this->pagination_post->get_per_page(),
			$this->pagination_post->get_page()
		);
		
		$rowsCount = count($sites);
      	
		if ($rowsCount > 0) {
			$rowCounter = 1;
			
			foreach ($sites as $site) {
				// url
				$this->table_builder->set_cell_content (  
					$rowCounter, 
					$col_alias['url'], 
					array('name' => $site->getUrl(), 'href' => 'http://'.$site->getUrl(),'extra' => 'target="_blank"'),
					'link'
				);
				$this->table_builder->add_cell_content ( 
					$rowCounter, 
					$col_alias['url'], 
					'<br>'.limit_str_and_hint($site->getName(),30)
				);
				
				// last month traffic
				$this->table_builder->set_cell_content ( 
					$rowCounter, 
					$col_alias['impressions'], 
					type_to_str($site->getImpressions(),'impressions')
				);
				
				// Clicks
				$this->table_builder->set_cell_content ( 
					$rowCounter, 
					$col_alias['clicks'], 
					type_to_str($site->getClicks(),'clicks')
				);
				
				// Min bid for text ads
				$cellContent = '
					<span class="min_bid_text">'.
						type_to_str($site->getMinCpc(),'money').'
						<span class="hide min_bid_value">'.type_to_str($site->getMinCpc(),'float').'</span>
					</span>'; 
				$this->table_builder->set_cell_content ( 
					$rowCounter, 
					$col_alias['min_bid'], 
					$cellContent);
					
				// Min bid for image ads
				$cellContent = '
					<span class="min_image_bid_text">'.
						type_to_str($site->getMinCpcImage(),'money').'
						<span class="hide min_image_bid_value">'.
							type_to_str($site->getMinCpcImage(),'float').'
						</span>
					</span>';
				$this->table_builder->set_cell_content ( 
					$rowCounter, 
					$col_alias['min_bid_image'], 
					$cellContent);
					
				// Actions
				$this->table_builder->set_cell_content ( 
					$rowCounter, 
					$col_alias['action'], 
					array (
						'name' => __('Add'), 
						'href' => '#', 
						'extra' => 'value="{@Add@}" title="{@Add@}" class="guibutton floatl ico ico-plusgreen" jframe="no" onclick="return addSiteToGroup(\''.type_to_str($site->getId(),'textcode').'\');"' ), 
						'link' 
				);
				
				// add attribute for row
				$this->table_builder->add_row_attribute( 
					$rowCounter, 
					'id_site', 
					type_to_str($site->getId(),'textcode')
				);
				
				$rowCounter++;
			}
		} else {
			$this->table_builder->set_cell_content (1, 0,__('Records not found'));
			$this->table_builder->cell(1, 0)->add_attribute('colspan',count($col_alias));
			$this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
		}

      	// Устанавливаем возможность выбора колонок
      	$this->table_builder->use_select_columns();
      	$invariable_columns = array(
        	$col_alias['url'], $col_alias['action']
      	);
      	$this->table_builder->set_invariable_columns($invariable_columns);
      
		$sites_table = $this->table_builder->get_sort_html ();
		
		$this->template = "common/parent/jq_iframe.html";
		
		$form_content = $this->form->get_form_content('create', $form_data, $this->input, $this);
		$form_content = str_replace('<%SITES_TABLE%>', $sites_table, $form_content);
		$form_content = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $form_content);
		$form_content = str_replace('<%PAGINATION%>', $this->pagination_post->create_form (), $form_content);
      
		$this->_set_content($form_content);
		$this->_display();
	}

	public function _load($id) {
		return array();
	}

	public function _save($id, $fields) {
	   	if(isset($fields['sites_range']) && $fields['sites_range'] == 'allsites'){
	       	$this->new_campaign->init_storage($this->id_xml);
	       	$this->new_campaign->del_sites();
	       	$this->new_campaign->save_data();
	   	}
			   
		return "";
	}

	public function _create($fields) {
		return " ";
	}
	
   /**
    * Отправка описания выбранной категории в формате JSON.  
    *
    */
   	public function ajax_get_category_details() {
   		$categoryDetails = new stdClass();
   		$categoryDetails->id_category = 0;
   		$categoryDetails->sites_in_cat = 0;
   		$categoryDetails->sites_in_cat_and_subcat = 0;
   		
   		try {
   			$categoryId  = $this->input->post('id_category');
   			
   			if (false === $categoryId) {
   				throw new Sppc_Exception('Category not specified');
   			}
   			
	   		$categoryModel = new Sppc_CategoryModel();
	   		$category = $categoryModel->findObjectById($categoryId);
	   		
	   		if (is_null($category)) {
	   			throw new Sppc_Exception('Specified category not found');
	   		}
	   		
	   		$categoryDetails->id_category = $category->getId();
	   		$categoryDetails->description = $category->getDescription();
	   		
	   		$siteModel = new Sppc_SiteModel();
	   		$searchFilter = new Sppc_Site_SearchFilter();
	   		$searchFilter->setHasCpc(true)->setCategories($category);
	   		
	   		$categoryDetails->sites_in_cat = $siteModel->getCount($searchFilter);

	   		$categories = array($category);
	   		$childs = $category->getChildCategories(true);
	   		$categories = array_merge($categories, $childs);
	   		
	   		$searchFilter->setCategories($categories);
	   		
	   		$categoryDetails->sites_in_cat_and_subcat = $siteModel->getCount($searchFilter);
	      
	      	echo json_encode(array('message' => 'OK', 'category_details' => $categoryDetails));
   		} catch (Exception $e) {
   			echo json_encode(array('message' => $e->getMessage(), 'category_details' => $categoryDetails));
   		}
   	}
   
   public function check_sites_requirements ($need_sites) {
     	$error_flag = false;
     	$error_message = "";
     
     	$this->new_campaign->init_storage($this->id_xml);
     	if ($need_sites && count($this->new_campaign->get_sites())<1) {
      		$error_flag = true;
      		$error_message = __("At least one site must be added!");
     	}
     	$places = array_merge($this->plugins->run('getPlace', $this), array('sites', 'allsites'));
     	$selected_places = $this->new_campaign->get_places();
     	if (count(array_intersect($places, $selected_places))<1) {
      		$error_flag = true;
      		$error_message = __("At least one place must be selected!");
     	}
     
     	echo json_encode(array('error_flag' => $error_flag, 'error_message' => $error_message));
   }

   public function toggle_place($active, $place) {
     	$this->new_campaign->init_storage($this->id_xml);
     	$places = $this->new_campaign->get_places();
        
     	if ($active) {
     	  	if ($place == 'sites') {
     	  		if ($index = array_search('allsites', $places)) {
     	  			unset($places[$index]);
     	  		}
     	  	}
        	if ($place == 'allsites') {
        		if (false !== ($index = array_search('sites', $places))) {
     	  			unset($places[$index]);
     	  		}
        	}
     	  	$places[] = $place;
        	$places = array_unique($places);
     	} else {
        	if ($place == 'sites' || $place == 'allsites') {
        	  	$this->unset_place($places, 'allsites');
           		$this->unset_place($places, 'sites');
        	} else {
        	  	$this->unset_place($places, $place);
        	}
     	}

     	$this->new_campaign->set_places($places);
     	$this->new_campaign->save_data();
   }   
   
   /**
    * Переключение для Network
    * т.е. ещё нужно удалить сайты,
    *
    * @param unknown_type $active
    */
   public function toggle_network($active){
      
     $this->new_campaign->init_storage($this->id_xml);
     $places = $this->new_campaign->get_places();
     
     print_r($places);
     
     if ($active) {
        $places[] = 'sites';
        $places = array_unique($places);
      } else {
        $this->unset_place($places, 'sites');
        $this->unset_place($places, 'allsites');
      }
      
     $this->new_campaign->set_places($places);
     $this->new_campaign->save_data();      
   }

   public function remove_sites() {
     $this->new_campaign->init_storage($this->id_xml);
     $this->new_campaign->del_sites();
     
     $places = $this->new_campaign->get_places();
     
     
     if(!in_array('sites',$places)){
         return;
     }
     
     $places[] = 'allsites';
     $this->new_campaign->set_places($places);
     
     $this->new_campaign->save_data();     
   }
   
   public function unset_place(&$places, $place) {
      
      $indexes = array_keys($places,$place);
      foreach($indexes as $key){
         unset($places[$key]);
      }
   /*
   	$index = array_search($place, $places);
   	if (is_numeric($index)) {
   		unset($places[$index]);
   	}
   */
   }
   
   
   public function selected_sites() {
      /*
     $this->new_campaign->init_storage($this->id_xml);
     $places = $this->new_campaign->get_places();
     
     //$this->unset_place($places, 'allsites');
     //$places[]='sites';
      
     $places = array_unique($places);
     $this->new_campaign->set_places($places);
     $this->new_campaign->save_data();
      */     
   }

   public function all_sites() {
     $this->new_campaign->init_storage($this->id_xml);
     $places = $this->new_campaign->get_places();
     /*
     $this->unset_place($places, 'sites');
     $places[]='allsites';
     */
     $places = array_unique($places);
     $this->new_campaign->set_places($places);
     $this->new_campaign->save_data();     
   }
   
   
   public function all_places() {
     $this->new_campaign->init_storage($this->id_xml);
     $this->new_campaign->set_places(array('allsites'));
     //$this->new_campaign->del_sites();
     $this->new_campaign->save_data();     
   }
   
   public function checkAllPlace($places){
      return count($places) == 1 && in_array('allsites',$places);   
   }
   
   public function getSitesCount(){
      
      $this->new_campaign->init_storage($this->id_xml);
      $sites = $this->new_campaign->get_sites();  

      $qty = count($sites);
      if($qty < 1 ){
         $res = __('no placements were selected');
      }elseif($qty > 1 ){
         $res = $qty . ' ' . __('sites were selected');
      }else{
         $res = '1 ' . __('site was selected');
      }

      echo $res;
   }   
   
}