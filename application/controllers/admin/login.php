<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_login.php';

/**
* контроллер для логина админа в систему
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Login extends Parent_login {

   protected $role = "guest_admin";
   
   protected $menu_item = "Login";
   
   protected $target_role = "admin";
   
   /**
   * конструктор класса,
   * инициализирует базовый класс
   *
   * @return ничего не возвращает
   */   
   public function Login() {
      parent::Parent_login();
   }
         
}

?>