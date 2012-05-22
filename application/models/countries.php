<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/jQTree/jQTree.php';

/**
* класс для работы со странами
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Countries extends CI_Model {
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end Countries

   /**
    * Возвращает список стран
    *
    * @param array $params параметры формируемого списка:
    *    filter ("enabled"|"banned"|"all") - список будет содержать ("незабаненные" - по умолчанию|"забаненные"|"все") страны
    *    search_string - строка для поиска страны по ее полному английскому названию
    *    add_select - true, если первой строкой надо добавить 'select country...'
    * @return array список в формате iso => name
    */   
   public function get_list($params) {
      if (array_key_exists("filter", ($params))) {
         switch ($params["filter"]) {
         	case "enabled":
         	   $this->db->where('banned', 'false');
         	   break;
         	case "banned":
               $this->db->where('banned', 'true');
         }       
      } else {         
         $this->db->where('banned', 'false');               
      }   
      if (array_key_exists("search_string", ($params))) {
         $this->db->like('name',$params["search_string"]);
      }   
      $res = $this->db->get('countries');      
      $countries = array();
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
            $name = $row->name;
            if ("" != $row->unicode_name) {
               $name .= " (" . $row->unicode_name . ")";
            }
            $countries[$row->iso] = $name;
         }
      }
      if (isset($params['add_select'])) {         
         $countries = array_merge(array('' => __('select country')), $countries);
      }
     
      return $countries;
   } //end get_list
 
   /**
   * осуществляет блокироваку/разблокировку страны
   *
   * @param string $iso идентификатор страны
   * @param boolean $is_banned бан-статус страны
   * @return none
   */   
   public function set_banned_status($iso, $is_banned = true) {
      $this->db->where('iso',$iso)->update('countries',array('banned' => $is_banned?'true':'false'));
   } //end set_banned_status
   
   /**
   * осуществляет разблокировку всех стран
   *
   * @param none
   * @return none
   */   
   public function clear_countries_banned_status() {
      $this->db->update('countries',array('banned' => 'false'));
   } //end clear_countries_banned_status
   
   /**
   * Осуществляет получение информации о названии страны, статусе по ее коду
   *
   * @param string $iso
   * @return object|NULL
   */   
   public function get_info($iso) {
   	$this->db->select('name, unicode_name, banned');
   	$this->db->where('iso', $iso);
      $res = $this->db->get('countries');      
      if ($res->num_rows()) {
         return $res->row();
         }
      return NULL;
   } //end get_info

   /**
   * возвращает текстовое значение выбранной страны
   *
   * @param string $id_country уникальный код страны
   * @return string текстовое значение языка
   */
   public function get_value($id_country) {
      $res = $this->db->get_where('countries', array('iso' => $id_country));
      if (!$res->num_rows()) {
         return '';     
      }
      $row = $res->row();
      $name = $row->name;
      if ("" != $row->unicode_name) {
         $name .= " (".$row->unicode_name . ")";
      }
      return $name;
   } //end get_value   

   /**
   * возвращает количество стран в таргетинге
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @return integer количество стран
   */
   public function get_targeting_count($id_targeting_group) {
   	return $this->db
         ->from("targeting_group_values tgv")
         ->join('countries c', 'tgv.value=c.iso')
         ->where('id_targeting_group', $id_targeting_group)
         ->where('group', 'countries')
         ->where('banned <>', 'true')
         ->count_all_results();
   } //end get_targeting_count
   
   /**
   * возвращает количество всех незабаненных стран
   *
   * @return integer количество стран
   */
   public function get_coutries_count() {
      return $this->db
         ->from("countries")
         ->where('banned <>', 'true')
         ->count_all_results();
   } //end get_targeting_count   
   
   /**
   * если добавлены все возможные страны очищаем список
   *
   * @param $id_targeting_group
   * @return boolean true - добавлены все страны
   */
   public function clear_all_allowed($id_targeting_group) {
      echo $this->get_targeting_count($id_targeting_group);
      echo $this->get_coutries_count();
      if ($this->get_targeting_count($id_targeting_group) == $this->get_coutries_count()) {
         $this->db
            ->where('id_targeting_group', $id_targeting_group)
            ->where('group', 'countries')
            ->delete('targeting_group_values');
         return TRUE;         
      }
      return FALSE;
   } //end get_targeting_count   

   /**
   * добавляет все незабаненные страны в группу таргетинга
   *
   * @param $id_targeting_group
   * @return ничего не возвращает
   */
   public function add_all_countries($id_targeting_group) {
      $res = $this->db
         ->select('iso')
         ->get_where('countries', array('banned <>' => 'true'));
      foreach ($res->result() as $row) {
      	$this->db->insert('targeting_group_values', 
      	  array(
      	     'id_targeting_group' => $id_targeting_group,
      	     'group' => 'countries',
      	     'value' => $row->iso
      	  ));
      }      
   } //end get_targeting_count      
   
   /**
   * возвращает фильтрованный по таргетингу список континентов/стран в виде дерева
   *
   * @param integer $id_targeting_group код группы таргетинга
   * @param boolean $allowed фильтр (true - возвращает разрешенные страны, false - запрещенные)
   * @param boolean $all_denied случай, когда запрещены все страны
   * @return string HTML-код дерева списка
   */
   public function get_tree($id_targeting_group, $allowed, $all_denied) {
      $values_count = $this->get_targeting_count($id_targeting_group);
      if ($values_count == 0) {
         if ($all_denied) {
            if ($allowed) {
               return '';               
            }
         } else {
            if (!$allowed) {
               return '';
            }
         }
         $res = $this->db
            ->select('iso, c.name AS country, c.unicode_name, con.name AS continent, con.id_continent')
            ->from('countries c')
            ->join('continents con', 'c.id_continent=con.id_continent')
            ->where('banned <>', 'true')
            ->order_by('continent')
            ->order_by('country')
            ->get();         
      } else {
         $not = $allowed?' NOT':'';
         $res = $this->db
            ->select('iso, c.name AS country, c.unicode_name, con.name AS continent, con.id_continent')
            ->from('countries c')
            ->join('continents con', 'c.id_continent=con.id_continent')
            ->join('targeting_group_values tgv', "c.iso=tgv.value AND tgv.id_targeting_group=$id_targeting_group AND tgv.group='countries'", 'LEFT')
            ->where('banned <>', 'true')
            ->where("id_targeting_group_value IS$not NULL")
            ->order_by('continent')
            ->order_by('country')
            ->get();
      }         
      $type = $allowed?'allowed':'denied';
      $top = new jQTreeNode();
      $top->setCaption(__('All Countries'));
      $top->setId($type.'_all_countries');
      $top->setOnClick("");
      $countries = array();            
      foreach ($res->result() as $row) {
         $countries[$row->id_continent]['name'] = $row->continent;
         $name = $row->country;
         if ("" != $row->unicode_name) {
            $name .= " (".$row->unicode_name . ")";
         }         
         $countries[$row->id_continent]['list'][$row->iso] = $name;
      }      
      foreach ($countries as $id_continent => $continent) {
      	$continent_node = new jQTreeNode();
      	$continent_node->setCaption(_($continent['name']));
      	$id = ($continent['name'] == 'Unknown') ? $type.'_country_UN' : $type."_continent_".$id_continent;
      	$continent_node->setId($id);
         $continent_node->setOnClick("countries_switch_selection('$id')");
         $top->add($continent_node);
         foreach ($continent['list'] as $iso => $name) {
            if ($name == 'Unknown') {
               continue;
            }
         	$country_node = new jQTreeNode();
         	$country_node->setCaption($name);
         	$id = $type.'_country_'.$iso; 
         	$country_node->setID($id);
         	$country_node->setOnClick("countries_switch_selection('$id')");
         	$continent_node->add($country_node);
         }         
      }
      $tree = new jQTree();
      $tree->setModel($top);
      $tree->setId($type.'_country_tree');
      return $tree->getTree();      
   } //end get_tree   
   
} //end Model Countries

?>