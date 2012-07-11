<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Abstract_builder.php';

/**
 * Класс клика
 *
 * @author Gennadiy Kozlenko
 */
class Click_builder extends Abstract_builder {
   
   /**
    * Идентификатор клика
    *
    * @var string
    */
   private $clickid = '';
   
   /**
    * Сервер, на котором находятся данные по клику
    *
    * @var integer
    */
   private $search_server = 0;
   
   /**
    * Search date
    *
    * @var integer
    */
   private $search_date = 0;
   
   /**
    * Массив с параметрами поиска, приведших к данному клику
    *
    * @var array
    */
   private $search_data = array();
   
   /**
    * Урл для редиректа
    *
    * @var string
    */
   private $url = '';
   
   /**
    * Instance
    *
    * @var Click_builder
    */
   protected static $_instance = null;
   
   /**
    * Папка в которой мы нашли клик
    */
   protected $_search_folder_time = null;
   
   /**
    * Плагины используемые для расширения функционала click_builder'а
    * 
    * @var array
    */
   protected $_hookObjects = array();
   
   /**
    * get instance of the class
    *
    * @return Click_builder
    */
   public static function getInstance() {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      parent::__construct();
      self::$_instance = $this;
   }
   
   /**
    * Процесс клика
    *
    */
   public function click() {

      // Получаем данные клика
      $click_data = Sppc_Stats_Utils::extractClickId($this->clickid);
      if ($click_data == false) {

      // Вылетели по ошибке невозможности прочтения данных клика
         $this->error = 'click_data_error';
         $this->result = Sppc_Protection::DENY;
         return false;
      }
     
      // Устанавливаем урл для редиректа
      $this->url = $click_data['destination_url'];

      // Статистика...
      if ($click_data['id_feed'] == 0){

      $this->obj->load->model('stat');
         $add_data = $this->obj->stat->get_click_info($click_data['id_ad'],$click_data['id_group_site_channel'],$click_data['id_channel']); 
  
         if ($add_data != false){
            foreach ($add_data as $key => $value){
               $click_data[$key] = $add_data[$key];
            }
         } else{
            return false; 
         }
      }
      
      
      $click_data['clicks'] = 1;

      if ($this->firephp) {
         $this->firephp->log($click_data, 'Click data');
      }
      
      // register click
      $this->registerStat(array($click_data));
      
      return true;
   }
   
   /**
    * Получение идентификатора клика
    *
    * @return string
    */
   public function getClickId() {
      return $this->clickid;
   }
   
   /**
    * Получение урла для редиректа
    *
    * @return string
    */
   public function getUrl() {
      return $this->url;
   }
   
   /**
    * Установка идентификатора клика
    *
    * @param string $clickid
    */
   public function setClickId($clickid) {
      $this->clickid = $clickid;
   }
   
   /**
    * Оплата за клик
    *
    * @param $click_data
    * @return bool
    */
   private function pay(&$click_data) {
      if ($click_data['id_feed'] == 0) {
         // Если это не CPC реклама, то ничего не платим
         return true;
      }
      
      // Подгружаем необходимые модельки
      $this->obj->load->model('global_variables');
      $this->obj->load->model('payment_gateways', 'pg_obj');
      
      // Получаем объект спора - бид
      $bid = $click_data['spent'];
      
      // Получаем объект кошелька платильщика
      $payerPurse = new Sppc_Pay($bid);
      // Получаем объект кошелька админа
      $adminPurse = new Sppc_Pay();
      
      // Получаем необходимые идентификаторы
      $id_feed_entity = $this->obj->global_variables->get('feed_entity');
      $id_admin = $this->obj->global_variables->get('admin_entity');
      $id_feed = $this->search_data['id_feed'];
      $id_payer = $id_feed_entity;
     
      // Забираем деньги от платильщика админу
      $payerPurse->gives($adminPurse, 100, Sppc_Pay::UNIT_PERCENT);
      // Оформляем сделку
      $this->obj->pg_obj->money_flow($id_feed_entity, $id_admin, $adminPurse->getValue(), $this->obj->entity_obj->ballance($id_admin) + $adminPurse->getValue(), 0, 'click', 0, true, '');

      // Выплачиваем деньги админу
      $this->obj->entity_obj->add_money(1, $adminPurse->getValue());
      
      return true;
   }

}
