<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Абстрактный класс поиска и клика
 *
 * @author Gennadiy Kozlenko
 */
class Abstract_builder {
   
   /**
    * Тип программы Flat_Rate
    *
    */
   const PROG_FLAT_RATE = 'Flat_Rate';

   /**
    * Тип программы CPM
    *
    */
   const PROG_CPM = 'CPM';

   /**
    * Тип программы CPC
    *
    */
   const PROG_CPC = 'CPC';

   /**
    * Тип текстового объявления
    *
    */
   const TYPE_TEXT = 'text';

   /**
    * Тип картиночного объявления
    *
    */
   const TYPE_IMAGE = 'image';

   /**
    * Тип flash объявления
    *
    */
   const TYPE_FLASH = 'flash';
      
   /**
    * Тип рекламного объявления
    *
    */
   const AD_TYPE_ADS = 'ads';
   
   /**
    * Тип отображения JS рекламы
    *
    */
   const SEARCH_TYPE_JS = 'js';

   /**
    * Тип отображения HTML рекламы
    *
    */
   const SEARCH_TYPE_HTML = 'html';
   
   /**
    * Тип отображения intext рекламы
    *
    */
   const SEARCH_TYPE_INTEXT = 'intext';
   
   /**
    * Тип текстового канала
    *
    */
   const CHANNEL_TEXT = 'text';

   /**
    * Тип картиночного канала
    *
    */
   const CHANNEL_IMAGE = 'image';
   
   /**
    * Результат с нашей базы
    *
    */
   const RESULT_OURDATABASE = 'ourdatabase';
   
   /**
    * Результат с XML фидов
    *
    */
   const RESULT_XML = 'xml';

   /**
    * Результат с поисковых движков
    *
    */
   const RESULT_ORGANIC = 'organic';

   /**
    * Отображение только рекламных объявлений рекламодателей
    *
    */
   const DISPLAY_ADV_ONLY = 'adv_only';

   /**
    * Отображение только рекламных объявлений с XML фидов
    *
    */
   const DISPLAY_XML_ONLY = 'xml_only';

   /**
    * Отображение рекламных объявлений рекламодателей и рекламных объявлений с XML фидов
    * при этом сначала показываются все объявления от рекламодателей а затем с xml фидов
    *
    */
   const DISPLAY_ADV_XML = 'adv_xml';
   
   /**
    * Отображение рекламных объявлений рекламодателей и рекламных объявлений с XML фидов
    * при этом сначала показываются все объявления с xml фидов а затем от собственных рекламодателей
    */
   const DISPLAY_XML_ADV = 'xml_adv';

   /**
    * Отображение рекламных объявлений рекламодателей и рекламных объявлений с XML фидов
    * отсортированных по биду
    *
    */
   const DISPLAY_ADV_XML_BLEND = 'adv_xml_blend';

   
    /**
    * Массив статистики
    *
    * @var array
    */
   protected $clicks = array();

   /**
    * Instance
    *
    * @var object
    */
   protected $obj = null;

   /**
    * Текущий сервер
    *
    * @var integer
    */
   protected $server = 0;
   
   /**
    * Реферер
    *
    * @var string
    */
   protected $referer = '';
   
   /**
    * Айпишник посетителя
    *
    * @var string
    */
   protected $ip_address = '';
   
   /**
    * Десятичное предстравление айпишника
    *
    * @var integer
    */
   protected $ip = 0;

   /**
    * Айпишник проксика посетителя
    *
    * @var string
    */
   protected $ip_address_proxy = '';
   
   /**
    * Десятичное предстравление айпишника проксика
    *
    * @var integer
    */
   protected $ip_proxy = 0;
   
   /**
    * Флаг, показывающий, был ли установлен ip proxy
    *
    * @var bool
    */
   protected $ip_proxy_set = false;
   
   /**
    * Юзер-агент посетителя
    *
    * @var string
    */
   protected $user_agent = '';
   
   /**
    * Accept-Language посетителя
    *
    * @var string
    */
   protected $accept = '';

   /**
    * Браузер посетителя
    *
    * @var string
    */
   protected $browser = '';

   /**
    * Язык браузера посетителя
    *
    * @var string
    */
   protected $language = '';
   
   /**
    * Страна посетителя
    *
    * @var string
    */
   protected $country = '';
   
   /**
    * Время и дата клика
    *
    * @var integer
    */
   protected $datetime = 0;
   
   /**
    * Дата клика
    *
    * @var integer
    */
   protected $date = 0;
   
   /**
    * Причина плохого серча
    *
    * @var string
    */
   protected $cause = '';

   /**
    * Результат для клика
    *
    * @var string
    */
   protected $result = Sppc_Protection::PAY;

   /**
    * Код ошибки
    *
    * @var string
    */
   protected $error = '';

   /**
    * Объект дебаггера
    *
    * @var FirePHP
    */
   protected $firephp = null;

   /**
    * Класс для работы с протекшенами
    *
    * @var Sppc_Protection
    */
   protected $protections = null;
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      $this->obj =& get_instance();
      $this->obj->load->model('global_variables');
      $this->obj->load->helper('launch');
      $this->obj->load->helper('location');
      $this->obj->load->helper('useragent');
      $this->obj->load->library('logger');
      $this->obj->load->library('benchmark');
      $this->server = (int) $this->obj->config->item('server_id');

      if ($this->obj->config->item('debug_mode')) {
         $this->obj->load->helper('firephp');
         $this->firephp =& get_firephp();
      }
   }

   /**
    * Возвращение кода предыдущей ошибки
    *
    * @return string
    */
   public function getLastError() {
      return $this->error;
   }

   /**
    * Метод загрузки необходимых параметров
    *
    * Вызывается во время поиска
    */
   protected function loadParameters() {
      // datetime
      $this->datetime = time();
      // date
      $this->date = mktime(0, 0, 0);
      
      // Определяем айпишник посетителя (по возможности за прокси)
      if (empty($this->ip_address)) {
         $this->setIpAddress(get_ip_address());
      }
      // Определяем айпишник прокси
      if (!$this->ip_proxy_set) {
         $this->setIpAddressProxy(get_proxy_ip_address());
      }
      // Определяем местоположение
      $this->determineLocation();
      // Определяем юзер-агент пользователя
      if (empty($this->user_agent)) {
         $this->setUserAgent($this->obj->input->server('HTTP_USER_AGENT'));
      }
      // Определяем браузер пользователя
      if (empty($this->browser)) {
         $this->setBrowser(get_ua_browser($this->user_agent));
      }
      // Определяем accept-language пользователя
      if (empty($this->accept)) {
         $this->setAccept($this->obj->input->server('HTTP_ACCEPT_LANGUAGE'));
      }
      // Определяем язык браузера пользователя
      if (empty($this->language)) {
         $this->setLanguage(get_ua_language($this->accept));
      }
      // Определяем страницу, на которой расположено рекламное объявление
      if (empty($this->referer)) {
         $this->setReferer(get_ua_referer());
      }
   }

   /**
    * Получение страны посетителя
    *
    * @return string
    */
   public function getCountry() {
      return $this->country;
   }

   /**
    * Установка страны пользователя
    * Вообще говоря не нужно, так как страна будет определяться по айдишнику
    *
    * @param string $country
    */
   public function setCountry($country) {
      $this->country = strtoupper($country);
   }

   /**
    * Получение айпишника посетителя
    *
    * @return string
    */
   public function getIpAddress() {
      return $this->ip_address;
   }

   /**
    * Установка айпишника пользователя
    *
    * @param string $ip_address
    */
   public function setIpAddress($ip_address) {
      if ($this->ip_address != $ip_address) {
         $this->ip_address = $ip_address;
         $this->ip = iptolong($this->ip_address);
      }
   }

   /**
    * Получение айпишника проксика посетителя
    *
    * @return string
    */
   public function getIpAddressProxy() {
      return $this->ip_address_proxy;
   }

   /**
    * Установка айпишника проксика пользователя
    *
    * @param string $ip_address
    */
   public function setIpAddressProxy($ip_address) {
      $this->ip_proxy_set = true;
      if ($this->ip_address_proxy != $ip_address) {
         $this->ip_address_proxy = $ip_address;
         $this->ip_proxy = iptolong($this->ip_address_proxy);
      }
   }

   /**
    * Получение юзер-агента посетителя
    *
    * @return string
    */
   public function getUserAgent() {
      return $this->user_agent;
   }

   /**
    * Установка юзер-агента пользователя
    *
    * @param string $user_agent
    */
   public function setUserAgent($user_agent) {
      $this->user_agent = $user_agent;
   }

   /**
    * Получение accept-language посетителя
    *
    * @return string
    */
   public function getAccept() {
      return $this->accept;
   }

   /**
    * Установка accept-language пользователя
    *
    * @param string $accept
    */
   public function setAccept($accept) {
      $this->accept = $accept;
   }

   /**
    * Получение браузера посетителя
    *
    * @return string
    */
   public function getBrowser() {
      return $this->browser;
   }

   /**
    * Установка браузера пользователя
    *
    * @param string $browser
    */
   public function setBrowser($browser) {
      $this->browser = $browser;
   }

   /**
    * Получение языка посетителя
    *
    * @return string
    */
   public function getLanguage() {
      return $this->language;
   }

   /**
    * Установка языка браузера пользователя
    *
    * @param string $language
    */
   public function setLanguage($language) {
      $this->language = strtoupper($language);
   }

   /**
    * Получение адреса страницы, на которой расположена реклама
    *
    * @return string
    */
   public function getReferer() {
      return $this->referer;
   }

   /**
    * Установка страницы, на которой расположена реклама
    *
    * @param string $referer
    */
   public function setReferer($referer) {
      $this->referer = $referer;
   }

   /**
    * Метод определения местоположения по айпишнику
    *
    */
   protected function determineLocation() {
      $this->country = get_country_by_ip($this->ip_address);
      if (empty($this->country)) {
         $this->country = 'UN';
      }
   }

   /**
    * Подготовка протекшенов
    *
    */
   protected function prepareProtections() {
      // Создаем класс протекшенов
      $this->protections = new Sppc_Protection();
   }

   /**
    * Подготовка параметров для протекшенов
    *
    */
   protected function prepareProtectionParameters() {
      // Формируем данные для протекта
      $params = array(
         'datetime'         => time(),
         'country'          => $this->country,
         'language'         => $this->language,
         'browser'          => $this->browser,
         'user_agent'       => $this->user_agent,
         'referer'          => $this->referer,
         'ip_address'       => $this->ip_address,
         'ip'               => $this->ip,
         'ip_address_proxy' => $this->ip_address_proxy,
         'ip_proxy'         => $this->ip_proxy
      );
      // Инициализируем класс поисковых параметров
      $parameters = Sppc_Protection_Parameters::getInstance();
      $parameters->setParameters($params);
   }

   /**
    * Тест на протекшены
    *
    * @return boolean
    */
   protected function checkProtections() {
      // Проверяем цепочку протекшенов
      $check = $this->protections->isValid();
      if (!$check) {
         // Получаем cause
         $this->cause = $this->protections->getCause();
         // Получаем статус
         $this->result = $this->protections->getStatus();
         if ($this->firephp) {
            $this->firephp->log($this->cause, 'Cause');
            $this->firephp->log($this->result, 'Status');
         }
      }
      return $check;
   }
   
   /**
    * Генерирование нормального сида для srand
    *
    * @return unknown
    */
   function make_seed() {
      list($usec, $sec) = explode(' ', microtime());
      return (float) $sec + ((float) $usec * 100000);
   }
   
   
   /**
    * Регистрация кликов (одной пачкой)
    *
    * @param array $clicks
    */
   public function registerStat($data) {
      $this->obj->benchmark->mark('register_clicks_start');
      $stats = new Sppc_Stats();
      $stats->writeStatstoDb($data);
      $this->obj->benchmark->mark('register_clicks_end');
   }
   
}
