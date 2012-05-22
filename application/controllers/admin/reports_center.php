<?php
if (!defined('BASEPATH') || !defined('APPPATH')) exit ('No direct script access allowed');

require_once APPPATH.'controllers/common/parent_reports_center.php';

/**
 * контроллер отчетов по паблишерам админа
 *
 * @author Владимир Юдин, Немцев Андрей
 * @project SmartPPC 6
 * @version 1.0.0
 */
class Reports_Center extends Parent_Reports_Center {
	
	protected $report_group = 0;
	
	protected $role = "admin";

	protected $menu_item = "Reports Center";
		
	public $form_prefix = "admin_rep_center";
	
	/**
	 * конструктор класса
	 *
	 * @return ничего не возвращает
	 */
	public function __construct() {
		parent::__construct();
		
		$this->search_entities_controller = "";
		$this->clone_report_controller = $this->role."/reports_center/clone_report";
		$this->view_report_controller = $this->role."/view_report";
		$this->after_create_controller = $this->role."/reports_center";
		$this->save_defaults_controller = $this->role."/reports_center/save_defaults";
		$this->view_report_from_controller = $this->role."/reports_center";
		
		$this->create_report_template = $this->role."/reports_center/create_report.html";	  
		
     	$this->display_actions_in_table = true;
      	
		$this->_set_title ( implode(self::TITLE_SEP, array( __( 'Administrator' ) , __( $this->menu_item ))));
	} //end __construct
   
} //end ad_serving_publishers_reports class

?>