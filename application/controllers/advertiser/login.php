<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_login.php';

/**
* контроллер для логина адвертайзера в систему
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Login extends Parent_login {

   protected $role = "guest";
   
   protected $menu_item = "Login";
   
   protected $target_role = "advertiser";
   
   /**
   * конструктор класса,
   * инициализирует базовый класс
   * 
   * @return ничего не возвращает
   */   
   public function Login() {
      parent::Parent_login();
   } //end Login
         
   /**
   * стандартный метод, вызывается при успешной авторизации в системе
   *
   * @return ничего не возвращает
   */
   public function _on_login() {
      $this->load->helper('cookie');
      
      $cookie = get_cookie('guest_buy_site_channel_' . $this->config->item('sess_cookie_name'));
       
      if (FALSE != $cookie) {
         delete_cookie('guest_buy_site_channel_' . $this->config->item('sess_cookie_name'));
         $buy_site_channel_info = json_decode($cookie);
         if (!is_null($buy_site_channel_info) && 
             isset($buy_site_channel_info->site_code) &&
             isset($buy_site_channel_info->program_code)) {
               $this->redirect = array(
                  'url' => $this->site_url.$this->index_page.'advertiser/buy_site_channel',
                  'site_code' => $buy_site_channel_info->site_code,
                  'program_code' => $buy_site_channel_info->program_code
               );    	             	
               /*redirect("advertiser/buy_site_channel/select_program_type/$buy_site_channel_info->site_code/$buy_site_channel_info->channel_code");
               exit();*/
         }
      } else {
         redirect("advertiser/dashboard");
      }
   } //end _on_login
   
} //end class Login

?>