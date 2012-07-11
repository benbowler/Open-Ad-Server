<?php
require_once 'Sppc/Db/Table/Rowset/Join.php';

class Sppc_Entity_Role_Rowset extends Sppc_Db_Table_Rowset_Join {
	/**
	 * Row class
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Entity_Role';
	
	/**
	 * Check if specified role exist in rowset
	 * @param mixed $role See role constants in class Sppc_Role
	 * @return bool
	 */
	public function hasRole($role) {
		foreach($this->_data as $row) {
			if ($row['id_role'] == $role) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Get specified role from rowset
	 * @param Sppc_Role $role
	 * @return Sppc_Entity_Role_Abstract
	 */
	public function getRole(Sppc_Role $role) {
		$entityRole = null;
		$position = null;
		
		foreach($this->_data as $key => $row) {
			if ($row['id_role'] == $role->getId()) {
				$position = $key;
				break;
			}	
		}
		
		if (!is_null($position)) {
			$this->seek($position);
			$entityRole = $this->current();
		}
		return $entityRole;
	}
}