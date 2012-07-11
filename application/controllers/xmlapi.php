<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/MY_Controller.php';

/**
 * Контроллер обработки запросов к XML API
 *
 * @author Cherenov Evgenii
 */
class Xmlapi extends MY_Controller {
   
   const XML_TEMPLATE = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><root/>";
   
   const XMLAPI_ERROR_SUCCESS = 0;
   const XMLAPI_ERROR_NO_ACCESS_CODE = 1;
   const XMLAPI_ERROR_NO_ACTION = 2;
   const XMLAPI_ERROR_INVALID_APIKEY = 45;
   const XMLAPI_ERROR_INVALID_API_ACTION =46;
   const XMLAPI_ERROR_API_NO_RESULTS = 47;   
 
   private $xml;
   private $response;
   private $errors;
   private $errorCodes = array();
   
   /**
    * Конструктор класса
    *
    */
   function __construct() {
      parent::__construct();
      $this->load->model('user_api');   
   }
   
   /**
    * Метод по умолчанию
    *
    */
   function index() {
      $this->xml = new SimpleXMLElement(self::XML_TEMPLATE);
   	$this->response = $this->xml->addChild('response');
      $this->errors = $this->response->addChild('errors');
      $apiKey = $this->input->get('apiKey');
      if (!empty($apiKey)) {
      $id_user = $this->user_api->searchUser($apiKey);
         if($id_user){
            $action = $this->input->get('action');
            if($action) {
               switch($action){
	            case 'get_sites_channels':
                        $this->get_sites_channels($id_user);
                        break;
                    case 'get_palettes':
                        $this->get_palettes($id_user);
                        break;
                    case 'test_connection':
                        $this->test_connection();
                        break;
                    case 'create_site':
                        $this->create_site($id_user);
                        break;
                    case 'create_channel':
                        $this->create_channel($id_user);
                        break;
                     case 'get_site_stat':
                        $this->get_site_stat();
                        break;
      	         default:
      	    	      $this->errorCodes[] = self::XMLAPI_ERROR_INVALID_API_ACTION;
               }
            }else{
               $this->errorCodes[] = self::XMLAPI_ERROR_NO_ACTION;
            }
         }else{
            $this->errorCodes[] = self::XMLAPI_ERROR_INVALID_APIKEY;
         }
      }else {
         $this->errorCodes[] = self::XMLAPI_ERROR_NO_ACCESS_CODE;
      }        
      if ($this->errorCodes != null){ $this->generate_xml();}
   }
   
   /**   
    * Вывод всех палитр
    */
   public function get_palettes($id){
      $palettes = $this->user_api->get_color_schemes($id);
      if ($this->errorCodes == null){
	 $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
         $data_node = $this->response->addChild('data');
         $palettes_node = $data_node->addChild('palettes');
         foreach($palettes as $palette){
            $palette_node = $palettes_node->addChild('palette'); 
            $palette_node->addChild('id',$palette['id_color_scheme']);
            $palette_node->addChild('name',$palette['color_schemes_name']);  
         }
      $this->generate_xml();
      }
   }/*get_palettes()*/

   /**
    * Show empty xml data with success code
    *
    * @return void
    */
   public function test_connection(){
      if ($this->errorCodes == null){
	 $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
         $data_node = $this->response->addChild('data');
      $this->generate_xml();
      }
   }//end test_connection()

   /**
    * Create new site
    *
    * @param int $id_user
    * @return void
    */
   public function create_site($id_user){
       $surl = rawurldecode($this->input->get('surl'));
       $sname = rawurldecode($this->input->get('sname'));
       $sdesc = rawurldecode($this->input->get('sdesc'));

       if ($this->errorCodes == null){
	 $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
         if ($site = $this->user_api->create_site($id_user, $surl, $sname, $sdesc))
             $data_node = $this->response->addChild('data');
             $sites_node = $data_node->addChild('sites');
             $site_node = $sites_node->addChild('site');
             $site_node->addChild('id',$site);
             $this->generate_xml();
      }
   }//end create_site();
   
   /**
    * Create new channel
    *
    * @param int $id_user
    * @return void
    */
   public function create_channel($id_user){
       $params = array(
            'name' => rawurldecode($this->input->get('c_name')),
            'id_parent_site' => $this->input->get('siteId'),
            'description' => rawurldecode($this->input->get('c_desc')),
            'id_dimension' => rawurldecode($this->input->get('c_fmt')),
            'ad_type' => rawurldecode($this->input->get('c_adtype')),
            'ad_sources' => rawurldecode($this->input->get('c_adsrc')),
        );

       if ($this->errorCodes == null){
         if ($channel = $this->user_api->create_channel($params)) {

             $this->load->model('channel_program');
             $this->load->model('channel');

             $channel_info = $this->channel->get_info($channel);
             $ad_type = explode(',', $channel_info->ad_type);

             //Set CPM price
             if ($price = $this->input->get('c_cpm')) {
                 $params = array(
                        'title' => "Basic Price",
                        'id_channel' => $channel,
                        'program_type' => 'CPM',
                        'volume' => 1000
                 );
               if (in_array(Sppc_Channel::AD_TYPE_TEXT, $ad_type)) {
                    $params['cost_text'] = type_to_str($price,'mysqlfloat');
                    $params['avg_cost_text'] = type_to_str(1000*$price/1000,'mysqlfloat');
                }

                if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $ad_type)) {
                    if (in_array(Sppc_Channel::AD_TYPE_TEXT, $ad_type)) {
                            $cost_image = $price*$channel_info->max_ad_slots;
                    } else {
                            $cost_image = $price;
                    }
                    $params['cost_image'] = type_to_str($price,'mysqlfloat');
                    $params['avg_cost_image'] = type_to_str(1000*$price/1000,'mysqlfloat');
                }
                $this->channel_program->create($params);
             }

             //Set Flaterate price
             if ($price = $this->input->get('c_flaterate')) {
                 $params = array(
                        'title' => "Basic Price",
                        'id_channel' => $channel,
                        'program_type' => 'Flat_Rate',
                        'volume' => 3
                 );
               if (in_array(Sppc_Channel::AD_TYPE_TEXT, $ad_type)) {
                    $params['cost_text'] = type_to_str($price,'mysqlfloat');
                    $params['avg_cost_text'] = type_to_str($price/3,'mysqlfloat');
                }

                if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $ad_type)) {
                    if (in_array(Sppc_Channel::AD_TYPE_TEXT, $ad_type)) {
                            $cost_image = $price*$channel_info->max_ad_slots;
                    } else {
                            $cost_image = $price;
                    }

                    $params['cost_image'] = type_to_str($cost_image,'mysqlfloat');
                    $params['avg_cost_image'] = type_to_str($cost_image/3,'mysqlfloat');
                }
                $this->channel_program->create($params);
             }
             $data_node = $this->response->addChild('data');
             $sites_node = $data_node->addChild('channels');
             $site_node = $sites_node->addChild('channel');
             $site_node->addChild('id',$channel);
             $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
             $this->generate_xml();
         }
      }
   }//end create_channel();

    /**   
    * Вывод всех сайтоканалов
    */
   public function get_sites_channels($id){
      $siteId = $this->input->get('siteId');
      $sites = $this->user_api->get_sites_channels($id,$siteId);
      if ($sites){
      if ($this->errorCodes == null){
	      $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
         $data_node = $this->response->addChild('data');
         $sites_node = $data_node->addChild('sites');
         $last_site_id = '0';

         foreach($sites as $site){       
            if ($last_site_id != $site['id_site']){
               $last_site_id = $site['id_site'];
               $site_node = $sites_node->addChild('site'); 
               $site_node->addChild('id',$site['id_site']);
               $site_node->addChild('name',$site['site_name']);
               $channels_node = $site_node->addChild('channels');
               $channel_node = $channels_node->addChild('channel');
               $channel_node->addChild('id',$site['id_channel']);
               $channel_node->addChild('name',$site['channel_name']);
               $channel_node->addChild('dimension',$site['id_dimension']);
               $channel_node->addChild('width',$site['width']);
               $channel_node->addChild('height',$site['height']);
               $channel_node->addChild('userid',$id);
	         }else{
               $channel_node = $channels_node->addChild('channel');
               $channel_node->addChild('id',$site['id_channel']);
               $channel_node->addChild('name',$site['channel_name']);
               $channel_node->addChild('dimension',$site['id_dimension']);
               $channel_node->addChild('width',$site['width']);
               $channel_node->addChild('height',$site['height']);
               $channel_node->addChild('userid',$id);
            }
         }
      }
      $this->generate_xml();
         }else{
 	         $this->errorCodes[] = self::XMLAPI_ERROR_API_NO_RESULTS;           
         }
   }
   
   /**   
    * Статистики всех сайтоканалов
    */
   public function get_site_stat(){
      $siteId = $this->input->get('siteId');
      if ($siteId){
         $period = $this->input->get('period');
         $sites = $this->user_api->get_site_stat($siteId,$period);
         if ($sites){
         if ($this->errorCodes == null){
            $this->errorCodes[] = self::XMLAPI_ERROR_SUCCESS;
            $data_node = $this->response->addChild('data');
            $sites_node = $data_node->addChild('sites');
            $last_site_id = '0';
            
            foreach($sites as $site){       
               if ($last_site_id != $site['id_site']){
                  $last_site_id = $site['id_site'];
                  $site_node = $sites_node->addChild('site'); 
                  $site_node->addChild('id',$site['id_site']);
                  $channels_node = $site_node->addChild('channels');
                  $channel_node = $channels_node->addChild('channel');
                  $channel_node->addChild('id',$site['id_channel']);
                  $channel_node->addChild('clicks',$site['clicks']);
                  $channel_node->addChild('impressions',$site['impressions']);
                  $channel_node->addChild('alternative_impressions',$site['alternative_impressions']);
                  $channel_node->addChild('earned_admin',$site['earned_admin']);
               }else{
                  $channel_node = $channels_node->addChild('channel');
                  $channel_node->addChild('id',$site['id_channel']);
                  $channel_node->addChild('clicks',$site['clicks']);
                  $channel_node->addChild('impressions',$site['impressions']);
                  $channel_node->addChild('alternative_impressions',$site['alternative_impressions']);
                  $channel_node->addChild('earned_admin',$site['earned_admin']);
               }
            }
         }
         $this->generate_xml();
         }else{
            $this->errorCodes[] = self::XMLAPI_ERROR_API_NO_RESULTS;           
         }
      }else{
         $this->errorCodes[] = self::XMLAPI_ERROR_API_NO_RESULTS; 
      }
   }
   
    /**   
    * Вывод XML-файла
    */
   public function generate_xml(){ 
      if ($this->errorCodes != null){
         foreach ($this->errorCodes as $code) {
	        $text = '';
            switch ($code) {
               case self::XMLAPI_ERROR_SUCCESS:
   	              $text = 'Success';
                  break;
               case self::XMLAPI_ERROR_NO_ACCESS_CODE:
   	              $text = 'Missing access code or api key';
   	              break;
               case self::XMLAPI_ERROR_NO_ACTION:
   	              $text = 'Missing action parameter';
   	              break;
               case self::XMLAPI_ERROR_INVALID_APIKEY:
   	              $text = "Invalid api key code";
   	              break;
               case self::XMLAPI_ERROR_INVALID_API_ACTION:
   	              $text = "Invalid action parameter (get_sites_channels,get_palettes)";
   	              break;
               case self::XMLAPI_ERROR_API_NO_RESULTS:
   	              $text = "No results";
   	              break;
				  } 
            $error = $this->errors->addChild('error');
            $error->addChild('code', $code);
   	      $error->addChild('description', $text);
		 }
         header('Content-Type: application/xml; charset=utf-8');
   	     print $this->xml->asXML();
   	     exit();
      }
   }
     
}
