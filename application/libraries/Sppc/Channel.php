<?php

/**
 * Channel
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_Channel extends Sppc_Db_Table_Row_Abstract {
	
	const STATUS_ACTIVE = 'active';
	const STATUS_BLOCKED = 'blocked';
	const STATUS_DELETED = 'deleted';
	const STATUS_PAUSED = 'paused';
	
	const AD_TYPE_TEXT = 'text';
	const AD_TYPE_IMAGE = 'image';

	const ADS_MODE_ADVERTISERS_ONLY = 'adv_only';
   const ADS_MODE_XMLFEEDS_ONLY = 'xml_only';
   const ADS_MODE_ADVERTISERS_THEN_XMLFEEDS = 'adv_xml';
   
   const AD_SOURCE_ADVERTISERS = 'advertisers';
   const AD_SOURCE_XMLFEEDS = 'xml_feeds';
	 
	/**
	 * Channel categories
	 * 
	 * @var array
	 */
	protected $_categories = null;
	
	/**
	 * Sites on which this channel exists
	 * 
	 * @var array
	 */
	protected $_sites = null;
	
	/**
	 * Tags which can be used on the channel
	 * 
	 * @var array
	 */
	protected $_tag = null;
	
	/**
	 * Parent site
	 * 
	 * @var Sppc_Site
	 */
	protected $_parentSite = null;
	
	/**
	 * Channel dimension
	 * 
	 * @var Sppc_Db_Table_Row
	 */
	protected $_dimension = null;
	
	/**
	 * Channel adsense code
	 * 
	 * @var string
	 */
	protected $_adsenseCode = null;
	
	/**
	 * Return categories to which this channel belongs
	 * 
	 * @return array
	 */
	public function getCategories() {
		if (is_null($this->_categories)) {
			$this->_categories = array();
			
			if (!is_null($this->getId())) {
				$categories = $this->findManyToManyRowset('Sppc_CategoryModel', 'Sppc_Channel_CategoryModel');
				
				foreach($categories as $category) {
					$this->_categories[$category->getId()] = $category;
				}
			}
		}
		
		return $this->_categories;
	}
	
    /**
	 * Return category to which this channel belongs
	 * 
	 * @return array
	 */
	public function getCategory() {
		if (is_null($this->_categories)) {
			$this->_categories = array();
			
			if (!is_null($this->getId())) {
				$categories = $this->findManyToManyRowset('Sppc_CategoryModel', 'Sppc_Channel_CategoryModel');

				foreach($categories as $category) {
					return $category->getId();
				}
			}
		}
      return key($this->_categories);
	}
	
   
	/**
	 * Sets categories to which this channel belongs
	 * 
	 * @param mixed $categories
	 * @return void
	 */
	public function setCategories($categories) {
		$this->_categories = array();
		foreach($categories as $category) {
			if (!array_key_exists($category->getId(), $this->_categories)) {
				$this->_categories[$category->getId()] = $category;
			}
		}
		$this->_modifiedFields['categories'] = true;
	}
	
	/**
	 * Add category to list of categories to which this channel belongs
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
	 * Remove category from list of categories to which this channel belongs
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
	 * Return true if channel belongs to the specified category
	 * 
	 * @param Sppc_Category $category
	 * @return bool
	 */
	public function hasCategory(Sppc_Category $category) {
		$categories = $this->getCategories();
		if (array_key_exists($category->getId(), $categories)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Return sites on which this channel exists
	 * 
	 * @param bool $status If not equal "all" only sites with specified status wil be returned
	 * @return array
	 */
	public function getSites($status = 'all') {
		if ($status == 'all') {
			if (is_null($this->_sites)) {
				$this->_sites = array();
				
				if (!is_null($this->getId())) {
					$sites = $this->findManyToManyRowset('Sppc_SiteModel', 'Sppc_Site_ChannelModel');
					foreach($sites as $site) {
						$this->_sites[$site->getId()] = $site;
					} 
				}
			}
			
			return $this->_sites;
		} else {
			$select = $this->getTable()->select()->where('site_channels.status = ?', $status);
			$rows = $this->findManyToManyRowset('Sppc_SiteModel', 'Sppc_Site_ChannelModel', null, null, $select);
			
			$sites = array();
			foreach($rows as $row) {
				$sites[$row->getId()] = $row;
			}
			
			return $sites;
		}
	}
	
	/**
	 * Sets site on which this channel exists
	 * 
	 * @param Iterator $sites
	 * @return void
	 */
	public function setSites($sites) {
		$this->_sites = array();
		foreach($sites as $site) {
			if (!array_key_exists($site->getId(), $this->_sites)) {
				$this->_sites[$site->getId()] = $site;
			}
		}
		$this->_modifiedFields['sites'] = true;
	}
	
	/**
	 * Add site on which this channel exists
	 * 
	 * @param Sppc_Site $site
	 * @return void
	 */
	public function addSite(Sppc_Site $site) {
		$sites = $this->getSites();
		if (!array_key_exists($site->getId(), $sites)) {
			$this->_sites[$site->getId()] = $site;
			$this->_modifiedFields['sites'] = true;
		}
	}
	
	/**
	 * Remove specified site from list of sites on which this channel exists
	 *
	 * @param Sppc_Site $site
	 * @return void
	 */
	public function removeSite(Sppc_Site $site) {
		$sites = $this->getSites();
		if (array_key_exists($site->getId(), $sites)) {
			unset($this->_sites[$site->getId()]);
			$this->_modifiedFields['sites'] = true;
		}
	}
	
	/**
	 * Sets parent site
	 * 
	 * @param Sppc_Site $site
	 * @return void
	 */
	public function setParentSite(Sppc_Site $site) {
		$this->_parentSite = $site;
		$this->id_parent_site = $site->getId();
	}
	
	/**
	 * Return parent site
	 * 
	 * @return Sppc_Site
	 */
	public function getParentSite() {
		if (is_null($this->_parentSite)) {
			$this->_parentSite = $this->findParentRow('Sppc_SiteModel', 'ParentSite');
		}
		
		return $this->_parentSite;
	}
   
    /**
	 * Return parent site category
	 * 
	 * @return Sppc_Site
	 */
	public function getParentSiteCategory() {
      $sites = $this->getParentSite();
      return $sites->getCategory();
	}
	
	/**
	 * Return channel dimension
	 * 
	 * @return Sppc_Db_Table_Row
	 */
	public function getDimension() {
		if (is_null($this->_dimension)) {
			$this->_dimension = $this->findParentRow('Sppc_DimensionModel', 'Dimension');
		}
		
		return $this->_dimension;
	}
	
	/**
	 * Sets channel dimension
	 * 
	 * @param Sppc_Db_Table_Row_Abstract $dimension
	 * @return void
	 */
	public function setDimension(Sppc_Db_Table_Row_Abstract $dimension) {
		$this->_dimension = $dimension;
		$this->id_dimension = $dimension->getId();
	}
	
	/**
	 * Return ad sources for channel
	 * 
	 * @return array
	 */
	public function getAdSources() {
		return explode(',', $this->ad_sources);
	}
	
	/**
	 * Sets channel ad sources
	 * 
	 * @param array $adSources
	 * @return void
	 */
	public function setAdSources($adSources = array()) {
		$this->ad_sources = implode(',', $adSources);
	}
	
	/**
	 * Add specified ad source to list of channel ad sources
	 * 
	 * @param string $adSource
	 * @return void
	 * @throws Sppc_Exception
	 */
	public function addAdSource($adSource) {
		if (($adSource != self::AD_SOURCE_ADVERTISERS)
		    && ($adSource != self::AD_SOURCE_XMLFEEDS)
		    )
		{
			throw new Sppc_Exception('Invalid ad source specified');
		}
		
		$channelAdSources = explode(',', $this->ad_sources);
		if (!in_array($adSource, $channelAdSources)) {
			$channelAdSources[] = $adSource;
			
			$this->ad_sources = implode(',', $channelAdSources);
		}
	}
	
	/**
	 * Remove specified ad sources from list of channel ad sources
	 * 
	 * @param string $adSource
	 * @return void
	 */
	public function removeAdSource($adSource) {
		$channelAdSources = explode(',', $this->ad_sources);
		
		$adSourceIndex = array_search($adSource, $channelAdSources);
		if (false !== $adSourceIndex) {
			unset($channelAdSources[$adSourceIndex]);
			$this->ad_sources = implode(',', $channelAdSources);
		}
	}
	
	/**
	 * Return true if specified ad source already added in list of ad sources for channel
	 * 
	 * @param string $adSource
	 * @return bool
	 */
	public function hasAdSource($adSource) {
		$adSources = $this->getAdSources();
		
		if (in_array($adSource, $adSources)) {
			return true;
		}
		
		return false;
	}  
	
	/**
	 * Return ad types which allowed for this channel
	 * 
	 * @return array
	 */
	public function getAdTypes() {
		return explode(',', $this->ad_type);
	}
	
	/**
	 * Sets ad types which allowed for this channel
	 * 
	 * @param array $types
	 * @return void
	 */
	public function setAdTypes(array $types = array()) {
		$adTypes = array();
		$allowedTypes = array(self::AD_TYPE_TEXT, self::AD_TYPE_IMAGE);
		
		foreach($types as $type) {
			if (in_array($type, $allowedTypes)) {
				$adTypes[] = $type;
			}	
		}
		
		$this->ad_type = implode(',', $adTypes);
	}
	
	/**
	 * Add specified type to ad types list which allowed for this channel
	 *  
	 * @param string $type
	 * @return void
	 */
	public function addAdType($type) {
		$adTypes = $this->getAdTypes();
		$allowedTypes = array(self::AD_TYPE_TEXT, self::AD_TYPE_IMAGE);
		
		if ((!in_array($type, $adTypes)) && (in_array($type, $allowedTypes))) {
			$adTypes[] = $type;
			$this->ad_type = implode(',', $adTypes);
		}
	}
	
	/**
	 * Remove specified type from ad types list which allowed for this channel
	 * 
	 * @param string $type
	 * @return void
	 */
	public function removeAdType($type) {
		$adTypes = $this->getAdTypes();
		if ($typeIndex = array_search($type, $adTypes)) {
			unset($adTypes[$typeIndex]);
			$this->ad_type = implode(',', $adTypes);
		}
	}
	
	/**
	 * Check if specified ad type allowed in this channel
	 * 
	 * @param string $type
	 * @return bool
	 */
	public function isAdTypeAllowed($type) {
		$adTypes = $this->getAdTypes();
		if (in_array($type, $adTypes)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Insert/update record in DB
	 * $th
	 * @return mixed
	 */
	public function save() {
		$needUpdateCategories = false;
		$needUpdateSites = false;
		$needUpdateTags = false;
		
	   if ((array_key_exists('categories', $this->_modifiedFields)) &&  ($this->_modifiedFields['categories'] == true)) {
         $needUpdateCategories = true;
      }
	   if ((array_key_exists('sites', $this->_modifiedFields)) &&  ($this->_modifiedFields['sites'] == true)) {
         $needUpdateSites = true;
      }
	  if ((array_key_exists('tags', $this->_modifiedFields)) &&  ($this->_modifiedFields['tags'] == true)) {
         $needUpdateTags = true;
      }
      
      $result = parent::save();
      
      // if need, update channel categories
	   if ($needUpdateCategories) {
         $channelCategoryModel = new Sppc_Channel_CategoryModel();
         
         $channelCategories = $this->findDependentRowset('Sppc_Channel_CategoryModel', 'Channel');
         $categories = $this->getCategories();
         
         foreach ($channelCategories as $channelCategory) {
            if (!array_key_exists($channelCategory->getIdCategory(), $categories)) {
               $channelCategory->delete();
            }
         }
         
         foreach($categories as $category) {
            $needToCreate = true;
            foreach($channelCategories as $channelCategory) {
               if ($channelCategory->getIdCategory() == $category->getId()) {
                  $needToCreate = false;
                  break;
               }
            }
            
            if ($needToCreate) {
               $data = array('id_channel' => $this->getId(), 'id_category' => $category->getId());
               $row = $channelCategoryModel->createRow($data);
               $row->save();
            }
         }
      }
      
      // if need update channel sites
      if ($needUpdateSites) {
      	$siteChannelModel = new Sppc_Site_ChannelModel();
      	
      	$channelSites = $this->findDependentRowset('Sppc_Site_ChannelModel');
      	$sites = $this->getSites();

      	foreach($channelSites as $channelSite) {
      		if (!array_key_exists($channelSite->getIdSite(), $sites)) {
      			$channelSite->setStatus('deleted');
      			$channelSite->save();
      		}
      	}
      	
      	foreach($sites as $site) {
      		$needToCreate = true;
      		foreach ($channelSites as $channelSite) {
      			if ($site->getId() == $channelSite->getIdSite()) {
      				$needToCreate = false;
      				break;
      			}
      		}
      		
      		if ($needToCreate) {
      			$data = array('id_channel' => $this->getId(), 'id_site' => $site->getId());
      			$row = $siteChannelModel->createRow($data);
      			$row->save();
      		}
      	}
      }
      
      return $result;
	}
	
	/**
	 * Additional pre insert logic
	 * 
	 * @return void
	 */
	protected function _insert() {
		$this->creation_date = Zend_Date::now()->toString('yyyy-MM-dd hh:mm:ss');
	}
}