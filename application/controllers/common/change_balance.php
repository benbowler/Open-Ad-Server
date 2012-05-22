<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Контроллер изменения баланса сущности
 *
 */
class Common_Change_Balance extends Parent_controller {	
	
	public $cancel_url;
	
	public $prev_title;
	
	public function __construct() {
	   parent::__construct();	  
	   $this->load->library('form');
      $this->load->model('entity', '', TRUE);
	} //end __construct()
	
	/**
	 * Отображение формы изменения баланса сущности
	 *
	 */
	public function index() { 
       $error_message = '';
       
	   $code = $this->input->post('balance_code');

       if (!$code) {
          $error_message = 'Advertiser is not specified!';
	      $data = array(
	         'MESSAGE' => __($error_message),
	         'REDIRECT' => $this->site_url . $this->index_page. $this->role . '/manage_advertisers'
	      );
	      $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
	      $this->_set_content($content);
	      $this->_display();
	      return NULL;
	   }
			   
	   $id_entity = type_cast($code, 'textcode');
      $entity_info = $this->entity->get_name_and_mail($id_entity);
      $entity_info = $entity_info->name.' ('.$entity_info->e_mail.')';
      $balance = $this->entity->ballance($id_entity);
      $balance = type_to_str($balance, 'money'); 
	   $this->load->model('campaigns', '', TRUE);
		$form = array(
		   'id'          => $code,
         'name'        => 'balance_form',
		   'redirect'    => "admin/change_".$this->subject_role."_balance/success",
         'view'        => "admin/change_balance/form.html",
		   'vars'        => array(
		      'ENTITY' => $entity_info,
		      'CUR' => $balance,
		      'CANCEL_URL' => $this->cancel_url,
		      'PREV_TITLE' => $this->prev_title
		   ),
		   'fields'      => array(                     
            'amount' => array(
               'display_name'     => __('Amount'),
               'id_field_type'    => 'money',
               'form_field_type'  => 'text',
               'validation_rules' => 'required|callback_valuetrim|float|positive'              
            ),
            'balance_code' => array (
               'id_field_type'    => 'string',
               'form_field_type'  => 'hidden',            
               'default' => $code                    
            ),
            'balance_mode' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'select',
               'options' => array(
                  'add' => __('Add'),
                  'sub' => __('Subtract')
               ),
               'default' => 'add'
            )
         ) 
      );	
		$this->_set_content($this->form->get_form_content('modify', $form, $this->input, $this));
		$this->_display();
	}
	
	public function valuetrim(&$val){
	   $val = trim($val);
	   return TRUE;
	}
	
	public function _load($id) {
	   //return array('amount' => 10.0);
      return array();
	} //end _load
	
   public function _save($id, $fields) {
      $id_entity = type_cast($id, 'textcode');
      $balance = $this->entity->ballance($id_entity);
      if ($fields['balance_mode'] == 'add') {
         $balance += $fields['amount'];
         $value = $fields['amount'];
      } else {
         if ($balance < $fields['amount']) {
            return __("You can't set user balance negative!"); 
         }
         $balance -= $fields['amount'];
         $value = -$fields['amount'];
      }
      $this->entity->set_ballance($id_entity, $balance);
      $this->load->model('payment_gateways', '', TRUE);
      $this->payment_gateways->money_flow(
         $this->user_id, $id_entity, $value, $balance, 0, ($value>0)?'deposit':'deduction',  0, TRUE,  0);
      $this->load->model('sites_channels', '', TRUE);
      $this->sites_channels->autopay($id_entity);      
      return '';            
   } //end _save    
   
} //end class Common_Change_Balance