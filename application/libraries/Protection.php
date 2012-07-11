<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс протекшенов
 *
 */
class Protection {
   
   /**
    * Instance
    *
    * @var object
    */
   private $obj = null;

   /**
    * Тип протекшена
    *
    * @var string
    */
   private $type = 'search';
   
   /**
    * Идентификатор протекшена, который зарезал сеарч или клик
    *
    * @var string
    */
   private $cause = '';
   
   /**
    * Результат проверки. Варианты
    *    pay, free, deny
    *
    * @var string
    */
   private $result = 'pay';
   
   /**
    * Массив протекшенов формата
    *    id_protection => id_action
    *
    * @var array
    */
   private $protections = array();
   
   /**
    * Массив данных поиска
    *
    * @var array
    */
   private $search_params = array();
   
   /**
    * Массив параметров клика
    *
    * @var array
    */
   private $click_params = array();
   
   /**
    * Массив ностроек протекта
    *
    * @var array
    */
   private $settings = array();
   
   /**
    * Конструктор класса
    *
    */
   function __construct() {
      $this->obj =& get_instance();
      $this->loadProtections();
      $this->loadSettings();
      $this->obj->load->helper('location');
   }
   
   /**
    * Проверяем серч
    *
    * @param array $params Массив поисковых параметров
    * @return bool
    */
   public function search($params) {
      $this->type = 'search';
      $this->search_params = $params;
      // Blocked Countries
      if (isset($this->protections['country'])) {
         // Check Country
         if (!$this->checkCountry($params['country'])) {
            if (!$this->deny('country')) {
               return false;
            }
         }
      }
      // Anti-Proxy
      if (isset($this->protections['proxy'])) {
         // Check Proxy
         if (!$this->checkProxy($params['ip'], $params['ip_proxy'])) {
            if (!$this->deny('proxy')) {
               return false;
            }
         }
      }
      // "Quick Search" Protection
      if (isset($this->protections['quick_search'])) {
         // Quick Search Filter
         if (!$this->checkQuickSearchFilter($params['ip'])) {
            if (!$this->deny('quick_search')) {
               return false;
            }
         }
      }
      // Firewall Protection
      if (isset($this->protections['firewall'])) {
         // Quick Search Filter
         if (!$this->checkFirewall($params['ip'])) {
            if (!$this->deny('firewall')) {
               return false;
            }
         }
      }
      return true;
   }
   
   /**
    * Проверяем клик
    *
    * @param array $params Массив поисковых параметров
    * @return bool
    */
   public function click($params, $search_params) {
      $this->type = 'click';
      $this->click_params = $params;
      $this->search_params = $search_params;
      // Blocked Countries
      if (isset($this->protections['country'])) {
         // Check Country
         if (!$this->checkCountry($params['country'])) {
            if (!$this->deny('country')) {
               return false;
            }
         }
      }
      // Search & Click Protection
      if (isset($this->protections['search_click'])) {
         // Check Search & Click IP
         if ('xml' != $search_params['search_type']) {
            if (!$this->checkIps($params['ip_address'], $search_params['ip_address'])) {
               if (!$this->deny('search_click')) {
                  return false;
               }
            }
         }
         // Check Referer
         if (!$this->checkReferer($params['referer'])) {
            if (!$this->deny('search_click')) {
               return false;
            }
         }
      }
      // "Quick Click" Protection
      if (isset($this->protections['quick_click'])) {
         // Time Filter
         if (!$this->checkTimeFilter($params['datetime'], $search_params['datetime'])) {
            if (!$this->deny('quick_click')) {
               return false;
            }
         }
      }
      // Anti-Proxy
      if (isset($this->protections['proxy'])) {
         // Check Proxy
         if (!$this->checkProxy($params['ip'], $params['ip_proxy'])) {
            if (!$this->deny('proxy')) {
               return false;
            }
         }
      }
      // Firewall Protection
      if (isset($this->protections['firewall'])) {
         // Quick Search Filter
         if (!$this->checkFirewall($params['ip'])) {
            if (!$this->deny('firewall')) {
               return false;
            }
         }
      }
      return true;
   }
   
   /**
    * Получение идентификатора протекшена, зарубившего клик или серч
    *
    * @return string
    */
   public function getCause() {
      return $this->cause;
   }
   
   /**
    * Получение результата проверки
    *
    * @return int
    */
   public function getResult() {
      return $this->result;
   }
   
   /**
    * Загрузка протекшенов
    *
    */
   private function loadProtections() {
      // Загружаем протекшены
      $this->protections = array();
      $this->obj->db->select('slug, id_action')
         ->from('fraud_protections')
         ->where('status', 'enabled')
         ->where('id_action >', 1);
      $query = $this->obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result() as $row) {
            $this->protections[$row->slug] = $row->id_action;
         }
      }
   }
   
   /**
    * Загрузка настроек
    *
    */
   private function loadSettings() {
      $this->settings = array();
      $this->obj->db->select('setting_name, setting_value')
         ->from('fraud_settings');
      $query = $this->obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result() as $row) {
            $this->settings[$row->setting_name] = $row->setting_value;
         }
      }
   }
   
   private function deny($protection) {
      /**
       * Actions:
       *    1 - Show Ad & Pay (not use)
       *    2 - Show Ad & Not To Pay
       *    3 - Not To Show Ad
       *    4 - Not To Show Ad & Block IP
       */
      $return = false;
      $this->cause = $protection;
      switch ($this->protections[$protection]) {
         // Show Ad & Pay
         case 1:
            $return = true;
            break;
         // Show Ad & Not To Pay
         case 2:
            $return = true;
            $this->setResult('free');
            break;
         // Not To Show Ad
         case 3:
            $return = false;
            $this->setResult('deny');
            break;
         // Not To Show Ad & Block IP
         case 4:
            $return = false;
            $this->setResult('deny');
            $this->blockIpAddress();
            break;
      }
      return $return;
   }
   
   /**
    * Усьановка результата
    *
    * @param string $result
    */
   private function setResult($result) {
      switch ($result) {
         case 'deny':
            $this->result = 'deny';
            break;
         case 'free':
            if ('pay' == $this->result) {
               $this->result = 'free';
            }
            break;
      }
   }
   
   /**
    * Блокировка айпишника
    *
    */
   private function blockIpAddress() {
      /*
      $ip = 'search' == $this->type ? $this->search_params['ip'] : $this->click_params['ip'];
      $this->obj->db->select('ip')
         ->from('fraud_ips')
         ->where('ip', $ip)
         ->limit(1);
      $query = $this->obj->db->get();
      if (0 == $query->num_rows()) {
         $this->obj->db->insert('fraud_ips', array('ip' => $ip));
      }
      */
      
      /*
      $ip_address = 'search' == $this->type ? $this->search_params['ip_address'] : $this->click_params['ip_address'];
      $this->obj->db->select('address')
         ->from('fraud_proxies')
         ->where('address', $ip_address)
         ->limit(1);
      $query = $this->obj->db->get();
      if (0 == $query->num_rows()) {
         $this->obj->db->insert('fraud_proxies', array('address' => $ip_address, 'type' => 'anonymous'));
      }
      */
      
      // Получаем ip адрес для блокировки
      $ip = 'search' == $this->type ? $this->search_params['ip'] : $this->click_params['ip'];
      
      // Проверяем наличие этого айпишника в базе запрещенных ip адресов
      $this->obj->db->from('fraud_firewall')
         ->where('ip_start <=', $ip)
         ->where('ip_finish >=', $ip);
      if (0 == $this->obj->db->count_all_results()) {
         // Заносим в базу
         $this->obj->db->insert('fraud_firewall', array('ip_start' => $ip, 'ip_finish' => $ip));
      }
   }
   
   /**
    * Получение настройки
    *
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
   private function getSetting($name, $default = false) {
      if (isset($this->settings[$name])) {
         return $this->settings[$name];
      } else {
         return $default;
      }
   }
   
   /**
    * Проверка страны
    *
    * @param string $country
    * @return bool
    */
   private function checkCountry($country) {
      $this->obj->db->select('iso')
         ->from('countries')
         ->where('iso', $country)
         ->where('banned', 'true')
         ->limit(1);
      $query = $this->obj->db->get();
      if (0 == $query->num_rows()) {
         return true;
      }
      return false;
   }
   
   /**
    * Проверка реферера
    *
    * @param string $referer
    * @return bool
    */
   private function checkReferer($referer) {
      if ('true' == $this->getSetting('ReferrerNonEmpty', 'true') && empty($referer)) {
         return false;
      }
      return true;
   }
   
   /**
    * Проверка Quick Search Filter
    *
    * @param int $ip
    * @return bool
    */
   private function checkQuickSearchFilter($ip) {
      // Для начала вносим в таблицу
      if (0 < $this->getSetting('MaximumSearchNumber', 100)) {
         $this->obj->db->insert('fraud_quick_search', array('ip' => $ip, 'search_date' => time()));
         // Чекаем
         $this->obj->db->where('ip', $ip)
            ->where('search_date >=', time() - $this->getSetting('TimePeriod', 5))
            ->from('fraud_quick_search');
         $count = $this->obj->db->count_all_results();
         if ($count <= $this->getSetting('MaximumSearchNumber', 100)) {
            return true;
         }
         return false;
      }
      return true;
   }
   
   /**
    * Проверка проксиков
    *
    * @param int $ip
    * @param int $ip_proxy
    * @return bool
    */
   private function checkProxy($ip, $ip_proxy) {
      if ('true' == $this->getSetting('block_transparent_clicks', 'false') && 0 < $ip_proxy) {
         return false;
      }
      $this->obj->db->select('address')
         ->from('fraud_proxies')
         ->where('address', longtoip($ip));
      if ('true' == $this->getSetting('allowed_proxy_clicks', 'true')) {
         $this->obj->db->where('type', 'anonymous');
      }
      $this->obj->db->limit(1);
      $query = $this->obj->db->get();
      if (0 == $query->num_rows()) {
         return true;
      }
      return false;
   }
   
   /**
    * Проверка ip click и ip search
    *
    * @param int $ip_click
    * @param int $ip_search
    * @return bool
    */
   private function checkIps($ip_click, $ip_search) {
      if ('true' == $this->getSetting('SearchClickIpMatch', 'true') && $ip_click != $ip_search) {
         return false;
      }
      return true;
   }
   
   /**
    * Проверка времени между кликом и поиском
    *
    * @param int $time_click
    * @param int $time_search
    * @return bool
    */
   private function checkTimeFilter($time_click, $time_search) {
      $interval = $time_click - $time_search;
      $min_interval = $this->getSetting('MinimumIntervalSearchClick', 0);
      $max_interval = $this->getSetting('MaximumIntervalSearchClick', 7200);
      if ($interval < $min_interval || ($interval > $max_interval && 0 < $max_interval)) {
         return false;
      }
      return true;
   }
   
   /**
    * Проверка заблокированности айпишника
    *
    * @param int $ip
    * @return bool
    */
   private function checkFirewall($ip) {
      $this->obj->db->from('fraud_firewall')
         ->where('ip_start <=', $ip)
         ->or_where('ip_finish >=', $ip);
      if (0 < $this->obj->db->count_all_results()) {
         return false;
      }
      return true;
   }
   
}