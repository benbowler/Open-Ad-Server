<?php // -*- coding: UTF-8 -*-


if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
   exit ( 'No direct script access allowed' );

require_once APPPATH . 'models/object_model.php';

/**
* класс для настройки размерностей (канала/объявления)
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

class Dimension extends Object_Model {
   
   public function __construct() {
      parent::__construct ();
      $this->_table_name = 'dimensions';
      $this->_id_field_name = 'id_dimension';
   }

   /**
    * Получение списка размерностей сгрупированного по типам (для options)
    *
    * @param array $params массив требуемых параметров размерностей
    * @return array|null - массив, содержащий список размерностей и их параметры
    */
   public function get_list($params,$postfix = true) {
      if (array_key_exists('fields', $params)) {
         $this->db->select ( $params['fields'] );
      }

      $this->db->from ( $this->_table_name );
      
      /*
       * Remove this when new dimensions will be approved 
       */
      
      $this->db->where( 'id_dimension not in (16,17)' );
      
      /*
       * end
       */
      
      $this->db->order_by ( 'orientation ASC, name');
      
      $query = $this->db->get ();
      
      if ($query->num_rows () > 0) {
         $result = array();
         if (isset($params['all'])) {
            $result[''] = __('All sizes');
         }
         foreach ($query->result() as $row) {
            $result['{@dim_'.$row->orientation.'@}'][$row->id_dimension] = $row->name .
                           ' ('.$row->width .
                                          ' × '. $row->height . ')';
             
         }
         return $result;
      } else {
         return null; 
      }
   }
   
   
   /**
    * Получение списка всех размерностей
    *
    * @return array - массив, содержащий список размерностей и их параметры
    */
   public function get_list_all() {
      
      $this->db->select ();

      $this->db->from ( $this->_table_name );
      
      $this->db->order_by ( 'orientation ASC, name');
      
      $query = $this->db->get ();
      
      if ($query->num_rows () > 0) {
         return $query->result_array();
      } else {
         return array(); 
      }
   }
   
  /**
    * Получение списка размерностей в соответсвии с типом рекламы
    *
    * @return array - массив, содержащий список размерностей и их параметры
    * @author Semerenko
    */
   public function get_list_specific($places) {
      
      $this->db->select();
      $this->db->from ( $this->_table_name );
      
      /**
       * Создания условия дял выборки только для нужных типов рекламы
       */
      
              
      $this->db->order_by ( 'width ASC, name');
      
      $query = $this->db->get ();
      
      $all_dims = $query->result_array();
      
      return $all_dims;
   }      
   
   
   /**
    * Получение списка размерностей в соответсвии с типом рекламы
    *
    * @return array - массив, содержащий список размерностей и их параметры
    * @author Semerenko
    */
   public function get_list_specific_more($places) {
      
      $this->db->select();
      $this->db->from ( $this->_table_name );
      
      $this->db->order_by ( 'width ASC, name');
      
      $query = $this->db->get ();
      
      $all_dims = $query->result_array();

      // get all places
           
      $this->load->library('Plugins', array(
       'path' => array('advertiser', 'create_campaign_step_select_sites'),
       'interface' => 'Sppc_Advertiser_CreateCampaignStep_SelectSites_Interface'));
      $all_places = array_merge(array('sites'),$this->plugins->run('getPlace',$this));
      $all_places_all = array();
      
      
      // get only selected
      $all_places = $places;
      
      $all_places_all['sites'] = array(
         'place' => 'sites', 
         'color' => 1, 
         'name' => 'Sites');
      
      $res = array();
      foreach($all_dims as $k => $dim){
         foreach($all_places_all as $place){
            $res[$k][$place['place']] = 1;
         }
      }
     
      // Нужно убрать пустые строки (если для всех плэйсов текущих - 
      // не поддерживается размер изображения)
      foreach($res as $k => $v){
         $remove = true;
         foreach($v as $flag){
            $remove = ($flag == 1)?false:true;
            if(!$remove){
               break;
            }
         }
         if($remove){
            unset($res[$k]);
         }
      }
      
      return array('dims' => $all_dims, 'places' => $res,'allplaces' => $all_places_all);
   }   

   public function check_dimension_channels($id_dimension) {
      $this->db->select('COUNT(*) AS count');

      $this->db->from('channels');
      
      $this->db->where('id_dimension', $id_dimension);
      $this->db->where('status != "deleted"');
      
      $query = $this->db->get();
      
      if ($query->num_rows () > 0) {
         $row = $query->row();
         return $row->count > 0 ? true : false;
      } else {
         return 0; 
      }
   }
   
   /**
    * Получение информации о размере (name, max_ad_slots, orientation, width, height)
    *
    * @param int $id идентификатор формата
    * @return object|null
    */
   public function get_info($id = -1) {
      $this->db->select('name, max_ad_slots, orientation, width, height, rows_count, columns_count');
      $this->db->from($this->_table_name);
      $this->db->where($this->_id_field_name, $id);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return null;
      }
   }
   
   /**
    * Будет ли показываться хоть какая-то реклама в этом месте
    *
    * @param unknown_type $place
    * @param unknown_type $ads
    */
   public function checkPlaceSuitability($place,$ads){

      foreach($ads as $ad){
         // если есть хоть одно текстовое, то всё ОК
         if($ad->ad_type == 'text'){
            return TRUE;
         }
         $info = $this->get_info($ad->id_dimension);
         return TRUE;
      }
      return FALSE;
   }
   
} 