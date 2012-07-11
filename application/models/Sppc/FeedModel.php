<?php
require_once 'Sppc/Db/Table/Abstract.php';

/**
 * Model for working with xml feeds
 * 
 * @author Sergey Revenko
 * @version $Id: $
 */
class Sppc_FeedModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table
	 * 
	 * @var string
	 */
	protected $_name = 'feeds';
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Feed';
	/**
	 * Find all feeds with specified status
	 * 
	 * @param string $status
	 * @return Sppc_Db_Table_Rowset
	 */
	public function findAllByStatus($status = Sppc_Feed::STATUS_ACTIVE) {
		$where = array(
			'status = ?' => $status);
		
		return $this->fetchAll($where);
	}
}