<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Orbit Ads Central Controller
 */
class OrbitscriptsControllerOrbitscriptsAds extends OrbitscriptsController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
		$this->registerTask( 'unpublish'  ,'publish' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function add()
	{
		JRequest::setVar( 'view', 'orbitscriptsads' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * edit module params
	 * @return void
	 */
	function edit()
	{
		$post = JRequest::get('post');
		$link = 'index.php?option=com_modules&client=0&task=edit&cid[]='.$post['cid'][0];
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('orbitscriptsads');
		$post = JRequest::get( 'post' );
        $post['module'] = 'mod_orbitscripts_ads';
        $post['params'] = "moduleclass_sfx= \nmodule_css_style= \nkeys=";
        $params = unserialize($model->getParam('channels'));
        $iduser = $params['userid'];
        unset($params['userid']);
        foreach ($params as $idsite=>$channels) {
        	foreach ($channels['channels'] as $id=>$channel) {
				if ($id == $post['channel']) {
					$keys = array (
        				'idsite'=>$idsite,
            			'idchannel'=>$id,
            			'idpalette'=>$post['palette'],
            			'iduser'=>$iduser,
            			'width'=>$channel['width'],
            			'height'=>$channel['height'],
            			'dimension'=>$channel['dimension'],
						'siteurl' => $channel['siteurl']
       				 );
       				 $post['title'] = $channel['name'];
				}
        	}
        }
        $post['params'] .= str_replace('"',"'",serialize($keys));
        $post['position'] = 'left';

        $id = $model->store($post);
		if ($id !== false) {
			$msg = JText::_( 'Channel Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Greeting' );
		}
        $data=JRequest::get('post');
		// Redirect to module settings
		
		$link = 'index.php?option=com_modules&client=0&task=edit&cid[]='.$id;
		
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('orbitscriptsads');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Channels Could not be Deleted' );
		} else {
			$msg = JText::_( 'Channel(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_orbitscriptsads', $msg );
	}
	
	function publish()
	{
		$this->setRedirect( 'index.php?option=com_orbitscriptsads' );

		// Initialize variables
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$id		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$task		= JRequest::getCmd( 'task' );
		$publish	= ($task == 'publish');

		if (empty( $id )) {
			return JError::raiseWarning( 500, JText::_( 'No items selected' ) );
		}

		JArrayHelper::toInteger( $id );
		$ids = implode( ',', $id );

		$query = 'UPDATE #__modules'
		. ' SET published = ' . (int) $publish
		. ' WHERE id IN ( '. $ids.'  )'
		. ' AND (checked_out = 0 OR (checked_out = ' .(int) $user->get('id'). ' ) )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}
		$this->setMessage( JText::sprintf( $publish ? 'Items published' : 'Items unpublished', $n ) );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_orbitscriptsads', $msg );
	}

}