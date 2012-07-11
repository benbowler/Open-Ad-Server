<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * базовый контроллер для логина пользователей в систему
 *
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Parent_login extends Parent_controller {
   
   protected $role = "guest";
   
   protected $menu_item = "Login";
   
   protected $target_role;
   
   protected $kills = array(
         'message');
   
   protected $login_role;
   
   protected $redirect;
   
   public function Parent_login() {
      parent::Parent_controller();
   //      header("Expires: " . gmdate("D, d M Y H:i:s" . " GMT"));    // дата в прошлом
   //      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");                                                            // всегда модифицируется
   //      header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");  // HTTP/1.1
   //      header("Cache-Control: post-check=0, pre-check=0", false);
   //      header("Pragma: no-cache");                          // HTTP/1.0
   

   //      header('Cache-Control: ');
   //      header('Pragma: ');
   
      $this->load->library('Plugins', array(
         'path' => array('review_controller'),
         'interface' => 'Sppc_Search_Controller_SiteReviewInterface'
      ));

   }
   
   /**
    * проверяет не залогинены ли мы в систему, и если да - переходим на dashboard
    *
    * @return ничего не возвращает
    */
   protected function check_login() {
      $session_data = $this->session->userdata('auth_' . $this->target_role);
      if (is_array($session_data) && isset($session_data['userid']) && $session_data['userid']) {
         $this->plugins->run('checkLogin', array ($this->target_role));
         redirect($this->target_role . "/dashboard");
      }
   } //end check_login
   

   public function index() {
      $this->check_login();
      $form = array(
            "name" => "login", 
            "view" => "common/login/form.html", 
            "kill" => $this->kills, 
            "fields" => array(
                  "mail" => array(
                        "display_name" => "E-mail", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        "validation_rules" => "required|valid_email"), 
                  "password" => array(
                        "display_name" => "Password", 
                        "id_field_type" => "string", 
                        "form_field_type" => "password", 
                        "validation_rules" => "required")));
      $this->load->library("form");
      $this->_set_content($this->form->get_form_content("create", $form, $this->input, $this));
      $this->_set_title("Login");
      $this->_set_help_index("login");
      $this->_display();
   }
   
   public function _create($fields, $redirect = null) {
      
   	$this->redirect = $redirect;
      $this->load->model("entity");
      $user = $this->entity->login($fields["mail"], md5($fields["password"]), $this->target_role);
      if (is_null($user)) {
         return __("Such user not found in the system!");
      } elseif (!in_array($this->target_role, $user["roles"])) {
         return __("This user is not have ") . __($this->target_role) . __(" account!");
      }
      switch ($user['status']) {
         case 'activation':
            return __('Account of this user not approved yet!');
         case 'blocked':
            return __('Account of this user was blocked by advertiser!');
         case 'deleted':
            return __('Account of this user was deleted!');
      }
      $this->user_id = $user["id"];
      $this->user_name = strtolower($fields["mail"]);
      
      // Получаем menuitem
      $session_data = $this->session->userdata('auth_' . $this->target_role);
      $menuitem = '';
      if (is_array($session_data) && isset($session_data['menuitem'])) {
         $menuitem = $session_data['menuitem'];
      }
      $controller = $this->menu->get_controller($this->target_role, $menuitem);
      
      $this->role = $this->target_role;
      $this->_save_session();
      $this->_on_login();
      
      if (!is_null($this->redirect) && !empty($this->redirect['url'])) {
         //Если пришел параметр $redirect то мы формируем пост форму и сабмитим ее по онлоаду 
         echo '<html><body onLoad="redirect();"><form id="redirectForm" action="' . $this->redirect['url'] . '"  method="Post">';
         echo '<script language="JavaScript">function redirect(){ document.getElementById("redirectForm").submit();}</script>';
         foreach ($this->redirect as $name => $value) {
            
            if ('url' == $name) {
               continue;
            }
            
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
         }
         
         echo '</form></body></html>';
         exit();
      } else {
         if (is_null($controller)) {
            if (in_array('admin', $user["roles"])) {
               // Логинем так же как админа, если это позволяют его роли
               $this->target_role = 'admin';
               /*
            $this->role = $this->target_role;
            $this->_save_session();
            $this->_on_login();
            */
            }
            redirect("$this->target_role/dashboard");
         } else {
            redirect($controller);
         }
      }
      exit();
   } //end _create
   

   public function _on_login() {
   }
   
   /**
    * автологин - вход в систему с переданными парметрами логин/пароль
    *
    * @param string $login логин пользователя
    * @param string $password пароль пользователя
    * @return ничего не возвращает
    */
   public function auto($login, $password) {
      $login = str_replace('.at.', '@', $login);
      $redirect = array();
      foreach ($_POST as $key => $value) {
         $redirect[$key] = $value; 
      }
      $this->_create(array(
            'mail' => $login, 
            'password' => $password), $redirect);
   } //end auto
   

   /**
    * вызывается при повторном логине при истечении времени сессии
    *
    * @return ничего не возвращает
    */
   public function timeout() {
      $this->check_login();
      $this->kills = array();
      $this->index();
   } //end name


}

?>