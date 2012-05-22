<?php  // -*- coding: UTF-8 -*-

if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'models/object_model.php';

class Font_Model extends Object_Model {
	
	public function __construct()	{
		parent::__construct();
		$this->_table_name = 'fonts';
		$this->_id_field_name = 'id_font';
		log_message('debug', 'Font_Model Class Initialized');
	}
	
   /**
    * Список цветовых схем для forms
    *
    * @return array
    */
   public function get_list() {
   	$this->db->select($this->_id_field_name.',name');
      $this->db->from($this->_table_name);
      $this->db->order_by('name');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
         $result[$row->id_font] = $row->name;
        } 
        return $result;
      } else {
        return array();
      }
   }
	
}