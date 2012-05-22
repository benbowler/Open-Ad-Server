<?php

/**
 * Country Protection
 *
 */
class Sppc_Protection_Country extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке о запрещенной стране
    *
    */
   const WRONG_COUNTRY = 'wrong_Country';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::WRONG_COUNTRY => "country '%value%' is blocked by admin"
   );
   
   /**
    * Массив разрешенных стран
    *
    * @var array
    */
   private $_allowedCountriesList = array();
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      $this->_loadAllowedCountriesList();
   }
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    */
   public function isValid($value = null) {
      if (is_null($value)) {
         // Если не передана страна, тогда получаем страну из параметров
         $parameters = Sppc_Protection_Parameters::getInstance();
         $value = $parameters->getParameter('country');
      }
      $this->_setValue($value);
      
      // Проверяем страну на принадлежность к списку разрешенных стран
      if (!in_array($this->_value, $this->_allowedCountriesList)) {
         $this->_error();
         return false;
      }
      return true;
   }
   
   /**
    * Загрузка списка разрешенных стран
    *
    */
   private function _loadAllowedCountriesList() {
      $this->_allowedCountriesList = array();
      $CI =& get_instance();
      $CI->load->helper('location');
      $countries = get_countries();
      foreach ($countries as $country) {
         if (!$country['banned']) {
            array_push($this->_allowedCountriesList, $country['iso']);
         }
      }
   }
   
}
