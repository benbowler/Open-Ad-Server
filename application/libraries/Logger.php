<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс для логирования
 * 
 * @author Gennadiy Kozlenko
 */
class Logger {
   
   private $instance = null;
   
   /**
    * Названия уровней ошибок
    *
    * @var array
    */
   private $level_names = array(
      LOG_LEVEL_ERROR => 'Error',
      LOG_LEVEL_DEBUG => 'Debug'
   );
   
   /**
    * Конструктор класса
    *
    * @return Logger
    */
   function Logger() {
      $this->instance =& get_instance();
      // Подгружаем конфиг
      $this->instance->load->config('logger');
   }
   
   /**
    * Логирование сообщения
    *
    * @param string $message
    * @param string $module
    * @param int $level
    * @return bool
    */
   function log($message, $module, $level = LOG_LEVEL_DEBUG) {
      if (!in_array($module, $this->instance->config->item('log_modules'))) {
         // Не логируем потом, что модуль отключен
         return false;
      }
      if ($level > $this->instance->config->item('log_level')) {
         // Не логируем потому, что не тот уровень
         return false;
      }
      if (false !== ($fh = fopen($this->_get_log_file($module), 'a'))) {
         flock($fh, LOCK_EX);
         $level_name = isset($this->level_names[$level]) ? $this->level_names[$level] : 'Unknown';
         fwrite($fh, sprintf("[%s]\t%s\t%s\n", date('H:i:s'), $level_name, $message));
         flock($fh, LOCK_UN);
         fclose($fh);
         return true;
      }
      return false;
   }
   
   /**
    * Логирование сообщения об ошибке
    *
    * @param string $message
    * @param string $module
    * @return bool
    */
   function error($message, $module) {
      return $this->log($message, $module, LOG_LEVEL_ERROR);
   }
   
   /**
    * Логирование дебажного сообщения
    *
    * @param string $message
    * @param string $module
    * @return bool
    */
   function debug($message, $module) {
      return $this->log($message, $module, LOG_LEVEL_DEBUG);
   }
   
   /**
    * Метод получения названия файла для логирования
    *
    * @param string $module
    * @return string
    */
   function _get_log_file($module) {
      $file_name = $this->instance->config->item('log_path') . $module;
      if (!file_exists($file_name)) {
         // Создаем каталог для модуля
         mkdir($file_name);
         @chmod($file_name, 0777);
      }
      $file_name .= '/' . date('Y-m-d') . '.log';
      return $file_name;
   }
   
}
