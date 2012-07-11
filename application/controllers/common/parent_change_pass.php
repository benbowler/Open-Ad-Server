<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* базовый контроллер для смены пароля пользователем
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Parent_change_pass extends Parent_controller {

   protected $role;
   
   protected $template = "common/parent/thickbox.html";
   
   public function Parent_change_pass() {
   	parent::Parent_controller();
   	
      
      $password_min = $this->global_variables->get("MinPasswordLen");
      $old = $this->entity->password($this->user_id);
   	$this->form_data = array(
         "name"        => "change_password",                   
         "view"        => "common/change_password/form.html",
         //"success_view" => "common/change_password/success.html",
         'redirect' => $this->role . '/change_pass/success',   
         "fields"      => array(                     
            "old" => array(
               "display_name"     => "Old Password",                         
               "id_field_type"    => "string",        
               "form_field_type"  => "password",          
               "validation_rules" => "required|md5[$old]"
            ),
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
   }
   
   public function index($code) {
      $this->load->model("entity");
      
      $this->load->library("form");
      
      $this->form_data['id'] = type_cast($code,'textcode');
      
      $this->_set_content($this->form->get_form_content("modify", $this->form_data, $this->input, $this));
      $this->_set_help_index("change_password");
      $this->_display();      
   }
   
   public function _load($id) {
   	return array();
   }
   
   public function _save($id, $fields) {
   	if ('admin' != $this->role) { //Если пароль меняет не админ, то старый пароль проверяется
	   	$old = $this->entity->password($this->user_id);
	      if ($old != md5($fields["old"])) {
	         return "You must input current password!";
	      }
   	}
      $this->entity->set_password($id, md5($fields["password"]));
   }
      
   
   private function success_message($message) {
      $data = array(
         'MESSAGE' => 
            __($message),
         'JSCODE' => "self.parent.tb_remove();"
      );
      $content = $this->parser->parse('common/js_infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success 
   
   public function success() {
   	 $this->success_message('Your password successfully updated!');
   } //end success      
   
   public function admin_success() {
   	 $this->success_message('User password successfully updated!');
   } //end success 
   
}

?>