<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_view_report.php';

/**
* контроллер для просмотра отчетов адвертайзером
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class View_Report extends Parent_View_Report  {

   protected $role = "admin";
   
   //protected $menu_item = "Reports Center";
   
   /**
   * конструктор класса,
   * вносит изменения в структуру базового класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {    
      parent::__construct();
      if ($this->input->post('from_page')) {
         $this->menu_item = $this->input->post('from_page');
      }
      if ($this->input->post('from_controller')) {
         $this->from_controller = $this->input->post('from_controller');
      }  
      
      //echo "menu item:".$this->menu_item;
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Admin'),__($this->menu_item),__('View Report'))));
   } //end View_report         
   
} //end Sign_up

?>