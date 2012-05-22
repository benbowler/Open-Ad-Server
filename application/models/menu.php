<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для генерации меню страницы
* доступные методы:
* generate($role, $highlight) генерирует HTML код для меню страницы
* get_controller($menuitem) по имени пункта меню возвращает соответствующий контроллер
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Menu extends CI_Model  {
 
   protected $select_menu="";    // код выделенного пункта главного меню
   
   protected $select_sub="";     // код выделенного пункта подменю
 
   protected $_hooks = array(); 
   
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
	public function __construct() {
		parent::__construct();
		// Fill up hooks array
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->common->menu)) {
         foreach($pluginsConfig->common->menu as $hookClass) {
            $hookObj = new $hookClass();
            if ($hookObj instanceof Sppc_Common_Menu_EventHandlerInterface) {
               $this->_hooks[] = $hookObj;
            }
         }
      }
	} //end Menu()

   /**
   * определяет выделенные пункты главного меню и подменю
   *
   * @param string $role тип пользователя, который работает в системе
   * @param string $highlight имя активного пункта меню
   * @return ничего не возвращает
   */
	protected  function get_select($role, $highlight) {
	   $this->db->select('id_menu_item, parent_item_id');
	   $this->db->from('menu_items')->join('roles', 'menu_items.id_role=roles.id_role');
	   $this->db->where(array('roles.name' => $role, 'menu_items.name' => $highlight));	   
	   $res = $this->db->get();
	   if($res->num_rows()) {
         $row = $res->row();
         $this->select_menu = $row->parent_item_id;
         $this->select_sub = $row->id_menu_item;      
      }                 
	}
	
   /**
   * генерирует HTML код для меню страницы
   *
   * @param string $role тип пользователя, который работает в системе
   * @param string $highlight имя активного пункта меню,
   *     если равен "", выводит только главное меню без подсветки
   * @return string сгенерированный HTML код для меню страницы 
   */
	public function generate($role, $highlight) {
	   if ($highlight != "") { 
   	   $this->get_select($role, $highlight);
	   }
	   $this->db->select('id_menu_item, parent_item_id, menu_items.name, controller');
       $this->db->from('menu_items')->join('roles', 'menu_items.id_role=roles.id_role');
       $this->db->where(array('roles.name' => $role, 'visible' => 'true'));
       $this->db->order_by('parent_item_id ASC, position');
	   $res = $this->db->get();
	   
	   
      $old_parent = -1;      
      $selected = $this->load->view('common/menu/selected.html', '', TRUE);
      $top_menu = '';
      $sub_menu = '';
      $sub_menus = '';
      $menu_item = $this->load->view('common/menu/item.html', '', TRUE);
      $submenu = $this->load->view('common/menu/submenu.html', '', TRUE);      
      if($res->num_rows()) {
         foreach ($res->result() as $row) {
            // Modify menu item info
            foreach($this->_hooks as $hookObj) {
               $row = $hookObj->modifyMenuItem(array($row, $role));
            }
            $url = $row->controller;
            
            if (!preg_match('~^https?://~', $url)) {
               $url = base_url() . ltrim($this->config->item('index_page') . '/', '/') . ltrim($url, '/');
            }
            $cur_menu_item = str_replace('<%HREF%>', $url, $menu_item);
            $cur_menu_item = str_replace('<%NAME%>', __($row->name), $cur_menu_item);            
            if ($row->parent_item_id == '') {
               $top_menu .= str_replace('<%SELECTED%>', ($row->id_menu_item == $this->select_menu) ? $selected : '', $cur_menu_item);                             
            } else {
               if ($old_parent != $row->parent_item_id) {
                  if ($old_parent != -1) {
                     $cur_submenu = str_replace('<%ITEMS%>', $sub_menu, $submenu);
                     $sub_menus .= str_replace('<%DISPLAY%>', $display = ($old_parent == $this->select_menu) ? 'block': 'none', $cur_submenu);                      
                     $sub_menu = '';
                  }
                  $old_parent = $row->parent_item_id;
               }
               if ($row->parent_item_id == $this->select_menu) {
                  $sub_menu .= str_replace('<%SELECTED%>', ($row->id_menu_item == $this->select_sub) ? $selected : '', $cur_menu_item);
               }                                            
            }
         }
      }
      if ($sub_menu != '') {
         $cur_submenu = str_replace('<%ITEMS%>', $sub_menu, $submenu);  
         $sub_menus .= str_replace('<%DISPLAY%>', $display = ($old_parent == $this->select_menu) ? 'block': 'none', $cur_submenu);
      }      
      if ($sub_menus != '') {       
         $sub_menus = str_replace('<%MENUS%>', $sub_menus, $this->load->view('common/menu/submenus.html', '', TRUE));
      }

      // add additional help menu items
      $additionalHelpMenuItems = array();
      $CI = &get_instance();
      $role = $CI->get_role();
      
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      $hookObjects = array();
      if (isset($pluginsConfig->menu->help_items)) {
      	foreach ($pluginsConfig->menu->help_items as $hookClass) {
      		$hookObject = new $hookClass;
      		if ($hookObject instanceof Sppc_Menu_HelpItems_Interface) {
      			$hookObjects[] = $hookObject;
      		}
      	}
      }
      
      foreach($hookObjects as $hookObject) {
      	$additionalHelpMenuItems = $hookObject->getMenuItems($role, $additionalHelpMenuItems);
      }
      
      $viewData = array(
      	'ADDITIONAL_HELP' => $additionalHelpMenuItems
      );
      
      $menu = str_replace('<%ITEMS%>', $top_menu, $this->parser->parse('common/menu/template.html', $viewData, TRUE)); 
      return str_replace('<%SUBMENUS%>', $sub_menus, $menu);
	}

   /**
   * по имени пункта меню возвращает соответствующий контроллер
   *
   * @param string $menuitem имя нужного пункта меню
   * @return string контроллер, который вызывается при выборе заданного пункта меню
   */	
   public function get_controller($role, $menuitem) {
      if (!$menuitem) {
         return NULL;
      }
      $this->db->select('controller');
      $this->db->from('menu_items')->join('roles', 'menu_items.id_role=roles.id_role');
      $this->db->where(array('roles.name' => $role, 'menu_items.name' => $menuitem));
      $res = $this->db->get();
      if ($res->num_rows()) {
         $row = $res->row();
         if ($row->controller == "Login") {
            return NULL; 
         } else {
            return $row->controller;
         }       
      } else {
         return NULL;
      }
   }
   
   /**
    * Create new menu item
    * 
    * @param int $role
    * @param int $parent_id
    * @param int $position
    * @param string $name
    * @param string $controller
    * @return int Return id of created menu item. On fail return false
    */
   public function create($role, $parent_id, $position, $name, $controller) {
   	  $data = array(
   	  	 'id_role' => $role,
   	  	 'parent_item_id' => $parent_id,
   	  	 'position' => $position,
   	  	 'name' => $name,
   	  	 'controller' => $controller
   	  );
   	  
   	  $result = $this->db->insert('menu_items', $data);
   	  if (true === $result) {
   	  	return $this->db->insert_id();
   	  }
   	  
   	  return false;
   }
   
   /**
    * Remove menu item(s) for specified controller
    * 
    * @param string $controller
    */
   public function remove_by_controller($controller) {
   	  $this->db->where('controller', $controller);
   	  $this->db->delete('menu_items');
   }
   
   /**
    * Check if menu item for specifie controller exists
    * 
    * @param string $controller
    * @return bool
    */
   public function is_exists_for_controller($controller) {
   	  $this->db->from('menu_items');
   	  $this->db->where('controller', $controller);
   	  $count = $this->db->count_all_results();
   	  
   	  return ($count > 0) ? true : false;
   }
}

?>