<?php
require_once 'Zend/Db/Table/Row/Abstract.php';

abstract class Sppc_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract {
    /**
     * Get Object Id
     *
     * @return mixed
     */
    public function getId() {
        if (count ( $this->_primary ) > 1) {
            $id = array ();
            foreach ( $this->_primary as $field ) {
                $id [$field] = $this->{$field};
            }
            return $id;
        }
        return $this->{current ( $this->_primary )};
    }
    /**
     * Convert yes/no to boolean
     *
     * @param string $value
     * @return boolean
     */
    protected function _getBoolean($value) {
        if ($value == 'true') {
            return true;
        }
        return false;
    }
    /**
     * Convert boolean to yes/no
     *
     * @param boolean $value
     * @return string
     */
    protected function _setBoolean($value) {
        if ($value===true) {
            return 'true';
        }
        return 'false';
    }
    /**
     * Convert object to string
     *
     * @return string
     */
    public function toString() {
        return __CLASS__;
    }

    public function __toString() {
        return $this->toString ();
    }
    /**
     * Helper magic to set or retrieve fields values as method calls
     * getSomeFieldName - will retrieve value from db field some_field_name
     * getAnotherFieldName - will set value to db field another_field_name
     *
     * @param string $name
     * @param array $arguments
     * @throws Zend_Db_Table_Row_Exception
     * @return mixed
     */
    public function __call($name, $arguments) {
        $action = substr( $name, 0, 3 );
        $propName = preg_replace( '~([A-Z])~e', "'_'.strtolower('$1')", substr( $name, 3 ) );
        $propName = ltrim( $propName, '_' );

        
        if ('get' == $action) {
            return $this->{$propName};
        }
        if ('set' == $action) {
            $numArguments = count( $arguments );
            $value = null;
            if ($numArguments == 1) {
                $value = current($arguments);
            } else
                if ($numArguments > 1) {
                    throw new Sppc_Db_Table_Row_Exception( 'Too many arguments to set field value.' );
                }
            $this->{$propName} = $value;

            return $value;
        }

        throw new Sppc_Db_Table_Row_Exception('Unknown method call: ' . $name);
    }
}
