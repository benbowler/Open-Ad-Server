<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/advertiser/create_campaign_step_create_ad.php';

/**
 * Контроллер редактирования сайтов и каналов для группы
 *
 */
class Create_ad extends Create_Campaign_Step_Create_Ad {

   protected $role = "advertiser";
	
	protected $menu_item = "Manage Ads";
	
	public $id_group;
   
   public function Create_ad() {
		parent::__construct();
		$this->cancel_confirmation = '';
		$this->create_save_button = 'Save';
		$this->progressbar = FALSE;
		$this->image_dir = $this->config->item('path_to_campaign_creation_images');
   } //end Create_ad

   /**
   * по коду объявления определяет его тип
   *
   * @param integer $ad_id уникальный код объявления
   * @return string тп объявления (text, image)
   */
   public function _get_ad_type($ad_id) {
      
   	$ad_info = $this->ads->get(type_cast($ad_id, 'textcode'));
      return $ad_info['ad_type'];
   } //end _get_ad_type

   public function index() {
      $this->load->model('ads', '', TRUE);
      $this->load->model('groups', '', TRUE);
      
      $id_ad = $this->input->post('id');
      
      if ($id_ad) {
         $id_ad_decoded = type_cast($id_ad, 'textcode');
         $this->load->model('ads', '', TRUE);
         $id_group_decoded = $this->ads->group($id_ad_decoded);
         $group_code = type_to_str($id_group_decoded, 'textcode');
         $this->id_group = $group_code;
      } else {
      	$this->load->model('new_campaign');
         $this->new_campaign->free_storage($this->id_xml);
         $this->session->unset_userdata('id_xml');
      }

      $id_group = $this->input->post('id_group');
      
      if(isset($id_ad_decoded)){
      $this->img_src=$this->ads->image($id_ad_decoded, TRUE);
      $this->img_dim=$this->ads->get_ad_dimensions($id_ad_decoded);
      }
	  
      if ($id_group) {
         $id_group_decoded = type_cast($id_group,'textcode');
         $group_code = type_to_str($id_group_decoded, 'textcode');
         $this->id_group = $id_group;
      }
      
      $campaign_name = type_to_str($this->groups->parent_campaign($id_group_decoded), 'encode');
      $id_campaign = type_to_str($this->groups->id_parent_campaign($id_group_decoded), 'textcode');
     
      $group_name = type_to_str($this->groups->name($id_group_decoded), 'encode');
      
      $this->cancel_creation_controller = "advertiser/manage_ads/group/$group_code/ads";
    
      if ($id_ad) {
         $this->next_step = 'advertiser/create_ad/success/'.$group_code.'/true';
      } else {
         $this->next_step = 'advertiser/create_ad/success/'.$group_code.'/false';
      }
      
      $this->upload_controller = "advertiser/create_ad/upload_image/$group_code";
      
      $this->form_title = '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads">{@Manage Ads@}</a> &rarr; '.
                          '<a href="advertiser/manage_ads/campaign/'.$id_campaign.'">{@Campaign@}:</a> <span class="green i">&bdquo;'.$campaign_name.'&ldquo;</span> &rarr; '.
                          '<a href="<%SITEURL%><%INDEXPAGE%>advertiser/manage_ads/group/'.$group_code.'/ads">{@Group@}:</a> <span class="green i">&bdquo;'.$group_name.'&ldquo;</span> &rarr; ';
      
      if ($id_ad) {
      	$ad_info = $this->ads->get($id_ad_decoded);
      	
      	if($ad_info['ad_type'] == 'richmedia'){
      	  $ad_info['title'] = __('Rich Media Ad');   
      	}
      	
      	$this->ad_id = $id_ad;
         $this->form_title .= __('Edit Ad') . ': <span class="green i">&bdquo;'.type_to_str($ad_info['title'],'encode').'&ldquo;</span>';
         $this->image_dir = $this->config->item('path_to_images');
      } else {
      	$this->form_title .= __('Create Ad');
      }

      
      parent::index('create_ad');
   } //end index

   public function _create($fields) {
      $this->load->model('ads', '', TRUE);
      $id_group = type_cast($this->id_group, 'textcode');
      if(isset($fields['display_url'])){
      $fields['display_url'] = trim($fields['display_url']);
      }
      if(isset($fields['destination_url'])){
      $fields['destination_url'] = trim($fields['destination_url']);
      }
      
      $this->ads->add($id_group, $fields, $this->input->post('ad_type'));
   }

   public function _save($id, $fields) {
      
      if(isset($fields['rich_content'])){
         /* */
         $fields['id_dimension'] =  $fields['format'];
      }else{
   	$fields['display_url'] = trim($fields['display_url']);
      $fields['destination_url'] = trim($fields['destination_url']);
      }
      
      $this->ads->save(type_cast($id, 'textcode'), $fields, $this->input->post('ad_type'));
   } //end _save

   public function _load($id) {
      $this->load->model('ads', '', TRUE);
      $fields = $this->ads->get(type_cast($id, 'textcode'));

      
      if($fields['ad_type'] == 'richmedia'){
         $fields['format'] = $fields['id_dimension'];
      }
      
      if(array_key_exists('id_dimension',$fields)) {
      	 $this->load->model('dimension');
          $dim_info = $this->dimension->get_info($fields['id_dimension']);
          $fields['img_w'] = $dim_info->width;
          $fields['img_h'] = $dim_info->height;
      }
      return $fields; 
   } //end _load

   public function success($code, $edit) {
      $data = array(
         'MESSAGE' => __('Congratulations! New ad was successfully created!'),
         'REDIRECT' => $this->site_url.$this->index_page.'advertiser/manage_ads/group/'.$code.'/ads'
      );
      if ($edit == 'true') {
         $data['MESSAGE'] = __('Congratulations! Ad was successfully updated!');
      }
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end successfully

} //end class Create_ad