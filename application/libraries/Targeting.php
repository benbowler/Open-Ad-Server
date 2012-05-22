<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс проверки на таргетинг
 * 
 * @author Gennadiy Kozlenko
 */
class Targeting {
   
   /**
    * Instance
    *
    * @var object
    */
   private $obj = null;

   /**
    * Группа таргетинга для тестирования
    *
    * @var int
    */
   private $id_targeting_group = 0;
   
   /**
    * Массив правил таргетинга, разбитый на группы
    *
    * @var array
    */
   private $rules = array();
   
   /**
    * User Agent
    *
    * @var string
    */
   private $user_agent;
   
   /**
    * Browser
    *
    * @var string
    */
   private $browser;
   
   /**
    * IP Address
    *
    * @var string
    */
   private $ip_address;
   
   /**
    * Language
    *
    * @var string
    */
   private $language;
   
   /**
    * Country
    *
    * @var string
    */
   private $country;
   
   /**
    * Page URL
    *
    * @var string
    */
   private $page_url;
   
   /**
    * Referer URL
    *
    * @var string
    */
   private $referer;
   
   /**
    * Объект дебаггера
    *
    * @var FirePHP
    */
   private $firephp = null;
   
   /**
    * Конструктор класса
    *
    */
   function __construct() {
      $this->obj =& get_instance();
      if ($this->obj->config->item('debug_mode')) {
         $this->obj->load->helper('firephp');
         $this->firephp =& get_firephp();
      }
   }
   
   /**
    * Get country
    * 
    * @return string
    */
   public function getCountry() {
      return $this->country;
   }
   
   /**
    * Get IP address
    * 
    * @return string
    */
   public function getIpAddress() {
      return $this->ip_address;
   }
   
   /**
    * Get language
    * 
    * @return string
    */
   public function getLanguage() {
      return $this->language;
   }
   
   /**
    * Get page url
    * 
    * @return string
    */
   public function getPageUrl() {
      return $this->page_url;
   }
   
   /**
    * Get Referer
    * 
    * @return string
    */
   public function getReferer() {
      return $this->referer;
   }
   
   /**
    * Get user agent
    * 
    * @return string
    */
   public function getUserAgent() {
      return $this->user_agent;
   }
   
   /**
    * Get browser
    * 
    * @return string
    */
   public function getBrowser() {
      return $this->browser;
   }
   
   /**
    * @param string $country
    */
   public function setCountry($country) {
      $this->country = $country;
   }
   
   /**
    * set IP address
    * 
    * @param string $ip_address
    */
   public function setIpAddress($ip_address) {
      $this->ip_address = $ip_address;
   }
   
   /**
    * Set language
    * 
    * @param string $language
    */
   public function setLanguage($language) {
      $this->language = $language;
   }
   
   /**
    * Set page URL
    * 
    * @param string $page_url
    */
   public function setPageUrl($page_url) {
      $this->page_url = $page_url;
   }
   
   /**
    * @param string $referer
    */
   public function setReferer($referer) {
      $this->referer = $referer;
   }
   
   /**
    * Set user agent
    * 
    * @param string $user_agent
    */
   public function setUserAgent($user_agent) {
      $this->user_agent = $user_agent;
   }
   
   /**
    * Set browser
    * 
    * @param string $browser
    */
   public function setBrowser($browser) {
      $this->browser = $browser;
   }
   
   /**
    * Установка группы таргетинга
    *
    * @param int $id_targeting_group
    */
   public function setTargetingGroup($id_targeting_group) {
      $this->id_targeting_group = $id_targeting_group;
      $this->loadTargetingRules();
   }
   
   /**
    * Проверка на правила таргетинга
    *
    * @return bool
    */
   public function checkRules() {
      foreach ($this->rules as $group => $rules) {
         $method = 'check' . str_replace(' ', '', ucwords(str_replace('_', ' ', $group)));
         if (is_callable(array($this, $method))) {
            if (!call_user_func(array($this, $method), $rules)) {
               return false;
            }
         }
      }
      return true;
   }
   
   /**
    * Загрузка правил группы таргетинга
    *
    */
   private function loadTargetingRules() {
      $this->rules = array();
      $this->obj->db->select('`group`, `name`, `value`, `compare`', false)
         ->from('targeting_group_values')
         ->where('id_targeting_group', $this->id_targeting_group);
      $query = $this->obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result() as $row) {
            if (!isset($this->rules[$row->group])) {
               $this->rules[$row->group] = array('rules' => array(), 'list' => array());
            }
            array_push($this->rules[$row->group]['rules'], array(
               'name'    => $row->name,
               'value'   => $row->value,
               'compare' => $row->compare
            ));
            array_push($this->rules[$row->group]['list'], $row->value);
         }
      }
      if ($this->firephp) {
         $this->firephp->log($this->rules, 'Rules');
      }
   }
   
   /**
    * Сравнение значения с правилом
    *
    * @param string $value   что проверяем
    * @param string $rule    правило
    * @param string $compare метод сравнения
    * @return bool
    */
   private function compareValues($value, $rule, $compare) {
      switch ($compare) {
         case 'equals':
            return 0 == strcmp($value, $rule);
            break;
         case 'not_equals':
            return 0 != strcmp($value, $rule);
            break;
         case 'contain':
            return false !== strstr($value, $rule);
            break;
         case 'not_contain':
            return false === strstr($value, $rule);
            break;
         case 'regexp':
            return 0 < preg_match('~' . $this->prepareRegexpRule($rule) . '~i', $value);
            break;
         case 'not_regexp':
            return 0 == preg_match('~' . $this->prepareRegexpRule($rule) . '~i', $value);
            break;
         case 'less_than':
            if (is_numeric($rule)) {
               // Нумериковое сравнение
               return $value < $rule;
            } else {
               // Строковое сравнение
               return 0 < strcmp($value, $rule);
            }
            break;
         case 'more_than':
            if (is_numeric($rule)) {
               // Нумериковое сравнение
               return $value > $rule;
            } else {
               // Строковое сравнение
               return 0 > strcmp($value, $rule);
            }
            break;
      }
      return false;
   }
   
   /**
    * Сравление значения со всеми правилами
    *
    * @param string $value что проверяем
    * @param array $rules  набор правил
    * @return bool
    */
   private function compareRules($value, $rules) {
      // Массив результатов
      $results = array();
      foreach ($rules as $rule) {
         // Определяем как будем сравнивать результаты между одинаковыми compare
         $is_or = false;
         $group = 'deny';
         if (false === strpos($rule['compare'], 'not_')) {
            $is_or = true;
            $group = 'allow';
         }
         if (!isset($results[$group])) {
            $results[$group] = !$is_or;
         }
         $res = $this->compareValues($value, $rule['value'], $rule['compare']);
         if ($is_or) {
            $results[$group] = $results[$group] || $res;
         } else {
            $results[$group] = $results[$group] && $res;
         }
      }
      return !in_array(false, $results);
   }
   
   /**
    * Подготовка правила для вставки в регулярное выражение
    *
    * @param string $rule
    * @return string
    */
   private function prepareRegexpRule($rule) {
      return str_replace(array('\^', '\$', '\*', '\+'), array('^', '$', '.*', '.+'), preg_quote($rule, '~'));
   }
   
   /**
    * Тестирование правил для браузера
    *
    * @return bool
    */
   private function checkBrowsers($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->browser, 'Browser');
         $this->firephp->log($rules['list'], 'Allowed browsers');
      }
      // Проверяем вхождение этого браузера в список разрешенных
      if (in_array($this->browser, $rules['list'])) {
         return true;
      }
      return false;
   }
   
   /**
    * Тестирование правил для айпишников клиента
    *
    * @return bool
    */
   private function checkIps($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->ip_address, 'IP Address');
         $this->firephp->log($rules['rules'], 'IP Address Rules');
      }
      return $this->compareRules($this->ip_address, $rules['rules']);
   }
   
   /**
    * Тестирование правил для языков
    *
    * @return bool
    */
   private function checkLanguages($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->language, 'Language');
         $this->firephp->log($rules['list'], 'Allowed Languages');
      }
      // Проверяем вхождение языка в список разрешенных
      if (in_array($this->language, $rules['list'])) {
         return true;
      }
      return false;
   }
   
   /**
    * Тестирование правил для агентов
    *
    * @return bool
    */
   private function checkUserAgents($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->user_agent, 'User Agent');
         $this->firephp->log($rules['rules'], 'User Agent Rules');
      }
      return $this->compareRules($this->user_agent, $rules['rules']);
   }
   
   /**
    * Тестирование правил для стран
    *
    * @return bool
    */
   private function checkCountries($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->country, 'Country');
         $this->firephp->log($rules['list'], 'Allowed Countries');
      }
      // Проверяем вхождение языка в список разрешенных
      if (in_array($this->country, $rules['list'])) {
         return true;
      }
      return false;
   }
   
   /**
    * Тестирование правил для Page URLs
    *
    * @return bool
    */
   private function checkUrls($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->page_url, 'Page URL');
         $this->firephp->log($rules['rules'], 'PageURL Rules');
      }
      return $this->compareRules($this->page_url, $rules['rules']);
   }
   
   /**
    * Тестирование правил для Referer URLs
    *
    * @return bool
    */
   private function checkReferers($rules) {
      if ($this->firephp) {
         $this->firephp->log($this->referer, 'Referer');
         $this->firephp->log($rules['rules'], 'Referer Rules');
      }
      return $this->compareRules($this->referer, $rules['rules']);
   }
   
   /**
    * Тестирование правил для Variables
    *
    * @return bool
    */
   private function checkVariables($rules) {
      // Получаем список переменных страницы
      $variables = array();
      $url = parse_url($this->page_url);
      if (is_array($url) && !empty($url['query'])) {
         parse_str($url['query'], $variables);
      }
      if ($this->firephp) {
         $this->firephp->log($variables, 'Page Variables');
         $this->firephp->log($rules['rules'], 'Veriables Rules');
      }
      // Группируем правила по названиям переменных
      $var_rules = array();
      foreach ($rules['rules'] as $rule) {
         if (!isset($var_rules[$rule['name']])) {
            $var_rules[$rule['name']] = array();
         }
         array_push($var_rules[$rule['name']], $rule);
      }
      // Проверяем правила для каждой переменой
      foreach ($var_rules as $name => $value) {
         if (isset($variables[$name]) && !$this->compareRules($variables[$name], $value)) {
            return false;
         }
         
      }
      //return $this->compareRules($this->page_url, $rules['rules']);
      return true;
   }
   
}
