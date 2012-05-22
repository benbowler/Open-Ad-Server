<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для восстановления потерянного пароля адвертайзера
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Parent_forgot_password extends Parent_controller {

   protected $role = "guest";
   
   protected $menu_item = "Forgot Password";
   
   protected $controller = "common/parent_forgot_password";

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */   
   public function Parent_forgot_password() {
      parent::Parent_controller();
      $this->_set_title("Forgot Password");
      $this->_set_help_index("forgot_password");
      
   } //end Parent_forgot_password

   /**
   * выводит форму с запросом электронного адреса
   *
   * @return ничего не возвращает
   */
   public function index() {
      $form = array(
         "name"        => "forgot_password",                   
         "view"        => "common/forgot_password/form.html",
         'redirect'    => $this->controller.'/send',
         //"success_view" => "common/forgot_password/mail_send.html",   
         "fields"      => array(                     
            "mail" => array(
               "display_name"     => "E-mail",                         
               "id_field_type"    => "string",        
               "form_field_type"  => "text",          
               "validation_rules" => "required|valid_email"
            )
         ) 
      );
      $this->load->library("form");
      $this->_set_content($this->form->get_form_content("", $form, $this->input, $this));      
      $this->_display();      
   } //end index
   
   /**
   * обновляет пароль пользователя
   *
   * @param int $id код пользователя
   * @param array $fields массив, содержащий новый пароль пользователя
   * @return string при удачном обновлении - "", иначе сообщение об ошибке
   */
   public function _save($id, $fields) {
      $this->load->model("entity");
      if ($id != "") {
         $password = md5($fields["password"]); 
         $res = $this->entity->set_password($id, $password);
         if ($res > 0) {
            return "";   
         } else {
            return "Can't change password!";
         }
      }
   }   

   /**
   * кодирование заголовков письма в MIME формате
   *
   * @param string $str текст
   * @return string закодированная строка
   */
   public function utf8($str) {
      return '=?UTF-8?B?'.base64_encode($str).'?=';
   } //end utf8
     
   /**
   * отправляет письмо пользователю на заданный адрес с кодом для восстановления пароля
   *
   * @param array $fields массив содержащий электронный адрес пользователя
   * @return string при удачной отправке - "", иначе сообщение об ошибке
   */   
   public function _create($fields) {
      $this->load->model("entity");
   	$code = $this->entity->password_recovery($fields["mail"]);
   	if (is_null($code)) {
   	   return "User with such E-Mail not found in the system!";
   	} else {
   	   $this->load->library('email');
         $config['charset'] = 'utf-8';
         $config['wordwrap'] = FALSE;
         $this->email->initialize($config);
         $site_name = $this->global_variables->get("SiteName");               	            
         $this->email->from(
            $this->global_variables->get("SystemEMail"), 
            $this->utf8($site_name.' '.__('Robot')));
         $this->email->to($fields["mail"]);
         $this->email->subject($this->utf8($site_name." ".__("Password Recovery Link")));
         $params = array(
            'LINK' => $this->site_url.$this->index_page.$this->role."/forgot_password/new_password/".$code,
            'EMAIL' => $this->user_name,
            'SYSTEM' => $site_name 
         );      
         $mail = $this->parser->parse("mails/$this->locale/password_recovery.html", $params, TRUE);
         $this->email->message($mail);                  
         $send_status = $this->email->send();   	   
         if ($send_status) {   	   
   	      return "";
         } else {
         	return __("An error occurred while sending E-Mail");
         }         
   	}
   }
   
   /**
   * выводит форму с запросом нового пароля
   *
   * @param string $code код восстановления пароля
   * @return ничего не возвращает
   */   
   public function new_password($code) {
      $this->load->model("entity");
      $this->user_id = $this->entity->password_code($code);
      if (is_null($this->user_id)) {
         $this->_set_content($this->load->view("common/forgot_password/invalid.html", "", TRUE));
         $this->_display();      
      } else {
         $password_min = $this->global_variables->get("MinPasswordLen");
         $form = array(
            "name"        => "forgot_password",
            "id"          => $this->user_id,                    
            "view"        => "common/forgot_password/new.html",
            //"success_view" => "common/forgot_password/success.html",
            'redirect'    => $this->controller.'/success',   
            "fields"      => array(                     
               "password" => array(     
                  "display_name"     => "New Password",   
                  "id_field_type"    => "string",        
                  "form_field_type"  => "password",          
                  "validation_rules" => "required|min_length[$password_min]"
               ),
               "confirm" => array(     
                  "display_name"     => "Confirm New Password",   
                  "id_field_type"    => "string",        
                  "form_field_type"  => "password",          
                  "validation_rules" => "required|matches[password]"
               )
            ) 
         );
         $this->load->library("form");
         $this->_set_content($this->form->get_form_content("", $form, $this->input, $this));
         $this->_display();      
      }
   } //end new_password

   public function send() {
      $data = array(
         'MESSAGE' => 
            __('Mail with password recovery link send successfully!'),
         'REDIRECT' => $this->site_url.$this->index_page.'guest'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end send     
   
   public function success() {
      $data = array(
         'MESSAGE' => 
            __('Your password successfully updated!'),
         'REDIRECT' => $this->site_url.$this->index_page.'guest'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success      
   
}

?>