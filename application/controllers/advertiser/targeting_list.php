<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/targeting_list.php';

/**
 * Контроллер для вывода вкладки обзора таргетинга
 *
 * @author Vladimir Yudin
 */

class Targeting_list extends Parent_targeting_list {
   
   protected  $role = "advertiser";                    // роль пользователя, открывающего контроллер
   
   protected $menu_item = "Manage Ads";
   /**
    * Конструктор класса
    *
    */
   public function Targeting_list() {
      parent::Parent_targeting_list();
   } //end Targeting_list
      
} //end Class Targeting_list
