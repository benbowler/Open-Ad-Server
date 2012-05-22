<?php
/**
 * Basic interface for all hook which extends admin dashboard block "Graphs For Periods"
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
interface Sppc_Admin_Dashboard_GraphsInterface {
	/**
	 * Add adttional tabs to graphs dashboard block 
	 * 
	 * @param array $data
	 * @return array
	 */
	public function addTabs(array $data);
}