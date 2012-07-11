<?php
/**
 * Model for working with contries data
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_CountryModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'countries';
	
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Country';

	/**
	 * Reference Map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
		'Continent' => array(
			'columns' => 'id_continent',
			'refTableClass' => 'Sppc_ContinentModel',
			'refColumns' => 'id_continent',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE
		)
	);
	
	/**
	 * Return total countries count
	 * 
	 * @return int
	 */
	public function getCount() {
		$select = $this->getAdapter()->select()
			->from($this->_name, array(new Zend_Db_Expr('COUNT(*)')));
			
		return (int) $this->getAdapter()->fetchOne($select);
	}
}