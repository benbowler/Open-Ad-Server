<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для выхода из системы адвертайзера
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Logout extends Parent_controller {

   protected $role = "advertiser";
   
   public function Logout() {
      parent::Parent_controller();
      $this->_logout("advertiser/login");
   } //end Logout
         
} //end class Logout

?>