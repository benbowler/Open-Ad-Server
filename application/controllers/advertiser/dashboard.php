<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Dashboard extends Parent_controller {

   protected $role = "advertiser";
   
   protected $menu_item = "Dashboard";

   protected $dashboard;
   
   public $range;
   
   protected $date_picker = TRUE;      
   
   protected $period;
   
   public $temporary = array (
      'dashboard_from' => 'select',
      'dashboard_to'   => 'today',
      'advdashboard_tab' => 'spent'
   );     
   
   public function Dashboard() {
      parent::Parent_controller();
      $this->_add_ajax(); 
      
      
      $this->load->model('entity', '', TRUE);
      $this->load->helper('fields');
      $this->load->library('Table_Builder');    

      $this->load->library("Plugins", array('path' => array('advertiser', 'dashboard')));
      
   }
   
   protected function _tabs() {
   	  //load hooks
   	  $hooks = array();
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->advertiser->dashboard_blocks->graphs)) {
      	 foreach($pluginsConfig->advertiser->dashboard_blocks->graphs as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Advertiser_Dashboard_GraphsInterface) {
      	 		$hooks[] = $hookObj;
      	 	}
      	 }
      }
      
      $this->load->library('tabs');
      
      $this->tabs->create('graphTabs', 'w100p', 'w100p h200 vmid');
      
      $this->load->library('google_chart');
      
      $this->google_chart->date_range($this->range);          
                
      $stat = $this->entity->get_line_stat($this->user_id, $this->range['from'], $this->range['to']);
      
      $this->google_chart->data($stat['spent'], 2);
      $this->tabs->add('spent', 'Spent', "<img src='{$this->google_chart->link()}'/>");
      
      $this->google_chart->data($stat['impressions']);
      $this->tabs->add('impressions', 'Impressions', "<img src='{$this->google_chart->link()}'/>");      
            
      $this->google_chart->data($stat['clicks']);
      $this->tabs->add('clicks', 'Clicks', "<img src='{$this->google_chart->link()}'/>");      
            
      $this->google_chart->data($stat['ctr'], 2);
      $this->tabs->add('ctr', '% CTR', "<img src='{$this->google_chart->link()}'/>");
      
      //render additional graphs from plugins
      foreach($hooks as $hookObj) {
      	 $hookObj->renderGraphs($stat, $this);
      }
      
      $this->tabs->set_ajax($this->site_url.$this->index_page.'advertiser/dashboard/save_tab');
      
      if ($this->temporary['advdashboard_tab'] == '' || !$this->temporary['advdashboard_tab']) {
         $this->temporary['advdashboard_tab'] = 'spent';
      }
      
      $this->tabs->select($this->temporary['advdashboard_tab']);
      
      return $this->tabs->html();
   } //end _tabs
   
   protected function _top_campaigns() {
   	  // load hooks
      $hooks = array();
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
   	  
      if (isset($pluginsConfig->advertiser->dashboard_blocks->top_campaigns)) {
      	 foreach($pluginsConfig->advertiser->dashboard_blocks->top_campaigns as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Advertiser_Dashboard_TopCampaignsInterface) {
      	 		$hooks[] = $hookObj;
      	 	}
      	 }
      }
      
      // configure table builder
      $this->table_builder->clear ();
      $this->table_builder->insert_empty_cells = false;      
            
      $this->table_builder->init_sort_vars('topcamps', 'spent', 'desc');
      
      // build column map
      $colIndex = 0;
      $colMap = array(
      	 'name' => $colIndex++,
      	 'spent' => $colIndex++,
      	 'clicks' => $colIndex++,
      	 'impressions' => $colIndex++,
      	 'ctr' => $colIndex++
      );
      
      // add additional columns from plugins
      foreach($hooks as $hookObj) {
      	 $colMap = $hookObj->extendColumnMap($colMap);
      }
      
      $this->table_builder->sorted_column($colMap['name'], 'name', 'Campaign', 'asc');
      $this->table_builder->sorted_column($colMap['spent'], 'spent', 'Spent', 'desc');
      $this->table_builder->sorted_column($colMap['clicks'], 'clicks', 'Clicks', 'desc');
      $this->table_builder->sorted_column($colMap['impressions'], 'impressions', 'Impressions', 'desc');
      $this->table_builder->sorted_column($colMap['ctr'], 'ctr', '% CTR', 'desc');
      
      // create additional columns from plugins
      foreach($hooks as $hookObj) {
      	 $hookObj->createColumns($colMap, $this->table_builder);
      }
      
      $this->table_builder->add_row_attribute(0, 'class', 'th');      
            
      $this->table_builder->add_col_attribute($colMap['spent'], 'class', '"w100 right"');
      $this->table_builder->add_col_attribute($colMap['clicks'], 'class', '"w100 right"');
      $this->table_builder->add_col_attribute($colMap['impressions'], 'class', '"w100 right"');
      $this->table_builder->add_col_attribute($colMap['ctr'], 'class', '"w100 right"');
      
      // define styles for addtition columns from plugins
      foreach($hooks as $hookObj) {
      	 $hookObj->defineColumnStyles($colMap, $this->table_builder);
      }
      
      $this->table_builder->add_attribute('class', '"xTable mb10"');
      
      $this->load->model('campaigns', '', TRUE);
      
      $top = $this->campaigns->top(
         $this->user_id, 
         $this->table_builder->sort_field, 
         $this->table_builder->sort_direction, 
         $this->range
      );
      
      $row = 1;
      
      foreach ($top as $id_camp => $camp) {
         $code = type_to_str($id_camp, 'textcode');
	 $camp['type'] = $this->campaigns->get_type($id_camp);
	 
         if ($camp['status']=='deleted') {
            $this->table_builder->set_cell_content($row, $colMap['name'], limit_str_and_hint($camp['name'], 20));
         } else {                              
            $this->table_builder->set_cell_content($row, $colMap['name'],
               array (
                  'name' => limit_str_and_hint($camp['name'], 20), 
                  'href' => $this->site_url.$this->index_page.'advertiser/manage_ads/campaign/'.$code
               ), 'link');
         }                    
         $this->table_builder->set_cell_content($row, $colMap['spent'], type_to_str($camp['spent'], 'money'));
         $this->table_builder->set_cell_content($row, $colMap['clicks'], type_to_str($camp['clicks'], 'integer'));
         $this->table_builder->set_cell_content($row, $colMap['impressions'], type_to_str($camp['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, $colMap['ctr'], type_to_str($camp['ctr'], 'float').' %');

         // render additional columns from plugins
         foreach($hooks as $hookObj) {
         	$hookObj->renderRow($row, $colMap, $camp, $this->table_builder);
         }
         
         $row++;
      };      
           
      if (0 == count($top)) {
         $this->table_builder->insert_empty_cells = false;      
         $this->table_builder->set_cell_content (1, 0, __('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');                           
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($colMap));         
      }            
      return $this->parser->parse(
         'advertiser/dashboard/top_campaigns.html',       
         array(
            'CAMPAIGNS' => $this->table_builder->get_sort_html(),
            'CAMPCOUNT' => count($top)), 
         TRUE);
   	
   } //end _top_campaigns           
   
   /**
   * создание и вывод HTML-кода дашборда адвертайзера
   *
   * @return ничего не возвращает
   */
   public function index() {
      $this->load->model('advertisers', '', TRUE);
      
      $this->_set_title(__('Advertiser').' - '.__('Dashboard'));
      $this->_set_help_index("advertiser_dashboard");
      $this->load->library("form");
      $this->load->helper('periods');
      $form = array(
         'name' => 'advdashboard_form',
         'view' => 'advertiser/dashboard/form.html',
         'no_errors' => 'true',
         'vars' => array(
            'DATEFILTER' => period_html('advdashboard') 
         )            
      );
      data_range_fields($form, 'select', 'today');             
      $this->dashboard = array(
         'BALLANCE' => type_to_str($this->entity->ballance($this->user_id), 'money'),
         'TOTALSPENT' => '-',
         'TOTALIMPRESSIONS' => '-',
         'TOTALCLICKS' => '-',
         'CTR' => '-',
         'BONUS' => '',
         'USE_BONUS' => array(),
      	 'ADDITIONAL_PERFORMANCE_FIELDS' => array()
      );

      $this->dashboard['FORM'] = $this->form->get_form_content('modify', $form, $this->input, $this);
      $this->dashboard['TABS'] = $this->_tabs();      
      $this->dashboard['CAMPAIGNS'] = $this->_top_campaigns();
      $this->sppc_range = new Sppc_Dashboard_DateRange();
      $this->_set_content($this->parser->parse('advertiser/dashboard/template.html', $this->dashboard));
      $this->_display();      
   }

   /**
   * подготавливает данные статистики для заданного периода времени 
   *
   * @return ничего не возвращает
   */  
   protected function _stat($range) {
	  //load hooks
   	  $hooks = array();
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->advertiser->dashboard_blocks->summary_performance)) {
      	 foreach($pluginsConfig->advertiser->dashboard_blocks->summary_performance as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Advertiser_Dashboard_SummaryPerformanceInterace) {
      	 		$hooks[] = $hookObj;
      	 	}
      	 }
      }
       
      $this->range = $range;
      
      $stat = $this->entity->get_stat($this->user_id, $range['from'], $range['to']);
      $ctr = $stat['impressions'] ? $stat['clicks']*100/$stat['impressions'] : 0;        
      $this->dashboard['TOTALSPENT'] = type_to_str($stat['spent'], 'money');
      $this->dashboard['TOTALIMPRESSIONS'] = type_to_str($stat['impressions'], 'integer');
      $this->dashboard['TOTALCLICKS'] = type_to_str($stat['clicks'], 'integer');   
      $this->dashboard['CTR'] = type_to_str($ctr, 'float') . " %";
      
      // add additional performance fields from plugins
      foreach($hooks as $hookObj) {
      	 $this->dashboard['ADDITIONAL_PERFORMANCE_FIELDS'] = $hookObj->addSummaryPerformanceFields(
      	 	$stat,
      	 	$this->dashboard['ADDITIONAL_PERFORMANCE_FIELDS']
      	 );
      }
   }
  
   /**
   * передаёт данные статистики для периода времени заданного пользователем в форме,
   * callback-функция для библиотеки form
   *
   * @param array $fields список полей формы и их сначений
   * @return string всегда 'error' - запрещает выход из формы
   */   
   public function _create($fields) {
      period_save('advdashboard', $fields);         
      $this->_stat(data_range($fields));
      return "error";}

   /**
   * подготавливает данные статистики для периода времени по умолчанию,
   * callback-функция для библиотеки form
   *
   * @param integer $id не используется (обязательный параметр callback-функции)
   * @return array пустой массив (обязательное возвращаемое значение callback-функции)
   */      
   public function _load($id) {
      $fields = period_load('advdashboard', 'select', 'today');
      $this->_stat(data_range($fields));
      return $fields;
   }

   /**
   * наполняет базу тестовыми данными по статистике адвертайзера 1
   *
   * @return ничего не возвращает
   */      
   public function test_data() {
      $this->entity->test_data();
   }

   /**
   * AJAX-функция, сохраняющая текущий выбранный таб
   *
   * @return ничего не возаращает
   */
   public function save_tab() {
      $tab = $this->input->post('tab');
      $this->temporary['advdashboard_tab'] = $tab;
      $this->_save_temporary();
   } //end save_tab   
   
} //end class Dashboard 

?>