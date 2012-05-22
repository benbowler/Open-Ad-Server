<?php

/**
 * Initialize plugins hook system
 */
function init_plugins() {
   include APPPATH . 'config/cache.php';
   $feEngine = $config['plugins_frontend_engine'];
   $feOptions = $config['plugins_frontend'];
   $beEngine = $config['plugins_backend_engine'];
   $beOptions = $config['plugins_backend'];
   if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
      mkdir($beOptions['cache_dir']);
      chmod($beOptions['cache_dir'], 0777);
   }
   $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   $pluginsConfigArray = $cache->load('pluginsConfig');
   if (!$pluginsConfigArray) {
      $pluginsConfigArray = array();
      $pluginConfDirHandler = opendir(APPPATH.'config/plugins');
      while (false !== ($pluginConfigFile = readdir($pluginConfDirHandler))) {
         if (preg_match('~^.*?\.ini$~si', $pluginConfigFile)) {
            try {
               $pluginConfig = new Zend_Config_Ini(APPPATH . 'config/plugins/'. $pluginConfigFile, 'plugins');
               $pluginsConfigArray = array_merge_recursive($pluginsConfigArray, $pluginConfig->toArray());
            } catch (Exception $e) {
               
            }
         }
      }
      closedir($pluginConfDirHandler);
      
      $cache->save($pluginsConfigArray, 'pluginsConfig');
   }
   Zend_Registry::getInstance()->set('pluginsConfig', new Zend_Config($pluginsConfigArray));
}