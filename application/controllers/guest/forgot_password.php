<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_forgot_password.php';

/**
* контроллер для восстановления пароля адвертайзера
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Forgot_password extends Parent_forgot_password {

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */   
   public function Forgot_password() {
      parent::Parent_forgot_password();
   } //end Forgot_password 
         
}
  
?>