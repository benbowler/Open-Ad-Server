<?php // -*- coding: UTF-8 -*-

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Object Model
*
* @package      SmartPPC6 Project
* @copyright    Copyright (c) 2008, OrbitScripts
* @link         http://www.orbitscripts.com
* @author       OrbitScripts Team
* @version      1.0
* @created 15-Sep-2008 10:02:44
* @created 24-Sep-2007 10:02:44
*/
class Object_Model extends CI_Model {
   /**
    * Internal data and paramenres for this model
    */
   protected $_id = null;                 // the object identifier
   protected $_id_field_name = null;      // the name of the field which contains object identifier
   protected $_table_name = null;         // the name of the table which contains object data
   protected $_use_session = false;       // the session ussage flag
   protected $_has_modified_data = false; // shows that data have been modified
   protected $_values = array();          // Массив данных объекта array('field1' => array('value' => '', 'is_changed' => false),...)
   protected $_name_values = array();     // Массив названий полей данных
   protected $_structuries = array();     // Массив структуры для _table_name

   /**
    * Constructor
    * 
    */
   public function __construct() {
      parent::__construct();
   }

   // --------------------------------------------------------------------

   /**
    * Set object identifier
    *
    * @param mixed        $id - the object identifier
    * @return object
    * 
    * The function invocation may be like as:
    *    1. $somethig = $obj->set_id(100);
    *    2. $somethig = $obj->set_id(array('id_field1' => 100, 'id_field2' => 200));
    *       If you use first argument as array they will be chained together with AND between them.
    */
   public function set_id($id) {
      $this->_values = array();
      $this->_has_modified_data = false;
      $this->_id = $id;
      return $this;
   }

   /**
   * Получение идентификатора объекта
   * 
   * @return mixed идентификатор объекта
   */
   public function get_id() {
      return $this->_id;
   }   // get_id

   // --------------------------------------------------------------------

  
   /**
    * Установка флага использования сессии
    *
    * @param bool $use_session the flag which handles that session will be used for
    *                                  data saving or not. if flag value is TRUE, then data will be saved
    *                                  into session variables, otherwise the data will be saved into database.
    * @return bool true|false флаг успешного изменения значения переменной $this->_use_session 
    */
   public function use_session($use_session = true) {
      if (is_bool($use_session)) {
         $this->_use_session = $use_session;
         return true;
      } else {
         return false;
      }
   }

   protected function before_insert() {
      return true;
   }

   protected function after_insert() {
      return true;
   }

   protected function _insert() {
      return true;
   }
   /**
    * Функция обновления 
    *
    * @return unknown
    */
   protected function _update() {
      return true;
   }
   
   protected function _delete() {
      return true;
   }
   
   protected function _check() {
      return true;
   }
   
   // добавляет объект в таблицу
   public function insert($re_read = false) {
      if(!$this->before_insert()) return false;

      $retval = _insert();
       
      if ($retval) {
         return $this->after_insert();
      }
      else {
         return false;
      }
   }

   protected function before_update() {
      return true;
   }

   protected function after_update() {
      return true;
   }
   
   // Обновляет объект в таблице
   public function update($re_read = false) {
      if(!$this->before_update()) return false;

      $retval = _update();
       
      if ($retval) {
         return $this->after_update();
      }
      else {
         return false;
      }
   }

   protected function before_delete() {
      return true;
   }

   protected function after_delete() {
      return true;
   }

   // Удаляет объект из таблицы
   public function delete() {
      if(!$this->before_delete()) return false;

      $retval = _delete();
       
      if ($retval) {
         return $this->after_delete();
      }
      else {
         return false;
      }
   }

   protected function before_check() {
      return true;
   }

   protected function after_check() {
      return true;
   }

   // Проверяет корректность данных объекта
   public function check() {
       if(!$this->before_check()) return false;

      $retval = _check();
       
      if ($retval) {
         return $this->after_check();
      }
      else {
         return false;
      }
   }

   // Загружает даныые объекта из БД по задданному ID
   protected function _load() {
      return true;
   }
   
   // Срхраняет даныые объекта в БД по задданному ID
   protected function _save() {
      return true;
   }

   public function set_value($name, $value) {
      //Добавить также проверку соответствия типа присваемого значения
      if (isset($this->_values[$name])) {
         if ($this->_values[$name]['value'] != $value) {
            $this->_values[$name]['value'] = $value;
            $this->_values[$name]['is_changed'] = true;
            $this->_is_update = true;
            $this->_has_modified_data = true;
         }
      }
      else {
         throw new Exception('Object_Model: Failed to set value named "' . $name . '"');
      }
   }

   public function get_value($name) {
      if (isset($this->_values[$name])) {
         return $this->_values[$name]['value'];
      }
      throw new Exception('Object_Model: Failed to get value named "' . $name . '"');
   }

   /**
    * Set Value of the Field без вызова исключения в случае ошибки
    * 
    * This function is used to set value (or values) of the field (fields) into internal
    * buffer.
    * 
    * @access public
    * @param string/array - the name of the field or associative array which contains the
    *                       pairs of (fileld_name => value) tuples
    * @param mixed         - the field value. This field is ignored if the first argumnent
    *                      is an array.
    * @param boolean       - if this argument is true, then object data will be written into
    *                      database (or session) immediately
    * 
    *  set_field_value(array('field1' => value1, 'field2' => value2), <ignored>, true);
    *  
    * @return return TRUE on success, and FALSE otherwise
    */
   /*public function set_value($name, $value,$immediate_update = false) {
      try {
         $this->__set($name, $value);
         if ($immediate_update) {
            return $this->update();
         }
         else {
            return true;
         }
      }
      catch (Exception $e) {
         show_error($e->getMessage());
         return false;
      }
   }*/

   /**
    * Get Value of the Field без вызова исключения в случае ошибки
    * 
    * This function is used to get value (or values) of the field (fields) from internal
    * buffer.
    * 
    * @access public
    * @param string/array - the name of the fiel or associative array which contains the
    *                       field names.
    * @param boolean       - if this argument is true, then object data will be read from
    *                      database before values will be return
    * 
    *  set_field_value(array('field1' => value1, 'field2' => value2), <ignored>, true);
    *  
    * @return return value of the field. If the first argument is an array, then this function
    *         will return an array of the pairs of (fileld_name => value) tuples. If error
    *         is occured, then function will return FALSE.
    */
  /* public function get_value($name,$force_load = false) {
      if($force_load) {
         $this->_load();
      }
      try {
         return $this->__get($name);
      }
      catch (Exception $e) {
         show_error($e->getMessage());
         return null;
      }
   }*/

   //---------------------------------------------------------------------

   /**
    * This function is used to set verbal name of the field.
    * 
    * @param string $field  the internal name of the field
    * @param string $name   the verbal name of the field
    */
   public function set_name($field, $name) {
      $this->_name_values[$field] = $name;
   }   // set_name

   //---------------------------------------------------------------------

   /**
    * This function is used to get verbal name of the field
    * 
    * @param string $field    the internal name of the field
    */
   public function get_name($field) {
      return $this->_name_values[$field];
   }   // get_name
   
   /**
    * Получение массива всех полей объекта с их значениями
    *
    * @return array
    */
   public function get_values() {
      $values = array();
      foreach ($this->_values as $name => $value) {  
         $values[$name] = $value['value'];
      }
      return $values;
   } // end get_values

   //---------------------------------------------------------------------

} // class Object END
