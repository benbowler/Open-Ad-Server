<?php
if (! defined ( 'BASEPATH' ))
   exit ( 'No direct script access allowed' );

/**
 * Модель для определения типов кампаний, доступных для создания в системе
 * 
 * @author Немцев Андрей
 * @project SmartPPC6
 * @version 1.0.0
 */

class Campaign_Types_Model extends CI_Model {
   /**
    * конструктор класса
    *
    * @return ничего не возвращает
    */
   public function __construct() {
      parent::__construct ();
      $this->_table_name = 'campaign_types';
      $this->_id_field_name = 'campaign_type';
   } //end __construct()

/**
    * Получение списка типов кампаний
    *
    * @return array|null - массив, содержащий список типов кампаний и их параметры
    */
   public function get_list() {
      $this->db->select('campaign_type, campaign_name,  description');
      $this->db->from($this->_table_name);
      $query = $this->db->get();
      
      if ($query->num_rows () > 0) {
         return $query->result_array ();
      } else {
         return null; 
      }
   }
   
   
   
   /**
    * Получение информации о программе (campaign_name, description)
    *
    * @param string $campaign_type тип программы
    * @return object|null
    */
   public function get_info($campaign_type) {
      $this->db->select('campaign_name, description');
      $this->db->from($this->_table_name);
      $this->db->where($this->_id_field_name, $campaign_type);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return null;
      }
   }
}

