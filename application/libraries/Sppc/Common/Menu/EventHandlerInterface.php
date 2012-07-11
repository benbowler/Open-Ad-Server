<?php

/**
 * Hook which extends menu model 
 * 
 * @author Evgeniy Balashov
 * @version $Id: 
 */
interface Sppc_Common_Menu_EventHandlerInterface {
   /**
    * Modify menu item info
    * 
    * @param array $params
    * @return array 
    */
	public function modifyMenuItem($params);
} //end Sppc_Common_Menu_EventHandlerInterface