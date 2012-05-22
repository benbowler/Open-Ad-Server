<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_entity.php';

/**
* контроллер для управления рекламой (каспаниями, группами и объявлениями)
* 
* @author Владимир Юдин
* @project SmartPPC6 
* @version 1.0.0
*/
class Manage_ads extends Parent_controller {
     
   protected $role = "advertiser"; 
   
   protected $menu_item = "Manage Ads";  
   
   protected $id_campaign = NULL;
   
   protected $id_group = NULL;
   
   protected $tab = NULL;
   
   /**
   * конструктор класса,
   * вносит изменения в структуру базового класса 
   *
   * @return ничего не возвращает
   */
   public function Manage_ads() {
      parent::Parent_controller();
      $this->_add_ajax();      
      
      $this->_set_title(__('Advertiser').' - '.__('Manage Ads'));
      $this->_set_help_index("advertiser_manage_ads");     
      $this->load->library('Plugins', array('path' => array('advertiser', 'manageads'))); 
	   $this->session->unset_userdata('id_xml');
	   $this->session->unset_userdata('add_site_channel');
   } //end Manage_ads

   /**
   * функция по умолчанию
   *
   * @return ничего не возвращает
   */   
   public function index() {
      $this->load->model('groups', '', TRUE);
      $this->groups->end_fl_status();
      if (is_null($this->id_campaign)) {
         if (is_null($this->id_group)) {         
            $usercode = type_to_str($this->user_id, 'textcode'); 
            $node = 'user'.$usercode;
            $startitem = "show_campaigns('$usercode');";
         } else {
            $node = 'group'.$this->id_group;
            $id_campaign_type = $this->groups->group_type(type_cast($this->id_group, 'textcode'));
            $startitem        = "show_ads('$this->id_group', '', '$id_campaign_type');";                                       
         }
      } else {
         $node = 'camp'.$this->id_campaign;
         $id_campaign      = type_cast($this->id_campaign, 'textcode');
         $id_campaign_type = $this->groups->campaign_type($id_campaign);
         $startitem        = "show_groups('$this->id_campaign', '$id_campaign_type');";                           
      }
      
      $vars = array(
         'TREE' => $this->groups->get_html_tree($this->user_id, $this->user_name),
         'TABLE' => 'table',
         'STARTITEM' => $startitem,
         'NODE' => $node,
         'TAB' => $this->tab,
         'NUMBERFORMAT' => get_number_format()
      );
      $vars['PLUGIN_COLOR_HINT']   = implode($this->plugins->run('get_colors_hint_html',  $this));
      $vars['PLUGIN_JS_FUNCTIONS'] = implode($this->plugins->run('get_js_functions_html', $this));
      $plugin_controller = '';
      foreach ($this->plugins->run('get_controller', $this) as $controllers) {
         foreach ($controllers as $id_campaign_type => $return_value) {
            $plugin_controller .= " case '$id_campaign_type': return $return_value; ";
         }
      }
      $vars['PLUGIN_CONTROLLER'] = $plugin_controller;
      
      $this->_set_content($this->parser->parse('advertiser/manage_ads/template.html', $vars, TRUE));
   	$this->_display();
   } //end index()
    
   /**
   * тоже что и index, но делает текущей выбранную кампанию
   *
   * @param string $code шифрованный код выбранной кампании
   * @return ничего не возвращает
   */   
   public function campaign($code) {
      $this->id_campaign = $code;
      
      $id_campaign = type_cast($code, 'textcode');
      $this->load->model('campaigns', '', TRUE);
      if ($this->campaigns->get_entity($id_campaign) != $this->user_id) {
         redirect($this->role . '/dashboard');
      }


      $this->index();      
   } //end camapign()

   /**
   * тоже что и index, но делает текущей выбранную группу
   *
   * @param string $code шифрованный код выбранной группы
   * @return ничего не возвращает
   */   
   public function group($code, $tab = NULL) {
      $this->tab = $tab;
      $this->id_group = $code;
      $this->index();      
   } //end camapign
      
} //end class Manage_ads

?>