<?php

/**
 * Search Click Protection
 *
 */
class Sppc_Protection_SearchClick extends Sppc_Protection_Abstract {
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    */
   public function isValid($value = null) {
      // Создаем валидатор Sppc_Protection_IpCompare
      $ipCompareProtection = new Sppc_Protection_IpCompare();
      $ipCompareProtection->setSettings($this->_settings);
      // Создаем валидатор Referer
      $refererProtection = new Sppc_Protection_Referer();
      $refererProtection->setSettings($this->_settings);
      // Валидируем
      if (!$ipCompareProtection->isValid($value) || !$refererProtection->isValid($value)) {
         // Получаем сообщения об ошибках
         $this->_messages = array_merge($ipCompareProtection->getMessages(), $refererProtection->getMessages());
         return false;
      }
      return true;
   }
   
}
