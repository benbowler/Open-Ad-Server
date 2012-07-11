<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_EntityModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'entities';


    protected $_guest = null;
    
    protected $_admin = null;

    /**
     * Row class
     * @var string
     */
    protected $_rowClass = 'Sppc_Entity';
    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array (
            'Sppc_Entity_RolesModel',
            'Sppc_SurferModel',
            'Sppc_Stat_Search_TypeModel',
    		'Sppc_Member_TabModel'
     );

    /**
     * Get guest entity
     *
     * @return Sppc_Entity
     * @throws Sppc_Exception
     */
    public function findGuest() {
        if (! is_null( $this->_guest )) {
            return $this->_guest;
        }
        $rolesModel = new Sppc_RoleModel( );
        $role = $rolesModel->findByName( 'guest' );
        /*@var $role Sppc_Role*/
        if (is_null( $role )) {
            throw new Sppc_Exception( 'Role guest doesnt exist' );
        }

        $rowset = $role->findManyToManyRowset( 'Sppc_EntityModel', 'Sppc_Entity_RolesModel' );
        if ($rowset->count() < 1) {
            throw new Sppc_Exception( 'entity guest doesnt exist' );
        }
        $this->_guest = $rowset->current();
        return $this->_guest;
    }
    /**
     * Get admin entity
     * 
     * @return Sppc_Entity
     * @throws Sppc_Exception
     */
    public function findAdmin() {
    	if (is_null($this->_admin)) {
    		$rolesModel = new Sppc_RoleModel( );
        	$role = $rolesModel->findByName('admin');
	        /*@var $role Sppc_Role*/
	        if (is_null( $role )) {
	            throw new Sppc_Exception( 'Role admin doesnt exist' );
	        }
	
	        $rowset = $role->findManyToManyRowset( 'Sppc_EntityModel', 'Sppc_Entity_RolesModel' );
	        if ($rowset->count() < 1) {
	            throw new Sppc_Exception( 'entity admin doesnt exist' );
	        }
	        $this->_admin = $rowset->current();
	    }
	    return $this->_admin;
    }
    /**
     * Add custom logic on row creation for this table
     * @param array $data
     * @return mixed
     */
    public function insert(array $data) {
        $data['creation_date'] = Zend_Date::now()->toString( 'yyyy-MM-dd' );
        return parent::insert( $data );
    }
}
