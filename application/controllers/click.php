<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MY_Controller.php';

/**
 * Контроллер обработки клика
 *
 * @author Gennadiy Kozlenko
 */
class Click extends MY_Controller {
   
   /**
    * Идентификатор клика
    *
    * @var string
    */
   var $clickid = '';

   /**
    * Конструктор класса
    *
    * @return Click
    */
   function Click() {
      parent::__construct();
      $this->load->library('session');
      $this->load->model('global_variables');
      $this->clickid = $this->input->get('id');
      // Security
      header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
   }
   
   /**
    * Метод по умолчанию
    *
    */
   function index() {
 
      $this->load->library('click_builder');
      $this->click_builder->setClickId($this->clickid);
      
      
      $url = '';
      if ($this->click_builder->click()) {
         $url = $this->click_builder->getUrl();
      }

      if (empty($url)) {
      
         $url = base_url() . 'deny';
      }
      // Редиректим пользователя на нужный урл
      header('Location: ' . $url);
      exit;
   }
   
}
