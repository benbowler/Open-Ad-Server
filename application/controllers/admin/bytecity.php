<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* Controller for registration on ByteCity
*
* @author Vladimir Yants
*/

class Bytecity extends Parent_controller {
	
	protected $role = "admin";
	
	protected $view = 'admin/bytecity/form.html';
	
	function __construct() {
		parent::__construct();
		$this->load->library("form");
		$this->load->model("feeds");
	}
	
	function index() {
		$form = array(
            "name" => "sign_up", 
            "view" => $this->view, 
            "vars" => array(), 
            "fields" => array(
                  "mail" => array(
                        "display_name" => "E-mail", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text",
						"default" => $this->user_name, 
                        "validation_rules" => "required|valid_email", 
                        'max' => 100), 
                  "password" => array(
                        "display_name" => "Password", 
                        "id_field_type" => "string", 
                        "form_field_type" => "password", 
                        "validation_rules" => "required|min_length[6]", 
                        "default" => NULL, 
                        'max' => 20), 
                  "confirm" => array(
                        "display_name" => "Confirm Password", 
                        "id_field_type" => "string", 
                        "form_field_type" => "password", 
                        "validation_rules" => "required|matches[password]", 
                        'max' => 20),
				   "site_url" => array(
                        "id_field_type" => "string", 
                        "form_field_type" => "hidden",
						"default" => $this->site_url),
					"site_name" => array(
                        "id_field_type" => "string",
						"default" => $this->global_variables->get('SiteName'), 
                        "form_field_type" => "hidden"),
            ));
		
		$form_content = $this->form->get_form_content("create", $form, $this->input, $this);
		$form_content = $this->_translate($form_content);
		
		$this->json_response('form', $form_content);
		
		return;
    	
	}
	
	function _create($fields) {
		$url = "http://bytecity.com/reg";
		$post = array (
			'email' => $fields['mail'],
			'password' => $fields['password'],
			'site_url' => $fields['site_url'],
			'site_name' => $fields['site_name'],
		);
		$curl_handler = curl_init($url);
 		curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
      	curl_setopt($curl_handler, CURLOPT_TIMEOUT, 3000);
      	curl_setopt($curl_handler, CURLOPT_POST, 1);
      	curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $post);
      	$response = curl_exec($curl_handler);
      	curl_close ($curl_handler);
      	if (empty($response)) {
      		$this->json_response('error', 'ByteCity server is not available now!');
      	}
      	//echo $response;
      	$response = json_decode($response, true);
      	
      	if ($response['status'] == 'ok') {
      		$this->feeds->set_settings(1,array('affiliate_id_1'=>$response['data']));
      		$this->json_response('ok', 'ByteCite feed is active now!');
      	} elseif ($response['status'] == 'error') {
      		$this->json_response('error', $response['data']);
      	} else {
      		$this->json_response('error', 'Undefined error.');
      	}
      	$this->json_response('error', 'Undefined error.');
	}
	
	private function json_response ($status, $data) {
		echo json_encode(array('status'=>$status, 'data'=>$data));
		exit;
	} 

}