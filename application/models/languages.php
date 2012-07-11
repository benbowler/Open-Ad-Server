<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* класс для работы с языками
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Languages extends CI_Model {
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
      $this->_table_name = 'languages';
      $this->_id_field_name = 'iso';
   } //end __construct

   /**
    * Возвращает список языков
    *
    * @param array $params параметры формируемого списка:
    *    filter ("enabled"|"banned"|"all") - список будет содержать ("незабаненные" - по умолчанию|"забаненные"|"все") языки
    *    search_string - строка для поиска языка по его названию
    * @return array список в формате id => name
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
      $res = $this->db->get($this->_table_name);      
      $languages = array();
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
            $name = $row->name;
            if ("" != $row->unicode_name) {
               $name .= " (".$row->unicode_name . ")";
            }
            $languages[$row->{$this->_id_field_name}] = $name;
         }
      }
      return $languages;
   } //end get_list
 
   /**
   * Осуществляет блокироваку/разблокировку языка
   *
   * @param string $iso идентификатор страны
   * @param boolean $is_banned бан-статус страны
   * @return none
   */   
   public function set_banned_status($id, $is_banned = true) {
      $this->db->where($this->_id_field_name,$id)->update($this->_table_name,array('banned' => $is_banned?'true':'false'));
   } //end set_banned_status
   
   /**
   * Осуществляет разблокировку всех языков
   *
   * @param none
   * @return none
   */   
   public function clear_languages_banned_status() {
      $this->db->update($this->_table_name,array('banned' => 'false'));
   } //end clear_languages_banned_status
   
   /**
   * Осуществляет получение информации о названии языка, статусе по его коду
   *
   * @param string $id_language
   * @return object|NULL
   */   
   public function get_info($id_language) {
      $this->db->select('name, unicode_name, banned');
      $this->db->where($this->_id_field_name, $id_language);
      $res = $this->db->get($this->_table_name);      
      if ($res->num_rows()) {
         return $res->row();
         }
      return NULL;
   } //end get_info
   
   /**
   * возвращает текстовое значение выбранного языка
   *
   * @param string $id_language уникальный код языка
   * @return string текстовое значение языка
   */
   public function get_value($id_language) {
      $res = $this->db->get_where('languages', array('iso' => $id_language));
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
   
}

?>