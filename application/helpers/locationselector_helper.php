<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper for drawing country selector control
 */

if (!(function_exists('render_location_selector_field'))) {
	/**
	 * Helper for rendering location selector field
	 * 
	 * @param array $countries Already selected location
	 * @param string $form_field Form field which store selected locations
	 * @param string $dialog_button Element which trigger location selector dialog 
	 * @param string $container Element in which control render selected locations
	 * @return string Location selector element code
	 */
	function render_location_selector_field($countries = array(), $form_field, $dialog_button, $container) {
		$selectedLocations = array();
		
		foreach ($countries as $country) {
			/* @var $category Sppc_Country */
			$selectedLocations[] = array(
				'id' => $country->getId(),
				'name' => $country->getName()
			);
		}
		
		$countryModel = new Sppc_CountryModel();
		
		$viewData = array(
			'SELECTED_LOCATIONS' => json_encode($selectedLocations),
			'FORM_FIELD' => $form_field,
			'DIALOG_BUTTON' => $dialog_button,
			'CONTAINER_ELEMENT' => $container,
			'TOTAL_COUNTRIES' => $countryModel->getCount()
		);
		
		$CI = &get_instance();
		
		return $CI->parser->parse('common/location_selector/control.html', $viewData, true);
	}
}