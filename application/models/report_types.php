<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с типами отчетов
* 
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Report_types extends CI_Model {

	protected $_hooks = array();
	
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   function __construct() {
	   parent::__construct();
   	// Fill up hooks array
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->common->report_types)) {
         foreach($pluginsConfig->common->report_types as $hookClass) {
            $hookObj = new $hookClass();
            if ($hookObj instanceof Sppc_Common_ReportTypes_EventHandlerInterface) {
               $this->_hooks[] = $hookObj;
            }
         }
      }	
	} //end Report_types
	
   /**
   * возвращает список отчетов, доступный для заданной роли и группы
   *
   * @param array $params массив с параметрами 
   *    'role' - выбранная роль 
   *    'group' - выбранная группа отчетов
   *    'all'   - добавить в список значение All
   * @return array список доступных отчетов в формате (id => title)
   */	
	public function get_list($params) {
		$this->db->select('id_report_type, title')
				 ->join('report_types', 'roles.id_role=report_types.id_role')
			     ->where('name', $params['role']);
		if (isset($params['group'])) {
		   $this->db->where('report_group', $params['group']);
		}
		$res = $this->db->order_by('report_group ASC, report_order ASC')
					    ->get('roles');
		$list = array();
		if (isset($params['all'])) {
		   $list[0] = __('all');
		}
		foreach ($res->result() as $row) {
			// Modify plugin's report type 
         foreach($this->_hooks as $hookObj) {
            $row = $hookObj->modifyReportType(array($row, $params["role"]));
         }
			$list[$row->id_report_type] = __($row->title); 
		}
		return $list;
	} //end get_list

   /**
   * возвращает наборы столбцов для типов отчетов заданного пользователя и роли
   *
   * @param string $role наименование текущей роли пользователя
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $group группа отчетов, по умолчанию - 0
   * @return array список наборов столбцов в формате id_report_type => (row_name => (title, visible))
   */	
	public function get_visible_columns($role, $id_entity, $group = 0) {
	   $res =  $this->db->select('report_types.id_report_type AS id_report_type, visible_columns*1 AS visible_columns', FALSE)
	  			->join('report_types', 'roles.id_role=report_types.id_role')
	   			->join('report_entity_columns', 'report_types.id_report_type=report_entity_columns.id_report_type')
	   			->where(array('name' => $role, 'id_entity' => $id_entity, 'report_group' => $group))
	   			->get('roles');
      $visible = array();
	   foreach ($res->result() as $row) {
	   	$visible[$row->id_report_type] = $row->visible_columns;  
	   }	   
	   $this->db->select('report_types.id_report_type AS id_report_type, report_type_fields.title AS title, report_type_fields.name AS name, column_order, report_type_fields.is_unchanged');
	   $this->db->from('roles');
	   $this->db->join('report_types', 'roles.id_role=report_types.id_role');
	   $this->db->join('report_type_fields', 'report_types.id_report_type=report_type_fields.id_report_type');
	   $this->db->where(array('roles.name' => $role, 'report_group' => $group));
	   $this->db->order_by('id_report_type, column_order');
	   $res = $this->db->get();
	   
	   $columns = array();
	   foreach ($res->result() as $row) {
	   	$columns[$row->id_report_type][$row->name]['title'] = __($row->title);
	   	$columns[$row->id_report_type][$row->name]['is_unchanged'] = 'true' == $row->is_unchanged ? true : false;
	   	if (isset($visible[$row->id_report_type])) {
	   	   $columns[$row->id_report_type][$row->name]['visible'] = $columns[$row->id_report_type][$row->name]['is_unchanged'] || (($visible[$row->id_report_type] & (1 << $row->column_order)) > 0);
	   	} else {
            $columns[$row->id_report_type][$row->name]['visible'] = TRUE;
	   	}
	   }	   	   	   	   
	   return $columns;
	} //end get_visible_columns

   /**
   * сохраняет в базу данных настройки видимых столбцов для заданного пользователя и отчета
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $id_report_type код типа отчета, которого сохраняются настройки
   * @param integer $columns битовая маска (1 столбец отображается, 0 - нет)
   * @return ничего не возвращает
   */	
   public function save_visible_columns($id_entity, $id_report_type, $columns) {
      $this->db->where(array('id_entity' => $id_entity, 'id_report_type' => $id_report_type))
      	       ->from('report_entity_columns');
      $records = $this->db->count_all_results();
      if ($records) {
         $this->db->where(array('id_entity' => $id_entity, 'id_report_type' => $id_report_type))
         		  ->update('report_entity_columns', array('visible_columns' => $columns));       
      } else {
         $this->db->insert('report_entity_columns',
            array(
               'id_entity' => $id_entity, 
               'id_report_type' => $id_report_type,
               'visible_columns' => $columns));
      }                
   } //end save_visible_columns
    
   /**
   * добавляем новый отчет в список запрошенных отчетов
   *
   * @param integer $id_entity код учетной записи пользователя, запросившего отчет
   * @param integer $id_report_type код типа запрошенного отчета
   * @param integer $visible_columns битовая маска (1 столбец отображается, 0 - нет)
   * @param datetime $from дата начала отчетного периода
   * @param datetime $to дата конца отчетного периода
   * @param string $title пользовательское название отчета
   * @param string $extra (опционально) дополнительные параметры для отчета
   * @return integer код запрошенного отчета
   */   
   public function add_new_report($id_entity, $id_report_type, $visible_columns, $from, $to, $title, $extra = '') {
      $this->db->insert('requested_reports',
         array(
            'id_entity' => $id_entity,
            'custom_title' => $title,
            'id_report_type' => $id_report_type,
            'visible_columns' => $visible_columns,
            'period_start' => type_to_str($from, 'databasedate'),
            'period_end' => type_to_str($to, 'databasedate'),
            'request_date' => type_to_str(time(), 'databasedatetime'),
            'extra_params' => $extra
         )
      );   	
      return $this->db->insert_id();
   } //end add_new_report

   /**
   * возвращает список запрошенных отчетов для заданного пользователя
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param string $role роль пользователя, для которого получаем список отчетов
   * @param integer $type фильтр по типу отчетов (по умолчанию 0 - все типы отчетов)
   * @param integr $group требуемая группа отчетов (по умолчанию NULL - все группы)
   * @return array массив с данными отчетов (id => (title, type, date)) 
   */   
   public function get_reports($id_entity, $page, $per_page, $sort_field, $sort_direction, $range, $role, $type = 0, $group = NULL) {
      $this->db->select('id_requested_report, custom_title, title, unix_timestamp(request_date) as date');
      $this->db->from('requested_reports');
      $this->db->join('report_types', 'requested_reports.id_report_type = report_types.id_report_type');
      $this->db->join('roles', 'report_types.id_role = roles.id_role');
      $this->db->where('roles.name', $role);    
      if (!is_null($group)) {
         $this->db->where('report_group', $group);
      }    
      if ($type != 0) {
         $this->db->where('requested_reports.id_report_type', $type);  
      }
      $this->db->where('requested_reports.id_entity', $id_entity);
      $this->db->where('DATE(request_date) >=', type_to_str($range['from'], 'databasedate'));
      $this->db->where('DATE(request_date) <=', type_to_str($range['to'], 'databasedate'));
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($per_page, ($page-1)*$per_page);
      $res = $this->db->get();
      
      $reports = array();
      foreach ($res->result() as $row) {
         foreach($this->_hooks as $hookObj) {
	   $row = $hookObj->modifyReportType(array($row, $role));
         }
         $reports[$row->id_requested_report]['title'] = $row->custom_title;
         $reports[$row->id_requested_report]['type'] = __($row->title);
         $reports[$row->id_requested_report]['date'] = type_to_str($row->date, 'date');
      }    
      return $reports;      
   } //end get_reports 

   /**
   * возвращает настройки заданного отчета
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $id_report код отчета
   * @return array массив с данными отчета (title, type, vis, from, to, extra, id_entity)
   */   
   public function report($id_entity, $id_report) {
      $this->db->select('custom_title, visible_columns*1 AS cols, 
         UNIX_TIMESTAMP(period_start) AS date_from, UNIX_TIMESTAMP(period_end) AS date_to, 
         extra_params, id_report_type, id_entity', FALSE);
      $this->db->from('requested_reports');
      $this->db->where(array(/*'id_entity' => $id_entity, */'id_requested_report' => $id_report));
      $res = $this->db->get();
      foreach ($res->result() as $row) {
         return array(
            'title' => $row->custom_title,
            'type' => $row->id_report_type,
            'vis' => $row->cols,
            'from' => type_to_str($row->date_from, 'date'),
            'to' => type_to_str($row->date_to, 'date'),
            'extra' => $row->extra_params,
            'id_entity' => $row->id_entity
         );
      }    
      return FALSE;      
   } //end report
    
   /**
   * совершает заданное действие над выбранным отчетом
   *
   * @param string $action действие совершаемое над отчетом ('delete')
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $id_requested_report код запрошенного отчета
   * @return ничего не возвращает
   */   
   public function action($action, $id_entity, $id_requested_report) {
      $this->db->where(
         array(
         	'id_entity' => $id_entity, 
         	'id_requested_report' => $id_requested_report));
      switch ($action) {
         case 'delete': 
            $this->db->delete('requested_reports');
            break;    
      }              
   } //end action   

   /**
   * возвращает количество отчетов, удовлетворяющих заданнам условиям
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param string $filt фильтр по статусу
   * @param array $range массив с периодом дат (from, to)
   * @param string $role роль пользователя, для которого получаем список отчетов
   * @param integer $type фильтр по типу отчетов (по умолчанию 0 - все типы отчетов)
   * @param integer $group выбранная группа отчетов (по умолчанию NULL - все группы)
   *    * @return integer количество записей
   */   
   public function total($id_entity, $range, $role, $type = 0, $group = NULL) {
      $this->db->where('id_entity', $id_entity);
      if (!is_null($group)) {
         $this->db->where('report_group', $group);
      }
      $this->db->where('request_date >=', type_to_str($range['from'], 'databasedatetime'));
      $this->db->where('request_date <=', type_to_str($range['to'], 'databasedatetime'));
      if ($type != 0) {
         $this->db->where('requested_reports.id_report_type', $type);  
      }
      $this->db->join('report_types', 'requested_reports.id_report_type = report_types.id_report_type');
      $this->db->join('roles', 'report_types.id_role = roles.id_role');
      $this->db->where('roles.name', $role);      
      $this->db->from('requested_reports');
   	return $this->db->count_all_results();
   } //end total   
   
   /**
   * возвращает информацию о столбцах отчета выбранного типа
   *
   * @param integer $id_report_type код выбранного типа отчета
   * @param integer $visible битовая маска в которой отмечены отображаемые столбцы
   *     Если -1, то показываются все колонки
   * @return array массив с данными о столбцах отчета в формате name => (title, type)
   */   
   public function columns_info($id_report_type, $visible) {
      $this->db->select('report_type_fields.name AS name, report_type_fields.title AS title,'.
         ' field_types.name AS type, sort_column=column_order AS sorted, sort_direction, direction,'.
         ' report_type_fields.is_total, report_type_fields.is_unchanged, roles.name AS role');
      $this->db->from('report_type_fields');
      $this->db->join('field_types', 'report_type_fields.id_field_type = field_types.id_field_type');
      $this->db->join('report_types', 'report_type_fields.id_report_type = report_types.id_report_type');
      $this->db->join('roles', 'report_types.id_role = roles.id_role');
      $this->db->where('report_type_fields.id_report_type', $id_report_type);
      $this->db->order_by('column_order');
      $res = $this->db->get();
      
      $bit = 1;
      $columns = array();
      $sort = FALSE;
      $first = NULL;
      $first_dir = NULL;
      foreach ($res->result() as $row) {
      	if (-1 == $visible || $row->is_unchanged == 'true' || $visible & $bit) {
      	   if (is_null($first)) {
      	      $first = $row->name;
      	      $first_dir = $row->direction;
      	   }
      	   $columns['columns'][$row->name]['title'] = $row->title;
            $columns['columns'][$row->name]['type'] = $row->type;
            $columns['columns'][$row->name]['direction'] = $row->direction;
            $columns['columns'][$row->name]['role'] = $row->role;
            $columns['columns'][$row->name]['is_total'] = 'true' == $row->is_total ? true : false;
            $columns['columns'][$row->name]['is_unchanged'] = 'true' == $row->is_unchanged ? true : false;
            if ($row->sorted) {
               $sort = TRUE;   
               $columns['sort']['name'] = $row->name; 
               $columns['sort']['direction'] = $row->sort_direction;            
            }
      	}
      	$bit = $bit<<1;
      }
      if (!$sort) {
         $columns['sort']['name']= $first;
         $columns['sort']['direction'] = $first_dir;
      }
      return $columns;            
   } //end columns_info
   
} //end Report_types class

?>