<?php

/**
 * Hook which extends retort_groups model 
 * 
 * @author Evgeniy Balashov
 * @version $Id: 
 */
interface Sppc_Common_ReportGroups_EventHandlerInterface {
   /**
    * Modify report group
    * 
    * @param array $params
    * @return array 
    */
	public function modifyReportGroup($params);
} //end Sppc_Common_ReportGroups_EventHandlerInterface