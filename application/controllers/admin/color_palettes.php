<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/color_palettes.php';

/**
* контроллер для создания/изменения цветовой схемы
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Color_Palettes extends Common_Color_Palettes {
     
   protected $role = "admin";
   
   protected $menu_item = "Color palettes";

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
   	parent::__construct();
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Administrator'),__('Ad Placing'),__('Color Palettes'))));
   } //end __construct
}