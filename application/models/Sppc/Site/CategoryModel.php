<?php

/**
 * Model for working with site categories
 * 
 * @author Sergey Revenko
 * @version $Id: $
 */
class Sppc_Site_CategoryModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'site_categories';
	
	/**
	 * Reference map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
		'Site' => array(
			'columns' => 'id_site',
			'refTableClass' => 'Sppc_SiteModel',
			'refColumns' => 'id_site',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE),
		'Category' => array(
			'columns' => 'id_category',
			'refTableClass' => 'Sppc_CategoryModel',
			'refColumns' => 'id_category',
			'onUpdate' => self::CASCADE,
			'onDelete' => self::CASCADE));
	
	/**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array ();
}