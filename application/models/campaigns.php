<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* модель для работы с кампаниями
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Campaigns extends CI_Model {
 
   
   /** 
   * конструктор класса, инициализация базового класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::__construct();
   } //end Campaigns

   /**
   * возвращает список лучших кампаний адвертайзера
   *
   * @param integer $id_entity код адвертайзера
   * @param string $sort_field имя поля по которому необходимо осуществлять сортировку
   * @param string $sort_direction направление сортировки
   * @param array $range массив задающий отчетный период (from, to), если не задан - за все время
   * @return array массив со списком кампаний в формате id => (name, spent, impressions, clicks)
   */   
   public function top($id_entity, $sort_field, $sort_direction, $range) {
   	$limit = $this->global_variables->get("TopCampaignsOnPage");   	
      $this->db->select('
      	 campaigns.id_campaign AS id, name, 
      	 SUM(spent) AS spent,
      	 SUM(impressions) AS impressions, 
      	 SUM(clicks) AS clicks,
      	 SUM(clicks)*100/SUM(impressions) AS ctr, 
      	 status
      ');
      $this->db->from('campaigns');
      $this->db->where('id_entity_advertiser', $id_entity);
      if (!is_null($range)) {
         $this->db->join('stat_campaigns', 
            'campaigns.id_campaign = stat_campaigns.id_campaign'.
            ' AND stat_date>="'.type_to_str($range['from'], 'databasedate').
            '" AND stat_date<="'.type_to_str($range['to'], 'databasedate').'"', 
            'LEFT');
               } else {
         $this->db->join('stat_campaigns', 'campaigns.id_campaign = stat_campaigns.id_campaign', 'LEFT');         
      }
      $this->db->group_by('id');
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($limit);
      $res = $this->db->get();                             	   	
   	   	   	
      $top = array();  
   	foreach ($res->result() as $row) {
   	   $top[$row->id]['name'] = $row->name; 
         $top[$row->id]['spent'] = $row->spent; 
   	   $top[$row->id]['impressions'] = $row->impressions; 
   	   $top[$row->id]['clicks'] = $row->clicks; 
         $top[$row->id]['ctr'] = $row->ctr; 
         $top[$row->id]['status'] = $row->status;
   	}   	
   	     	
   	return $top;   	   	
   } //end top()

   /**
   * возвращает список запрошенных кампаний для заданного пользователя
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param string $type_filter фильтр по типу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @return array массив с данными кампаний (id => (title, type, status, date)) 
   */   
   public function select($id_entity, $page, $per_page, $sort_field, $sort_direction, $filt, $range) {
      $this->db->select('campaigns.id_campaign AS id_campaign, campaigns.name AS name,'.
         ' campaigns.status AS status, COUNT(DISTINCT groups.id_group) AS groups,'.
         ' COUNT(DISTINCT id_ad) AS ads, id_campaign_type AS type,'.
         ' if(id_campaign_type="cpc",COUNT(DISTINCT id_group_site),COUNT(DISTINCT id_group_site_channel)) AS site_channels', FALSE);
      $this->db->from('campaigns');
      $this->db->join('groups', 'campaigns.id_campaign = groups.id_campaign AND groups.status!="deleted"', 'LEFT');
      $this->db->join('ads', 'groups.id_group = ads.id_group AND ads.status!="deleted"', 'LEFT');
      $this->db->join('group_site_channels', 'groups.id_group = group_site_channels.id_group AND group_site_channels.status!="deleted"', 'LEFT');
      $this->db->join('site_channels', 'group_site_channels.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT');           
      $this->db->join('sites', 'site_channels.id_site = sites.id_site', 'LEFT');           
      $this->db->join('channels', 'site_channels.id_channel = channels.id_channel', 'LEFT');           
      $this->db->join('group_sites gs', 'groups.id_group = gs.id_group AND gs.status!="deleted"', 'LEFT');
      $this->db->where('id_entity_advertiser', $id_entity);
      if ($filt == 'all') {
         $this->db->where('campaigns.status !=', 'deleted');
      } elseif ($filt == 'blocked') {
         $this->db->where(array('campaigns.status' => 'active', 'groups.frequency_coup_current'=> 0));
      } else {
         $this->db->where('campaigns.status', $filt);
      }
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($per_page, ($page-1)*$per_page);
      $this->db->group_by('id_campaign');
      $res = $this->db->get();
      $campaigns = array();
      foreach ($res->result() as $row) {
         $campaigns[$row->id_campaign]['name'] = $row->name;
         $campaigns[$row->id_campaign]['status'] = $row->status;
         $campaigns[$row->id_campaign]['groups'] = $row->groups;
         $campaigns[$row->id_campaign]['ads'] = $row->ads;
         $campaigns[$row->id_campaign]['sites_channels'] = $row->site_channels;
         $campaigns[$row->id_campaign]['spent'] = 0;
         $campaigns[$row->id_campaign]['impressions'] = 0;
         $campaigns[$row->id_campaign]['clicks'] = 0;
         $campaigns[$row->id_campaign]['ctr'] = 0;
         $campaigns[$row->id_campaign]['trouble'] = FALSE;            
         $campaigns[$row->id_campaign]['unpaid'] = FALSE;            
         $campaigns[$row->id_campaign]['type'] = $row->type;
         $campaigns[$row->id_campaign]['id']  = $row->id_campaign;
      }    
      
      // получение статистики для выбранных кампаний
      if (count($campaigns)) {
         $res = $this->db->select('id_campaign')->
            select_sum('spent')->
            select_sum('clicks')->
            select_sum('impressions')->
            select('SUM(clicks)*100/SUM(impressions) AS ctr')->
            from('stat_campaigns')->
            where_in('id_campaign', array_keys($campaigns))->
            where('stat_date >=', type_to_str($range['from'], 'databasedate'))->
            where('stat_date <=', type_to_str($range['to'], 'databasedate'))->
            group_by('id_campaign')->
            get();
         foreach ($res->result() as $row) {
            $campaigns[$row->id_campaign]['spent'] = $row->spent;   
            $campaigns[$row->id_campaign]['impressions'] = $row->impressions;   
            $campaigns[$row->id_campaign]['clicks'] = $row->clicks;   
            $campaigns[$row->id_campaign]['ctr'] = $row->ctr;   
         }  
         $res = $this->db->select('id_campaign, gsc.status')->
            join('group_site_channels gsc', 'g.id_group=gsc.id_group')->
            join('site_channels sc', 'gsc.id_site_channel=sc.id_site_channel')->
            join('sites s','sc.id_site=s.id_site')->
            join('channels c','sc.id_channel=c.id_channel')->
            where_in('id_campaign', array_keys($campaigns))->
            where("(gsc.status='unpaid' OR gsc.status='trouble')", "", FALSE)->
            where('c.status','active')->
            where('s.status','active')->
            where('sc.status','active')->
            get('groups g');
         foreach ($res->result() as $row) {
            $campaigns[$row->id_campaign][$row->status] = TRUE;
         }
         // troubled & unpaid status для CPC кампаний
         $res = $this->db->select('id_campaign, gs.status')->
            join('group_sites gs', 'g.id_group=gs.id_group')->
            join('sites s','gs.id_site=s.id_site')->
            where_in('id_campaign', array_keys($campaigns))->
            where("(gs.status='unpaid' OR gs.status='trouble')", "", FALSE)->
            where('s.status','active')->
            get('groups g');
         foreach ($res->result() as $row) {
            $campaigns[$row->id_campaign][$row->status] = TRUE;
         }
      }
             
      return $campaigns;      
   } //end select
      
   /**
    * Получение списка кампаний, упорядоченного по названию кампании
    *
    * @param array $params параметры фильтрации списка кампаний
    */
   public function get_list($params) {
   	$result = array();
   	$this->db->select('id_campaign, name')
   	  ->from('campaigns');
   	if (array_key_exists('id_advertiser',$params)) {
   		$this->db->where('id_entity_advertiser',$params['id_advertiser']);
   	}
   	
      if (!(array_key_exists('show_deleted',$params) && $params['show_deleted'])) {
      	$this->db->where('status <>', 'deleted'); 
      }
   	
      $this->db->order_by('name');
      
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
      	foreach ($query->result() as $row) {
      		$result[$row->id_campaign] = $row->name;
      	}
      }
      return $result;
   }
   
   
   /**
   * возвращает количество кампаний, удовлетворяющих заданнам условиям
   *
   * @param integer $id_entity код учетной записи пользователя
   * @param string $filt фильтр по статусу
   *    * @return integer количество записей
   */   
   public function total($id_entity, $filt, $range) {
      $this->db->select('COUNT(DISTINCT campaigns.id_campaign) AS cnt, '.
         ' COUNT(DISTINCT groups.id_group) AS groups,'.
         ' COUNT(DISTINCT id_ad) AS ads,'.
         ' COUNT(DISTINCT id_group_site_channel)+COUNT(DISTINCT id_group_site) AS site_channels', FALSE);
      $this->db->from('campaigns');
      $this->db->join('groups', 'campaigns.id_campaign = groups.id_campaign AND groups.status!="deleted"', 'LEFT');
      $this->db->join('ads', 'groups.id_group = ads.id_group AND ads.status!="deleted"', 'LEFT');
      $this->db->join('group_site_channels', 'groups.id_group = group_site_channels.id_group AND group_site_channels.status!="deleted"', 'LEFT');
      $this->db->join('site_channels', 'group_site_channels.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT');           
      $this->db->join('sites', 'site_channels.id_site = sites.id_site', 'LEFT');           
      $this->db->join('channels', 'site_channels.id_channel = channels.id_channel', 'LEFT');           
      $this->db->join('group_sites gs', 'groups.id_group = gs.id_group AND gs.status!="deleted"', 'LEFT');
      $this->db->where('id_entity_advertiser', $id_entity);
      if ($filt == 'all') {
         $this->db->where('campaigns.status !=', 'deleted');
      } else {
         $this->db->where('campaigns.status', $filt);
      }
      $this->db->group_by('id_entity_advertiser');
      $res = $this->db->get();
      $campaigns = array('cnt' => 0);
      foreach ($res->result() as $row) {
         $campaigns['cnt'] = $row->cnt;
         $campaigns['groups'] = $row->groups;
         $campaigns['ads'] = $row->ads;
         $campaigns['sites_channels'] = $row->site_channels;
      }    
      if ($filt == 'all') {
         $this->db->where('c.status !=', 'deleted');
      } else {
         $this->db->where('c.status', $filt);
      }
      $res = $this->db
         ->select_sum('spent')
         ->select_sum('clicks')
         ->select_sum('impressions')
         ->select('SUM(clicks)*100/SUM(impressions) AS ctr')
         ->from('stat_campaigns sc')
         ->join('campaigns c', 'sc.id_campaign=c.id_campaign')
         ->where('id_entity_advertiser', $id_entity)
         ->where('stat_date >=', type_to_str($range['from'], 'databasedate'))
         ->where('stat_date <=', type_to_str($range['to'], 'databasedate'))
         ->get();
      $row = $res->row();
      $campaigns['spent'] = $row->spent;
      $campaigns['impressions'] = $row->impressions;
      $campaigns['clicks'] = $row->clicks;
      return $campaigns;      
   } //end total                
   
   /**
   * совершает заданное действие над выбранной кампанией
   *
   * @param string $action действие совершаемое над кампанией ('delete')
   * @param integer $id_entity код учетной записи пользователя
   * @param integer $id_campaign код кампании
   * @return ничего не возвращает
   */   
   public function action($action, $id_entity, $id_campaign) {
      $this->db->where(array('id_entity_advertiser' => $id_entity, 'id_campaign' => $id_campaign));
      switch ($action) {
         case 'delete': 
            $this->db->update('campaigns', array('status' => 'deleted'));
            $res = $this->db->select('id_group')->
               get_where('groups', array('id_campaign' => $id_campaign));
            foreach ($res->result() as $row) {
               $this->db->where(array('id_group' => $row->id_group))->update('groups', array('status' => 'deleted'));
               $this->db->where(array('id_group' => $row->id_group))->update('group_site_channels', array('status' => 'deleted'));
               $this->db->where(array('id_group' => $row->id_group))->update('group_sites', array('status' => 'deleted'));
               $this->db->where('id_group', $row->id_group)->update('ads', array('status' => 'deleted'));               
            }
            break;    
         case 'restore': 
            $this->db->where(array('status' => 'deleted'));
            $this->db->update('campaigns', array('status' => 'active'));
            $res = $this->db->select('id_group')->
               get_where('groups', array('id_campaign' => $id_campaign, 'status' => 'deleted'));
            foreach ($res->result() as $row) {
               $this->db->where(array('id_group' => $row->id_group, 'status' => 'deleted'))->update('groups', array('status' => 'active'));
               $this->db->where(array('id_group' => $row->id_group, 'status' => 'deleted'))->update('group_site_channels', array('status' => 'active'));
               $this->db->where(array('id_group' => $row->id_group, 'status' => 'deleted'))->update('group_sites', array('status' => 'active'));
               $this->db->where(array('id_group' => $row->id_group, 'status' => 'deleted'))->update('ads', array('status' => 'active'));
            }
            break;    
         case 'pause':
            $this->db->where('status', 'active');
            $this->db->update('campaigns', array('status' => 'paused'));
            break;
         case 'resume':
            $this->db->where('status', 'paused');
            $this->db->update('campaigns', array('status' => 'active'));
            break;
      }              
   } //end action     
   
   /**
   * возвращает имя кампании по ее коду
   *
   * @param integer $id_campaign уникальный код кампании
   * @return string имя кампании, NULL - если кампания не найдена
   */
   public function name($id_campaign) {
      $this->db->select('name');
      $res = $this->db->get_where('campaigns', array('id_campaign' => $id_campaign));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->name;   	
      }
      return NULL;
   } //end name
   
   /**
   * возвращает информацию о заданной кампании
   *
   * @param integer $id_campaign уникальный код кампании
   * @return array массив с данными кампании, NULL - кампания не найдена
   */
   public function info($id_campaign) {
      $this->db->select('name, id_campaign_type, targeting_type, unix_timestamp(start_date_time) AS start, unix_timestamp(end_date_time) AS end, id_targeting_group');
      $this->db->limit(1);
      $res = $this->db->get_where('campaigns', array('id_campaign' => $id_campaign));
      if ($res->num_rows()) {
         $row = $res->row();
         return array(
            'name' => $row->name,
            'id_campaign_type' => $row->id_campaign_type,
            'id_targeting_group' => $row->id_targeting_group,
            'targeting_type' => $row->targeting_type
             /*,
            'start' => $row->start,
            'end' => $row->end*/
         );      
      }
      return NULL;
      
   } //end info
   
   public function get_entity($id_campaign) {
      $this->db->select('id_entity_advertiser');
      $res = $this->db->get_where('campaigns', array('id_campaign' => $id_campaign));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->id_entity_advertiser;
      }
      return NULL;
      
   } //end info
   
   /**
   * обновляет данные кампании
   *
   * @param integer $id_campaign уникальный код кампании
   * @param array $fields массив с полями новых данных кампании
   * @return ничего не возвращает 
   */
   public function update($id_campaign, $fields) {
      
      $update_array = array();
      
      if(array_key_exists('targeting_type',$fields)) {
         $update_array['targeting_type'] = "{$fields['targeting_type']}";
      }
      if(array_key_exists('campaign_name',$fields)) {
         $update_array['name'] = $fields['campaign_name'];
      }
      if(array_key_exists('id_targeting_group',$fields)) {
         $update_array['id_targeting_group'] = $fields['id_targeting_group'];
      }
      
      //print_r($fields);
      if(count($update_array) > 0) {
	      $this->db->update('campaigns', $update_array, array('id_campaign' => $id_campaign));
      }
            	
   } //end update      
   
   /**
   * устанавливает список стран связанных с выбранной кампанией
   *
   * @param integer $id_campaign уникальный код кампании
   * @param array $countries массив со списком стран выбранных для этой кампании
   * @return ничего не возвращает 
   */
   public function set_countries($id_campaign, $countries) {
      $this->db->delete('campaign_countries', array('id_campaign' => $id_campaign));
      foreach ($countries as $id => $info) {
      	$this->db->insert('campaign_countries', 
      	   array('id_campaign' => $id_campaign, 'country' => $id));
      }         	
   } //end set_countries
   
   /**
   * устанавливает список стран связанных с выбранной кампанией
   *
   * @param integer $id_campaign уникальный код кампании
   * @param array $countries массив со списком идентификаторов стран выбранных для этой кампании
   * @return ничего не возвращает 
   */
   public function set_countries_by_id($id_campaign, $countries) {
      $this->db->delete('campaign_countries', array('id_campaign' => $id_campaign));
      foreach ($countries as $id) {
         $this->db->insert('campaign_countries', 
            array('id_campaign' => $id_campaign, 'country' => $id));
      }           
   } //end set_countries_by_id
   
   /**
   * устанавливает список языков связанных с выбранной кампанией
   *
   * @param integer $id_campaign уникальный код кампании
   * @param array $languages массив со списком языков выбранных для этой кампании
   * @return ничего не возвращает 
   */
   public function set_languages($id_campaign, $languages) {
      $this->db->delete('campaign_languages', array('id_campaign' => $id_campaign));
      foreach ($languages as $id => $info) {
         $this->db->insert('campaign_languages', 
            array('id_campaign' => $id_campaign, 'language' => $id));
      }           
   } //end set_languages
   
   /**
   * устанавливает список языков связанных с выбранной кампанией
   *
   * @param integer $id_campaign уникальный код кампании
   * @param array $languages массив со списком идентификаторов языков выбранных для этой кампании
   * @return ничего не возвращает 
   */
   public function set_languages_by_id($id_campaign, $languages) {
      $this->db->delete('campaign_languages', array('id_campaign' => $id_campaign));
      foreach ($languages as $id) {
         $this->db->insert('campaign_languages', 
            array('id_campaign' => $id_campaign, 'language' => $id));
      }           
   } //end set_languages_by_id
   
   /**
   * возвращает список стран для выбранной кампании
   *
   * @param integer $id_campaign уникальный код кампании
   * @return array список стран, установленный для выбранной кампании
   */
   public function countries($id_campaign) {
   	$this->db->select('iso, name, unicode_name');
   	$this->db->from('campaign_countries');
   	$this->db->join('countries', 'campaign_countries.country=countries.iso');
   	$this->db->where('id_campaign',$id_campaign);
   	$this->db->order_by('name');
   	$res = $this->db->get();
   	$list = array();
   	foreach ($res->result() as $row) {
   		$list[$row->iso] = $row->name.' ('.$row->unicode_name.')'; 
   	}
   	return $list;   	   	   
   } //end countries   
   
   /**
   * возвращает список языков для выбранной кампании
   *
   * @param integer $id_campaign уникальный код кампании
   * @return array список языков, установленный для выбранной кампании
   */
   public function languages($id_campaign) {
      $this->db->select('iso, name, unicode_name');
      $this->db->from('campaign_languages');
      $this->db->join('languages', 'campaign_languages.language=languages.iso');
      $this->db->where('id_campaign',$id_campaign);
      $this->db->order_by('name');
      $res = $this->db->get();
      $list = array();
      foreach ($res->result() as $row) {
         $list[$row->iso] = $row->name; //.' ('.$row->unicode_name.')'; 
      }
      return $list;              
   } //end languages   
   
   /**
   * устанавливает график для выбранной кампании
   *
   * @param integer $id_campaign уникальный код выбранной кампании
   * @param array $id_schedule код недельного графика, NULL - если полный график
   * @return ничего не возвращает
   */
   public function set_schedule($id_campaign, $id_schedule) {
   	$this->db->where('id_campaign', $id_campaign);
   	$this->db->update('campaigns', array('id_schedule' => $id_schedule));
   } //end set_schedule

   /**
   * возвращает код графика для выбранной кампании
   *
   * @param integer $id_campaign уникальный код выбранной кампании
   * @param type2 name2 comm2
   * @param type3 name3 comm3
   * @param type4 nam comm4
   * @return integer код графика для выбранной кампании, NULL - если полный график или кампания не найдена
   */
   public function schedule($id_campaign) {
   	$this->db->select('id_schedule');
   	$res = $this->db->get_where('campaigns', array('id_campaign' => $id_campaign));
   	if ($res->num_rows()) {
   	   $row = $res->row();
   	   return $row->id_schedule;
   	}
   	return NULL;
   } //end schedule
         
   /**
   * Добавление новой кампании
   *
   * @param integer $id_campaign уникальный код выбранной кампании
   * @param array $params параметры создаваемой кампании
   * @return id созданной кампании
   */
   public function create($params) {
      $this->db->insert('campaigns', 
         array('name' => $params['name'], 'id_entity_advertiser' => $params['id_entity_advertiser'], 'id_campaign_type' => $params['id_campaign_type']));
      return $this->db->insert_id();
   } //end set_schedule

   /**
   * проверяет, имеются ли неоплаченные кампании?
   *
   * @param integer $id_entity уникальный код пользователя
   * @return bool TRUE - если имеются неоплаченные кампании
   */
   public function have_unpaid($id_entity) {
      $res = $this->db->get_where(
         'campaigns', 
         array('id_entity_advertiser' => $id_entity, 'status' => 'unpaid'));
   	return $res->num_rows()>0;
   } //end have_unpaid

   /**
    * возвращает тип кампании для заданной кампании
    *
    * @param integer $id_group код кампании
    * @return string nbg кампании
    */
   public function get_type($id_campign) {
        $res = $this->db
           ->select('id_campaign_type')
          ->get_where('campaigns', array('id_campaign' => $id_campign));
        if ($res->num_rows()) {
          $row = $res->row();
          return $row->id_campaign_type;
        }
        return NULL;
   } //enc get_type

} //end class Campaigns

?>