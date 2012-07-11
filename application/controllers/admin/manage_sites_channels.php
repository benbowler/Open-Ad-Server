<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
   exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/common/manage_sites_channels.php';

class Manage_Sites_Channels extends Common_Manage_Sites_Channels {
   
   protected $role = "admin";

   protected $menu_item = "Manage Sites/Channels";

   protected $date_picker = TRUE;

   protected $date_range;
   
   protected $revenue_field = 'earned_admin';
   
   public function __construct() {
   	$this->temporary = array (
	      'manage_sites_channels_from' => 'select',
	      'manage_sites_channels_to'   => 'today',
	      'manage_sites_channels_status_filter' => 'all',
	      'manage_sites_channels_active_tab' => '',
   	   'manage_sites_columns' => 'all',
   	   'manage_channels_columns' => 'all',
   	   'manage_sites_channels_channel_columns' => 'all',
   	   'manage_sites_channels_site_columns' => 'all'
      );
      parent::__construct ();

      $this->_set_title ( implode(self::TITLE_SEP, array( __( 'Administrator' ) , __( 'Ad Placing' ), __( 'Manage Sites/Channels' ))));
      
      $path_to_views_dir = $this->role.'/adplacing/manage_sites_channels/';
      
      $this->views_paths['body'] = $path_to_views_dir.'body.html';
      $this->views_paths['sites_list'] = $path_to_views_dir.'sites_list.html';
      $this->views_paths['channels_list'] = $path_to_views_dir.'channels_list.html'; 
      $this->views_paths['iframe_sites_list'] = 'common/manage_sites_channels/iframe_sites_list.html';
      $this->views_paths['iframe_channels_list'] = 'common/manage_sites_channels/iframe_channels_list.html';
   }
}
?>