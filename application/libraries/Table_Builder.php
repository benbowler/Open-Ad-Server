<?php  // -*- coding: UTF-8 -*-

if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

/**
* Table Builder Library
*
* @package      SmartPPC6 Project
* @copyright    Copyright (c) 2008, OrbitScripts
* @link         http://www.orbitscripts.com
* @author       OrbitScripts Team
* @version      1.0
*/


/**
 * Класс описывающий одну ячейку таблицы
 *
 */
class Table_Cell {

   protected $_content = array(); // Массив с данными, присвоенными ячейке

   protected $_attributes = array(); // Массив в атрибутами, присвоенными ячейке

   protected $_colspan = 1;

   protected $_rowspan = 1;
   //-------------------------------------------------------------------

   /**
    * Конструктор класса
    *
    * @access Public
    * @param None
    * @return None
    */
   public function __construct() {
      $this->clear();
   }   // __construct


   public function colspan() {
   	return $this->_colspan;
   }

   public function setColspan($value) {
   	if (is_int($value) && $value > 0) {
   		$this->_colspan = $value;
   		if ($value > 1) {
   		    $this->add_attribute('colspan',$value);
   		} else {
   		    $this->remove_attribute('colspan');
   		}
   		return true;
   	} else {
   		return false;
   	}
   }

   public function rowspan() {
      return $this->_rowspan;
   }

   public function setRowspan($value) {
      if (is_int($value) && $value > 0) {
         $this->_rowspan = $value;
      if ($value > 1) {
             $this->add_attribute('rowspan',$value);
         } else {
             $this->remove_attribute('rowspan');
         }
         return true;
      } else {
         return false;
      }
   }
   /**
    * HTML-код ячейки
    *
    * Функция возвращает HTML-код ячейки
    *
    * @access Public
    * @param Array $col_attributes - Атрибуты колонки в которой находится ячейка в виде массива 'атрибут' => 'значение'
    * @return String
    */
   public function get_html($col_attributes = null) {
      $cell_html = '';
      $attributes = '';

      $arr_attributes = array();

      foreach ($this->_attributes as $attr_name => $attr_value) {
         $arr_attributes[$attr_name] = str_replace('"','',$attr_value);
      	//$attributes .= $attr_name.'='.$attr_value.' ';
      }
      if (isset($col_attributes)) { //добавление к атрибутам ячейки атрибутов колонки
         foreach ($col_attributes as $attr_name => $attr_value) {
         	if (array_key_exists($attr_name,$arr_attributes)) {
         		$arr_attributes[$attr_name] .= ' '.str_replace('"','',$attr_value);
         	} else {
         		$arr_attributes[$attr_name] = str_replace('"','',$attr_value);
         	}

         	//$attributes .= $attr_name.'='.$attr_value.' ';
         }
      }

      foreach ($arr_attributes as $attr_name => $attr_value) {
         $attributes .= $attr_name.'="'.$attr_value.'" ';
      }

      foreach ($this->_content as $cell_item) {
         switch ($cell_item['type']) {
            case 'link':
               $cell_item_html = '<a';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="link_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['href'])) {
                  $cell_item_html .= ' href="'.$cell_item['content']['href'].'"';
               }
               $cell_item_html .= '>';
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= $cell_item['content']['name'];
               }
               $cell_item_html .= '</a>';
               break;
            case 'image':
               $cell_item_html = '<img';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="img_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['src'])) {
                  $cell_item_html .= ' src="'.$cell_item['content']['src'].'"';
               }
               if (isset($cell_item['content']['onclick'])) {
                  $cell_item_html .= ' onclick="'.$cell_item['content']['onclick'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= $cell_item['content']['name'];
               }
               $cell_item_html .= '/>';
               break;
            case 'checkbox':
               $cell_item_html = '<input type="checkbox"';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="checkbox_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['value'])) {
                  $cell_item_html .= ' value="'.$cell_item['content']['value'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= ' name="'.$cell_item['content']['name'].'"';
               }
               $cell_item_html .= '>';
               break;
            case 'input':
               $cell_item_html = '<input type="text"';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="text_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['value'])) {
                  $cell_item_html .= ' value="'.$cell_item['content']['value'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= ' name="'.$cell_item['content']['name'].'"';
               }
               $cell_item_html .= '>';
               break;
            case 'button':
               $cell_item_html = '<input type="button"';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="button_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['value'])) {
                  $cell_item_html .= ' value="'.$cell_item['content']['value'].'"';
               }
               $cell_item_html .= '>';
               break;
            case 'textarea':
               $cell_item_html = '<textarea';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="textarea_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= ' name="'.$cell_item['content']['name'].'"';
               }
               $cell_item_html .= '>';
               if (isset($cell_item['content']['value'])) {
                  $cell_item_html .= $cell_item['content']['value'];
               }
               $cell_item_html .= '</textarea>';
               break;
            case 'selectbox':
               $cell_item_html = '<select';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="select_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= ' name="'.$cell_item['content']['name'].'"';
               }

               $cell_item_html .= '>';
               if (isset($cell_item['content']['options'])) {
                  foreach ($cell_item['content']['options'] as $value => $title) {
                     $cell_item_html .= '<option value="'.$value.'"';
                     if (isset($cell_item['content']['selected']) && ($value == $cell_item['content']['selected'])) {
                        $cell_item_html .= ' selected';
                     }
                     $cell_item_html .= '>'.$title.'</option>';
                  }
               }
               $cell_item_html .= '</select>';
               break;
            case 'radiobutton':
               $cell_item_html = '<input type="radio"';
               if (isset($cell_item['content']['extra'])) {
                  $cell_item_html .= ' '.$cell_item['content']['extra'];
               }
               if (isset($cell_item['content']['cssclass'])) {
                  $cell_item_html .= ' class="radio_'.$cell_item['content']['cssclass'].'"';
               }
               if (isset($cell_item['content']['value'])) {
                  $cell_item_html .= ' value="'.$cell_item['content']['value'].'"';
               }
               if (isset($cell_item['content']['name'])) {
                  $cell_item_html .= ' name="'.$cell_item['content']['name'].'"';
               }
               $cell_item_html .= '>';
               break;
            default:
                  $cell_item_html = $cell_item['content'];
         }
         $cell_html .= $cell_item['separator'].$cell_item_html;
      }
      if ('' != $attributes) {
         $attributes = ' '.$attributes;
      }
      return '<td'.$attributes.'>'.$cell_html.'</td>';
   }   // get_html()

   //-------------------------------------------------------------------

   /**
    * Задать содержимое ячейки таблицы
    *
    * Функция присваивает ячейке данные. Если данные уже были присвоенны,
    * то они будут удалены и заменены новым значением.
    *
    * @param String $content - Содержимое ячейки
    * @param String $type - Тип добавляемых данных (link,checkbox,input,textarea,selectbox,radiobutton,button,text)
    * @return None
    */
   public function set_content($content, $type = '') {
         $this->_content = array(array(
                           'content' => $content,
                           'type' => $type,
                           'separator' => ''
                                       )
                                );
   }   // set_content

   //-------------------------------------------------------------------

   /**
    * Добавить содержимое к ячейке таблицы
    *
    * Функция добавляет данные в ячейку. Если данные уже были добавлены,
    * то при выводе они добавятся через разделитель $separator
    *
    * @param String $content - Содержимое ячейки
    * @param String $type - Тип добавляемых данных (link,checkbox,input,textarea,selectbox,radiobutton,button,text)
    * @param String $separator - Разделитель с существующими данными
    * @return None
    */
   public function add_content($content, $type = '', $separator = '') {
         $this->_content[] = array(
                           'content' => $content,
                           'type' => $type,
                           'separator' => $separator
                                          );
   }   // add_content

   //-------------------------------------------------------------------

   /**
    * Добавить атрибут ячейке
    *
    * Функция добавляет атрибут для ячейки.
    *
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function add_attribute($attribute_name, $value) {
      if (!empty($attribute_name) && ('' != $value)) {
         $this->_attributes[$attribute_name] = $value;
         return true;
      }
      return false;
   }  // add_attribute

   //-------------------------------------------------------------------

   /**
    * Удалить заданное значение из атрибута ячейки
    *
    *
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута, которое нужно удалить
    * @return Bool - true | false
    */
   public function remove_attribute_value($attribute_name, $value) {
      if (!empty($attribute_name) && ('' != $value)) {
      	//var_dump( $this->_attributes);
         $this->_attributes[$attribute_name] = str_replace($value,'',$this->_attributes[$attribute_name]);
         return true;
      }
      return false;
   }  // remove_attribute_value

   //-------------------------------------------------------------------

   /**
    * Удалить атрибут ячейки
    *
    * Функция удаляет атрибут у ячейки.
    *
    * @param String $attribute_name - Название атрибута
    * @return Bool - true | false
    */
   public function remove_attribute($attribute_name) {
      if (!empty($attribute_name)) {
         unset($this->_attributes[$attribute_name]);
         return true;
      }
      return false;
   }   // remove_attribute

   //-------------------------------------------------------------------

   /**
    * Очистить все атрибуты ячейки
    *
    * Функция очищает массив атроибутов ячейки
    *
    * @access Public
    * @param None
    * @return None
    */
   public function clear_attributes() {
      $this->_attributes = array();
   }   // clear_attributes

   /**
    * Добавить атрибуты ячейке
    *
    * Функция добавляет массив с атрибутами для ячейки.
    * массив должен иметь формат:
    *  [название атрибута] => значение атрибута
    *
    * @access Public
    * @param Array $attributes - массив атрибутов
    * @return Bool - true | false
    */
   public function add_attributes($attributes) {
      if (is_array($attributes)) {
         foreach ($attributes as $key => $value) {
            if (!$this->add_attribute($key, $value)) {
               return false;
            }
         }
         return true;
      }
      return false;
   }   // add_attributes

   //-------------------------------------------------------------------

   /**
    * Функция устанавливает значения по умолчанию для ячейки
    *
    * @access Public
    * @return Bool - true | false
    */
   public function clear() {
      $this->_attributes = array();
      $this->_content = array();
      return true;
   }   // clear

   /**
    * Получение контента в виде строки
    *
    * @return string
    */
   public function get_content_value() {
      $content = '';
      foreach ($this->_content as $value) {
         if (!is_array($value['content'])) {
            $content .= $value['content'] . $value['separator'];
         }
      }
      return $content;
   }

}   // Table_Cell

   //-------------------------------------------------------------------

class Table_Builder {

   private $rows_count = 0; // Колличество строк
   private $cols_count = 0; // Колличество столбцов

   protected $table;          // префикс таблицы
   protected $table_id;          // идентификатор таблицы (по-умолчанию совпадает с именем)

   public $sort_field;     // имя поля, по которому осуществляется сортировка
   public $sort_direction; // направление сортировки
   public $insert_empty_cells; //добавлять пустые ячейки (контент которых не задан) в тело таблицы

   protected $_cells = array(); // Массив ячеек таблицы
   protected $_attributes = array(); // Массив в атрибутами таблицы
   protected $_row_attributes = array(); //Массив с атрибутами строк таблицы
   protected $_col_attributes = array(); //Массив с атрибутами колонок таблицы

   protected $_use_select_columns = false; // Использовать ли выбор столбцов при отображении
   protected $_invariable_columns = array(); // Список неотключаемых столбцов

   protected $sufix = '';


   //-------------------------------------------------------------------

   /**
    * Конструктор класса
    */
   public function __construct() {
      $this->clear();
   }   // __construct

   //-------------------------------------------------------------------

   /**
    * Функция устанавливает значения по умолчанию для таблицы
    *
    * @access Public
    * @return Bool - true | false
    */
   public function clear() {
      $this->rows_count = 0;
      $this->cols_count = 0;
      $this->_cells = array();
      $this->_attributes = array();
      $this->_row_attributes = array();
      $this->_col_attributes = array();
      $this->sort_field = '';
      $this->sort_direction  = '';
      $this->table = '';
      $this->table_id = '';
      $this->insert_empty_cells = true;
      return true;
   }   // clear

   // --------------------------------------------------------------------

   /**
    * Функция получения колличества столбцов в таблице
    *
    * @return int - Колличество столбцов
    */
   public function get_col_count() {
      return $this->cols_count;
   }   // get_col_count

   // --------------------------------------------------------------------

   /**
    * Функция получения колличества строк в таблице
    *
    * @return int - Колличество строк
    */
   public function get_row_count() {
      return $this->rows_count;
   }   // get_row_count

   /**
    * Получить объект-ячейку таблицы
    *
    * Функция возвращает объект-ячейку таблицы
    *
    * @access Public
    * @param Integer $row - номер строки в таблице
    * @param Integer $column - номер строки в таблице
    * @return Table_Cell | null
    */
   public function cell($row,$column)
   {
      if (isset($this->_cells[$row][$column])) {
         return $this->_cells[$row][$column];
      }
      else {
         return null;
      }
   }

   /**
    * Добавить строки таблицы на основе данных двумерного массива
    *
    * Функция заполняет таблицу данными из двумерного массива
    *
    * @access Public
    * @param Array $table_data - Данные для отобрадежения в виде
    * @return bool - true | false
    */
   public function add_from_array($table_data)
   {
      if (is_array($table_data)) {
         foreach ($table_data as $row) {
            if (!$this->add_row($row)) {
               return false;
            }
         }
         return true;
      }
      else {
         return false;
      }
   } // add_from_array

   /**
    * Добавить строку в таблицу
    *
    * Функция добавляет строку в таблицу
    *
    * @access Public
    * @param Array $row_data - Данные для отображения в строке
    * @param Mixed $type - Тип данных (button,checkbox,...)
    * @return Bool - true | false
    */
   public function add_row($row_data, $type = '')
   {
      $row_index = $this->rows_count;
      $row_content = array();
      if (is_array($row_data)) {
         foreach ($row_data as $cell_value) {
            $row_content[] = array('content' => $cell_value, 'type' => $type);
         }
         if ($this->set_row_content($row_index,$row_content)) {
            return true;
         }
         else {
            return true;
         }
      }
      else {
         return false;
      }
   }

   /**
    * Удалить строку из таблицы
    *
    * Функция удаляет строку из таблицы
    *
    * @access Public
    * @param Integer $row - Номер удаляемой строки
    * @return Bool - true | false
    */
   public function del_row($row)
   {
      if(is_int($row) && ($row >= 0) && ($row < $this->rows_count)){
         unset($this->_cells[$row]);
         unset($this->_row_attributes[$row]);
         $this->_cells = array_values($this->_cells);
         $this->_row_attributes = array_values($this->_row_attributes);
         $this->rows_count--;
         return true;
      }
      else {
         return false;
      }
   } // del_row

/**
    * Добавить колонку в таблицу
    *
    * Функция добавляет колонку в таблицу
    *
    * @access Public
    * @param Array $column_data - Данные для отображения в колонке
    * @param Mixed $type - Тип данных (button,checkbox,...)
    * @return Bool - true | false
    */
   public function add_column($column_data, $type = '')
   {
      $column_index = $this->cols_count;
      $column_content = array();
      if (is_array($column_data)) {
         foreach ($column_data as $cell_value) {
            $column_content[] = array('content' => $cell_value, 'type' => $type);
         }
         if ($this->set_column_content($column_index,$column_content)) {
            return true;
         }
         else {
            return true;
         }
      }
      else {
         return false;
      }
   }

   /**
    * Удалить колонку из таблицы
    *
    * Функция удаляет колонку из таблицы
    *
    * @access Public
    * @param Integer $col - Номер удаляемой колонки
    * @return Bool - true | false
    */
   public function del_column($col)
   {
      if(is_int($col) && ($col >= 0) && ($col < $this->cols_count)){
         foreach ($this->_cells as $row_index => $row_cells) {
            if (isset($row_cells[$col])) {
               unset($row_cells[$col]);
               $this->_cells[$row_index] = array_values($row_cells);
            }
         }
         unset($this->_col_attributes[$col]);
         $this->_col_attributes = array_values($this->_col_attributes);
         $this->cols_count--;
         return true;
      }
      else {
         return false;
      }
   } // del_row

   /**
    * Добавить атрибут таблице
    *
    * Функция добавляет атрибут таблице.
    *
    * @access Public
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function add_attribute($attribute_name, $value) {
      if (!empty($attribute_name) && ('' != trim($value))) {
         $this->_attributes[$attribute_name] = $value;
         return true;
      }
      return false;
   }   // add_attribute

   /**
    * Удалить атрибут таблицы
    *
    * Функция удаляет атрибут у таблицы.
    *
    * @access Public
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function remove_attribute($attribute_name) {
      if (!empty($attribute_name)) {
         unset($this->_attributes[$attribute_name]);
         return true;
      }
      return false;
   }   // remove_attribute

   /**
    * Удалить заданное значение из атрибута таблицы
    *
    *
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута, которое нужно удалить
    * @return Bool - true | false
    */
   public function remove_attribute_value($attribute_name, $value) {
      if (!empty($attribute_name) && ('' != $value)) {
         $this->_attributes[$attribute_name] = str_replace($value,'',$this->_attributes[$attribute_name]);
         return true;
      }
      return false;
   }  // remove_attribute_value


   /**
    * Очистить все атрибуты таблицы
    *
    * Функция удаляет у таблицы все атрибуты.
    *
    * @access Public
    * @param None
    * @return Bool - true | false
    */
   public function clear_attributes() {
      $this->_attributes = array();
   }   // clear_attributes

   /**
    * Добавить атрибут строке таблицы
    *
    * Функция добавляет атрибут для строки таблицы.
    *
    * @access Public
    * @param Integer $row - Номер строки
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function add_row_attribute($row, $attribute_name, $value) {
      if (!empty($attribute_name) && ('' != trim($value)) && is_int($row) && ($row >=0) && ($row < $this->rows_count)) {
         //$this->_row_attributes[$row][$attribute_name] = $value;
         if (isset($this->_row_attributes[$row][$attribute_name])) {
            $this->_row_attributes[$row][$attribute_name] .= ' '.str_replace('"','',$value);
         } else {
            $this->_row_attributes[$row][$attribute_name] = str_replace('"','',$value);
         }
         return true;
      }
      return false;
   }   // add_row_attribute

   /**
    * Удалить значение атрибута у строки таблицы
    *
    *
    * @access Public
    * @param Integer $row - Номер строки
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение удаляемого атрибута
    * @return Bool - true | false
    */
   public function remove_row_attribute_value($row, $attribute_name,$value) {
      if (!empty($attribute_name) && ('' != trim($value)) && is_int($row) && ($row >=0) && ($row < $this->rows_count)) {
         if (isset($this->_row_attributes[$row][$attribute_name])) {
            $this->_row_attributes[$row][$attribute_name] = str_replace($value,'',$this->_row_attributes[$row][$attribute_name]);
         }
         return true;
      }
      return false;
   }   // remove_row_attribute_value

   /**
    * Удалить атрибут у строки таблицы
    *
    * Функция удаляет атрибут у строки таблицы.
    *
    * @access Public
    * @param Integer $row - Номер строки
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function remove_row_attribute($row, $attribute_name) {
      if (!empty($attribute_name) && isset($this->_row_attributes[$row][$attribute_name])) {
         unset($this->_row_attributes[$row][$attribute_name]);
         return true;
      }
      return false;
   }   // remove_row_attribute

   /**
    * Удалить все атрибуты у строки таблицы
    *
    * Функция удаляет все атрибуты у строки таблицы.
    *
    * @access Public
    * @param Integer $row - Номер строки
    * @return Bool - true | false
    */
   public function clear_row_attributes($row) {
      if (isset($this->_row_attributes[$row])) {
         $this->_row_attributes[$row] = array();
         return true;
      }
      return false;
   }   // clear_row_attributes

   /**
    * Добавить атрибут столбцу таблицы
    *
    * Функция добавляет атрибут для столбца таблицы.
    *
    * @access Public
    * @param Integer $col - Номер столбца
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function add_col_attribute($col, $attribute_name, $value) {
      if (!empty($attribute_name) && ('' != trim($value)) && is_int($col) && ($col >=0) && ($col < $this->cols_count)) {
         //$this->_col_attributes[$col][$attribute_name] = $value;
         if (isset($this->_col_attributes[$col][$attribute_name])) {
            $this->_col_attributes[$col][$attribute_name] .= ' '.$value;
         } else {
            $this->_col_attributes[$col][$attribute_name] = $value;
         }
         return true;
      }
      return false;
   }   // add_col_attribute

   /**
    * Удалить атрибут у столбца таблицы
    *
    * Функция удаляет атрибут у столбца таблицы.
    *
    * @access Public
    * @param Integer $col - Номер столбца
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение атрибута
    * @return Bool - true | false
    */
   public function remove_col_attribute($col, $attribute_name) {
      if (!empty($attribute_name) && isset($this->_col_attributes[$col][$attribute_name])) {
         unset($this->_col_attributes[$col][$attribute_name]);
         return true;
      }
      return false;
   }   // remove_col_attribute

   /**
    * Удалить значение атрибута у колонки таблицы
    *
    *
    * @access Public
    * @param Integer $col - Номер столбца
    * @param String $attribute_name - Название атрибута
    * @param String $value - Значение удаляемого атрибута
    * @return Bool - true | false
    */
   public function remove_col_attribute_value($col, $attribute_name,$value) {
      if (!empty($attribute_name) && ('' != trim($value)) && is_int($col) && ($col >=0) && ($col < $this->cols_count)) {
         if (isset($this->_col_attributes[$col][$attribute_name])) {
            $this->_col_attributes[$col][$attribute_name] = str_replace($value,'',$this->_col_attributes[$col][$attribute_name]);
         }
         return true;
      }
      return false;
   }   // remove_row_attribute_value

   /**
    * Удалить все атрибуты у столбца таблицы
    *
    * Функция удаляет все атрибуты у столбца таблицы.
    *
    * @access Public
    * @param Integer $col - Номер столбца
    * @return Bool - true | false
    */
   public function clear_col_attributes($col) {
      if (isset($this->_col_attributes[$col])) {
         $this->_col_attributes[$col] = array();
         return true;
      }
      return false;
   }   // clear_col_attributes


   /**
    * Функция задает аттрибуты для четной/нечетной строк таблицы
    *
    * @access Public
    * @param String $attribute_name - Название атрибута
    * @param String $odd_value - Значение атрибута для нечетной строки
    * @param String $even_value - Значение атрибута для четной строки
    * @param Int $row_start - Номер строки начиная с которой начинать указание атрибута
    * @param Int $row_end - Номер строки до которой продолжать указание атрибута (если совпадает с $row_start, то будует задан для всех строк начиная с $row_start)
    * @return Bool - true | false
    */
   public function set_row_bands($attribute_name,$odd_value,$even_value,$row_start = 0,$row_end = 0){
      if (is_int($row_start) && is_int($row_end) && ($row_start > -1) && ($row_end < $this->rows_count) &&
          !empty($attribute_name) && ('' != trim($odd_value)) && ('' != trim($even_value))) {
         if (($row_end <= $row_start)) {
             $row_end = $this->rows_count - 1;
         }
         for ($i = $row_start; $i <= $row_end; $i++) {
            if ((($i + 1) % 2) == 1) {
               $this->_row_attributes[$i][$attribute_name] = $odd_value;
            }
            else {
               $this->_row_attributes[$i][$attribute_name] = $even_value;
            }
         }
         return true;
     }
     else {
        return false;
     }
   }

    /**
    * Функция задает аттрибуты для четного/нечетного столбца таблицы
    *
    * @access Public
    * @param String $attribute_name - Название атрибута
    * @param String $odd_value - Значение атрибута для нечетного столбца
    * @param String $even_value - Значение атрибута для четного столбца
    * @param Int $col_start - Номер столбца начиная с которого начинать указание атрибута
    * @param Int $col_end - Номер столбца до которого продолжать указание атрибута (если совпадает с $col_start, то будует задан для всех столбцов начиная с $col_start)
    * @return Bool - true | false
    */
   public function set_col_bands($attribute_name,$odd_value,$even_value,$col_start = 0,$col_end = 0){
      if (is_int($col_start) && is_int($col_end) && ($col_start > -1) && ($col_end < $this->cols_count) &&
          !empty($attribute_name) && ('' != trim($odd_value)) && ('' != trim($even_value))) {
         if (($col_end <= $col_start)) {
             $col_end = $this->cols_count - 1;
         }
         for ($i = $col_start; $i <= $col_end; $i++) {
            if ((($i + 1) % 2) == 1) {
               $this->_col_attributes[$i][$attribute_name] = $odd_value;
            }
            else {
               $this->_col_attributes[$i][$attribute_name] = $even_value;
            }
         }
         return true;
     }
     else {
        return false;
     }
   }
   // --------------------------------------------------------------------

   /**
    * Функция присваивает ячейке данные. Если данные уже были присвоенны,
    * то они будут удалены и заменены новым значением...
    *
    * @param int $col - Номер столбца
    * @param int $row - Номер строки
    * @param string $content - Содержимое ячейки
    * @param string $type - Тип добавляемых данных (link,checkbox,input,textarea,selectbox,radiobutton,button,text)
    * @return bool - true | false
    */
   public function set_cell_content($row, $col, $content, $type = '') {
      if (is_int($row) && is_int($col)) {
         if (!isset($this->_cells[$row][$col])) {
            if ($row > $this->rows_count - 1) {
               for ($i = $this->rows_count; $i <= $row; $i++) {
                  $this->_row_attributes[] = array();
               }
               $this->rows_count = $row + 1;
            }
            if ($col > $this->cols_count - 1) {
               for ($i = $this->cols_count; $i <= $col; $i++) {
                  $this->_col_attributes[] = array();
               }
               $this->cols_count = $col + 1;
            }
            $this->_cells[$row][$col] = new Table_Cell();
         }
         $this->_cells[$row][$col]->set_content($content, $type);
         return true;
      }
      else {
         return false;
      }
   }   // set_cell_content

   /**
    * Функция добавляет данные к данным в ячейке. Если ячейка не существует, то она будет создана.
    *
    * @param int $col - Номер столбца
    * @param int $row - Номер строки
    * @param string $content - Содержимое ячейки
    * @param string $type - Тип добавляемых данных (link,checkbox,input,textarea,selectbox,radiobutton,button,text)
    * @return bool - true | false
    */
   public function add_cell_content($row, $col, $content, $type = '', $separator = '') {
      if (is_int($row) && is_int($col)) {
         if (!isset($this->_cells[$row][$col])) {
            if ($row > $this->rows_count - 1) {
               for ($i = $this->rows_count; $i <= $row; $i++) {
                  $this->_row_attributes[] = array();
               }
               $this->rows_count = $row + 1;
            }
            if ($col > $this->cols_count - 1) {
               for ($i = $this->cols_count; $i <= $col; $i++) {
                  $this->_col_attributes[] = array();
               }
               $this->cols_count = $col + 1;
            }
            $this->_cells[$row][$col] = new Table_Cell();
         }
         $this->_cells[$row][$col]->add_content($content, $type, $separator);
         return true;
      }
      else {
         return false;
      }
   }   // add_cell_content


   // --------------------------------------------------------------------

   /**
    * Функция присваивает строке таблицы данные. Если данные уже были присвоенны,
    * то они будут удалены и заменены новым значением.
    *
    * @param int $row - Номер строки
    * @param array $contents - Массив данных для добавления. Формат массива:
    *          array (
    *                   0 => array(
    *                          'content' => '',
    *                          'type' => ''
    *                             ),
    *                   1 => array (
    *                            ...
    *                              )
    *                 )
    * @return bool - true | false
    */
   public function set_row_content($row, $contents) {
      if (is_array($contents)) {
         foreach ($contents as $key => $values) {
            if (!isset($values['content']) && !isset($values['type'])) {
               return false;
            } else {
               if (!$this->set_cell_content($row, $key, $values['content'], $values['type'])) {
                  return false;
               }
            }
         }
         return true;
      }
      else {
         return false;
      }
   }   // set_row_content

   /**
    * Функция присваивает столбцу таблицы данные. Если данные уже были присвоенны,
    * то они будут удалены и заменены новым значением.
    *
    * @param int $col - Номер столбца
    * @param array $contents - Массив данных для добавления. Формат массива:
    *          array (
    *                   0 => array(
    *                          'content' => '',
    *                          'type' => ''
    *                             ),
    *                   1 => array (
    *                            ...
    *                              )
    *                 )
    * @return bool - true | false
    */
   public function set_column_content($col, $contents) {
      if (is_array($contents)) {
         foreach ($contents as $key => $values) {
            if (!isset($values['content']) && !isset($values['type'])) {
               return false;
            } else {
               if (!$this->set_cell_content($key, $col, $values['content'], $values['type'])) {
                  return false;
               }
            }
         }
         return true;
      }
      else {
         return false;
      }
   }   // set_column_content

   /**
    * Функция осуществляет генерацию HTML кода таблицы.
    *
    * @return string
    */
   public function get_html() {
      $table_attributes = '';
      $dummy_cell = new Table_Cell();
      //Добавление к аттрибутам таблицы 'cellspacing' и 'cellpadding' с нулевыми значениями
      if (!isset($this->_attributes['cellspacing'])) {
      	$this->_attributes['cellspacing'] = '0';
      }
      if (!isset($this->_attributes['cellpadding'])) {
         $this->_attributes['cellpadding'] = '0';
      }
      
      foreach ($this->_attributes as $attr_name => $attr_value) {
      	$attr_value = str_replace('"','',$attr_value);
         $table_attributes .= $attr_name.'="'.$attr_value.'" ';
      }
      if ('' != $table_attributes) {
          $table_attributes = " ".$table_attributes;
      }

      // Генерируем код для выбора колонок
      $columns = $this->_get_columns();

      $table_code = "<table$table_attributes>\n";
      $table_code .= "<colgroup>"; 
      for($column_index = 0; $column_index < $this->cols_count; $column_index++) {
      	 $table_code .= "<col>";
      }
      $table_code .= "</colgroup>";

      for ($row_index = 0; $row_index < $this->rows_count; $row_index++) {
         $row_attributes = '';

         foreach ($this->_row_attributes[$row_index] as $attr_name => $attr_value) {
            $row_attributes .= $attr_name.'="'.$attr_value.'" ';
         }
         if ('' != $row_attributes) {
            $row_attributes = " ".$row_attributes;
         }

         $table_code .= "<tr".$row_attributes.">";

         $skip_column = 0;

         for ($column_index = 0; $column_index < $this->cols_count; $column_index++) {
            if ($skip_column > 0) {
                  $skip_column--;
                  continue;
            }

         	if ($this->_use_select_columns && isset($columns[$column_index]) && !$columns[$column_index]['checked']) {

         		$skip_column = $this->_cells[0][$column_index]->colspan() - 1;
               continue;
            }

            if (isset($this->_cells[$row_index][$column_index])) {
               $table_code .= $this->_cells[$row_index][$column_index]->get_html($this->_col_attributes[$column_index]);
            }
            else {
               if ($this->insert_empty_cells) {
                  $table_code .= $dummy_cell->get_html($this->_col_attributes[$column_index]);
               }
            }
         }

         $table_code .= "</tr>\n";
      }
      $table_code .= "</table>";
      return $table_code;
   }   // get_html

   /**
   * задает заголовок столбца с сортировкой
   *
   * @param int $column номер столбца
   * @param string $field название поля
   * @param string $name заголовок столбца
   * @param string $default_direction сортировка столбца по умолчанию
   * @param integer $row_index номер строки, в которой располагается ячейка, отвечающая за сортировку
   * @return ничего не возвращает
   */
   public function sorted_column($column, $field, $name, $default_direction, $row_index = 0) {
      $this->set_cell_content($row_index, $column, __($name));
      $this->cell($row_index, $column)->add_attribute('onclick',
         "\"return set_sort('$this->table_id', '$field', '$default_direction', '$this->sufix')\"");
      if ($field == $this->sort_field) {
         $this->cell($row_index, $column)->add_attribute('class', $this->sort_direction);
      }
   } //end sorted_column

   /**
   * задает параметры сортировки таблицы
   *
   * @param string $table имя таблицы
   * @param string $sort_field имя поля для текущей сортировки
   * @param string $sort_direction направление текущей сортировки
   * @return ничего не возвращает
   */
   public function define_sort($table, $sort_field, $sort_direction) {
   	$this->table = $table;
   	$this->sort_field = $sort_field;
   	$this->sort_direction = $sort_direction;
   } //end sort

   /**
   * считывает из базы переменные сортировки
   *
   * @param string $table имя сортируемой таблицы
   * @param string $default_sort_field имя поля сортируемого по умолчанию
   * @param string $default_sort_direction направление сортировки по умолчанию
   * @param bool $use_default использовать сортировку по умолчанию (независимо от переменных в БД)
   * @param string $table идентификатор таблицы
   * @return ничего не возвращает
    */
   public function init_sort_vars($table, $default_sort_field, $default_sort_direction, $use_default = FALSE, $table_id = null, $sufix = '') {
   	$this->sufix = $sufix;
      $CI =& get_instance();
      $this->table = $table;
      $this->table_id = is_null($table_id)?$table:$table_id;
      if ($use_default) {
         $this->sort_field = $default_sort_field;
         $this->sort_direction = $default_sort_direction;
      } else {
         $this->sort_field =
            $CI->global_variables->temporary_var($this->table."_sort_field".$sufix, $default_sort_field);

         $this->sort_direction =
            $CI->global_variables->temporary_var($this->table."_sort_direction".$sufix, $default_sort_direction);
      }
        
   } //end init_sort_vars

   /**
   * возвращает HTML-кода таблицы с добавлением скрытых полей для сортировки
   *
   * @return string HTML-код
   */
   public function get_sort_html() {
   	return $this->get_html().
   	  "<input type='hidden' id='{$this->table_id}_sort_field{$this->sufix}' name='{$this->table}_sort_field{$this->sufix}' value='$this->sort_field'>
         <input type='hidden' id='{$this->table_id}_sort_direction{$this->sufix}' name='{$this->table}_sort_direction{$this->sufix}' value='$this->sort_direction'>";
   } //end get_sort_html

   /**
   * возвращает массив с описанием полей формы сортировки таблицы
   *
   * @param string $view имя файла с отображением для таблицы
   * @param array $vars опциональный массив с переменными для отображения таблицы
   * @return array массив с описанием полей формы сортировки таблицы
   */
   public function get_sort_form($view, $vars = FALSE) {
      $form = array(
         'name' => $this->table.'_form',
         'view' => $view,
         'fields' => array(
            $this->table.'_sort_field' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'hidden',
               'default' => $this->sort_field
            ),
            '_sort_direction' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'hidden',
               'default' => $this->sort_direction
            )
         )
      );
      if ($vars) {
         $form['vars'] = $vars;
      }
      return $form;
   } //end get_sort_form

   /**
    * Установка режима выбора колонок
    *
    * @param bool $value
    */
   public function use_select_columns($value = true) {
      $this->_use_select_columns = $value;
   }

   /**
    * Установка неотключаемой колонки
    *
    * @param int $column
    */
   public function add_invariable_column($column) {
      array_push($this->_invariable_columns, $column);
   }

   /**
    * Очистка списка неотключаемых колонок
    *
    */
   public function clean_invariable_columns() {
      $this->_invariable_columns = array();
   }

   /**
    * Установка неотключаемых колонок
    *
    * @param array $columns
    */
   public function set_invariable_columns($columns) {
      $this->clean_invariable_columns();
      if (is_array($columns)) {
         foreach ($columns as $column) {
            $this->add_invariable_column($column);
         }
      }
   }

   /**
    * Получение неотключаемых колонок
    *
    * @return array
    */
   public function get_invariable_columns() {
      return $this->_invariable_columns;
   }

   /**
    * Метод получения html кода меню выбора колонок
    *
    * @return string
    */
   public function get_columns_html() {
      $html = '';
      if ($this->_use_select_columns && 0 < $this->get_row_count()) {
         $obj =& get_instance();
         $data = array(
            'TABLE' => $this->table_id,
            'COLUMNS' => $this->_get_columns()
         );
         $html = $obj->parser->parse('common/columns.html', $data, true);
      }
      return $html;
   }

   /**
    * Метод получения массива колонок
    *
    * @return array
    */
   private function _get_columns() {
      $columns = array();
      $obj =& get_instance();
      $obj->load->helper('form');
      $select_all = false;
      // Берем данные из поста
      $post_columns = $obj->input->post($this->table_id . '_column');
      if (!is_array($post_columns)) {
         $post_columns = array();
      }
      $post_columns = array_keys($post_columns);
      if (!$obj->input->post('hidden_columns_' . $this->table_id)) {
         // Загружаем в первый раз
         $var = isset($obj->temporary[$this->table . '_columns' . $this->sufix]) ? $obj->temporary[$this->table . '_columns' . $this->sufix] : 'all';
         if ('all' == $var) {
            $select_all = true;
         } else {
            $post_columns = explode(',', $var);
         }
      } else {
         // Сохраняем переменные
         $obj->temporary[$this->table . '_columns' . $this->sufix] = implode(',', $post_columns);
      }

      for ($i = 0; $i < $this->get_col_count(); $i++) {
         $cell = $this->cell(0, $i);

         if (null !== $cell) {
            $title = trim(str_replace(array('&nbsp;', '&nbsp'), ' ', $cell->get_content_value()));
            if (empty($title)) {
               continue;
            }
            $disabled = '';
            if (in_array($i, $this->_invariable_columns)) {
               $disabled = 'disabled="disabled"';
            }
            $selected = false;
            if ($select_all || in_array($i, $post_columns) || in_array($i, $this->_invariable_columns)) {
               $selected = true;
            }
            $id = $this->table_id . '_column_' . $i;
            $additional = $disabled . ' id="' . $id . '" rel="' . $i . '"';
            $column = array(
               'checkbox' => form_checkbox($this->table_id . '_column[' . $i . ']', 'on', $selected, $additional),
               'hidden'   => form_hidden($this->table_id . '_column_hold[' . $i . ']', $selected ? '1' : ''),
               'title'    => $title,
               'checked'  => $selected,
               'id'       => $id
            );
            $columns[$i] = $column;
         }
      }
      return $columns;
   }

}