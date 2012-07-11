<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MY_Controller.php';

/**
 * Контроллер обработки deny страницы
 *
 * @author Gennadiy Kozlenko
 */
class Deny extends MY_Controller {
   
   /**
    * Конструктор класса
    *
    * @return Deny
    */
   function Deny() {
      parent::__construct();
      $this->load->library('parser');
   }
   
   /**
    * Метод по умолчанию
    *
    */
   function index() {
      $data = array();
      $this->parser->parse('show_ads/deny.html', $data);
   }
   
}
