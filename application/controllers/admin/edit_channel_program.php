<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/edit_channel_program.php';

/**
* контроллер для создания/изменения платежной программы канала
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Edit_Channel_Program extends Common_Edit_Channel_Program {
     
   protected $role = "admin";
   
   protected $menu_item = "Manage Sites/Channels";

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Administrator'),__('Ad Placing'),__('Manage Sites/Channels'))));

   } //end __construct
}