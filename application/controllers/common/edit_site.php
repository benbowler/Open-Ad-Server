<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * контроллер для создания/изменения сайта
 *
 * @author Немцев Андрей
 * @project SmartPPC6
 * @version 1.0.0
 */
class Common_Edit_Site extends Parent_controller {

	protected $menu_item = "Manage Sites/Channels";

	protected $site_id = null;
	
	protected $_site = null;

	protected $allowed_extensions = array (
		'gif',
		'jpg',
		'png' );

	/**
	 * конструктор класса
	 *
	 * @return ничего не возвращает
	 */
	public function __construct() {
		parent::__construct ();

		$this->load->model ( 'site' );
		$this->load->model ( 'category_model' );  

		$this->_add_ajax ();
		
		
		$this->_add_css ('fields/category-selector');

		$this->load->library ( "form" );
		
		$this->load->helper('categoryselector');

	} //end __construct


	/**
	 * показывает форму для изменения сайта или его создания
	 *
	 * @param $id - идентификатор редактируемого сайта, если null - создание сайта
	 * @return ничего не возвращает
	 */
	public function index($id = null) {
		$current_site_info = null;

		$categories_tree = $this->category_model->get_html_tree ();

		$id_channel = $this->input->post ( 'id_channel' );

		if (! empty ( $id )) {
			$siteModel = new Sppc_SiteModel();
			$this->_site = $siteModel->findObjectById($id);
			
			$mode = "modify";

			// Set Site layout
			try {
				if (is_null ( $this->_site )) {
					throw new Exception ( 'New site' );
				}
				$layoutModel = new Sppc_Site_LayoutModel ( );
				$layout = $layoutModel->findBySite ($this->_site);
				if (is_null ( $layout )) {
					throw new Exception ( 'Site without layout' );
				}
				$this->_add_java_script_inline ( "var siteLayoutJson='{$layout->toString()}';",'siteLayoutJson' );
			} catch ( Exception $e ) {
				$this->_add_java_script_inline ( "var siteLayoutJson='';",'siteLayoutJson' );
			}
		} else {
			$this->_add_java_script_inline ( "var siteLayoutJson='';", 'siteLayoutJson' );
		}

		if (is_null ($this->_site ) || ($this->user_id != $this->_site->getIdEntityPublisher())) {
			$this->_site = null;
			$mode = "create";
		}
      
		$form = array (
			"id" => (is_null($this->_site)) ? '' : $this->_site->getId(),
			"name" => "site_form",
			"view" => $this->views_paths ['site_form'],
			"vars" => array (
				'CATEGORIES_TREE' => $categories_tree,
				'IMAGE' => $this->site->get_thumb ( $this->site_id )."?ac=".rand(100,9999999),
				'IMAGEPATH' => ltrim ( $this->config->item ( 'path_to_images' ), './' )),
			"fields" => array (
				"domain" => array (
					"display_name" => __( "Site URL" ),
					"id_field_type" => "string",
					"form_field_type" => "text",
					"validation_rules" => "trim|required|hostname",
					"max" => 150 ),
				"title" => array (
					"display_name" => ("Title"),
					"id_field_type" => "string",
					"form_field_type" => "text",
					"validation_rules" => "trim|required",
					"max" => 100 ),
				"description" => array (
					"display_name" => __( "Description" ),
					"id_field_type" => "string",
					"form_field_type" => "textarea",
					"validation_rules" => "trim|required",
					"max" => 300 ),
            "category" => array (
					"display_name" => __( "Category" ),
					"id_field_type" => "int",
					"form_field_type" => "hidden",
					"validation_rules" => "required" ),
				'thumb_id' => array (
					'id_field_type' => 'string',
					'form_field_type' => 'hidden',
					'default' => 'DEFAULT'),
				'layout_json' => array (
					'id_field_type' => 'string',
					'form_field_type' => 'text' ) ) );

		if ($this->input->post ( 'redirect_after_save' )) {
			$form ['redirect'] = $this->input->post ( 'redirect_after_save' );
		}

		switch ($mode) {
			case 'create' :
				$form ['vars'] ['FORM_TITLE'] = "Create site";
				$form ['vars'] ['CURRENT_SITE_NAME'] = '';
				$form ['vars'] ['APPLY_BTN_CAPTION'] = "Create";
				if (! array_key_exists ( 'redirect', $form )) {
					$form ['redirect'] = "/" . $this->role . "/edit_site/create_complete";
				}
				if ($id_channel) {

					$this->load->model ( 'channel' );
					$channel_info = $this->channel->get_info ( $id_channel );

					$form ['vars'] ['FORM_TITLE'] = sprintf ( __( "Create site and add channel %s to this site" ), '<span class="green i">&bdquo;' . $channel_info->name . '&ldquo;</span>' );

					$form ['fields'] ['id_channel'] = array (
						"display_name" => "{@ID channel@}",
						"id_field_type" => "string",
						"form_field_type" => "hidden",
						"default" => $id_channel );
				}
				break;
			case 'modify' :
				$form ['vars'] ['FORM_TITLE'] = __( "Edit site" ) . ':';
				$form ['vars'] ['CURRENT_SITE_NAME'] = '&bdquo;' . type_to_str ( $this->_site->getName(), 'encode' ) . ' (' . $this->_site->getUrl() . ')&ldquo;';
				$form ['vars'] ['APPLY_BTN_CAPTION'] = "Save";
				if (! array_key_exists ( 'redirect', $form )) {
					$form ['redirect'] = "/" . $this->role . "/edit_site/edit_complete/".type_to_str($this->_site->getId(),'textcode');
				}
				break;
		}
		
      $this->_add_java_script('ajaxupload.3.9');
      $this->_set_content ( $this->form->get_form_content ( $mode, $form, $this->input, $this ) );
		$this->_display ();
	} //end index

	public function _validationFailed($fields, $errors) {
	    $this->_unset_java_script_inline('siteLayoutJson');
	    $this->_add_java_script_inline ( "var siteLayoutJson='".$fields['layout_json']."';",'siteLayoutJson' );
	}

	/**
	 * Отображение сообщения об успешном создании новости
	 *
	 * @param string $id Индентификатор созданного сайта 
	 */
	public function create_complete($siteId = '') {
		$verificationRequired = (bool) $this->global_variables->get('SitesOwnershipConfirmationRequired', 0, '0');
		if ($verificationRequired) {
			$redirectUri = $this->site_url .$this->index_page. $this->role . '/approve_site/index/'.$siteId;
		} else {
			$redirectUri = $this->site_url .$this->index_page. $this->role . '/manage_sites_channels';
		}
		$data = array (
			'MESSAGE' => __( 'Site was created successfully' ),
			'REDIRECT' =>  $redirectUri
		);
		$content = $this->parser->parse ( 'common/infobox.html', $data, FALSE );
		$this->_set_content ( $content );
		$this->_display ();
	}

	/**
	 * Отображение сообщения об успешном редактировании новости
	 *
	 */
	public function edit_complete($site_code = NULL) {
		$site_channels_list = NULL;
		if (!is_null($site_code)) {
			$this->load->model('channel');
			$id_site = type_cast($site_code,'textcode');
			$site_channels_list = $this->channel->get_sites_channels(array('fields' => 'channels.id_channel', 
			                                                               'site_id_filter' => $id_site,
			                                                               'show_deleted_channels' => false,
			                                                               'disable_site_ordering' => true));
		}
		
		if (is_null($site_channels_list)) {
			$redirect_url = $this->site_url .$this->index_page. $this->role . '/manage_sites_channels';
		} else {
			$redirect_url = $this->site_url .$this->index_page. $this->role . '/edit_site_channel_layout/index/'.$id_site;
		}
		
		$data = array (
			'MESSAGE' => __( 'Site was edited successfully' ),
			'REDIRECT' => $redirect_url );
		$content = $this->parser->parse ( 'common/infobox.html', $data, FALSE );
		$this->_set_content ( $content );
		$this->_display ();
	}

	/**
	 * обновление данных сайта
	 *
	 * @param array $fields параметры сохраняемого сайта
	 * @return string описание ошибки ('' при успешном создании)
	 */
	public function _save($id, $fields) {
		if (is_null($this->_site)) {
			return __('Site not found');
		}
		
	   if(!is_numeric($fields['thumb_id'])) {
         $to = $this->config->item ( 'path_to_images' ) . 'thumbs/' . $id . '.jpeg';
         if (file_exists($to) && strtolower($to) != 'default') {
            unlink($to);
         }         
   	   if (strtolower($fields ['thumb_id']) != 'default') {
   			$from = $this->config->item ( 'path_to_images' ) . 'thumbs/' . $fields ['thumb_id'] . '.jpeg';
   			rename ( $from, $to );
   		}
	   }
		$categoryModel = new Sppc_CategoryModel();
		$category = $categoryModel->find(explode(',', $fields['category']));
		
		$this->_site->setUrl(trim($fields['domain']));
		$this->_site->setName(trim($fields['title']));
		$this->_site->setDescription(trim($fields['description']));
		$this->_site->setCategories($category);
		
		try {
			$this->_site->save();
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		$siteLayoutModel = new Sppc_Site_LayoutModel();
        $siteLayoutModel->updateFromJson($this->_site, trim ( $fields ['layout_json'] ));
		
		return '';
	}

	/**
	 * Создание сайта
	 *
	 * @param array $fields параметры создаваемого сайта
	 * @return string описание ошибки ('' при успешном создании)
	 */
	public function _create($fields) {
		
		$siteModel = new Sppc_SiteModel();
		$site = $siteModel->createRow();
		
		/* @var Sppc_Site $site */
		
		$categoryModel = new Sppc_CategoryModel();
		$category = $categoryModel->find(explode(',', $fields['category']));
      
		$site->setUrl(trim($fields['domain']));
		$site->setName(trim($fields['title']));
		$site->setCreationDate(Zend_Date::now());
		$site->setDescription(trim($fields['description']));
		$site->setCategories($category);
		$site->setIdEntityPublisher($this->user_id);
		
		$siteStatus = 'active';
		if ($this->role != 'admin') {
			$siteConfirmationRequired = (bool) $this->global_variables->get('SitesOwnershipConfirmationRequired', 0, '0');
			$siteAdminApproveRequired = (bool) $this->global_variables->get('ApproveSites', 0, '0');
			
			if ($siteConfirmationRequired) {
				$siteStatus = 'unapproved'; 
			} else if($siteAdminApproveRequired) {
				$siteStatus = 'pending';
			}
		}
		$site->setStatus($siteStatus);
		
		if (array_key_exists('id_channel', $fields)) {
			$channelModel = new Sppc_ChannelModel();
			$channel = $channelModel->findObjectById($fields['id_channel']);
			
			if (!is_null($channel)) {
				$site->addChannel($channel);
			}
		}
		
		try {
			$site->save();
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		

		$siteLayoutModel = new Sppc_Site_LayoutModel();
        $siteLayoutModel->updateFromJson($site, trim ( $fields ['layout_json'] ));
		
		if (strtolower($fields ['thumb_id']) != 'default') {
			$from = $this->config->item ( 'path_to_images' ) . 'thumbs/' . $fields ['thumb_id'] . '.jpeg';
			$to = $this->config->item ( 'path_to_images' ) . 'thumbs/' . $site->getId() . '.jpeg';
			if (file_exists($to)) {
				unlink($to);
			}
			rename($from, $to);
		}
		
		if (($this->role != 'admin') && ($siteConfirmationRequired)) {
			redirect($this->role . '/edit_site/create_complete/'.$site->getId());
			exit();
		}
		
		return '';
	}

	public function _validator($fields) {

		$trimmed_url = trim ( $fields ['domain'] );
		if ($trimmed_url != '') {
			$siteModel = new Sppc_SiteModel();
			$site = $siteModel->findByUrl($trimmed_url);

			$check = false;

			if(is_null($this->_site)){
			   $check = true;   
			} elseif(!is_null($site)) {
			   $check = ($site->getId() != $this->_site->getId());
			}
			
			if ((!is_null($site)) && $check) {
				$this->validation->domain_error = "<p class='errorP'>" . __( "Site with such URL is already exists" ) . ".</p>";
				return FALSE;
			}
		}
		
		return TRUE;
	}

	/**
	 * Загрузка данных сайта
	 *
	 * @param array $fields параметры создаваемого сайта
	 * @return string описание ошибки ('' при успешном создании)
	 */
	public function _load($id) {
		if (! is_null ( $this->_site )) {
			// Set Site layout
			return array (
				'domain' => $this->_site->getUrl(),
				'title' => $this->_site->getName(),
				'description' => $this->_site->getDescription(),
            'category' => $this->_site->getCategory(),
				'categories' => implode(',', array_keys($this->_site->getCategories())),
			   'thumb_id' => $this->site->get_thumb($id, TRUE));
		} else {
			return "Site is not found";
		}
	}

	/**
	 * возвращает иконку сайта по его адресу
	 *
	 */
	public function thumb() {
		$url = $this->input->post ( "url" );
		$id_site = 'temp/' . uniqid ();
		$file = file_get_contents ( 'http://open.thumbshots.org/image.pxf?url=http://' . $url );
		$md5 = md5($file);
		if ($md5 == "78d1311ce3916906af37ebeb078d92dc" || $md5 == '8e89579783ed03763543e8284a22fec0') {
			$id_site = 'default';
		} else {
			$localfile = $this->config->item ( 'path_to_images' ) . 'thumbs/' . $id_site . '.jpeg';
			file_put_contents ( $localfile, $file );
		}
		echo $id_site;
		/*$netfile = $this->get_siteurl().ltrim($this->config->item('path_to_images'),'./').
         'thumbs/'.$id_site.'.jpeg';
      echo '<img src="'.$netfile.'" style="border: 1px; border-style: solid; border-color=\'black\'; margin-right: 10px;">';*/
	} //end thumb


	/**
	 * Обработчик загрузки иконки сайта
	 *
	 */
	public function upload_image() {
		$result = array (
			'id_image' => 'temp/' . uniqid (),
			'error' => NULL );
		$localfile = $this->config->item ( 'path_to_images' ) . '/thumbs/' . $result ['id_image'] . '.jpeg';
		$config ['upload_path'] = $this->config->item ( 'path_to_images' ) . '/thumbs/temp';
		$config ['allowed_types'] = implode ( '|', $this->allowed_extensions );
		$config ['max_size'] = '1000';
		$this->load->library ( 'upload', $config );
		if ($this->upload->do_upload ( 'thumb_upload_file' )) {
			$upload_data = $this->upload->data ();
			list ( $img_w, $img_h, $type ) = getimagesize ( $upload_data ['full_path'] );
			$result ['img_w'] = $img_w;
			$result ['img_h'] = $img_h;
			$result ['file_type'] = $type;
			$wrong_dimension = $img_w != 120 || $img_h != 90;
			if ($wrong_dimension) {
				unlink ( $upload_data ['full_path'] );
				$result ['error'] = __( 'Uploaded file has wrong dimensions' ) . ': ' . $img_w . 'x' . $img_h . "\n" . __( 'Only thumbshots 120x90 supported.' );
				echo json_encode ( $result );
			} else {
				switch ($type) {
					case IMAGETYPE_GIF :
						$im = imagecreatefromgif ( $upload_data ['full_path'] );
						imagejpeg ( $im, $localfile );
						unlink ( $upload_data ['full_path'] );
						break;
					case IMAGETYPE_PNG :
						$im = imagecreatefrompng ( $upload_data ['full_path'] );
						imagejpeg ( $im, $localfile );
						unlink ( $upload_data ['full_path'] );
						break;
					case IMAGETYPE_JPEG :
						rename ( $upload_data ['full_path'], $localfile );
						break;
				}
				echo json_encode ( $result );
			}
		} else {
			$result ['error'] = __( $this->upload->display_errors ( '', '' ) );
			echo json_encode ( $result );
		}
	}

   /**
    * Отправка описания выбранной категории в формате JSON.  
    *
    */
   public function ajax_get_description() {
      $id = $this->input->post('id_category');
      
      $this->load->model("category_model");
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