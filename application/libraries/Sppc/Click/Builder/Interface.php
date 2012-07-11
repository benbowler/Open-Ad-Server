<?php
/**
 * Interface which must implement all hook objects which extend 
 * functional of Click_builder class
 *   
 * @author Sergey Revenko
 * @version $Id$
 */
interface Sppc_Click_Builder_Interface {
	/**
	 * This method called by Click_Builder before click registration
	 * 
	 * @param array $click_data
	 * @param Click_builder $clickBuilder
	 */
	public function beforeClickRegistration($click_data, $clickBuilder);

	/**
	 * This method called by Click_builder after click registration
	 * 
	 * @param array $click_data
	 * @param Click_builder $clickBuilder
	 */
	public function afterClickRegistration($click_data, $clickBuilder);
}