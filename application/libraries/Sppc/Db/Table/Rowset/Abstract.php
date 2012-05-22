<?php
/**
 * Sppc db table rowset abstract
 *
 * @version $Id$
 */

require_once 'Zend/Db/Table/Rowset/Abstract.php';

abstract class Sppc_Db_Table_Rowset_Abstract extends Zend_Db_Table_Rowset_Abstract {

	public function __construct(array $config) {
		parent::__construct($config);
	}
}
