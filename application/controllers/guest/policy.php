<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Policy extends Parent_controller {

   protected $role = "guest";
   
   protected $menu_item = "Privacy Policy";

   public function Policy() {
      parent::Parent_controller();
   }
   
   public function index() {
      $this->_set_title("Privacy Policy");
      $this->_set_help_index("smart_ppc_6");
      $mail = $this->global_variables->get('SystemEMail');
      $vars = array(
       'SITEEMAIL' => "<a href='mailto:$mail'>$mail</a>",
       'SITENAME' => $this->global_variables->get('SiteName')              
      );
      $this->_set_content($this->load->view("guest/home/policy.html", '', TRUE));
      $this->content = array_merge($this->content, $vars);
      $this->_display();      
   }
   
}

?>