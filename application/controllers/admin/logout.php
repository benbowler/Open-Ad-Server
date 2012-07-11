<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для выхода из системы админа
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Logout extends Parent_controller {

   protected $role = "admin";

   public function Logout() {
      parent::Parent_controller();
      $this->_logout("admin/login");
   }
         
}

?>