<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

class Sppc_Role extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Roles constants
	 * @var int
	 */
	const ADMIN = 1;
	const GUEST = 2;
	const ADVERTISER = 3;
	const PUBLISHER = 4;
	const AFFILIATE = 5;
	const PARTNER = 6;
	const SURFER = 7;
	const GUEST_ADMIN = 8;
	const PAYMENT_GATEWAY = 9;
	const MEMBER = 10;
	
	const STATUS_ACTIVE = 'active';
    const STATUS_ACTIVATION = 'activation';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_DELETED = 'deleted';
}