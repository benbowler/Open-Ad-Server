<?php
/**
 * Basic interface for hooks which extends admin dashboard block "Summary Performance"
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
interface Sppc_Admin_Dashboard_SummaryPerformanceInterface {
	/**
	 * Add additional summary performance fields
	 *  
	 * @param array $stats
	 * @return array 
	 */
	public function addSummaryPerformanceFields($stats);
}