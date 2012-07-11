<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MY_Controller.php';

/**
* контроллер для логина гостя в систему,
* перенаправляет пользователя на логин адвертайзера
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Login extends MY_Controller  {

   public function __construct() {
      parent::__construct();
      
      $defaultLoginRole = $this->config->item('default_login_role');
      if (empty($defaultLoginRole)) $defaultLoginRole = 'advertiser';
      
      redirect($defaultLoginRole."/login");
   }
      
}

?>