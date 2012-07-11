<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер создания объявления в группе в кампании
 *
 */
class Create_Campaign_Step_Create_Ad extends Campaign_Wizard {

   protected $role = "advertiser";

   protected $menu_item = "Manage Ads";

   protected $allowed_extensions = array('gif', 'jpg', 'png', 'swf');

   protected $breadcrumb = '';

   protected $group = '';

   protected $ad_id = NULL;

   protected $next_step = NULL;

   protected $upload_controller = 'advertiser/create_campaign_step_create_ad/upload_image';

   protected $import_mode = false;

   protected $skip = false;
   
   public function __construct() {
      parent::__construct();

      $this->id_campaign = NULL;
      $this->id_group = NULL;
      $this->id_ad = NULL;

      $this->form_title = '';
      $this->_set_title (implode(self::TITLE_SEP, array(__('Advertiser'), __('Manage Ads'))));
      $this->on_submit = 'onSubmit();';
      $this->cancel_creation_controller = 'advertiser/manage_ads';

      $this->_add_css('design');
      $this->_add_ajax();
      $this->_add_java_script('ajaxupload.3.9');
      $this->_add_java_script('farbtastic');
      $this->_add_css('farbtastic');

      $this->load->library('form');
      $this->load->library('tabs');
      $this->load->model('new_campaign');
      $this->load->model('groups');

      $this->ad_type = "text";

      $this->image_upload_dir = $this->config->item('path_to_campaign_creation_images');
   }

   /**
    * по коду объявления определяет его тип
    *
    * @param integer $ad_id уникальный код объявления
    * @return string тп объявления (text, image)
    */
   public function _get_ad_type($ad_id) {
      $ad_info = $this->new_campaign->get_ad($ad_id);
      return $ad_info->ad_type;
   } //end _get_ad_type

   /**
    * по коду объявления определяем типы рекламы
    *
    * @param integer $ad_id уникальный код объявления
    * @return string тп объявления (text, image)
    * @author Semerenko
    */
   public function _get_ad_places($ad_id) {
      $ad_info = $this->new_campaign->get_ad($ad_id);
      return $ad_info->places;
   } //end _get_ad_type

   /**
    * Отображение формы для задания параметров объявления
    *
    * @param string $campaign_type тип создаваемой кампании
    */
   public function index($campaign_type) {
      $this->set_campaign_type($campaign_type);
      $this->setCurrentStep(uri_string());
      $this->review_mode = $this->input->post('review_mode');
      $this->import_mode = false !== $this->input->post('import_mode');
      $this->new_campaign->init_storage($this->id_xml);

      // Получаем список типов программ, а для картиночных типов - размеры
      $ad_types = array();
      $dimensions = array();
      $image_programs = array();
      $programs = $this->new_campaign->get_sites_channels(array('status' => 'all'));
      foreach ($programs as $program) {
         if (!in_array($program['ad_type'], $ad_types)) {
            array_push($ad_types, $program['ad_type']);
         }
         if ('image' == $program['ad_type']) {
            // Собираем картиночные программы
            if (!in_array($program['id_program'], $image_programs)) {
               array_push($image_programs, $program['id_program']);
            }
         }
      }

      // Получаем размеры
      if (0 < count($image_programs)) {
         $this->db->select('c.id_dimension')->from('channels c')->join('channel_program_types cpt', 'cpt.id_channel = c.id_channel')->where_in('cpt.id_program', $image_programs)->group_by('c.id_dimension');

         $query = $this->db->get();

         if (0 < $query->num_rows()) {
            foreach ($query->result_array() as $row) {
               array_push($dimensions, $row['id_dimension']);
            }
         }
      }

      if (1 == count($ad_types) && 'image' == current($ad_types)) {
         $this->ad_type = 'image';
      }

      if (is_null($this->ad_id)) {
         $ad_id = $this->input->post('id');
      } else {
         $ad_id = $this->ad_id;
      }

      if ($ad_id) {
         $form_mode = 'modify';
         $this->ad_type = $this->_get_ad_type($ad_id);

         if ('' == $this->form_title) {
            $this->form_title = __("Edit Ad");
         }
      } else {
         $ad_id = NULL;
         $form_mode = 'create';
         $target_ad_type = $this->input->post('ad_type');
         $this->ad_type = $target_ad_type ? $target_ad_type : $this->ad_type;
         if ('' == $this->form_title) {
            $this->form_title = __("Create Ad");
         }
      }

      $places = array('sites');

      $this->tabs->create('adTabs');

      if (is_null($this->next_step)) {
         $this->next_step = $this->get_next_step_controller();
      }

      //Text Ads ==================================================================================================
      if ((('modify' == $form_mode) && ('text' == $this->ad_type)) || ('create' == $form_mode)) {
         //Загрузка последнего созданного объявления для значения полей по-умолчанию	
         if ('create' == $form_mode) {
            $last_ad = null;//$this->new_campaign->get_last_ad('text');
         } else {
            $last_ad = null;
         }

		 // Подгружаем стили дефолтной color scheme
		 
	     $this->db->select('cs.*, ft.name title_font_name, ft2.name text_font_name, ft3.name url_font_name', false);
		 $this->db->from('color_schemes cs');
		 $this->db->join('fonts ft', 'ft.id_font = cs.title_id_font');
		 $this->db->join('fonts ft2', 'ft2.id_font = cs.text_id_font');
		 $this->db->join('fonts ft3', 'ft3.id_font = cs.url_id_font');

		 $query = $this->db->get();
		 $row = $query->row();
         $this->skip = $this->skip || (!is_null($this->new_campaign->get_last_ad('text')));
         $this->skip = $this->skip || (!is_null($this->new_campaign->get_last_ad('image')));
       
         $this->skip = $this->skip && ($form_mode == 'create');
         $this->skip = $this->skip && ($this->input->post('title') == "");
         $this->skip = $this->skip && ($this->input->post('description1') == "");
         $this->skip = $this->skip && ($this->input->post('description2') == "");   
         $this->skip = $this->skip && ($this->input->post('display_url') == "");
         $this->skip = $this->skip && ($this->input->post('destination_url') == "");
         
         $form_data = array(
            "name"     => 'text_ad',
            "redirect" => $this->next_step,
            "vars"     => array('REVIEW_MODE'     => $this->review_mode,
               'TEXT_AD_EXAMPLE' => $this->load->view('common/text_ad_example.html', '', TRUE),
			   'BACKGROUND_COLOR'  => $row->background_color,
			   'BORDER_COLOR'  => $row->border_color,
			   'TITLE_COLOR'  => $row->title_color,
			   'TITLE_FONT_NAME'  => $row->title_font_name,
			   'TITLE_FONT_SIZE'  => $row->title_font_size,
			   'TITLE_FONT_STYLE'  => $row->title_font_style,
			   'TITLE_FONT_WEIGHT'  => $row->title_font_weight,
			   'TEXT_COLOR' => $row->text_color,
			   'TEXT_FONT_NAME'  => $row->text_font_name,
			   'TEXT_FONT_SIZE'  => $row->text_font_size,
			   'TEXT_FONT_STYLE'  => $row->text_font_style,
			   'TEXT_FONT_WEIGHT'  => $row->text_font_weight,
			   'URL_COLOR' => $row->url_color,
			   'URL_FONT_NAME'  => $row->url_font_name,
			   'URL_FONT_SIZE'  => $row->url_font_size,
			   'URL_FONT_STYLE'  => $row->url_font_style,
			   'URL_FONT_WEIGHT'  => $row->url_font_weight
            ),
            "view"     => 'advertiser/manage_ads/campaigns/creation/create_ad/text.html',
            "fields"   => array(
               "title"                => array(
                  "display_name"     => __("Title"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->title,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"required",
                  'max'              => 25
               ),
               "description1"         => array(
                  "display_name"     => __("Description 1"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->description1,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"required",
                  'max'              => 35
               ),
               "description2"         => array(
                  "display_name"     => __("Description 2"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->description2,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"required",
                  'max'              => 35
               ),
               "display_url"          => array(
                  "display_name"     => __("Display URL"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->display_url,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"trim|required|url",
                  'max'              => 35
               ),
               "destination_url"      => array(
                  "display_name"     => __("Destination URL"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->destination_url,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"trim|required|url",
                  'max'              => 1024
               ),
               "destination_protocol" => array(
                  "display_name"     => __("Destination URL protocol"),
                  "default"          => is_null($last_ad) ? 'http' : $last_ad->destination_protocol,
                  "id_field_type"    => "string",
                  "form_field_type"  => "select",
                  "validation_rules" => "required",
                  "options"          => array('http'  => 'http://', 'https' => 'https://')
               ),
               "id_group"             => array(
                  "id_field_type"   => "string",
                  "form_field_type" => "hidden",
                  "default"         => $this->id_group
               )
            )
         );

         if ('modify' == $form_mode) {
            $form_data['id'] = $ad_id;
         }

         $this->tabs->add('text',
            'Text Ad',
            $this->form->get_form_content($form_mode, $form_data, $this->input, $this),
            $this->ad_type == 'text' && !$this->import_mode
         );
      }

      //Image Ads ==================================================================================================
      if ((('modify' == $form_mode) && ('image' == $this->ad_type)) || ('create' == $form_mode)) {
         if ('create' == $form_mode) {
            $last_ad = null;//$this->new_campaign->get_last_ad('image');
         } elseif ($ad_id) {
            $last_ad = $this->new_campaign->get_ad($ad_id);
         } else {
            $last_ad = null;
         }

         $this->load->model('dimension');
         $dimensions_list = $this->dimension->get_list_specific_more($places);
         $dimensions_array = $dimensions_list['dims'];

         /* tipo view */
         $required_dimensions_html = '<table id="dimensions_list" cellspacing="0" cellpadding="0"><tr class="top" ><td class="col1"></td>';
         foreach ($dimensions_list['allplaces'] as $place) {
            $required_dimensions_html .= '<td class="colp" style="color:#' . $place['color'] . '">' . $place['name'] . '</td>';
         }
         $required_dimensions_html .= '</tr>';
         foreach ($dimensions_list['places'] as $k => $dim) {
            $required_dimensions_html .= '<tr id=id_dim_' . $dimensions_array[$k]['id_dimension'] . '><td class="row">';
            $required_dimensions_html .= $dimensions_array[$k]['width'] . ' × ' .
               $dimensions_array[$k]['height'] . ' <span class="bname">' .
               $dimensions_array[$k]['name'] . '</span>';
            $required_dimensions_html .= '</td>';
            foreach ($dim as $pl) {
               $required_dimensions_html .= '<td class="colp row">';
               $required_dimensions_html .= $pl ? '✓' : '';
               $required_dimensions_html .= '</td>';
            }
            $required_dimensions_html .= '</tr>';
         }
         $required_dimensions_html .= "</tr></table>";

         $i = 0;
         $UPLOAD_IMAGE_DIR = ltrim($this->image_upload_dir, './');

         $temp_img_src = '';
         $temp_img_width = '';
         $temp_img_height = '';
         $temp_img_id_dimension = '';

         if (!empty($this->img_src) && !empty($this->img_dim)) {
            $temp_img_src = $this->img_src;
            $temp_img_width=$this->img_dim['width'];
            $temp_img_height = $this->img_dim['height'];
         } elseif (!is_null($this->new_campaign->get_temp_img())) {
            $res = $this->new_campaign->get_temp_img();
            $temp_img_src = (string) $res['src'];
            $temp_img_width = (string) $res['width'];
            $temp_img_height = (string) $res['height'];
         } elseif (!is_null($last_ad)) {
            $temp_img_src = (string) $last_ad->image_id;
            $this->load->model('dimension');
            $temp_img_id_dimension = $last_ad->id_dimension;
            $dim_info = $this->dimension->get_info($last_ad->id_dimension);
            $temp_img_width = (string) $dim_info->width;
            $temp_img_height = (string) $dim_info->height;
          }
         $this->skip = $this->skip || (!is_null($this->new_campaign->get_last_ad('text')));
         $this->skip = $this->skip || (!is_null($this->new_campaign->get_last_ad('image')));
         
         $this->skip = $this->skip && ($form_mode == 'create');
         $this->skip = $this->skip && ($this->input->post('title') == "");  
         $this->skip = $this->skip && ($this->input->post('display_url') == "");
         $this->skip = $this->skip && ($this->input->post('destination_url') == "");
         $this->skip = $this->skip && ($this->input->post('id_image') == "");
         
         $form_data = array(
            "name"         => 'image_ad',
            "redirect"     => $this->next_step,
            "vars"         => array(
               'CONTROLLER'         => $this->upload_controller,
               'IMAGE_DIR'          => isset($this->image_dir) ? ltrim($this->image_dir, './') : ltrim($this->image_upload_dir, './'),
               'UPLOAD_IMAGE_DIR'   => $UPLOAD_IMAGE_DIR,
               'REVIEW_MODE'        => $this->review_mode,
               'REQIRED_DIMENSIONS' => $required_dimensions_html,
               'ALLOWED_EXTENSIONS' => implode(', ', $this->allowed_extensions),
               'TMP_IMG_SRC'        => $temp_img_src,
               'TMP_IMG_WIDTH'      => $temp_img_width,
               'TMP_IMG_HEIGHT'     => $temp_img_height),
            "view"         => 'advertiser/manage_ads/campaigns/creation/create_ad/image.html',
            "fields"       => array(
               "title"                => array(
                  "display_name"     => __("Title"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->title,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"required",
                  'max'              => 25
               ),
               "display_url"          => array(
                  "display_name"     => __("Display URL"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->display_url,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"trim|required|url",
                  'max'              => 35
               ),
               "destination_url"      => array(
                  "display_name"     => __("Destination URL"),
                  "default"          => is_null($last_ad) ? '' : $last_ad->destination_url,
                  "id_field_type"    => "string",
                  "form_field_type"  => "text",
                  "validation_rules" => $this->skip?"":"trim|required|url",
                  'max'              => 1024
               ),
               "destination_protocol" => array(
                  "display_name"     => __("Destination URL protocol"),
                  "default"          => is_null($last_ad) ? 'http' : $last_ad->destination_protocol,
                  "id_field_type"    => "string",
                  "form_field_type"  => "select",
                  "validation_rules" => $this->skip?"":"required",
                  "options"          => array('http'  => 'http://', 'https' => 'https://')
               ),
               "id_image"             => array(
                  "display_name"     => __("Image"),
                  "id_field_type"    => "string",
                  "form_field_type"  => "hidden",
                  "validation_rules" => $this->skip?"":"required"
               ),
               "id_group"             => array(
                  "id_field_type"   => "string",
                  "form_field_type" => "hidden",
                  "default"         => $this->id_group
               ),
               "id_dimension"         => array(
                  "id_field_type"   => "string",
                  "form_field_type" => "hidden",
                  "default"         => $temp_img_id_dimension
               ),
               "bgcolor"              => array(
                  "id_field_type"   => "string",
                  "form_field_type" => "text",
                  "max"             => 7
               )
            )
         );

         if ('modify' == $form_mode) {
            $form_data['id'] = $ad_id;
         }

         $this->tabs->add('image',
            'Image Ad',
            $this->form->get_form_content($form_mode, $form_data, $this->input, $this),
            $this->ad_type == 'image' && !$this->import_mode
         );
      }

      $data = array(
         'AD_TYPE_TABS'    => $this->tabs->html(),
         'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html', '', TRUE)
      );

      $this->_set_content($this->parser->parse('advertiser/manage_ads/campaigns/creation/create_ad/body.html', $data, FALSE));
      $this->_display();
   }

   public function _load($id) {
      $fields = array();

      $this->new_campaign->init_storage($this->id_xml);
      $ad = $this->new_campaign->get_ad($id);

      if (!is_null($ad)) {
         if ("text" == $ad->ad_type) {
            $fields['description1'] = $ad->description1;
            $fields['description2'] = $ad->description2;
         } elseif ('image' == $ad->ad_type) {
            $fields['id_image'] = $ad->image_id;
            $fields['bgcolor'] = $ad->bgcolor;
            $fields['id_dimension'] = $ad->id_dimension;

            $this->load->model('dimension');
            $dim_info = $this->dimension->get_info($ad->id_dimension);

            $fields['img_w'] = $dim_info->width;
            $fields['img_h'] = $dim_info->height;
         }
      }

      $fields['title'] = $ad->title;
      $fields['display_url'] = $ad->display_url;
      $fields['destination_url'] = $ad->destination_url;
      $fields['destination_protocol'] = $ad->destination_protocol;

      return $fields;
   }

   public function _create($fields) {
      if (!$this->skip) {
         $this->new_campaign->init_storage($this->id_xml);

         if (!$this->import_mode) {
            if ("text" == $this->ad_type) {
               $ad = new Text_Ad();
               $ad->description1 = $fields['description1'];
               $ad->description2 = $fields['description2'];
            } elseif ('image' == $this->ad_type) {
               $ad = new Image_Ad();
               $ad->image_id = $fields['id_image'];
               $ad->bgcolor = $fields['bgcolor'];
               $ad->id_dimension = $fields['id_dimension'];
            } 

            $ad->title = $fields['title'];
            $ad->display_url = trim($fields['display_url']);
            $ad->destination_url = trim($fields['destination_url']);
            $ad->destination_protocol = $fields['destination_protocol'];

            // так как объявление сохраняется нормально, то
            // необходимости в элементе temp_img уже нет.
            $this->new_campaign->remove_temp_img();

            $this->new_campaign->set_ad(time(), $ad); //ID объявления - UNIX-timestamp
            $this->new_campaign->save_data();
         } else {
            srand(time());
            $ids = array_unique(array_filter(explode(',', $this->input->post('selected_ids'))));
            $this->load->model('ads');

            foreach ($ids as $id) {
               $data = $this->ads->get($id);
               if (0 < count($data)) {
                  if ('text' == $data['ad_type']) {
                     $ad = new Text_Ad();
                     $ad->description1 = $data['description1'];
                     $ad->description2 = $data['description2'];
                  } else {
                     $ad = new Image_Ad();
                     $file = $data['id_image'];
                     $new_file = '';
                     if (file_exists($this->config->item('path_to_images') . $file)) {
                        // Копируем картинку туда, где ее ожидает создатор кампаний
                        $new_file = md5(uniqid(time())) . $this->ads->get_file_extension($file);
                        if (!copy($this->config->item('path_to_images') . $file, $this->config->item('path_to_campaign_creation_images') . $new_file)) {
                           // Не удалось скопировать файл
                           continue;
                        }
                        @chmod($this->config->item('path_to_campaign_creation_images') . $new_file, 0777);
                     } else {
                        // У объявления по какой-то причине нет изображения
                        continue;
                     }
                     $ad->image_id = $new_file;
                     $ad->bgcolor = $data['bgcolor'];
                     $ad->id_dimension = $data['id_dimension'];
                  }

                  $ad->title = $data['title'];
                  $ad->display_url = $data['display_url'];
                  $ad->destination_url = $data['destination_url'];
                  $ad->destination_protocol = $data['destination_protocol'];

                  $this->new_campaign->set_ad(rand(100000, 999999), $ad);
                  $this->new_campaign->save_data();
               }
            }
         }
      }
      $this->skip = false;
   }

   public function _save($id, $fields) {
      $this->new_campaign->init_storage($this->id_xml);

      if ("text" == $this->ad_type) {
         $ad = new Text_Ad();
         $ad->description1 = $fields['description1'];
         $ad->description2 = $fields['description2'];
      } elseif ('image' == $this->ad_type) {
         $ad = new Image_Ad();
         $ad->image_id = $fields['id_image'];
         $ad->bgcolor = $fields['bgcolor'];
         $ad->id_dimension = $fields['id_dimension'];
      }

      $ad->title = $fields['title'];
      $ad->display_url = trim($fields['display_url']);
      $ad->destination_url = trim($fields['destination_url']);
      $ad->destination_protocol = $fields['destination_protocol'];

      $this->new_campaign->set_ad($id, $ad); //если ID не существует, то будет создано новое объявление

      $this->new_campaign->save_data();
   }

   /**
    * Обработчик загрузки изображения для объявления
    *
    */
   public function upload_image($code = NULL) {
      $this->group = $code;
      $result = array('id_image' => NULL, 'error' => NULL, 'img_w' => NULL, 'img_h' => NULL, 'file_type' => NULL);
      $config['upload_path'] = $this->image_upload_dir;
      $config['allowed_types'] = implode('|', $this->allowed_extensions);
      $config['max_size'] = '1000';

      $this->load->library('upload', $config);

      if ($this->upload->do_upload('ad_image')) {
         $upload_data = $this->upload->data();

         $this->new_campaign->init_storage($this->id_xml);

         /*$added_channels_ids = $this->get_channels();

         	
          $this->load->model('channel');
          $params = array ('fields' => 'channels.id_channel, dimensions.id_dimension, dimensions.width, dimensions.height',
          //'channel_id_filter' => $added_channels_ids,
          'join_tables' => array('dimensions'));
          */

         //Определяем размеры изображения getimagesize, т.к. Codeigniter Upload Class не определяет размеры SWF
         //$img_w = $upload_data['image_width'];
         //$img_h = $upload_data['image_height'];

         list($img_w, $img_h, $type, $attr) = getimagesize($upload_data['full_path']);

         $result['img_w'] = $img_w;
         $result['img_h'] = $img_h;
         $result['file_type'] = $type;

         $this->load->model('dimension');

         $this->new_campaign->init_storage($this->id_xml);
         $places = $this->new_campaign->get_places();

         $dimensions_array = $this->dimension->get_list_specific($places);

         $wrong_dimension = true;
         foreach ($dimensions_array as $dimension) {
            if (($dimension['width'] == $img_w) && ($dimension['height'] == $img_h)) {
               $wrong_dimension = FALSE;
               $image_dimension = $dimension['id_dimension'];
               //$this->new_campaign->init_storage($this->id_xml);
               //$this->new_campaign->add_image_info(array('id_image' => $upload_data['file_name'],'id_dimension' => $dimension['id_dimension']));
               //$this->new_campaign->save_data();

               $this->new_campaign->set_temp_img($upload_data['file_name'], $img_w, $img_h);
               $this->new_campaign->save_data();
               break;
            }
         }

         /*$required_dimensions = array();

          foreach ($channel_dimension_array as $channel_dimension) {
          $required_dimensions[$channel_dimension['id_dimension']] = array('width' => $channel_dimension['width'],
          'height' => $channel_dimension['height']);
          }

          $wrong_dimension = true;

          foreach ($required_dimensions as $id_dimension => $dimension) {
          if (($dimension['width'] == $img_w) && ($dimension['height'] == $img_h)) {
          $wrong_dimension = FALSE;
          $this->new_campaign->init_storage($this->id_xml);
          $this->new_campaign->add_image_info(array('id_image' => $upload_data['file_name'],'id_dimension' => $id_dimension));
          $this->new_campaign->save_data();
          break;
          }
          }
          */
         if ($wrong_dimension) {
            unlink($upload_data['full_path']);
            $result['error'] = __('Uploaded file has wrong dimensions') . ': ' . $img_w . 'x' . $img_h;
            echo json_encode($result);
         } else {
            $result['id_image'] = $upload_data['file_name'];
            $result['id_dimension'] = $image_dimension;
            echo json_encode($result);
         }
      } else {
         $result['error'] = __($this->upload->display_errors('', ''));
         echo json_encode($result);
      }
   }
}
