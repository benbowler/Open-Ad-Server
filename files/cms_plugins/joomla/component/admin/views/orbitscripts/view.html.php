<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Chanells View
 */
class OrbitscriptsViewOrbitscripts extends JView
{
	/**
	 * Channels view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		
		// Require specific controller if requested
		$path = JPATH_COMPONENT.DS.'controllers'.DS.'orbitscriptsapi.php';
			if (file_exists($path)) {
				require_once $path;
		}
		// Create the controller
		$orbitscriptsapi = new OrbitscriptsControllerOrbitscriptsApi();
		
		JToolBarHelper::title(JText::_( '<%SITE_NAME%> Ads Manager' ), 'orbitscriptsads64.png' );

	if (file_exists(JPATH_ROOT.'/modules/mod_orbitscripts_ads/mod_orbitscripts_ads.php')) {
		$msg=$orbitscriptsapi->testconnect();
		if ($msg['code']==0) {
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::addNewX('add','Create New Channel');
			JToolBarHelper::editListX('edit','Edit Related Module');
			JToolBarHelper::deleteList();
		} else {
			JError::raiseWarning( 100, 'Warning: API return error. Code: <b>'.$msg['code'].' - '.$msg['msg'].'</b>. You cannot add new channels. Please check "Preferences".' );
		}
	} else {
		JError::raiseWarning( 100, 'Warning: mod_orbitscripts_ads not found. You cannot add new channels. Please install this module.' );
	}
		JToolBarHelper::Preferences('com_orbitscriptsads','300','570');
		// Get data from the model
		$items	= & $this->get('Data');

		$this->assignRef('items',$items);

		parent::display($tpl);
	}
}