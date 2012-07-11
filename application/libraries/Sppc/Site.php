<?php

/**
 * Object which represent single site
 * 
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_Site extends Sppc_Db_Table_Row_Abstract {
	// Aviable site statuses
	const STATUS_UNAPPROVED = 'unapproved';
	const STATUS_PENDING = 'pending';
	const STATUS_DENIED = 'denied';
	const STATUS_ACTIVE = 'active';
	const STATUS_BLOCKED = 'blocked';
	const STATUS_DELETED = 'deleted';
	const STATUS_PAUSED = 'paused';
	
	/**
	 * Site categories
	 * 
	 * @var array
	 */
	protected $_categories = null;
	
	/**
	 * Site channels
	 * 
	 * @var array
	 */
	protected $_channels = null;
	
	/**
	 * Sets creation date
	 * 
	 * @param Zend_Date $date
	 * @return void
	 */
	public function setCreationDate(Zend_Date $date) {
		$this->creation_date = $date->toString(Zend_Date::ISO_8601);
	}
	
	/**
	 * Return creation date
	 * 
	 * @return Zend_Date
	 */
	public function getCreationDate() {
		return new Zend_Date($this->creation_date, Zend_Date::ISO_8601);
	}
	/**
	 * Return site categories
	 * 
	 * @return array
	 */
	public function getCategories() {
		if (is_null($this->_categories)) {
			$this->_categories = array();
			$categories = $this->findManyToManyRowset('Sppc_CategoryModel', 'Sppc_Site_CategoryModel');

			foreach($categories as $category) {
				$this->_categories[$category->getId()] = $category;
			}
		}
		
		return $this->_categories;
	}
   /**
	 * Return site category
	 * 
	 * @return array
	 */
	public function getCategory() {
		if (is_null($this->_categories)) {
			$this->_categories = array();
			$categories = $this->findManyToManyRowset('Sppc_CategoryModel', 'Sppc_Site_CategoryModel');
			foreach($categories as $category) {
				return $category->getId();
			}
		}
      return key($this->_categories);
	}
	
	/**
	 * Sets site categories
	 * 
	 * @param Iterator $categories
	 * @return void
	 */
	public function setCategories(Iterator $categories) {
		$this->_categories = array();
		foreach ($categories as $category) {
			$this->_categories[$category->getId()] = $category;
		}
		$this->_modifiedFields['categories'] = true;
	}
	
	/**
	 * Add specified category to site categories
	 * 
	 * @param Sppc_Category $category
	 * @return void
	 */
	public function addCategory(Sppc_Category $category) {
		$categories = $this->getCategories();
		if (!array_key_exists($category->getId(), $categories)) {
			$this->_categories[$category->getId()] = $category;
			$this->_modifiedFields['categories'] = true;
		}
	}
	
	/**
	 * Remove specified category from site categories
	 *  
	 * @param Sppc_Category $category
	 * @return void
	 */
	public function removeCategory(Sppc_Category $category) {
		$categories = $this->getCategories();
		if (array_key_exists($category->getId(), $categories)) {
			unset($this->_categories[$category->getId()]);
			$this->_modifiedFields['categories'] = true;
		}
	}
	
	/**
	 * Check if specified category exists in site categories
	 * 
	 * @param Sppc_Category $category
	 * @return bool
	 */
	public function hasCategory(Sppc_Category $category) {
		$categories = $this->getCategories();
		
		return (array_key_exists($category->getId(), $categories)) ? true : false;
	}
	
	/**
	 * Return site channels
	 * 
	 * @param string $status If not equal "all" then only channels with specified status will be returned
	 * @return array
	 */
	public function getChannels($status = 'all') {
		if ($status == 'all') {
			if (is_null($this->_channels)) {
				$this->_channels = array();
				
				$channels = $this->findManyToManyRowset('Sppc_ChannelModel', 'Sppc_Site_ChannelModel');
				foreach($channels as $channel) {
					$this->_channels[$channel->getId()] = $channel;
				}
			}
			
			return $this->_channels;
		} else {
			$select = $this->getTable()->select()->where('site_channels.status = ?', $status);
			$rows = $this->findManyToManyRowset('Sppc_ChannelModel', 'Sppc_Site_ChannelModel', null, null, $select);
			$channels = array();
			foreach($rows as $row) {
				$channels[$row->getId()] = $row;
			}
			
			return $channels;
		}
	}
	
	/**
	 * Add channel to site
	 * 
	 * @param Sppc_Db_Table_Row $channel
	 * @return void
	 */
	public function addChannel(Sppc_Db_Table_Row $channel) {
		$channels = $this->getChannels();
		if (!array_key_exists($channel->getId(), $channels)) {
			$this->_channels[$channel->getId()] = $channel;
			$this->_modifiedFields['channels'] = true;
		}
	}
	
	/**
	 * Remove channel from site
	 * 
	 * @param Sppc_Db_Table_Row $channel
	 * @return void
	 */
	public function removeChannel(Sppc_Db_Table_Row $channel) {
		$channels = $this->getChannels();
		if (array_key_exists($channel->getId(), $channels)) {
			unset($this->_channels[$channel->getId()]);
			$this->_modifiedFields['channels'] = true;
		}
	}
	
	/**
	 * Insert/update record in DB
	 * 
	 * @return mixed
	 */
	public function save() {
		$needUpdateCategories = false;
		$needUpdateChannels = false;
		if ((array_key_exists('categories', $this->_modifiedFields)) &&  ($this->_modifiedFields['categories'] == true)) {
			$needUpdateCategories = true;
		}
		if ((array_key_exists('channels', $this->_modifiedFields)) && ($this->_modifiedFields['channels'] == true)) {
			$needUpdateChannels = true;
		}
		
		$result = parent::save();
		
		if ($needUpdateCategories) {
			$siteCategoryModel = new Sppc_Site_CategoryModel();
			
			$siteCategories = $this->findDependentRowset('Sppc_Site_CategoryModel', 'Site');
			$categories = $this->getCategories();
			
			foreach ($siteCategories as $siteCategory) {
				if (!array_key_exists($siteCategory->getIdCategory(), $categories)) {
					$siteCategory->delete();
				}
			}
			
			foreach($categories as $category) {
				$needToCreate = true;
				foreach($siteCategories as $siteCategory) {
					if ($siteCategory->getIdCategory() == $category->getId()) {
						$needToCreate = false;
						break;
					}
				}
				
				if ($needToCreate) {
					$data = array('id_site' => $this->getId(), 'id_category' => $category->getId());
					$row = $siteCategoryModel->createRow($data);
					$row->save();
				}
			}
		}
		
		if ($needUpdateChannels) {
			$siteChannelModel = new Sppc_Site_ChannelModel();
			
			$siteChannels = $this->findDependentRowset('Sppc_Site_Channel', 'Site');
			$channels = $this->getChannels();
			
			foreach ($siteChannels as $siteChannel) {
				if (!array_key_exists($siteChannel->getIdChannel(), $channels)) {
					$siteChannel->setStatus('deleted');
					$siteChannel->save();
				}
			}
			
			foreach ($channels as $channel) {
				$needToCreate = true;
				
				foreach ($siteChannels as $siteChannel) {
					if ($siteChannel->getIdChannel() == $channel->getId()) {
						$needToCreate = false;
						break;
					}
				}
				
				if ($needToCreate) {
					$data = array('id_site' => $this->getId(), 'id_channel' => $channel->getId());
					$row = $siteChannelModel->createRow($data);
					$row->save();
				}
			}
		}
		
		return $result;
	}
}