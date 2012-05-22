<?php

/**
 * Model for working with continents data
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_ContinentModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'continents';
	
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Continent';
	
	/**
	 * Dependent tables
	 * 
	 * @var array
	 */
	protected $_dependentTables = array(
		'Sppc_CountryModel'
	);
}