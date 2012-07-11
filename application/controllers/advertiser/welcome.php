<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/advertiser/dashboard.php';

class Welcome extends Dashboard {

	protected  $role = "advertiser";                    // роль пользователя, открывающего контроллер
   
   protected $menu_item = "Dashboard";
   
   public function Dashboard() {
      parent::Dashboard();
   }

}

?>