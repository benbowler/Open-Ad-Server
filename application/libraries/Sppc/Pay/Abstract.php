<?php

/**
 * Абстрактный класс для работы с деньгами
 *
 */
abstract class Sppc_Pay_Abstract {
   
   /**
    * Использовать абсолютные величины
    *
    */
   const UNIT_ABSOLUTE = 'absolute';
   
   /**
    * Использовать процентные величины
    *
    */
   const UNIT_PERCENT = 'percent';
   
   /**
    * Точность расчетов
    *
    */
   const PRECISION = 4;
   
   protected $_statFields = array();
   
   /**
    * Pocket
    *
    * @var float
    */
   public $value = 0E0;
   
   /**
    * Конструктор класса
    *
    * @param mixed $value
    */
   public function __construct($value = null) {
      if (!is_null($value)) {
         $this->setValue($value);
      }
      $this->_init();
   }
   
   /**
    * Метод установки баланса
    *
    * @param float $value
    */
   public function setValue($value) {
      $this->value = (float) $value;
   }
   
   /**
    * Метод инициализации начального значения
    *
    */
   protected function _init() {
      
   }
   
   /**
    * Метод передачи денег
    *
    * @param Sppc_Pay_Abstract $obj
    * @param float $value
    * @param string $unit
    */
   public function gives(Sppc_Pay_Abstract $obj, $value, $unit = self::UNIT_ABSOLUTE) {
      if ($unit === self::UNIT_PERCENT) {
         $value = round($this->value * $value / 100, self::PRECISION);
      }
      $this->value -= $value;
      $obj->value += $value;
   }
   
   /**
    * Метод получения денег
    *
    * @param Sppc_Pay_Abstract $obj
    * @param float $value
    * @param string $unit
    */
   public function takes(Sppc_Pay_Abstract $obj, $value, $unit = self::UNIT_ABSOLUTE) {
      if ($unit === self::UNIT_PERCENT) {
         $value = round($obj->value * $value / 100, self::PRECISION);
      }
      $this->value += $value;
      $obj->value -= $value;
   }
   
   /**
    * Метод получения денег
    *
    * @param string $unit
    * @param mixed $percent
    * @return float
    */
   public function getValue($unit = self::UNIT_ABSOLUTE, $percent = null) {
      if ($unit === self::UNIT_ABSOLUTE) {
         return $this->value;
      }
      $value = round($this->value * $percent / 100, self::PRECISION);
      return $value;
   }
   
   public function getStatFields() {
      return $this->_statFields;
   }
}