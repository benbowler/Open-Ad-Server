<?php

/**
 * Referer Protection
 *
 */
class Sppc_Protection_Referer extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке об пустом referer
    *
    */
   const EMPTY_REFERER = 'empty_referer';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::EMPTY_REFERER => "referer is empty"
   );
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    *
    * @param $value string
    */
   public function isValid($value = null) {
      if (null === $value) {
         // Если не передан referer, тогда берем referer из параметров
         $parameters = Sppc_Protection_Parameters::getInstance();
         $value = $parameters->getParameter('referer');
      }
      $this->_setValue($value);
      
      if ('true' == $this->_getSetting('ReferrerNonEmpty', 'true') && empty($this->_value)) {
         $this->_error();
         return false;
      }
      return true;
   }
   
}
