<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с фидами
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Feeds extends CI_Model {
 
   /**
   * конструктор, инициализация базового класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end Feeds

   /**
   * возвращает список фидов, которые можно запросить для установки в систему
   *
   * @return array массив имен фидов
   */   
   public function get_request_feeds() {
      $installed = $this->get_list(array('type' => 'used'));
      $res = $this->db->get('feeds_to_request');
      $feeds = array();
      foreach ($res->result() as $row) {
      	$feeds[$row->name] = $row->name;
      }
      foreach ($installed as $id => $feed) {
      	unset($feeds[$feed]);
      }
      $feeds['Other...'] = __('Other...');
      return $feeds;      
   } //end
   
   /**
   * возвращает список существующих фидов
   *
   * @param array параметры выборки
   *    type = 'request' выбрать фиды доступные для запроса 
   * @return array список в формате id_feed => name
   */   
   public function get_list($params) {
      if ($params['type'] == 'request') {
         return $this->get_request_feeds();
      }
      $this->db->select('id_feed, name, timeout')
      		   ->order_by('name');
      $res = $this->db->get('feeds');
      $feeds = array();
      if($res->num_rows()) {
         foreach ($res->result() as $row) {
            $feeds[$row->id_feed] = $row->name;
         }
      }
     
      return $feeds;
   } //end get_list
   
   /**
   * возвращает список существующих фидов. Версия 2 (с выбором возвращаемых полей )
   *
   * @param array параметры выборки
   * @return array список в формате id_feed => name
   */   
   public function get_list2($params) {
     if (isset($params['fields'])) {
     	$this->db->select($params['fields']);
     }  
     
   	 if (isset($params['status'])) {
     	$this->db->where_in($params['status']);
     }
     
   	 if (isset($params['order_by'])) {
     	$this->db->order_by($params['order_by']);
     }
     
      $res = $this->db->get('feeds');
      
      $feeds = array();
      if($res->num_rows() > 0) {
         foreach ($res->result() as $row) {
            $feeds[] = $row;
         }
      }
      return $feeds;
   } //end get_list
   
   /**
   * возвращает имя фида по его коду
   *
   * @param integer $id_feed код фида
   * @return string имя фида, '' - если фид не найден
   */   
   public function get($id_feed) {
   	$this->db->select('name');
   	$res = $this->db->get_where('feeds', array('id_feed' => $id_feed));
   	if ($res->num_rows()) {
   	   $row = $res->row();
   	   return $row->name;
   	}
   	return '';
   } //end get   
 
   /**
   * возвращает данные фида по его коду
   *
   * @param integer $id_feed код фида
   * @return object
   */   
   public function get_feed($id_feed) {
   	$this->db->select();
   	$res = $this->db->get_where('feeds', array('id_feed' => $id_feed));
   	if ($res->num_rows()) {
   	   return $res->row();
   	}
   	return false;
   } //end get   
 
   /**
   * возвращает список лучших фидов для администратора
   *
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param array $range массив задающий отчептный период (from, to), по умолчанию - весь период
   * @return array массив со списком сайтов в формате (id_feed => (name, impressions, clicks, revenue, ctr))
   */   
   public function top($sort_field, $sort_direction, $range) {
      $limit = $this->global_variables->get("TopFeedsOnPage");
         
      $this->db->select('feeds.id_feed AS id, feeds.name, feeds.title, SUM(earned_admin) AS revenue,'.
         ' SUM(impressions) AS impressions, SUM(clicks) AS clicks, SUM(clicks)*100/SUM(impressions) AS ctr');
      $this->db->from('feeds');
      if (!is_null($range)) {
         $this->db->join('stat_feeds', 
            'feeds.id_feed = stat_feeds.id_feed AND stat_date>="'.type_to_str($range['from'], 'databasedate').
            '" AND stat_date<="'.type_to_str($range['to'], 'databasedate').'"', 
            'LEFT');
      } else {
         $this->db->join('stat_feeds', 'feeds.id_feed = stat_feeds.id_feed', 'LEFT');         
      }
      $this->db->group_by('id')
      	       ->order_by($sort_field, $sort_direction)
      		   ->limit($limit);
      $res = $this->db->get();        
         
      $top = array();  
      foreach ($res->result() as $row) {
         $top[$row->id]['name'] = $row->name; 
         $top[$row->id]['title'] = $row->title; 
         $top[$row->id]['revenue'] = $row->revenue; 
         $top[$row->id]['impressions'] = $row->impressions; 
         $top[$row->id]['clicks'] = $row->clicks; 
         $top[$row->id]['ctr'] = $row->ctr; 
      }     
      return $top;              
   } //end top

   /**
   * возвращает количество фидов, попадающих под указанный фильтр
   *
   * @param string $filt фильтр по статусу
   * @return integer количество
   */
   public function total($filt, $range) {
      $total = array(
         'cnt'         => 0,
         'impressions' => 0,
         'clicks'      => 0,
         'revenue'     => 0
      );
      
      // All pages total
      $this->db->select("SUM(sf.impressions) AS impressions, 
         SUM(sf.clicks) AS clicks, SUM(sf.earned_admin) AS revenue", false)
         ->from('feeds f')
         ->join('stat_feeds sf', "f.id_feed = sf.id_feed AND sf.stat_date BETWEEN '" . type_to_str($range['from'], 'databasedate') . "' AND '" . type_to_str($range['to'], 'databasedate') . "'", 'left');
      if ($filt != 'all') {
         $this->db->where('f.status', $filt);
      }
      $query = $this->db->get();
      if (0 < $query->num_rows()) {
         $row = $query->row();
         $total['impressions'] = $row->impressions;
         $total['clicks'] = $row->clicks;
         $total['revenue'] = $row->revenue;
      }
      
      // All count
      $this->db->from('feeds');
      if ($filt != 'all') {
         $this->db->where('status', $filt);
      }
      $total['cnt'] = $this->db->count_all_results();
                  
      return $total;
   } //end total  

   /**
   * возвращает отсортированную выборку фидов 
   *
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @return array массив с данными фидов (id => (name, status, impressions, clicks, ctr, revenue)) 
   */   
   public function select($page, $per_page, $sort_field, $sort_direction, $filt, $range) {
      $this->db
         ->select('feeds.id_feed AS id, feeds.name, feeds.title, status, (SUM(clicks)*100/SUM(impressions)) AS ctr', FALSE)
         ->select_sum('impressions')
         ->select_sum('clicks')
      	->select_sum('earned_admin')
      	->from('feeds')
      	->join('stat_feeds', 'feeds.id_feed = stat_feeds.id_feed AND (stat_date BETWEEN "'.
                 type_to_str($range['from'], 'databasedate').'" AND "'.
                 type_to_str($range['to'], 'databasedate').'")', 'LEFT');
      if ($filt != 'all') {
         $this->db->where('status', $filt);
      }
      $this->db
         ->order_by($sort_field, $sort_direction)
      	->group_by('id')
      	->limit($per_page, ($page-1)*$per_page);
      $res = $this->db->get();
      $list = array();
      foreach ($res->result() as $row) {
         $id_list[] = $row->id;
         $list[$row->id]['name'] = $row->name;
         $list[$row->id]['title'] = $row->title;
         $list[$row->id]['status'] = 'feed_'.$row->status;
         $list[$row->id]['impressions'] = $row->impressions;
         $list[$row->id]['clicks'] = $row->clicks;
         $list[$row->id]['revenue'] = $row->earned_admin;
         $list[$row->id]['ctr'] = $row->ctr;
      }
      return $list;      
   } //end select   
   
   /**
   * приостанавливает работу выбранного фида
   *
   * @param integer $id_feed код нужного фида
   * @return ничего не возвращает
   */   
   public function pause($id_feed) {
      $this->db->where('id_feed', $id_feed)
      		   ->update('feeds', array('status' => 'paused'));
   } //end pause
   
   /**
   * возобновляет работу выбранного фида
   *
   * @param integer $id_feed код нужного фида
   * @return ничего не возвращает
   */   
   public function resume($id_feed) {
      $this->db->where('id_feed', $id_feed)
      	       ->update('feeds', array('status' => 'active'));
   } //end resume
      
   /**
   * возвращает настройки для заданного фида
   *
   * @param integer $id_feed код выбранного фида
   * @return array настройки фида в формате (commission, affiliate_id)
   */   
   public function get_settings($id_feed) {
      $CI =& get_instance();
      $default_commission = $CI->global_variables->get("DefaultFeedCommission");
   	$this->db->select('commission, affiliate_id_1');
   	$res = $this->db->get_where('feeds', array('id_feed' => $id_feed));
   	foreach ($res->result() as $row) {
   		return array('commission' => $row->commission, 'affiliate_id' => $row->affiliate_id_1);
   	}
      return array('commission' => $default_commission, 'affiliate_id' => '');   	
   } //end get_settings   

   /**
   * сохраняет новые настройки для заданного фида
   *
   * @param integer $id_feed код выбранного фида
   * @param array $fields настройки фида в формате (field => value)
   * @return ничего не возвращает
   */   
   public function set_settings($id_feed, $fields) {
      $this->db->where('id_feed', $id_feed)
   		       ->update('feeds', $fields);
   } //end set_settings
   
   public function active_feeds_qty(){
      $this->db->select('id_feed')
               ->where('status','active')
               ->where('name <> "OurdatabaseKeywords"');
      $res = $this->db->get('feeds');
            
      return $res->num_rows();      
   }
   
} //end class Feeds

?>