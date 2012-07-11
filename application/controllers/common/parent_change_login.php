<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* базовый контроллер для смены пароля пользователем
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Parent_change_login extends Parent_controller {

   protected $role;
   
   protected $template = "common/parent/thickbox.html";
   
   public function Parent_change_login() {
   	parent::Parent_controller();
   	
      
      $password_min = $this->global_variables->get("MinPasswordLen");
      $old = $this->entity->password($this->user_id);
   	$this->form_data = array(
         "name"        => "change_login",                   
         "view"        => "common/change_login/form.html",
         //"success_view" => "common/change_password/success.html",
         'redirect' => $this->role . '/change_login/success',   
         "fields"      => array(                     
            "password" => array(
               "display_name"     => "Current Password",                         
               "id_field_type"    => "string",        
               "form_field_type"  => "password",          
               "validation_rules" => "required|md5[$old]"
            ),
            "login" => array(
               "display_name"     => "New Login",                         
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required|valid_email"
            )
         ) 
      );
   }
   
   public function index($code) {
      $this->load->model("entity");
      
      $this->load->library("form");
      
      $this->form_data['id'] = type_cast($code,'textcode');
      
      $this->_set_content($this->form->get_form_content("modify", $this->form_data, $this->input, $this));
      $this->_set_help_index("change_password");
      $this->_display();      
   }

   public function _validator($fields, $int_validator) {
      $this->load->model("entity");
      $res = TRUE;
      if ($fields['login'] != $this->user_name) {
      	$id_by_email = $this->entity->get_id_by_email($fields['login']);
         if (!is_null($id_by_email)) {
            //return __("Account with such e-mail already exist in the system.");
            $this->validation->login_error = "<p class='errorP'>".__("Account with such e-mail already exist in the system.")."</p>";
            $res = FALSE;
         }
   	}
      return $res;
   }   
   
   public function _load($id) {
   	return array("login" => $this->user_name);
   }
   
   public function _save($id, $fields) {
      if($fields['login'] == $this->user_name) {
         return '';
      }
      $this->load->model("entity");
      $this->user_name = strtolower($fields['login']);
      $this->entity->set_mail($id, $this->user_name);
      $this->_save_session();      
      return '';
   } //end _save
         
   private function success_message($message) {
      $data = array(
         'MESSAGE' => 
            __($message),
         'JSCODE' => "self.parent.tb_remove(); self.parent.$('.userName>b').html('$this->user_name');"
      );
      $content = $this->parser->parse('common/js_infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success 
   
   public function success() {
   	 $this->success_message('Your login successfully updated!');
   } //end success       
   
} //end Controller Parent_change_login

?>