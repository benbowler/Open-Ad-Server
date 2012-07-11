<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для изменения настроек платежных шлюзов
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Edit_Payment_Gateways extends Parent_controller {
     
   protected $role = "admin";
   
   protected $menu_item = "Manage Payment Gateways";

   protected $payment_gateway_id = '';
   
   protected $info = array();
   
   private $publishers_is_enabled = false;
   
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      
      $this->load->model('payment_gateways');
      
      $this->_add_java_script('j');
      $this->_add_java_script('stuff');
	  
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Administrator'),__('Settings'),__('Manage Payment Gateways'))));
      $this->_set_help_index("admin_settings_manage_payment_gateways");
      $this->load->library("form");
      
      $this->load->model('roles');
      
      $enabled_roles = $this->roles->get_list(array('is_used' => 'true'));
      
      $this->publishers_is_enabled = in_array('publisher',$enabled_roles); 
   } //end __construct

   /**
   * показывает форму для изменения платежного шлюза
   *
   * @param $id - идентификатор редактируемой новости, если null - создание новости
   * @return ничего не возвращает
   */   
   public function index($id = null) {
      
      if (!is_null($id)) { 
        $this->payment_gateway_id = $id;
        $this->info = $this->payment_gateways->get_edit_info($id);
      } else {
        redirect('admin/manage_payment_gateways'); 
      }
      
      $form = array(
         "name"        => 'edit_payment_gateway_form', 
         "id"          => $this->payment_gateway_id,
         "vars"         => array(
               'ACCOUNT_ID_1_TITLE' => $this->info->account_id_1_title,
               'ACCOUNT_ID_2_TITLE' => $this->info->account_id_2_title,
               'ACCOUNT_ID_3_TITLE' => $this->info->account_id_3_title,
               'GATEWAY_NAME' => $this->payment_gateways->get_name($this->payment_gateway_id),
               'MONEYFORMAT' => get_money_format(),
               'NUMBERFORMAT' => get_number_format()      
            ),
         "view"         => "admin/settings/manage_payment_gateways/edit.html",
         "redirect"     => 'admin/edit_payment_gateways/edit_complete',                
         'kill' => array(
               $this->info->use_textarea?'useinput':'usetextarea'
            ),                
         "fields"      => array(                     
            "account_id1" => array(
               "display_name"     => __($this->info->account_id_1_title),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => $this->info->validation_rules_1               
            ),
            "used_for" => array(
               "display_name"     => __("Used For"),
               "id_field_type"    => "string",
               "form_field_type"  => "select",
               "validation_rules" => "required"                                              
            ),
            "fund_comm" => array(
               "display_name"     => __("Fund Commission"),
               "id_field_type"    => "integer",
               "form_field_type"  => "text",
               "validation_rules" => "integer|non_negative"               
            ),
            "withdraw_comm" => array(
               "display_name"     => __("Withdraw Commission"),
               "id_field_type"    => "integer",
               "form_field_type"  => "text",
               "validation_rules" => "float|non_negative"               
            ),
            "minimal_payment" => array(
               "display_name"     => __("Minimal payment"),
               "id_field_type"    => "float",
               "form_field_type"  => "text",
               "validation_rules" => "float[2]|non_negative"
            	               
            ),

            
         ) 
      );
      
      if ($this->publishers_is_enabled) {
         switch ($this->info->possibility_mode){
            case 'deposit':
               $form['fields']['used_for']['options'] = array(
                  'deposit' => __('Deposit') 
               );
               $form['fields']['used_for']['default'] = 'deposit';
               break;
            case 'withdraw':
               $form['fields']['used_for']['options'] = array(
                  'withdraw' => __('Withdraw')
               );
               $form['fields']['used_for']['default'] = 'withdraw';
               break;
            case 'all':
               $form['fields']['used_for']['options'] = array(
                  'deposit' => __('Deposit'), 
                  'withdraw' => __('Withdraw'),
                  'both' => __('Deposit & Withdraw')
               );
               $form['fields']['used_for']['default'] = 'both';
         }
      } else {
      	$form['fields']['used_for']['options'] = array('deposit' => __('Deposit'));
      	$form['fields']['used_for']['default'] = 'deposit';
      }
      
      if ($this->info->use_account_id_2) {
         $form['fields']['account_id2'] = array(
               "display_name"     => __($this->info->account_id_2_title),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => $this->info->validation_rules_2              
            );
         $form['vars']['ACCOUNT_ID_2_VISIBLE'] = '';
         if ($this->info->max_2) {
            $form['fields']['account_id2']['max'] = $this->info->max_2; 
         }
      } else {
         $form['vars']['ACCOUNT_ID_2_VISIBLE'] = 'style="display:none"';
      }
      
      if ($this->info->use_account_id_3) {
         $form['fields']['account_id3'] = array(
               "display_name"     => __($this->info->account_id_3_title),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => $this->info->validation_rules_3              
            );
         $form['vars']['ACCOUNT_ID_3_VISIBLE'] = '';
         if ($this->info->max_3) {
            $form['fields']['account_id3']['max'] = $this->info->max_3; 
         }
      } else {
         $form['vars']['ACCOUNT_ID_3_VISIBLE'] = 'style="display:none"';
      }
      
      if ($this->info->max_1) {
         $form['fields']['account_id1']['max'] = $this->info->max_1; 
      }
      
      $this->_set_content($this->form->get_form_content("modify", $form, $this->input, $this));
      $this->_display();
   } //end index
   
   /**
    * Отображение сообщения об успешном изменении платежного шлюза
    *
    */  
   public function edit_complete() {
      $data = array(
         'MESSAGE' => __('Payment Gateway was modified successfully'),
         'REDIRECT' => $this->site_url.$this->index_page.'admin/manage_payment_gateways'
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }
   
   public function _load($id) {
      $gateway_data = array('account_id1' => $this->info->account_id_1,
                   'account_id2' => $this->info->account_id_2,
                   'account_id3' => $this->info->account_id_3,
                   'fund_comm'   => $this->info->fund_comm,
                   'withdraw_comm'   => $this->info->withdraw_comm);
      switch ($this->info->mode) {
      	case 'deposit':
      	  $gateway_data['used_for'] = 'deposit';
      	break;
      	case 'withdraw':
           $gateway_data['used_for'] = 'withdraw';
         break;
      	default:
      		$gateway_data['used_for'] = 'both';
      	break;
      }
      $gateway_data['minimal_payment']=$this->info->minimal_payment;
      return $gateway_data;
   }
   
   public function _save($id, $fields) {
      if (isset($fields['withdraw_comm'])) {
         if (!is_int($fields['withdraw_comm']))
	        return __('The \'Withdraw Commission\' field must contain a positive integer number.');
      }
      if (isset($fields['fund_comm'])) {
         if (!is_int($fields['fund_comm']))
	        return __('The \'Deposit Commission\' field must contain a positive integer number.');
      }
      
      if (isset($fields['withdraw_comm'])) {
         if ($fields['withdraw_comm'] > 100)
	        return __('Withdraw Commission can not be more than 100%');
      }
      if (isset($fields['fund_comm'])) {
         if ($fields['fund_comm'] > 100)
	        return __('Deposit Commission can not be more than 100%');
      }
      
      $edit_data = array('account_id_1' => $fields['account_id1']);
      if (array_key_exists('account_id2',$fields)) {
         $edit_data['account_id_2'] = $fields['account_id2'];
      }
      if (array_key_exists('account_id3',$fields)) {
         $edit_data['account_id_3'] = $fields['account_id3'];
      }
      
      switch ($fields['used_for']) {
            case 'deposit':
              $edit_data['mode'] = 'deposit';
              $edit_data['withdraw_comm'] = 0;
              $edit_data['fund_comm'] = $fields['fund_comm'];
            break;
            case 'withdraw':
              $edit_data['mode'] = 'withdraw';
              $edit_data['withdraw_comm'] = $fields['withdraw_comm'];
              $edit_data['fund_comm'] = 0;
            break;
            default:
               $edit_data['mode'] = 'deposit,withdraw';
               $edit_data['withdraw_comm'] = $fields['withdraw_comm'];
               $edit_data['fund_comm'] = $fields['fund_comm'];
            break;
         }
      
         //var_dump($fields);
      $edit_data['minimal_payment'] = $fields['minimal_payment'];
      $this->payment_gateways->set_edit_info($id, $edit_data);
   }
}

?>