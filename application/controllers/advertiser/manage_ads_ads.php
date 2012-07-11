<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для отображения таблицы объявлений выбранной группы
*
* @author Владимир Юдин
* @project SmartPPC6
*/
class Manage_ads_ads extends Parent_controller {

   protected $role = "advertiser";

   protected $menu_item = "Manage Ads";

   protected $template = "common/parent/jq_iframe.html";

   public $date_range;

   protected $date_picker = TRUE;

   public $id_group;
   
   public $id_campaign_type;
   
   protected $model;

   public  $temporary = array (
      'manadsads_filt' => 'all',
      'manadschannels_filt' => 'all',
      'manadsads_columns' => 'all',
      'manadschannels_columns' => 'all',
      'manadschannels_columnscpm_flatrate' => 'all'
   );

   /**
   * конструктор класса, вызов базового конструктора
   *
   * @return ничего не возвращает
   */
   public function Manage_ads_ads() {
		parent::Parent_controller();   	
      	$this->_load_temporary();		
   } //end Manage_ads_ads

   /**
   * совершает выбранное действие над выделенными объявлениями
   *
   * @param $action действие производимое над объявлениями (delete, pause, resume)
   * @return ничего не возвращает
   */
   public function _actions($action) {
      $id_ads = $this->input->post('id_ads');
      if (is_array($id_ads) && !empty($action)) {
         foreach ($id_ads as $code) {
            $id_ad = type_cast($code, 'textcode');
            $this->ads->action($action, $this->id_group, $id_ad);
         }
         switch ($action) {
            case 'pause':
               $this->_set_notification('Selected ads was paused.');
               break;
            case 'resume':
               $this->_set_notification('Selected ads was resumed.');
               break;
            case 'delete':
               $this->_set_notification('Selected ads was deleted.');
               break;
         }
      }
   } //end _actions

   /**
   * совершает выбранное действие над выделенными каналами
   *
   * @param $action действие производимое над каналами (delete)
   * @return ничего не возвращает
   */
   public function _actions_channels($action) {
      $id_channels = $this->input->post('id_channels');
      if (is_array($id_channels)) {
         foreach ($id_channels as $code) {
            $id_group_site_channel = type_cast($code, 'textcode');
            $this->model->action($action, $id_group_site_channel);
         }
         switch ($action) {
            case 'pause':
               $this->_set_notification('Selected sites/channels were suspended.');
               break;
            case 'resume':
               $this->_set_notification('Selected sites/channels were resumed.');
               break;
            case 'delete':
               $this->_set_notification('Selected sites/channels were deleted.');
               break;
         }
      }
   } //end _actions_channels

   /**
   * выводит таблицу объявлений
   *
   * @return string HTML-код таблицы объявлений
   */
   public function ads() {
      $this->load->library('form');
      $this->load->helper('periods');
      
      $form = array(
         'id' => 'ads',
         'name' => 'manadsads_form',
         'view' => 'advertiser/manage_ads/ads/table.html',
         'redirect' => 'advertiser/manage_ads',
         'no_errors' => 'true',
         'action' => $this->site_url.$this->index_page."advertiser/manage_ads_ads/index/".type_to_str($this->id_group, 'textcode'),
         'vars' => array(
            'DATEFILTER' => period_html('manadsads'),
            'BULK_EDITOR_CONTAINER' => $this->get_bulk_editor()
         ),
         'fields' => array(
            'filt' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'select',
               'options' => array(
                  'all' => __('all'),
                  'active' => __('pl_active'),
                  'paused' => __('pl_paused')
               )
            )
         )
      );
      data_range_fields($form, 'select', 'alltime');
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);

      $this->load->model('ads');
      
      $this->_actions($this->input->post('manadsads_action'));
      
      $this->load->library('Table_Builder');
      
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = TRUE;
      $this->table_builder->init_sort_vars('manadsads', 'id_ad', 'asc');
      
      $this->load->model('pagination_post');
      $this->pagination_post->set_form_name('manadsads');
      
      $total = $this->ads->total(
         $this->id_group,
         $this->temporary['manadsads_filt'],
         $this->date_range
      );
         
      $this->pagination_post->set_total_records($total['cnt']);
      $this->pagination_post->read_variables('manadsads', 1, $this->global_variables->get('AdsPerPage'));
      
      $ads = $this->ads->select(
         $this->id_group,
         $this->temporary['manadsads_page'],
         $this->temporary['manadsads_per_page'],
         $this->table_builder->sort_field,
         $this->table_builder->sort_direction,
         $this->temporary['manadsads_filt'],
         $this->date_range
      );
      
      $row = 0;
      
      // build column map
      $colIndex = 0;
      $colMap = array(
      	 'chkbox' 		=> $colIndex++,
      	 'id'			=> $colIndex++,
      	 'title' 		=> $colIndex++,
      	 'name' 		=> $colIndex++,
      	 'status' 		=> $colIndex++,
      	 'impressions'	=> $colIndex++,
      	 'clicks' 		=> $colIndex++,
      	 'ctr' 			=> $colIndex++,
      	 'action' 		=> $colIndex++
      );
      
      // create columns
      $this->table_builder->set_cell_content(
      	 $row, 
      	 $colMap['chkbox'], 
      	 array (
      	 	'name' => 'checkAll', 
      	 	'extra' => 'onclick="return select_all(\'manadsads\', this)"' 
      	 ), 
      	 'checkbox'
      );
      $this->table_builder->sorted_column($colMap['id'], "id_group", "ID", "asc");
      $this->table_builder->sorted_column($colMap['title'], "title", "Ad", "asc");
      $this->table_builder->sorted_column($colMap['name'], "name", "Ad Type", "asc");
      $this->table_builder->sorted_column($colMap['status'], "status", "Current Status", "asc");
      $this->table_builder->sorted_column($colMap['impressions'], "impressions", "Impressions", "desc");
      $this->table_builder->sorted_column($colMap['clicks'], "clicks", "Clicks", "desc");
      $this->table_builder->sorted_column($colMap['ctr'], "ctr", "CTR", "desc");
      $this->table_builder->set_cell_content ($row, $colMap['action'] ,__('Action'));
      
      $this->table_builder->add_row_attribute($row,'class', 'th');

      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($colMap['chkbox'], 'class', '"chkbox"');
      $this->table_builder->add_col_attribute($colMap['id'], 'class', '"w20"');
      $this->table_builder->add_col_attribute($colMap['name'], 'class', '"w100 center"');
      $this->table_builder->add_col_attribute($colMap['status'], 'class', '"w100 center"');
      $this->table_builder->add_col_attribute($colMap['impressions'], 'class', '"w100  right"');
      $this->table_builder->add_col_attribute($colMap['clicks'], 'class', '"w50 right"');
      $this->table_builder->add_col_attribute($colMap['ctr'], 'class', '"w50 right"');
      $this->table_builder->add_col_attribute($colMap['action'], 'class', '"nowrap center"');
      
      $this->table_builder->add_attribute('class', 'xTable');
      
      $clone_icon = __('Edit');      
      $image_icon = "<img class='tooltip' href='" .$this->site_url.$this->index_page. "advertiser/manage_ads_ads/ad/<%CODE%>'".
         " title='".__('Image Ad')." (<%DIMENSION%>)' ajx='true' src='".$this->site_url."images/smartppc6/icons/image.png'/><br><%DIMENSION%>";
      $text_icon = "<img class='tooltip' href='".$this->site_url.$this->index_page."advertiser/manage_ads_ads/ad/<%CODE%>'".
         " title='".__('Text Ad')."' ajx='true' src='".$this->site_url."images/smartppc6/icons/script_code.png'/>";
      $row++;
      
      $page_total = array(
         'impressions' => 0,
         'clicks' => 0,
      	 'spent' => 0
      );
   	  
      foreach ($ads as $id_ad => $ad) {
         $page_total['impressions'] += $ad['impressions'];
         $page_total['clicks'] += $ad['clicks'];         
         $page_total['spent'] += $ad['spent'];         
         
         $code = type_to_str($id_ad, 'textcode');
         
         // checkbox
         $this->table_builder->set_cell_content($row, $colMap['chkbox'],
            array (
               'name' => 'id_ads[]',
               'value' => $code,
               'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""
            ), 
            'checkbox' 
         );
         
         // id
         $this->table_builder->set_cell_content($row, $colMap['id'], $id_ad);
         
         // ad
         $this->table_builder->set_cell_content($row, $colMap['title'], $this->ads->get_text_preview($id_ad));
         
         // type
         $varname = $ad['type'].'_icon';
         if ($ad['type']=='image') {
            $dims = $this->ads->get_ad_dimensions($id_ad);
            if (is_null($dims)) {
               $dim_text = '???';
            } else {
               $dim_text = $dims['width'].'x'.$dims['height'];
            }            
            $tmp_icon = str_replace('<%DIMENSION%>', $dim_text, $$varname);
            $this->table_builder->set_cell_content($row, $colMap['name'], str_replace('<%CODE%>', $code, $tmp_icon));
         } else {
            $this->table_builder->set_cell_content($row, $colMap['name'], str_replace('<%CODE%>', $code, $$varname));
         }
         
         // status 
         $this->table_builder->set_cell_content($row, $colMap['status'], __('ads_'.$ad['status']));
         
         // impressions
         $this->table_builder->set_cell_content($row, $colMap['impressions'], type_to_str($ad['impressions'], 'integer'));
         
         // clicks
         $this->table_builder->set_cell_content($row, $colMap['clicks'], type_to_str($ad['clicks'], 'integer'));
         
         // ctr
         $this->table_builder->set_cell_content($row, $colMap['ctr'], type_to_str($ad['ctr'], 'procent'));
         
         // actions
         $this->table_builder->set_cell_content($row, $colMap['action'],
            array (
               'name' => $clone_icon,
               'href' => "#edit_ad",
               'extra' => "jframe='no' class='guibutton floatl ico ico-edit' value='$clone_icon' title='$clone_icon' onclick='top.editAd(\"$code\");'"
            ), 
            'link'
         );
         
         // add row id attribute
         $this->table_builder->add_row_attribute($row, 'id', "tr$row");
         
         $row++;
      }
      if (0 == count($ads)) {
         $this->table_builder->insert_empty_cells = false;
         $this->table_builder->set_cell_content ($row, 0, __('Records not found'));
         $this->table_builder->cell($row, 0)->add_attribute('colspan', count($colMap));
      } else {
      	 // render page summary
         $this->table_builder->set_cell_content ($row, $colMap['title'], __("Page total"));                           
         $this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($page_total['impressions'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($page_total['clicks'], 'integer'));  
         $ctr = $page_total['impressions']?$page_total['clicks']/$page_total['impressions']*100:0;                          
         $this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));                           
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');      
         
         // render summary
         $row++;
         $this->table_builder->set_cell_content ($row, $colMap['title'], __("Total"));                           
         $this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($total['impressions'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($total['clicks'], 'integer'));                                    
         $ctr = $total['impressions']?$total['clicks']/$total['impressions']*100:0;
         $this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');               

      }
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         $colMap['id'], $colMap['title'], $colMap['action']
      );
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      $html = str_replace('<%ADS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      
      $vars = array(
         'EDITNAME' => __('Manage Channels'),
         'EDITFUNCTION' => 'editChannels',
         'TAB' => 'ads',
         'GROUP' => "'".type_to_str($this->id_group, 'textcode')."'"       
      );   
      $buttons = $this->parser->parse('advertiser/manage_ads/ads/buttons.html', $vars, TRUE);      
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      return $html;
   } //end ads


   /**
   	 * Show table with selected sites
   	 * 
   	 * @return string
   	 */
   	public function channels() {
   		$this->load->library('form');
      	$this->load->helper('periods');
      	
   		$form = array(
         	'id' => 'channels',
         	'name' => 'manadschannels_form',
         	'view' => 'advertiser/manage_ads/ads/channels.html',
         	'redirect' => 'advertiser/manage_ads',
         	'no_errors' => 'true',
         	'action' => $this->site_url.$this->index_page."advertiser/manage_ads_ads/index/".type_to_str($this->id_group, 'textcode'),
         	'vars' => array(
            	'DATEFILTER' => period_html('manadschannels'),
         	),
         	'fields' => array(
            	'filt' => array(
               		'id_field_type' => 'string',
               		'form_field_type' => 'select',
               		'options' => array(
                  		'all' => __('all'),
                  		'active' => __('pl_active'),
                  		'non-active' => __('pl_non-active'),
         				   'completed' => __('pl_completed'),
                  		'paused' => __('pl_paused')
               		)
            	)
         	)
      	);
      	data_range_fields($form, 'select', 'alltime');
      	
      	$html = $this->form->get_form_content('modify', $form, $this->input, $this);
      	
      	$this->load->model("site", "", TRUE);
      	$this->load->model('sites_channels');
        $this->model = $this->sites_channels;
      	
        $this->_actions_channels($this->input->post('manadschannels_action'));
      
      	$this->load->library('Table_Builder');
      
      	$this->table_builder->clear();
      	$this->table_builder->insert_empty_cells = true;
      	$this->table_builder->init_sort_vars('manadschannels', 'id_site_channel', 'asc', FALSE, NULL, $this->id_campaign_type);
      
      	$this->load->model('pagination_post');
      	$this->pagination_post->set_form_name('manadschannels');
      
        $total = $this->sites_channels->total(
	  	 	$this->id_group,
	 		$this->temporary['manadschannels_filt'],
	  	 	$this->date_range
	  	);
	  	
	  	$this->pagination_post->set_total_records($total['cnt']);
      	$this->pagination_post->read_variables('manadschannels', 1, $this->global_variables->get('ChannelsPerPage', 0, 10));
      	
      	$channels = $this->sites_channels->select(
      		$this->user_id,
      		$this->id_group,
      		$this->temporary['manadschannels_page'],
      		$this->temporary['manadschannels_per_page'],
      		$this->table_builder->sort_field,
      		$this->table_builder->sort_direction,
      		$this->temporary['manadschannels_filt'],
      		$this->date_range
      	);
      	
      	$row = 0;
      	
   		// build column map
      	$colIndex = 0;
      	$colMap = array(
      		'chkbox' 			=> $colIndex++,
      		'id' 				=> $colIndex++,
      		'site' 				=> $colIndex++,
      		'channel' 			=> $colIndex++,
      	    'status' 			=> $colIndex++,
      		'program'			=> $colIndex++,
      		'price'				=> $colIndex++,
      		'format'			=> $colIndex++,
      		'spent' 			=> $colIndex++,
      		'impressions' 		=> $colIndex++,
      		'clicks' 			=> $colIndex++,
      		'ctr' 				=> $colIndex++/*,
      		'action' 			=> $colIndex++*/
      	);
      	
      	// create columns
      	$this->table_builder->set_cell_content(0, $colMap['chkbox'], 
      		array(
      			'name' => 'checkAll', 
      			'extra' => 'onclick="return select_all(\'manadsads\', this)"' 
      		), 
      		'checkbox'
      	);
      	$this->table_builder->sorted_column($colMap['id'], "id_site_channel", "ID", "asc");
      	$this->table_builder->sorted_column($colMap['site'], "site_name", "Site", "asc");
      	$this->table_builder->sorted_column($colMap['channel'], "name", "Channel", "asc");
      	$this->table_builder->sorted_column($colMap['status'], "tstatus", "Current Status", "asc");
      	$this->table_builder->sorted_column($colMap['program'], "program_type", "Cost Model", "asc");
      	$this->table_builder->set_cell_content($row, $colMap['price'], __("Price"));
      	$this->table_builder->sorted_column($colMap['format'], "ad_type", "Format", "asc");
      	$this->table_builder->sorted_column($colMap['spent'], "spent", "Spent", "desc");
        $this->table_builder->sorted_column($colMap['impressions'], "impressions", "Impressions", "desc");
        $this->table_builder->sorted_column($colMap['clicks'], "clicks", "Clicks", "desc");
        $this->table_builder->sorted_column($colMap['ctr'], "ctr", "CTR", "desc");

        // setup columns styles
        $this->table_builder->add_col_attribute($colMap['chkbox'], 'class', '"chkbox"');
        $this->table_builder->add_col_attribute($colMap['id'], 'class', '"w20"');
        $this->table_builder->add_col_attribute($colMap['channel'], 'class', '"w100 center"');
        $this->table_builder->add_col_attribute($colMap['status'], 'class', '"center"');
        $this->table_builder->add_col_attribute($colMap['program'], 'class', '"w100  center"');
        $this->table_builder->add_col_attribute($colMap['price'], 'class', '"w100  center"');
        $this->table_builder->add_col_attribute($colMap['format'], 'class', '"w50 center"');
        $this->table_builder->add_col_attribute($colMap['spent'], 'class', '"right"');
        $this->table_builder->add_col_attribute($colMap['impressions'], 'class', '"right"');
        $this->table_builder->add_col_attribute($colMap['clicks'], 'class', '"right"');
        $this->table_builder->add_col_attribute($colMap['ctr'], 'class', '"right"');
        
        $this->table_builder->add_row_attribute($row, 'class', 'th');
             
      	$this->table_builder->add_attribute('class', 'xTable');
      	
      	$row++;
      	
      	$page_total = array(
         	'spent' => 0,
         	'impressions' => 0,
         	'clicks' => 0
      	);
      	
      	//render table rows
      	foreach($channels as $id_channel => $channel) {
      		$page_total['spent'] += $channel['spent'];
         	$page_total['impressions'] += $channel['impressions'];
         	$page_total['clicks'] += $channel['clicks'];
      	
         	$code = type_to_str($id_channel, 'textcode');
         	
         	// checkbox
         	$this->table_builder->set_cell_content($row, $colMap['chkbox'],
            	array (
               		'name' => 'id_channels[]',
               		'value' => $code,
               		'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""
            	), 
            	'checkbox' 
            );
            
            // id
            $this->table_builder->set_cell_content($row, $colMap['id'], $id_channel);
            
            // site
         	$this->table_builder->set_cell_content($row, $colMap['site'],
            	array (
               		'name' => limit_str_and_hint($channel['site_name'], 30),
               		'href' => 'http://'.$channel['url'],
               		'extra' => "target=blank"
            	), 
            	'link'
            );
         	$this->table_builder->cell($row, $colMap['site'])->add_attribute('class', 'td_site');
            
         	// channel
         	$this->table_builder->set_cell_content($row, $colMap['channel'], limit_str_and_hint($channel['name'], 30));
         
         	// status
         	$this->table_builder->set_cell_content($row, $colMap['status'], __('chn_'.$channel['status']));
         	$this->table_builder->cell($row, $colMap['status'])->add_attribute('class', "td_status");
         
         	// program
         	$this->table_builder->set_cell_content($row, $colMap['program'], __($channel['type']));
         	
         	// price
         	$allowedTypes = explode(',', $channel['format']);
         	if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
         		$cost = $channel['cost_image'];
         	} else if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
         		$cost = $channel['cost_text'];
         	} else {
         		$cost = 0;
         	}
         	$cost = type_to_str($cost, 'money');
         	
	       	switch ($channel['type']) {
	         	case 'CPM':
	         	   $cost = "$cost / {$channel['volume']} ".__('impressions');
	         	   break;
	         	case 'Flat_Rate':
	               $cost = "$cost / {$channel['volume']} ".__('days');
	         	   break;
	         }
	      	$this->table_builder->set_cell_content($row, $colMap['price'], $cost);                           
	         
	      	// format
	      	$this->table_builder->set_cell_content($row, $colMap['format'], '');
	         
         	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
         		$ico_path = $this->site_url . 'images/smartppc6/icons/script_code.png';
         		$hint_title = __('Text Ad') . ' (' . $channel['width'] . '&times;' . $channel['height'] . ')';
				$img_prefix = 'txt_';
				$this->table_builder->cell($row, $colMap['format'])->add_content(
						array(
							'src' => $ico_path, 
							'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channel['id_dimension'] . '.png" class="tooltip"'
						),
						'image'
					);
	   			}
	   			
	   			if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
	   				$ico_path = $this->site_url . 'images/smartppc6/icons/image.png';
	   				$hint_title = __('Image Ad') . ' (' . $channel['width'] . '&times;' . $channel['height'] . ')';
	   				$img_prefix = 'img_';
         		$this->table_builder->cell($row, $colMap['format'])->add_content(
	   					array(
	   						'src' => $ico_path, 
	   						'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channel['id_dimension'] . '.png" class="tooltip"'
	   					), 
	   					'image'
	   				);
	   			}
	   			
         	$this->table_builder->cell($row, $colMap['format'])->add_content(
         		'<br>'.$channel['width'].'&times;'.$channel['height']
         	);
         	
         	// spent
         	$this->table_builder->set_cell_content($row, $colMap['spent'], type_to_str($channel['spent'], 'money'));
         	
         	// impressions
         	$this->table_builder->set_cell_content($row, $colMap['impressions'], type_to_str($channel['impressions'], 'integer'));
         	
         	// clicks
         	$this->table_builder->set_cell_content($row, $colMap['clicks'], type_to_str($channel['clicks'], 'integer'));
         	
         	// ctr
         	$this->table_builder->set_cell_content($row, $colMap['ctr'], type_to_str($channel['ctr'], 'procent'));
         
         	// add attributes to the table row
            $this->table_builder->add_row_attribute($row, 'id', "tr_$code");
         	if ($channel['status'] == 'paused') {         	
         		$this->table_builder->add_row_attribute($row, 'class', 'blocked_row');
         	}
         	
         $row++;
      }
      	
      if (0 == count($channels)) {
         $this->table_builder->insert_empty_cells = FALSE;     
         	$this->table_builder->set_cell_content ($row, 0, __('Records not found'));
         	$this->table_builder->cell($row, 0)->add_attribute('colspan', count($colMap));
      } else {
      		// add page summary row
      		$this->table_builder->set_cell_content ($row, $colMap['chkbox'], '');
      		$this->table_builder->set_cell_content ($row, $colMap['id'], '');
         	$this->table_builder->set_cell_content ($row, $colMap['site'], __("Page total"));
         	$this->table_builder->set_cell_content ($row, $colMap['spent'], type_to_str($page_total['spent'], 'money'));                           
         	$this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($page_total['impressions'], 'integer'));                           
         	$this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($page_total['clicks'], 'integer'));
         	
         $ctr = $page_total['impressions']?$page_total['clicks']/$page_total['impressions']*100:0;                          
         	$this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));                           

         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');  

         	// add summary row
         $row++;
         	$this->table_builder->set_cell_content ($row, $colMap['site'], __("Total"));
         	$this->table_builder->set_cell_content ($row, $colMap['spent'], type_to_str($total['spent'], 'money'));                           
         	$this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($total['impressions'], 'integer'));                           
         	$this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($total['clicks'], 'integer'));
         	
         $ctr = $total['impressions']?$total['clicks']/$total['impressions']*100:0;                          
         	$this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));
         	
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');               

      	}
      
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      
      	$invariable_columns = array(
      		$colMap['id'], $colMap['site'], $colMap['channel']/*, $colMap['action']*/
      	);
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      $html = str_replace('<%CHANNELS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      $vars = array(
         	'EDITNAME' => __('Manage Channels'),
         	'EDITFUNCTION' => 'editChannels',
         'TAB' => 'channels'      
      );   

      $buttons = "";
      $buttons .= $this->parser->parse('advertiser/manage_ads/ads/channels_buttons.html', $vars, TRUE);
     	$buttons = str_replace('<%GROUP%>', "'".type_to_str($this->id_group, 'textcode')."'", $buttons);
      
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      	
      return $html;
   	}

   /**
   * выводит итоговую таблицу
   *
   * @return string HTML-код тоговой таблицы
   */
   public function summary() {
      $this->load->library('form');
      $this->load->helper('periods');
      
      $additionalButtons = '';
      
      $form = array(
         'id' => 'summary',
         'name' => 'manadssummary_form',
         'view' => 'advertiser/manage_ads/ads/summary.html',
         'redirect' => 'advertiser/manage_ads',
         'no_errors' => 'true',
         'action' => $this->site_url.$this->index_page."advertiser/manage_ads_ads/index/".type_to_str($this->id_group, 'textcode'),
         'vars' => array(
            'DATEFILTER' => period_html('manadssummary'),
            'EDITNAME' => __('Manage Channels'),
            'EDITFUNCTION' => 'editChannels',
            'CPCBIDS' => '',
      		'ADDITIONAL_BUTTONS' => $additionalButtons         
         )
      );
      	
      data_range_fields($form, 'select', 'alltime');
      	
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);
      
      $this->load->model('groups');
      	
      $this->load->library('Table_Builder');
      $this->table_builder->clear();
      
      $summary = $this->groups->summary($this->id_group, $this->date_range);
      
      $col = 0;
      $cols['type'] = array('index' => $col++, 'name' => '');
      
      $cols['ads'] = array('index' => $col++, 'name' => 'Ads', 'class' => '"w100 right"');
      $col_name = __('Sites/Channels');
      $cols['sites'] = array('index' => $col++, 'name' => $col_name, 'class' => '"w100 right"');
       
      $cols['spent'] = array('index' => $col++, 'name' => 'Spent', 'class' => '"w100 right"');
      $cols['clicks'] = array('index' => $col++, 'name' => 'Clicks', 'class' => '"w100 right"');
	   $cols['impressions'] = array('index' => $col++, 'name' => 'Impressions', 'class' => '"w100 right"');
      $cols['ctr'] = array('index' => $col++, 'name' => 'CTR', 'class' => '"w100 right"');
	  
	  foreach ($cols as $data) {
	  	 $this->table_builder->set_cell_content(0, $data['index'], __($data['name']));
	  	 if (isset($data['class'])) {
	  	 	$this->table_builder->add_col_attribute($data['index'], 'class', $data['class']);
	  	 }
	  }
      $this->table_builder->add_row_attribute(0,'class', 'th');
      $this->table_builder->add_attribute('class', 'xTable');

      $row = 1;
      $sites_text = type_to_str($summary['site_channels'], 'integer').' '.__('Selected Sites');

      $summary_other = array();           

      $this->table_builder->set_cell_content($row, $cols['type']['index'], __('Total'));
      $this->table_builder->set_cell_content($row, $cols['ads']['index'],
      array (
         'name' => type_to_str($summary['ads'], 'integer'),
         'href' => "#select_ads",
         'extra' => "jframe='no' onclick='$(\"#tab_ads\").click(); return false;'"
      		), 
      		'link'
      	);
      $this->table_builder->set_cell_content($row, $cols['sites']['index'],
      array (
         'name' => type_to_str($summary['site_channels'], 'integer'),
         'href' => "#select_channels",
         'extra' => "jframe='no' onclick='$(\"#tab_channels\").click(); return false;'"
      		), 
      		'link'
      	);
      $this->table_builder->set_cell_content($row, $cols['spent']['index'], type_to_str($summary['spent'], 'money'));
      $this->table_builder->set_cell_content($row, $cols['clicks']['index'], type_to_str($summary['clicks'], 'integer'));
      $this->table_builder->set_cell_content($row, $cols['impressions']['index'], type_to_str($summary['impressions'], 'integer'));
      $this->table_builder->set_cell_content($row, $cols['ctr']['index'], type_to_str($summary['ctr'], 'procent'));
      $html = str_replace('<%GROUP%>', "'".type_to_str($this->id_group, 'textcode')."'", $html);      
      return str_replace('<%SUMMARY%>', $this->table_builder->get_html(), $html);
   } //end summary

   /**
   * функция, вызываемая по умолчанию, выводит таблицы объявлений
   *
   * @param integer $id_group уникальный код группы объявлений
   * @return ничего не возвращаяет
   */
   public  function index($code_group, $tab = NULL) {
      $this->id_group = type_cast($code_group, 'textcode');;
      $this->load->model('groups', '', TRUE);
      $this->id_campaign_type = $this->groups->group_type($this->id_group);
      $this->load->library('tabs');
      $this->tabs->create('adTabs', '', '', '2');
      $this->tabs->add('summary', 'Summary', $this->summary());
      $this->tabs->add('ads', 'Ads', $this->ads());
    	$tab_name = 'Sites/Channels';  
      $this->tabs->add('channels', $tab_name, $this->channels());
      
      if (is_null($tab)) {
         $tab = $this->input->post('tab');
      }
      if ($tab) {
         $this->tabs->select($tab);
      }
      
      $this->_set_content('<%NOTIFICATION%>'.$this->tabs->html());
   	$this->_display();
   } //end index

   /**
   * Callback-функция, устанавливает значения по умолчанию для фильтра таблицы
   *
   * @return array массив со значениями по умолчанию для фильтров
   */
   public function _load($id) {
      if ($id == 'ads') {
         $fields = period_load('manadsads', 'select', 'alltime');
         $fields['filt'] = $this->temporary['manadsads_filt'];
      } elseif ($id == 'channels') {
         $fields = period_load('manadschannels', 'select', 'alltime');
         $fields['filt'] = $this->temporary['manadschannels_filt'];
      } else {
         $fields = period_load('manadssummary', 'select', 'alltime');
      }
      $this->date_range = data_range($fields);
      return $fields;
   } //end _load

   /**
   * Callback-функция, сохраняет установленные пользователем значения для фильтра таблицы
   *
   * @return string непустая строка для подавления succes-режима формы
   */
   public function _save($id, $fields) {
      $this->date_range = data_range($fields);
      if ($id == 'ads') {
         $this->temporary['manadsads_filt'] = $fields['filt'];
         period_save('manadsads', $fields);
      } elseif ($id == 'channels') {
         $this->temporary['manadschannels_filt'] = $fields['filt'];
         period_save('manadschannels', $fields);
      } else {
         period_save('manadssummary', $fields);
      }
      return 'false';
   } //end _save

   /**
   * AJAX-функция, возвращает HTML-код выбранного объявления
   *
   * @param string $code шифрованный код выбранного объявления
   * @return ничего не возвращает
   */
   public function ad($code, $resize = TRUE) {
      $id_ad = type_cast($code, 'textcode');
      $this->load->model('ads');
      if($resize) {
         $html = $this->ads->get_html($id_ad, 220, 120);         
      } else {
         $html = $this->ads->get_html($id_ad);
      }
      if (strpos($html, '<!--LoginPage-->')) {
         redirect($this->role . "/login");
      }
      echo $html;
   } //end ad
   
   /**
    * AJAX функция получения инфы по выбранному объявлению в JSON формате
    *
    * @param unknown_type $code
    */
   public function get_ad_info($code) {
      $id_ad = type_cast($code, 'textcode');
      $this->load->model('ads');
      // Проверяем на принадледность объявления рекламодателю
      if (!$this->ads->check_advert_ad($id_ad, $this->user_id)) {
         show_404();
      }
      $ad = $this->ads->get($id_ad);
      $data = array(
         'ad_type'              => $ad['ad_type'],
         'title'                => $ad['title'],
         'description1'         => isset($ad['description1']) ? $ad['description1'] : '',
         'description2'         => isset($ad['description2']) ? $ad['description2'] : '',
         'display_url'          => $ad['display_url'],
         'destination_url'      => $ad['destination_url'],
         'destination_protocol' => $ad['destination_protocol'] 
      );
      echo json_encode($data);
   }
   
   public function get_bulk_editor() {
      $this->load->library('form');
      $this->load->helper('fields');
	  
		// Подгружаем стили дефолтной color scheme
		 
		$this->db->select('cs.*, ft.name title_font_name, ft2.name text_font_name, ft3.name url_font_name', false);
		$this->db->from('color_schemes cs');
		$this->db->join('fonts ft', 'ft.id_font = cs.title_id_font');
		$this->db->join('fonts ft2', 'ft2.id_font = cs.text_id_font');
		$this->db->join('fonts ft3', 'ft3.id_font = cs.url_id_font');

		$query = $this->db->get();
		$row = $query->row();
	  
      $form_data = array(
         "name"        => 'bulk_editor_form',
         "vars" => array(
            'TEXT_AD_EXAMPLE' => $this->load->view('common/text_ad_example.html','',TRUE),
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
         "view" => 'advertiser/manage_ads/ads/bulk_editor.html',
         "fields"      => array(
            "title" => array(
               "display_name"     => __("Title"),
               "default"          => "",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               'max'              => 25
            ),
            "description1" => array(
               "display_name"     => __("Description 1"),
               "default"          => "",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               'max'              => 35
            ),
            "description2" => array(
               "display_name"     => __("Description 2"),
               "default"          => "",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               'max'              => 35
            ),
            "display_url" => array(
               "display_name"     => __("Display URL"),
               "default"          => "",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "trim|required|url",
               'max'              => 35
            ),
            "destination_url" => array(
               "display_name"     => __("Destination URL"),
               "default"          => "",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "trim|required|url",
               'max'              => 1024
            ),
            "destination_protocol" => array(
               "display_name"     => __("Destination URL protocol"),
               "default"          => 'http',
               "id_field_type"    => "string",
               "form_field_type"  => "select",
               "validation_rules" => "required",
               "options"          => array('http' => 'http://', 'https' => 'https://')
            ),
         )
      );

      return $this->form->get_form_content('modify', $form_data, $this->input, $this);
   }
   
   function save_ad($code, $bulk = false, $bulk_field = 'all') {
      $jdata = array(
         'status' => 'succ'
      );
      $this->load->model('ads');
      // Получаем идентификатор рекламного объявления
      $id_ad = type_cast($code, 'textcode');
      // Проверяем на принадледность объявления рекламодателю
      if (!$this->ads->check_advert_ad($id_ad, $this->user_id)) {
         show_404();
      }
      // Получаем тип объявления
      $ad = $this->ads->get($id_ad);
      $type = $ad['ad_type'];
      
      // Валидируем
      $this->load->library('validation');
      $this->validation->set_error_delimiters('', '');
      $rules = array(
         'title'                => 'trim|required|max_length[25]',
         'display_url'          => 'trim|required|callback_check_url|max_length[35]',
         'destination_url'      => 'trim|required|callback_check_url|max_length[1024]',
         'destination_protocol' => 'trim|required'
      );
      if ('text' == $type) {
         $rules['description1'] = 'trim|required|max_length[35]';
         $rules['description2'] = 'trim|required|max_length[35]';
      }
      $this->validation->set_rules($rules);
      $titles = array(
         'title'                => __("Title"),
         'display_url'          => __("Display URL"),
         'destination_url'      => __("Destination URL"),
         'destination_protocol' => __("Destination URL protocol"),
         'description1'         => __("Description 1"),
         'description2'         => __("Description 2")
      );
      if ($this->validation->run()) {
         // Получаем fileds
         $fields = array(
            'title'                => trim($this->input->post('title')),
            'description1'         => trim($this->input->post('description1')),
            'description2'         => trim($this->input->post('description2')),
            'display_url'          => trim($this->input->post('display_url')),
            'destination_url'      => trim($this->input->post('destination_url')),
            'destination_protocol' => trim($this->input->post('destination_protocol'))
         );
         // Сохраняем
         if (!$bulk) {
            $this->ads->save($id_ad, $fields, $type);
         } else {
            // Получаем группу
            $id_group = $this->ads->group($id_ad);
            // Получаем все объявления этой группы
            $ads = $this->ads->get_ads_ids_by_group($id_group);
            // Обновляем все объявления
            foreach ($ads as $id) {
               // Собираем массив для обновления
               $ad = $this->ads->get($id);
               $ad_type = $ad['ad_type'];
               if ('all' != $bulk_field && isset($fields[$bulk_field])) {
                  // Обновляем только одно поле
                  $ad[$bulk_field] = $fields[$bulk_field];
                  if ('destination_url' == $bulk_field) {
                     $ad['destination_protocol'] = $fields['destination_protocol'];
                  }
               } else {
                  if ('image' == $type && 'image' != $ad['ad_type']) {
                     // Сохраняем descriptions для тектовых адов, если редактировался картиночный ад
                     $desc1 = $ad['description1'];
                     $desc2 = $ad['description2'];
                     $ad = $fields;
                     $ad['description1'] = $desc1;
                     $ad['description2'] = $desc2;
                  } else {
                     // Обновляем все поля
                     $ad = $fields;
                  }
               }
               $this->ads->save($id, $ad, $ad_type);
            }
         }
      } else {
         // Возвращяем ошибки
         $jdata['status'] = 'error';
         $errors = array();
         foreach (array_keys($rules) as $key) {
            $error = $key . '_error';
            if (isset($this->validation->$error) && !empty($this->validation->$error)) {
               $errors[$key] = str_replace('{@' . $key . '@}', "'" . $titles[$key] . "'", $this->validation->$error);
            }
         }
         $jdata['errors'] = $errors;
      }
      echo json_encode($jdata);
   }
   
   function save_ad_bulk($code, $bulk_field = 'all') {
      $this->save_ad($code, true, $bulk_field);
   }
   
} //end class Manage_ads_ads

?>