<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для создания/изменения канала
*
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Common_Edit_Channel extends Parent_controller {

   protected $menu_item = "Manage Sites/Channels";

   protected $channel_id = null;

   protected $form_data;
   
   protected $have_errors = FALSE;
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
      $this->_add_css('fields/category-selector');

      $this->load->model('channel');
      $this->load->model('category_model');
      $this->load->model('site');
      $this->load->model('dimension');

      $this->load->library("form");
      
      $this->load->helper('categoryselector');
   } //end __construct
   
   
   /**
    * внешний валидатор для формы
    *
    * @return bool истина, если нет ошибок
    */
   public function _validator() {
   	return !$this->have_errors;
   } //end _validator   
   
   /**
   * показывает форму для изменения канала или его создания
   *
   * @param $id_channel - идентификатор редактируемого канала, если null - создание канала
   * @param $id_site - идентификатор сайта, для которого создается /редактируется канал
   * @return ничего не возвращает
   */
   public function index() {
      $this->session->unset_userdata(get_class($this) . '_id_channel');
      
      $site = null;
      $channel = null;
      
   	  $id_site = $this->input->post('id_site');
   	  $id_channel = $this->input->post('id_channel');

   	  $error_flag = false;
      $error_message = '';
      
      $categories_tree = $this->category_model->get_html_tree();
      
      if ($id_site !== false) {
      	$siteModel = new Sppc_SiteModel();
         $site = $siteModel->findObjectById($id_site);
      }
		
		if ($id_channel) {
			$this->channel_id = $id_channel;
			$channelModel = new Sppc_ChannelModel();
			$channel = $channelModel->findObjectById($id_channel);
			
			if ($this->user_id != $this->channel->get_channel_owner ( $id_channel )) {
				$error_message = 'Access denied';
				$error_flag = true;
			}
			
			$mode = "modify";
		} else {
	      if (is_null($site)) {
		  	    $error_message = 'Site is not specified!';
	      	  	$error_flag = true;
		   }
		  
	      if ((!$error_flag) && ($this->user_id != $site->getIdEntityPublisher())) {
	      	  	$error_message = 'Access denied';
	      	  	$error_flag = true;
	      	  	
	      }
          $mode = "create";
      }
          
   	  if  ($error_flag) {
      		    $data = array(
	            'MESSAGE' => __($error_message),
	            'REDIRECT' => $this->site_url .$this->index_page. $this->role . '/manage_sites_channels'
	         );
	         $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
	         $this->_set_content($content);
	         $this->_display();
	         return NULL;
      }

      $available_dimensions = $this->dimension->get_list(array('name'));
      
      $dimensions_info = $this->dimension->get_list_all();

      $max_ad_slots_info = array();

      foreach ($dimensions_info as $dimension_info) {
      	$max_ad_slots_info[$dimension_info['id_dimension']] = $dimension_info['max_ad_slots'];
      }
      
      if (false !== $this->input->post('categories')) {
         $categoryModel = new Sppc_CategoryModel();
         $selectedCategories = $categoryModel->find(explode(',', $this->input->post('categories'))); 
      } else {
         $selectedCategories = (is_null($channel)) ? array() : $channel->getCategories();
      }
      $this->form_data = array(
         "id"          => $this->channel_id,
         "name"        => "channel_form",
         "vars"        => array(
            'MAX_AD_SLOTS_INFO' => json_encode($max_ad_slots_info), 
            'NUMBERFORMAT' => get_number_format(),
            'CATEGORY_SELECTOR' => render_category_selector_field($selectedCategories, 'categories', 'select_category_btn', 'selected_categories'),
            'CATEGORIES_TREE' => $categories_tree,
            'CHANNEL_ID' => (is_null($channel)) ? 'new' : type_to_str($channel->getId(), 'textcode')
         ), 
         "view"        => $this->views_paths['channel_form'],
         "fields"      => array(
            "name" => array(
               "display_name"     => __("Channel Name"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               'validation_rules' => "trim|required",
               "max"              => 35
            ),
            "description" => array(
               "display_name"     => __("Channel Description"),
               "id_field_type"    => "string",
               "form_field_type"  => "textarea",
               "validation_rules" => "trim|required",
               "max"              => 300
            ),
            "category" => array(
               "display_name"     => __("Category"),               
               "id_field_type"    => "int",
               "form_field_type"  => "hidden",
               "validation_rules" => "required"
            ),
            'ad_sources_advertisers' => array(
               'display_name'     => __('Ad Sources'),
               'id_field_type'    => 'bool',
               'form_field_type'  => 'checkbox',
               'default'          => 'true'
            ),
            'ad_sources_xml_feeds' => array(
               'display_name'     => __('Ad Sources'),
               'id_field_type'    => 'bool',
               'form_field_type'  => 'checkbox',
               'default'          => 'true',
               'validation_rules' => ($this->input->post('ad_sources_advertisers') != "true")?"required":""
            ),
            "ad_type_text" => array(
               "display_name"     => __("Ad Type"),
               "id_field_type"    => "string",
               "form_field_type"  => "checkbox",
               "default" => "text"
            ),
            "ad_type_image" => array(
               "display_name"     => __("Ad Type"),
               "id_field_type"    => "string",
               "form_field_type"  => "checkbox",
               "default" => "",
               'validation_rules' => ($this->input->post('ad_type_text') != "text")?"required":""
            ),
            "format" => array(
               "display_name"     => __("Format"),
               "id_field_type"    => "string",
               "form_field_type"  => "select",
               "params"           => array('fields' => 'id_dimension, name, orientation'),
               "options"          => array('' => __('Select Channel Image format')),
               "validation_rules" => "required"
            ),
            "channel_type" => array(
               "display_name"     => __("Channel Type"),
               "id_field_type"    => "string",
               "form_field_type"  => "radio",
               //"validation_rules" => "required",
               "default"          => "contextual"
            ),
            "id_channel" => array(
               "display_name"     => __("ID channel"),
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "default"          =>  $id_channel
            ),
            "ad_settings" => array(
               "display_name"     => __("Ad Settings"),
               "id_field_type"    => "string",
               "form_field_type"  => "radio",
               "validation_rules" => "required",
               "default"          => "blank"
            ),
            "tag_code" => array(
              "display_name"     => __("Html tag"),
              "id_field_type"    => "string",
              "form_field_type"  => "textarea",
              "validation_rules" => ($this->input->post('ad_settings') == "tag")?"required":""
            ),
            "blank_color" => array(
               "display_name"     => __("Ad Settings Blank Color"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "default"          => "",
               "validation_rules" => ($this->input->post('ad_settings') == "blank_color")?"required":""
            )
         )
      );
      if (null !== $site){
         $this->form_data['fields']['category']['default'] = $site->getCategory();
      }
      $this->form_data['vars']['ID_CHANNEL'] = $this->channel_id;
      if ($this->input->post('redirect_after_save')) {
          $this->form_data['redirect'] = $this->input->post('redirect_after_save');
      }
      
      switch ($mode) {
         case 'create':
         	$this->form_data['fields']['format']['options'] = array_merge($this->form_data['fields']['format']['options'],$available_dimensions);
            $this->form_data['vars']['DISABLED_AD_TYPE'] = '';
            $this->form_data['vars']['DISABLED_AD_TYPE_RADIO'] = '';
         	$this->form_data['vars']['APPLY_BTN_CAPTION'] = "Create";
         	$this->form_data['vars']['CURRENT_CHANNEL_NAME'] = '';
         	$this->form_data['vars']['CURRENT_CHANNEL_FORMAT'] = '';
         	$this->form_data['vars']['HIDE_AD_TYPE_CHANGE'] = '';
         	$this->form_data['vars']['HIDE_AD_TYPE_DISPLAY'] = 'style="display:none;"';
         	if (!array_key_exists('redirect',$this->form_data)) {
               $this->form_data['redirect'] = "/" . $this->role . "/edit_channel/create_complete";
         	}
            if (!is_null($site)) {
            	$this->form_data['vars']['FORM_TITLE'] = __("Create channel for site").': <span class="green i">&bdquo;'.type_to_str($site->getName(),'encode').' ('.$site->getUrl().')&ldquo;</span>';
            	$this->form_data['fields']['id_site'] = array(
		               "display_name"     => __("ID Site"),
		               "id_field_type"    => "string",
		               "form_field_type"  => "hidden",
		               "default"          =>  $id_site
		               );
            } else {
            	$this->form_data['vars']['FORM_TITLE'] = __("Create channel");
            }
         break;
         case 'modify':
         	$channelAdTypes = array();
         	
         	if ($channel->isAdTypeAllowed(Sppc_Channel::AD_TYPE_TEXT)) {
         		$channelAdTypes[] = __("Text"); 
         	}
         	
      		if ($channel->isAdTypeAllowed(Sppc_Channel::AD_TYPE_IMAGE)) {
         		$channelAdTypes[] = __("Image"); 
         	}
         	
         	
         	$this->form_data['fields']['format']['options'] = $available_dimensions;
         	$this->form_data['vars']['CURRENT_CHANNEL_AD_TYPE'] = implode(', ', $channelAdTypes);
         	$this->form_data['vars']['DISABLED_AD_TYPE'] = 'readonly="true"';
         	$this->form_data['vars']['DISABLED_AD_TYPE_RADIO'] = 'onclick="return restoreOldAdType(this);"';
            $this->form_data['vars']['APPLY_BTN_CAPTION'] = "Save";
            $this->form_data['vars']['CURRENT_CHANNEL_NAME'] = '&bdquo;'.type_to_str($channel->getName(),'encode').'&ldquo;';
            $this->form_data['vars']['HIDE_AD_TYPE_CHANGE'] = 'style="display:none;"';
            $this->form_data['vars']['HIDE_AD_TYPE_DISPLAY'] = '';
            if (!array_key_exists('redirect',$this->form_data)) {
               $this->form_data['redirect'] = "/" . $this->role . "/edit_channel/edit_complete";
            }

            $this->form_data['vars']['FORM_TITLE'] = __("Edit channel").':';
         break;
      }
      
     // print_r($this->form_data);
      $this->_set_content($this->form->get_form_content($mode, $this->form_data, $this->input, $this));
      $this->_display();
      return '';
   } //end index
   
   /**
    * Отображение сообщения об успешном создании канала
    *
    */
   public function create_complete() {
      $id_channel = (int) $this->session->userdata(get_class($this) . '_id_channel');
      $data = array();
      if (!empty($id_channel)) {
         $data = array(
            'MESSAGE'  => __('Channel was created successfully. You must create prices for new channel.'),
            'REDIRECT' => $this->site_url . $this->index_page.$this->role . '/manage_channel_prices/index/' . $id_channel
         );
      } else {
         $data = array(
            'MESSAGE'  => __('Channel was created successfully'),
            'REDIRECT' => $this->site_url . $this->index_page. $this->role . '/manage_sites_channels'
         );
      }
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }

   /**
    * Отображение сообщения об успешном редактировании канала
    *
    */
   public function edit_complete() {
      $data = array(
         'MESSAGE' => __('Channel was edited successfully'),
         'REDIRECT' => $this->site_url . $this->index_page. $this->role . '/manage_sites_channels'
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }

   /**
    * обновление данных канала
    *
    * @param array $fields параметры сохраняемого канала
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _save($id, $fields) {
   	try {
   		$channelModel = new Sppc_ChannelModel();
   		$channel = $channelModel->findObjectById($id);
   		if (is_null($channel)) {
   			throw new Sppc_Exception('Specified channel not found');
   		}
   		
   	   // channel ad sources
   	   if ($this->role == 'admin') {
	         $channelAdSources = array();
	         if ($fields['ad_sources_advertisers'] == true) {
	            $channelAdSources[] = Sppc_Channel::AD_SOURCE_ADVERTISERS;
	         }
	         if ($fields['ad_sources_xml_feeds'] == true) {
	            $channelAdSources[] = Sppc_Channel::AD_SOURCE_XMLFEEDS;
	         } 
	         
	   	   if (count($channelAdSources) == 0) {
	            throw new Sppc_Exception('At least one ad source must be selected');
	         }
   	   }
         
         $channel->setName(trim($fields['name']));
         $channel->setDescription(trim($fields['description']));
         if ($channel->getCategory() !== null){
            if ($fields['category'] != $channel->getCategory()){
               $categoryModel = new Sppc_CategoryModel();
	            $categories = $categoryModel->find(explode(',', $fields['category']));
	            $channel->setCategories($categories);
            }
         }else if ($fields['category'] !=  $channel->getParentSiteCategory()) {
	         $categoryModel = new Sppc_CategoryModel();
	         $categories = $categoryModel->find(explode(',', $fields['category']));
	         $channel->setCategories($categories);
         }
         
         
         $channel->setAdSettings($fields['ad_settings']);
         $channel->setAdSources($channelAdSources);
     
         if ($fields['ad_settings'] == 'blank_color') {
            $channel->setBlankColor(str_replace('#', '', $fields['blank_color']));
         } else if ($fields["ad_settings"] == 'tag'){
            $tagModel = new Sppc_TagModel();  
            $tag = $tagModel->findObjectById($channel->getId());
            $tag->setCode($fields['tag_code']);
            $tag->save();
         }
         
   	   if (!empty($fields['channel_type'])) {
            $channel->setChannelType($fields['channel_type']);
         }
         
         $channel->save();
         
        return false;
   	} catch (Exception $e) {
   		return __($e->getMessage());
   	}
   }

   /**
    * Создание сайта
    *
    * @param array $fields параметры создаваемого канала
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _create($fields) {
   	try {
   		if ('' == $fields['format']) {
   			throw new Sppc_Exception('Channel dimension is not selected');
   		}
   		
   	   // check channel ad type
   	   $channelAdTypes = array();
   	   if ($fields['ad_type_text']) {
   	   		$channelAdTypes[] = Sppc_Channel::AD_TYPE_TEXT;
   	   }
   	   if ($fields['ad_type_image']) {
   	   		$channelAdTypes[] = Sppc_Channel::AD_TYPE_IMAGE;
   	   }
   	   if (count($channelAdTypes) == 0) {
   	   		throw new Sppc_Exception('At least one ad type must be selected');
   	   }
   	   
   	   // channel ad sources
   	   if ($this->role == 'admin') {
            $channelAdSources = array();
            if ($fields['ad_sources_advertisers'] == true) {
               $channelAdSources[] = Sppc_Channel::AD_SOURCE_ADVERTISERS;
            }
            if ($fields['ad_sources_xml_feeds'] == true) {
               $channelAdSources[] = Sppc_Channel::AD_SOURCE_XMLFEEDS;
            } 
         } else {
            $channelAdSources = array(
               Sppc_Channel::AD_SOURCE_ADVERTISERS,
               Sppc_Channel::AD_SOURCE_XMLFEEDS
            );
         }
         if (count($channelAdSources) == 0) {
            throw new Sppc_Exception('At least one ad source must be selected');
         }
   		
	   	if (empty($fields['channel_type'])) {
	         $fields['channel_type'] = 'contextual';
	      }
         
         $parentSite = null;
	      if (isset($fields['id_site'])) {
	      	$siteModel = new Sppc_SiteModel();
	      	$parentSite = $siteModel->findObjectById($fields['id_site']);
	      }
         

	      $dimensionModel = new Sppc_DimensionModel();
	      $dimension = $dimensionModel->findObjectById($fields['format']);
	    
	      $channelModel = new Sppc_ChannelModel();
	      $channel = $channelModel->createRow();
	      
	      $channel->setName(trim($fields['name']));
	      $channel->setDescription(trim($fields['description']));
         
         if ($fields['category'] != $parentSite->getCategory()) {
	      	$categoryModel = new Sppc_CategoryModel();
	         $categories = $categoryModel->find(explode(',', $fields['category']));
	         $channel->setCategories($categories);
         }
	      
	      $channel->setDimension($dimension);
	      $channel->setAdTypes($channelAdTypes);
	      $channel->setAdSettings($fields['ad_settings']);
	      
         if ($fields['ad_settings'] == 'blank_color') {
	      	$channel->setBlankColor(str_replace('#', '', $fields['blank_color']));
	      }
	      $channel->setChannelType($fields['channel_type']);
         $channel->setAdSources($channelAdSources);
	      
	      if (!is_null($parentSite)) {
	      	$channel->setParentSite($parentSite);
	      	$channel->addSite($parentSite);
	      }
	      
	      $channel->save();
         
         $tagModel = new Sppc_TagModel();   
         
         if ($fields["ad_settings"] == 'tag'){
            $tag = $tagModel->createRow($channel, $fields["tag_code"]);
         } else {
            $tag = $tagModel->createRow($channel, '');
         }
         $tag->save();
	      
	      // Сохраняем айдишник в сессию
         $this->session->set_userdata(get_class($this) . '_id_channel', $channel->getId());
         
   	} catch (Exception $e) {
   		return __($e->getMessage());
   	}
   	
   	return false;
   }

   /**
    * Загрузка данных канала
    *
    * @param array $fields параметры создаваемого канала
    * @return string описание ошибки ('' при успешной загрузке)
    */
   public function _load($id) {
   	try {
   		$channelModel = new Sppc_ChannelModel();
   		$channel = $channelModel->findObjectById($id);
   		if (is_null($channel)) {
   			throw new Sppc_Exception('Channel is not found');
   		}
         $site = $channel->getParentSite();
         $tagModel = new Sppc_TagModel();
         $tag = $tagModel->findObjectById($id);
         $category = $channel->getCategory();
         if (null === $category) {
            $category = $site->getCategory();
         }
   		$data = array(
   		   'name' => $channel->getName(),
   		   'description' => $channel->getDescription(),
   		   'format' => $channel->getDimension()->getId(),
   		   'ad_type_text' => $channel->isAdTypeAllowed(Sppc_Channel::AD_TYPE_TEXT) ? 'text' : '',
   		   'ad_type_image' => $channel->isAdTypeAllowed(Sppc_Channel::AD_TYPE_IMAGE) ? 'image' : '',
   		   'channel_type' => $channel->getChannelType(),
   		   'ad_settings' => $channel->getAdSettings(),
            'tag_code' => $tag->getCode(),
   		   'blank_color' => '#' . $channel->getBlankColor(),
            'category' => $category,
   		   'ad_sources_advertisers' => $channel->hasAdSource(Sppc_Channel::AD_SOURCE_ADVERTISERS),
   		   'ad_sources_xml_feeds' => $channel->hasAdSource(Sppc_Channel::AD_SOURCE_XMLFEEDS)
   		);
   		return $data;
   	} catch (Exception $e) {
   		return __($e->getMessage());
   	}
   }
   
}
    