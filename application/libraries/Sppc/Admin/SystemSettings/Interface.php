<?php

interface Sppc_Admin_SystemSettings_Interface {
	/**
	 * Process data from additional field
	 * 
	 * @param array $fields
	 * @return void
	 */
	public function saveAdditionalSettings($fields);
	
	/**
	 * Return rendered additional fields
	 * 
	 * @return string rendered additional fields
	 */
	public function getAdditionalSettingsHTML();
	
	/**
	 * Register fields for additional settings in form object
	 * 
	 * @param array $form
	 * @return void
	 */
	public function registerFieldsForAdditionalSettings(&$form);
	
	/**
	 * Load data for additional settings
	 * 
	 * @param array $fields
	 * @return void
	 */
	public function loadAdditionalSettings(&$fields);
}