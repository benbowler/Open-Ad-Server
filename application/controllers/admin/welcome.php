<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/admin/dashboard.php';

class Welcome extends Dashboard {

   public function Dashboard() {
       parent::Dashboard();
   }

}

?>