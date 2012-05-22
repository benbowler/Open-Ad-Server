<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Modules Table class
 */
class TableOrbitscriptsAds extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 */
	var $params = null;
	
	var $module = null;
	
	var $title = null;
	
	var $position = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableOrbitscriptsAds(& $db) {
		parent::__construct('#__modules', 'id', $db);
	}
}