<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

/**
 * Class which reprsent XML feed
 * 
 * @author Sergey Revenko
 * @version $Id: $
 */
class Sppc_Feed extends Sppc_Db_Table_Row_Abstract {
	const STATUS_ACTIVE = 'active';
	const STATUS_PAUSED = 'paused';
}