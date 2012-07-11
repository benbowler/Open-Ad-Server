<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_RoleModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * @var string
	 */
	protected $_name = 'roles';
	/**
	 * Row class
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Role';

	protected $_dependentTables = array(
		'Sppc_Entity_RolesModel');


	/**
	 * Find role by it's name
	 *
	 * @param string $roleName
	 * @return Sppc_Role
	 */
	public function findByName($roleName) {
	    $select = $this->select();
	    $select->where('name=?',$roleName);
	    return $this->fetchRow($select);
	}
}