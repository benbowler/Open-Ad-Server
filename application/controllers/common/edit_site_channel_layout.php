<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Set position of channels on the site
 *
 * @version $Id$
 */
class Common_Edit_Site_Channel_Layout extends Parent_controller {

	protected $menu_item = "Manage Sites/Channels";
	protected $site_id = null;

	public function __construct() {
		parent::__construct ();

		$this->_add_ajax ();
		
		//$this->_add_java_script ( 'stuff' );
		//$this->_add_java_script ( 'jquery-ui-1.7.2.custom.min' );
		//$this->_add_java_script ( 'gridwizard' );
		//$this->_add_css ( 'gridwizard' );
		$this->load->library ( "form" );
		$this->load->library('Plugins',array(
            'path' => array('common', 'edit_site_channel_layout'),
            'interface' => 'Sppc_Common_SiteManage_Interface'));
	}

	public function index($idSite = null) {
		$current_site_info = null;

		$form = array (
			"id" => $idSite,
			"name" => "edit_site_channel_layout_form",
			"view" => $this->role . '/adplacing/manage_sites_channels/layout/body.html',
			"redirect" => $this->role . '/manage_sites_channels',
			"vars" => array (
				'ROLE' => $this->role ),
			"fields" => array (
				'layout_json' => array (
					'id_field_type' => 'string',
					'form_field_type' => 'text' ) ) );

		if ($this->input->post ( 'redirect_after_save' )) {
			$form ['redirect'] = $this->input->post ( 'redirect_after_save' );
		}

		try {
			$siteModel = new Sppc_SiteModel ( );
			$site = $siteModel->findObjectById ( $idSite );
			if (is_null ( $site )) {
				throw new Exception ( 'Site was not found' );
			}
			$form ['vars'] ['CURRENT_SITE_NAME'] = '&bdquo;' . type_to_str ( $site->getName (), 'encode' ) . ' (' . $site->getUrl () . ')&ldquo;';
		} catch ( Exception $e ) {
			$viewData ['CURRENT_SITE_NAME'] = 'Undefined';
			$this->_set_notification ( "The site with id {$idSite} was not found!", 'error' );
			$content = $this->parser->parse ( 'common/manage_sites_channels/layout/error.html', $viewData, true );
			$this->_set_content ( $content );
			$this->_display ();
			return;
		}

		try {
			$siteLayoutModel = new Sppc_Site_LayoutModel ( );
			$siteLayout = $siteLayoutModel->findBySite ( $site );
			if (is_null ( $siteLayout )) {
				$siteLayout = $siteLayoutModel->createRow ();
				$siteLayout->setIdSite ( $site->getId () );
				$siteLayout->save ();
				throw new Exception ( 'new layout' );
			}

			$siteLayoutChannels = $siteLayout->findDependentRowset ( 'Sppc_Site_Layout_ChannelModel' );
		
			$layoutChannelIds = array ();
			foreach ( $siteLayoutChannels as $layoutChannel ) {
				$layoutChannelIds [] = $layoutChannel->getIdChannel ();
			}

			// $viewChannelsData = array ();
			$jsonChannelsData = array ();
			$select = $site->select();
            $select->where('i.status=?','active');
			$channels = $site->findManyToManyRowset ( 'Sppc_ChannelModel', 'Sppc_Site_ChannelModel', null, null, $select );
			foreach ( $channels as $channel ) {
				/*@var $channel Sppc_Db_Table_Row*/
				if (in_array ( $channel->getId (), $layoutChannelIds )) {
					continue;
				}
				$dimension = $channel->findParentRow ( 'Sppc_DimensionModel' );
				//                $viewChannelsData[] = array (
				//                        'CHANNEL_ID' => $channel->getId(),
				//                        'CHANNEL_NAME' => $channel->getName(),
				//                        'CHANNEL_WIDTH' => $dimension->getWidth(),
				//                        'CHANNEL_HEIGHT' => $dimension->getHeight() );
				$jsonChannelsData [] = array (
				   'id' => $channel->getId (),
				   'title' => $channel->getName (),
				   'width' => $dimension->getWidth (),
				   'height' => $dimension->getHeight (),
				   'id_dimension' =>  $channel->getIdDimension(),
				   'ad_type' => $channel->getAdType(),
				   'max_slots_count' => $dimension->getMaxAdSlots());
			}

			$jsonLayout = $siteLayout->toString();
		} catch ( Exception $e ) {
			$this->_add_java_script_inline ( "var siteLayoutJson='';" );
			//            $viewChannelsData = array ();
			$jsonChannelsData = array ();
			$select = $site->select();
			$select->where('i.status=?','active');
			$channels = $site->findManyToManyRowset ( 'Sppc_ChannelModel', 'Sppc_Site_ChannelModel',null,null,$select );
			foreach ( $channels as $channel ) {
				/*@var $channel Sppc_Db_Table_Row*/
				$dimension = $channel->findParentRow ( 'Sppc_DimensionModel' );
				//                $viewChannelsData[] = array (
				//                        'CHANNEL_ID' => $channel->getId(),
				//                        'CHANNEL_NAME' => $channel->getName(),
				//                        'CHANNEL_WIDTH' => $dimension->getWidth(),
				//                        'CHANNEL_HEIGHT' => $dimension->getHeight() );
				$jsonChannelsData [] = array (
					'id' => $channel->getId (),
					'title' => $channel->getName (),
					'width' => $dimension->getWidth (),
					'height' => $dimension->getHeight (),
               'id_dimension' =>  $channel->getIdDimension(),
               'ad_type' => $channel->getAdType(),
               'max_slots_count' => $dimension->getMaxAdSlots() );
			}
			$jsonLayout = '';
		}
		$this->_add_java_script_inline ( "var siteLayoutJson='{$jsonLayout}';" );
		$this->_add_java_script_inline ( "var siteChannelsJson='" . json_encode ( $jsonChannelsData ) . "';" );
		//        $form['vars']['SITE_CHANNELS'] = $viewChannelsData;
		$this->_set_content ( $this->form->get_form_content ( 'modify', $form, $this->input, $this ) );
		$this->_display ();
	} //end index


	public function _load($idSite) {
		$siteModel = new Sppc_SiteModel ( );
		$site = $siteModel->findObjectById ( $idSite );
		$siteLayoutModel = new Sppc_Site_LayoutModel ( );
		$siteLayout = $siteLayoutModel->findBySite ( $site );
		return array (
			'layout_json' => $siteLayout->toString () );
	}

	public function _save($idSite, $fields) {
		try {
			if (isset ( $fields ['layout_json'] )) {
				$siteModel = new Sppc_SiteModel ( );
				$site = $siteModel->findObjectById ( $idSite );
				$siteLayoutModel = new Sppc_Site_LayoutModel ( );
				$siteLayoutModel->updateFromJson ( $site, $fields ['layout_json'] );
			}
		} catch ( Exception $e ) {
			$this->_set_notification ( 'There is an error while saving your site data:' . $e->getMessage (), 'error' );
			return $e->getMessage ();
		}
		$this->_set_notification ( 'Site layout was saved', 'notification', true );
		return '';
	}
}
