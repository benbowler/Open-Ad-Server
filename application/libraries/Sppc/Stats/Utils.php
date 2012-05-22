<?php

/**
 * Утилита для работы со статистикой
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Stats_Utils {
   
   /**
    * Search stats
    */
   const STATS_SEARCH = 'searches';
   
   /**
    * Click stats
    */
   const STATS_CLICK = 'clicks';
   
   /**
    * Stats types
    */
   const STATS_TYPE_SEARCH = 'search';
   const STATS_TYPE_CLICK = 'click';
   const STATS_TYPE_EMPTY = 'empty';
   
   /**
    * Generate clickid
    * @param int $server
    * @param int $date
    * @param string $seed
    * @param bool $useRand
    * @return string
    */
   public static function generateClickIdold($server = 1, $date = 0, $seed = '', $useRand = true) {
      if (0 == $date) {
         $date = time();
      }
      $hash = '';
      if ($useRand) {
         $hash = uniqid(self::makeSeed(), true);
      }
      $clickid = $server . '_' . date('YmdHi', $date) . '_' . md5($hash . $seed);
      return $clickid;
   }   
   
   /**
    * Generate clickid
    * обновил параметры
    * @param array data:    
    *  int id_feed
    *  int id_ad
    *  int id_cannel
    *  int id_group_site_cannel
    *  string destination_url
    *  int spent
    * @return string
    */
   public static function generateClickId($data) {
      $CI =& get_instance();
      $CI->load->library('encrypt');
      if ((!isset($data['id_feed'])) || ($data['id_feed'] == 0)){
         $clickData = $data['id_channel'] . '|' . $data['id_group_site_channel'] . '|' . $data['id_ad'] . '|' . 0 . '|' . 0;
      }else{
         $clickData = 0 . '|' . 0 . '|' . 0 . '|' . $data['id_feed'] . '|' . $data['spent'];      
      }
      $temp = $CI->encrypt->encode($clickData) . '|' . $data['destination_url'];

      $clickid = urlencode($CI->encrypt->encode($clickData) . '|' . $data['destination_url']);
      return $clickid;
   }
   
   /**
    * Extract metadata from clickid
    * @param string $clickid
    * @return array|bool
    *  int time - time
    *  int feed - id_feed
    *  int ad - id_ad
    *  int channel - id_cannel
    *  int group_site_cannel - id_group_site_cannel
    *  string url - destination_url
    *  int spent - spent
    * 
    */
   public static function extractClickId($clickid) {
      $clickData = explode('|', $clickid, 2);   
      $CI =& get_instance();
      $CI->load->library('encrypt');
      $clickData[0] = explode('|', $CI->encrypt->decode($clickData[0]), 5);
      if (5 == count($clickData[0])) {
         $data = array(
            'id_channel' => (int) $clickData[0][0],
            'id_group_site_channel' => (int) $clickData[0][1],
            'id_ad' => (int) $clickData[0][2],
            'id_feed' => (int) $clickData[0][3],
            'spent' => (double) $clickData[0][4],
            'earned_admin' => (double) $clickData[0][4],
            'destination_url' => $clickData[1]
         );
         return $data;
      }

      return false;
   }
   
   /**
    * Make seed extended
    * @return unknown
    */
   public static function makeSeed() {
      list($usec, $sec) = explode(' ', microtime());
      return (float) $sec + ((float) $usec * 100000);
   }

}