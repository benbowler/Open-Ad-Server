<?php

/**
 * Hook which extends retort_types model 
 * 
 * @author Evgeniy Balashov
 * @version $Id: 
 */
interface Sppc_Common_ReportTypes_EventHandlerInterface {
   /**
    * Modify report type
    * 
    * @param array $params
    * @return array 
    */
	public function modifyReportType($params);
} //end Sppc_Common_ReportTypes_EventHandlerInterface