<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Модель генератора отчетов
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Report_generator extends CI_Model {
 
   /**
   * конструктор класса
   */
	public function __construct() {
      parent::__construct();     
   } //end Report_generator
   
   /**
   * возвращает имя sqlite файла для заданного отчета
   *  
   * @param integer $_id_report код отчета
   * @return string имя sqlite файла
   */
   public function get_sqlite_file($id_report) {

      $this->db->select('roles.name AS role');
      $this->db->from('requested_reports');
      $this->db->join('report_types', 'requested_reports.id_report_type=report_types.id_report_type');
      $this->db->join('roles','report_types.id_role=roles.id_role');
      $this->db->where('id_requested_report', $id_report);
      $res = $this->db->get();
      $row = $res->row();
      $role = $row->role;
      return BASEPATH . "../files/reports/".$role."/".$id_report.".sqlite";
   } //end get_sqlite_file   

   /**
   * возвращает sql-запрос для генерации отчета заданного типа
   *  
   * @param integer $id_report_type код типа отчета
   * @return string строка sql-запроса
   */   
   public function get_sql_request($id_report_type) {
      $sql_file = BASEPATH . "../files/reports/sql/" . $id_report_type . ".sql";
      $this->load->helper('file');
      $sql_reuest = read_file($sql_file);
      return $sql_reuest;      
   } //end get_sqlite_file   

   /**
   * кодирование заголовков письма в MIME формате
   *
   * @param string $str текст
   * @return string закодированная строка
   */
   public function utf8($str) {
      return '=?UTF-8?B?'.base64_encode($str).'?=';
   } //end utf8
    
   /**
   * возвращает данные отчета по его коду
   *
   * @param integer $id_report уникальный код отчета
   * @return array массив с данными отчета (id_entity, title, e_mail, role), NULL - при ошибке
   */
   public function get_report_info($id_report) {
      $res = $this->db->select('entities.id_entity, custom_title, entities.e_mail, roles.name')->
         from('requested_reports')->
         join('entities', 'requested_reports.id_entity = entities.id_entity')->
         join('report_types', 'requested_reports.id_report_type = report_types.id_report_type')->
         join('roles', 'report_types.id_role = roles.id_role')->
         where('id_requested_report', $id_report)->
         get();
      if ($res->num_rows()) {
         $row = $res->row();
         return array(
            'id_entity' => $row->id_entity,
            'title' => $row->custom_title,
            'e_mail' => $row->e_mail,
            'role' => $row->name
         );                                       
      }
      return NULL;
   } //end get_report_info   
   
   /**
   * создание таблицы для отчета заданного типа
   *
   * @param integer $id_report_type код типа отчета
   * @param object $sqlite объект базы данных sqlite
   */   
   function create_report_table($id_report_type, $sqlite) {
      $res = $this->db->query("
         SELECT report_type_fields.name AS nm, field_types.name AS tp
         FROM report_type_fields LEFT JOIN field_types USING(id_field_type)
         WHERE id_report_type=$id_report_type
         ORDER BY column_order");
      $sql = "CREATE TABLE report (";
      $notfirst = FALSE;
      foreach ($res->result() as $row) {
         if ($notfirst) $sql .= ", "; 
      	$name = $row->nm;
      	$type = $row->tp;
      	$sql .= $name . " ";
      	switch ($type) {
      		case 'string': $sql .= "TEXT"; break;
      		case 'integer': $sql .= "INTEGER"; break;
      		case 'procent': $sql .= "REAL"; break;
      		case 'money': $sql .= "REAL"; break;
            case 'date': $sql .= "TEXT"; break;
            case 'time': $sql .= "TEXT"; break;
      	   case 'datetime': $sql .= "TEXT"; break;
            case 'float': $sql .= "REAL"; break;
            case 'boolean': $sql .= "INTEGER"; break;
            default: $sql .= "TEXT"; break;
      	}
      	$notfirst = TRUE;
      }
      $sql .= ")";
      $sqlite->query("DROP TABLE IF EXISTS report");
      $sqlite->query($sql);       
   }   
   
   /**
   * генерирует отчет и помещает его в файл-базу SQLite
   *  
   * @param integer $id_report код отчета
   * @param object $db объект sqlite базы данных
   */   
   public function generate_report($id_report, $sqlite) {
   	//получаем данные отчета   	
      $res = $this->db->get_where('requested_reports', array('id_requested_report' => $id_report));
      $row = $res->row();
      $id_entity = $row->id_entity;
      $id_report_type = $row->id_report_type;
      $period_start = $row->period_start;   
      $period_end = $row->period_end;
      $extra_params = $row->extra_params;
      //получаем шаблон SQL-запроса
      $sql_request = $this->get_sql_request($id_report_type);
      //устанавливаем дату начала и конца отчета
      $sql_request = str_replace('<%PSTART%>', $period_start, $sql_request);
      $sql_request = str_replace('<%PEND%>', $period_end, $sql_request);
  	   $sql_request = str_replace('<%PSTART_TIME%>',    $period_start . ' 00:00:00', $sql_request);
      $sql_request = str_replace('<%PEND_TIME%>',      $period_end . ' 23:59:59',   $sql_request);
      $sql_request = str_replace('<%ID_ENTITY%>', $id_entity,    $sql_request);
      //при наличии дополнительных параметров - устанавливаем их
      if ($extra_params != "") {
         $sql_request = str_replace('<%EXTRA%>', $extra_params, $sql_request);
         $sql_request = preg_replace('/<%EX[SE]%>/', '', $sql_request);       
      } else {
         $sql_request = preg_replace('/<%EXS%>.*<%EXE%>/', '', $sql_request);       
      }
      //создаем таблицу отчета в базе данных SQLite
      $this->create_report_table($id_report_type, $sqlite);
      //получаем данные SQL-запроса отчета      
      $res = $this->db->query($sql_request);
      if ($res->num_rows()) {
         foreach ($res->result_array() as $row) {
            //для каждой строки данных генерим SQL-запрос добавления данных
         	$sql = "INSERT INTO report VALUES(";
         	$notfirst = FALSE;
         	foreach ($row as $field) {
         	   if ($notfirst) $sql .= ", ";
               $sql .= $sqlite->escape($field);	
         	   $notfirst = TRUE;
         	}         	
         	$sql .= ");";
            //добавляем данные в таблицу отчета
            $sqlite->query($sql);
         }   
      }            
   } //end generate_report()

   /**
   * возвращает общее количество записей в заданном файле-отчете
   *
   * @param object $sqlite объект sqlite-базы данных
   * @return integer количество записей в таблице базы данных
   */
   public function total($sqlite) {
      return $sqlite->count_all('report');
   } //end total
   
   public function total_info($sqlite, $columns) {
      // Массив для возврата
      $result = array_map(create_function('$v', 'return 0;'), array_flip($columns));
      // Получаем суммы нужных колонок
      foreach ($columns as $column) {
         $sqlite->select_sum($column);
      }
      $sqlite->from('report');
      $res = $sqlite->get();
      if($res) {
         if (0 < $res->num_rows()) {
            $row = $res->row_array();
            foreach ($row as $key => $val) {
               $result[$key] = $val;
            }
         }
      }
      return $result;
   }
   
   /**
   * возвращает нужные ряды данных выбранного сортированного отчета
   *
   * @param object $sqlite объект sqlite-базы данных
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param integer $page (опционально) номер запрошенной страницы, при отсутствии - отчет полностью
   * @param integer $per_page (опционально) количество записей на странице 
   * @return array ряды данных отчета
   */
   public function report($sqlite, $sort_field, $sort_direction, $page = NULL, $per_page = NULL) {
      $sqlite->from('report');
      $sqlite->order_by($sort_field, $sort_direction);
      if (!is_null($page)) {
         $sqlite->limit($per_page, ($page-1)*$per_page);
      }
      $res = $sqlite->get();
      return $res->result_array();
   } //end report   
   
} //end Report_generator

?>