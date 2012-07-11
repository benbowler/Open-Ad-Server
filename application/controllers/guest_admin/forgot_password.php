<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_forgot_password.php';

/**
* контроллер для выхода из системы админа
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Forgot_password extends Parent_forgot_password {

   protected $role = "guest_admin";
 
   public function Forgot_password() {
      parent::Parent_controller();
   }
         
}

?>