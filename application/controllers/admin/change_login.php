<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_change_login.php';

/**
* Контроллер смены пароля
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Change_login extends Parent_change_login  {

   protected $role = "admin";
   
   public function Change_login() {
      parent::Parent_change_login();
   }

   public function index($code) {
   	parent::index($code);
   }
} //end Change_password
