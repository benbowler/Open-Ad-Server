<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Terms extends Parent_controller {

   protected $role = "guest";
   
   protected $menu_item = "Terms & Services";

   public function Terms() {
      parent::Parent_controller();
   }
   
   public function index() {
      $this->_set_title("Terms & Services");
      $this->_set_help_index("smart_ppc_6");
      $vars = array(
       'SITENAME' => $this->global_variables->get('SiteName')              
      );      
      $this->_set_content($this->load->view("guest/home/terms.html", '', TRUE)); 
      $this->_display();      
   }
   
}

?>