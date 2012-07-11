<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

class Create_group extends Parent_controller {
	
	protected $role = "advertiser";
   
   protected $menu_item = "Manage Ads";	
   
   protected $campaign_type = NULL;
	
	public function Create_group() {
		parent::Parent_controller();	  
		$this->load->library('form');
	} //end Create_group
	
	public function index() {
	   $campaign_code = $this->input->post('campaign');
	   $id_campaign = type_cast($campaign_code, 'textcode');
	   $group = $this->input->post('group');
	   $this->load->model('campaigns', '', TRUE);	   
      $this->campaign_type = $this->campaigns->get_type($id_campaign);
      $campaign_name = type_to_str($this->campaigns->name($id_campaign), 'encode');
      $is_cpc = ($this->campaign_type == 'cpc');
//	  $view = $is_cpc?'advertiser/manage_ads/groups/create_cpc.html':'advertiser/manage_ads/groups/create.html';
		$form = array(
         'name'        => 'group_form',
		   'vars'        => array(	
		      'CAMPAIGN_CODE' => $campaign_code,
		      'CAMPAIGN_NAME' => $campaign_name,
		      'CAMPAIGN_SCHEME' => 
		         $this->load->view('advertiser/manage_ads/campaigns/campaign_scheme.html', '', TRUE)),
         'view'        => 'advertiser/manage_ads/groups/create.html',
		   'redirect'    => "advertiser/create_group/success/$campaign_code/false",
         //'redirect'    => "advertiser/manage_ads/campaign/$code",
		   'fields'      => array(                     
            'group_name' => array(
               'display_name'     => __('Group Name'),
               'id_field_type'    => 'string',
               'form_field_type'  => 'text',
               'validation_rules' => 'required',
		         'max' => 50              
            ),
            'budget' => array(
               'display_name'     => __('Daily Budget'),
               'id_field_type'    => 'float',
               'form_field_type'  => 'text',
               'validation_rules' => 'float|positive'
            ),
            'campaign' => array (
               'id_field_type'    => 'string',
               'form_field_type'  => 'hidden',            
               'default' => $campaign_code                    
            ),
            'group' => array (
               'id_field_type'    => 'string',
               'form_field_type'  => 'hidden',            
               'default' => $group                    
            )
         ) 
      );	
      if ($this->campaign_type != 'cpc') {
      	$form['kill'] = array('cpc_only');
      	unset($form['fields']['budget']);
      }
      if ($group) {
         $this->load->model('groups', '', TRUE);
         $name = type_to_str($this->groups->name(type_cast($group, 'textcode')), 'encode');
         $form['id'] = $group;
         $form['redirect'] = "advertiser/create_group/success/$campaign_code/true";
         $mode = 'modify';
         $form['vars']['TITLE'] =  __('Edit Group').': <span class="green i">&bdquo;' . $name . '&ldquo;</span>';
         $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'), __('Edit Group'))));      
      } else {
         $mode = 'create';
         $form['vars']['TITLE'] = __('Create Group');
         $this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'), __('Create Group'))));     
      }
		$this->_set_content($this->form->get_form_content($mode, $form, $this->input, $this));
		$this->_display();
	}
	
	public function _load($id) {
      $this->load->model('groups', '', TRUE);
      $id_group = type_cast($id, 'textcode');
      $group = $this->groups->name($id_group);
      $fields = array(
        'group_name' => $group
      );
      if ($this->campaign_type == 'cpc') {
      	$budget = $this->groups->get_daily_budget($id_group);
      	if ($budget) { 
           $fields['budget'] = $budget;
      	}
      }
	   return $fields;
	} //end _load
	
   public function _save($id, $fields) {
      $this->load->model('groups', '', TRUE);
      $id_group = type_cast($id, 'textcode');
      if ($this->campaign_type == 'cpc') {
           $daily_budget = $fields['budget'];
           $daily_budget = ($daily_budget=='')?NULL:$daily_budget;
      	   //$daily_budget = $this->groups->get_daily_budget($id_group);
           /*if (!is_null($daily_budget) && (($daily_budget < $fields['default_bid']) || ($daily_budget < $fields['default_bid_image']))) {
            return sprintf(__("Default Bids can't be greater than Daily Budget (%s)!"),type_to_str($daily_budget,'money'));
           }*/
      	
         //$this->groups->set_default_bid($id_group, $fields['default_bid']);
         //$this->groups->set_default_bid_image($id_group, $fields['default_bid_image']);
         $this->groups->set_daily_budget($id_group, $daily_budget);
         $this->load->model('entity');
         $this->entity->update_cpc_sites(array('id_group' => $id_group));
      }
      $this->groups->rename($id_group, $fields['group_name']);
   } //end _save
   
   public function _create($fields) {
      $this->load->model('groups', '', TRUE);
    	$default_bid =  ($this->campaign_type == 'cpc')?
    	   type_to_str($this->global_variables->get('DefaultTextBid',0,'0.01'), 'float'):
    	   0; 
    	$default_bid_image = ($this->campaign_type == 'cpc')?
    	   type_to_str($this->global_variables->get('DefaultImageBid',0,'0.01'), 'float'):
    	   0; 
      $id_group = $this->groups->add(type_cast($fields['campaign'], 'textcode'), $fields['group_name'], $default_bid, $default_bid_image);
      if ($this->campaign_type == 'cpc') {
      	  if ($fields['budget'] != '') {
      	     $this->groups->set_daily_budget($id_group, $fields['budget']);
      	  }
      }
   } //end _create
   
   public function success($code, $edit) {
      $mode = ($edit == 'true') ? __('Edit Group') : __('Create Group');
   	$this->_set_title ( implode(self::TITLE_SEP, array(__('Advertiser') , __('Manage Ads'), $mode, __('Success'))));      
   	$data = array(
         'MESSAGE' =>  __('Congratulations! New group was successfully created!'),
         'REDIRECT' => $this->site_url.$this->index_page."advertiser/manage_ads/campaign/$code",
      );
      if ($edit == 'true') {
         $data['MESSAGE'] = __('Congratulations! Group was successfully updated!');
      }
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_approve   
   
   
} //end class Create_group