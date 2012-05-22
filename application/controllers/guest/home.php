<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Home extends Parent_controller {

   protected $role = "guest";
   
   protected $menu_item = "Welcome";

   public function Home() {
      parent::Parent_controller();
      $this->_add_java_script('j');
      $this->_add_java_script('stuff');
   }
   
   public function index() {
      $this->_set_title("Welcome");
      $this->_set_help_index("smart_ppc_6");
      $vars = array(
       'ADVERTISERINFO' => $this->load->view("guest/home/{$this->locale}/advertiser_info.html", "", TRUE)     
      );
      $this->_set_content($this->parser->parse("guest/home/template.html", $vars, TRUE)); 
      $this->_display();      
   }
   
}

?>