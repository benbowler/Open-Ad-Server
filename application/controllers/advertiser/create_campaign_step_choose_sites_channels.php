<?php 
if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');
require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер задания выбора сайтов/каналов группы в кампании
 *
 */
class Create_Campaign_Step_Choose_Sites_Channels extends Campaign_Wizard {

	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
	protected $hide_old_sites_channels = true;

   public $temporary = array (
      'iframe_channels_columns' => 'all'
   );  
    
	public function __construct() {
		parent::__construct();

		$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
		$this->on_submit = "onSubmit();";
		$this->cancel_creation_controller = 'advertiser/manage_ads';
		$this->_add_ajax();
		

		$this->load->library('form');
		$this->load->library('Table_Builder');
		$this->load->model('new_campaign');
		$this->load->model("category_model", "", TRUE);
		$this->load->model("channel", "", TRUE);
		$this->load->model("site", "", TRUE);

		$this->load->helper('form');
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

     	$add_site_channel = $this->session->userdata('add_site_channel');
      
      if(FALSE !== $add_site_channel) {
         $add_site_channel = json_decode($add_site_channel);
         if ('cpm_flatrate' == $add_site_channel->program_type) {
            $id_site = type_cast($add_site_channel->site_code,'textcode');
            $id_channel = type_cast($add_site_channel->channel_code,'textcode');
            $site_info = $this->site->get_info($id_site);
            $channel_info = $this->channel->get_info($id_channel);
            if (!is_null($site_info) && !is_null($channel_info)) {
            	$this->load->model('sites_channels');
            	$site_channel_info =  $this->sites_channels->get_id_site_channel($id_site, $id_channel);
            	
            	if (!is_null($site_channel_info)) {
            		$this->load->model('channel_program');
                  $default_program = $this->channel_program->get_default_program(array('id_site_channel' => $site_channel_info->id_site_channel));
                  if (!is_null($default_program)) {
                  	$this->new_campaign->init_storage($this->id_xml);
                  
                     $this->new_campaign->add_site_channel(array('id_site_channel' => $site_channel_info->id_site_channel,
                        'id_program' => $default_program['id_program'],
                        'cost' => $default_program['cost'],
                        'volume' => $default_program['volume'],
                        'ad_type' => $default_program['ad_type'],
                        'is_autorenew' => 'false'),FALSE); //не перезаписывать настройки ранее добавленного сайта-канала
                     
                     $this->new_campaign->save_data();
                     
                     $this->_set_notification(
                     sprintf(__('Channel &bdquo;%s&ldquo; at site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory was successfully appended to &bdquo;Recently added channels&ldquo; list'),
                           type_to_str($channel_info->name,'encode'), type_to_str($site_info->name,'encode'),  
                           $site_info->url));
                  } else {
                  	$this->_set_notification(
                     sprintf(__('Due to error channel &bdquo;%s&ldquo; at site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory was not appended to &bdquo;Recently added channels&ldquo; list'),
                           type_to_str($channel_info->name,'encode'), type_to_str($site_info->name,'encode'), 
                           $site_info->url), 'error' );
                  }           	
            	} else {
                 $this->_set_notification(
                 sprintf(__('Due to error channel &bdquo;%s&ldquo; at site &bdquo;%s (%s)&ldquo; choosed by you in Site Directory was not appended to &bdquo;Recently added channels&ldquo; list'),
                           type_to_str($channel_info->name,'encode'), type_to_str($site_info->name,'encode'), 
                           $site_info->url), 'error' );
              }
            }
         }
         
         $this->session->unset_userdata('add_site_channel');
      }
		
		
		$categories_tree = $this->category_model->get_html_tree();
		
		$form_data = array(
      'name' => 'choose_sites_channels_form',
      'view' => 'advertiser/manage_ads/campaigns/creation/step_choose_sites_channels/body.html',
      'redirect' => $this->get_next_step_controller(),
      'vars' => array('REVIEW_MODE' => $this->review_mode,
		                'NEXT_STEP_CONTROLLER' => $this->get_next_step_controller(),
		                'HIDE_OLD_SITES_CHANNELS' => $this->hide_old_sites_channels?'style="display:none;"':'',
                    'CATEGORIES_TREE' => $categories_tree,
                    'MONEYFORMAT' => get_money_format(), 'NUMBERFORMAT' => get_number_format(),
                    'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE)),
      "fields"      => array(
            "dummy" => array(
               "id_field_type"    => "hidden",
               "form_field_type"  => "text"
               )
               )
               );

               $content = $this->form->get_form_content('create', $form_data, $this->input, $this);
               $this->_set_content($content);
               $this->_display();
	}

	
	/**
	 * Добавление сайта-канала в группу
	 *
	 */
	public function add_site_channel() {
       	$ajax_reply = array('message' => '', 'error_flag' => false);		
	
		$id_site_channel = $this->input->post('id_site_channel');
		if ($id_site_channel) {
			$this->load->model('channel_program');
            $default_program = $this->channel_program->get_default_program(array('id_site_channel' => $id_site_channel));
            $ajax_reply['default_program'] = $default_program;
         	if ($default_program) {
         		$this->new_campaign->init_storage($this->id_xml);
         		
         		$this->new_campaign->add_site_channel(
         			array(
         				'id_site_channel' => $id_site_channel,
                        'id_program' => $default_program['id_program'],
                        'cost' => $default_program['cost'],
                        'volume' => $default_program['volume'],
                        'ad_type' => $default_program['ad_type'],
                        'is_autorenew' => 'false'
         			),
         			FALSE
         		); //не перезаписывать настройки ранее добавленного сайта-канала
                     
               	$this->new_campaign->save_data();
                     
              	$ajax_reply['message'] = __('Site/channel was sucesfully added to group');
        	} else { $ajax_reply['message'] = __('Cannot determine default cost program for this site/channel');
            	$ajax_reply['error_flag'] = true;
       		}
       	} else {
       		$ajax_reply['message'] = 'id_site_channel is not specified';
         	$ajax_reply['error_flag'] = true;
       	}
       
       echo json_encode($ajax_reply);
	}
	
   /**
    * Удаление сайта-канала из группы
    *
    */
   public function del_site_channel() {
       $ajax_reply = array('message' => '', 'error_flag' => false);     
   
       $id_site_channel = $this->input->post('id_site_channel');
       if ($id_site_channel) {
       	   $this->new_campaign->init_storage($this->id_xml);
            $this->new_campaign->del_site_channel($id_site_channel);
            $this->new_campaign->save_data();
                     
            $ajax_reply['message'] = __('Site/channel was sucesfully deleted from group');                  
       } else {
         $ajax_reply['message'] = 'id_site_channel is not specified';
         $ajax_reply['error_flag'] = true;
       }
       
       echo json_encode($ajax_reply);
   }
	
	
	/**
	 * Получение HTML-таблицы, содержащей каналы, добавленные в группу
	 *
	 */
	public function get_added_channels() {
		$this->load->model('channel_program');

		$this->new_campaign->init_storage($this->id_xml);

		$added_sites_channels = $this->new_campaign->get_sites_channels(array('status' => 'new'));

		$added_sites_channels_ids = array();

		foreach ($added_sites_channels as $key => $value) {
			$added_sites_channels_ids[] = $key;
		}

		$this->table_builder->clear ();

		$this->table_builder->set_cell_content(0, 0, __('Channel Name'));
		$this->table_builder->set_cell_content ( 0, 1, __('Action'));


		$this->table_builder->add_row_attribute(0,'class', 'th');

		$this->table_builder->add_col_attribute(0, 'class', 'simpleTitle');
		$this->table_builder->add_col_attribute(1, 'class', 'simpleTitle w50 center');

		$channels_programs = NULL;

		//установка атрибутов таблицы
		$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
		if (count($added_sites_channels_ids) > 0) {
			$this->load->model('channel');

			$params = array ('fields' => 'id_site_channel, channels.name, channels.id_channel, sites.url as site_url, sites.id_site',
         'order_by' => 'name', 'show_deleted_channels' => false,
         'order_direction' => 'asc', 'site_channel_id_filter' => $added_sites_channels_ids);

			$sites_channels_array = $this->channel->get_sites_channels($params);

			if (is_null($sites_channels_array)) {
				$sites_channels_array = array();
			} else { //формирование данных о программах, доступных в каждом канале
				$channels_programs = array();
				$this->load->model('channel_program');
				foreach ($sites_channels_array as $site_channel) {
					$available_programs = $this->channel_program->get_list(array('fields' => 'id_program, title, program_type',
	      		'id_channel' => $site_channel['id_channel'], 'order_by' => 'program_type', 'order_direction' => 'asc'));
					if (!is_null($available_programs)) {
						$channels_programs[$site_channel['id_channel']] = $available_programs;
					}
				}
			}
		} else {
			$sites_channels_array = array();
		}

		$data_rows_conut = sizeof ( $sites_channels_array );

		$site_id = '';
		$row_counter = 1;
		$this->table_builder->insert_empty_cells = false;

		for($i = 0; $i < $data_rows_conut; $i ++) {
			if ($site_id <> $sites_channels_array [$i] ['id_site']) {
				$this->table_builder->set_cell_content ( $row_counter, 0, array('src' => $this->site_url.'images/pixel.gif', 'onclick' => 'top.SwitchExpander('.$sites_channels_array [$i] ['id_site'].',this)', 'extra' => 'class="minus"'),'image');
				$this->table_builder->cell($row_counter, 0)->add_content (__('Site').':&nbsp<span class="green i">&bdquo;'.$sites_channels_array [$i] ['site_url'].'&ldquo;</span>');
				$this->table_builder->cell($row_counter,0)->add_attribute('colspan', '4');
				$this->table_builder->add_row_attribute($row_counter,'class', 'group');
				$site_id = $sites_channels_array [$i] ['id_site'];
				$row_counter++;
			}
			$this->table_builder->set_cell_content ( $row_counter, 0, type_to_str($sites_channels_array [$i] ['name'],'encode'));
			$this->table_builder->set_cell_content ( $row_counter, 1, array ('name' => __('Delete'), 'href' => '#', 'extra'=> 'class="guibutton floatl ico ico-delete" value="{@Delete@}" title="{@Delete@}" jframe="no" onclick="return deleteNewSiteChannelFromGroup('.$sites_channels_array [$i] ['id_site_channel'].')"'), 'link' );
			$this->table_builder->add_row_attribute( $row_counter, 'id', "tr{$sites_channels_array [$i] ['id_site']}");
			$this->table_builder->add_row_attribute( $row_counter, 'id_site_channel', $sites_channels_array [$i] ['id_site_channel']);
			$row_counter++;
		}

		if (0 == $data_rows_conut) {
			$this->table_builder->set_cell_content (1, 0,__('Records not found'));
			$this->table_builder->cell(1, 0)->add_attribute('colspan',4);
			$this->table_builder->cell(1, 0)->add_attribute('class','nodata');
		}

		$table = $this->table_builder->get_sort_html ();

		$this->template = "common/parent/jq_iframe.html";
		$this->_set_content($this->parser->parse('advertiser/manage_ads/campaigns/creation/step_choose_sites_channels/iframe_added_channels_table.html', array('CHANNELS_TABLE' =>  $table, 'CHANNELS_PROGRAMS' => json_encode($channels_programs)), TRUE));
		$this->_display();
	}

/**
    * Получение HTML-таблицы, содержащей каналы, добавленные ранее в группу (до момента редактирования сайтов-каналов)
    *
    */
   public function get_old_added_channels() {
      $this->load->model('channel_program');

      $this->new_campaign->init_storage($this->id_xml);

      //обновление параметров добавленных ранее сайтов-каналов   

      $action = $this->input->post('manage_action');
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
      $added_sites_channels = $this->new_campaign->get_sites_channels();

      $added_sites_channels_ids = array();

      foreach ($added_sites_channels as $key => $value) {
         $added_sites_channels_ids[] = $key;
      }

      $this->table_builder->clear ();

      $this->table_builder->set_cell_content(0, 0, __('Channel Name'));

      $this->table_builder->add_row_attribute(0,'class', 'th');

      $this->table_builder->add_col_attribute(0, 'class', 'simpleTitle');

      $channels_programs = NULL;

      //установка атрибутов таблицы
      $this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
      if (count($added_sites_channels_ids) > 0) {
         $this->load->model('channel');

         $params = array ('fields' => 'id_site_channel, channels.name, channels.id_channel, sites.url as site_url, sites.id_site',
         'order_by' => 'name', 'show_deleted_channels' => false,
         'order_direction' => 'asc', 'site_channel_id_filter' => $added_sites_channels_ids);

         $sites_channels_array = $this->channel->get_sites_channels($params);

         if (is_null($sites_channels_array)) {
            $sites_channels_array = array();
         } else { //формирование данных о программах, доступных в каждом канале
            $channels_programs = array();
            $this->load->model('channel_program');
            foreach ($sites_channels_array as $site_channel) {
               $available_programs = $this->channel_program->get_list(array('fields' => 'id_program, title, program_type',
               'id_channel' => $site_channel['id_channel'], 'order_by' => 'program_type', 'order_direction' => 'asc'));
               if (!is_null($available_programs)) {
                  $channels_programs[$site_channel['id_channel']] = $available_programs;
               }
            }
         }
      } else {
         $sites_channels_array = array();
      }

      $data_rows_conut = sizeof ( $sites_channels_array );

      $site_id = '';
      $row_counter = 1;
      $this->table_builder->insert_empty_cells = false;

      for($i = 0; $i < $data_rows_conut; $i ++) {
         if ($site_id <> $sites_channels_array [$i] ['id_site']) {
            $this->table_builder->set_cell_content ( $row_counter, 0, array('src' => $this->site_url.'images/pixel.gif', 'onclick' => 'top.SwitchExpander('.$sites_channels_array [$i] ['id_site'].',this)', 'extra' => 'class="minus"'),'image');
            $this->table_builder->cell($row_counter, 0)->add_content (__('Site').':&nbsp<span class="green i">&bdquo;'.$sites_channels_array [$i] ['site_url'].'&ldquo;</span>');
            $this->table_builder->cell($row_counter,0)->add_attribute('colspan', '1');
            $this->table_builder->add_row_attribute($row_counter,'class', 'group');
            $site_id = $sites_channels_array [$i] ['id_site'];
            $row_counter++;
         }
         $this->table_builder->set_cell_content ( $row_counter, 0, type_to_str($sites_channels_array [$i] ['name'],'encode'));
         $this->table_builder->add_row_attribute( $row_counter, 'id', "tr{$sites_channels_array [$i] ['id_site']}");
         $this->table_builder->add_row_attribute( $row_counter, 'id_site_channel', $sites_channels_array [$i] ['id_site_channel']);
         $row_counter++;
      }

      if (0 == $data_rows_conut) {
         $this->table_builder->set_cell_content (1, 0,__('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan',1);
         $this->table_builder->cell(1, 0)->add_attribute('class','nodata');
      }

      $table = $this->table_builder->get_sort_html ();

      $this->template = "common/parent/jq_iframe.html";
      $this->_set_content($this->parser->parse('advertiser/manage_ads/campaigns/creation/step_choose_sites_channels/iframe_added_channels_table.html', array('CHANNELS_TABLE' =>  $table, 'CHANNELS_PROGRAMS' => json_encode($channels_programs)), TRUE));
      $this->_display();
   }
	
	/**
	 * Ответ на AJAX-запрос поиска сайтов по имени
	 *
	 */
	public function search_by_name() {
		$search_mask = $this->input->post('mask');

		$this->load->model('site');

		echo json_encode(array('list' => $this->site->get_list(array('fields' => 'sites.id_site, url, sites.name', 'order_by' => 'name', 'title_hostname_filter' => $search_mask) )));
	}

	public function get_sites_by_category() {
		$id_category = $this->input->post('id_category');
		echo json_encode(array('html_table' => $id_category ));
	}

	/**
	 * Получение таблицы сайтов по указанным критериям
	 *
	 */
	public function sites_table() {
		$this->load->model('channel_program');
		
		$filter_mode = $this->input->post('filter_mode');
		$this->load->helper('periods_helper');

		$this->table_builder->clear ();
		$this->table_builder->init_sort_vars('iframe_channels', 'channel_name', 'asc');
		$this->table_builder->sorted_column(0,'channel_name',"Channel Name",'asc');
		$this->table_builder->sorted_column(1,'id_dimension','Format','asc');
		$this->table_builder->set_cell_content ( 0, 2, __('Cost Model') );

		$this->table_builder->sorted_column(3, 'min_cpm_volume', __('Minimal CPM Package (Impressions)'),'asc' );
		$this->table_builder->sorted_column(4, 'min_cpm_cost_text', __('CPM Cost (Text Ads)'),'asc' );
		$this->table_builder->sorted_column(6, 'min_cpm_cost_image', __('CPM Cost (Text & Image)'),'asc' );

		$this->table_builder->sorted_column(8, 'min_flat_rate_volume', __('Minimal Flat Rate Package (Days)'),'asc' );
		$this->table_builder->sorted_column(9, 'min_flat_rate_cost_text', __('Flat Rate Cost (Text Ads)'),'asc' );
		$this->table_builder->sorted_column(11, 'min_flat_rate_cost_image', __('Flat Rate Cost (Text & Image)'),'asc' );

		$this->table_builder->sorted_column(13,'impressions','Traffic Volume','asc');
		$this->table_builder->set_cell_content ( 0, 14, __('Action') );

		$this->table_builder->cell(0,2)->add_attribute('class', 'simpleTitle');
		$this->table_builder->cell(0,14)->add_attribute('class', 'simpleTitle');

		$this->table_builder->add_col_attribute(0, 'class', '');
		$this->table_builder->add_col_attribute(1, 'class', 'w80 center');
		$this->table_builder->add_col_attribute(2, 'class', 'w80 center');
		$this->table_builder->add_col_attribute(3, 'class', 'center');
		$this->table_builder->add_col_attribute(4, 'class', 'center');
		$this->table_builder->add_col_attribute(6, 'class', 'center');
		$this->table_builder->add_col_attribute(8, 'class', 'center');
		$this->table_builder->add_col_attribute(9, 'class', 'center');
		$this->table_builder->add_col_attribute(11, 'class', 'center');
		$this->table_builder->add_col_attribute(13, 'class', 'center');
		$this->table_builder->add_col_attribute(14, 'class', 'center');


		$this->table_builder->add_row_attribute(0,'class', 'th f9px');

		$form_data = array( 
			"name" => "iframe_channels_form",
			"view" => "admin/adplacing/manage_sites_channels/choose_channels/iframe_channels_table.html",
			'fields' => array(
				'filter_mode' => array(
					'id_field_type' => 'string',
					'form_field_type' => 'hidden',
					'default' => $filter_mode
				)
			)
		);

		switch ($filter_mode) {
			case 'by_category':
				$id_category = $this->input->post('id_category');
				if ($id_category) {
					$form_data['fields']['id_category'] = array(
						'id_field_type' => 'int',
						'form_field_type' => 'hidden',
						'default' => $id_category
					);

					$channels_array = $this->channel->get_sites_channels(
						array(
							'fields' => 'id_site_channel, channels.ad_type, channels.name as channel_name,'.
										' sites.url as site_url, sites.id_site,'.
										' channels.id_channel, channels.id_dimension, dimensions.width,'.
										' dimensions.height, SUM(impressions) as impressions',
							'category_id_filter' => $id_category,
							'show_deleted_channels' => false,
							'order_by' => $this->table_builder->sort_field,
							'order_direction' => $this->table_builder->sort_direction,
							'hide_wo_programs' => true,
							'date_filter' => data_range(array('mode'=>'select','period'=>'lastmonth')),
                     'status' => 'active'
						)
					);
				} else {
					$channels_array = array();
				}
				break;
				
			case 'by_price':
				$price = $this->input->post('price');
				if ($price) {
					$price = type_cast($price, 'float');
				} else {
					$price = 0;
				}

				$price_program = $this->input->post('price_program');
				$ads_type = $this->input->post('ads_type');

				if ($price && $price_program) {
					$form_data['fields']['price'] = array(
						'id_field_type' => 'positive_float',
						'form_field_type' => 'hidden',
						'default' => type_to_str($price, 'float')
					);
					
					$form_data['fields']['price_program'] = array(
						'id_field_type' => 'string',
						'form_field_type' => 'hidden',
						'default' => $price_program
					);
					
					$form_data['fields']['ads_type'] = array(
						'id_field_type' => 'string',
						'form_field_type' => 'hidden',
						'default' => $ads_type
					);

					$channels_array = $this->channel->get_sites_channels(
						array(
							'fields' => 'id_site_channel, channels.ad_type, channels.name as channel_name, '.
										'sites.url as site_url, sites.id_site, channels.id_channel, '.
										'channels.id_dimension, dimensions.width, dimensions.height, '.
										'SUM(impressions) as impressions',
							'show_deleted_channels' => false,
							'order_by' => $this->table_builder->sort_field,
							'order_direction' => $this->table_builder->sort_direction,
							'price_filter' => array(
								'price' => $price, 
								'price_program' => $price_program,
								'ads_type' => $ads_type
							),
							'hide_wo_programs' => true,
							'date_filter' => data_range(array('mode'=>'select','period'=>'lastmonth'))
						)
					);
				} else {
					$channels_array = array();
				}
				break;
			}

			//установка атрибутов таблицы
			$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here

			if (is_null($channels_array)) {
				$channels_array = array();
			}

			$data_rows_conut = sizeof ( $channels_array );

			$this->table_builder->insert_empty_cells = false;
			$row_counter = 1;
			$site_id = '';

			for($i = 0; $i < $data_rows_conut; $i ++) {
				if (!$this->channel_program->get_default_program(array('id_site_channel' => $channels_array[$i]['id_site_channel']))) {
					continue; 
				}
							
				if ($site_id <> $channels_array [$i] ['id_site']) {
					$this->table_builder->set_cell_content ( $row_counter, 0, array('src' => $this->site_url.'images/pixel.gif', 'onclick' => 'top.SwitchExpander('.$channels_array [$i] ['id_site'].',this)', 'extra' => 'class="minus"'),'image');
					$this->table_builder->cell($row_counter, 0)->add_content (__('Site').':&nbsp<span class="green i">&bdquo;'.$channels_array [$i] ['site_url'].'&ldquo;</span>');
					$this->table_builder->cell($row_counter,0)->add_attribute('colspan', 15);
					$this->table_builder->add_row_attribute($row_counter,'class', 'group');
					$site_id = $channels_array [$i] ['id_site'];
					$row_counter++;
				}
				$this->table_builder->set_cell_content ( $row_counter, 0, type_to_str($channels_array [$i] ['channel_name'],'encode'));

				$allowedTypes = explode(',', $channels_array[$i]['ad_type']);
				$this->table_builder->set_cell_content($row_counter, 1, '');
	
	   			if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
	   				$ico_path = $this->site_url . 'images/smartppc6/icons/script_code.png';
	   				$hint_title = __('Text Ad') . ' (' . $channels_array[$i]['width'] . '&times;' . $channels_array[$i]['height'] . ')';
	   				$img_prefix = 'txt_';
	   				$this->table_builder->cell($row_counter, 1)->add_content(
	   					array(
	   						'src' => $ico_path,
	   						'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
	   					),
	   					'image'
					);
	   			}
	   			
	   			if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
	   				$ico_path = $this->site_url . 'images/smartppc6/icons/image.png';
	   				$hint_title = __('Image Ad') . ' (' . $channels_array[$i]['width'] . '&times;' . $channels_array[$i]['height'] . ')';
	   				$img_prefix = 'img_';
	   				$this->table_builder->cell($row_counter, 1)->add_content(
	   					array(
	   						'src' => $ico_path, 
	   						'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
	   					), 
	   					'image'
	   				);
	   			}
	   			
				$this->table_builder->cell($row_counter, 1)->add_content($channels_array [$i]['width'].'&times;'.$channels_array [$i]['height'],'','<br/> ');


				$program_type = "";
				$min_volume_cpm = "";
				$min_volume_flat_rate = "";
				
				if (!is_null($channels_array[$i]['min_cpm_volume'])) {
					$program_type .= __('CPM');
					$this->table_builder->set_cell_content ( $row_counter, 3, type_to_str($channels_array[$i]['min_cpm_volume'],'integer'));
				
					if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
						$this->table_builder->set_cell_content ( $row_counter, 4, type_to_str($channels_array[$i]['min_cpm_cost_text'],'money'));
					} else {
						$this->table_builder->set_cell_content ( $row_counter, 4, '&#151;');
					}	
				
					if ((in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) && ($channels_array[$i]['min_cpm_cost_image'] > 0)) {
						$this->table_builder->set_cell_content ( $row_counter, 6, type_to_str($channels_array[$i]['min_cpm_cost_image'],'money'));
					} else {
						$this->table_builder->set_cell_content ( $row_counter, 6, '&#151;');
					}
				} else {
					$program_type .= '&#151;';
					$this->table_builder->set_cell_content ( $row_counter, 3, '&#151;');
					$this->table_builder->set_cell_content ( $row_counter, 4, '&#151;');
					$this->table_builder->set_cell_content ( $row_counter, 6, '&#151;');
				}

				if (!is_null($channels_array[$i]['min_flat_rate_volume'])) {
					$program_type .= ' / '.__('Flat Rate');
				
					$this->table_builder->set_cell_content ( $row_counter, 8, type_to_str($channels_array[$i]['min_flat_rate_volume'],'integer'));
				
					if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
						$this->table_builder->set_cell_content ( $row_counter, 9, type_to_str($channels_array[$i]['min_flat_rate_cost_text'],'money'));
					} else {
						$this->table_builder->set_cell_content ( $row_counter, 9, '&#151;');
					}

					if ((in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) && ($channels_array[$i]['min_flat_rate_cost_image'] > 0)) { 
						$this->table_builder->set_cell_content ( $row_counter, 11, type_to_str($channels_array[$i]['min_flat_rate_cost_image'],'money'));
					} else {
						$this->table_builder->set_cell_content ( $row_counter, 11, '&#151;');
					}

				} else {
					$program_type .= ' / &#151;';
					$this->table_builder->set_cell_content ( $row_counter, 8, '&#151;');
					$this->table_builder->set_cell_content ( $row_counter, 9, '&#151;');
					$this->table_builder->set_cell_content ( $row_counter, 11, '&#151;');
				}

				$this->table_builder->set_cell_content ( $row_counter, 2, $program_type);

				$this->table_builder->set_cell_content ( $row_counter, 13, type_to_str($channels_array[$i]['impressions'],'impressions'));
				$this->table_builder->set_cell_content ( $row_counter, 14, array ('name' => __('Add'), 'href' => '#', 'extra' => 'value="{@Add@}" title="{@Add@}" class="guibutton floatl ico ico-plusgreen" jframe="no" onclick="return addSiteChannelToGroup('.$channels_array [$i] ['id_site_channel'].');"' ), 'link' );
				$this->table_builder->add_row_attribute( $row_counter, 'id', "tr{$channels_array [$i] ['id_site']}");
				$this->table_builder->add_row_attribute( $row_counter, 'id_site_channel', $channels_array [$i] ['id_site_channel']);
				$row_counter++;
			}

			if (0 == $data_rows_conut) {
				$this->table_builder->set_cell_content (1, 0,__('Records not found'));
				$this->table_builder->cell(1, 0)->add_attribute('colspan',15);
				$this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
			}

      		// Устанавливаем возможность выбора колонок
      		$this->table_builder->use_select_columns();
      		$invariable_columns = array(
         		0, 1, 2, 14
      		);
      		$this->table_builder->set_invariable_columns($invariable_columns);
      
			$channels_table = $this->table_builder->get_sort_html ();
			$this->template = "common/parent/jq_iframe.html";
		
			$form_content = $this->form->get_form_content('create', $form_data, $this->input, $this);
      
      		$form_content = str_replace('<%CHANNELS_TABLE%>', $channels_table, $form_content);
      		$form_content = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $form_content);
      
			$this->_set_content($form_content);
			$this->_display();
		}

	public function _load($id) {
		return array();
	}

	public function _save($id, $fields) {
		return " ";
	}

	public function _create($fields) {
		if ('iframe_channels_form' == $this->input->post('active_form')) {
			return " ";
		} else {
			$this->new_campaign->init_storage($this->id_xml);
			if ((count($this->new_campaign->get_sites_channels())<1) && (count($this->new_campaign->get_sites_channels(array('status' => 'new')))<1)) {
				return "At least one channel must be added";
			}
		}
	}
	
	public function check_sites_channels_requirements() {
	  $error_flag = false;
	  $error_message = "";
	  
	  $this->new_campaign->init_storage($this->id_xml);
     if ((count($this->new_campaign->get_sites_channels())<1) && (count($this->new_campaign->get_sites_channels(array('status' => 'new')))<1)) {
     	$error_flag = true;
      $error_message = __("At least one channel must be added");
     }
     echo json_encode(array('error_flag' => $error_flag, 'error_message' => $error_message));
	}
	
   /**
    * Отправка описания выбранной категории в формате JSON.  
    *
    */
   public function ajax_get_category_description() {
   	$this->load->model('category_model');
   	
      $id = $this->input->post('id_category');
      
      $description = $this->category_model->get_description($id);
      
      if (is_null($description)) {
         $result = 'error';
         $description = '';
      } else {
         $result = 'ok';
      }
      echo json_encode(array('result' => $result, 'description' => $description));
   }
}