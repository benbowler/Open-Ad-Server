<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Помощник для получения данных по расположению посетителя по IP
 *
 * @author Gennadiy Kozlenko
 */

/**
 * Получение страны по айпишнику
 *
 * @param string $ip_address
 */
function get_country_by_ip($ip_address) {
   $obj =& get_instance();
   static $gi = null;
   if (null === $gi) {
      require_once APPPATH . 'libraries/geoip.php';
      $gi = geoip_open($obj->config->item('path_to_files') . 'GeoIP.dat', GEOIP_STANDARD);
   }
   return geoip_country_code_by_addr($gi, $ip_address);
}

/**
 * Получение названия страны по айпишнику
 *
 * @param string $ip_address
 */
function get_country_name_by_ip($ip_address) {
   $obj =& get_instance();
   require_once APPPATH . 'libraries/geoip.php';
   $gi = geoip_open($obj->config->item('path_to_files') . 'GeoIP.dat', GEOIP_STANDARD);
   return geoip_country_name_by_addr($gi, $ip_address);
}

/**
 * Получение IP-адреса
 *
 * @return string
 */
function get_ip_address() {
   $obj =& get_instance();
   
   $ip_address = $obj->input->storage_server('HTTP_CLIENT_IP');
   if (!$obj->input->valid_ip($ip_address)) {
      $ip_address = $obj->input->storage_server('HTTP_X_FORWARDED_FOR');
      if (!$obj->input->valid_ip($ip_address)) {
         $ip_address = $obj->input->storage_server('REMOTE_ADDR');
      }
   }
   
   // Проверяем на валидность
   if (!$obj->input->valid_ip($ip_address)) {
      $ip_address = '';
   }
   
   return $ip_address;
}

/**
 * Получение IP-адреса проскси.
 * Возвращает IP-адрес прокси или пустую строку
 *
 * @return string
 */
function get_proxy_ip_address() {
   $obj =& get_instance();
   
   $ip_address = $obj->input->storage_server('REMOTE_ADDR');
   
   // Проверяем на валидность
   if (!$obj->input->valid_ip($ip_address)) {
      $ip_address = '';
   }
   
   if ($ip_address == get_ip_address()) {
      $ip_address = '';
   }
   
   return $ip_address;
}

/**
 * Враппер для функции ip2long
 *
 * @param $ip_address string
 * @return unknown_type
 */
function iptolong($ip_address) {
   return sprintf('%u', ip2long($ip_address));
}

/**
 * Враппер для функции long2ip
 *
 * @param  $long_ip
 * @return ip_address string
 */
function longtoip($long_ip) {
   return long2ip($long_ip);
}

/**
 * Get countries
 *
 * @return array
 */
function get_countries() {
   $obj =& get_instance();
   $countries = array();
   $feEngine = $obj->config->item('countries_frontend_engine');
   $feOptions = $obj->config->item('countries_frontend');
   $beEngine = $obj->config->item('countries_backend_engine');
   $beOptions = $obj->config->item('countries_backend');
   if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
      mkdir($beOptions['cache_dir']);
      chmod($beOptions['cache_dir'], 0777);
   }
   $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   if (false === ($countries = $cache->load('countries'))) {
      $countries = array();
      $obj->db->select('`iso`, `name`, `banned`', false)
         ->from('countries');
      $query = $obj->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result_array() as $row) {
            $row['banned'] = 'true' == $row['banned'];
            array_push($countries, $row);
         }
      }
      $cache->save($countries, 'countries');
   }
   return $countries;
}
