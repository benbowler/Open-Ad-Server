<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Orbit Ads Model
 */
class OrbitscriptsModelOrbitscriptsAds extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the hello identifier
	 *
	 * @access	public
	 * @param	int Hello identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a hello
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty($this->_data )) {
			$query = ' SELECT * FROM #__modules'.
					 ' WHERE id = '.$this->_id.' AND module = "mod_orbitscripts_ads"';
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			$this->_data->greeting = null;
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store($post_a = array())
	{	
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );
		$data = array_merge($data,$post_a);
		
		// Bind the form fields to the hello table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the hello record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError( $row->getErrorMsg() );
			return false;
		}
		
		if (empty($data['id'])) $data['id']=$this->_db->insertid();
		
		return $data['id'];
	}

	/**
	 * Method to delete record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids )) {
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * Method to set a orbitscriptsads params
	 * @return object with data
	 */
	function setParam($name, $value)
	{
		$query = ' UPDATE #__orbitscripts_ads_params'.
				 ' SET value=\''.$value.
			     '\' WHERE name = \''.$name.'\'';
		$this->_db->setQuery( $query );
		
		return $this->_db->query();
	}
	/**
	 * Method to get a orbitscriptsads params
	 * @return object with data
	 */
	function getParam($name)
	{
		$query = ' SELECT value FROM #__orbitscripts_ads_params'.
			     ' WHERE name = \''.$name.'\'';
		$this->_db->setQuery( $query );
		
		return $this->_db->loadResult();
	}
}