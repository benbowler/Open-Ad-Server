<?php
if (!defined('BASEPATH'))
   exit('No direct script access allowed');

/**
 * модель для работы с группами отчетов
 * 
 * @author Anton Potekhin
 * @project SmartPPC 6
 * @version 1.0.0
 */
class Report_Groups extends CI_Model {
   
   protected $_tableName = 'report_groups';
   
   protected $_hooks = array();
   
   /**
    * Constructor
    */
   public function __construct() {
      parent::__construct();
      // Fill up hooks array
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->common->report_groups)) {
         foreach($pluginsConfig->common->report_groups as $hookClass) {
            $hookObj = new $hookClass();
            if ($hookObj instanceof Sppc_Common_ReportGroups_EventHandlerInterface) {
               $this->_hooks[] = $hookObj;
            }
         }
      }
   }
   
   /**
    * возвращает список групп отчетов, доступный для заданной роли
    *
    * @param int $idRole
    * @return array список доступных отчетов 
    */
   public function getList($idRole) {
      $this->db->select('report_groups.title, report_groups.controller')->join('report_types', 'report_types.report_group = report_groups.id_report_group')->where('report_groups.id_role', $idRole)->group_by('report_groups.id_report_group');
      $res = $this->db->order_by('id_report_group')->get($this->_tableName);
      $list = array();
      $cobj = get_instance();
      $cobj->load->model('roles');
      $roles = $cobj->roles->get_all_roles();
      foreach ($res->result() as $row) {
         // Modify plugin's report group 
         foreach($this->_hooks as $hookObj) {
            $row = $hookObj->modifyReportGroup(array($row, $roles[$idRole]));
         }
         $list[] = $row;
      }
      return $list;
   } //end get_list()
   /**
    * Find group id by controller name
    * @param string $controller
    * @return int
    */
   public function findGroupByController($controller) {
		$result = $this->db->select('report_groups.id_report_group')
				->where('report_groups.controller', $controller)
				->get($this->_tableName);
      if(($result===false)||($result->num_rows()<1)) {
      	return null;
      }
      return $result->row()->id_report_group;
   }   
} //end Report_groups class

