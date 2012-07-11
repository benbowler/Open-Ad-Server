<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
 * Контроллер предпросмотра объявлений в группе кампании
 *
 */
class Create_Campaign_Step_Preview_Ads extends Campaign_Wizard {
	
	protected $role = "advertiser";
   
   	protected $menu_item = "Manage Ads";
	
	public function __construct() {
	  	parent::__construct();
	  
	  	$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'))));
	  	$this->on_submit = 'onSubmit();';
	  	$this->cancel_creation_controller = 'advertiser/manage_ads';
	  	$this->_add_css('design');
	  	
	  	$this->load->library('form');
	  
	  	$this->load->model('new_campaign');
	}
	
	/**
	 * Отображение страницы с предпросмотром созданных объявлений
	 *
	 * @param string $campaign_type тип создаваемой кампании
	 */
	public function index($campaign_type) {
		$this->set_campaign_type($campaign_type);
		$this->setCurrentStep(uri_string());
		


		$this->new_campaign->init_storage($this->id_xml);

		$error_message = '';
		
		// Нужно ли переходить на следующий шаг?
		if (false !== $this->input->post('complete')) {
		   	// Проверяем, все ли нормально у кампании?
			$error_message = $this->new_campaign->check_ads();
		   	if (empty($error_message)) {
   		   		// Переходим на следующий шаг
   		   		redirect($this->get_next_step_controller());
		   	}
		}
		
	   	$action = $this->input->post('form_action');
      	if ($action) {
         	switch ($action) {
         		case 'delete':
         			$id_ad = $this->input->post('id');
         			if ($id_ad) {
         	     		$this->new_campaign->del_ad($id_ad,'textcode');
         			}
         			break;
         	}
         	$this->new_campaign->save_data();
      	}
		
      	$ads_list = $this->new_campaign->get_ads_list();
      	
      	$ads_preview = '';
   
      	/* Выбранные места */
      	$choosen_places = $this->new_campaign->get_places();      
      
      	// Нужно получить названия и цвета
      	$places_data = array(
      	   'place' => 'sites',
      	   'name' => 'Content Network', 
      	   'color' => '000000');       
      	$choosen_places_view = '';
      	$this->load->model('dimension');
      	$places_errors = array();
            
      	foreach ($ads_list as $ad_id => $ad) {
      		if ('text' == $ad->ad_type) {
			// Подгружаем стили дефолтной color scheme
		 
				$this->db->select('cs.*, ft.name title_font_name, ft2.name text_font_name, ft3.name url_font_name', false);
				$this->db->from('color_schemes cs');
				$this->db->join('fonts ft', 'ft.id_font = cs.title_id_font');
				$this->db->join('fonts ft2', 'ft2.id_font = cs.text_id_font');
				$this->db->join('fonts ft3', 'ft3.id_font = cs.url_id_font');

				$query = $this->db->get();
				$row = $query->row();
		 
      			$ad_preview = $this->parser->parse(
      				'common/text_ad_example.html',
      				array(
      					'TITLE' => type_to_str($ad->title,'encode'),
      					'DESCRIPTION' => type_to_str($ad->description1,'encode'),
      					'DESCRIPTION2' => type_to_str($ad->description2,'encode'),
      					'DISPLAY_URL' => $ad->display_url,
      					'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url,
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
      				FALSE
      			);
      		} elseif('image' == $ad->ad_type) {
      			list($img_w, $img_h, $type, $attr) = getimagesize($this->config->item('path_to_campaign_creation_images').$ad->image_id);
      			if (13 == $type) { //Для флэша используем специальный шаблон
      		    	$ad_preview = $this->parser->parse(
      		    		'common/image_ad_swf_example.html',
      		    		array(
      		    			'TITLE' => type_to_str($ad->title,'encode'),
      		    			'ID_IMAGE' => $ad->image_id,
      		    			'ID_SWF_CONTAINER' => str_replace('.','',$ad->image_id),
      		    			'IMG_W' => $img_w,
      		    			'IMG_H' => $img_h,
      		    			'BGCOLOR' => $ad->bgcolor,
      		    			'DISPLAY_URL' => $ad->display_url,
      		    			'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url
      		    		),
      		    		FALSE
      		    	);
      			} else {
      		 		$ad_preview = $this->parser->parse(
      		 			'common/image_ad_example.html',
      		 			array(
      		 				'TITLE' => type_to_str($ad->title,'encode'),
      		 				'ID_IMAGE' => $ad->image_id,
      		 				'DISPLAY_URL' => $ad->display_url,
      		 				'DESTINATION_URL' => $ad->destination_protocol."://".$ad->destination_url
      		 			),
      		 			FALSE
      		 		);
      			}
      		} elseif('richmedia' == $ad->ad_type){
      	   		// dimension of iFrame
      	   		$this->load->model('dimension');
      	   		$dim = $this->dimension->get_info($ad->id_dimension);         	   
				$ad_preview = $this->parser->parse(
					'common/richmedia_ad_example.html',
					array(
						'RICH_CONTENT' => $ad->rich_content,
						'IFRAME_SRC' => base_url() . 'advertiser/create_campaign_step_preview_ads/previewAd/'.$ad_id,
						'WIDTH' => $dim->width, 
						'HEIGHT' => $dim->height
					),
					FALSE 
				);
      		}   	   

      		// Warnings ======================
         	$warnings = array();
         	$messages = array();
         	foreach($places_data as $place){
            	if(in_array($place['place'],$choosen_places)){
               		$choosen_places_view[] = '<span class="place_name" style="color:#'.$place['color'].'">'.$place['name'].'</span>';
               
               		if(!$this->dimension->checkPlaceSuitability($place['place'],array($ad))){
                  		$messages[] = array('MESSAGE' => '<span class="place_name" style="color:#'.$place['color'].'">'.$place['name'].'</span> ' . __('doesn\'t support this format.'));
               		}
            	}               
         	}   

         	if(count($messages)){
            	$warnings = array(array('MESSAGES'=>$messages));
         	}
         
      		// Warning END ===================
      	
      		$ads_preview.= $this->parser->parse('advertiser/manage_ads/campaigns/creation/preview_ads/ad_box.html',array('AD_PREVIEW' => $ad_preview, 'AD_ID' => $ad_id, 'WARNINGS' => $warnings),FALSE);
      	}
      	
		$data = array(
		    'ERROR_MESSAGE' => ('' != $error_message)?$this->parser->parse('advertiser/manage_ads/campaigns/creation/preview_ads/error.html',array('ERROR' => $error_message),FALSE):'',
		    'NEXT_CONTROLLER_URL' => $this->get_next_step_controller(), 
		    'EDIT_ADS_URL' => $this->get_previous_step_controller(),
		    'ADD_MORE_ADS_URL' => $this->get_previous_step_controller(),
		    'ADS_PREVIEW' => $ads_preview ,
		    'CAMPAIGN_SCHEME' => $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html','',TRUE)
		);
		$this->_set_content($this->parser->parse('advertiser/manage_ads/campaigns/creation/preview_ads/body.html',$data,FALSE));
		$this->_display();
	}
	
	/**
	 * SRC для iframe
	 *
	 * @param int $id
	 */
	public function previewAd($id){
	  	$this->new_campaign->init_storage($this->id_xml); 
	  	$ad = $this->new_campaign->get_ad($id);
	  	header('Content-type: text/html; charset=utf-8');
	  	echo $ad->rich_content; 
	}
	
  	public function previewAd_direct($id){
     	$this->db->select('rich_content');
     	$this->db->from('ads_rich');
     	$this->db->where('id_ad',$id);
     	$res = $this->db->get();
     	$row = $res->row();
     
     	header('Content-type: text/html; charset=utf-8');
     	echo $row->rich_content; 
   	}
}