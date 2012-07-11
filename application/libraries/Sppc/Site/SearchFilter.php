<?php
/**
 * Filter object which usign during sites search
 * 
 * @author Sergey Revenko
 * @version $Id: $
 */
class Sppc_Site_SearchFilter {
	/**
	 * Text ads bid type
	 * @var string
	 */
	const BID_TYPE_TEXT = 'min_cpc';
	/**
	 * Image ads bid type
	 * 
	 * @var string
	 */
	const BID_TYPE_IMAGE = 'min_cpc_image';
	
	/**
	 * Site status
	 * 
	 * @var string
	 */
	protected $_status = Sppc_Site::STATUS_ACTIVE;
	
	/**
	 * Filter for site's id property
	 * 
	 * @var array
	 */
	protected $_siteId = array();
	
	/**
	 * Site owner
	 * 
	 * @var Sppc_Entity|array
	 */
	protected $_owners = array();
	
	/**
	 * Filter for site's owner status property
	 * 
	 * @var string
	 */
	protected $_ownersStatus = Sppc_Entity::STATUS_ACTIVE;
	
	/**
	 * Is cpc allowed on the site
	 * 
	 * @var bool
	 */
	protected $_hasCpc = null;
	
	/**
	 * Categories to which site must belong
	 * 
	 * @var array
	 */
	protected $_categories = array();
	
	/**
	 * Filter parameter which determine does site stats table must be joined
	 * 
	 * @var bool
	 */
	protected $_connectStats = false;
	
	/**
	 * Filter parameter which deterine from which date stats must be connected
	 * 
	 * @var Zend_Date
	 */
	protected $_statsStartDate = null;
	
	/**
	 * Filter parameter which determine to which date stats must be connected
	 * 
	 * @var Zend_Date
	 */
	protected $_statsEndDate = null;
	
	/**
	 * Filter for maximum value of site's text ads bid property  
	 * 
	 * @var float
	 */
	protected $_maxTextBid = null;
	
	/**
	 * Filter for maximum value of site's image ads bid property
	 * 
	 * @var float
	 */
	protected $_maxImageBid = null;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct() {
		$this->_statsStartDate = new Zend_Date(array('year' => 2008, 'month' => 4, 'day' => 1));
		$this->_statsEndDate = Zend_Date::now();
	}	
	
	/**
	 * Sets filter for site status
	 *  
	 * @param string|null $status
	 * @return Sppc_Site_SearchFilter
	 * @throws Sppc_Exception
	 */
	public function setStatus($status) {
		$allowedStatuses = array(
			Sppc_Site::STATUS_ACTIVE,
			Sppc_Site::STATUS_BLOCKED,
			Sppc_Site::STATUS_DELETED,
			Sppc_Site::STATUS_DENIED,
			Sppc_Site::STATUS_PAUSED,
			Sppc_Site::STATUS_PENDING,
			Sppc_Site::STATUS_UNAPPROVED);
			
		if (!is_null($status) && (!in_array($status, $allowedStatuses))) {
			throw new Sppc_Exception('Invalid site status specified');
		}
		
		$this->_status = $status;
		
		return $this;
	}
	
	/**
	 * Return filter for site status
	 * 
	 * @return string
	 */
	public function getStatus() {
		return $this->_status;
	}
	
	/**
	 * Sets filter for site which allowed/disalloif (is_bool())wed cpc ads
	 * 
	 * @param bool|null $hasCpc
	 * @return Sppc_Site_SearchFilter
	 */
	public function setHasCpc($hasCpc) {
		if (is_bool($hasCpc)) {
			$this->_hasCpc = ($hasCpc) ? 'true' : 'false';
		} else {
			$this->_hasCpc = null;
		}
		
		return $this;
	}
	
	/**
	 * Return filter value for site property 'hasCpc'
	 * 
	 * @return string|null
	 */
	public function getHasCpc() {
		return $this->_hasCpc;
	}
	
	/**
	 * Sets filter value for site's 'Owner' property
	 * 
	 * @param Sppc_Entity|array $owners
	 * @return Sppc_Site_SearchFilter
	 */
	public function setOwners($owners) {
		if (is_array($owners)) {
			$this->_owners = array();
			foreach($owners as $owner) {
				if (($owner instanceof Sppc_Entity) && (!array_key_exists($owner->getId(), $this->_owners))) {
					$this->_owners[$owner->getId()] = $owner;
				}
			}
		} else {
			if (($owners instanceof Sppc_Entity) && (!array_key_exists($owner->getId(), $this->_owners))) {
				$this->_owners[$owners->getId()] = $owners;
			}
		}
		
		return $this;
	}
	
	/**
	 * Return filter value for site's 'Owner' propertyquuer
	 * 
	 * @return array
	 */
	public function getOwners() {
		return $this->_owners;
	}
	
	/**
	 * Set filter value for site's id property
	 * 
	 * @param array|int $id
	 * @return Sppc_Site_SearchFilter
	 */
	public function setSiteId($id) {
		if (is_array($id)) {
			$this->_siteId = $id;
		} else {
			if (!in_array($id)) {
				$this->_siteId[] = $id;
			}
		}
		return $this;
	}
	
	/**
	 * Return filter value for site's id property
	 * 
	 * @return array
	 */
	public function getSiteId() {
		return $this->_siteId;
	}
	
	/**
	 * Sets filter value for site's owners status property
	 * 
	 * @param string|null $status
	 * @return Sppc_Site_SearchFilter
	 * @throws Sppc_Exception
	 */
	public function setOwnersStatus($status) {
		$allowedStatuses = array(
			Sppc_Entity::STATUS_ACTIVATION,
			Sppc_Entity::STATUS_ACTIVE,
			Sppc_Entity::STATUS_BLOCKED,
			Sppc_Entity::STATUS_DELETED);
			
		if ((!is_null($status)) && (!in_array($status, $allowedStatuses))) {
			throw new Sppc_Exception('Invalid site owner status specified');
		}
		
		$this->_ownersStatus = $status;
		
		return $this;
	}
	
	/**
	 * Return filter value for site's owners status property
	 * 
	 * @return string|null
	 */
	public function getOwnersStatus() {
		return $this->_ownersStatus;
	}
	
	/**
	 * Sets filter value for site's categories property
	 * 
	 * @param Sppc_Category|array $categories
	 * @return Sppc_Site_SearchFilter
	 */
	public function setCategories($categories) {
		if (is_array($categories)) {
			$this->_categories = array();
			
			foreach($categories as $category) {
				if (($category instanceof Sppc_Category) && (!array_key_exists($category->getId(), $this->_categories))) {
					$this->_categories[$category->getId()] = $category;
				}
			}
		} else if (($categories instanceof Sppc_Category) && (!array_key_exists($categories->getId(), $this->_categories))) {
			$this->_categories[$categories->getId()] = $categories;
		}
		
		return $this;
	}
	
	/**
	 * Return filter value for sites categories property
	 * 
	 * @return array
	 */
	public function getCategories() {
		return $this->_categories;
	}
	
	/**
	 * Sets filter value which determine does site stats table must me joined
	 * 
	 * @param bool $connect
	 * @param Zend_Date $startDate
	 * @param Zend_Date $endDate
	 * @return Sppc_Site_SearchFilter
	 */
	public function setConnectToStats($connect, Zend_Date $startDate = null, Zend_Date $endDate = null) {
		$this->_connectStats = (bool) $connect;
		
		if (!is_null($startDate)) {
			$this->_statsStartDate = $startDate;
		}
		
		if (!is_null($endDate)) {
			$this->_statsEndDate = $endDate;
		}
		
		return $this;
	}
	
	/**
	 * Return filter value which determine does site stats table must be joined
	 * 
	 * @return bool
	 */
	public function getConnectToStats() {
		return $this->_connectStats;
	}
	
	/**
	 * Return filter value which determine from which date stats must be connected
	 * 
	 * @return Zend_Date|null
	 */
	public function getStatsStartDate() {
		return $this->_statsStartDate; 
	}
	
	/**
	 * Return filter value which determine to which date stats must be connected
	 * 
	 * @return Zend_Date|null
	 */
	public function getStatsEndDate() {
		return $this->_statsEndDate;
	}
	
	/**
	 * Sets maximum value of site's text ads bid property
	 * 
	 * @param float $bid
	 * @return Sppc_Site_SearchFilter
	 */
	public function setMaximumTextBid($bid) {
		$this->_maxTextBid = (is_null($bid)) ? null : (float) $bid;
		
		return $this;
	}
	
	/**
	 * Return maximum value of site's text ads bid property
	 * 
	 *  @return float|null
	 */
	public function getMaximumTextBid() {
		return $this->_maxTextBid;
	}
	
	/**
	 * Sets max value for site's image ads bid property
	 * 
	 * @param float|null $bid
	 * @return Sppc_Site_SearchFilter
	 */
	public function setMaximumImageBid($bid) {
		$this->_maxImageBid = (is_null($bid)) ? null : (float) $bid;
		
		return $this;
	}
	
	/**
	 * Return max value of site's image ads bid property
	 * 
	 * @return float|null
	 */
	public function getMaximumImageBid() {
		return $this->_maxImageBid;
	}
}