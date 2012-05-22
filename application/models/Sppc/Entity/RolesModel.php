<?php
require_once 'Sppc/Db/Table/Join.php';

class Sppc_Entity_RolesModel extends Sppc_Db_Table_Join {
	/**
	 * Table name
	 * @var string
	 */
	protected $_name = 'entity_roles';
	/**
	 * Row class
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Entity_Role';
	/**
	 * Rowset class
	 * @var string
	 */
	protected $_rowsetClass = 'Sppc_Entity_Role_Rowset';
	/**
	 * Reference map
	 * @var array
	 */
	protected $_referenceMap = array(
		'Entity' => array(
			'columns' => 'id_entity',
			'refTableClass' => 'Sppc_EntityModel',
			'refColumns' => 'id_entity',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE),
		'Role' => array(
			'columns' => 'id_role',
			'refTableClass' => 'Sppc_RoleModel',
			'refColumns' => 'id_role',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE));
	
	protected $_joinTableClass = 'Sppc_RoleModel';
	
	protected $_joinTableColumn = 'class';
	
	protected $_joinColumn = 'id_role';
	
	/**
	 * Find all roles of specified entity
	 * @param Sppc_Entity $entity
	 * @return Sppc_Entity_Role_Rowset
	 */
	public function findByEntity(Sppc_Entity $entity) {
		$where = array(
			'id_entity = ?' => $entity->getId());
		return $this->fetchAll($where);
	}
	/**
	 * Find row by enity and role
	 * @param Sppc_Entity $entity
	 * @param Sppc_Role $role
	 * @return Sppc_Db_Table_Row
	 */
	public function findByEntityAndRole(Sppc_Entity $entity, Sppc_Role $role) {
		$where = array(
			'id_entity = ?' => $entity->getId(),
			'id_role = ?' => $role->getId());

		return $this->fetchRow($where);
	}
	/**
	 * Create row
	 * @param Sppc_Entity $entity
	 * @param Sppc_Role $role
	 * @return Sppc_Entity_Role_Abstract
	 */
	public function createRow(Sppc_Entity $entity, Sppc_Role $role) {
		$row = $this->findByEntityAndRole($entity, $role);
		if (is_null($row)) {
			$data = array(
				'id_entity' => $entity->getId(),
				'id_role' => $role->getId()
			);

			$row = parent::createRow($data);
		}
		return $row;
	}
	/**
	 * Create new row
	 * @param Sppc_Entity $entity
	 * @param Sppc_Role $role
	 * @return Sppc_Entity_Role_Abstract
	 */
	public function fetchNew(Sppc_Entity $entity, Sppc_Role $role) {
		return $this->createRow($entity, $role);
	}
}