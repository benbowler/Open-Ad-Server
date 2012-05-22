<?php

/**
 * Proxy Protection
 *
 */
class Sppc_Protection_Proxy extends Sppc_Protection_Abstract {
   
   /**
    * Ключи к ошибке об отклоненном proxy
    *
    */
   const PROXY_NOT_ALLOWED = 'proxy_not_allowed';
   
   const TRANSPARENT_PROXY_NOT_ALLOWED = 'transparent_proxy_not_allowed';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::PROXY_NOT_ALLOWED => "proxy '%ip%' is not allowed",
      self::TRANSPARENT_PROXY_NOT_ALLOWED => "transparent proxy '%ip_proxy%' is not allowed"
   );
   
   /**
    * @see Sppc_Protection_Abstract::$_messageVariables
    */
   protected $_messageVariables = array(
      'ip'       => '_ip',
      'ip_proxy' => '_ip_proxy',
   );

   protected $_ip_proxy = '';
   
   protected $_ip = '';
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    *
    * @param $value array with keys 'ip' (string) & 'proxy_ip' (string)
    */
   public function isValid($value = null) {
      $this->_setValue($value);

      $CI = & get_instance();
      $CI->load->helper('location');
      
      // Проверяем на anonymous proxy
      $CI->db->from('fraud_proxies')
         ->where('ip_start <=', ip2long($this->_ip))
         ->where('ip_finish >=', ip2long($this->_ip));
      if (0 < $CI->db->count_all_results()) {
         $this->_error(self::PROXY_NOT_ALLOWED);
         return false;
      }
      $CI->db->from('fraud_proxies')
         ->where('(ip_start-4294967296) <=', ip2long($this->_ip))
         ->where('(ip_finish-4294967296) >=', ip2long($this->_ip));
      if (0 < $CI->db->count_all_results()) {
         $this->_error(self::PROXY_NOT_ALLOWED);
         return false;
      }
      
      // Проверяем на transparent proxy
      if ('true' == $this->_getSetting('block_transparent_clicks', 'false') && 0 < iptolong($this->_ip_proxy)) {
         $allowed = false;
         if ('true' == $this->_getSetting('allowed_proxy_clicks', 'true')) {
            // Проверяем на нахождение в разрешенном листе
            $CI->db->from('fraud_allowed')
               ->where('ip_start <=', ip2long($this->_ip_proxy))
               ->where('ip_finish >=', ip2long($this->_ip_proxy));
            if (0 < $CI->db->count_all_results()) {
               $allowed = true;
            }
            $CI->db->from('fraud_allowed')
               ->where('(ip_start-4294967296) <=', ip2long($this->_ip_proxy))
               ->where('(ip_finish-4294967296) >=', ip2long($this->_ip_proxy));
            if (0 < $CI->db->count_all_results()) {
               $allowed |= true;
            }
         }
         if (!$allowed) {
            $this->_error(self::TRANSPARENT_PROXY_NOT_ALLOWED);
            return false;
         }
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
      
      // Получаем айпи визитора
      if (isset($value['ip'])) {
         $this->_ip = $value['ip'];
      } else {
         $this->_ip = $parameters->getParameter('ip_address');
      }
      
      // Получаем айпи прокси визитора
      if (isset($value['ip_proxy'])) {
         $this->_ip_proxy = $value['ip_proxy'];
      } else {
         $this->_ip_proxy = $parameters->getParameter('ip_address_proxy');
      }
      
      // Обнуляем массив сообщений об ошибках
      $this->_messages = array();
   }
   
}
