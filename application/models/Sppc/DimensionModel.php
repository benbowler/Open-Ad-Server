<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_DimensionModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'dimensions';
    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array (
            'Sppc_ChannelModel' );
    
    /**
     * Row class
     * 
     * @var string
     */
    protected $_rowClass = 'Sppc_Dimension';
    
    /**
     * Get product zone dimensions
     * 
     */
    public function getProductZoneDimensions() {
       $select = $this->select(self::SELECT_WITH_FROM_PART)
			->setIntegrityCheck(false)
	 		->where('product_zone > ?', 0)
	 		->order(new Zend_Db_Expr('orientation ASC, name ASC'));
	 	return $this->fetchAll($select);
    } //end getProductZoneDimensions()
    
} //end class Sppc_DimensionModel
