<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

/**
 * Class which represent category
 * 
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_Category extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Parent category
	 * 
	 * @var Sppc_Category|null
	 */
	protected $_parent = null;
	/**
	 * Childs categories
	 * 
	 * @var array
	 */
	protected $_childs = null;

	/**
	 * Set parent category
	 * 
	 * @param Sppc_Category $parent
	 * @return void
	 */
	public function setParentCategory(Sppc_Category $parent) {
		$this->_parent = $parent;
		if (!is_null($parent)) {
			$this->id_category_parent = $parent->getId();
		} else {
			$this->id_category_parent = null;
		}
	}
	/**
	 * Get parent category
	 * 
	 * @return Sppc_Category|null
	 */
	public function getParentCategory() {
		if ((is_null($this->_parent)) && (!is_null($this->id_category_parent))) {
			$this->_parent = $this->findParentRow('Sppc_CategoryModel', 'Parent');
		}
		return $this->_parent;
	}
	/**
	 * Get child categories
	 * 
	 * @param bool $recursive
	 * @return array
	 */
	public function getChildCategories($recursive = false) {
		if (is_null($this->_childs)) {
			$this->_childs = array();
			
			$select = $this->getTable()->select()->order('name');
			$childs = $this->findDependentRowset('Sppc_CategoryModel', 'Parent', $select);
			foreach($childs as $children) {
				$this->_childs[$children->getId()] = $children;
				if ($recursive == true) {
					$categories = $children->getChildCategories(true);
					foreach($categories as $category) {
						if (!array_key_exists($category->getId(), $this->_childs)) {
							$this->_childs[$category->getId()] = $category;
						}
					}
				}
			}
		}
		
		return $this->_childs;
	}
	
	
	public function save() {
		parent::save();
		
	}
	/**
	 * Return all child categories
	 * arra_keys
	 * @return array
	 */
	public function getChildCategoriesRecursive() {
		return $this->_table->findChildRecursive($this);
	}
	
	/**
	 * Return true if category has children
	 * 
	 * @return bool
	 */
	public function hasChildren() {
		return (count($this->getChildCategories()) > 0) ? true : false;
	}
}
