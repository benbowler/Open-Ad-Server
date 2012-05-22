<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

class Sppc_Entity extends Sppc_Db_Table_Row {
    const STATUS_ACTIVE = 'active';
    const STATUS_ACTIVATION = 'activation';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_DELETED = 'deleted';

    protected $_member = null;
    /**
     * Entity roles
     * @var Sppc_Entity_Role_Rowset
     */
    protected $_roles = null;
    /**
     * Get all entity roles
     * @return Sppc_Db_Table_Rowset
     */
    public function getRoles() {
        if (is_null( $this->_roles )) {
            $entityRolesModel = new Sppc_Entity_RolesModel( );
            $this->_roles = $entityRolesModel->findByEntity( $this );
        }
        return $this->_roles;
    }
    /**
     * Check is entity has specified role
     * @param Sppc_Role $role See role constants in class Sppc_Role
     * @return bool
     */
    public function hasRole($role) {
       	return $this->getRoles()->hasRole( $role );
    }
    /**
     * Add specified role to the entity
     * @param Sppc_Role $role
     * @return bool
     */
    public function addRole(Sppc_Role $role) {
        try {
            if (! $this->hasRole( $role )) {
                $entityRolesModel = new Sppc_Entity_RolesModel( );
                $newEntityRole = $entityRolesModel->createRow( $this, $role );
                $newEntityRole->save();

                $this->_roles = null;
            }
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }
    /**
     * Remove specified role from entity roles
     * @param Sppc_Role $role
     * @return bool
     */
    public function removeRole(Sppc_Role $role) {
        try {
            $roleForRemove = $this->getRoles()->getRole( $role );
            if (! is_null( $roleForRemove )) {
                $roleForRemove->delete();

                $this->_roles = null;
            }
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }
    /**
     * Get member if exists
     *
     * @return Sppc_Member
     */
    public function getMember() {
        if (is_null( $this->_member )) {
            $memberModel = new Sppc_MemberModel( );
            $rowset = $memberModel->find( $this->getId() );
            if (count( $rowset )) {
                $this->_member = $rowset->current();
            } else {
                $this->_member = false;
            }
        }
        if ($this->_member === false) {
            return null;
        }
        return $this->_member;
    }
    /**
     * Get creation date
     * @return Zend_Date
     */
    public function getCreationDate() {
        return new Zend_Date( $this->creation_date, Zend_Date::ISO_8601 );
    }
    /**
     * Set creation date
     * @param Zend_Date $date
     * @return void
     */
    public function setCreationDate(Zend_Date $date) {
        $this->creation_date = $date->toString( 'yyyy-MM-dd HH:mm' );
    }
}
