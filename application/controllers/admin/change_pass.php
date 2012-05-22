<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_change_pass.php';

/**
* Контроллер смены пароля
* 
* @author Gennadiy Kozlenko
* @project SmartPPC6
* @version 1.0.0
*/
class Change_pass extends Parent_change_pass  {

   protected $role = "admin";
   
   public function Change_pass() {
      parent::Parent_change_pass();
   }

   public function index($code) {
   	if ($this->user_id != type_cast($code,'textcode')) { //Если админ меняет пароль другого юзера, то старый пароль не спрашиваем
	   	unset($this->form_data['fields']['old']);
	   	$this->form_data['kill'] = array('old_password');
	   	$this->form_data['redirect'] = $this->role . '/change_pass/admin_success';
   	}
   	
   	parent::index($code);
   }
} //end Change_password
