<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Модель для работы с базами SQLite
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class SQLite extends CI_Model {

   protected $config = array();

   /**
   * конструктор класса
   */
   public function __construct() {
      parent::__construct();
      $this->config['hostname'] = "";
      $this->config['username'] = "";
      $this->config['password'] = "";
      $this->config['dbdriver'] = "pdo";
      $this->config['dbprefix'] = "";
      $this->config['pconnect'] = FALSE;
      $this->config['db_debug'] = FALSE;
      $this->config['cache_on'] = FALSE;
      $this->config['cachedir'] = "";
      $this->config['char_set'] = "utf8";
      $this->config['dbcollat'] = "utf8_general_ci";
   } //end Report_generator

   /**
   * возвращает объект базы данных для заданного файла SQLite
   * 
   * @param string $file файл базы данных SQLite
   * @return object открытая база данных
   */
   public function get_database($file) {
      /*if (!file_exists($file)) {
         return FALSE;
      }*/
      $this->config['database'] = "sqlite:" . $file;
      return $this->load->database($this->config, TRUE);
   } //end get_database
   
}
?>