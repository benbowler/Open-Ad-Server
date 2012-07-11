<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!defined('_LOGGER_CONFIG')) {
   /**
    * Модули, использующие логирование
    * 
    */
   
   // Модуль поиска
   define('LOG_MODULE_SEARCH', 'search');
   
   // Модуль клика
   define('LOG_MODULE_CLICK', 'click');
   
   // Модуль платежных шлюзов
   define('LOG_MODULE_PGATEWAY', 'payment_gateway');
   
   /**
    * Уровни логирования
    *
    */
   
   // Не логировать
   define('LOG_LEVEL_NONE', 0);
   
   // Логировать ошибки
   define('LOG_LEVEL_ERROR', 1);
   
   // Логировать дебажную инфу - читай все
   define('LOG_LEVEL_DEBUG', 2);
   
   define('_LOGGER_CONFIG', 1);
}

/**
 * Путь к папке с файлами
 * 
 */
$config['log_path'] = BASEPATH . 'files/logs/';

/**
 * Список модулей, которым разрешено логирование
 * 
 */
$config['log_modules'] = array(
   LOG_MODULE_SEARCH,
   LOG_MODULE_CLICK,
   LOG_MODULE_PGATEWAY
);

/**
 * Уровень логирования
 * 
 */
$config['log_level'] = LOG_LEVEL_DEBUG;
