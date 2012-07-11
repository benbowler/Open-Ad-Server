<?php

/**
 * Класс протекшенов поиска
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Protection_Target_Search extends Sppc_Protection_Target_Abstract 
      implements Sppc_Protection_Handler_Interface {
   
   /**
    * Engine поиска
    *
    */
   const ENGINE_SEARCH = 'search';
   
   /**
    * Конструктор класса
    *
    */
   function __construct() {
      $this->_setEngine(self::ENGINE_SEARCH);
      parent::__construct();
   }
   
   /**
    * @see Sppc_Protection_Handler_Interface::processResults
    *
    * @param array $results
    */
   function processResults(&$results) {
      // Бежим по всем протекшенам
      foreach ($this->_protections as $element) {
         /* @var $protection Sppc_Protection_Handler_Interface */
         $protection = $element['instance'];
         if ($protection instanceof Sppc_Protection_Handler_Interface) {
            $protection->processResults(&$results);
         }
      }
   }
   
}
