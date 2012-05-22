<?php

/**
 * Firewall Protection
 *
 */
class Sppc_Protection_Firewall extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке об отклоненном брэндмауером айпишнике
    *
    */
   const FIREWALL_REJECT = 'rejected_by_Firewall';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::FIREWALL_REJECT => "ip address '%value%' is rejected by firewall"
   );
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    */
   public function isValid($value = null) {
      if (is_null($value)) {
         // Если не передан ip, тогда получаем ip из параметров
         $parameters = Sppc_Protection_Parameters::getInstance();
         $value = $parameters->getParameter('ip_address');
      }
      $CI =& get_instance();
      $CI->load->helper('location');
      
      $this->_setValue($value);
      $ip = iptolong($this->_value);
      
      $CI->db->from('fraud_firewall')
         ->where('ip_start <=', $ip)
         ->where('ip_finish >=', $ip);
      if (0 < $CI->db->count_all_results()) {
         $this->_error();
         return false;
      }
      return true;
   }
   
}
