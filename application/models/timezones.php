<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* класс для работы с временными зонами
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Timezones extends CI_Model {
 
   public function __construct() {
      parent::__construct();
   }

   /**
   * возвращает список существующих временных зон
   *
   * @return array список в формате id_timezone => name
   */   
   public function get_list($params) {
          $this->db->select('id_timezone, name');
      $this->db->order_by('position');
      $res = $this->db->get('timezones');
      if($res->num_rows()) {
         foreach ($res->result() as $row) {
            $timezones[$row->id_timezone] = $row->name;
         }
      }
      if ($params['add_select']) {
         $timezones = array('' => __('select timezone')) + $timezones;
      }
      return $timezones;
   }
 
}

?>