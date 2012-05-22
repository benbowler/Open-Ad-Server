<?php
/**
 * Sppc db table with row class feature
 *
 * @version $Id: Class.php 7927 2009-08-25 08:35:08Z  $
 */
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_Db_Table_Class extends Sppc_Db_Table_Abstract {
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
    protected $_rowsetClass = 'Sppc_Db_Table_Rowset_Class';
    /**
     * Default filed name for RowClass
     *
     * @var string
     */
    protected $_fieldRowClass = 'class';

    /**
     * @see Zend_Db_Table_Abstract::fetchRow()
     *
     * @param string|array|Zend_Db_Table_Select $where
     * @param string|array $order
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function fetchRow($where=null, $order=null) {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1);

        } else {
            $select = $where->limit(1);
        }

        $rows = $this->_fetch($select);

        if (count($rows) == 0) {
            return null;
        }

        $data = array(
            'table'   => $this,
            'data'     => $rows[0],
            'readOnly' => $select->isReadOnly(),
            'stored'  => true
        );

        if(empty($rows[0][$this->_fieldRowClass])) {
            $rowClass = $this->_rowClass;
        }
        else {
            $rowClass = $rows[0][$this->_fieldRowClass];
        }

        if (!class_exists($rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowClass);
        }
        return new $rowClass($data);
    }
}
