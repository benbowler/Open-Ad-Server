<?php
/**
 * Basic interface for hooks which extends admin dashboard block - "Top Advertisers"
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
interface Sppc_Admin_Dashboard_TopAdvertisersInterface {
	/**
	 * Add additional columns to top sites table
	 * 
	 * @param MY_Controller $pObj
	 * @return array
	 */
	public function addColumns($pObj);
	/**
	 * Add row data for created columns
	 * 
	 * @param array $data
	 * @param array $row
	 * @return array
	 */
	public function addColumnsData(array $data, array $row);
}