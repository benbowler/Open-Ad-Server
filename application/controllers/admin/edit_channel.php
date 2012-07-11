<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/edit_channel.php';

/**
* контроллер для создания/изменения канала
*
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Edit_channel extends Common_Edit_channel {

   protected $role = "admin";

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      $this->_set_title ( implode(self::TITLE_SEP, array(__('Administrator'),__('Ad Placing'),__('Manage Sites/Channels'))));
      
      $path_to_views_dir = $this->role.'/adplacing/manage_sites_channels/';
      
      $this->views_paths['channel_form'] = $path_to_views_dir.'channel_form.html';
   } //end __construct
     
}