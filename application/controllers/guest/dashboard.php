<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Dashboard extends Parent_controller {

   protected $role = "guest";
   
   protected $menu_item = "";

   public function Dashboard() {
      parent::Parent_controller();
   }
   
   public function index() {
      $this->_set_title("Welcome");
      $this->_set_help_index("smart_ppc_6");
      $this->_set_content($this->load->view("guest/welcome.html", "", TRUE)); 
      $this->_display();      
   }
   
}

?>