<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Orbit View
 */
class OrbitscriptsViewOrbitscriptsAds extends JView
{
	/**
	 * display method of Orbitads view
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
		$orbitscriptsapi	= new OrbitscriptsControllerOrbitscriptsApi();

		//get data
		$orbitscriptsads		=& $this->get('Data');
		$isNew		= ($orbitscriptsads->id < 1);

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(JText::_( 'Manage Ads' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save('save','Save');
		JToolBarHelper::cancel();
		
		$this->assignRef('orbitscriptsads', $orbitscriptsads);
		$this->assignRef('orbitscriptsapi', $orbitscriptsapi);

		parent::display($tpl);
	}
}