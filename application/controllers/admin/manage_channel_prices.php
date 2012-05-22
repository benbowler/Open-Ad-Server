<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/common/manage_channel_prices.php';

/**
 * Контроллер настроек цен канала
 *
 * @author Немцев Андрей
 * @project SmartPPC 6
 * @version 1.0.0
 */
class Manage_Channel_Prices extends Common_Manage_Channel_Prices {
	
	protected $role = "admin";
	
	protected $id_channel = null;
	
	protected $channel_info = NULL;
	
	protected $menu_item = "Manage Sites/Channels";

	protected $action = "show_list"; //Действие над списком цен, которое осуществляет пользователь: show_list, delete, create
	
	/**
	 * Конструктор контроллера настроек цен
	 *
	 */
	public function __construct() {
	   $this->temporary = array ('manage_channel_prices_cpm_sort_direction' => 'asc',
                                'manage_channel_prices_flat_rate_sort_direction' => 'asc'
                              );
		parent::__construct ();
		
		$this->_set_title ( implode(self::TITLE_SEP, array( __( 'Administrator' ) , __( 'Ad Placing' ) , __( 'Manage Sites/Channels' ))));
	}
   
} //end class Manage_Channel_Prices

?>