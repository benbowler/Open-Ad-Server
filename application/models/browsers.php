<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* модель для работы со списками обозревателей
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Browsers extends CI_Model {
 
   public function __construct() {
      parent::__construct();
   } //end Browsers

   /**
   * возвращает отфильтрованный список обозревателей
   *
   * @param array $params набор параметров для фильтрования списка 
   *     id_targeting_group - код группы таргетинга, по умолчанию считаются все обозреватели enabled
   *     status - статус таргетинга браузера (enabled, disabled), по умолчанию - все обозреватели 
   * @return array список в формате name => title
   */   
   public function get_list($params = array()) {
      $this->db
         ->select('b.name, b.title')
         ->from('browsers b')
         ->join('targeting_group_values tgv', 'b.name=tgv.value', 'LEFT')
         ->order_by('position');
      if (isset($params['id_targeting_group'])) {
         $this->db
            ->where('id_targeting_group', $params['id_targeting_group']);
      }
      $res = $this->db->get();
      $list = array();
      if ($res->num_rows()){
         foreach ($res->result() as $row) {
            $list[$row->name] = $row->title;	
         }         
      }      
      $all_list = array();
      $res = $this->db->select('name, title')->order_by('position')->get('browsers');
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
         	$all_list[$row->name] = $row->title;
         }         
      }      
      if (isset($params['status'])) {
         if ($params['status'] == 'enabled') {
            if(isset($params['id_targeting_group'])) {            
               return $list;
            } else {
               return $all_list;   
            }
         } else {
            if(isset($params['id_targeting_group'])) {
               return array_diff_assoc($all_list,$list);
            } else {
               return array();
            }
         }
      }      
      return $all_list;
   } //end get_list

   /**
   * возвращает текстовое значение выбранного обозревателя
   *
   * @param string $id_browser уникальный код обозревателя
   * @return string текстовое значение языка
   */
   public function get_value($id_browser) {
      $res = $this->db->get_where('browsers', array('name' => $id_browser));
      if (!$res->num_rows()) {
         return '';     
      }
      $row = $res->row();
      return $row->title;
   } //end get_value
   
   /**
    * Возвращает список браузеров
    *
    * @param array $params параметры формируемого списка:
    *    filter ("enabled"|"banned"|"all") - список будет содержать ("незабаненные" - по умолчанию|"забаненные"|"все") браузеры
    * @return array список в формате name => title
    */   
   public function get_browsers_list($params = array()) {
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
      $res = $this->db->get('browsers');      
      $browsers = array();
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
            $browsers[$row->name] = $row->title;
         }
      }
      return $browsers;
   }
   
   /**
   * осуществляет блокироваку/разблокировку браузера
   *
   * @param string $name
   * @param boolean $is_banned
   * @return none
   */   
   public function set_banned_status($name, $is_banned = true) {
      $this->db->where('name', $name)->update('browsers', array('banned' => $is_banned ? 'true' : 'false'));
   } //end set_banned_status
   
   /**
   * осуществляет разблокировку всех браузеров
   *
   * @param none
   * @return none
   */   
   public function clear_banned_status() {
      $this->db->update('browsers',array('banned' => 'false'));
   } //end clear_banned_status
   
} //end class Browsers

?>