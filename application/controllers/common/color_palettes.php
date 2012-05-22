<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для создания/изменения цветовой схемы
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Common_Color_Palettes extends Parent_controller {
   
   protected $id_pallete = null;

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
   	parent::__construct();
      
      $this->load->model('Code_Color_Scheme_Model');
      
      $this->_add_ajax();
      $this->_add_java_script('farbtastic');
      
      $this->_add_css('farbtastic');
           
      $this->load->library("form");
      $this->load->library("tabs");
      $this->load->model('Code_Color_Scheme_Model','color_scheme');
   } //end __construct

   public function delete() {
   	$id = $this->input->post('id_color_palette');
   	
   	$response = array('result' => 'ERROR', 
                        'message' => '', 
                        'id_palette' => null);
      
      $error_message = '';
      $error_flag = false;

      $id_palette_owner = $this->color_scheme->get_owner($id);
      
      if (0 == $id_palette_owner) {
         $error_message = __('System default palette cannot be deleted');
         $error_flag = true;
      }
      
      if (($this->user_id != $this->color_scheme->get_owner($id)) && (!$error_flag)) {
         $error_message = __('Access denied');
         $error_flag = true;
      }
      
      if (!$error_flag) {
         $this->color_scheme->delete($id, $this->user_id);
      }
      
      if ($error_flag) {
         $response['message'] = $error_message;
      } else {
         $response['result'] = "OK";
         $response['id_palette'] = $id;
      }

      echo json_encode($response);
   }
   
   	/**
   	 * показывает форму для изменения новости или ее создания
   	 *
   	 * @return ничего не возвращает
   	 */   
   	public function index() {
      $id = $this->input->post('id_color_palette');
      
   		if ($id) { 
        	$this->id_pallete = $id;
        	$mode = "modify";
      	} else {
        	$mode = "create"; 
      	}
      
      	$this->tabs->create('paletteTabs');
      
      	$this->tabs->add('create', 'Create palette', $this->load->view('common/color_palettes/new_palette.html','',TRUE), 'create' == $mode);
      
      	$edit_fields = $this->load->view('common/color_palettes/edit_palette.html','',TRUE);
      
      	$this->tabs->add('modify', 'Edit palettes', $edit_fields, 'modify' == $mode);
      
      
      	$form = array(
        	"id"          => $this->id_pallete,
         	"name"        => "color_palette_form",
         	"view"        => "common/color_palettes/palette_form.html", 
         	"vars"        => array(
         		'PALETTE_TABS'=> $this->tabs->html(),
         		'PREVIEW_TEMPLATE' => $this->load->view('common/color_palettes/color_scheme_preview.html','',TRUE),
         		'ROLE' => $this->role
      		),
         	"fields"      => array(                     
            	"palettes_list" => array(
               		"display_name"     => __("Palettes List"),
               		"id_field_type"    => "string",
               		"form_field_type"  => "select",
               		"options"          => "Code_Color_Scheme_Model",
               		"params"           => array('id_entity' => $this->user_id)           
            	),
            	"title_font" => array(
               		"display_name"     => __("Link Title Font Name"),
               		"id_field_type"    => "integer",
               		"form_field_type"  => "select",
               		"options"          => "font_model",
               		"validation_rules" => "required"              
            	),
            	"title_font_color" => array(
               		"display_name"     => __("Link Title Font Color"),
               		"id_field_type"    => "string",
               		"form_field_type"  => "text",
               		"validation_rules" => "required",
               		"default"          => '#0000FF'              
            	),
            	"title_font_size" => array(
               		"display_name"     => __("Link Title Font Size"),
               		"id_field_type"    => "integer",
               		"form_field_type"  => "select",
               		"options"          => array(
            			6 => '6 {@px@}', 
            			8 => '8 {@px@}', 
            			10 => '10 {@px@}', 
            			12 => '12 {@px@}', 
            			14 => '14 {@px@}', 
            			16 => '16 {@px@}', 
            			18 => '18 {@px@}'
            		),
               		"validation_rules" => "required",
               		"default"          => 12
            	),
            "title_font_style" => array(
               "display_name"     => __("Link Title Font Style"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "title_font_weight" => array(
               "display_name"     => __("Link Title Font Weight"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "text_font" => array(
               "display_name"     => __("Text Font Name"),
               "id_field_type"    => "integer",
               "form_field_type"  => "select",
               "options"          => "font_model",
               "validation_rules" => "required"              
            ),
            "text_font_color" => array(
               "display_name"     => __("Text Font Color"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               "default"          => '#000000'              
            ),
            "text_font_size" => array(
               "display_name"     => __("Text Font Size"),
               "id_field_type"    => "integer",
               "form_field_type"  => "select",
               "options"          => array(6 => '6 {@px@}', 8 => '8 {@px@}', 10 => '10 {@px@}', 12 => '12 {@px@}', 14 => '14 {@px@}', 16 => '16 {@px@}', 18 => '18 {@px@}'),
               "validation_rules" => "required",
               "default"          => 12
            ),
            "text_font_style" => array(
               "display_name"     => __("Text Font Style"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "text_font_weight" => array(
               "display_name"     => __("Text Font Weight"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "url_font" => array(
               "display_name"     => __("URL Font Name"),
               "id_field_type"    => "integer",
               "form_field_type"  => "select",
               "options"          => "font_model",
               "validation_rules" => "required"              
            ),
            "url_font_color" => array(
               "display_name"     => __("URL Font Color"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               "default"          => '#008000'              
            ),
            "url_font_size" => array(
               "display_name"     => __("URL Font Size"),
               "id_field_type"    => "integer",
               "form_field_type"  => "select",
               "options"          => array(6 => '6 {@px@}', 8 => '8 {@px@}', 10 => '10 {@px@}', 12 => '12 {@px@}', 14 => '14 {@px@}', 16 => '16 {@px@}', 18 => '18 {@px@}'),
               "validation_rules" => "required",
               "default"          => 12
            ),
            "url_font_style" => array(
               "display_name"     => __("URL Font Style"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "url_font_weight" => array(
               "display_name"     => __("URL Font Weight"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => ""
            ),
            "background_color" => array(
               "display_name"     => __("Background Color"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               "default"          => '#FFFFFF'              
            ),
            "border_color" => array(
               "display_name"     => __("Border Color"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               "default"          => '#AAAAAA'              
            )
                               
         ) 
      );
      
      switch ($mode) {
         case 'create':
            $form['redirect'] = "/" . $this->role . "/color_palettes/create_complete";
            $form['fields']['palette_name'] = array("display_name"     => __("Name"),
                                                    "id_field_type"    => "string",
                                                    "form_field_type"  => "text",
                                                    "validation_rules" => "required",
                                                    "max" => 50);
         break;
         case 'modify':
            $form['redirect'] = "/" . $this->role . "/color_palettes/edit_complete";
         break;
      }
      
      $this->_set_content($this->form->get_form_content($mode, $form, $this->input, $this));
      $this->_display();
   } //end index
   
   /**
    * Отображение сообщения об успешном создании новости
    *
    */
   public function create_complete() {
      $data = array(
         'MESSAGE' => __('Color palette was created successfully'),
         'REDIRECT' => $this->site_url .$this->index_page. $this->role . '/color_palettes'
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }
   
   /**
    * Отображение сообщения об успешном редактировании новости
    *
    */
   public function edit_complete() {
      $data = array(
         'MESSAGE' => __('Color palette was edited successfully'),
         'REDIRECT' => $this->site_url .$this->index_page. $this->role . '/color_palettes'
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }
   
   /**
    * обновление данных цветовой схемы
    *
    * @param array $fields параметры сохраняемого сайта
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _save($id, $fields) {

   	if ($this->user_id != $this->color_scheme->get_owner($id) &&
   		$this->user_id != 1) {
   			return 'This color scheme cannot be changed!';
   	}
   	
      $params = array('border_color' => str_replace('#','',$fields['border_color']),
                      'background_color' => str_replace('#','',$fields['background_color']),
                      'title_color' => str_replace('#','',$fields['title_font_color']),
                      'text_color' => str_replace('#','',$fields['text_font_color']),
                      'url_color' => str_replace('#','',$fields['url_font_color']),
                      'title_id_font' => $fields['title_font'],
                      'text_id_font' => $fields['text_font'],
                      'url_id_font' => $fields['url_font'],
                      'title_font_size' => $fields['title_font_size'],
                      'text_font_size' => $fields['text_font_size'],
                      'url_font_size' => $fields['url_font_size'],
                      'title_font_style' => $fields['title_font_style'],
                      'text_font_style' => $fields['text_font_style'],
                      'url_font_style' => $fields['url_font_style'],
                      'title_font_weight' => $fields['title_font_weight'],
                      'text_font_weight' => $fields['text_font_weight'],
                      'url_font_weight' => $fields['url_font_weight']);
     
      
      $this->color_scheme->update($id, $params, $this->user_id);
       
   } 
   
   /**
    * Создание сайта
    *
    * @param array $fields параметры создаваемого сайта
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _create($fields) {
      $params = array('name' => $fields['palette_name'],
                      'border_color' => str_replace('#','',$fields['border_color']),
                      'background_color' => str_replace('#','',$fields['background_color']),
                      'title_color' => str_replace('#','',$fields['title_font_color']),
                      'text_color' => str_replace('#','',$fields['text_font_color']),
                      'url_color' => str_replace('#','',$fields['url_font_color']),
                      'title_id_font' => $fields['title_font'],
                      'text_id_font' => $fields['text_font'],
                      'url_id_font' => $fields['url_font'],
                      'title_font_size' => $fields['title_font_size'],
                      'text_font_size' => $fields['text_font_size'],
                      'url_font_size' => $fields['url_font_size'],
                      'title_font_style' => $fields['title_font_style'],
                      'text_font_style' => $fields['text_font_style'],
                      'url_font_style' => $fields['url_font_style'],
                      'title_font_weight' => $fields['title_font_weight'],
                      'text_font_weight' => $fields['text_font_weight'],
                      'url_font_weight' => $fields['url_font_weight'],  
                      'id_entity_publisher' => $this->user_id);
     
      return $this->color_scheme->create($params);
   } 
   
   /**
    * Загрузка данных сайта
    *
    * @param array $fields параметры создаваемого сайта
    * @return string описание ошибки ('' при успешном создании)
    */
   /*public function _load($id) {
      return array();
   } */
   
   public function get_palette() {
   	$response = array('result' => 'ERROR', 
   	                  'message' => '', 
   	                  'palette_info' => null);
   	
   	$error_message = '';
   	$error_flag = false;
   	
      $id = $this->input->post('id_palette');

      $id_pallette_owner = $this->color_scheme->get_owner($id);
      
      if (($this->user_id != $id_pallette_owner) && ($id_pallette_owner != 0)) {
      	$error_message = __('Access denied');
      	$error_flag = true;
      }
      
      if (!$error_flag) {
      	$palette_info = $this->color_scheme->get_info($id);
      	if (is_null($palette_info)) {
      		$error_message = __('Palette not found');
            $error_flag = true;
      	}
      }
      
      if ($error_flag) {
      	$response['message'] = $error_message;
      } else {
      	$response['result'] = "OK";
      	$response['palette_info'] = $palette_info;
      }

      echo json_encode($response);
   }
}