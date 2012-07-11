<?php
/**
 * Sppc db table abstract
 *
 * @version $Id: Abstract.php 7927 2009-08-25 08:35:08Z  $
 */
require_once 'Zend/Db/Table/Abstract.php';

class Sppc_Db_Table_Abstract extends Zend_Db_Table_Abstract {
    /**
     * Default row class
     *
     * @var string
     */
    protected $_rowClass = 'Sppc_Db_Table_Row';
    /**
     * Default rowset class
     *
     * @var string
     */
    protected $_rowsetClass = 'Sppc_Db_Table_Rowset';

    /**
     * @param mixed $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findObjectById($id) {
        $rowSet = $this->find($id);
        if($rowSet->count()>0) {
            return $rowSet->current();
        }
        return null;
    }
}
