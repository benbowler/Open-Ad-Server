<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для логина гостя в систему,
* перенаправляет пользователя на логин адвертайзера
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Contact_us extends Parent_controller  {
   
   protected $role = "guest";
   
   protected $menu_item = "Send Message";
   
   protected $messageid;
   
   public function Contact_us() {
      parent::Parent_controller();
      $this->messageid = uniqid();
   }
   
   public function index() {
      $form = array(
         "name"        => "contact_us",                   
         "view"        => "guest/contact_us/form.html",
	 "template"    => "contact",
         "redirect"    => "guest/contact_us/success/".$this->messageid,
         "fields"      => array(                     
            "name" => array(  
               "display_name"     => "Name",                               
               "id_field_type"    => "string",        
               "form_field_type"  => "text",          
               "validation_rules" => "required",
               "max"              => 50
            ),
            "e_mail" => array(  
               "display_name"     => "E-Mail",                               
               "id_field_type"    => "string",        
               "form_field_type"  => "text",          
               "validation_rules" => "required|valid_email",
               "max"              => 50
            ),
            "message" => array(
               "display_name"     => "Message",                                     
               "id_field_type"    => "string",
               "form_field_type"  => "textarea",
               "validation_rules" => "required",
               "max"              => 500
            )
         ) 
      );
      $this->load->library("form");
      $this->_set_content($this->form->get_form_content("create", $form, $this->input, $this));
      $this->_set_title("Contact Us");
      $this->_set_help_index("contact_us");
      $this->_display();    	
   }

   /**
   * отправляет письмо
   *
   * @param array $fields массив со значениями полей формы
   * @return string при успехе - пустая строка, иначе текст ошибки
   */   
   public function _create($fields) {
      $title = $this->global_variables->get('SiteName');
      $CI =& get_instance();
      $CI->load->library('email');         
      $config['charset'] = 'utf-8';
      $config['wordwrap'] = FALSE;
      $CI->email->initialize($config);      
      $CI->email->from($fields['e_mail'],type_to_str($fields['name'], 'mime'));
      $CI->email->to($this->global_variables->get("SystemEMail"));      
      $subject = $title.' '.__('Contact Us Message');
      $CI->email->subject(type_to_str($subject, 'mime'));
      $CI->email->message($fields['message']);         
      $CI->email->send();
      $this->global_variables->set('MsgID_'.$this->messageid, 'true', 0);      
      return "";
   } //end _create   

   public function success($msg_id){
      if (!is_null($this->global_variables->get('MsgID_'.$msg_id, 0))) {
         $this->_set_notification('Message was successfully sent!');
         $this->global_variables->kill(0, 'MsgID_'.$msg_id);
      }           
      $this->index();   
   }
   
}

?>