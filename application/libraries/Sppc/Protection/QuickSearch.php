<?php

/**
 * Quick search Protection
 *
 */
class Sppc_Protection_QuickSearch extends Sppc_Protection_Abstract {
   
   /**
    * Ключ к ошибке об отклоненном айпишнике
    *
    */
   const QUICK_SEARCH_REJECT = 'rejected_by_Quick_search_filter';
   
   /**
    * @see Sppc_Protection_Abstract::$_messageTemplates
    */
   protected $_messageTemplates = array(
      self::QUICK_SEARCH_REJECT => "ip address '%value%' is rejected by Quick search filter"
   );
   
   /**
    * @see Sppc_Protection_Abstract::isValid()
    *
    * @param $value string
    */
   public function isValid($value = null) {
      if (is_null($value)) {
         // Если не передан ip, тогда берем ip из параметров
         $parameters = Sppc_Protection_Parameters::getInstance();
         $value = $parameters->getParameter('ip_address');
      }
      $this->_setValue($value);
      
      $CI =& get_instance();
      $CI->load->helper('location');
      
      $ip = iptolong($this->_value);
      
      $number = (int) $this->_getSetting('MaximumSearchNumber', 100);
      $period = (int) $this->_getSetting('TimePeriod', 5);
      
      if (0 < $number) {
         // Use database
         $CI->db->insert('fraud_quick_search', array('ip' => $ip, 'search_date' => time()));
         // Чекаем
         $CI->db->where('ip', $ip)
            ->where('search_date >=', time() - $period)
            ->from('fraud_quick_search');
         $count = $CI->db->count_all_results();
         if ($count > $number) {
            $this->_error();
            return false;
         }
      }
      
      return true;
   }
   
}
