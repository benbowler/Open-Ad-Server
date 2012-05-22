<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * контроллер центра отчетов 
 * 
 * @author Владимир Юдин
 * @project SmartPPC 6
 * @version 1.0.0
 */
abstract class Parent_Reports_Center extends Parent_Controller {
   
   protected $date_picker = TRUE;
   
   protected $date_range;
   
   protected $report_group = 0;
   
   public $form_prefix = '';
   
   public $temporary = array();
   
   /**
    *
    * @return ничего не возвращает
    */
   public function __construct() {
      //Значения временных полей заполняются parent_controller
      $this->temporary = array(
            $this->form_prefix . '_reqreports_type_filt' => 'all', 
            $this->form_prefix . '_reqreports_columns' => 'all', 
            $this->form_prefix . '_reptemplates_columns' => 'all');
      
      parent::__construct();

      $this->_add_ajax();
      $this->load->helper('periods');
      $this->load->library('form');
      
      $this->search_entities_controller = "";
      $this->clone_report_controller = "";
      $this->view_report_controller = "";
      $this->after_create_controller = "";
      $this->save_defaults_controller = "";
      $this->view_report_from_controller = "";
      
      $this->content_template = "common/reports_center/template.html";
      $this->create_report_template = 'common/reports_center/create_report.html';
      $this->req_report_buttons_template = "common/reports_center/buttons.html";
      
      $this->all_entities_tab_label = "All Users";
      $this->selected_entities_tab_label = "Selected Users";
      $this->entities_filter_label = "Users Filter";
      $this->search_result_label = "Users";
      $this->choosed_entities_label = "Choosed Users";
      $this->select_entity_alert = "Select at least one user!";
      
      $this->display_entities_selects = false;
      $this->display_actions_in_table = true;
   } //end Parent_reports_center
   

   /**
    * генерация формы для создания нового отчета
    *
    * @return string HTML-код формы для создания нового отчета
    */
   protected function _create_report() {

      $this->load->model('report_types');
      $visible_columns = $this->report_types->get_visible_columns($this->role, $this->user_id, $this->report_group);
      $column_title = $this->load->view('common/reports_center/column_title.html', '', TRUE);
      $column_checkbox = $this->load->view('common/reports_center/column_checkbox.html', '', TRUE);
      $manage_columns = $this->load->view('common/reports_center/manage_columns.html', '', TRUE);
      $manage = '';
      foreach ($visible_columns as $id_report_type => $report_type) {
         $titles = '';
         $checkboxes = '';
         $vis = 0;
         $bit = 1;
         foreach ($report_type as $name => $field) {
            $title = str_replace('<%ID%>', $id_report_type . '-' . $name, $column_title);
            $title = str_replace('<%STYLE%>', $field['visible'] ? '' : " style='display:none'", $title);
            $titles .= str_replace('<%TITLE%>', $field['title'], $title);
            $checkbox = str_replace('<%ID%>', $id_report_type . '-' . $name, $column_checkbox);
            $checkbox = str_replace('<%NAME%>', $name, $checkbox);
            $checkbox = str_replace('<%CHECKED%>', $field['visible'] ? 'checked="checked"' : '', $checkbox);
            $checkbox = str_replace('<%DISABLED%>', $field['is_unchanged'] ? 'disabled="disabled"' : '', $checkbox);
            $checkboxes .= str_replace('<%TITLE%>', $field['title'], $checkbox);
            if ($field['visible']) {
               $vis = $vis | $bit;
            }
            $bit = $bit << 1;
         }
         $man = str_replace('<%TITLES%>', $titles, $manage_columns);
         $man = str_replace('<%ID%>', 'report_type_' . $id_report_type, $man);
         $man = str_replace('<%VISVALUE%>', $vis, $man);
         $manage .= str_replace('<%CHECKBOXES%>', $checkboxes, $man);
      }
      $form = array(
            'name' => 'create_report', 
            'view' => $this->create_report_template, 
            'redirect' => $this->after_create_controller, 
            'no_errors' => 'true', 
            'vars' => array(
                  'DATEFILTER' => period_html('repcreate', '', ''), 
                  'SEARCH_ENTITIES_CONTROLLER' => $this->site_url .$this->index_page. $this->search_entities_controller,
                  'SAVE_DEFAULTS_CONTROLLER' => $this->site_url .$this->index_page. $this->save_defaults_controller,
                  'CLONE_REPORT_CONTROLLER' => $this->site_url .$this->index_page. $this->clone_report_controller,
                  'PERIODCASE' => get_all_periods(), 
                  'MANAGECOLUMNS' => $manage, 
                  'ALL_ENTITIES_TAB_LABEL' => $this->all_entities_tab_label, 
                  'SELECTED_ENTITIES_TAB_LABEL' => $this->selected_entities_tab_label, 
                  'ENTITIES_FILTER_LABEL' => $this->entities_filter_label, 
                  'SEARCH_RESULT_LABEL' => $this->search_result_label, 
                  'CHOOSED_ENTITIES_LABEL' => $this->choosed_entities_label, 
                  'SELECT_ENTITY_ALERT' => $this->select_entity_alert, 
                  'DISPLAY_ENTITIES_SELECTS' => $this->display_entities_selects ? 'true' : 'false', 
                  'CREATEOPEN' => ($this->input->post('edit_code') === FALSE && $this->input->post('feed_list') === FALSE) ? 'false' : 'true'), 
            'fields' => array(
                  'report_type' => array(
                        'id_field_type' => 'int', 
                        'form_field_type' => 'select', 
                        'options' => 'report_types', 
                        'params' => array(
                              'role' => $this->role, 
                              'group' => $this->report_group)), 
                  'report_name' => array(
                        'display_name' => 'Report Name', 
                        'id_field_type' => 'string', 
                        'form_field_type' => 'text'), 
                  'defname' => array(
                        'id_field_type' => 'string', 
                        'form_field_type' => 'hidden', 
                        'default' => 'true'), 
                  'extra' => array(
                        'id_field_type' => 'string', 
                        'form_field_type' => 'hidden')));
      
      $selected_entities = $this->input->post('edit_code');
      if ($selected_entities) {
         $form['vars']['DEFGROUP'] = 'selEnt';
         $form['fields']['ent_choose']['form_field_type'] = 'select';
         $this->load->model('entity');
         $codes = explode(',', $selected_entities);
         foreach ($codes as $id_code) {
            $id_entity = type_cast($id_code, 'textcode');
            $entity = $this->entity->get_name_and_mail($id_entity);
            $form['fields']['ent_choose']['options'][$id_entity] = "$entity->name ($entity->e_mail)";
         }
      }
      $selected_feeds = $this->input->post('feed_list');
      
      if ($selected_feeds) {
      	$form['fields']['report_type']['default'] = 3;
         $form['vars']['DEFGROUP'] = 'selFeed';
         $form['fields']['feed_choose']['form_field_type'] = 'select';
         $form['fields']['feed_choose']['options'] = array();
         $this->load->model('feeds');
         $codes = explode(',', $selected_feeds);
         foreach ($codes as $id_code) {
            $id_feed = type_cast($id_code,'textcode');
           
            $feed = $this->feeds->get_feed($id_feed);
            if ($feed) {
               $form['fields']['feed_choose']['options'][$id_feed] = type_to_str($feed->title, 'encode');
            }
         } 
         
      }
      data_range_fields($form, 'select', 'today');
      
      return $this->form->get_form_content('create', $form, $this->input, $this);
   } //end _create_report      

   /**
    * совершает выбранное действие над выделенными отчетами
    *
    * @param $action действие производимое над отчетами (delete)
    * @return ничего не возвращает
    */
   public function _action_reports($action) {
      $id_reports = $this->input->post('id_report');
      if (is_array($id_reports)) {
      	 foreach ($id_reports as $code) {
		 	$id_report = type_cast($code, 'textcode');
		    $this->report_types->action($action, $this->user_id, $id_report);
		 }
      	 switch ($action) {
      	 	case 'delete':
		      	$this->_set_notification('Selected reports was successfully deleted.');		
		       	break;
      	 }         
      }   
   } //end _action_reports   
   
   /**
    * возвращает HTML-код таблицы со списком запрошенных отчетов
    *
    * @return string HTML-код таблицы со списком запрошенных отчетов
    */
   protected function _report_table() {

      $form = array(
            'id' => 'reports', 
            'name' => $this->form_prefix . '_reqreports_form', 
            'view' => 'common/reports_center/table.html', 
            'redirect' => $this->after_create_controller, 
            'no_errors' => 'true', 
            'vars' => array(
                  'DATEFILTER' => period_html($this->form_prefix . '_reqreports'), 
                  'VIEW_REPORT_CONTROLLER' => $this->site_url .$this->index_page. $this->view_report_controller,
                  'VIEW_FROM_CONTROLLER' => $this->view_report_from_controller, 
                  'MENUITEM' => $this->menu_item, 
                  'FORM_PREFIX' => $this->form_prefix)

            , 
            'fields' => array(
                  'type_filt' => array(
                        'id_field_type' => 'string', 
                        'form_field_type' => 'select', 
                        'options' => 'report_types', 
                        'params' => array(
                              'group' => $this->report_group, 
                              'role' => $this->role, 
                              'all' => TRUE))));
            
       
      data_range_fields($form, 'select', 'alltime');
      
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);

      $this->load->model('report_types');
      
      $this->_action_reports($this->input->post($this->form_prefix . '_reqreports_action'));
      
      
      $this->load->library('Table_Builder');
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = false;
      $this->table_builder->init_sort_vars($this->form_prefix . '_reqreports', 'request_date', 'desc');
      $this->load->model('pagination_post');
      
      $this->pagination_post->set_form_name($this->form_prefix . '_reqreports');
      $this->pagination_post->set_total_records($this->report_types->total($this->user_id, $this->date_range, $this->role, $this->temporary[$this->form_prefix . '_reqreports_type_filt'], $this->report_group));
      $this->pagination_post->read_variables($this->form_prefix . '_reqreports', 1, $this->global_variables->get('ReportsPerPage'));

      $reports = $this->report_types->get_reports($this->user_id, 
                         $this->temporary[$this->form_prefix . '_reqreports_page'], 
                         $this->temporary[$this->form_prefix . '_reqreports_per_page'], 
                         $this->table_builder->sort_field, 
                         $this->table_builder->sort_direction, 
                         $this->date_range, $this->role, 
                         $this->temporary[$this->form_prefix . '_reqreports_type_filt'], 
                         $this->report_group);

      $col_index = 0;
      $col_alias = array(
            'checkboxes' => $col_index++, 
            'id_report' => $col_index++, 
            'custom_title' => $col_index++, 
            'title' => $col_index++);
      
      $col_alias['date'] = $col_index++;
      
      if ($this->display_actions_in_table) {
         $col_alias['action'] = $col_index++;
      }
      
      $this->table_builder->set_cell_content(0, $col_alias['checkboxes'], array(
            'name' => 'checkAll', 
            'extra' => 'onclick="return select_all(\'' . $this->form_prefix . '_reqreports\', this)"'), 'checkbox');
      $this->table_builder->sorted_column($col_alias['id_report'], "id_requested_report", "ID", "asc");
      $this->table_builder->sorted_column($col_alias['custom_title'], "custom_title", "Report Name", "asc");
      $this->table_builder->sorted_column($col_alias['title'], "title", "Report Type", "asc");
      $this->table_builder->sorted_column($col_alias['date'], "request_date", "Created", "desc");
      
      if ($this->display_actions_in_table) {
         $this->table_builder->set_cell_content(0, $col_alias['action'], __('Action'));
      }
      $this->table_builder->add_row_attribute(0, 'class', 'th');
      
      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($col_alias['checkboxes'], 'class', '"chkbox"');
      $this->table_builder->add_col_attribute($col_alias['id_report'], 'class', '"w20"');
      $this->table_builder->add_col_attribute($col_alias['date'], 'class', '"center w100"');
      
      if ($this->display_actions_in_table) {
         $this->table_builder->add_col_attribute($col_alias['action'], 'class', '"center nowrap"');
      }
      
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
            $col_alias['id_report'], 
            $col_alias['custom_title']);
      
      if ($this->display_actions_in_table) {
         $invariable_columns[] = $col_alias['action'];
      }
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      $this->table_builder->add_attribute('class', 'xTable');
      
      $clone_icon = __('Clone');
      
      $row = 1;
      foreach ($reports as $id_report => $report) {
         $code = type_to_str($id_report, 'textcode');
         $this->table_builder->set_cell_content($row, $col_alias['checkboxes'], array(
               'name' => 'id_report[]', 
               'value' => $code, 
               'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""), 'checkbox');
         $this->table_builder->set_cell_content($row, $col_alias['id_report'], $id_report);
         $this->table_builder->set_cell_content($row, $col_alias['custom_title'], array(
               'name' => type_to_str($report['title'], 'encode'), 
               'href' => "#view_report", 
               'extra' => "onclick='viewReport(\"$code\"); return false;'"), 'link');
         $this->table_builder->set_cell_content($row, $col_alias['title'], $report['type']);
         
         $this->table_builder->set_cell_content($row, $col_alias['date'], $report['date']);
         if ($this->display_actions_in_table) {
            $this->table_builder->set_cell_content($row, $col_alias['action'], array(
                  'name' => $clone_icon, 
                  'href' => "#create_report", 
                  'extra' => "class='guibutton floatl ico ico-copy' value=\"$clone_icon\"  title=\"$clone_icon\" onclick='cloneReport(\"$code\");'"), 'link');
         }
         $this->table_builder->add_row_attribute($row, 'id', "tr$row");
         $this->table_builder->add_row_attribute($row, 'id_report', type_to_str($id_report, 'textcode'));
         $row++;
      }
      if (0 == count($reports)) {
         $this->table_builder->insert_empty_cells = false;
         $this->table_builder->set_cell_content(1, 0, __('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($col_alias));
         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
         $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
         $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      }
      $html = str_replace('<%REPORTS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      $buttons = $this->parser->parse($this->req_report_buttons_template, array(
            'FORM_PREFIX' => $this->form_prefix));
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      return $html;
   } //end _report_table
   
   /**
    * функция котроллера, вызываемая по умолчанию
    *
    * @return ничего не возвращает
    */
   public function index() {
      if (!is_null($this->global_variables->get('ReportCreated', $this->user_id))) {
         $this->_set_notification('Report was successfully Created!');
         $this->global_variables->kill($this->user_id, 'ReportCreated');
      }
      
      $this->vars = array(
            'CREATEREPORT' => $this->_create_report(), 
            'TABLE' => $this->_report_table(), 
            'MENU_ITEM' => $this->menu_item, 
            'CREATE_REPORTS_BUTTONS' => $this->_get_create_report_buttons());
      $this->_set_content($this->parser->parse($this->content_template, $this->vars, TRUE));
      $this->_set_help_index($this->role . "_reports_center");
      $this->_display();
   } //end index   

   /**
    * Prepare create report buttons to display
    * @author Anton Potekhin
    * @return array
    */
   protected function _get_create_report_buttons() {
      $buttons = array();
      $cobj = get_instance();
      $cobj->load->model('report_groups');
      $cobj->load->model('roles');
      $idRole = $cobj->roles->get_role_by_name($cobj->role)->id_role;
      $rows = $cobj->report_groups->getList($idRole);
      foreach ($rows as $row) {
         $buttons[] = array(
               'TITLE' => __('Create ' . $row->title), 
               'REPORT_GROUP_CONTROLLER' => $row->controller);
      }
      return $buttons;
   }
   
   /**
    * AJAX функция, сохраняет настройки видимых столбцов для заданного пользователя и отчета
    *
    * @return ничего не возвращает
    */
   public function save_defaults() {
      $id_report_type = $this->input->post('id_report_type');
      $columns = $this->input->post('columns');
      $this->load->model('report_types');
      $this->report_types->save_visible_columns($this->user_id, $id_report_type, $columns);
   } //end save_default
   

   /**
    * AJAX функция, считывает параметры
    *
    * @return ничего не возвращает
    */
   public function clone_report() {
      $id_report_type = $this->input->post('id_report_type');
      $code = $this->input->post('code');
      $id_report = type_cast($code, 'textcode');
      $this->load->model('report_types');
      $report = $this->report_types->report($this->user_id, $id_report);
      
      $extra = array();
      $response = array(
            'isValid' => false);
      
      if ($report) {
         $response['isValid'] = true;
         $response['title'] = $report['title'];
         $response['type'] = $report['type'];
         $response['vis'] = $report['vis'];
         $response['from'] = $report['from'];
         $response['to'] = $report['to'];
         $response['extra'] = $extra;
      }
      echo json_encode($response);
      exit();
   } //end save_default   
   

   /**
    * Callback-функция, устанавливает значения по умолчанию для фильтров таблиц
    *
    * @return array массив со значениями по умолчанию для фильтров
    */
   public function _load($id) {
      $fields = period_load($this->form_prefix . '_reqreports', 'select', 'alltime');
      $this->date_range = data_range($fields);
      $fields['type_filt'] = $this->temporary[$this->form_prefix . '_reqreports_type_filt'];
      return $fields;
   } //end _load
   

   /**
    * Callback-функция, сохраняет установленные пользователем значения для фильтров таблиц
    *
    * @return string непустая строка для подавления succes-режима формы
    */
   public function _save($id, $fields) {

      $this->date_range = data_range($fields);
      $this->temporary[$this->form_prefix . '_reqreports_type_filt'] = $fields['type_filt'];
      period_save($this->form_prefix . '_reqreports', $fields);
      return 'false';
   } //end _save
   

   /**
    * Callback-функция, проверяет введенные данные и сохраняет новый запрос отчета
    *
    * @param array $fields массив с полями заполненными пользователем
    * @return string при неудаче - текст ошибки
    */
   public function _create($fields) {
      $this->global_variables->set('ReportCreated', 'true', $this->user_id);
      $this->load->model('report_types');
      $this->load->helper('periods');
      $range = data_range($fields);
      if(is_null($fields['extra'])) {
         $fields['extra'] = '';
      }
      $id_report = $this->report_types->add_new_report(
         $this->user_id, 
         $fields['report_type'], 
         $this->input->post('vis_report_type_' . $fields['report_type']), 
         $range['from'], 
         $range['to'], 
         $fields['report_name'], 
         $fields['extra']);
      if (!$id_report) return "Can't create new report request.";
      $this->load->model("report_generator", "", TRUE);
      $this->load->model("sqlite");
      $sqlite_file = $this->report_generator->get_sqlite_file($id_report);
      $sqlite = $this->sqlite->get_database($sqlite_file);
      if (!$sqlite) {
         $this->report_types->action('delete', $this->user_id, $id_report);
         return "Can't create SQLite database object.";
      }
      $this->report_generator->generate_report($id_report, $sqlite);
      return '';
   } //end create
   

   /**
    * AJAX функция, возвращает список cущностей по маске
    *
    * @return array
    */
   public function _entities($mask, $role) {
      $this->load->model('entity', '', TRUE);
      $list = $this->entity->get_list_by_name_or_mail($mask, $role);
      $result = array();
      if (!is_null($list)) {
         foreach ($list as $entity) {
            $result[$entity['id_entity']] = $entity['name'] . ' (' . $entity['e_mail'] . ')';
         }
      }
      return $result;
   } //end _entities      

   public function not_found() {
      $this->_set_notification('Sorry! Report file not found.', 'error');
      $this->index();
   }

} //end Parent_reports_center class


?>