<?php

/**
 * Класс протекшенов клика
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Protection_Target_Click extends Sppc_Protection_Target_Abstract {

   /**
    * Engine поиска
    *
    */
   const ENGINE_CLICK = 'click';

   /**
    * CI Instance
    *
    * @var object
    */
   private $CI = null;

   /**
    * Идентификатор клика
    *
    * @var string
    */
   static private $clickid;

   /**
    * Конструктор класса
    *
    */
   function __construct() {
      $this->_setEngine(self::ENGINE_CLICK);
      parent::__construct();
      $this->CI =& get_instance();
      // Инициализируем storage
      $this->_initStorage();
      // Сохраняем, если нужно, данные по пользователю
      if (!$this->_isStoredVisitorData()) {
         $this->_storeVisitorData();
      } else {
         $this->_restoreVisitorData();
      }
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
      // Получаем последний протекшен из хранилища
      $slug = $this->_getStoredProtection();
      if (self::PAY != $this->_status) {
         $result = false;
      }
      // Дебажим
      if (null !== $this->firephp) {
         $this->firephp->log($this->_fraudProtections, 'Fraud Protections');
         $cache = self::getCache();
         if (false !== ($storage = $cache->load(self::getCacheId()))) {
            $this->firephp->log($storage, 'Click Storage');
         }
      }
      foreach ($this->_protections as $element) {
         /* @var $protection Sppc_Protection_Abstract */
         $protection = $element['instance'];
         // Получаем slug текущего протекшена
         $current_slug = $this->_fraudProtections[get_class($protection)]['slug'];
         // Проверяем на обработанность
         if (false !== $slug && $current_slug != $slug) {
            continue;
         }
         // Устанавливаем параметры
         $protection->setSettings($this->_settings);
         $check = $protection->isValid();
         if ($protection instanceof Sppc_Protection_Redirect_Interface && $protection->isRedirect()) {
            // Сохраняем протекшен
            $this->_storeProtection($current_slug);
            // Редирктим куда нужно
            $this->_redirect($protection->getRedirectUrl());
         } else {
            // Удаляем протекшен
            $this->_storeProtection();
            $slug = false;

            if (!$check) {
               // Дебажим
               if (null !== $this->firephp) {
                  $this->firephp->error('Protection ' . get_class($protection) . ' fail');
               }
               $result = false;
               $messages = $protection->getMessages();
               $this->_messages = array_merge($this->_messages, $messages);
               $this->_setCause($this->_fraudProtections[get_class($protection)]['slug']);
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
            }
         }
      }
      $this->_clearStorage();
      // Дебажим
      if (null !== $this->firephp) {
         $this->firephp->log($this->_messages, 'Fraud messages');
      }
      return $result;
   }

   /**
    * Установка clickid
    *
    * @param string $clickid
    */
   static public function setClickId($clickid) {
      self::$clickid = $clickid;
   }

   /**
    * Инициализация хранилища
    *
    */
   private function _initStorage() {
      //
   }

   /**
    * Сохранение в хранилище данных пользователя
    * _SERVER, _COOKIE, _GET, _POST
    *
    */
   private function _storeVisitorData() {
      $cache = self::getCache();
      $visitor_data = array(
         '_SERVER' => $_SERVER,
         '_COOKIE' => $_COOKIE,
         '_GET'    => $_GET,
         '_POST'   => $_POST
      );
      $click_storage = array();
      if ($this->_isStoredVisitorData()) {
         if (false !== ($storage = $cache->load(self::getCacheId()))) {
            $click_storage = $storage;
         }
      }
      $click_storage['visitor_data'] = $visitor_data;
      $cache->save($click_storage, self::getCacheId());
   }

   /**
    * Восстановление из хранилища данных пользователя
    * _SERVER, _COOKIE, _GET, _POST
    *
    */
   private function _restoreVisitorData() {
      if ($this->_isStoredVisitorData()) {
         $cache = self::getCache();
         if (false !== ($storage = $cache->load(self::getCacheId()))) {
            // Восстанавливаем глобальные массивы
            $this->CI->input->set_storage($storage['visitor_data']);
         }
      }
   }

   /**
    * Проверка на доступность в хранилище данных пользователя
    *
    * @return bool
    */
   private function _isStoredVisitorData() {
      $cache = self::getCache();
      if (false !== ($storage = $cache->load(self::getCacheId()))) {
         if (isset($storage['visitor_data']) && !empty($storage['visitor_data'])) {
            return true;
         }
      }
      return false;
   }

   /**
    * Сохранение в хранилище очередного protection
    *
    * @param mixed $protection
    */
   private function _storeProtection($protection = null) {
      $cache = self::getCache();
      $click_storage = array();
      if (false !== ($storage = $cache->load(self::getCacheId()))) {
         $click_storage = $storage;
      }
      if (null !== $protection) {
         $click_storage['protection'] = $protection;
      } elseif (isset($click_storage['protection'])) {
         unset($click_storage['protection']);
      }
      $click_storage['current_status'] = $this->_status;
      $click_storage['current_cause'] = $this->_cause;
      $cache->save($click_storage, self::getCacheId());
   }

   /**
    * Получение сохраненного в хранилище протекшена
    *
    * @return mixed
    */
   private function _getStoredProtection() {
      $cache = self::getCache();
      if (false !== ($storage = $cache->load(self::getCacheId()))) {
         if (isset($storage['current_status'])) {
            $this->_setStatus($storage['current_status']);
         }
         if (isset($storage['current_cause'])) {
            $this->_setCause($storage['current_cause']);
         }
         if (isset($storage['protection'])) {
            return $storage['protection'];
         }
      }
      return false;
   }

   /**
    * Очищение хранилища данных
    *
    */
   private function _clearStorage() {
      $cache = self::getCache();
      $cache->remove(self::getCacheId());
   }

   /**
    * Редирект на нужный урл
    *
    * @param string $url
    */
   private function _redirect($url) {
      header('Location: ' . $url);
      exit;
   }
   
   /**
    * Get cache object
    * @return Zend_Cache
    */
   static private function getCache() {
      $CI =& get_instance();
      $feEngine = $CI->config->item('protection_click_frontend_engine');
      $feOptions = $CI->config->item('protection_click_frontend');
      $beEngine = $CI->config->item('protection_click_backend_engine');
      $beOptions = $CI->config->item('protection_click_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      return Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   }
   
   /**
    * Get cache id
    */
   static private function getCacheId() {
      return 'click_storage_' . md5(self::$clickid);
   }

   /**
    * Установка пользовательских данных в хранилище
    *
    * @param string $key
    * @param mixed $value
    */
   static public function storeUserData($key, $value = null) {
      $cache = self::getCache();
      $click_storage = array();
      if (false !== ($storage = $cache->load(self::getCacheId()))) {
         $click_storage = $storage;
      }
      if (!isset($click_storage['user_data'])) {
         $click_storage['user_data'] = array();
      }
      if (null !== $value) {
         $click_storage['user_data'][$key] = $value;
      } elseif (isset($click_storage['user_data'][$key])) {
         unset($click_storage['user_data'][$key]);
      }
      $cache->save($click_storage, self::getCacheId());
   }

   /**
    * Получение пользовательских данных из хранилища
    *
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   static public function getUserData($key, $default = null) {
      $cache = self::getCache();
      $click_storage = array();
      if (false !== ($storage = $cache->load(self::getCacheId()))) {
         $click_storage = $storage;
      }
      if (isset($click_storage['user_data']) && isset($click_storage['user_data'][$key])) {
         return $click_storage['user_data'][$key];
      }
      return $default;
   }

}
