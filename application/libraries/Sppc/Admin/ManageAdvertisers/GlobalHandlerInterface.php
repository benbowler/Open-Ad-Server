<?php
/**
 * Basic interface for all hooks which extend functionality of 
 * "Manage Advertisers" controller 
 * 
 * @author Evgeny Balashov
 * @version $Id$
 */
interface Sppc_Admin_ManageAdvertisers_GlobalHandlerInterface {

   /**
    * Возвращает js-код для JS функциий плагина 
    *
    * @return string
    */
   public function get_js_functions_html($pObj);
   
} //end interface Sppc_Admin_ManageAdvertisers_GlobalHandlerInterface