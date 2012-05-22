<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * URI Class
 * 
 * @author Gennadiy Kozlenko
 */
class MY_URI extends CI_URI {
    
   /**
    * Get the URI String
    *
    * @access  private
    * @return  string
    */
   function _fetch_uri_string() {
      if (strtoupper($this->config->item('uri_protocol')) == 'AUTO') {
         if (isset($_SERVER['PATH_INFO']) && '' != trim($_SERVER['PATH_INFO'], '/')) {
            $this->uri_string = trim(str_replace('index.php', '', $_SERVER['PATH_INFO']), '/');
            return;
         }
         if (isset($_SERVER['ORIG_PATH_INFO']) && '' != trim($_SERVER['ORIG_PATH_INFO'], '/')) {
            $this->uri_string = trim(str_replace('index.php', '', $_SERVER['ORIG_PATH_INFO']), '/');
            return;
         }
         if (isset($_SERVER['REQUEST_URI']) && '' != trim($_SERVER['REQUEST_URI'], '/')) {
            $this->uri_string = trim(str_replace('index.php', '', $this->_parse_request_uri()), '/');
            return;
         }
         $this->uri_string = '';
      } else {
         $uri = strtoupper($this->config->item('uri_protocol'));
         if ($uri == 'REQUEST_URI') {
            $this->uri_string = trim(str_replace('index.php', '', $this->_parse_request_uri()), '/');
            return;
         }
         $this->uri_string = trim(str_replace('index.php', '', (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri)), '/');
      }
      if ($this->uri_string == '/') {
         $this->uri_string = '';
      }
   }
   
   /**
    * Parse the REQUEST_URI
    *
    * Due to the way REQUEST_URI works it usually contains path info
    * that makes it unusable as URI data.  We'll trim off the unnecessary
    * data, hopefully arriving at a valid URI that we can use.
    *
    * @access  private
    * @return  string
    */   
   function _parse_request_uri() {
      $uri_string = '';
      if (isset($_SERVER['REQUEST_URI']) && '' != trim($_SERVER['REQUEST_URI'], '/')) {
         $path = $_SERVER['REQUEST_URI'];
         if (false !== strstr($path, '?')) {
            $info = explode('?', $path);
            $path = current($info);
         }
         $fPath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(str_replace('\\', '/', FCPATH)));
         if (!empty($fPath)) {
            $path = str_replace($fPath, '', $path);
         }
         $uri_string = trim($path, '/');
      }
      return $uri_string;
   }

}

