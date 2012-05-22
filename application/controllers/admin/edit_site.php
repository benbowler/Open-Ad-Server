<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/edit_site.php';

/**
* контроллер для создания/изменения сайта
*
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Edit_Site extends Common_Edit_Site {

   protected $role = "admin";

   protected $menu_item = "Manage Sites/Channels";

   protected $site_id = null;
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();

      $this->_set_title ( implode(self::TITLE_SEP, array(__('Administrator'),__('Ad Placing'),__($this->menu_item))));

      $path_to_views_dir = $this->role.'/adplacing/manage_sites_channels/';

      $this->views_paths['site_form'] = $path_to_views_dir.'site_form.html';
   } //end __construct
}

?>