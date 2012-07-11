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

   protected $role = "advertiser";
   
   protected $menu_item = "Account Settings";
   
   public function Change_pass() {
      parent::Parent_change_pass();
   }

} //end Change_password
