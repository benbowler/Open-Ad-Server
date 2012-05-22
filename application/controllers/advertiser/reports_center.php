<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_reports_center.php';

/**
* контроллер центра отчетов рекламодателя
* 
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Reports_Center extends Parent_Reports_Center {

   protected $role = "advertiser";
   
   protected $menu_item = "Reports Center";
   
   public $form_prefix = "adv_rep_center";
   
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
      
		$this->clone_report_controller = $this->role."/reports_center/clone_report";
		$this->view_report_controller = $this->role."/view_report";
		$this->after_create_controller = $this->role."/reports_center";
		$this->save_defaults_controller = $this->role."/reports_center/save_defaults";
		$this->check_report_status_controller = $this->role."/reports_center/check_report_status";
		$this->view_report_from_controller = $this->role."/reports_center";
		
		$this->_set_title ( implode(self::TITLE_SEP, array( __( 'Advertiser' ) , __( $this->menu_item ))));
   } //end Reports_center
      
} //end Reports_center class

?>