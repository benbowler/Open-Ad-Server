<?php

/**
 * IP compare Protection
 *
 */
class Sppc_Protection_IpCompare extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке о несоответствии IP поиска и IP клика
    *
    */
   const SEARCH_CLICK_NO_MATCH = 'search_ip_no_match_click_ip';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::SEARCH_CLICK_NO_MATCH => "click ip address '%click_ip%' doesn't match search ip adress '%search_ip%'"
   );
   
   protected $_messageVariables = array(
      'search_ip' => '_ip_search',
      'click_ip' => '_ip_click'
   );
   
   protected $_ip_click = '';
   
   protected $_ip_search = '';
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    *
    * @param $value array
    */
   public function isValid($value = null) {
      $this->_setValue($value);
      
      if ('true' == $this->_getSetting('SearchClickIpMatch', 'true') && $this->_ip_click != $this->_ip_search) {
         $this->_error();
         return false;
      }
      return true;
   }
   
   /**
    * Установка значения для проверки
    *
    * @param mixed $value
    */
   protected function _setValue($value) {
      // Параметры
      $parameters = Sppc_Protection_Parameters::getInstance();
      
      // Получаем время клика
      if (isset($value['click_ip'])) {
         $this->_ip_click = $value['click_ip'];
      } else {
         $this->_ip_click = $parameters->getParameter('ip_address');
      }
      
      // Получаем время поиска
      if (isset($value['search_ip'])) {
         $this->_ip_search = $value['search_ip'];
      } else {
         $this->_ip_search = $parameters->getParameter('search_ip_address');
      }
      
      // Обнуляем массив сообщений об ошибках
      $this->_messages = array();
   }
   
}
