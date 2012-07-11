<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Помощник для получения данных о useragent'е посетителя
 *
 * @author Мешков Павел
 */

/**
 * Получение user_agent
 */
function get_ua_agent() {
   $obj =& get_instance();
   return $obj->input->storage_server('HTTP_USER_AGENT');
}

/**
 * Функция получения браузера визитора
 *
 * @return string
 */
function get_ua_browser($user_agent = '') {
   $obj =& get_instance();
   if (empty($user_agent)) {
      $user_agent = $obj->input->storage_server('HTTP_USER_AGENT');
   }
   $browsers = get_browsers();
   // Определяем текущий браузер
   $browser = 'unknown';
   foreach ($browsers as $br) {
      if (preg_match('~' . $br['regexp'] . '~i', $user_agent)) {
         $browser = $br['name'];
         break;
      }
   }
   
   return $browser;
}

/**
 * Get browsers
 *
 * @return array
 */
function get_browsers() {
   $obj =& get_instance();
   $browsers = array();
   $feEngine = $obj->config->item('browsers_frontend_engine');
   $feOptions = $obj->config->item('browsers_frontend');
   $beEngine = $obj->config->item('browsers_backend_engine');
   $beOptions = $obj->config->item('browsers_backend');
   if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
      mkdir($beOptions['cache_dir']);
      chmod($beOptions['cache_dir'], 0777);
   }
   $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   if (false === ($browsers = $cache->load('browsers'))) {
      $browsers = array();
      $obj->db->select('`name`, `regexp`, `banned`', false)
         ->from('browsers')
         ->order_by('position');
      $query = $obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result_array() as $row) {
            $row['banned'] = 'true' == $row['banned'];
            array_push($browsers, $row);
         }
      }
      $cache->save($browsers, 'browsers');
   }
   return $browsers;
}

/**
 * Get languages
 *
 * @return array
 */
function get_languages() {
   $obj =& get_instance();
   $languages = array();
   $feEngine = $obj->config->item('languages_frontend_engine');
   $feOptions = $obj->config->item('languages_frontend');
   $beEngine = $obj->config->item('languages_backend_engine');
   $beOptions = $obj->config->item('languages_backend');
   if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
      mkdir($beOptions['cache_dir']);
      chmod($beOptions['cache_dir'], 0777);
   }
   $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   if (false === ($languages = $cache->load('languages'))) {
      $languages = array();
      $obj->db->select('`iso`, `name`, `banned`', false)
         ->from('languages');
      $query = $obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result_array() as $row) {
            $row['banned'] = 'true' == $row['banned'];
            array_push($languages, $row);
         }
      }
      $cache->save($languages, 'languages');
   }
   return $languages;
}


/**
 * Функция получения языка браузера визитора
 *
 * @return string
 */
function get_ua_language($accept = '') {
   $obj =& get_instance();
   if (empty($accept)) {
      $accept = $obj->input->storage_server('HTTP_ACCEPT_LANGUAGE');
   }
   
   // Получаем список языков
   $languages = array();
   foreach (explode(',', preg_replace('/(;q=.+)/i', '', strtolower($accept))) as $lang) {
      $lang = preg_replace('/(-[a-z]+)/i', '', $lang);
      if (!in_array($lang, $languages)) {
         array_push($languages, $lang);
      }
   }
   
   $language = 'UN';
   if (0 < $languages) {
      $language = strtoupper(array_shift($languages));
   }
   
   return $language;
}

/**
 * Получение реферера
 *
 * @return string
 */
function get_ua_referer() {
   $obj =& get_instance();
   return $obj->input->storage_server('HTTP_REFERER');
}

