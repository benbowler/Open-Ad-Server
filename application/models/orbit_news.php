<?php // -*- coding: UTF-8 -*-

if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
   exit ( 'No direct script access allowed' );
   
/**
* класс для работы с новостями OrbitScripts
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

class Orbit_News extends CI_Model {
   /**
    * Формат XML-файла с новостями OrbitScripts
    * <?xml version="1.0" encoding="UTF-8"?>
    * <orbit_news>
    *    <admin_news>
    *       <item>
    *          <id> //идентификатор новости администраторам
    *          <date> //дата размещения новости в формате mySQL
    *          <title> //заголовок новости
    *          <link> //ссылка новости
    *          <description> //текст новости
    *       </item>
    *    </admin_news>
    *    <payment_gateways>
    *       <item>
    *          <id> //идентификатор новости платежного шлюза
    *          <date> //дата размещения информации о плагине шлюза в формате mySQL
    *          <payment_gateway_id> //идентификатор платежного шлюза
    *          <version> //версия платежного шлюза
    *          <title> //название новости о платежном шлюзе
    *          <link> //ссылка на плагин
    *          <description> //описание плагина
    *       </item>
    *    </payment_gateways>
    *    <plugins>
    *       <item>
    *          <id> //идентификатор новости плагина
    *          <plugin_id></plugin_id> //идентификатор плагина
    *          <version></version> //версия плагина
    *          <date></date> //дата размещения новости в формате mySQL
    *          <title></title> //название новости о плагине
    *          <link></link> //ссылка на плагин
    *          <description></description> //описание плагина
    *       </item>
    *    </plugins>
    * </orbit_news>
    */
   
   
   public function __construct() {
      parent::__construct();      
   }
   
   /**
    * Осуществляет парсинг XML файла и размещение
    *
    * @param string $path путь к XML-файлу с новостями
    * @return null|string строка ошибки в случае невозможности парсинга файла
    */
   public function parse_xml($filepath) {
      $contents = file_get_contents($filepath);
      try {
         $xml = new SimpleXMLElement($contents);
         
         if (isset($xml->admin_news)) {
            $this->parse_admin_news($xml->admin_news);   
         }
         
         if (isset($xml->payment_gateways)) {
            $this->parse_payment_gateways($xml->payment_gateways);   
         }
         
         if (isset($xml->plugins)) {
            $this->parse_plugins($xml->plugins);   
         }
         return null;
      } catch (Exception $e) {
         return $e->getMessage();
      }
   }
   
   /**
    * Добавление новостей админа в БД
    *
    * @param object $nodes объекты <admin_news>
    */
   private function parse_admin_news($nodes) {
      foreach ($nodes->item as $item) {  
         $this->db->select('id')->from('orbitscripts_admin_news')->where('id',$item->id);
         $query = $this->db->get();
         if (0 == $query->num_rows()) {  	
      	  $this->db->insert('orbitscripts_admin_news',$item);
         }
      }
   }
   
   /**
    * Добавление новостей платежных шлюзов в БД
    *
    * @param object $nodes объекты <payment_gateways>
    */
   private function parse_payment_gateways($nodes) {
      foreach ($nodes->item as $item) {
         $this->db->select('id')->from('orbitscripts_payment_gateways_news')->where('id',$item->id);
         $query = $this->db->get();
         if (0 == $query->num_rows()) {
            $this->db->insert('orbitscripts_payment_gateways_news',$item);
         }
      }
   }
   
   /**
    * Добавление новостей плагинов в БД
    *
    * @param object $nodes объекты <payment_gateways>
    */
   private function parse_plugins($nodes) {
      foreach ($nodes->item as $item) {
         $this->db->select('id')->from('orbitscripts_plugins_news')->where('id',$item->id);
         $query = $this->db->get();
         if (0 == $query->num_rows()) {
            $this->db->insert('orbitscripts_plugins_news',$item);
         }
      }
   }
   
   /**
    * Получение списка новостей для администратора
    *
    * @param array $params параметры списка ('fields' - список полей через запятую, 'limit' - максимальное число новостей)
    * @return null|array
    */
   
   public function get_admin_news($params) {
      if (array_key_exists('fields',$params)) {
         $this->db->select($params['fields']);
      }
      
      $this->db->from('orbitscripts_admin_news');
      
      if (array_key_exists('limit',$params)) {
         $this->db->limit($params['limit']);
      }
      
      $this->db->order_by('date','desc');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->result_array(); 
      } else {
         return null;
      }
   }
   
   /**
    * Получение списка новостей плагинов
    *
    * @param array $params параметры списка ('fields' - список полей через запятую, 'limit' - максимальное число новостей)
    * @return null|array
    */
   
   public function get_plugins_news($params) {
      if (array_key_exists('fields',$params)) {
         $this->db->select($params['fields']);
      }
      
      $this->db->from('orbitscripts_plugins_news');
      
      if (array_key_exists('limit',$params)) {
         $this->db->limit($params['limit']);
      }
      
      $this->db->order_by('date','desc');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->result_array(); 
      } else {
         return null;
      }
   }
   
 /**
    * Получение списка новостей платежных шлюзов
    *
    * @param array $params параметры списка ('fields' - список полей через запятую, 'limit' - максимальное число новостей)
    * @return null|array
    */
   
   public function get_payment_gateways_news($params) {
      if (array_key_exists('fields',$params)) {
         $this->db->select($params['fields']);
      }
      
      $this->db->from('orbitscripts_payment_gateways_news');
      
      if (array_key_exists('limit',$params)) {
         $this->db->limit($params['limit']);
      }
      
      $this->db->order_by('date','desc');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->result_array(); 
      } else {
         return null;
      }
   }
   
}
?>