<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/change_balance.php';

/**
 * Контроллер изменения баланса рекламодателя
 *
 */
class Change_Advertiser_Balance extends Common_Change_Balance {
	
	protected $menu_item = "Manage Advertisers";
	
   protected $role = "admin";

   protected $subject_role = "advertiser";
	
	public function __construct() {
	   parent::__construct();	
	   $this->_set_title(implode(self::TITLE_SEP,array(__('Admin'),__('Manage Advertisers'),__('Change Balance'))));
	   $this->cancel_url =  $this->site_url.$this->index_page.'admin/manage_advertisers';
      $this->prev_title =  __('Manage Advertisers');    
	} //end __construct()
      
   public function success() {
   	            
      $data = array(
         'MESSAGE' =>  __('Advertiser balance was changed successfuly!'),
         'REDIRECT' => $this->site_url.$this->index_page."admin/manage_advertisers",
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end success     
   
} //end class Change_Advertiser_Balance