<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Buy_Site_Channel extends Parent_controller {
   
   protected $role = "guest";
   
   //protected $menu_item = "Site Directory";
   

   public function __construct() {
      parent::__construct();
      $this->load->helper('cookie');
      $this->load->model('entity');
   }
   
   public function check() {
      $response = array(
         'error_flag' => true, 
         'error_message' => __('Unknown Error')
      );
      
      $site_code = $this->input->post('site_code');
      $program_code = $this->input->post('program_code');
      
      $user_roles = $this->entity->get_roles($this->user_id, 'active');
      if (in_array('advertiser', $user_roles)) {
         $response['error_flag'] = false;
         $response['buy_link'] = $this->site_url . $this->index_page . 'advertiser/buy_site_channel';
         $response['site_code'] = $site_code;
         $response['program_code'] = $program_code;
      } else {
         $cookie = array(
               'name' => 'buy_site_channel_' . $this->config->item('sess_cookie_name'), 
               'value' => json_encode(
                  array(
                     'site_code' => $site_code, 
                     'program_code' => $program_code
                  )
               ), 
               'expire' => 86500, 
               'domain' => '', 
               'path' => '/', 
               'prefix' => 'guest_');         
         set_cookie($cookie);
         $response['error_flag'] = false;
         $response['buy_link'] = $this->site_url.$this->index_page . 'advertiser/login';
      }
      $this->_set_stored_notification('To add ad in to the selected channel you need to login or register');
      echo json_encode($response);
   }
}