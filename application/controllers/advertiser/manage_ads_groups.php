<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для отображения таблицы групп в "Управлении Объявлениями"
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Manage_ads_groups extends Parent_controller {
   
   protected $role = "advertiser"; 
   
   protected $menu_item = "Manage Ads";  
   
   protected $template = "common/parent/jq_iframe.html";
   
   protected $date_range;
   
   protected $date_picker = TRUE;

   protected $id_campaign;
   
   public  $temporary = array (
      'manadsgroups_filt' => 'all',
      'manadsgroups_columns' => 'all'
   );  
   
   /**
    * Hook objects which extend controller functionality
    * 
    * @var array
    */
   protected $_hooks = array();
   
   /**
   * конструктор класса, вызов базового конструктора
   *
   * @return ничего не возвращает
   */   
   public function Manage_ads_groups() {
      parent::Parent_controller();
      
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->advertiser->manage_ads->groups)) {
      	 foreach($pluginsConfig->advertiser->manage_ads->groups as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Advertiser_ManageAds_Groups_EventHandlerInterface) {
      	 		$this->_hooks[] = $hookObj;
      	 	}
      	 }
      }
   } //end Manage_ads_groups

   /**
   * совершает выбранное действие над выделенными группами
   *
   * @param $action действие производимое над группами (delete, pause, resume)
   * @return ничего не возвращает
   */   
   public function _actions($action) {
      $this->content['JSACTION'] = '';
      $id_groups = $this->input->post('id_groups');
      if (is_array($id_groups) && !empty($action)) {
         foreach ($id_groups as $code) {
            $id_group = type_cast($code, 'textcode');
            $this->groups->action($action, $this->id_campaign, $id_group);
         }
         switch ($action) {
            case 'pause':       
               $this->_set_notification('Selected groups was paused.');
               break;
            case 'resume':
               $this->_set_notification('Selected groups was resumed.');
               break;
            case 'delete':
               $this->_set_notification('Selected groups was deleted.');
               $this->content['JSACTION'] = "$(['".implode("','", $id_groups)."']).each(function(a){top.deleteGroup(this);});";               
               break;
         }         
      }                            
   } //end _actions         
   
   /**
   * функция вызываемая по умолчанию, выводит таблицу кампаний
   *
   * @param integer $id_campaign уникальный код кампании, содержащей группы объявлений
   * @return ничего не возвращает
   */
   public function index($code_campaign) {      
      $this->id_campaign = type_cast($code_campaign, 'textcode');
      $this->load->library('form');            
      $this->load->helper('periods');
      $form = array(
         'id' => 'groups', 
         'name' => 'manadsgroups_form',
         'view' => 'advertiser/manage_ads/groups/table.html',
         'redirect' => 'advertiser/manage_ads',
         'no_errors' => 'true',
         'vars' => array(
            'DATEFILTER' => period_html('manadsgroups') 
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
            
      $this->load->model('groups');
      
      $this->_actions($this->input->post('manadsgroups_action'));
      
      $this->load->library('Table_Builder');      
            
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = true;            
      $this->table_builder->init_sort_vars('manadsgroups', 'id_campaign', 'asc');
      
      $this->load->model('pagination_post');
      $this->pagination_post->set_form_name('manadsgroups');
      $total_function = 'total';      
            
      $total = $this->groups->$total_function(
            $this->id_campaign,
            $this->temporary['manadsgroups_filt'],
            $this->date_range); 
             
      $this->pagination_post->set_total_records($total['cnt']);            
      $this->pagination_post->read_variables('manadsgroups', 1, $this->global_variables->get('GroupsPerPage'));            

      $select_function = 'select';      
      $groups = $this->groups->$select_function(
         $this->id_campaign,
         $this->temporary['manadsgroups_page'],
         $this->temporary['manadsgroups_per_page'],
         $this->table_builder->sort_field,
         $this->table_builder->sort_direction,
         $this->temporary['manadsgroups_filt'],
         $this->date_range);
         
      $row = 0;
      
      //build column map
      $colIndex = 0;
      $colMap = array(
      	 'chkbox' => $colIndex++,
      	 'id' => $colIndex++,
      	 'name' => $colIndex++,
      	 'status' => $colIndex++,
      	 'ads' => $colIndex++,
      	 'siteChannels' => $colIndex++,
      	 'spent' => $colIndex++,
      	 'impressions' => $colIndex++,
      	 'clicks' => $colIndex++,
      	 'ctr' => $colIndex++,
      	 'action' => $colIndex++
      );
      
   	  // register additional columns from plugins
      foreach($this->_hooks as $hookObj) {
      	 $colMap = $hookObj->extendColumnMap($colMap);
      }

      // create columns
      $this->table_builder->set_cell_content(
      	 $row, 
      	 $colMap['chkbox'], 
      	 array (
      	 	'name' => 'checkAll', 
      	 	'extra' => 'onclick="return select_all(\'manadsgroups\', this)"' 
      	 ), 
      	 'checkbox'
      );
      $this->table_builder->sorted_column($colMap['id'], "id_group", "ID", "asc");
      $this->table_builder->sorted_column($colMap['name'], "name", "Group Name", "asc");
      $this->table_builder->sorted_column($colMap['status'], "status", "Current Status", "asc");
      $this->table_builder->sorted_column($colMap['ads'], "ads", "Ads", "desc");
      
    	$col_name = "Sites/Channels";
      $this->table_builder->sorted_column($colMap['siteChannels'], "site_channels", $col_name, "desc");
      
      $this->table_builder->set_cell_content($row, $colMap['spent'], __("Spent"));
      $this->table_builder->set_cell_content($row, $colMap['impressions'], __("Impressions"));
      $this->table_builder->set_cell_content($row, $colMap['clicks'], __("Clicks"));
      $this->table_builder->set_cell_content($row, $colMap['ctr'], __("CTR"));
      $this->table_builder->set_cell_content($row, $colMap['action'], __('Action'));

   	  // create additional columns from plugins
      foreach($this->_hooks as $hookObj) {
      	 $hookObj->createColumns($colMap, $this->table_builder);
      }
      
      $this->table_builder->add_row_attribute($row,'class', 'th');      
      
      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($colMap['chkbox'], 'class', '"chkbox"');
      $this->table_builder->add_col_attribute($colMap['id'], 'class', '"w20"');
      $this->table_builder->add_col_attribute($colMap['status'], 'class', '"w100  center"');
      $this->table_builder->add_col_attribute($colMap['ads'], 'class', '"w50   right"');
      $this->table_builder->add_col_attribute($colMap['siteChannels'], 'class', '"w100   right"');
      $this->table_builder->add_col_attribute($colMap['spent'], 'class', '"w100  right"');
      $this->table_builder->add_col_attribute($colMap['impressions'], 'class', '"w100   right"');
      $this->table_builder->add_col_attribute($colMap['clicks'], 'class', '"w50   right"');
      $this->table_builder->add_col_attribute($colMap['ctr'], 'class', '"w50   right"');
      $this->table_builder->add_col_attribute($colMap['action'], 'class', '"nowrap center"');
      
      // add styles for additional columns from plugins
      foreach($this->_hooks as $hookObj) {
      	 $hookObj->defineColumnStyles($colMap, $this->table_builder);
      }
      
      $this->table_builder->add_attribute('class', 'xTable');
      
      $clone_icon = __('Edit');
      $row++;
      $page_total = array(
         'ads' => 0, 
         'sites_channels' => 0,
         'spent' => 0,
         'impressions' => 0,
         'clicks' => 0
      );
      
   	  // register additional per page statistic fields
      foreach($this->_hooks as $hookObj) {
      	 $page_total = $hookObj->registerPerPageStatisticFields($page_total);
      }
      
      foreach ($groups as $id_group => $group) {      
      	 // calculate per page statistic
         $page_total['ads'] += $group['ads'];
         $page_total['sites_channels'] += $group['site_channels'];
         $page_total['spent'] += $group['spent'];
         $page_total['impressions'] += $group['impressions'];
         $page_total['clicks'] += $group['clicks'];         

      	 // calculate per page statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
         	$page_total = $hookObj->calculatePerPageStatistic($page_total, $group);
         }
         
         $code = type_to_str($id_group, 'textcode'); 
         
         // checkbox
         $this->table_builder->set_cell_content($row, $colMap['chkbox'], 
            array (
               'name' => 'id_groups[]', 
               'value' => $code, 
               'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""
            ), 'checkbox' );
            
         // id
         $this->table_builder->set_cell_content($row, $colMap['id'], $id_group);

         // name
         $this->table_builder->set_cell_content($row, $colMap['name'],
            array (
               'name' => limit_str_and_hint($group['name'], 30), 
               'href' => "#select_group",
               'extra' => "jframe='no' onclick='return top.show_ads(\"$code\",\"\");'"
            ), 'link');

         // status
         $this->table_builder->set_cell_content($row, $colMap['status'], __('grp_'.$group['status']));

         // ads
         $this->table_builder->set_cell_content($row, $colMap['ads'], 
            array(
               'name' => type_to_str($group['ads'], 'integer'), 
               'href' => "#select_ads",
               'extra' => "jframe='no' onclick='return top.show_ads(\"$code\", \"ads\");'"
            ), 'link');         

         // site/channels
         $this->table_builder->set_cell_content($row, $colMap['siteChannels'], 
            array(
               'name' => type_to_str($group['site_channels'], 'integer'), 
               'href' => "#select_sitechannels",
               'extra' => "jframe='no' onclick='return top.show_ads(\"$code\", \"channels\");'"
            ), 'link');                           

         // spent
         $this->table_builder->set_cell_content($row, $colMap['spent'], type_to_str($group['spent'], 'money'));
         
         // impressions
         $this->table_builder->set_cell_content($row, $colMap['impressions'], type_to_str($group['impressions'], 'integer'));
         
         // clicks
         $this->table_builder->set_cell_content($row, $colMap['clicks'], type_to_str($group['clicks'], 'integer'));
         
         // ctr
         $this->table_builder->set_cell_content($row, $colMap['ctr'], type_to_str($group['ctr'], 'procent'));
         
         // action
         $this->table_builder->set_cell_content($row, $colMap['action'], 
            array (
               'name' => $clone_icon, 
               'href' => "#edit_group",
               'extra' => "jframe='no' class='guibutton floatl ico ico-edit' value='$clone_icon' title='$clone_icon' onclick='top.editGroup(\"$code_campaign\",\"$code\");'"
            ), 'link');
            
      	 // render additional columns from plugins
         foreach($this->_hooks as $hookObj) {
         	$hookObj->renderRow($row, $colMap, $group, $this->table_builder);
         }

         // add id attribute to the table row
         $this->table_builder->add_row_attribute($row, 'id', "tr$row");         
                  
         $row++;
      }
      if (0 == count($groups)) {
         $this->table_builder->insert_empty_cells = false;            
         $this->table_builder->set_cell_content ($row, 0,'&nbsp;&nbsp;'.__('Records not found'));
         $this->table_builder->cell($row, 0)->add_attribute('class', 'nodata');
         $this->table_builder->cell($row, 0)->add_attribute('colspan', count($colMap));         
      } else {
      	 // render per page statistic row
         $this->table_builder->set_cell_content ($row, $colMap['name'], __("Page total"));                           
         $this->table_builder->set_cell_content ($row, $colMap['ads'], type_to_str($page_total['ads'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['siteChannels'], type_to_str($page_total['sites_channels'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['spent'], type_to_str($page_total['spent'], 'money'));                           
         $this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($page_total['impressions'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($page_total['clicks'], 'integer'));
           
         $ctr = $page_total['impressions']?$page_total['clicks']/$page_total['impressions']*100:0;                          
         $this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));
         
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');      

      	 // render per page statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
         	$hookObj->renderPageStatisticRow($row, $colMap, $page_total, $this->table_builder);
         }
         
         // render summary statistic row
         $row++;
         $this->table_builder->set_cell_content ($row, $colMap['name'], __("Total"));                           
         $this->table_builder->set_cell_content ($row, $colMap['ads'], type_to_str($total['ads'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['siteChannels'], type_to_str($total['site_channels'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['spent'], type_to_str($total['spent'], 'money'));                           
         $this->table_builder->set_cell_content ($row, $colMap['impressions'], type_to_str($total['impressions'], 'integer'));                           
         $this->table_builder->set_cell_content ($row, $colMap['clicks'], type_to_str($total['clicks'], 'integer'));                                    
         $ctr = $total['impressions']?$total['clicks']/$total['impressions']*100:0;                          
         $this->table_builder->set_cell_content ($row, $colMap['ctr'], type_to_str($ctr, 'procent'));

         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');               

      	 // render summary statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
         	$hookObj->renderSummaryRow($row, $colMap, $total, $this->table_builder);
         }
      }                  
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         $colMap['id'], $colMap['name'], $colMap['action']
      );
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      $html = str_replace('<%GROUPS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      $buttons = $this->load->view('advertiser/manage_ads/groups/buttons.html', '', TRUE);
      $buttons = str_replace('<%CAMPAIGN%>', $code_campaign, $buttons);
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);      
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      $this->_set_content($html);
      $this->_display();
   } //end index

   /**
   * Callback-функция, устанавливает значения по умолчанию для фильтра таблицы
   *
   * @return array массив со значениями по умолчанию для фильтров
   */      
   public function _load() {
      $fields = period_load('manadsgroups', 'select', 'alltime');      
      $this->date_range = data_range($fields);
      $fields['filt'] = $this->temporary['manadsgroups_filt']; 
      return $fields;
   } //end _load   

   /**
   * Callback-функция, сохраняет установленные пользователем значения для фильтра таблицы
   *
   * @return string непустая строка для подавления succes-режима формы
   */      
   public function _save($id, $fields) {
      $this->date_range = data_range($fields);
      $this->temporary['manadsgroups_filt'] = $fields['filt'];
      period_save('manadsgroups', $fields);
      return 'false';
   } //end _save      
   
} //end class Manage_ads_groups

?>