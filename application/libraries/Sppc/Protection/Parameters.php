<?php

/**
 * Класс параметров для protection
 * Singleton
 *
 */
class Sppc_Protection_Parameters {
   
   /**
    * Экземпляр класса
    *
    * @var Sppc_Protection_Parameters
    */
   private static $instance;
   
   /**
    * Массив параметров
    *
    * @var array
    */
   private $parameters = array();
   
   /**
    * Получение экземпляра класса
    *
    * @param array $parameters
    * @return Sppc_Protection_Parameters
    */
   public static function getInstance() {
      if (null === self::$instance) {
         self::$instance = new Sppc_Protection_Parameters();
      }
      return self::$instance;
   }

   /**
    * Приватьный конструктор класса
    *
    * @param array $parameters
    */
   private function __construct() {
      // Nothing todo
   }
   
   /**
    * Установка параметров
    *
    * @param array $parameters
    */
   public function setParameters($parameters) {
      $this->parameters = $parameters;
   }
   
   /**
    * Получение параметров
    *
    * @return array
    */
   public function getParameters() {
      return $this->parameters;
   }
   
   /**
    * Получение параметра
    *
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
   public function getParameter($name, $default = null) {
      if (isset($this->parameters[$name])) {
         return $this->parameters[$name];
      }
      return $default;
   }
   
   /**
    * Установка параметра
    *
    * @param string $name
    * @param mixed $value
    */
   public function setParameter($name, $value) {
      $this->parameters[$name] = $value;
   }
   
}
