<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Родительский контроллер для редактирования списков таргетинга
 *
 * @author Vladimir Yudin
 */

class Parent_targeting_list extends Parent_controller {
   
   protected $id_targeting_group = NULL;

   protected $template = "common/parent/jq_iframe.html";
      
   protected $tab_name;
   
   /**
    * Конструктор класса
    *
    * @return Parent_targeting_group
    */
   public function Parent_targeting_list() {
      parent::Parent_controller();
   } //end Parent_targeting_list
      
   /**
   * возвращает содержимое вкладки фильтра таргетинга   
   *
   * @return string HTML-код содержимого вкладки 
   */
   public function index($targeting_group_code = NULL) {
      $this->load->model('targeting_groups', '', TRUE);
      if (!is_null($targeting_group_code)) {
         $this->id_targeting_group = type_cast($targeting_group_code, 'textcode');
      }
                
      $this->load->library('form');

      
      $allowed_list = $this->targeting_groups->get_group_values($this->id_targeting_group, $this->tab_name);
      if ($allowed_list == '') {
         $allowed_list = 'all records';
      }
      $form = array(
         'name'        => "{$this->tab_name}_form",
         'redirect'    => "$this->role/tab_$this->tab_name/index/$targeting_group_code",
         'view'        => "common/targeting_group/targeting_list.html",
         'vars'        => array(
            'TARGETINGGROUPCODE' => $targeting_group_code,
            'TARGETINGGROUP' => $this->tab_name
         ),
         'fields'      => array(
            $this->tab_name.'_denied' => array( 
               'id_field_type'   => 'integer',
               'form_field_type' => 'select',
               'options'         => $this->tab_name                     
            ),
            $this->tab_name.'_denied_count' => array( 
               'id_field_type'   => 'integer',
               'form_field_type' => 'hidden'                     
            ),
            $this->tab_name.'_allowed_list' => array(
               'id_field_type'   => 'string',
               'form_field_type' => 'hidden',
               'default'         => $allowed_list
            )                                    
         )                   
      );
      $html = $this->form->get_form_content('create', $form, $this->input, $this);
      $this->_set_content($html);            
      $this->_display();      
   } //end index
                       
   /**
   * создает новую группу таргетинга исходя из значений полей формы
   *
   * @param array $fields массив с полями формы
   * @return string сообщение об ошибке
   */
   public function _create($fields) {
      $this->load->model('targeting_groups', '', TRUE);
      $name = '';
      if ($this->use_name) {
         $name = $fields["{$this->tab_name}_targeting_name"];
      }
      $this->targeting_groups->add_value($this->id_targeting_group, $this->tab_name, $fields["{$this->tab_name}_targeting_filter"], $fields["{$this->tab_name}_compare"], $name);
      return '';
   } //end _create
            
   /**
   * AJAX-функция, помечает элементы списка как удаленные
   *
   * @param POST values список значений списка
   * @param POST targeting_group_code шифрованный код группы таргетинга
   * @param POST group наименование группы значений
   * @return ничего не возвращает
   */
   public function set_values() {
   	$values = $this->input->post('values');
      $group = $this->input->post('group');
      $denied = $this->input->post('denied');
      if ($denied == 0) {
         $values = '';
      }
      $targeting_group_code = $this->input->post('targeting_group_code');
   	$id_targeting_group = type_cast($targeting_group_code, 'textcode');
      $this->load->model('targeting_groups', '', TRUE);
      //while(!is_null($this->global_variables->get("set_values_".$id_targeting_group."_".$group, $this->user_id)));
      //$this->global_variables->set("set_values_".$id_targeting_group."_".$group, "true", $this->user_id, TRUE);
      $this->targeting_groups->set_group_value($id_targeting_group, $group, $values);      	
      //$this->global_variables->kill($this->user_id, "set_values_".$id_targeting_group."_".$group);
   } //end name   
   
} //end Class Parent_targeting_list
