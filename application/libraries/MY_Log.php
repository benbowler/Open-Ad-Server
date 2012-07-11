<?php // -*- coding: UTF-8 -*-
/**
 * Класс, расширяющий базовый функционал CI_Input
 *
 */
class MY_Log extends CI_Log {

   var $_levels   = array('SPPC' => '0.5', 'ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');
   
   var $_uniq = null;
   
   /**
    * Constructor
    *
    * @access  public
    */
   function MY_Log() {
      $this->_uniq = uniqid(time());
      parent::__construct();            
   }
   
   /**
    * Write Log File
    *
    * Generally this function will be called using the global log_message() function
    *
    * @access  public
    * @param   string   the error level
    * @param   string   the error message
    * @param   bool  whether the error is a native PHP error
    * @return  bool
    */      
   function write_log($level = 'error', $msg, $php_error = FALSE)
   {     
      return parent::write_log($level, '(uniq=' . $this->_uniq . ') ' . $msg, $php_error);
   }
   
}
