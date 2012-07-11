<?php  // -*- coding: UTF-8 -*-

if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'models/object_model.php';

class Code_Color_Scheme_Model extends Object_Model {
	
	public function __construct()	{
		parent::__construct();
		$this->_table_name = 'color_schemes';
		$this->_id_field_name = 'id_color_scheme';
	}
	
	/**
	 * Список цветовых схем для forms (c палитрой по-умолчанию)
	 *
	 * @return array
	 */
	public function get_list($params) {
		$this->db->select($this->_id_field_name.',name')
		         ->from($this->_table_name)
		         ->order_by('name');
		if (isset($params['id_entity'])) {
		          $this->db->where('id_entity_publisher ',$params['id_entity'])
		                   ->or_where('id_entity_publisher',0);
		}
		
	   $query = $this->db->get();
      if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
        	$result[$row->id_color_scheme] = $row->name;
        }
        	 
        return $result;
      } else {
        return array();
      }
	}
	
	/**
	 * Получение идентификатора владельца цветовой схемы
	 *
	 * @param int $id
	 * @return int|null
	 */
	public function get_owner($id) {
		$query = $this->db->select('id_entity_publisher')
				         ->from($this->_table_name)
				         ->where($this->_id_field_name,$id)
				         ->get();
	   if ($query->num_rows() > 0) {
	   	return $query->row()->id_entity_publisher;
	   } else {
	   	return null;
	   }
	}
	
	/**
    * Параметры цветовой схемы
    *
    * @return array
    */
	public function get_info($id) {
	  $fields = array($this->_table_name.'.name',
	                  'border_color',
	                  'background_color',
	                  'title_color',
	                  'title_id_font',
	                  'title_font.name as title_font_name',
	                  'title_font_size',
	                  'title_font_style',
	                  'title_font_weight',
	                  'text_color',
                     'text_id_font',
                     'text_font.name as text_font_name',
                     'text_font_size',
                     'text_font_style',
                     'text_font_weight',
	                  'url_color',
                     'url_id_font',
                     'url_font.name as url_font_name',
                     'url_font_size',
                     'url_font_style',
                     'url_font_weight',
	                  'IF(id_entity_publisher,false,true) as is_system'
	  );
	  $this->db->select($fields)
              ->from($this->_table_name)
              ->join('fonts as title_font','title_font.id_font = '.$this->_table_name.'.title_id_font','left')
              ->join('fonts as text_font','text_font.id_font = '.$this->_table_name.'.text_id_font','left')
              ->join('fonts as url_font','url_font.id_font = '.$this->_table_name.'.url_id_font','left')
              ->where($this->_id_field_name, $id);
     $query = $this->db->get();
     if ($query->num_rows() > 0) {
        return $query->row();
     } else {
        return null;
     }
	}
	
	/**
	 * Создание цветовой схемы
	 *
	 * @param array $params параметры цветовой схемы
	 */
	public function create($params) {
		$this->db->insert($this->_table_name, $params);
	}
	
   /**
    * Изменение цветовой схемы 
    * 
    * Редактирование цветовой схемы по-умолчанию блокируется
    *
    * @param int $id идентификатор цветовой схемы
    * @param array $params параметры цветовой схемы
    * @param int $id_entity идентификатор сущности, изменяющей схему 
    */
   public function update($id, $params, $id_entity) {
   	
      if ($id_entity == 1) {
         $this->db->where($this->_id_field_name,$id);
      } 
      else {
         $this->db->where($this->_id_field_name,$id)
                  ->where('id_entity_publisher',$id_entity);
      }
    
	  $this->db->update($this->_table_name, $params);
   }
   
   /**
    * Удаление цветовой схемы
    *
    * @param int $id идентификатор цветовой схемы
    * @param int $id_entity идентификатор сущности, удаляющей схему
    * @return none
    */
   public function delete($id, $id_entity) {
      $this->db->delete($this->_table_name, array('id_entity_publisher' => $id_entity,
                                                  $this->_id_field_name => $id));
   }
	
} 