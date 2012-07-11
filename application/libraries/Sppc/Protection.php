<?php

/**
 * Класс работы с протекшенами
 *
 */
class Sppc_Protection implements Sppc_Protection_Interface {
   
   /**
    * Статус pay
    *
    */
   const PAY = 'pay';
   
   /**
    * Статус free
    *
    */
   const FREE = 'free';
   
   /**
    * Статус deny
    *
    */
   const DENY = 'deny';
   
   /**
    * Статус
    *
    * @var string
    */
   protected $_status = self::DENY;
   
   /**
    * Массив цепочек валидаторов протекшенов
    *
    * @var array
    */
   protected $_protections = array();
   
   /**
    * Массив сообщений об ошибках
    *
    * @var array
    */
   protected $_messages = array();
   
   /**
    * Дебаггер
    *
    * @var FirePHP
    */
   protected $firephp = null;
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      $CI =& get_instance();
      if ($CI->config->item('debug_mode')) {
         $CI->load->helper('firephp');
         $this->firephp =& get_firephp();
      }
   }
   
   /**
    * Добавление валидатора протекшена в цепочку
    *
    * @param Protection_Abstract $protection
    * @param bool $breakChainOnFailure
    * @return Sppc_Protection
    */
   public function addProtection(Sppc_Protection_Abstract $protection, $breakChainOnFailure = true) {
      $this->_protections[] = array(
         'instance' => $protection, 
         'breakChainOnFailure' => (boolean) $breakChainOnFailure
      );
      return $this;
   }
   
   /**
    * @see Sppc_Protection_Interface::isValid
    */
   public function isValid($value = null) {
      $this->_setStatus(self::DENY);
      $this->_messages = array();
      
      $result = true;
      foreach ($this->_protections as $element) {
         $protection = $element['instance'];
         if ($protection->isValid($value)) {
            continue;
         }
         $result = false;
         $messages = $protection->getMessages();
         $this->_messages = array_merge($this->_messages, $messages);
         if ($element['breakChainOnFailure']) {
            break;
         }
      }
      return $result;
   }
   
   /**
    * @see Sppc_Protection_Interface::getMessages
    */
   public function getMessages() {
      return $this->_messages;
   }
   
   /**
    * Получение статуса
    *
    * @return string
    */
   public function getStatus() {
      return $this->_status;
   }

   /**
    * Установка статуса
    *
    * @param string $status
    */
   protected function _setStatus($status) {
      $this->_status = $status;
   }
   
}
