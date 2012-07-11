<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/**
 * Модель для работы с категориями
 * 
 * @author Немцев Андрей
 * @project SmartPPC6
 * @version 1.0.0
 */
require_once APPPATH . 'libraries/jQTree/jQTree.php';

class Category_Model extends CI_Model {
	
	const CAT_SEPARATOR = " &raquo; ";
	
	/**
	 * конструктор класса
	 *
	 * @return ничего не возвращает
	 */
	public function __construct() {
		parent::__construct ();
		$this->_table_name = 'categories';
		$this->_id_field_name = 'id_category';
	} //end __construct()
	

	/**
	 * возвращает HTML-код дерева категорий,
	 * текущая категория выделяется
	 *
	 * @param string $categories_tree_id - идентификатор дерева категорий
	 * @param string $on_click_func_name - имя функции, вызываемой при клике на элементе дерева
	 * @return string HTML-код дерева категорий
	 */
	public function get_html_tree($categories_tree_id = 'categories_tree', $on_click_func_name = 'set_active_category') {
	   
		$category_node0 = new jQTreeNode ();
		$this->db->select ( 'id_category, name, id_category_parent' );
		$this->db->order_by ( 'id_category_parent ASC, name' );
		$res = $this->db->get ( $this->_table_name );
		
		foreach ( $res->result () as $row ) {
			$id = "category_node" . $row->id_category;
			$id_parent = "category_node" . $row->id_category_parent;			
			$$id = new jQTreeNode ( );
			$$id->setCaption ( $row->name );
			$$id->setId ( $categories_tree_id.'_'.$row->id_category );
			$$id->setOnClick ( $on_click_func_name.'(' . $row->id_category . ',this)' );			
			$$id_parent->add ( $$id );		
		}		
		$tree = new jQTree ( );
		$tree->setModel ( $category_node1 );
		$tree->setId ( $categories_tree_id );
		return $tree->getTree ();
	} //end get_content

	/**
	 * Создание категории
	 *
	 * @param array $data параметры создаваемой категории ('id_category_parent','name','description')
	 * @return int|string идентификатор созданной категории либо текст ошибки
	 */
	public function create($data) {
	   $this->db->select($this->_id_field_name);
      $this->db->from($this->_table_name);
	   $this->db->where(array('name' => $data['name'], 'id_category_parent' => $data['id_category_parent']));
	   $query = $this->db->get();
	   if ($query->num_rows() > 0) {
	      return 'Cannot create category - already exist'; 
	   }
	   
	   $this->db->insert($this->_table_name , $data);
	   return $this->db->insert_id();
	}
	
   /**
    * Изменение категории
    *
    * @param array $data новые параметры категории ('id_category','name','description')
    * @return null|string null в случае успеха либотекст ошибки
    */
   public function update($data) {
      $this->db->select('id_category_parent');
      $this->db->from($this->_table_name);
      $this->db->where(array('id_category' => $data['id_category']));
      $query = $this->db->get();
      if ($query->num_rows() == 0) {
         return 'Cannot determite parent category'; 
      } else {
         $id_parent = $query->row()->id_category_parent; 
      }
      
      $this->db->select($this->_id_field_name);
      $this->db->from($this->_table_name);
      $this->db->where(array('name' => $data['name'], 'id_category_parent' => $id_parent, 'id_category !=' => $data['id_category']));
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return 'Cannot create category - already exist'; 
      }
      
      $this->db->where($this->_id_field_name, $data['id_category']);
      $this->db->update($this->_table_name , array('name' => $data['name'], 'description' => $data['description']));
      return null;
   }
	
   /**
    * Удаление категории включая всех ее потомков 
    *
    * @param int $id идентификатор категории
    * @return null|string null либо текст ошибки
    */
   public function delete($id) {
      if ($id > 1) {
         $ids_to_delete = array($id);
         $this->db->select('id_category, id_category_parent');
         $this->db->where('id_category >' ,$id);
         $this->db->order_by ( 'id_category_parent ASC' );
         $query = $this->db->get ( $this->_table_name );
         if ($query->num_rows() > 0) {
            foreach ( $query->result () as $row ) {
                if (in_array($row->id_category_parent, $ids_to_delete)) {
                   array_push($ids_to_delete, $row->id_category);
                }
            }
         }
         
         //определение количества неудаленных сайтов, содержащихся в категории и ее потомках
         $this->db->select('id_category');
         $this->db->from('sites');
         $this->db->join('site_categories', 'sites.id_site = site_categories.id_site');
         $this->db->where_in('id_category' ,$ids_to_delete);
         $this->db->where('sites.status <>' ,'deleted');
         $query = $this->db->get ();
         
         if ($query->num_rows() > 0) {
            return 'Cannot delete category due to it (or it\'s child category) contain sites';   
         }
         
         //определение количества неудаленных каналов, содержащихся в категории и ее потомках
         $this->db->select('id_category');
         $this->db->from('channels');
         $this->db->join('channel_categories', 'channels.id_channel = channels.id_channel');
         $this->db->where_in('id_category' ,$ids_to_delete);
         $this->db->where('channels.status <>' ,'deleted');
         $query = $this->db->get ();
         
         if ($query->num_rows() > 0) {
            return 'Cannot delete category due to it (or it\'s child category) contain channels';   
         }
         
         $this->db->where_in($this->_id_field_name,$ids_to_delete);
         $this->db->delete($this->_table_name);
         return null;   
      } else {
         return 'Cannot delete Top category';
      }
      
   }

   /**
    * Получение описания категории
    *
    * @param int $id идентификатор категории
    * @return null|string описание созданной категории
    */
   public function get_description($id) {
      $this->db->select('description');
      $this->db->from($this->_table_name);
      $this->db->where(array('id_category' => $id));
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        return $query->row()->description; 
      }
      return null;
   }
   
   /**
    * Получение названия категории
    *
    * @param int $id идентификатор категории
    * @return null|string название категории
    */
   public function get_name($id) {
      $this->db->select('name');
      $this->db->from($this->_table_name);
      $this->db->where(array('id_category' => $id));
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        return $query->row()->name; 
      }
      return null;
   }
   
   /**
    * Получение названия категорий по списку идентификаторов
    *
    * @param array $ids массив идентификаторов категорий
    * @return array  массив вида 'id' => 'название категории'
    */
   public function get_names($ids) {
   	$result = array();
   	if (!count($ids)) {
   		return $result; 
   	}
   	
      $this->db->select('id_category, name')
               ->where_in('id_category',$ids);
      $query = $this->db->get($this->_table_name);
      if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
        	 $result[$row->id_category] = $row->name;
        }
      }
      return $result;
   }
   
   /**
    * Получение id предка категории
    *
    * @param int $id идентификатор категории
    * @return int $id идентификатор предка категории
    */
   public function get_parent($id) {
      $this->db->select('id_category_parent')
               ->from($this->_table_name);
      $this->db->where(array('id_category' => $id));
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        return $query->row()->id_category_parent; 
      }
      return null;
   }  
   
   /**
    * Определение количества сайтов в категории
    *
    * @param integer $id - идентификатор категории
    * @param bool $include_subcats - учитывать при рассчете сайты, находящиеся в подкатегориях
    */
   public function get_sites_count($id, $include_subcats = false, $sites_params = array()) {
   	
   	if ($include_subcats) {
         $ids_to_determine = array_merge(array($id), $this->get_childs($id));
      	
      	$this->db->select('1', false)
      			 ->from('sites')
      			 ->join('site_categories', 'site_categories.id_site = sites.id_site', 'left')
      			 ->where_in('site_categories.id_category',$ids_to_determine)
      			 ->where('status <>','deleted')
      			 ->group_by('sites.id_site');
      			 
      	$subQuery = $this->db->_compile_select();
      } else {
      	$this->db->select('1', false)
      			 ->from('sites')
      			 ->join('site_categories', 'site_categories.id_site = sites.id_site', 'left')
      			 ->where('site_categories.id_category',$id)
      			 ->where('status <>','deleted')
      			 ->group_by('sites.id_site');
      			 
        $subQuery = $this->db->_compile_select();
      }
      
      $this->db->_reset_select();
      $query = $this->db->query('SELECT COUNT(*) as num_records FROM ('.$subQuery.') f');
      
      if ($query->num_rows() > 0) {
      	 return $query->row()->num_records;
      }
      
      return 0;
   }
   
   /**
    * Получение идентификаторов всех потомков категории (включая подкатегории)
    *
    * @param integer $id - идентификатор категории
    * @return array массив идентификаторов
    */
   public function get_childs($id) {
         $ids = array();
         $this->db->select('id_category, id_category_parent')
                  ->where('id_category_parent' ,$id)
                  ->order_by ( 'id_category_parent ASC' );
         $query = $this->db->get ( $this->_table_name );
         if ($query->num_rows() > 0) {
            foreach ( $query->result () as $row ) {
            	$ids[] = $row->id_category;
            	$childs = $this->get_childs($row->id_category);
            	foreach ($childs as $children) {
            		if (!in_array($children, $ids)) {
            			$ids[] = $children;
            		}
            	} 
            }
         }

         return $ids;
   }

   /**
    * Возвращает код корневой записи категорий
    *
    * @return integer код корневой записи
    */
   public function get_root() {
   	$res = $this->db
   	   ->select('id_category')
   	   ->where('id_category_parent', 0)
   	   ->get($this->_table_name);
   	if ($res->num_rows()) {
   	   return $res->row()->id_category;
   	}
   	return NULL;
   } //end get_root         
   
   /**
    * Получение идентификаторов всех потомков одним уровнем ниже
    *
    * @param integer $id - идентификатор категории
    * @return array массив идентификаторов
    */
   public function get_child_level($id = NULL) {
      $ids = array();
      if (is_null($id)) {
         $id = $this->get_root();
      }
      $query = $this->db
         ->select('id_category')
         ->where('id_category_parent' ,$id)
         ->order_by('name')
         ->get ( $this->_table_name );
      foreach ($query->result() as $row) {
         $ids[] = $row->id_category;
      }
      return $ids;
   } //end get_child_level
   
   /**
    * Получение пути к категории
    *
    * @param array $ids массив идентификаторов категорий, для которых необходимо определить путь
    * @return array массив вида (id_cat_1 => array('top_cat','sub_cat1',sub_cat2), id_cat_2 => array('top_cat','sub_cat5'))
    */
   public function get_path($ids = array()) {
   	$paths = array();
   	
   	$categories = array();
   	
   	$query = $this->db->from($this->_table_name)->select('id_category, id_category_parent, name')->get();
   	if ($query->num_rows() > 0) {
            foreach ( $query->result () as $row ) {
                $categories[$row->id_category] = array('id_parent' => $row->id_category_parent, 'name' => $row->name);
            }
            
            foreach ($ids as $id_req_category) { //Заполнение имен категорий в иерархии от Top до текущей категории
            	$id_current_parent = $id_req_category;
            	$paths[$id_req_category] = array();
            	while ($id_current_parent) { //0 - Top Category
            		$paths[$id_req_category][] = $categories[$id_current_parent]['name'];
            		$id_current_parent = $categories[$id_current_parent]['id_parent'];
            	}
            	$paths[$id_req_category] = array_reverse($paths[$id_req_category]);
            }
      }
      
     return $paths;
   }

   /**
    * возвращает цепочку категорий от верхнего уровня до текущей
    *
    * @param integer $id код текущей категории
    * @return array массив категорий в формате (id => name) 
    */
   public function get_chain($id) {
      $ids = array();
      do {
         array_unshift($ids, $id);
         $id = $this->get_parent($id);
      } while ($id);
      array_shift($ids);
      return $this->get_names($ids);
   } //end get_chain

   /**
   * возвращает ветку дерева категорий
   *
   * @param integer $parent код родителя ветки
   * @param array $list список всех элементов дерева
   * @param integer $level уровень вложенности
   * @return array массив ветки id => name
   */
   protected function branch($parent, $list, $level) {
      $branch = array();
      if (isset($list[$parent])) {
         foreach ($list[$parent] as $id => $name) {
            $branch[$id] = str_repeat('   ',$level).$name;            
         	$branch = $branch + $this->branch($id, $list, $level+1);
         }
      }     
      return $branch; 
   } //end branch
      
   /**
   * возвращает список существующих временных зон
   *
   * @return array список в формате id_timezone => name
   */   
   public function get_list($params) {
      $res = $this->db
         ->select('id_category, name, id_category_parent')
         ->order_by ('id_category_parent ASC, name')
         ->get($this->_table_name);
      $list = array();      
      $root = 0;
      foreach ($res->result () as $row) {
         if ($row->id_category_parent == 0) {
            $root = $row->id_category; continue;          
         }
         $name = $row->name;
         $list[$row->id_category_parent][$row->id_category] = $name;
      }
      $all = array('' => __('All categories'));
      return $all + $this->branch($root, $list, 1);            
   }   
   
   /**
    * Возвращает список категорий для заданног сайта
    * 
    * @param int $id_site
    * @return array массив имен категорий, связанных с сайтом
    */
   public function get_list_by_site($id_site) {
   	$query = $this->db
   	   ->select('name')
   	   ->from('categories c')
   	   ->join('site_categories sc', 'c.id_category=sc.id_category')
   	   ->where('id_site', $id_site)
   	   ->order_by('name')
   	   ->get();
   	$list = array();
      if ($query->num_rows()) {
      	foreach ($query->result() as $row) {
      		$list[] = $row->name;
      	}
      } 
      return $list;      
   }
   
}   

?>