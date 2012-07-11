<?php

require_once APPPATH . 'helpers/launch_helper.php';

/**
 * Stats library
 * @author Gennadiy Kozlenko
 */
class Sppc_Stats {
   
   // input Fields
   private $fields = array(
      'id_click',
      'type',
      'datetime',
      'date',
      'country',
      'language',
      'program_type',
      'ad_type',
      'ad_display_type',
      'id_feed',
      'id_advertiser',
      'id_campaign',
      'id_group',
      'id_ad',
      'id_site',
      'id_channel',
      'id_group_site_channel',
      'position',
      'destination_url',
      'referer_url',
      'user_agent',
      'browser',
      'status',
      'spent'
   );

   // output Fields   
   private $outFields = array(
      'id_ad',
      'impressions',
      'clicks',
      'spent',
      'id_group_site_channel',      
      'id_entity_advertiser',
      'id_group',
      'id_site',
      'id_channel',
      'id_campaign',
      'alternative_impressions',
      'id_feed',
      'earned_admin'
   );
   
   /**
    * Class constructor
    */
   public function __construct() {
   }
   
    /**
    * set stats db
    */
   public function writeStatstoDb($statsArray) { 
      $obj =& get_instance();
      $values = array();
      foreach ($statsArray as $stats) {
         if (is_array($stats)) {
            $value = array('stat_date' => date('Y-m-d')); 
            foreach($this->outFields as $name){
               if (isset($stats[$name])){
                  $value[$name] = $stats[$name];
                  }else{
                  $value[$name] = 0;
               }
            }
            $values[]=$value;
         }
      }

      $obj->load->model('stat');
      if ($values!=array()) {
         $obj->stat->update_ads($values);
         $obj->stat->update_ads_packet($values);
         $obj->stat->update_advertiser_channels($values);
         $obj->stat->update_advertisers($values);
         $obj->stat->update_campaigns($values);
         $obj->stat->update_channels($values);
         $obj->stat->update_feeds($values);
         $obj->stat->update_groups($values);
         $obj->stat->update_sites($values);
         $obj->stat->update_sites_channels($values);
      }
   }
   
}
