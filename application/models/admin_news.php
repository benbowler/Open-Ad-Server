<?php // -*- coding: UTF-8 -*-

if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'models/object_model.php';

/**
* класс для работы с новостями
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

class Admin_News extends Object_Model {
	
	public function __construct() {
		parent::__construct ();
		$this->_table_name = 'admin_news';
		$this->_id_field_name = 'id_news';
		$this->set_name ( 'title', __('Title') );
		$this->set_name ( 'status', __('Status') );
		$this->set_name ( 'target', __('To') );
		$this->set_name ( 'publication_date', __('Publication Date') );
		$this->set_name ( 'creation_date', __('Creation Date') );
	}
	
	/**
	 * Загрузка объекта-новости из базы данных 
	 *
	 * @return bool true|false результат загрузки объекта из базы
	 */
	protected function _load() {
		$this->_values = array ( );
		$this->_has_modified_data = false;
		
		$query = $this->db->select ('roles.name as target,content,title,UNIX_TIMESTAMP(creation_date) as creation_date,UNIX_TIMESTAMP(publication_date) as publication_date,status' )
		         ->join('roles','roles.id_role = '.$this->_table_name.'.target','left')
		         ->where ( $this->_id_field_name, $this->_id )
		         ->get ( $this->_table_name );
		if ($query->num_rows () > 0) {
			$news_rec = $query->result();
			$news_info = $news_rec [0];
			unset ( $news_rec );
			$this->_values ['content'] ['value'] = $news_info->content;
			$this->_values ['title'] ['value'] = $news_info->title;
			$this->_values ['status'] ['value'] = $news_info->status;
			$this->_values ['target'] ['value'] = $news_info->target;
			$this->_values ['publication_date'] ['value'] = $news_info->publication_date;
			$this->_values ['creation_date'] ['value'] = $news_info->creation_date;
			foreach ( $this->_values as &$vals) {
				$vals ['is_changed'] = false;
			}
			return true;
		} else {
			return false;
		}
	}
	
   /**
   * Получение списка новостей для админа
   *
   * @return array - список новостей в формате 'id => array('date', 'title', 'content')
   */ 
   public function get_admin_news() {
      $max_news = $this->global_variables->get('MaxNewsOnPage');
      
      $res = $this->db->order_by('date', 'DESC')
                      ->limit($max_news)
                      ->get('orbitscripts_admin_news');
      
      $news = array();
      foreach ($res->result() as $row) { 
         $news[$row->id] = array(
            'date' => $row->date,
            'title' => $row->title,
            'content' => $row->description,
            'link' => $row->link       
         );          
      }     
      return $news;
   } //end get_admin_news	
	
} 