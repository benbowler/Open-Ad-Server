<?php

/**
 * Hook which extends advertiser/create_campaign controller
 * Modify information about Pop-Up Campaign
 * 
 * @author Evgeniy Balashov
 * @version $Id: 
 */
interface Sppc_Common_CampaignWizard_EventHandlerInterface {
   /**
    * Modify titile and description of Plugin Campaign
    * 
    * @param array $campaign_type
    * @return array 
    */
	public function modifyCampaignType($campaign_type);
} //end Sppc_Common_CampaignWizard_EventHandlerInterface