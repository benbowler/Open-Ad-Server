<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' )) exit ( 'No direct script access allowed' );
require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Controller for getting info about locations (countries and continents)
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Locations extends Parent_controller {
	/**
	 * Roles
	 * 
	 * @var string
	 */
	protected $role = 'guest';
	
	/**
	 * Render list of children locations
	 * 
	 * @throws Sppc_Exception
	 * @return void
	 */
	public function get_children() {
		header('Content-Type: application/json');
		
		try {
			$locationId = $this->input->get('id');
			
			if (false === $locationId) {
				throw new Sppc_Exception('Parent item not specified');
			}
			
			if ($locationId == 0) {
				$locations = $this->_get_continents();
			} else {
				$locations = $this->_get_countries($locationId);
			}
			
			echo json_encode($locations);
		} catch (Exception $e) {
			echo json_encode(array());
		}
	}
	
	/**
	 * Return list of continents
	 * 
	 * @return array
	 */
	protected function _get_continents() {
		$locations = array();
		
		$continentModel = new Sppc_ContinentModel();
		$continents = $continentModel->fetchAll();
		
		foreach($continents as $continent) {
			$location = array(
				'attributes' => array(
					'id' => 'continent_' . $continent->getId()
				),
				'data' => $continent->getName(),
				'state' => 'closed'
			);
			
			$locations[] = $location;
		}
		
		return $locations;
	}
	
	/**
	 * Return list of countries on specified continent
	 * 
	 * @param int $continentId
	 * @return array
	 */
	protected function _get_countries($continentId) {
		$locations = array();
		
		$continentModel = new Sppc_ContinentModel();
		$continent = $continentModel->findObjectById($continentId);
		
		if (is_null($continentModel)) {
			throw new Sppc_Exception('Specified continent not found');
		}
		/** @var $continent Sppc_Continent */
		$countries = $continent->getCountries();
		foreach($countries as $country) {
			$locations[] = array(
				'attributes' => array('id' => 'country_'.$country->getId()),
				'data' => $country->getName()
			);
		}
		
		return $locations;
	}
	
	public function get_tree() {
		header('Content-Type: application/json');
		try {
			$locations = array();
			
			$continentModel = new Sppc_ContinentModel();
			$continents = $continentModel->fetchAll();
			
			foreach($continents as $continent) {
				$continentData = array(
					'attributes' => array('id' => 'continent_' . $continent->getId()),
					'data' => $continent->getName(),
					'state' => 'closed',
					'children' => array()
				);
				
				$countries = $continent->getCountries();
				foreach($countries as $country) {
					$continentData['children'][] = array(
						'attributes' => array('id' => 'country_'.$country->getId()),
						'data' => $country->getName()
					);
				}
				
				$locations[] = $continentData;
			}
			
			echo json_encode($locations);
		} catch (Exception $e) {
			echo json_encode(array());
		}
	}
}