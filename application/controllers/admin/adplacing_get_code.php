<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/adplacing_get_code.php';

/**
* контроллер для получения кода канала
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Adplacing_Get_Code extends Common_Adplacing_Get_Code {
     
   protected $role = "admin";
   
   protected $menu_item = "Manage Sites/Channels";
   
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      $this->_set_title(implode(self::TITLE_SEP, array(__('Administrator'),__('Ad Placing'),__('Manage Sites/Channels'))));
   } //end __construct

}