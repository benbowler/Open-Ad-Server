<?php
require_once 'Sppc/Db/Table/Abstract.php';

/**
 * Model for working with sites data
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_SiteModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'sites';
    
    /**
     * Row class
     * 
     * @var string
     */
    protected $_rowClass = 'Sppc_Site';
    
    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array (
      'Sppc_Site_LayoutModel',
      'Sppc_Site_ChannelModel',
      'Sppc_Site_CategoryModel',
      'Sppc_Site_StatModel' 
    );
    
    /**
     * Find site by url
     * 
     * @param string $url
     * @return Sppc_Site
     */
    public function findByUrl($url) {
    	$where = array(
    		'url = ?' => $url,
    		'status <> ?' => 'deleted'
    	);
    	
    	return $this->fetchRow($where);
    }
    
    /**
     * Return sites count according to specified filter
     * 
     * @param Sppc_Site_SearchFilter $filter
     * @return int
     */
    public function getCount(Sppc_Site_SearchFilter $filter) {
    	$select = $this->getAdapter()->select()
    		->from($this->_name, 'COUNT(*)')
    		->joinLeft('entity_roles', 'entity_roles.id_entity = '.$this->_name.'.id_entity_publisher AND entity_roles.id_role = 4', array()); /* publisher */
    		
    	//site id
    	if (count($filter->getSiteId()) > 1) {
    		$select->where($this->_name.'.id_site IN (?)', $filter->getSiteId());
    	} else if (count($filter->getSiteId()) == 1) {
    		$select->where($this->_name.'.id_site = ?', current($filter->getSiteId()));
    	}
    	
    	// site status
    	if (!is_null($filter->getStatus())) {
    		$select->where($this->_name.'.status = ?', $filter->getStatus());
    	}
    	
    	// site owner
    	if (count($filter->getOwners()) > 1) {
    		$select->where($category_detailsthis->_name.'.id_entity_publisher IN (?)', array_keys($filter->getOwners()));
    	} else if (count($filter->getOwners()) == 1) {
    		$select->where($this->_name.'.id_entity_publisher = ?', current($filter->getOwners())->getId());
    	}
    	
    	// site owner status
    	if (!is_null($filter->getOwnersStatus())) {
    		$select->where('entity_roles.status = ?', $filter->getOwnersStatus());
    	}
    	
    	// site categories
    	if (count($filter->getCategories()) > 1) {
    		$select->joinInner('site_categories',
    			'(site_categories.id_site = '.$this->_name.'.id_site) 
    			  AND ('.
    				$this->getAdapter()->quoteInto(
    			  		'site_categories.id_category IN (?)', 
    					array_keys($filter->getCategories())) . ')',
    			array());
    	} else if (count($filter->getCategories()) == 1) {
    		$select->joinInner('site_categories',
    			'(site_categories.id_site = '.$this->_name.'.id_site) 
    			  AND ('.
    				$this->getAdapter()->quoteInto(
    			  		'site_categories.id_category = ?', 
    					current($filter->getCategories())->getId()) . ')',
    			array());
    	}
    	
    	// max site's text ads bid
    	if (!is_null($filter->getMaximumTextBid())) {
    		$select->where($this->_name.'.min_cpc <= ?', $filter->getMaximumTextBid());
    	}
    	
    	// max site's image ads bid
    	if (!is_null($filter->getMaximumImageBid())) {
    		$select->where($this->_name.'.min_cpc_image <= ?', $filter->getMaximumImageBid());
    	}
    	
    	//echo $select->assemble();
    	
    	return (int) $this->getAdapter()->fetchOne($select);
    }
    
    /**
     * Return sites according to specified filter
     * 
     * @param Sppc_Site_SearchFilter $filter
     * @param string $order
     * @param int $limit
     * @param int page
     * 
     * @return Sppc_Db_Table_Rowset
     */
    public function search(Sppc_Site_SearchFilter $filter, $order = null, $limit = null, $page = null) {
    	$select = $this->select(self::SELECT_WITH_FROM_PART)
    		->joinLeft('entity_roles', 'entity_roles.id_entity = '.$this->_name.'.id_entity_publisher AND entity_roles.id_role = 4', array()); /* publisher */
    		
    	//site id
    	if (count($filter->getSiteId()) > 1) {
    		$select->where($this->_name.'.id_site IN (?)', $filter->getSiteId());
    	} else if (count($filter->getSiteId()) == 1) {
    		$select->where($this->_name.'.id_site = ?', current($filter->getSiteId()));
    	}
    	
    	// site status
    	if (!is_null($filter->getStatus())) {
    		$select->where($this->_name.'.status = ?', $filter->getStatus());
    	}
    	
    	// site owner
    	if (count($filter->getOwners()) > 1) {
    		$select->where($this->_name.'.id_entity_publisher IN (?)', array_keys($filter->getOwners()));
    	} else if (count($filter->getOwners()) == 1) {
    		$select->where($this->_name.'.id_entity_publisher = ?', current($filter->getOwners())->getId());
    	}
    	
    	// site owner status
    	if (!is_null($filter->getOwnersStatus())) {
    		$select->where('entity_roles.status = ?', $filter->getOwnersStatus());
    	}
    	
    	// site categories
    	if (count($filter->getCategories()) > 1) {
    		$select->joinInner('site_categories',
    			'(site_categories.id_site = '.$this->_name.'.id_site) 
    			  AND ('.
    				$this->getAdapter()->quoteInto(
    			  		'site_categories.id_category IN (?)', 
    					array_keys($filter->getCategories())) . ')',
    			array());
    	} else if (count($filter->getCategories()) == 1) {
    		$select->joinInner('site_categories',
    			'(site_categories.id_site = '.$this->_name.'.id_site) 
    			  AND ('.
    				$this->getAdapter()->quoteInto(
    			  		'site_categories.id_category = ?', 
    					current($filter->getCategories())->getId()) . ')',
    			array());
    	}
    	
    	// max site's text ads bid
    	if (!is_null($filter->getMaximumTextBid())) {
    		$select->where($this->_name.'.min_cpc <= ?', $filter->getMaximumTextBid());
    	}
    	
    	// max site's image ads bid
    	if (!is_null($filter->getMaximumImageBid())) {
    		$select->where($this->_name.'.min_cpc_image <= ?', $filter->getMaximumImageBid());
    	}
    	
    	
    	// if need connect site stats table
    	if (true == $filter->getConnectToStats()) {
    		$joinCondition = array(
    			'stat_sites.id_site = '.$this->_name.'.id_site',
    			$this->getAdapter()->quoteInto('stat_sites.stat_date > ?', $filter->getStatsStartDate()->toString('yyyy-MM-dd')),
    			$this->getAdapter()->quoteInto('stat_sites.stat_date < ?', $filter->getStatsEndDate()->toString('yyyy-MM-dd'))
    		);
    		
    		$select -> joinLeft('stat_sites', 
    					'((' . implode(') AND (', $joinCondition) . '))',
    					array(
    						'SUM(clicks) as clicks',
    						'SUM(impressions) as impressions',
    						'SUM(alternative_impressions) as alternative_impressions',
    						'SUM(fraud_clicks) as fraud_clicks',
    						'SUM(earned_admin) as earned_admin',
    						'SUM(earned_publisher) as earned_publisher'
    					)
    				)-> setIntegrityCheck(false);
    	}
    	
    	// group by sites.id_site
    	$select->group($this->_name.'.id_site');
    	
    	// if need sets order
    	if (!is_null($order)) {
    		$select->order($order);
    	}
    	
    	// if need sets limit and page
    	if ((!is_null($limit)) || (!is_null($page))) {
    		$select->limitPage($page, $limit);
    	}
    	
    	$sites = $this->fetchAll($select);

    	if ($filter->getConnectToStats() == true) {
    		foreach ($sites as $site) {
    			$site->setReadOnly(true);
    		}
    	}
    	
    	return $sites; 
    }
}
