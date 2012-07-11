<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* Контроллер управления категориями
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Manage_Categories extends Parent_controller {

   protected $role = "admin";
   
   protected $menu_item = "Manage Categories";
   
   public $create_form_visible = false;
   
   public $edit_form_visible = false;
   
   protected $manage_error = '';
   
 /**
   * конструктор контроллера
   *
   * @return ничего не возвращает
   */
   public function __construct() {
		parent::__construct();
		
		$this->_set_title ( implode(self::TITLE_SEP, array(__( 'Administrator' ),__( 'Settings' ),__( 'Manage Categories' ))));
		
		
		$this->load->library('form');
		$this->load->model("category_model", "", TRUE);
   }

   /**
    * Отображение сообщения об успешном редактировании категории
    *
    * @param int $active_category идентификатор категории
    */
   public function success_edit($active_category) {
      $data = array(
         'MESSAGE' => __('Category was edited successfully'),
         'REDIRECT' => $this->site_url.$this->index_page.'admin/manage_categories/index/'.$active_category
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
   	
      $this->_display();
   }
   
   /**
    * Отображение сообщения об успешном создании категории
    *
    * @param int $active_category идентификатор предка созданной категории
    */
   public function success_create($active_category) {
      $data = array(
         'MESSAGE' => __('Category was created successfully'),
         'REDIRECT' => $this->site_url.$this->index_page.'admin/manage_categories/index/'.$active_category
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
     
      $this->_display();
   }
   
  
   /**
   * выводит дерево категорий
   *
   * @param string $active_category код текущей категории
   * @return ничего возвращает
   */   
   public function index($active_category = 1) {
      $this->_add_ajax();
      
      
      switch ($this->input->post('manage_categories_action')) {
      	case 'delete':
      	  $id = $this->input->post('id_current_category');
      	  if ($id) {
      	     $parent = $this->category_model->get_parent($id);
      	     if (!is_null($parent)) {
      	        $rez = $this->category_model->delete($id);
      	        if (!is_null($rez)) {
      	        	  $this->_set_notification($rez,'error');
      	        	  $active_category = $id;
      	           //$this->error_delete($id,__($rez)); 
      	           //return null;
      	        } else {
      	        	  $this->_set_notification('Category was deleted successfully');
                    $active_category = $parent;
      	           //redirect('admin/manage_categories/success_delete/'.$parent);
      	        }
      	     }
      	  }
      	break;
      	case 'create':
           $active_category = $this->input->post('id_category_parent');
           if (!$active_category) {
              $active_category = 1;
           }
           $this->create_form_visible = true;
         break;
         case 'edit':
           $active_category = $this->input->post('id_category');
           if (!$active_category) {
              $active_category = 1;
           }
           $this->edit_form_visible = true;
         break;
      }
      
         	$form_data = array(
                            "name" => "create_category_form",
         	                "vars" => array('CURRENT_TITLE' => type_to_str($this->category_model->get_name($active_category), 'encode')),
                            "view" => "admin/settings/manage_categories/create_form.html",
         	                "redirect" => "admin/manage_categories/success_create/".$active_category, 
                            "fields"      => array(     
                               "category_title" => array(
         	                      "display_name"     => __("Category Name"), 
                                  "id_field_type"    => "string",
                                  "form_field_type"  => "text",
                                  "validation_rules" => "required"
                                  ),
                               "category_description" => array(
                                  "display_name"     => __("Category Description"),
                                  "id_field_type"    => "string",
                                  "form_field_type"  => "textarea",
                                  "validation_rules" => "required"
                                                               ),
                               "id_category_parent" => array(
                                  "display_name"     => __("Category Parent"),
                                  "id_field_type"    => "int",
                                  "form_field_type"  => "hidden",
                                  "default"          => $active_category,
                                  "validation_rules"  => "required|numeric"
                                                               )
                                                        )
                                                   );
                                                                                        
            $create_form = $this->form->get_form_content("create", $form_data, $this->input, $this);

            $form_data = array(
                            "name" => "edit_category_form",
                            "id" => $active_category, 
                            "vars" => array('CURRENT_TITLE' => type_to_str($this->category_model->get_name($active_category), 'encode')),
                            "view" => "admin/settings/manage_categories/edit_form.html", 
                            "redirect" => "admin/manage_categories/success_edit/".$active_category,
                            "fields"      => array(     
                               "category_title" => array(
                                  "display_name"     => ("Category Name"), 
                                  "id_field_type"    => "string",
                                  "form_field_type"  => "text",
                                  "validation_rules"  => "required"
                                  ),
                               "category_description" => array(
                                  "display_name"     => __("Category Description"),
                                  "id_field_type"    => "string",
                                  "form_field_type"  => "textarea",
                                  "validation_rules"  => "required"
                                  ),
                               "id_category" => array(
                                  "display_name"     => __("Category Parent"),
                                  "id_field_type"    => "int",
                                  "form_field_type"  => "hidden",
                                  "validation_rules"  => "required|numeric"
                                                        )
                                                        )
                                                   );
                                                                                       
            $edit_form = $this->form->get_form_content("modify", $form_data, $this->input, $this);
      
      $categories_tree = $this->category_model->get_html_tree();
      $data = array('MANAGE_ERROR' => $this->manage_error, 'CREATE_FORM_VISIBLE' => $this->create_form_visible?'':'style="display: none;"', 
                                                           'EDIT_FORM_VISIBLE' => $this->edit_form_visible?'':'style="display: none;"', 
      'DESCRIPTION_VISIBLE' => ($this->create_form_visible||$this->edit_form_visible)?'style="display: none;"':'', 'SELECTED_CATEGORY_ID' => (string)$active_category,'CATEGORIES_TREE' => $categories_tree, 'CREATE_FORM' => $create_form , 'EDIT_FORM' => $edit_form);
      $content = $this->parser->parse('admin/settings/manage_categories/body.html', $data, TRUE);
      $this->_set_content($content);
      $this->_display();
   }
   
   public function _load($id) {
      return array('category_title' => '','category_description' => '');
   }
   
   public function _save($id, $fields) {
      $data = array('id_category' => $fields['id_category'],
                    'name' => $fields['category_title'],
                    'description' => $fields['category_description']);

      $rez = $this->category_model->update($data);
      
      if (!is_null($rez)) {
         return $rez;
      } else {
         $this->edit_form_visible = false;
         return "";
      }
   }
   
   public function _create($fields) {

      $data = array('id_category_parent' => $fields['id_category_parent'],
                    'name' => $fields['category_title'],
                    'description' => $fields['category_description']);

      $rez = $this->category_model->create($data);
      
      if (!is_numeric($rez)) {
         return $rez;
      } else {
         $this->create_form_visible = false;
         return "";
      }
   }
   
   /**
    * Отправка описания выбранной категории в формате JSON.  
    *
    */
   public function ajax_get_description() {
      $id = $this->input->post('id_category');
      
      $description = $this->category_model->get_description($id);
      
      if (is_null($description)) {
         $result = 'error';
         $description = '';
      } else {
         $result = 'ok';
      }
      echo json_encode(array('result' => $result, 'description' => $description));
   }
}


?>