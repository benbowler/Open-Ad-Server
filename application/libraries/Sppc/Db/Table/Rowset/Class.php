<?php
/**
 * Sppc db table rowset with row class feature
 *
 * @version $Id: Class.php 7927 2009-08-25 08:35:08Z  $
 */

require_once 'Sppc/Db/Table/Rowset/Abstract.php';

class Sppc_Db_Table_Rowset_Class extends Sppc_Db_Table_Rowset_Abstract {
    /**
     * Default filed name for RowClass
     *
     * @var string
     */
    protected $_fieldRowClass = 'class';

	public function __construct(array $config) {
		parent::__construct($config);
	}
    /**
     * @see Zend_Db_Table_Rowset_Abstract::current()
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function current() {
        if ($this->valid() === false) {
            return null;
        }
        // do we already have a row object for this position?
        if (empty($this->_rows[$this->_pointer])) {
            if(empty($this->_data[$this->_pointer][$this->_fieldRowClass])) {
                $rowClass = $this->_rowClass;
            }
            else {
                $rowClass = $this->_data[$this->_pointer][$this->_fieldRowClass];
            }

            $this->_rows[$this->_pointer] = new $rowClass(
                array(
                    'table'    => $this->_table,
                    'data'     => $this->_data[$this->_pointer],
                    'stored'   => $this->_stored,
                    'readOnly' => $this->_readOnly
                )
            );
        }

        // return the row object
        return $this->_rows[$this->_pointer];
    }
}
