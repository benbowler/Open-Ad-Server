<?php
/**
 * Object which represent country
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_Country extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Continent
	 * 
	 * @var Sppc_Continent
	 */
	protected $_continent = null;
	
	/**
	 * Sets country continent
	 * 
	 * @param Sppc_Continent $continent
	 * @return void
	 */
	public function setContinent(Sppc_Continent $continent) {
		$this->_continent = $continent;
		$this->id_continent = $continent->getId();
	}
	
	/**
	 * Return country continent
	 * 
	 * @return Sppc_Continent
	 */
	public function getContinent() {
		if ((is_null($this->_continent)) && (!is_null($this->id_continent))) {
			$this->_continent = $this->findParentRow('Sppc_ContinentModel');
		}
		
		return $this->_continent;
	}
}