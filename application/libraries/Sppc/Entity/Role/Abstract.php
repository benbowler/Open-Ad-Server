<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

class Sppc_Entity_Role_Abstract extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Entity
	 * @var Sppc_Entity
	 */
	protected $_entity = null;
	/**
	 * Role
	 * @var Sppc_Role
	 */
	protected $_role = null;
	/**
	 * Get entity
	 * @return Sppc_Entity
	 */
	public function getEntity() {
		if (is_null($this->_entity)) {
			$this->_entity = $this->findParentRow('Sppc_EntityModel', 'Entity');
		}
		return $this->_entity;
	}
	/**
	 * Get role
	 * @return Sppc_Role
	 */
	public function getRole() {
		if (is_null($this->_role)) {
			$this->_role = $this->findParentRow('Sppc_RoleModel', 'Role');
		}
	}
}