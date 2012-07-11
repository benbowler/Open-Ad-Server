<?php

/**
 * Model for working with sites stats data
 * 
 * @author Sergey Revenko
 * @version $Id: $
 */
class Sppc_Site_StatModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'stat_sites';
	
	/**
	 * Reference Map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
		'Site' => array(
			'columns' => 'id_site',
			'refTableClass' => 'Sppc_SiteModel',
			'refColumns' => 'id_site',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE));
		 
}