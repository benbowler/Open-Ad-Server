<?php
/**
 * Object which represent continent
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_Continent extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Countries
	 * 
	 * @var array
	 */
	protected $_countries = null;
	
	/**
	 * Return list of countries
	 * 
	 * @return array
	 */
	public function getCountries() {
		if (is_null($this->_countries)) {
			$countryModel = new Sppc_CountryModel();
			$select = $countryModel->select()->order('name');
			
			$this->_countries = $this->findDependentRowset('Sppc_CountryModel', 'Continent', $select);
		}
		
		return $this->_countries;
	}
}