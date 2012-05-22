<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* модель для работы со статистикой
* 
* @author Черенов Евгений
* @project SmartPPC6
* @version 1.0.0
*/
class Stat extends CI_Model {
 
   public function __construct() {
      parent::__construct();
   } //end Stat

    /**
    * Get values from rows
    * @param array $row
    */
   private function values($rows,$filledFields,$fields) {
      $values = array();

      foreach ($rows as $row) {
         $value = array();
         $flag = true;
         foreach ($filledFields as $name)
         {
            if ($flag && $row[$name]!= 0) {
               $value[$name] = $this->db->escape($row[$name]);
            }else{
               $flag = false;
            }
         }
         foreach ($fields as $name)
         {
            $value[$name] = $this->db->escape($row[$name]);
         }
         if ($flag){
            $values[] = '(' . implode(',', $value) . ')';
         }
      }
      return $values;
   }
   
    /**
    * Update stats Ads
    * @param array $row
    */
   public function update_ads($rows) {
      $filledFields=array(
         'id_ad'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );
      $values = $this->values($rows,$filledFields,$fields);

      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_ads (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               clicks = clicks + VALUES(clicks),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats AdsPacket
    * @param array $row
    */
   public function update_ads_packet($rows) {
      $filledFields=array(
         'id_ad',
         'id_group_site_channel'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_ads_packet (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions +  VALUES(impressions),
               clicks = clicks +  VALUES(clicks),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats AdvertiserChannels
    * @param array $row
    */
   public function update_advertiser_channels($rows) {
      $filledFields=array(
         'id_entity_advertiser',
         'id_group',
         'id_site',
         'id_channel'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_advertiser_channels (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               clicks = clicks + VALUES(clicks),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);      }
   }
   
   /**
    * Update stats Advertisers
    * @param array $row
    */
   public function update_advertisers($rows) {
      $filledFields=array(
         'id_entity_advertiser'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_advertisers (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               clicks = clicks + VALUES(clicks),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats Campaigns
    * @param array $row
    */
   public function update_campaigns($rows) {
      $filledFields=array(
         'id_campaign'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_campaigns (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               clicks = clicks + VALUES(clicks),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats Channels
    * @param array $row
    */
   public function update_channels($rows) {
      $filledFields=array(
         'id_channel'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'alternative_impressions',
         'clicks',
         'earned_admin'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_channels (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               alternative_impressions = alternative_impressions + VALUES(alternative_impressions),
               clicks = clicks + VALUES(clicks),
               earned_admin = earned_admin + VALUES(earned_admin)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats Feeds
    * @param array $row
    */
   public function update_feeds($rows) {
      $filledFields=array(
         'id_feed'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'earned_admin'
      );      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_feeds (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               clicks = clicks + VALUES(clicks),
               earned_admin = earned_admin + VALUES(earned_admin)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats Groups
    * @param array $row
    */
   public  function update_groups($rows) {
      $filledFields=array(
         'id_group'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'clicks',
         'spent'
      );      
      $values = $this->values($rows,$filledFields,$fields); 
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_groups (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               clicks = clicks + VALUES(clicks),
               impressions = impressions + VALUES(impressions),
               spent = spent + VALUES(spent)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats Sites
    * @param array $row
    */
   public  function update_sites($rows) {
      $filledFields=array(
         'id_site'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'alternative_impressions',
         'clicks',
         'earned_admin'
      );      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){
         $sql = "
            INSERT INTO
               stat_sites (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               clicks = clicks + VALUES(clicks),
               impressions = impressions + VALUES(impressions),
               alternative_impressions = alternative_impressions + VALUES(alternative_impressions),
               earned_admin = earned_admin + VALUES(earned_admin)
         ";
         $this->db->query($sql);
      }
   }
   
   /**
    * Update stats SitesChannels
    * @param array $row
    */
   public function update_sites_channels($rows) {
      $filledFields=array(
         'id_channel',
         'id_site'
      );
      $fields=array(
         'stat_date',
         'impressions',
         'alternative_impressions',
         'clicks',
         'earned_admin'
      );
      
      $values = $this->values($rows,$filledFields,$fields);
      
      if (count($values) > 0){      
         $sql = "
            INSERT INTO
               stat_sites_channels (" . implode(',', $filledFields) . ',' . implode(',', $fields) . ") 
               VALUES "  . implode(',', $values) . " 
            ON DUPLICATE KEY UPDATE
               impressions = impressions + VALUES(impressions),
               alternative_impressions = alternative_impressions + VALUES(alternative_impressions),
               clicks = clicks + VALUES(clicks),
               earned_admin = earned_admin + VALUES(earned_admin)
         ";
         $this->db->query($sql);
      }
   }
   public function get_click_info($ad,$group_site_channel,$channel) {
      $sql="SELECT a.id_group,g.id_campaign,sc.id_site,c.id_entity_advertiser FROM ads AS a LEFT JOIN groups AS g ON a.id_group=g.id_group
         LEFT JOIN group_site_channels AS gsc ON gsc.id_group=g.id_group
         LEFT JOIN campaigns AS c ON c.id_campaign=g.id_campaign
         LEFT JOIN site_channels AS sc ON sc.id_site_channel=gsc.id_site_channel
         WHERE id_ad = '" . $ad . "' AND  gsc.id_group_site_channel = '" . $group_site_channel ."' AND sc.id_channel ='" . $channel ."' LIMIT 1";
      $result = $this->db->query($sql);

      if (0 < $result->num_rows()) {
         $row = $result->row();
         return (array)$row;
      }
      return false;
   }
} //end class Stat

?>