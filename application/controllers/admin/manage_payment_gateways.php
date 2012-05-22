<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* Контроллер управления платежными шлюзами
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Manage_Payment_Gateways extends Parent_controller {

   protected $role = "admin";
   
   protected $menu_item = "Manage Payment Gateways";
   
   public $_col_alias = array();
   
   private $publishers_is_enabled = false;
   
 /**
   * конструктор контроллера
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      $this->temporary = array ('manage_gateways_status_filter' => null, 
                                'manage_gateways_sort_field' => null, 
                                'manage_gateways_sort_direction' => null,
                                'manage_gateways_columns' => 'all');
      parent::__construct();
      
      $this->_set_title ( implode(self::TITLE_SEP, array( __( 'Administrator' ) , __( 'Payments' ) , __( 'Manage Payment Gateways' ))));
      
      $this->load->model('roles');
      
      $enabled_roles = $this->roles->get_list(array('is_used' => 'true'));
      
      $this->publishers_is_enabled = in_array('publisher',$enabled_roles);      
   }

   
   public function _buildTableColumns() {
   	if ($this->publishers_is_enabled) { 
   	  $this->_col_alias = array(
         'checkbox' => 0,
         'id' => 1,
         'name' => 2,
         'status' => 3,
   	   'fund_status' => 4,
   	   'withdraw_status' => 5,
         'action' => 6
       );
   	} else {
   	  $this->_col_alias = array(
         'checkbox' => 0,
         'id' => 1,
         'name' => 2,
         'status' => 3,
         'action' => 4
       );
   	}
   }
   
   public function _buildTableColumnsHeaders() {
       $this->table_builder->sorted_column($this->_col_alias['id'],'id','ID','asc');
       $this->table_builder->sorted_column($this->_col_alias['name'],'name','Title','asc');
 
       $this->table_builder->sorted_column($this->_col_alias['status'],'status','Status','asc');
     
       //добавление ячеек-заголовка
       $this->table_builder->set_cell_content ( 0, 0, array ('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'manage_gateways\', this)"' ), 'checkbox' );
      
       $this->table_builder->set_cell_content ( 0, $this->_col_alias['action'], __('Action') );
      
       //прописывание стилей для ячеек
       $this->table_builder->add_col_attribute($this->_col_alias['checkbox'], 'class', '"chkbox"');
       $this->table_builder->add_col_attribute($this->_col_alias['id'], 'class', 'w20 center');
       $this->table_builder->add_col_attribute($this->_col_alias['status'], 'class', 'w100 center');

       $this->table_builder->add_col_attribute($this->_col_alias['action'], 'class', 'w100 center nowrap');
      
       $this->table_builder->add_row_attribute(0,'class', 'th');
       
       if ($this->publishers_is_enabled) { 
              $this->table_builder->set_cell_content ( 0, $this->_col_alias['fund_status'], __('Fund Status') );
	      $this->table_builder->set_cell_content ( 0, $this->_col_alias['withdraw_status'], __('Withdraw Status') );
	      $this->table_builder->add_col_attribute($this->_col_alias['fund_status'], 'class', 'w100 center');
	      $this->table_builder->add_col_attribute($this->_col_alias['withdraw_status'], 'class', 'w100 center');
       }
   }
   
   public function _reqList($params) {
   	if ($this->publishers_is_enabled) {
   		$params['fields'].=', payment_gateways.mode';
   	}
       return $this->payment_gateways->get_list($params);
   }
   
   public function _fillTableRows($gateways_array) {
    	
     if (is_null($gateways_array)) {
      $gateways_array = array();
     }
     
     $data_rows_conut = count( $gateways_array );
      
      //модификация контента отдельных столбцов (ссылки, чекбоксы)
      for($i = 0; $i < $data_rows_conut; $i ++) {
         
         
         $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['checkbox'], array ('name' => 'id_gateways[]', 'value' => $gateways_array [$i] ['id'], 'extra' => 'id=chk'.$i.' onclick="checktr(\'chk'.$i.'\',\'tr'.($i+1).'\')"'), 'checkbox' );
         $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['id'], $gateways_array [$i] ['id']);
         $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['name'], $gateways_array [$i] ['name']);
         $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['status'], __('gateway_'.$gateways_array [$i] ['status']));
         
         $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['action'], array ('name' => 'Edit', 'extra' => " value=\"{@gateway_Edit@}\" title=\"{@gateway_Edit@}\" class=\"guibutton floatl ico ico-edit\" onclick=\"go('" . $this->site_url .$this->index_page. "admin/edit_payment_gateways/index/" . $gateways_array[$i]['id'] ."');\"", 'href' => "#" ), 'link' );
         $this->table_builder->add_row_attribute( $i + 1, 'id', 'tr'.($i+1));
         if ('disabled' == $gateways_array [$i] ['status']) {
            $this->table_builder->add_row_attribute( $i + 1, 'class', 'blocked_row');
         }
         
         if ($this->publishers_is_enabled) {
         	$gateway_modes = split(',',$gateways_array [$i] ['mode']);
            
            if (in_array('deposit',$gateway_modes)) {
               $deposit_mode = 'enabled';
            } else {
               $deposit_mode = 'disabled';
            }
            $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['fund_status'], __('gateway_'.$deposit_mode));
            
            if (in_array('withdraw',$gateway_modes)) {
               $withdraw_mode = 'enabled';
            } else {
               $withdraw_mode = 'disabled';
            }
            $this->table_builder->set_cell_content ( $i + 1, $this->_col_alias['withdraw_status'], __('gateway_'.$withdraw_mode));
         }
      }
      
      if (0 == $data_rows_conut) {
         $this->table_builder->set_cell_content (1, 0,'&nbsp;&nbsp;'.__('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($this->_col_alias));         
            $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
            $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
            $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      }      
   }
   
   /**
    * вызывает функцию соответствующую действию пользователя по управлению списком платежных шлюзов c последующим отображением списка 
    *
    * @return ничего не возвращает
    */
   public function index() {
       $this->load->model( 'payment_gateways' );
       
      $this->manage_gateways_action = $this->input->post ( 'manage_gateways_action' );
      switch ( $this->manage_gateways_action) {
         case 'enable' :
            $this->set_status('enabled');
            $this->_set_notification('Payment gateways were enabled successfully!');
         break;
         case 'disable' :
            $this->set_status('disabled');
            $this->_set_notification('Payment gateways were disabled successfully!');
         break;
      }
      
      //после изменения статуса платежных шлюзов отображение их списка
      $this->manage_gateways_action = "show_list";
      $this->show_list ();
   }
   
   /**
    * нзменение статуса платежного шлюза 
    *
    * @return ничего не возвращает
    */
   protected function set_status($status) {
      $id_gateways = $this->input->post ( 'id_gateways' );
      if (is_array ( $id_gateways )) {
         foreach ($id_gateways as $id) {
             if (is_numeric($id)) {
                $this->payment_gateways->set_status ( $id , $status);
             }
         }
      }
   }
   
  public function show_list() {
     $this->load->library ( 'Table_Builder' );
     $this->load->model( 'pagination_post' );
     $this->load->model( 'orbit_news' );
     
      //режим фильтрации по статусу шлюза
      $status_filter = $this->input->post ( 'manage_gateways_status_filter' );
      if (! $status_filter) {
         $status_filter = $this->temporary ['manage_gateways_status_filter'];
         if (is_null ( $status_filter )) {
            $status_filter = 'all';
         }
      }
     
     $this->pagination_post->clear();
     $this->pagination_post->set_form_name('manage_gateways');
     $this->pagination_post->set_total_records ( $this->payment_gateways->get_count($status_filter) );
     $this->pagination_post->read_variables('manage_gateways',1,10);
     
     //настройка параметров разбиения на страницы
     $pagination = $this->pagination_post->create_form ();
     
     $this->table_builder->clear ();
     //установка атрибутов таблицы
     $this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
     $this->table_builder->insert_empty_cells = false;
     $this->table_builder->init_sort_vars('manage_gateways', 'id_gateway', 'asc');
     
     $this->_buildTableColumns();

     $this->_buildTableColumnsHeaders();
     
     $params = array ('fields' => 'payment_gateways.id_entity as id, name, '
                                 .'payment_gateways.status',
     'sort' => array('field' => $this->table_builder->sort_field, 'direction' => $this->table_builder->sort_direction),
     'filter' => $status_filter, 'subset' => array('offset' => ($this->pagination_post->get_page() - 1)*$this->pagination_post->get_per_page(),'limit' => $this->pagination_post->get_per_page() ));                          
                                 
     $gateways_array = $this->_reqList($params);

     $this->_fillTableRows($gateways_array);
      
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         $this->_col_alias['id'], $this->_col_alias['status'], $this->_col_alias['action']
      );
      $this->table_builder->set_invariable_columns($invariable_columns);
      
     $gateways_table = $this->table_builder->get_sort_html ();
     $columns = $this->table_builder->get_columns_html();
       
     
     
     $payment_gateways_news = $this->orbit_news->get_payment_gateways_news(array('limit' =>1));
     
     if (!is_null($payment_gateways_news)) {

      $infobox_content = $this->parser->parse('admin/settings/manage_payment_gateways/infobox.html',
                                    array('TITLE' => __($payment_gateways_news[0]['title']), 
                                          'DESCRIPTION' => __(nl2br($payment_gateways_news[0]['description'])), 
                                          'URL' => $this->site_url.$this->index_page.'admin/manage_payment_gateways/request'),TRUE);
     //                                     'URL' => $payment_gateways_news[0]['link']),TRUE);
     } else {
        $infobox_content = '';
     }
     
     //заполнение структуры формы для фильтрации записей
     $form = array ("name" => "manage_gateways_form",
                    "vars" => array('PAGINATION' => $pagination, 'TABLE' => $gateways_table, 'INFOBOX' => $infobox_content, 'COLUMNS' => $columns), 
                    "view" => "admin/settings/manage_payment_gateways/list.html", 
                    "fields" => array ("manage_gateways_action" => array ("id_field_type" => "string", 
                                                                           "form_field_type" => "hidden", 
                                                                           "default" => $this->manage_gateways_action ), 
                                       "manage_gateways_status_filter" => array ("id_field_type" => "string", 
                                                                                 "form_field_type" => "select", 
                                                                                 "options" => array ("all" => __("all"), 
                                                                                                     "enabled" => __("pl_enabled"), 
                                                                                                     "disabled" => __("pl_disabled")),  
                                                                                 "default" => $status_filter ) ) );
      
     $this->load->library ( "form" );
     $content = $this->form->get_form_content ( "filter", $form, $this->input, $this );
     $this->_set_content($content);
     
     //сохранение параметров фильтрации

     $this->temporary ['manage_gateways_status_filter'] = $status_filter;
      
     $this->_display();
  } //end show_list

   /**
   * подстраница запроса на получение нового платежного шлюза
   *
   * @return ничего не возвращает
   */   
   public function request() {
      $this->load->library ( "form" );
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Admin'),__('Manage Payment Gateways'),__('Request Payment Gateway Installation'))));
      $this->_set_help_index("request_payment_gateway");
      $form = array(
         'name' => 'requestgateway_form',
         'view' => 'admin/settings/manage_payment_gateways/request_gateway.html',
         'redirect' => 'admin/manage_payment_gateways/success_request',
         'fields' => array(
            'gateway_name' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'select',
               'options' => 'payment_gateways',
               'params' => array(
                  'type' => 'request'
               )
            ),
            'comment' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'textarea'
            ),
            'phone' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'text',
               'max' => 16
            )              
         )         
      );
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
      $data = array(
         'MESSAGE' => __('Payment gateway request was successfully sent!'),
         'REDIRECT' => $this->site_url.$this->index_page.'admin/manage_payment_gateways'
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success_request

   /**
   * кодирование заголовков письма в MIME формате
   *
   * @param string $str текст
   * @return string закодированная строка
   */
   public function utf8($str) {
      return '=?UTF-8?B?'.base64_encode($str).'?=';
   } //end utf8   
   
   /**
   * отсылает запрос на добавление нового платежного шлюза в систему
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
      $this->email->from(
         $system_email, 
         $this->utf8($site_name.' робот'));
      $this->email->to($this->global_variables->get("OrbitscriptsEMail"));
      $this->email->subject($this->utf8("$site_name, New payment gateway integration request"));
      $params = array(
         'PAYMENTGATEWAY' => $fields['gateway_name'],      
         'PHONE' => $fields['phone'],
         'COMMENT' => $fields['comment'],
         'SYSTEM' => $site_name,
         'EMAIL' => $system_email,
         'SITEURL' => $this->site_url         
      );      
      $mail = $this->parser->parse("mails/gateway_request.html", $params, TRUE);
      $this->email->message($mail);         
      $send_status = $this->email->send();         
      if ($send_status) {        
         return '';
      } else {
         return __('An error occurred while sending E-Mail.');
      }         
   } //end send_request   
   
   /**
   * вызывает функцию для отправки запроса на инсталляцию нового платежного шлюза
   * callback-функция для библиотеки form
   *
   * @param array $fields список полей формы и их сначений
   * @return string сообщение об ошибке или '' в случае успеха
   */   
   public function _create($fields) {
      return $this->send_request($fields);
   } //end _create   
   
}

?>