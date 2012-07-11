<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/jQTree/jQTree.php';

/**
* модель для работы с историей пакета объявлений 
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Renew_history extends CI_Model {
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::__construct();
   } //end Groups

   /**
   * возвращает список истории для выбранного пакета объявлений
   *
   * @param integer $id_group_site_channel уникальный код пакета объявлений
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @return array массив с данными групп (id => (title, type, status, date)) 
   */   
   public function select($id_group_site_channel, $page, $per_page, $sort_field, $sort_direction) {
      $res = $this->db->
         select('impressions, clicks, spent, used, days, (clicks/impressions) AS ctr,'.
            ' UNIX_TIMESTAMP(start_date_time) AS start_date_time,'.
            ' UNIX_TIMESTAMP(ADDDATE(start_date_time, INTERVAL days DAY)) AS end_date_time', FALSE)->
         where('id_group_site_channel', $id_group_site_channel)->
         order_by($sort_field, $sort_direction)->
         limit($per_page, ($page-1)*$per_page)->
         get('renew_history');
      $list = array();
      $index = 0;
      foreach ($res->result() as $row) {
         $list[$index]['impressions'] = $row->impressions;
         $list[$index]['clicks'] = $row->clicks;
         $list[$index]['ctr'] = $row->ctr;
         $list[$index]['spent'] = $row->spent;
         $list[$index]['used'] = $row->used;
         $list[$index]['days'] = $row->days;
         $list[$index]['start_date_time'] = $row->start_date_time;
         $list[$index]['end_date_time'] = $row->end_date_time;
         $index++;
      }          
      return $list;      
   } //end select
      
   /**
   * возвращает количество записей в истории
   *
   * @param integer $id_group_site_channel уникальный код выбранного пакета объявлений
   * @return integer количество записей
   */   
   public function total($id_group_site_channel) {
      $res = $this->db
         ->select('COUNT(*) AS cnt, SUM(impressions) AS impressions, SUM(clicks) AS clicks,'.
            ' SUM(spent) AS spent', FALSE)
         ->where('id_group_site_channel', $id_group_site_channel)
         ->get('renew_history');
      if ($res->num_rows()) {
         $row = $res->row();
         return array(
            'impressions' => $row->impressions,
            'clicks' => $row->clicks,
            'spent' => $row->spent,
            'cnt' => $row->cnt
         );
      }          
      return NULL;      
   } //end total         
   
} //end class Renew_history

?>