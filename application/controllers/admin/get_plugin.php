<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_get_plugin.php';

/**
 * Контроллер для получения CMS плагина
 *
 * @author Владимир Янц
 * @project SmartPPC6
 * @version 1.0.0
 */
class Get_Plugin extends Parent_get_plugin {

   protected $role = "admin";
	
   public function Get_Plugin() {
      parent::Parent_get_plugin();
   }
}

?>