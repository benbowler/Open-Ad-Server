<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * контроллер управления фидами
 * 
 * @author Владимир Юдин
 * @project SmartPPC 6
 * @version 1.0.0  
 */
class Manage_feeds extends Parent_controller {
   
   protected $role = "admin";
   
   protected $menu_item = "Manage Feeds";
   
   protected $date_picker = TRUE;
   
   protected $range;
   
   protected $period;
   
   public $temporary = array('managefeeds_filt' => 'all', 'managefeeds_columns' => 'all');
   
   /**
    * конструктор класса,
    * подключает необходимые js библиотеки, загружает модели и хелперы
    *
    * @return ничего не возвращает
    */
   public function Manage_feeds() {
      parent::Parent_controller();
      $this->_add_ajax();
      
      $this->load->model('entity', '', TRUE);
      $this->load->helper('fields');
      $this->load->library('Table_Builder');
      $this->load->model('feeds');
      $this->load->library("form");
   } //end Manage_feeds
   

   /**
    * выполнение различных действий над выбранными фидами
    *
    * @param string $action действие, которое необходимо выполнить
    * @return ничего не возвращает
    */
   protected function _actions($action) {
      $id_feeds = $this->input->post('id_feed');
      if (is_array($id_feeds)) {
         foreach ($id_feeds as $code) {
            $id_feed = type_cast($code, 'textcode');
            switch ($action) {
               case 'pause':
                  $this->feeds->pause($id_feed);
                  $this->_set_notification('Selected feeds was paused.');
               break;
               case 'resume':
                  $this->feeds->resume($id_feed);
                  $this->_set_notification('Selected feeds was resumed.');
               break;
            }
         }
      }
   } //end _action
   

   /**
    * создание и вывод HTML-кода для управления фидами
    *
    * @return ничего не возвращает
    */
   public function index() {
      $this->_set_title(implode(self::TITLE_SEP, array(__('Admin'), __('Manage Feeds'))));
      $this->_set_help_index("manage_feeds");
      $this->load->helper('periods');
      $form = array(
         'id' => 0, 
         'name' => 'managefeeds_form', 
         'view' => 'admin/manage_feeds/template.html', 
         'no_errors' => 'true', 
         'vars' => array(
            'DATEFILTER' => period_html('managefeeds')), 
            'fields' => array(
               'filt' => array(
                  'id_field_type' => 'string', 
                  'form_field_type' => 'select', 
                  'options' => array(
                     'all' => __('all'), 
                     'active' => __('pl_enabled'), 
                     'paused' => __('pl_disabled')
               )
            )
         )
      );
      data_range_fields($form, 'select', 'today');
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);
      $this->_actions($this->input->post('managefeeds_action'));
      $this->load->library('Table_Builder');
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = true;
      $this->table_builder->init_sort_vars('managefeeds', 'id', 'asc');
      $this->load->model('pagination_post');
      $this->pagination_post->set_form_name('managefeeds');
      // All pages total
      $total = $this->feeds->total($this->temporary['managefeeds_filt'], $this->range);
      $this->pagination_post->set_total_records($total['cnt']);
      $this->pagination_post->read_variables('managefeeds', 1, $this->global_variables->get('FeedsPerPage'));
      $list = $this->feeds->select(
         $this->temporary['managefeeds_page'], 
         $this->temporary['managefeeds_per_page'], 
         $this->table_builder->sort_field, 
         $this->table_builder->sort_direction, 
         $this->temporary['managefeeds_filt'], 
         $this->range);
      $this->table_builder->set_cell_content(0, 0, array('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'managefeeds\', this)"'), 'checkbox');
      $this->table_builder->sorted_column(1, "id", "ID", "asc");
      $this->table_builder->sorted_column(2, "title", "Feed Name", "asc");
      $this->table_builder->sorted_column(3, "status", "Status", "asc");
      $this->table_builder->sorted_column(4, "impressions", "Impressions", "desc");
      $this->table_builder->sorted_column(5, "clicks", "Clicks", "desc");
      $this->table_builder->sorted_column(6, "ctr", "CTR", "desc");
      $this->table_builder->sorted_column(7, "earned_admin", "Revenue", "desc");
      $this->table_builder->set_cell_content(0, 8, __("Action"));
      $this->table_builder->add_row_attribute(0, 'class', 'th');
      
      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute(0, 'class', '"chkbox simpleTitle"');
      $this->table_builder->add_col_attribute(1, 'class', '"w20 chkbox"');
      //$this->table_builder->add_col_attribute(2, 'class', '"w20"');
      $this->table_builder->add_col_attribute(3, 'class', '"w100 center"');
      $this->table_builder->add_col_attribute(4, 'class', '"w100 right"');
      $this->table_builder->add_col_attribute(5, 'class', '"w50 right"');
      $this->table_builder->add_col_attribute(6, 'class', '"w50 right"');
      $this->table_builder->add_col_attribute(7, 'class', '"w100 right"');
      $this->table_builder->add_col_attribute(8, 'class', '"w100 center nowrap simpleTitle"');
      $this->table_builder->add_attribute('class', 'xTable');
      $row = 1;
      
      // Page Total
      $page_total = array(
         'impressions' => 0,
         'clicks'      => 0,
         'revenue'     => 0
      );
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         1, 2, 10
      );
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      foreach ($list as $id_feed => $feed) {
         $code = type_to_str($id_feed, 'textcode');
         
         // Calc total values
         $page_total['impressions'] += $feed['impressions'];
         $page_total['clicks'] += $feed['clicks'];
         $page_total['revenue'] += $feed['revenue'];
      
         $this->table_builder->set_cell_content($row, 0, array('name' => 'id_feed[]', 'value' => $code, 'extra' => "id=chk$row onclick=\"checktr('chk$row','tr$row')\""), 'checkbox');
         $this->table_builder->set_cell_content($row, 1, $id_feed);
         $this->table_builder->set_cell_content($row, 2, type_to_str($feed['title'], 'encode'));
         $this->table_builder->set_cell_content($row, 3, __($feed['status']));
         
         if ('feed_paused' == $feed['status']) {
            $this->table_builder->add_row_attribute($row, 'class', "blocked_row");	
         }
         
         $this->table_builder->set_cell_content($row, 4, type_to_str($feed['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, 5, type_to_str($feed['clicks'], 'integer'));
         $this->table_builder->set_cell_content($row, 6, type_to_str($feed['ctr'], 'float') . ' %');
         $this->table_builder->set_cell_content($row, 7, type_to_str($feed['revenue'], 'money'));
         
         $this->table_builder->set_cell_content($row, 8, array('name' => __('feed_Edit'), 'href' => "#edit", 'extra' => "value='" . __('feed_Edit') . "' title='" . __('feed_Edit') . "' class='guibutton floatl ico ico-edit' onclick='edit(\"$code\");'"), 'link');
         $this->table_builder->add_row_attribute($row, 'id', "tr$row");
         $row++;
      }
      if (0 == count($list)) {
         $this->table_builder->insert_empty_cells = false;
         $this->table_builder->set_cell_content(1, 0, '&nbsp;&nbsp;' . __('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan', 10);
         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
         $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
         $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      } else {
         // Display page total
         $this->table_builder->set_cell_content($row, 2, __("Page total"));
         $this->table_builder->set_cell_content($row, 4, type_to_str($page_total['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, 5, type_to_str($page_total['clicks'], 'integer'));
         $ctr = $page_total['impressions'] ? $page_total['clicks'] / $page_total['impressions'] * 100 : 0;
         $this->table_builder->set_cell_content($row, 6, type_to_str($ctr, 'float').' %');
         $this->table_builder->set_cell_content($row, 7, type_to_str($page_total['revenue'], 'money'));
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');
         $row++;
         // Display all pages total
         $this->table_builder->set_cell_content($row, 2, __("Total"));
         $this->table_builder->set_cell_content($row, 4, type_to_str($total['impressions'], 'integer'));
         $this->table_builder->set_cell_content($row, 5, type_to_str($total['clicks'], 'integer'));
         $ctr = $total['impressions'] ? $total['clicks'] / $total['impressions'] * 100 : 0;
         $this->table_builder->set_cell_content($row, 6, type_to_str($ctr, 'float').' %');
         $this->table_builder->set_cell_content($row, 7, type_to_str($total['revenue'], 'money'));
         $this->table_builder->clear_row_attributes($row);
         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');
      }
      $html = str_replace('<%FEEDS%>', $this->table_builder->get_sort_html(), $html);
      $html = str_replace('<%PAGINATION%>', $this->pagination_post->create_form(), $html);
      $buttons = $this->load->view('admin/manage_feeds/buttons.html', '', TRUE);
      $html = str_replace('<%BUTTONST%>', str_replace('<%ID%>', 'top_', $buttons), $html);
      $html = str_replace('<%BUTTONSB%>', str_replace('<%ID%>', 'bottom_', $buttons), $html);
      $html = str_replace('<%COLUMNS%>', $this->table_builder->get_columns_html(), $html);
      $this->_set_content($html);
      $this->_display();
   } //end index
   

   /**
    * кодирование заголовков письма в MIME формате
    *
    * @param string $str текст
    * @return string закодированная строка
    */
   public function utf8($str) {
      return '=?UTF-8?B?' . base64_encode($str) . '?=';
   } //end utf8   
   

   /**
    * отсылает запрос на добавление нового фида в систему
    *
    * $param array $fields массив с полями заполненной формы
    * $return string строка ошибки, '' - в случае успеха
    */
   public function send_request($fields) {
      $this->load->library('email');
      $config['charset'] = 'utf-8';
      $config['wordwrap'] = FALSE;
      $this->email->initialize($config);
      $site_name = $this->global_variables->get('SiteName');
      $system_email = $this->global_variables->get("SystemEMail");
      $this->email->from($system_email, $this->utf8($site_name . ' robot'));
      $this->email->to($this->global_variables->get("OrbitscriptsEMail"));
      $this->email->subject($this->utf8("$site_name, New feed installation request"));
      $params = array('FEEDNAME' => $fields['feed_name'], 'COMMENT' => $fields['comment'], 'PHONE' => $fields['phone'], 'SYSTEM' => $site_name, 'EMAIL' => $system_email, 'SITEURL' => $this->site_url);
      $mail = $this->parser->parse("mails/feed_request.html", $params, TRUE);
      $this->email->message($mail);
      $send_status = $this->email->send();
      if ($send_status) {
         return '';
      } else {
         return __('An error occurred while sending E-Mail.');
      }
   } //end send_request
   

   /**
    * подготавливает данные статистики для периода времени заданного пользователем в форме,
    * callback-функция для библиотеки form
    *
    * @param array $fields список полей формы и их сначений
    * @return string всегда 'error' - запрещает выход из формы
    */
   public function _create($fields) {
      if (isset($fields['feed_name'])) {
         return $this->send_request($fields);
      }
      period_save('managefeeds', $fields);
      $this->temporary['managefeeds_filt'] = $fields['filt'];
      $this->range = data_range($fields);
      return "error";
   }
   
   /**
    * подготавливает данные статистики для периода времени по умолчанию,
    * callback-функция для библиотеки form
    *
    * @return array пустой массив (обязательное возвращаемое значение callback-функции)
    */
   public function _load($id) {
      if ($id) {
         return $this->feeds->get_settings($id);
      } else {
         $fields = period_load('managefeeds', 'select', 'today');
         $fields['filt'] = $this->temporary['managefeeds_filt'];
         $this->range = data_range($fields);
         return $fields;
      }
   } //end _load   
   

   /**
    * подстраница для редактирования данных фида
    *
    * @param POST edit_code
    * @return ничего не возвращает
    */
   public function edit() {
      $this->date_picker = FALSE;
      $this->_set_title(implode(self::TITLE_SEP, array(__('Admin'), __('Manage Feeds'), __('Feed Settings'))));
      
      $this->_set_help_index("edit_feed");
      $code = $this->input->post('edit_code');
      $id_feed = type_cast($code, 'textcode');
      $feed = $this->feeds->get_feed($id_feed);
      $form = array( 'id' => $id_feed, 
                     'name' => 'editfeed_form',
                     'view' => 'admin/manage_feeds/edit_feed.html',
                     'redirect' => 'admin/manage_feeds/success',
                     'vars' => array('FEEDNAME' => $feed->title,
                                     'MONEYFORMAT' => get_money_format(),
                                     'NUMBERFORMAT' => get_number_format()),
                     'fields' => array(
                     	'affiliate_id' => array(
                     		'id_field_type' => 'string', 
                     		'form_field_type' => 'text', 
                     		'validation_rules' => 'required', 
                     		'display_name' => 'Affiliate ID', 
                     		'max' => 100
      					), 
      					'commission' => array(
      						'id_field_type' => 'integer', 
      						'form_field_type' => 'text', 
      						'validation_rules' => 'required|integer|max_val[100]|min_val[0]', 
      						'display_name' => 'Commission, %'
      					), 
      					'edit_code' => array(
      						'form_field_type' => 'hidden', 
      						'default' => $code
      					)
      				  ));
      
      $html = $this->form->get_form_content('modify', $form, $this->input, $this);
      $this->_set_content($html);
      $this->_display();
   } //end edit
   

   /**
    * сохраняет новые парметры для заданного фида
    *
    * @param integer $id код изменяемого фида
    * @param array $fields массив с данными из полей формы 
    * @return string текст ошибки или '' при удаче
    */
   public function _save($id, $fields) {
      $this->feeds->set_settings($id, array('commission' => $fields['commission'],
      										'affiliate_id_1' => $fields['affiliate_id'] ));
      return '';
   } //end _save
   

   /**
    * подстраница запроса на получение нового фида
    *
    * @return ничего не возвращает
    */
   public function request() {
      $this->date_picker = FALSE;
      $this->_set_title(implode(self::TITLE_SEP, array(__('Admin'), __('Manage Feeds'), __('Request Feed Installation'))));
      $this->_set_help_index("request_feed");
      $form = array('name' => 'requestfeed_form', 'view' => 'admin/manage_feeds/request_feed.html', //'success_view' => 'admin/manage_feeds/success.html',
      'redirect' => 'admin/manage_feeds/success_request', 'fields' => array('feed_name' => array('id_field_type' => 'string', 'form_field_type' => 'select', 'options' => 'feeds', 'params' => array('type' => 'request')), 'comment' => array('id_field_type' => 'string', 'form_field_type' => 'textarea'), 'phone' => array('id_field_type' => 'string', 'form_field_type' => 'text', 'max' => 16)));
      $html = $this->form->get_form_content('create', $form, $this->input, $this);
      $this->_set_content($html);
      $this->_display();
   } //end request   
   

   /**
    * Вывод подтверждения об успешной отправке запроса
    *
    */
   public function success_request() {
      $this->date_picker = FALSE;
      $data = array('MESSAGE' => __('Feed request was successfully sent!'), 'REDIRECT' => $this->site_url .$this->index_page. 'admin/manage_feeds');
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success_request
   

   /**
    * Вывод уведомления об успешном изменении фида
    *
    * @param type1 var1 cmt1
    * @param type2 var2 cmt2
    * @param type3 var3 cmt3
    * @param type4 var4 cmt4
    * @return type cmt
    */
   public function success() {
      $this->date_picker = FALSE;
      $data = array('MESSAGE' => __('Feed was successfully updated!'), 'REDIRECT' => $this->site_url .$this->index_page. 'admin/manage_feeds');
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success
   
} //end class Manage_feeds


?>