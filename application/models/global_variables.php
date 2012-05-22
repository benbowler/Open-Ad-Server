<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Модель для работы с глобальными переменными
* достпуные методы:
* get($name, $id_entity)         получение глобальной переменной для заданного пользователя
* set($name, $value, $id_entity) запись нового значения глобальной переменной для заданного пользователя
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Global_variables extends CI_Model {

   /**
    * Вводим кеширование
    *
    * @author Anton Potekhin
    * @var array
    */
   protected $_variablesCache = array();
   
   protected $_variablesBigCache = array();
   
   /**
   * консруктор класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
   	parent::__construct();
   	$this->load->helper("fields");
   	$this->kill_expired();
   }
    
   /**
   * получение глобальной переменной для заданного пользователя
   *
   * @param string $name имя переменной
   * @param int $id_entity код пользователя владельца переменной,по умолчанию - 0 (системная переменная)
   * @param string $default значение переменной по умолчанию
   * @return string значение переменной или NULL если переменная не найдена
   */
   public function get($name, $id_entity=0, $default=NULL) {
      if (isset($this->_variablesCache[$id_entity][$name])) {
         return $this->_variablesCache[$id_entity][$name];
      }
      $this->db->select('value, type');
      $this->db->from('settings');
      $this->db->join('setting_fields', 'settings.name=setting_fields.name', 'left');
      $this->db->where(array('settings.name' => $name, 'id_entity' => $id_entity));
      $res = $this->db->get();
      if($res->num_rows()) {
         $row = $res->row();
         $this->_variablesCache[$id_entity][$name] = type_cast($row->value, $row->type);          
      } else {      
         $this->_variablesCache[$id_entity][$name] = $default;
      }
      return $this->_variablesCache[$id_entity][$name];    
   }

   /**
   * запись нового значения глобальной переменной для заданного пользователя 
   *
   * @param string $name имя глобальной переменной
   * @param string $value новое значение глобальной переменной
   * @param int $id_entity код пользователя владельца переменной,по умолчанию - 0 (системная переменная)
   * @return ничего не возвращает
   */   
   public function set($name, $value, $id_entity=0, $expired=FALSE) {
      if (is_null($id_entity)) return;
      $this->load->helper("fields");
      $expired_date = "Null";
      if ($expired) {
         $expired_date = timestamp_to_mysql(time() + 60*30);
      }
      if (is_null($value)) {
         $this->db->where(array('name' => $name, 'id_entity' => $id_entity))->delete('settings');
         if (isset($this->_variablesCache[$id_entity][$name])) {
            unset($this->_variablesCache[$id_entity][$name]);      
         }
      } else {
   	$this->db->query("
   	   INSERT INTO settings
   	   SET name={$this->db->escape($name)}, 
            value={$this->db->escape($value)},
            expired=$expired_date,
            id_entity=$id_entity
   	   ON DUPLICATE KEY
            UPDATE 
   	         value={$this->db->escape($value)},
   	         expired=$expired_date");
      }
   }   
   
   /**
   * удаляет устаревшие переменные
   *
   * @return ничего не возвращает
   */      
   public function kill_expired() {
      $this->db->delete('settings', 'expired IS NOT NULL AND expired<NOW()');
   }

   /**
   * удаляет указанную переменную выбранного пользователя
   *
   * @param integer $id_entity код учетной записи пользователя, которому принадлежит переменная
   * @param string $name имя нужной переменной
   * @return ничего не возвращает
   */   
   public function kill($id_entity, $name) {
      $this->db->delete('settings', array('id_entity' => $id_entity, 'name' => $name));
   } //end kill   
   
   /**
   * удаляет указанные переменные выбранного пользователя
   *
   * @param integer $id_entity код учетной записи пользователя, которому принадлежит переменная
   * @param array $list список имен переменных, которые необходимо удалить
   * @return ничего не возвращает
   */   
   public function kill_vars($id_entity, $list) {
      foreach ($list as $name) {
      	$this->kill($id_entity, $name);
      }      
   } //end kill_vars
   
   /**
   * читаем переменную из POST-данных,
   * если ее там нет, то читаем ее из временных переменных пользователя,
   * если ее и там нет, то берем значение по умолчанию
   * полученное значение сохраняем в массив временных переменных пользователя
   *
   * @param string $name имя переменной без префикса таблицы
   * @param string $default значение переменной по умолчанию
   * @return string значение переменной
   */   
   public function temporary_var($name, $default, $post_name = NULL) {
      $CI =& get_instance();
      
      if (!is_null($post_name)) {
         $val = $CI->input->post($post_name);
      } else {
         $val = $CI->input->post($name);
      }
      if (!$val) {
         if (!is_null($CI->user_id)) {
            $val = $this->get($name, $CI->user_id);
         }
         if (!$val) {
            $val = $default;
         }
      }
      $CI->temporary[$name] = $val;
      return $val;
   } //end temporary_var  
         

 // text variables
  
   /**
   * получение глобальной переменной для заданного пользователя
   *
   * @param string $name имя переменной
   * @param int $id_entity код пользователя владельца переменной,по умолчанию - 0 (системная переменная)
   * @param string $default значение переменной по умолчанию
   * @return string значение переменной или NULL если переменная не найдена
   */
   public function get_big($name, $id_entity=0, $default=NULL) {
      if (isset($this->_variablesBigCache[$id_entity][$name])) {
         return $this->_variablesBigCache[$id_entity][$name];
      }
      $this->db->select('value');
      $this->db->from('settings_big');
      $this->db->where(array('name' => $name, 'id_entity' => $id_entity));
      $res = $this->db->get();
      
      
      if($res->num_rows()) {
         $row = $res->row();
         $this->_variablesBigCache[$id_entity][$name] = $row->value;          
      } else {      
         $this->_variablesBigCache[$id_entity][$name] = $default;
      }
      return $this->_variablesBigCache[$id_entity][$name];    
   }

   /**
   * запись нового значения глобальной переменной для заданного пользователя 
   *
   * @param string $name имя глобальной переменной
   * @param string $value новое значение глобальной переменной
   * @param int $id_entity код пользователя владельца переменной,по умолчанию - 0 (системная переменная)
   * @return ничего не возвращает
   */   
   public function set_big($name, $value, $id_entity=0, $expired=FALSE) {
      if (is_null($id_entity)) return;
      $this->load->helper("fields");
      $expired_date = "Null";
      if ($expired) {
         $expired_date = timestamp_to_mysql(time() + 60*30);
      }
      if (is_null($value)) {
         $this->db->where(array('name' => $name, 'id_entity' => $id_entity))->delete('settings_big');
         if (isset($this->_variablesBigCache[$id_entity][$name])) {
            unset($this->_variablesBigCache[$id_entity][$name]);      
         }
      } else {

      $this->db->query("
         INSERT INTO settings_big
         SET name={$this->db->escape($name)}, 
            value={$this->db->escape($value)},
            id_entity=$id_entity
         ON DUPLICATE KEY
            UPDATE 
               value={$this->db->escape($value)},
               expired=$expired_date");
      }
      
   }    
   
}   