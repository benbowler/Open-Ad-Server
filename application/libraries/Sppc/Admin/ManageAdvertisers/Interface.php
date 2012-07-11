<?php
/**
 * Interace which must implement all plugins hooks for controller advertiser/dashboard 
 * 
 * @author Sergey Revenko
 * @version $Id:$
 */
interface Sppc_Admin_ManageAdvertisers_Interface {
	/**
	 * Register fields for bonus ballance at dashboard
	 * 
	 * @param array $form
	 * @return void
	 */
	public function getAdditionalColumns(&$array);
	
	/**
	 * Register fields for bonus ballance at dashboard
	 * 
	 * @param array $form
	 * @return void
	 */
	public function getAdditionalFields(&$table);
	
	/**
	 * Register fields for bonus ballance at dashboard
	 * 
	 * @param array $form
	 * @return void
	 */
	public function getAdditionalCellsContent($params);
	
	public function setAdditionalStyle(&$table);
}