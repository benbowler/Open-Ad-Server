<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер управления адвертайзерами
*
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Manage_advertisers extends Parent_controller {

   protected $role = "admin";

   protected $menu_item = "Manage Advertisers";

   protected $date_picker = TRUE;

   protected $range;

   protected $period;

   public $temporary = array (
      'manageadvertisers_filt'    => 'all',
      'manageadvertisers_columns' => 'all',
        'manageadvertisers_quicksearch' => ''
   );

   /**
    * Hooks which extend controllers functionality
    * 
    * @var array
    */
   protected $_hooks = array();

   /**
   * конструктор класса,
   * подключает необходимые js библиотеки, загружает модели и хелперы
   *
   * @return ничего не возвращает
   */
   public function Manage_advertisers() {
      parent::Parent_controller();
      $this->_add_ajax();
      $this->load->model('entity', '', TRUE);
      $this->load->helper('fields');
      $this->load->library('Table_Builder');
      
      // load hooks
        $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->admin->manage_advertisers_extended)) {
         foreach($pluginsConfig->admin->manage_advertisers_extended as $hookClass) {
             $hookObj = new $hookClass();
             if ($hookObj instanceof Sppc_Admin_ManageAdvertisers_EventHandlerInterface) {
                $this->_hooks[] = $hookObj;
             }
           }
      }
   } //end Manage_advertisers

   /**
   * выполнение различных действий над выбранными рекламодателями
   *
   * @param string $action действие, которое необходимо выполнить
   * @return ничего не возвращает
   */
   protected function _actions($action) {
      $id_entities = $this->input->post('id_entity');
      if (is_array($id_entities)) {
         foreach ($id_entities as $code) {
            $id_entity = type_cast($code, 'textcode');
            switch ($action) {
               case 'pause':
                  if ($this->advertisers->pause($id_entity)) {
                     $this->_set_notification('Selected advertiser accounts was paused.');
                  } else {
                     $this->_set_notification('Selected advertiser accounts wasn\'t paused.');
                  }
                  break;
               case 'resume':
                  if ($this->advertisers->resume($id_entity)) {
                     $this->_set_notification('Selected advertiser accounts was resumed.');
                  } else {
                     $this->_set_notification('Selected advertiser accounts wasn\'t resumed.');
                  }
                  break;
               case 'approve':
                  if ($this->advertisers->approve($id_entity)) {
                     $this->_set_notification('Selected advertiser accounts was approved.');
                  } else {
                     $this->_set_notification('Selected advertiser accounts wasn\'t approved.');
                  }
                  break;
               case 'delete':
                  if ($this->advertisers->delete($id_entity)) {
                     $this->_set_notification('Selected advertiser accounts was successfully deleted.');
                  } else {
                     $this->_set_notification('Selected advertiser accounts wasn\'t deleted.');
                  }
                  break;
               case 'restore':
                  if ($this->advertisers->restore($id_entity)) {
                     $this->_set_notification('Selected advertiser accounts was successfully restored.');
                  } else {
                     $this->_set_notification('Selected advertiser accounts wasn\'t restored.');
                  }
                  break;
            }
         }
      }
   } //end _action

   /**
   * создание и вывод HTML-кода для управления рекламодателями
   *
   * @return ничего не возвращает
   */
   public function index() {
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Admin'),__('Manage Advertisers'))));
      $this->_set_help_index("manage_advertisers");
      $this->load->library("form");
      $this->load->helper('periods');
      $form = array(
         'name' => 'manageadvertisers_form',
         'view' => 'admin/manage_advertisers/template.html',
         'no_errors' => 'true',
         'vars' => array(
            'DATEFILTER' => period_html('manageadvertisers')
         ),
         'fields' => array(
            'filt' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'select',
               'options' => array(
                  'all' => __('all'),
                  'active' => __('pl_active'),
                  'blocked' => __('pl_blocked'),
                  'deleted' => __('pl_deleted'),
                  'activation' => __('pl_unapproved')
               )
            ),
            'quicksearch' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'text'
            )
         )
      );
      $this->load->library("Plugins", array(
             'path'      => array('admin', 'manage_advertisers_global'),
           'interface' => 'Sppc_Admin_ManageAdvertisers_GlobalHandlerInterface'));
      
      $form['vars']['PLUGIN_JS_FUNCTIONS'] = implode($this->plugins->run('get_js_functions_html', $this));
      
      data_range_fields($form, 'select', 'today');
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);
      $this->load->model('advertisers');
      $this->_actions($this->input->post('manageadvertisers_action'));
      $this->load->library('Table_Builder');
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = true;
      $this->table_builder->init_sort_vars('manageadvertisers', 'id', 'asc');
      $this->load->model('pagination_post');
      $this->pagination_post->set_form_name('manageadvertisers');
      
        // All pages total
        $total = $this->advertisers->total(
           $this->temporary['manageadvertisers_filt'], 
           $this->range, 
           $this->temporary['manageadvertisers_quicksearch']
        );
        
      $this->pagination_post->set_total_records($total['cnt']);
      $this->pagination_post->read_variables('manageadvertisers', 1, $this->global_variables->get('AdvertisersPerPage'));
      
      $list = $this->advertisers->get(
          $this->temporary['manageadvertisers_page'],
          $this->temporary['manageadvertisers_per_page'],
          $this->table_builder->sort_field,
          $this->table_builder->sort_direction,
          $this->temporary['manageadvertisers_filt'],
          $this->range,
          $this->temporary['manageadvertisers_quicksearch']
      );
      
      $col_index = 0;
      $col_alias = array('checkAll' => $col_index++);
        $col_alias['id'] = $col_index++;
        $col_alias['name'] = $col_index++;
        $col_alias['e_mail'] = $col_index++;
        $col_alias['creation_date'] = $col_index++;
        $col_alias['status'] = $col_index++;
        $col_alias['ballance'] = $col_index++;
        $col_alias['impressions'] = $col_index++;
        $col_alias['clicks'] = $col_index++;
        $col_alias['ctr'] = $col_index++;
        $col_alias['spent'] = $col_index++;
        $col_alias['action'] = $col_index++;
        
        $this->load->library("Plugins", array(
             'path' => array('admin', 'manage_advertisers'),
           'interface' => 'Sppc_Admin_ManageAdvertisers_Interface'
          ), 'coupons_plugin');

         $this->coupons_plugin->run('getAdditionalColumns', $col_alias);
      
         // register additional columns from plugins
         foreach($this->_hooks as $hookObj) {
             $col_alias = $hookObj->extendColumnMap($col_alias);
         }
      
      $this->table_builder->set_cell_content(0, $col_alias['checkAll'], array ('name'=>'checkAll', 'extra'=>'onclick="return select_all(\'manageadvertisers\', this)"'), 'checkbox');
      $this->table_builder->sorted_column($col_alias['id'], "id", "ID", "asc");
      $this->table_builder->sorted_column($col_alias['name'], "name", "Full Name", "asc");
      $this->table_builder->sorted_column($col_alias['e_mail'], "e_mail", "E-Mail", "asc");
      $this->table_builder->sorted_column($col_alias['creation_date'], "creation_date", "Join Date", "desc");
      $this->table_builder->sorted_column($col_alias['status'], "status", "Status", "asc");
      $this->table_builder->sorted_column($col_alias['ballance'], "ballance", "Ballance", "desc");
      $this->table_builder->sorted_column($col_alias['impressions'], "impressions", "Impressions", "desc");
      $this->table_builder->sorted_column($col_alias['clicks'], "clicks", "Clicks", "desc");
      $this->table_builder->sorted_column($col_alias['ctr'], "ctr", "CTR", "desc");
      $this->table_builder->sorted_column($col_alias['spent'], "spent", "Spent", "desc");
      $this->table_builder->set_cell_content(0, $col_alias['action'], __("Action"));
      $this->table_builder->add_row_attribute(0, 'class', 'th');

      $this->coupons_plugin->run('getAdditionalFields', $this->table_builder);

         // create additional columns from plugins
         foreach($this->_hooks as $hookObj) {
             $hookObj->createColumns($col_alias, $this->table_builder);
         }


      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($col_alias['checkAll'], 'class', '"chkbox"');
      $this->table_builder->add_col_attribute($col_alias['id'], 'class', '"w20 chkbox"');
      $this->table_builder->add_col_attribute($col_alias['creation_date'], 'class', '"w100 center"');
      $this->table_builder->add_col_attribute($col_alias['status'], 'class', '"w100 center"');
      $this->table_builder->add_col_attribute($col_alias['ballance'], 'class', '"right"');
      $this->table_builder->add_col_attribute($col_alias['impressions'], 'class', '"right w100"');
      $this->table_builder->add_col_attribute($col_alias['clicks'], 'class', '"w50 right"');
      $this->table_builder->add_col_attribute($col_alias['ctr'], 'class', '"w50 right"');
      $this->table_builder->add_col_attribute($col_alias['spent'], 'class', '"w50 right"');
      $this->table_builder->add_col_attribute($col_alias['action'], 'class', '"center nowrap"');

         // add styles for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
             $hookObj->defineColumnStyles($col_alias, $this->table_builder);
         }

      $this->table_builder->add_attribute('class', 'xTable');

      $this->coupons_plugin->run('setAdditionalStyle', $this->table_builder);

      // Page Total
      $page_total = array(
         'balance'     => 0,
         'impressions' => 0,
         'clicks'      => 0,
         'spent'       => 0,
         'bonus'       => 0
      );

         // register additional per page statistic fields
         foreach($this->_hooks as $hookObj) {
             $page_total = $hookObj->registerPerPageStatisticFields($page_total);
         }

      $row = 1;
      foreach ($list as $id_entity => $entity) {
         $code = type_to_str($id_entity, 'textcode');

         // Calc total values
         $page_total['balance'] += $entity['ballance'];
         $page_total['impressions'] += $entity['impressions'];
         $page_total['clicks'] += $entity['clicks'];
         $page_total['spent'] += $entity['spent'];
         $page_total['bonus'] += $entity['bonus'];

            // calculate per page statistic for additional columns from plugins
            foreach($this->_hooks as $hookObj) {
               $page_total = $hookObj->calculatePerPageStatistic($page_total, $entity);
            }

            // checkbox
            $this->table_builder->set_cell_content($row, $col_alias['checkAll'],
            array (
               'name' => 'id_entity[]',
               'value' => $code,
               'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""
               ), 
               'checkbox' 
            );
            
            // email
            $this->table_builder->set_cell_content($row, $col_alias['e_mail'],
            "{$entity['email']}"
            );

            if ('deleted' != $entity['status']) {
               // actions
               $this->table_builder->set_cell_content($row, $col_alias['action'],
                  array (
                     'name' => __('Edit'),
                     'href' => "#edit",
                     'extra' => "value=\"" . __('Edit') . "\" title=\"" . __('Edit') . "\" class='guibutton floatl mr3 ico ico-edit' onclick='edit(\"$code\");'"
                  ), 
                  'link'
               );
               
               $this->table_builder->cell($row, $col_alias['action'])->add_content(
                  array (
                     'name' => __('Change Balance'),
                     'href' => "#balance",
                     'extra' => "value=\"" . __('Change Balance') . "\" title=\"" . __('Change Balance') . "\" class='guibutton floatl ico ico-money' onclick='balance(\"$code\");'"
                  ), 'link', ' '
               );
            }

            switch ($entity['status']) {
               case 'deleted':
                  $this->table_builder->add_row_attribute($row, 'class', "deleted_row");
                  break;
               case 'blocked':
                  $this->table_builder->add_row_attribute($row, 'class', "blocked_row");
                  break;
            }

            // id
            $this->table_builder->set_cell_content($row, $col_alias['id'], $id_entity);
            
            // name
            $this->table_builder->set_cell_content($row, $col_alias['name'], type_to_str($entity['name'], 'encode'));

            // registration date
            $this->table_builder->set_cell_content($row, $col_alias['creation_date'], type_to_str($entity['join_date'], 'date'));
            
            // status
            $this->table_builder->set_cell_content($row, $col_alias['status'], __('adv_'.$entity['status']));
            
            // ballance
            $this->table_builder->set_cell_content($row, $col_alias['ballance'], type_to_str($entity['ballance'], 'money'));
            
            // impressions
            $this->table_builder->set_cell_content($row, $col_alias['impressions'], type_to_str($entity['impressions'], 'integer'));
            
            // clicks
            $this->table_builder->set_cell_content($row, $col_alias['clicks'], type_to_str($entity['clicks'], 'integer'));
            
            // ctr
            $this->table_builder->set_cell_content($row, $col_alias['ctr'], type_to_str($entity['ctr'], 'float').' %');
            
            // spent
            $this->table_builder->set_cell_content($row, $col_alias['spent'], type_to_str($entity['spent'], 'money'));

            // render addtional columns from simple plugins
            $this->coupons_plugin->run('getAdditionalCellsContent', array('table' => &$this->table_builder, 'row' => $row, 'content' => type_to_str($entity['bonus'], 'money')));

            // render additional columns from plugins
            foreach($this->_hooks as $hookObj) {
               $hookObj->renderRow($row, $col_alias, $entity, $this->table_builder);
            }

         $this->table_builder->add_row_attribute($row, 'id', "tr$row");
         $row++;
      }

      if (0 == count($list)) {
         $this->table_builder->insert_empty_cells = false;
         $this->table_builder->set_cell_content (1, 0,'&nbsp;&nbsp;'.__('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($col_alias));
         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
         $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
         $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      } else {
         // Display page total
         $this->table_builder->set_cell_content($row, $col_alias['name'], __("Page total"));
         $this->table_builder->set_cell_content($row, $col_alias['ballance'], type_to_str($page_total['balance'], 'money'));
         $this->table_builder->set_cell_content($row, $col_alias['impressions'], type_to_str($page_total['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, $col_alias['clicks'], type_to_str($page_total['clicks'], 'integer'));
         $ctr = $page_total['impressions'] ? $page_total['clicks'] / $page_total['impressions'] * 100 : 0;
         $this->table_builder->set_cell_content($row, $col_alias['ctr'], type_to_str($ctr, 'float').' %');
         $this->table_builder->set_cell_content($row, $col_alias['spent'], type_to_str($page_total['spent'], 'money'));
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');
         
         // render additional columns from simple plugins
         $this->coupons_plugin->run('getAdditionalCellsContent', array('table' => &$this->table_builder, 'row' => $row, 'content' => type_to_str($page_total['bonus'], 'money')));
         
         // render per page statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
            $hookObj->renderPageStatisticRow($row, $col_alias, $page_total, $this->table_builder);
         }
         
         $row++;
         // Display all pages total
         $this->table_builder->set_cell_content($row, $col_alias['name'], __("Total"));
         $this->table_builder->set_cell_content($row, $col_alias['ballance'], type_to_str($total['balance'], 'money'));
         $this->table_builder->set_cell_content($row, $col_alias['impressions'], type_to_str($total['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, $col_alias['clicks'], type_to_str($total['clicks'], 'integer'));
         $ctr = $total['impressions'] ? $total['clicks'] / $total['impressions'] * 100 : 0;
         $this->table_builder->set_cell_content($row, $col_alias['ctr'], type_to_str($ctr, 'float').' %');
         $this->table_builder->set_cell_content($row, $col_alias['spent'], type_to_str($total['spent'], 'money'));
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');
         
            // render additional columns from simple plugins
         $this->coupons_plugin->run('getAdditionalCellsContent', array('table' => &$this->table_builder, 'row' => $row, 'content' => type_to_str($total['bonus'], 'money')));
            
            // render summary statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
            $hookObj->renderSummaryRow($row, $col_alias, $total, $this->table_builder);
         }
      }

      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         $col_alias['id'], 
         $col_alias['name'], 
         $col_alias['e_mail'], 
         $col_alias['action']
      );
      $this->table_builder->set_invariable_columns($invariable_columns);

      $html = str_replace('<%ADVERTISERS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      $buttons = $this->load->view('admin/manage_advertisers/buttons.html', '', TRUE);
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      $this->_set_content($html);
      $this->_display();
   } //end index

   /**
   * подготавливает данные статистики для периода времени заданного пользователем в форме,
   * callback-функция для библиотеки form
   *
   * @param array $fields список полей формы и их сначений
   * @return string всегда 'error' - запрещает выход из формы
   */
   public function _create($fields) {
      period_save('manageadvertisers', $fields);
      $this->temporary['manageadvertisers_filt'] = $fields['filt'];
      $this->temporary['manageadvertisers_quicksearch'] = $fields['quicksearch'];
      $this->range = data_range($fields);
      return "error";
   } //end _create

   /**
   * подготавливает данные статистики для периода времени по умолчанию,
   * callback-функция для библиотеки form
   *
   * @return array пустой массив (обязательное возвращаемое значение callback-функции)
   */
   public function _load() {
      $fields = period_load('manageadvertisers', 'select', 'today');
      $fields['filt'] = $this->temporary['manageadvertisers_filt'];
      $fields['quicksearch'] = $this->temporary['manageadvertisers_quicksearch'];
      $this->range = data_range($fields);
      return $fields;
   } //end _load
   
} //end class Manage_advertisers

?>