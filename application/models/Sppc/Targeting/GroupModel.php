<?php

/**
 * Model for working with targeting groups data
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_Targeting_GroupModel extends Sppc_Db_Table_Abstract {

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'targeting_groups';
	
	/**
	 * Return all targeting groups of specified entity and role
	 * 
	 * @param Sppc_Entity $entity
	 * @param Sppc_Role $role
	 * @param string $status
	 * @param string $order
	 * @return Sppc_Db_Table_Rowset_Abstract
	 */
	public function findAllByEntityRoleAndStatus(Sppc_Entity $entity, Sppc_Role $role, $status = 'active', $order = 'title ASC') {
		$select = $this->select(self::SELECT_WITH_FROM_PART)
		   ->where('id_entity = ?', $entity->getId())
		   ->where('id_role = ?', $role->getId())
		   ->where('status = ?', $status);
		
	   if (!is_null($order)) {
	   	$select->order($order);
	   }
	   
	   return $this->fetchAll($select);
	}
}