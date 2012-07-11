<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

class Create_Campaign extends Campaign_Wizard {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";
	
	public function __construct() {
	  parent::__construct();
	  $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  $this->cancel_creation_controller = 'advertiser/manage_ads';
	  $this->session->unset_userdata('id_xml');
	  $this->session->unset_userdata('add_site_channel');
	}
}