<?php
if (! defined( 'BASEPATH' ) || ! defined( 'APPPATH' ))
    exit( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

abstract class Common_Site_Information extends Parent_controller {

    protected $template = 'common/parent/jq_iframe.html';

    public function __construct() {
        parent::__construct();
        $this->load->library("Plugins", array('path' => array('common', 'siteinformation')));
    }

    public function index($site_code = NULL) {

        $error_flag = false;
        $error_message = '';
        if (is_null( $site_code )) {
            $error_flag = true;
            $error_message = __( 'Site is not found' );
        }

        if (! $error_flag) {
            $id_site = type_cast( $site_code, 'textcode' );

            $this->load->model( 'site' );
            $site_info = $this->site->get_list( array (
                'fields' => 'sites.name, sites.url, SUM(stat_sites.impressions) as impressions, ' . 'UNIX_TIMESTAMP(sites.creation_date) as creation_date, site_categories.id_category as id_category, ' . 'sites.description',
                'site_id_filter' => array ($id_site ),
            	'joinCategories' => true 
            ));


            try {
                if (is_null( $site_info )) {
                    throw new Exception( 'Site was not found' );
                }
                $siteModel = new Sppc_SiteModel( );
                $site = $siteModel->findObjectById( $id_site );
                if (is_null( $site )) {
                    throw new Exception( 'Site was not found' );
                }

                try {
                    $siteLayoutModel = new Sppc_Site_LayoutModel( );
                    $siteLayout = $siteLayoutModel->findBySite( $site );
                    if (is_null( $siteLayout )) {
                        throw new Exception( 'layout is not found' );
                    }
                    $jsonLayout = $siteLayout->toString();
                    
                    //die($jsonLayout);
                    
                    //$this->_add_java_script_inline( "var siteLayoutJson='{$jsonLayout}';" );
                    $this->content['JSCODE'] = "siteLayoutJson='{$jsonLayout}';";
                } catch ( Exception $e ) {
                    //$this->_add_java_script_inline( "var siteLayoutJson='';" );
                    $this->content['JSCODE'] = "siteLayoutJson='';";
                }
            } catch ( Exception $e ) {
                $error_flag = true;
                $error_message = __( 'Site is not found' );
            }

        }

        if ($error_flag) {
            $data = array (
                    'MESSAGE' => __( $error_message ),
                    'REDIRECT' => $this->site_url .$this->index_page. $this->role . '/site_directory' );
            $content = $this->parser->parse( 'common/errorbox.html', $data, FALSE );
            $this->_set_content( $content );
            $this->_display();
            return;
        }

        $site_info = $site_info[0];

        $this->load->model( 'category_model', 'category' );

        $category_list = $this->category->get_list_by_site($id_site);
                
        $this->load->model( 'channel' );

        
        
        $channels_list = $this->channel->get_list( array (
                'fields' => 'channels.id_channel as channel_code, channels.name as channel_name',
                'show_deleted_in_site_channels' => false,
                'hide_wo_programs' => true,
                'site_id_filter' => $id_site ) );
        if(is_null($channels_list)) {
        	$channels_list=array();
        }
        foreach ( $channels_list as &$channel ) {
            $channel['id_channel'] = $channel['channel_code'];
            $channel['channel_code'] = type_to_str( $channel['channel_code'], 'textcode' );
        }
        $channels_list = array_merge($channels_list, $this->plugins->run('get_channels', $this));
        
        $rank_script = "http://xslt.alexa.com/site_stats/js/s/a?url={$site_info['url']}";

        $vars = array (
                'SITE_VIEWS_COUNT' => type_to_str( $site_info['impressions'], 'impressions' ),
                'SITE_CREATION_DATE' => type_to_str( $site_info['creation_date'], 'date' ),
                'SITE_CATEGORY' => implode(', ', $category_list),
                'SITE_DESCRIPTION' => nl2br( type_to_str( $site_info['description'], 'encode' ) ),
                'SITE_DESTINATION_URL' => $site_info['url'],
                'SITE_TITLE' => type_to_str( $site_info['name'], 'encode' ),
                'SITE_CODE' => $site_code,
                'SITE_CHANNELS_LIST' => json_encode( $channels_list ),
                'IMAGE' => $this->site->get_thumb( $id_site ),
                'SITE_ALEXA_RANK' => $rank_script,
                'ROLE' => $this->role );
        
        $this->_set_content( $this->parser->parse( "common/site_directory/site_information/template.html", $vars, TRUE ) );
        $this->_display();
    } //end index

    public function get_channel_details() {
        $channel_code = $this->input->post( 'channel_code' );
        $site_code = $this->input->post( 'site_code' );

        $error_flag = false;
        $error_message = '';
        if ((! $channel_code) || (! $site_code)) {
            $error_flag = true;
            $error_message = __( 'Site or Channel code is not specified' );
        }

        if (! $error_flag) {
        	   $plugin_work = $this->plugins->run('get_channel_details', array($this, $channel_code, type_cast( $site_code, 'textcode' )));
        	   if (in_array(true, $plugin_work)) {
        	   	return;
        	   }        	   
        	
            $this->load->model( 'channel' );
            $this->load->model( 'site' );

            $id_site = type_cast( $site_code, 'textcode' );

            $id_channel = type_cast( $channel_code, 'textcode' );
            $channel_info = $this->channel->get_info( $id_channel );

            $site_info = $this->site->get_info( $id_site );
            
            $this->load->model('sites_channels');
            $id_site_channel = $this->sites_channels->get_id_site_channel($id_site, $id_channel);
            if (!is_null($id_site_channel)) {
	            $slot_info = $this->sites_channels->get_slot_info($id_site_channel->id_site_channel);            
	            $buyed_flat_rate = ($slot_info['free'] == 0) ||
	               ($slot_info['type'] == 'image' && $slot_info['free'] != $slot_info['max']);
            } else {
               $buyed_flat_rate = false;
            }

            if (is_null( $channel_info )) {
                $error_flag = true;
                $error_message = __( 'Channel is not found' );
            }

            if (is_null( $site_info )) {
                $error_flag = true;
                $error_message = __( 'Site is not found' );
            }
        }

        if ($error_flag) {
            echo json_encode( array (
                    'error_flag' => true,
                    'error_message' => $error_message ) );
            return;
        }

        $result = array (
                'error_flag' => false );

        $supported_cost_models = array ();

        $this->load->model( 'channel_program' );

        $channel_program_types = $this->channel_program->get_channel_program_types( $id_channel );
        $price_list = $this->channel_program->get_list(
           array(
              'fields' => 'program_type,title,id_program,volume,cost_text,cost_image',
              'id_channel' => $id_channel,
              'order_bye' => 'volume'              
           )
        );


        foreach ( $channel_program_types as $program_type ) {
            $supported_cost_models[] = __( $program_type );
        }
       
        $allowedTypes = explode(',', $channel_info->ad_type);
        $allowedTypesLabels = array();
        $max_ad_slots = 1;
        
        if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
        	$allowedTypesLabels[] = __( 'Text' );
        	$max_ad_slots = $channel_info->max_ad_slots; 
        }
        
    	if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
        	$allowedTypesLabels[] = __( 'Image' );
        }
        
        $channel_ad_type_text = implode(', ', $allowedTypesLabels);
        
        $channel_data = array (
                'name' => $channel_info->name,
                'type' => $channel_ad_type_text,
                'format' => $channel_info->width . '&times;' . $channel_info->height,
                'slots' => $max_ad_slots,
                'cost_models' => implode( ', ', $supported_cost_models ),
                'id_dimension' =>  $channel_info->id_dimension,
                'buyed_flat_rate' => $buyed_flat_rate,
        	'prices' => array()
        );
        
        if (!is_null($price_list)) {
	        foreach ($price_list as $price) {
	           $channel_data['prices'][$price['program_type']][$price['id_program']]['id_program'] = 
	              type_to_str($price['id_program'], 'textcode');
	        	  $channel_data['prices'][$price['program_type']][$price['id_program']]['title'] = 
	        	     type_to_str($price['title'], 'encode');
	           $channel_data['prices'][$price['program_type']][$price['id_program']]['volume'] = 
	              type_to_str($price['volume'], 'integer');
	           $channel_data['prices'][$price['program_type']][$price['id_program']]['cost_text'] = 
	              type_to_str($price['cost_text'], 'money');
	           $channel_data['prices'][$price['program_type']][$price['id_program']]['cost_image'] = 
	              $price['cost_image']?type_to_str($price['cost_image'], 'money'):'-';
	        }
        }        
        
        if(in_array(__('CPC'),$supported_cost_models)){
           $channel_data['prices']['CPC']['id_program'] = 
              type_to_str(0, 'textcode');
           $text_bid = $this->site->get_min_bid($id_site, 'text'); 
           $channel_data['prices']['CPC']['cost_text'] = 
              $text_bid?type_to_str($text_bid, 'money'):'-';
           $image_bid = $this->site->get_min_bid($id_site, 'image'); 
           $channel_data['prices']['CPC']['cost_image'] = 
              $image_bid?type_to_str($image_bid, 'money'):'-';
        }

        $result = array (
                'error_flag' => false,
                'channel' => $channel_data );

        echo json_encode( $result );
    }

}

?>