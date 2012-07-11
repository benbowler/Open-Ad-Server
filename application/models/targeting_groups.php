<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модель для работы с группами таргетинга
 * 
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Targeting_Groups extends CI_Model {

   /**
    * Конструктор класса
    *
    * @return Targeting_Groups
    */
   public function __construct() {
      parent::__construct();
   }
 
   /**
    * возвращает список групп таргетинга для заданного пользователя и роли
    *
    * @param integer $id_entity идентификатор пользователя
    * @param string $role роль пользователя
    * @param integer $page номер запрошенной страницы
    * @param integer $per_page количество записей на странице
    * @param string $sort_field имя поля, по которому осуществляется сортировка
    * @param string $sort_direction направление сортировки
    * @param string $filt фильтр по статусу
    * @return array массив с данными групп таргетинга 
    */   
   public function get($id_entity, $role, $page, $per_page, $sort_field, $sort_direction, $filt) {
      $this->db->select('tg.id_targeting_group AS id, tg.title, tg.status')
         ->from('targeting_groups tg')
         ->join('roles r', 'tg.id_role = r.id_role')
         ->where('tg.id_entity', $id_entity)
         ->where('tg.status <>', 'temp')
         ->where('r.name', $role);
      if ($filt != 'all') {
         $this->db->where('tg.status', $filt);
      }
      $this->db->order_by($sort_field, $sort_direction)
         ->limit($per_page, ($page-1) * $per_page);
      $res = $this->db->get();
      $list = array();
      $id_list = array();
      foreach ($res->result() as $row) {
         $id_list[] = $row->id;
         $list[$row->id]['title'] = $row->title;
         $list[$row->id]['status'] = $row->status;
      }
      return $list;      
   }
   
   /**
    * возвращает список групп таргетинга для заданного пользователя и роли
    *
    * @param array $params массив требуемых параметров групп таргетинга
    * @return array массив со списком групп таргетинга 
    */ 
   public function get_list($params) {

      $role = (isset($params['role']))?$params['role']:'guest';
  
      if (isset($params['sort_direction'])) {
         $this->db->order_by('tg.title', $params['sort_direction']);
      } else {
      	$this->db->order_by('tg.title');
      }
         
      if (isset($params['id_entity'])) {   
         $this->db->where('tg.id_entity', $params['id_entity']);
      }
      
      if ( isset($params['status']) && ($params['status'] != 'all')) {
         $this->db->where('tg.status', $params['status']);
      }
      
      $res = $this->db->select('tg.id_targeting_group AS id, tg.title')
				         ->from('targeting_groups tg')
				         ->join('roles r', 'tg.id_role = r.id_role')
				         ->where('r.name', $role)
				         ->get(); 
    
      $list = array();
      foreach ($res->result() as $row) {
         $list[$row->id] = $row->title;
      }
      return $list;
   }
   
   /**
    * возвращает количество групп таргетинга, попадающих под указанный фильтр
    *
    * @param integer $id_entity идентификатор пользователя
    * @param string $role роль пользователя
    * @param string $filt фильтр по статусу
    * @return integer количество
    */
   public function total($id_entity, $role, $filt) {
      $total = array(
         'cnt' => 0
      );
      
      // All count
      $this->db->from('targeting_groups tg')
         ->join('roles r', 'tg.id_role = r.id_role')
         ->where('tg.id_entity', $id_entity)
         ->where('r.name', $role)
         ->where('tg.status <>', 'temp');
      if ($filt != 'all') {
         $this->db->where('tg.status', $filt);
      }
      $total['cnt'] = $this->db->count_all_results();
      return $total;
   }

   /**
    * совершает заданное действие над выбранной группой таргетинга
    *
    * @param string $action действие совершаемое над кампанией ('pause', 'resume')
    * @param integer $id_entity идентификатор пользователя
    * @param string $role роль пользователя
    * @param integer $id_targeting_group идентификатор группы таргетинга
    * @return ничего не возвращает
    */   
   public function action($action, $id_entity, $role, $id_targeting_group) {
      // Получаем идентификатор роли
      $id_role = 0;
      $obj =& get_instance();
      $obj->load->model('roles');
      $role_obj = $obj->roles->get_role_by_name($role);
      if (null !== $role_obj) {
         $id_role = $role_obj->id_role;
      }
      $this->db->where(array('id_entity' => $id_entity, 'id_role' => $id_role, 'id_targeting_group' => $id_targeting_group));
      switch ($action) {
         case 'pause':
            $this->db->where('status', 'active')
                     ->update('targeting_groups', array('status' => 'paused'));
            break;
         case 'resume':
            $this->db->where('status', 'paused')
                     ->update('targeting_groups', array('status' => 'active'));
            break;
      }
   }
   
   /**
    * Удаление группы таргетинга
    *
    * @param integer $id_entity идентификатор пользователя
    * @param string $role роль пользователя
    * @param integer $id_targeting_group идентификатор группы таргетинга
    * @return bool
    */
   public function delete($id_entity, $role, $id_targeting_group) {
      // Получаем идентификатор роли
      $id_role = 0;
      $obj =& get_instance();
      $obj->load->model('roles');
      $role_obj = $obj->roles->get_role_by_name($role);
      if (null !== $role_obj) {
         $id_role = $role_obj->id_role;
      }
      // Проверяем возможность удаления
      $all_count = 0;
      foreach (array('sites', 'channels') as $table) {
         $count = $this->db->from($table)->where(array('id_targeting_group' => $id_targeting_group, 'status <>' => 'deleted'))->count_all_results();
         $all_count += $count;
         if (0 < $all_count) {
            break;
         }
      }
      if (0 < $all_count) {
         // Запись удалить нельзя
         return false;
      }
      // Удаляем запись
      $results = $this->db->from('targeting_groups')->where(array('id_entity' => $id_entity, 'id_role' => $id_role, 'id_targeting_group' => $id_targeting_group))
         ->count_all_results();
      if (0 < $results) {
         // Подтвердили принадлежность группы пользователю
         /*$this->db->where('id_targeting_group', $id_targeting_group)
            ->update('targeting_group_values', array('status' => 'deleted'));*/
         $this->db->where(array('id_entity' => $id_entity, 'id_role' => $id_role, 'id_targeting_group' => $id_targeting_group))
            ->update('targeting_groups', array('status' => 'deleted'));
      }
      return true;
   }

   /**
   * создает новую группу таргетинга
   *
   * @param integer $id_entity код пользователя создающего группу
   * @param integer $role роль для которой создается группа
   * @param string $name название для новой группы
   * @return integer код созданной группы таргетинга
   */
   public function create($id_entity, $role, $name) {
      if (is_numeric($role)) {
         $id_role = $role;
      } else {
         $res = $this->db->get_where('roles', array('name' => $role));
         if (!$res->num_rows()) {
            return;
         }
         $row = $res->row();
         $id_role = $row->id_role;
      }
   	$this->db->insert('targeting_groups',
   	   array(
   	      'id_entity' => $id_entity,
   	      'id_role'=> $id_role,
   	      'title' => $name,
   	      'status' => 'temp'
   	   )
   	);
   	return $this->db->insert_id();
   } //end create

   /**
   * очищает группу значений в заданной группе таргетинга
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы параметров
   * @return ничего не возвращает 
   */
   public function clear_group($id_targeting_group, $group) {
   	$this->db->delete('targeting_group_values', 
   	   array(
   	      'id_targeting_group' => $id_targeting_group,
   	      'group' => $group
   	   )
   	);
   } //end clear_group
   
   /**
   * добавляет в группу таргетинга новое значение
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @param string $value помещаемое значение
   * @return integer код добавленного значения
   */
   public function add_value($id_targeting_group, $group, $value, $compare = 'equals', $name = NULL) {
   	$this->db->insert('targeting_group_values',
   	   array(
   	      'id_targeting_group' => $id_targeting_group,
   	      '`group`' => $group,
   	      '`value`' => $value,
   	      'compare' => $compare,
   	      'name' => $name
   	   )
   	);
   	return $this->db->insert_id();   	
   } //end add_value

   /**
   * удаляет из группы таргетинга значение
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @param string $value удаляемое значение
   * @return ничего не возвращает
   */
   public function remove_value($id_targeting_group, $group, $value) {
      $this->db
         ->where('id_targeting_group', $id_targeting_group)
         ->where('group', $group)
         ->where('value', $value)
         ->delete('targeting_group_values');
   } //end remove_value
      
   /**
   * добавляет в группу таргетинга страны всего континента
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param integer $id_continent код континента
   * @return ничего не возвращает 
   */
   public function add_continent($id_targeting_group, $id_continent) {
   	$res = $this->db
   	  ->select('iso')
   	  ->from('countries c')
   	  ->join('targeting_group_values tgv', "iso=value AND tgv.group='countries' AND id_targeting_group =$id_targeting_group", "LEFT")
   	  ->where('id_continent', $id_continent)
   	  ->where('id_targeting_group_value IS NULL')
   	  ->get();
   	if ($res->num_rows()) {
   	   foreach ($res->result() as $row) {
   	   	$this->add_value($id_targeting_group, 'countries', $row->iso);
   	   }   	   
   	}   	  
   } //end add_continent

   /**
   * удаляет из группы таргетинга страны всего континента
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param integer $id_continent код континента
   * @return ничего не возвращает 
   */
   public function remove_continent($id_targeting_group, $id_continent) {
      $res = $this->db
        ->select('iso')
        ->from('countries c')
        ->join('targeting_group_values tgv', "iso=value AND tgv.group='countries' AND id_targeting_group =$id_targeting_group", "LEFT")
        ->where('id_continent', $id_continent)
        ->where('id_targeting_group_value IS NOT NULL')
        ->get();
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
            $this->remove_value($id_targeting_group, 'countries', $row->iso);
         }        
      }       
   } //end remove_continent
   
   /**
   * возвращает список значений группы значений для заданной группы таргетинга 
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @return string список значений разделенный запятыми
   */
   public function get_group_values($id_targeting_group, $group) {
      if (is_null($id_targeting_group)) {
         return '';
      }
   	$res = $this->db
   	  ->select('value')
   	  ->from('targeting_group_values')
   	  ->where('id_targeting_group', $id_targeting_group)
   	  ->where('group', $group)
   	  ->get();
   	if (!$res->num_rows()) {
   	   return '';
   	}
   	$list = array();
   	foreach ($res->result() as $row) {
   		$list[] = str_replace(',', '<;>', $row->value);
   	}
   	return implode(',', $list);
   } //end get_group_values   

   /**
   * возвращает данные по значениям заданной группы таргетинга
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @return array массив с данными значений группы таргетинга
   */
   public function get_group_list($id_targeting_group, $group, $page = NULL, $per_page = NULL, $sort_field = NULL, $sort_direction = NULL) {
   	$this->db
   	   ->select('id_targeting_group_value, name, value, compare')
   	   ->from('targeting_group_values')
   	   ->where('id_targeting_group', $id_targeting_group)
   	   ->where('group', $group);
   	if (!is_null($sort_field)) {  
         $this->db->order_by($sort_field, $sort_direction);
   	   $this->db->limit($per_page, ($page-1)*$per_page);
      }         
   	$res = $this->db->get();
   	$list = array();
   	if ($res->num_rows()) {
   	   foreach ($res->result() as $row) {
   	   	$list[$row->id_targeting_group_value]['name'] = $row->name;
            $list[$row->id_targeting_group_value]['value'] = $row->value;
            $list[$row->id_targeting_group_value]['compare'] = $row->compare;
   	   }   	   
   	}
      return $list;   	
   } //end get_group_list   

   /**
   * возвращает данные по значениям заданной группы таргетинга
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @return integer количество значений в группе заданной группы таргетинга
   */
   public function group_total($id_targeting_group, $group) {
      return $this->db
         ->from('targeting_group_values')
         ->where('id_targeting_group', $id_targeting_group)
         ->where('group', $group)
         ->count_all_results();
   } //end group_total      
   
   /**
   * возвращает информацию о выбранной группе таргетинга
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @return array структурированная информация о группе таргетинга, NULL - группа не найдена
   */
   public function get_info($id_targeting_group) {
   	$res = $this->db
   	   ->select('title')
   	   ->get_where('targeting_groups', array('id_targeting_group' => $id_targeting_group));
   	if (!$res->num_rows()) {
   	   return NULL;   	      	 
   	}
   	$info = array();
   	$info['title'] = $res->row()->title;
   	$res = $this->db
   	   ->select('id_targeting_group_value, group, name, value, compare')
   	   ->get_where('targeting_group_values', array('id_targeting_group' => $id_targeting_group));
   	if ($res->num_rows()) {
   	   foreach ($res->result() as $row) {
   	      $info[$row->group][$row->id_targeting_group_value]['name'] = $row->name;
   	      $info[$row->group][$row->id_targeting_group_value]['value'] = $row->value;
   	      $info[$row->group][$row->id_targeting_group_value]['compare'] = $row->compare;	
   	   }   	   
   	}
   	return $info;
   } //end get_info   
   
   /**
   * меняет имя группы таргетинга
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $title нове имя группы таргетинга
   * @return ничего не возвращает
   */
   public function set_title($id_targeting_group, $title) {
   	$this->db
   	   ->where('id_targeting_group', $id_targeting_group)
   	   ->update('targeting_groups', array('title' => $title));
   } //end set_title
   
   /**
   * помечает выбранное значение группы таргетинга как удаленное
   *
   * @param integer $id_targeting_group_value код значения группы таргетинга
   * @return ничего не возвращает 
   */
   public function delete_value($id_targeting_group_value) {
   	$this->db
   	   ->where('id_targeting_group_value', $id_targeting_group_value)
   	   ->delete('targeting_group_values');
   } //end name
   
   /**
   * вызывается при отмене создания/редактирования группы
   *
   * @param integer $id_targeting_group код создаваемой/редактируемой группы
   * @return ничего не возвращает
   */
   public function cancel($id_targeting_group) {
      $this->db
         ->where('id_targeting_group', $id_targeting_group)
         ->delete('targeting_groups');
      $this->db
         ->where('id_targeting_group', $id_targeting_group)
         ->delete('targeting_group_values');
   } //end cancel
      
   /**
    * Копирование группы таргетинга
    *
    * @param integer $id_targeting_group_from
    * @param integer $id_targeting_group_to
    * @return integer идентификатор группы таргетинга, в которую было осуществлено копирование 
    */
   public function copy($id_targeting_group_from, $id_targeting_group_to = NULL) {
      //echo "COPY FROM TO:".$id_targeting_group_from.' - '.$id_targeting_group_to;
   	  $query = $this->db->select('id_entity, id_role, title')
   	              ->where('id_targeting_group', $id_targeting_group_from)
   	              ->get('targeting_groups');
   	
      if (is_null($id_targeting_group_to)) {
      	 $obj =& get_instance();
		 
         $id_targeting_group_to = $this->create($obj->get_user_id(), $obj->get_role(), 'AutoCreate');
   	  } else {
   		 $this->db->from('targeting_groups')
                  ->where('id_targeting_group',$id_targeting_group_to);
             
         if (0 == $this->db->count_all_results()) { //Группа назначения не найдена
            return -1;
         }
   	  }
   	  if ($query->num_rows() > 0) {
   	     $row = $query->row();
         $this->db->where('id_targeting_group',$id_targeting_group_to);
         $this->db->update('targeting_groups',
            	  array(
                  'id_entity' => $row->id_entity,
                  'id_role'=> $row->id_role,
            	   'title'=> $row->title/*,
                  'status' => 'temp'
                 */));
    	 //Очистка значений группы назначения
    	 $this->db->where('id_targeting_group',$id_targeting_group_to)->delete('targeting_group_values');
    	 
    	 //Копирование значений группы таргетинга
    	 $query = $this->db->from('targeting_group_values')
                        ->where('id_targeting_group',$id_targeting_group_from)
                        ->get();
         
         if ($query->num_rows() > 0) {
         	foreach ($query->result() as $row) {
         		$this->db->insert('targeting_group_values',
         		   array(
		            'id_targeting_group' => $id_targeting_group_to,
		            '`group`' => $row->group,
		            '`value`' => $row->value,
		            'compare' => $row->compare,
		            'name' => $row->name
		           ));
      	    }
         }	   	   
   	  }
      return $id_targeting_group_to;
   } //end copy
   
   /**
    * 
    * 
    * @param integer $id_targeting_group_from
    * @param integer $id_targeting_group_to\
    * @return ничего не возвращает
    */
   public function move($id_targeting_group_from, $id_targeting_group_to) {
   	$this->copy($id_targeting_group_from, $id_targeting_group_to);
   	$this->cancel($id_targeting_group_from);
   } //end move   
   
   /**
   * помечает выбранное значение группы таргетинга как удаленное
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param string $group наименование группы значений
   * @param string $value значение таргетинга
   * @return ничего не возвращает 
   */
   public function set_group_value($id_targeting_group, $group, $values) {
      $this->db
         ->where('id_targeting_group', $id_targeting_group)
         ->where('group', $group)
         ->delete('targeting_group_values');
      foreach (explode(',', $values) as $value) {
         if ($value == '') {
            continue;
         }
      	$this->db
      	   ->insert('targeting_group_values', 
      	     array(
      	        'id_targeting_group' => $id_targeting_group,
      	        'group' => $group,
      	        'value' => $value
      	     ));
      }
   } //end set_group_value

   /**
   * устанавливает новый статус для заданной группы таргетинга
   *
   * @param integer $id_targeting_group код выбранной группы таргетинга
   * @param string $status новый статус
   * @return ничего не возвращает 
   */
   public function set_status($id_targeting_group, $status) {
   	$this->db
   	   ->where('id_targeting_group', $id_targeting_group)
   	   ->update('targeting_groups', array('status' => $status));
   } //end set_status
   
   /**
   * уничтожает устаревшие (больше часа) временные группы таргетинга и их значения
   *
   * @return integer количество уничтоженных групп таргетинга
   */
   public function kill_expired() {
   	$this->db->query(
   	  "DELETE tg.*, tgv.*
   	   FROM targeting_groups tg LEFT JOIN targeting_group_values tgv USING (id_targeting_group)
   	   WHERE tg.status='temp' AND ADDDATE(creation_date, INTERVAL 1 HOUR)<NOW()"   	  
      );
   } //end kill_expire   
   
} //end class Targeting_Groups

?>