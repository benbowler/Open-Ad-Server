<?php
require_once 'Sppc/Db/Table/Abstract.php';

/**
 * Model for working with categories
 * 
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_CategoryModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'categories';
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Category';
	/**
	 * Reference map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
		'Parent' => array(
			'columns'		=> 'id_category_parent',
			'refTableClass'	=> 'Sppc_CategoryModel',
			'refColumns'	=> 'id_category',
			'onUpdate'		=> self::CASCADE,
			'onDelete'		=> self::CASCADE
		)
	);
	/**
	 * Dependent tables
	 * 
	 * @var array
	 */
	protected $_dependentTables = array (
		'Sppc_CategoryModel',
		'Sppc_Site_CategoryModel',
	   'Sppc_Channel_CategoryModel'
	);
	/**
	 * Find recursively all child categories of specified category 
	 * 
	 * @param Sppc_Category $category
	 * @return array
	 */
	public function findChildRecursive(Sppc_Category $category) {
		$categories = array();
		
		$where = array(
			'id_category_parent = ?' => $category->getId()
		);
		$childs  = $this->fetchAll($where);
		foreach($childs as $children) {
			$childCategories = $this->findChildRecursive($children);
			
			$categories[] = $children;
			$categories += $childCategories;
		}
		
		return $categories;
	}
}