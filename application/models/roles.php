<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Класс для получения списка ролей пользователей системы
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Roles extends CI_Model {
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end __construct

   public function find_entities($role_filter, $status_filter = 'all') {
   	$result = array();
   	switch ($role_filter) {
   		case 'advertiser':
   			$table_name = 'advertisers';
   			$id_field = 'id_entity_advertiser';
   		break;
   		case 'publisher':
            $table_name = 'publishers';
            $id_field = 'id_entity_publisher';
         break;
   	}
   	
   	if ('all' != $status_filter) {
   		$this->db->join('entities', $table_name.'.'.$id_field.' = entities.id_entity');
   		switch ($status_filter) {
   			case 'non_deleted':
   				$this->db->where('status <>','deleted');
   			break;
   		}
   		         
   	}
   	$query = $this->db->select('id_entity')   	
   	                  ->get($table_name);
   	
   	
   	if ($query->num_rows() > 0) {
   		foreach($query->result() as $row) {
   			$result[] = $row->id_entity;
   		}
   	} 
      return $result;
   }
   
   /**
   * возвращает список ролей пользователей
   *
   * @return array список в формате role => name
   */   
   public function get_list($params) {
   	
   	$result = array();
   	
   	if(isset($params['is_used'])) {
   		$this->db->where('is_used',$params['is_used']);
   	}
   	
      if(isset($params['is_recipient'])) {
         $this->db->where('is_recipient',$params['is_recipient']);
      }
      
      if(isset($params['order_by'])) {
         $this->db->order_by($params['order_by']);
      }
   	
   	$query = $this->db->select('name')
   	                  ->get('roles');
   	
   	foreach ($query->result() as $row) {
   		$result[$row->name] = $row->name;                  
   	}
   	                  
   	return $result;
   } //end get_list
   
   /**
    * Получение списка всех ролей из базы
    * 
    * @return array
    */
   public function get_all_roles() {
      $roles = array();
      $query = $this->db->select('id_role, name')
                        ->from('roles')
                        ->get();
      
      foreach ($query->result() as $row) {
         $roles[$row->id_role] = $row->name;
      }
      return $roles;
   }
   
   /**
    * Получение роли по названию
    *
    * @param string $name
    * @return mixed
    */
   public function get_role_by_name($name) {
      $role = null;
      $query = $this->db->from('roles')
			               ->where('name', $name)
			               ->limit(1)
			               ->get();
 
      if (0 < $query->num_rows()) {
         $role = $query->row();
      }
      return $role;
   }
}

?>