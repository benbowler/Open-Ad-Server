<?php

/**
 * Абстрактный класс валидатора протекшенов
 *
 */
abstract class Sppc_Protection_Abstract implements Sppc_Protection_Interface {
   
   /**
    * Значение на проверку
    *
    * @var mixed
    */
   protected $_value = null;
   
   /**
    * Массив сообщений об ошибках
    *
    * @var array
    */
   protected $_messages = array();
   
   /**
    * Массив шаблонов сообщений об ошибках
    *
    * @var array
    */
   protected $_messageTemplates = array();
   
   /**
    * Маппинг переменных к полям класса для замены в шаблонах сообщений об ошибках
    *
    * @var array
    */
   protected $_messageVariables = array();
   
   /**
    * Массив настроек протекшена
    *
    * @var array
    */
   protected $_settings = array();
   
   /**
    * Установка значения для проверки
    *
    * @param mixed $value
    */
   protected function _setValue($value) {
      $this->_value = $value;
      $this->_messages = array();
   }
   
   /**
    * Получение настройки
    *
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
   protected function _getSetting($name, $default = null) {
      if (isset($this->_settings[$name])) {
         return $this->_settings[$name];
      }
      return $default;
   }
   
   /**
    * @see Sppc_Protection_Interface::getMessages
    */
   public function getMessages() {
      return $this->_messages;
   }
   
   /**
    * Установка настроек
    *
    * @param array $settings
    */
   public function setSettings($settings) {
      $this->_settings = $settings;
   }
   
   /**
    * Установка настройки
    *
    * @param string $name
    * @param mixed $value
    */
   public function setSetting($name, $value) {
      $this->_settings[$name] = $value;
   }
   
   /**
    * Подготовка сообщения об ошибке
    *
    * @param string $messageKey
    * @param mixed $value
    * @return mixed
    */
   protected function _createMessage($messageKey, $value) {
      if (!isset($this->_messageTemplates[$messageKey])) {
         return null;
      }
      
      $message = $this->_messageTemplates[$messageKey];
      
      if (is_object($value)) {
         if (!in_array('__toString', get_class_methods($value))) {
            $value = get_class($value) . ' object';
         } else {
            $value = $value->__toString();
         }
      } else {
         $value = (string) $value;
      }
      
      $message = str_replace('%value%', (string) $value, $message);
      foreach ($this->_messageVariables as $ident => $property) {
         $message = str_replace("%$ident%", (string) $this->$property, $message);
      }
      return $message;
   }
   
   /**
    * Добавление сообщения об ошибке в пул ошибок
    *
    * @param string $messageKey
    * @param mixed $value
    */
   protected function _error($messageKey = null, $value = null) {
      if ($messageKey === null) {
         $keys = array_keys($this->_messageTemplates);
         $messageKey = current($keys);
      }
      if ($value === null) {
         $value = $this->_value;
      }
      $this->_messages[$messageKey] = $this->_createMessage($messageKey, $value);
   }

}