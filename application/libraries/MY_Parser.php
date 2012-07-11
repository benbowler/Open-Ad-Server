<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Parser extends CI_Parser {
	
	/**
	 * Parse prepared template
	 * 
	 * @param string $template Template content
	 * @param array $data
	 * @param bool $return
	 */
	function parse_prepared_template($template, $data, $return = FALSE) {
      foreach ($data as $key => $val)
      {
         if (is_array($val))
         {
            $template = $this->_parse_pair($key, $val, $template);      
         }
         else
         {
            $template = $this->_parse_single($key, (string)$val, $template);
         }
      }
      
      if ($return == FALSE)
      {
         $CI->output->append_output($template);
      }
      
      return $template; 
	}
}