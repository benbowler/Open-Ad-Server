<?php

/**
 * Абстрактный класс протекшенов
 *
 * @author Gennadiy Kozlenko
 */
abstract class Sppc_Protection_Target_Abstract extends Sppc_Protection {

   /**
    * Show Ad & Pay
    *
    */
   const ACTION_PAY = 1;

   /**
    * Show Ad & Not To Pay
    *
    */
   const ACTION_FREE = 2;

   /**
    * Not To Show Ad
    *
    */
   const ACTION_DENY = 3;

   /**
    * Not To Show Ad & Block IP
    *
    */
   const ACTION_BLOCK = 4;

   /**
    * Идентификатор protection, заблокировавший показ объявления, клик или оплату
    *
    * @var unknown_type
    */
   protected $_cause = '';

   /**
    * Массив настроек протекшенов
    *
    * @var array
    */
   protected $_settings = array();

   /**
    * Массив протекшенов для построения цепочек
    *
    * @var array
    */
   protected $_fraudProtections = array();

   /**
    * Engine протекшена
    *
    * @var unknown_type
    */
   protected $_engine = '';

   /**
    * Конструктор класса
    *
    */
   function __construct() {
      parent::__construct();
      $this->_loadSettings();
      $this->_loadFraudProtections();
   }

   /**
    * Проверка на валидность цепочки валидаторов
    *
    * @param mixed $value
    */
   public function isValid($value = null) {
      $this->_setStatus(self::PAY);
      $this->_messages = array();
      $result = true;
      // Дебажим
      if (null !== $this->firephp) {
         $this->firephp->log($this->_fraudProtections, 'Fraud Protections');
      }
      foreach ($this->_protections as $element) {
         /* @var $protection Sppc_Protection_Abstract */
         $protection = $element['instance'];
         // Устанавливаем параметры
         $protection->setSettings($this->_settings);
         if (!$protection->isValid()) {
            // Дебажим
            if (null !== $this->firephp) {
               $this->firephp->error('Protection ' . get_class($protection) . ' fail');
            }
            $result = false;
            $messages = $protection->getMessages();
            $this->_messages = array_merge($this->_messages, $messages);
            $this->_setCause($this->_fraudProtections[get_class($protection)]['slug']);
            if ($this->_fraudProtections[get_class($protection)]['use_actions']) {
               switch ($this->_fraudProtections[get_class($protection)]['id_action']) {
                  // Показываем, но не платим и продолжаем проверку
                  case self::ACTION_FREE:
                     $this->_setStatus(self::FREE);
                     continue 2;
                     break;
                  // Не показываем и не платим
                  case self::ACTION_DENY:
                     $this->_setStatus(self::DENY);
                     break;
                  // Не показываем, не платим и блокируем
                  case self::ACTION_BLOCK:
                     $this->_setStatus(self::DENY);
                     self::blockIp();
                     break;
               }
               break;
            } else {
               $this->_setStatus(self::FREE);
            }
         }
      }
      // Дебажим
      if (null !== $this->firephp) {
         $this->firephp->log($this->_messages, 'Fraud messages');
      }
      return $result;
   }

   /**
    * Получение идентификатора protection, заблокировавшего показ объявления, клик или оплату
    *
    * @return string
    */
   public function getCause() {
      return $this->_cause;
   }

   /**
    * Установка идентификатора protection, заблокировавшего показ объявления, клик или оплату
    *
    * @param string $cause
    */
   protected function _setCause($cause) {
      $this->_cause = $cause;
   }

   /**
    * Установка engine протекшена
    *
    * @param string $engine
    */
   protected function _setEngine($engine) {
      $this->_engine = $engine;
   }

   /**
    * Загрузка массива настроек
    *
    */
   protected function _loadSettings() {
      $CI =& get_instance();
      $settings = array();
      $feEngine = $CI->config->item('protection_settings_frontend_engine');
      $feOptions = $CI->config->item('protection_settings_frontend');
      $beEngine = $CI->config->item('protection_settings_backend_engine');
      $beOptions = $CI->config->item('protection_settings_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      if (false === ($settings = $cache->load('protection_settings'))) {
         $CI->db->select('setting_name, setting_value')
            ->from('fraud_settings');
         $query = $CI->db->get();
         if (0 < $query->num_rows()) {
            foreach ($query->result() as $row) {
               $settings[$row->setting_name] = $row->setting_value;
            }
         }
         $cache->save($settings, 'protection_settings');
      }
      $this->_settings = $settings;
   }

   /**
    * Загрузка протекшенов для построения цепочки
    *
    */
   protected function _loadFraudProtections() {
      $CI =& get_instance();

      $protections = array();
      $feEngine = $CI->config->item('protections_frontend_engine');
      $feOptions = $CI->config->item('protections_frontend');
      $beEngine = $CI->config->item('protections_backend_engine');
      $beOptions = $CI->config->item('protections_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      if (false === ($protections = $cache->load('protections_' . $this->_engine))) {
         $protections = array();
         $CI->db->select('slug, id_action, use_actions')
            ->from('fraud_protections')
            ->where('status', 'enabled')
            ->where("FIND_IN_SET('" . $this->_engine . "', target) > 0", null, false)
            ->where('id_action <>', self::ACTION_PAY);
         $query = $CI->db->get();
         if (0 < $query->num_rows()) {
            foreach ($query->result_array() as $row) {
               $row['use_actions'] = 'true' == $row['use_actions'];
               array_push($protections, $row);
            }
         }
         $cache->save($protections, 'protections_' . $this->_engine);
      }
      
      $this->_fraudProtections = array();
      foreach ($protections as $row) {
         // Добавляем протекшн в цепочку
         $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $row['slug'])));
         $protectionClass = 'Sppc_Protection_' . $class;
         try {
            Sppc_Loader::tryLoadClass($protectionClass);
            $this->addProtection(new $protectionClass);
            // Сохраняем параметры для этого protection
            $this->_fraudProtections[$protectionClass] = array(
               'id_action'   => $row['id_action'],
               'slug'        => $row['slug'],
               'use_actions' => $row['use_actions']
            );
         } catch (Exception $e) {
            // Class $protectionClass not found
         }
      }
   }

   /**
    * Блокировка IP адреса
    *
    * @var $ip_address string
    */
   static public function blockIp() {
      $CI =& get_instance();
      $parameters = Sppc_Protection_Parameters::getInstance();
      $ip = $parameters->getParameter('ip');
      $CI->db->from('fraud_firewall')
         ->where('ip_start <=', $ip)
         ->where('ip_finish >=', $ip);
      if (0 == $CI->db->count_all_results()) {
         // Заносим в базу
         $CI->db->insert('fraud_firewall', array('ip_start' => $ip, 'ip_finish' => $ip));
      }
   }

}
