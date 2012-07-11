<?php

interface Sppc_Common_ManageSiteChannels_Interface {
	/**
	 * Register additional buttons
	 * 
	 * @return string
	 */
	public function registerAdditionalButtons();
	/**
    * Register additional buttons for site
    * 
    * @return string
    */
   public function registerAdditionalSiteButtons($cell, $id_site);
   /**
    * Register JScode for buttons added by the 'registerAdditionalSiteButtons'
    * 
    * @return string
    */
   public function registerJSSiteButtons();
}