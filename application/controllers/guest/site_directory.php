<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/site_directory.php';

class Site_Directory extends Common_Site_Directory {

   protected $role = "guest";
   
   protected $menu_item = "Site Directory";
   
   public function __construct() {
      parent::__construct();
   }
}

?>