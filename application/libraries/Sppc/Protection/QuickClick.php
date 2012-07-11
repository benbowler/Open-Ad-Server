<?php

/**
 * Quick click Protection
 *
 */
class Sppc_Protection_QuickClick extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке о малом времени между кликом и поиском
    *
    */
   const MIN_CLICK_TIME = 'min_click_time';
   
   const MAX_CLICK_TIME = 'max_click_time';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::MIN_CLICK_TIME => "too little time between the search and click",
      self::MAX_CLICK_TIME => "too large time between the search and click",
   );
   
   protected $_time_click = 0;
   
   protected $_time_search = 0;
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    *
    * @param $value array
    */
   public function isValid($value = null) {
      // Устанавливаем значение
      $this->_setValue($value);
      
      // Получаем параметры
      $min_interval = $this->_getSetting('MinimumIntervalSearchClick', 0);
      $max_interval = $this->_getSetting('MaximumIntervalSearchClick', 7200);
      
      
      // Вычисляем время между поиском и кликом
      $interval = $this->_time_click - $this->_time_search;
      
      if ($interval < $min_interval) {
         $this->_error(self::MIN_CLICK_TIME);
         return false;
      }
      
      if ($interval > $max_interval && 0 < $max_interval) {
         $this->_error(self::MAX_CLICK_TIME);
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
      if (isset($value['time_click'])) {
         $this->_time_click = $value['time_click'];
      } else {
         $this->_time_click = $parameters->getParameter('datetime');
      }
      
      // Получаем время поиска
      if (isset($value['time_search'])) {
         $this->_time_search = $value['time_search'];
      } else {
         $this->_time_search = $parameters->getParameter('search_datetime');
      }
      
      // Обнуляем массив сообщений об ошибках
      $this->_messages = array();
   }
   
}
