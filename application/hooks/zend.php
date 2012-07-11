<?php

/**
 * Initialize Zend
 */
function init_zend() {
   set_include_path('.' . PATH_SEPARATOR .
      APPPATH . 'libraries' . PATH_SEPARATOR .
      APPPATH . 'models' . PATH_SEPARATOR .
      APPPATH . 'helpers'
   );
   
   require_once 'Zend/Loader/Autoloader.php';
   $autoloader = Zend_Loader_Autoloader::getInstance();
   $autoloader->registerNamespace('Zend')
       ->registerNamespace('Sppc')
       ->registerNamespace('Box')
       ->registerNamespace('Plugin');
}

/**
 * Initizlize Zend DB
 */
function init_zend_db() {
   try {
      include APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';
      $__dbParams = array(
         'host'           => $db[$active_group]['hostname'],
         'username'       => $db[$active_group]['username'],
         'password'       => $db[$active_group]['password'],
         'dbname'         => $db[$active_group]['database'],
         'persistent'     => $db[$active_group]['pconnect']
      );
      $__db = Zend_Db::factory('PDO_MYSQL', $__dbParams);
      
      include APPPATH . 'config/cache.php';
      $feEngine = $config['dbschema_frontend_engine'];
      $feOptions = $config['dbschema_frontend'];
      $beEngine = $config['dbschema_backend_engine'];
      $beOptions = $config['dbschema_backend'];
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
//      var_dump($beOptions['cache_dir']);
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
      Zend_Db_Table_Abstract::setDefaultAdapter($__db);
      Profiler::start($db[$active_group], $__db);
      $__db->query('SET NAMES ' . $db['default']['char_set']);
      $__db->query('SET SQL_MODE = "NO_UNSIGNED_SUBTRACTION"');
   } catch (Exception $e) {
      header("HTTP/1.1 500 Internal Server Error (DB)");
      echo $e->getMessage();
      exit;
   }
}

/**
 * Start Zend Profiler
 */
function start_zend_profiler() {
   include APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';
   if ($db[$active_group]['Zend_Db_Profiler_Firebug']) {
      Profiler::start($db[$active_group], Zend_Db_Table_Abstract::getDefaultAdapter());
   }
}

/**
 * Stop Zend Profiler
 */
function stop_zend_profiler() {
   include APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';
   if ($db[$active_group]['Zend_Db_Profiler_Firebug']) {
      Profiler::stop();
   }
}

/**
 * DB Profiler class
 */
class Profiler {
   
   private static $_response = null;
   
   private static $_channel = null;
   
   private static $_config = null;
   
   public static function start($config, $db) {
      self::$_config = $config;
      if (self::$_config['Zend_Db_Profiler_Firebug'] == false) {
         return;
      }
      $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
      $profiler->setEnabled(true);
      $db->setProfiler($profiler);
      $request = new Zend_Controller_Request_Http();
      self::$_response = new Zend_Controller_Response_Http();
      self::$_channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
      self::$_channel->setRequest($request);
      self::$_channel->setResponse(self::$_response);
      ob_start();
   }
   
   public static function stop() {
      if (self::$_config['Zend_Db_Profiler_Firebug'] == false) {
         return;
      }
      self::$_channel->flush();
      self::$_response->sendHeaders();
   }

}
