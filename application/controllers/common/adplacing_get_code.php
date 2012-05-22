<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для получения кода канала
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Common_Adplacing_Get_Code extends Parent_controller {
   
   protected $id_site = null;
   
   protected $id_channel = null;
   
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      $this->_add_ajax();

      $this->_add_java_script('farbtastic');
      
      $this->_add_css('farbtastic');


      $this->load->library("form");
      $this->load->library('tabs');
      $this->load->model('code_color_scheme_model','color_scheme');
      $this->load->model('site');
      $this->load->model('channel');
      $this->load->model('code_model');
   } //end __construct

   /**
   * показывает форму для изменения параметров кода или предпросмотра
   *
   * @return ничего не возвращает
   */   
   public function index() {
   	
      $this->id_site = $this->input->post('id_site');
   	$this->id_channel = $this->input->post('id_channel');
   	
   	$error_flag = false;
      $error_message = '';
   	
      if (!($this->id_site && $this->id_channel)) { //не указан сайт или канал
      	$error_message = 'Site or channel is not specified';
         $error_flag = true;
      }
   	
      if (!$error_flag) {
      	$site_info = $this->site->get_info($this->id_site);
      	if (is_null($site_info)) {
      		$error_message = 'Site is not found';
            $error_flag = true;
      	} else {
      		if ($this->user_id != $site_info->id_entity_publisher) {
	      		$error_message = 'Access denied';
	            $error_flag = true;	
      		}
      	}
      }
      
      if (!$error_flag) {
         $channel_info = $this->channel->get_info($this->id_channel);
         if (is_null($channel_info)) {
            $error_message = 'Channel is not found';
            $error_flag = true;
         } else {
            if ($this->user_id != $this->channel->get_channel_owner($this->id_channel)) {
               $error_message = 'Access denied';
               $error_flag = true;  
            }
         }
      }
      
      if  ($error_flag) {
                $data = array(
               'MESSAGE' => __($error_message),
               'REDIRECT' => $this->site_url . $this->index_page. $this->role . '/manage_sites_channels'
            );
            $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
            $this->_set_content($content);
            $this->_display();
            return;
      }
      
      // Получаем данные по каналу (размеры канала)
      $width = 0;
      $height = 0;

      $id_dimension = $channel_info->id_dimension;
      // Получаем ширину и высоту
      $this->load->model('dimension');
      $dimension = $this->dimension->get_info($id_dimension);
      if (null !== $dimension) {
         $width = $dimension->width;
         $height = $dimension->height;
      }
      
      
      $form = array(
         "name"        => "channel_code_form",
         "vars"        => array('JS_CODE_TEMPLATE' => $this->code_model->get_code_template(),
                                'SITE_NAME' => type_to_str($site_info->name,'encode'),"SITE_URL" => $site_info->url, "CHANNEL_NAME" => type_to_str($channel_info->name,'encode'), 
                                'ID_SITE' => $this->id_site, 'ID_CHANNEL' => $this->id_channel, 'ID_USER' => $this->user_id, 
                                'ID_DIMENSION' => $id_dimension, 'WIDTH' => $width, 'HEIGHT' => $height,
                                'ROLE' => $this->role, 'KEYWORD_COLOR' => '#228118'
                                ),      
         "view"        => "common/manage_sites_channels/code_form.html",             
         "fields"      => array(                     
            "palette" => array(
               "display_name"     => __("Palette"),
               "id_field_type"    => "string",
               "form_field_type"  => "select",
               "options"          => "code_color_scheme_model",
               "params"           => array('id_entity' => $this->user_id), 
               "validation_rules" => "required"              
            ),
            "id_site" => array(
               "display_name"     => __("ID site"),               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "default"          =>  $this->id_site                    
            ),
            "id_channel" => array(
               "display_name"     => __("ID channel"),               
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "default"          =>  $this->id_channel                    
            )                    
         ) 
      );
      
      $form['redirect'] = "/" . $this->role . "/adplacing_get_code/preview/{$this->id_site}/{$this->id_channel}";
      
      $this->_set_content($this->form->get_form_content('create', $form, $this->input, $this));
      $this->_display();
   } //end index
   
   public function get_preview() {
   	$id_color_scheme = $this->input->post('palette');
   	
   	$color_scheme_info = $this->color_scheme->get_info($id_color_scheme);
   	if (is_null($color_scheme_info)) {
   		$color_scheme_info = $this->color_scheme->get_default_scheme();
   	}
      
      $this->parser->parse('common/color_palettes/color_scheme_preview.html',
                           array("BORDER_COLOR" => $color_scheme_info->border_color,
                                 "BACKGROUND_COLOR" => $color_scheme_info->background_color,
                                 "TITLE_COLOR" => $color_scheme_info->title_color,
                                 "TITLE_FONT_NAME" => $color_scheme_info->title_font_name,
                                 "TITLE_FONT_SIZE" => $color_scheme_info->title_font_size,
                                 "TITLE_FONT_STYLE" => $color_scheme_info->title_font_style,
                                 "TITLE_FONT_WEIGHT" => $color_scheme_info->title_font_weight,
                                 "TEXT_COLOR" => $color_scheme_info->text_color,
                                 "TEXT_FONT_NAME" => $color_scheme_info->text_font_name,
                                 "TEXT_FONT_SIZE" => $color_scheme_info->text_font_size,
                                 "TEXT_FONT_STYLE" => $color_scheme_info->text_font_style,
                                 "TEXT_FONT_WEIGHT" => $color_scheme_info->text_font_weight,
                                 "URL_COLOR" => $color_scheme_info->url_color,
                                 "URL_FONT_NAME" => $color_scheme_info->url_font_name,
                                 "URL_FONT_SIZE" => $color_scheme_info->url_font_size,
                                 "URL_FONT_STYLE" => $color_scheme_info->url_font_style,
                                 "URL_FONT_WEIGHT" => $color_scheme_info->url_font_weight
                           ));
   }
   
   /**
    * заглушка для forms
    *
    */
   public function _create($fields) {
      return '';
   } 
}