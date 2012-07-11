<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/jQTree/jQTree.php';

/**
* модель для работы с группами объявлений 
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Groups extends CI_Model {
   
   public $EmptyTree = FALSE;
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::__construct();
   } //end Groups

   /**
   * возвращает HTML-код для дерева компаний и групп заданного рекламодателя
   *
   * @param integer id_entity код учетной записи выбранного рекламодателя
   * @param string e_mail адрес электронной почты рекламодателя
   * @param int $id_group группа, которую не стоит показывать
   * @return string HTML-код дерева
   */
   public  function get_html_tree($id_entity, $e_mail, $id_group = 0, $campaign_type_filter = 'all') {
      $top = new jQTreeNode();
      
      $code_entity = type_to_str($id_entity, 'textcode');
      $top->setId('user'.$code_entity);
      $top->setOnClick("show_campaigns('$code_entity')");
      $campaigns = array();   

      switch($campaign_type_filter) {
      	case 'cpm_flatrate':
      		$this->db->where('campaigns.id_campaign_type','cpm_flatrate');
      		$top->setCaption(__('All CPM/Flat Rate Campaigns'));
      	break;
      	default:
      		$top->setCaption(__('All Campaigns'));
      }
      
      $this->db->select('id_group, groups.name AS group_name, campaigns.id_campaign AS id_campaign, campaigns.name AS campaign, id_campaign_type');
   	$this->db->from('campaigns');
   	$this->db->join('groups', 'campaigns.id_campaign = groups.id_campaign AND id_group <> ' . $this->db->escape($id_group) . ' AND groups.status != "deleted"', 'LEFT');
   	$this->db->where(array(
   	   'id_entity_advertiser' => $id_entity,
   	   'campaigns.status !=' => 'deleted'
   	));   	
   	//$this->db->where("(groups.status != 'deleted' OR groups.status is NULL)", '', FALSE);
   	$this->db->order_by('campaign ASC, group_name ASC');
   	$res = $this->db->get();
   	$this->EmptyTree = ($res->num_rows() == 0);   	
   	foreach ($res->result() as $row) {
   	   $code_campaign = type_to_str($row->id_campaign, 'textcode');
   	   $id_campaign_type = $row->id_campaign_type;
         $campaign_node = 'camp'.$code_campaign;
   	   if (!in_array($row->id_campaign, $campaigns)) {
   		   $campaigns[] = $row->id_campaign;
            $$campaign_node = new jQTreeNode();
            $$campaign_node->setCaption($row->campaign);
            $$campaign_node->setId($campaign_node);
            $$campaign_node->setOnClick("show_groups('$code_campaign', '$id_campaign_type')");
            $$campaign_node->setClass($row->id_campaign_type);            
            $top->add($$campaign_node);   		   
   		}
   		if ($row->group_name != '') {
      		$code_group = type_to_str($row->id_group, 'textcode');
            $group_node = 'group'.$code_group;
            $$group_node = new jQTreeNode();
            $$group_node->setCaption($row->group_name);
            $$group_node->setId($group_node);
            $$group_node->setOnClick("show_ads('$code_group', '', '$id_campaign_type')");
            $$campaign_node->add($$group_node);
   		}            
   	}
      $tree = new jQTree();
      $tree->setModel($top);
      $tree->setId('groups_tree');
      return $tree->getTree();
   } //end get_html_tree()   

   /**
   * возвращает список запрошенных групп для заданного пользователя
   *
   * @param integer $id_campaign уникальный код кампании, содержащей группы объявлений
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @return array массив с данными групп (id => (title, type, status, date)) 
   */   
   public function select($id_campaign, $page, $per_page, $sort_field, $sort_direction, $filt, $range) {
      $this->db->select('groups.id_group AS id_group, groups.name AS name,'.
         ' groups.status AS status, COUNT(DISTINCT id_ad) AS ads,'.
         ' COUNT(DISTINCT id_group_site_channel) AS site_channels,'.         
         'IF(groups.frequency_coup IS NULL, false, groups.frequency_coup_current>=groups.frequency_coup) AS blocked', FALSE);
      $this->db->from('groups');
      $this->db->join('ads', 'groups.id_group = ads.id_group AND ads.status!="deleted"', 'LEFT');
      $this->db->join('group_site_channels', 'groups.id_group = group_site_channels.id_group AND group_site_channels.status!="deleted"', 'LEFT');
      $this->db->join('site_channels', 'group_site_channels.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT');
      $this->db->join('sites', 'site_channels.id_site = sites.id_site AND sites.status!="deleted"', 'LEFT');           
      $this->db->join('channels', 'site_channels.id_channel = channels.id_channel AND channels.status!="deleted"', 'LEFT');                 
      $this->db->where('id_campaign', $id_campaign);
      if ($filt == 'all') {
         $this->db->where('groups.status !=', 'deleted');
      } else {
         $this->db->where('groups.status', $filt);
      }
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($per_page, ($page-1)*$per_page);
      $this->db->group_by('id_group');
      $res = $this->db->get();
      $groups = array();
      $id_list = array();
      foreach ($res->result() as $row) {
         $id_list[] = $row->id_group;
         $groups[$row->id_group]['name'] = $row->name;
         if ($row->status == 'active' && $row->blocked) {
            $row->status = 'blocked';
         }
         $groups[$row->id_group]['status'] = $row->status;
         $groups[$row->id_group]['ads'] = $row->ads;
         $groups[$row->id_group]['site_channels'] = $row->site_channels;
         $groups[$row->id_group]['spent'] = 0;
         $groups[$row->id_group]['impressions'] = 0;
         $groups[$row->id_group]['clicks'] = 0;
         $groups[$row->id_group]['ctr'] = 0;
         $groups[$row->id_group]['trouble'] = FALSE;
         $groups[$row->id_group]['unpaid'] = FALSE;       
      }    

      // получение статистики для выбранных групп      
      if (count($id_list)) {
         $res = $this->db->select('id_group')->
            select_sum('spent')->
            select_sum('clicks')->
            select_sum('impressions')->
            select('SUM(clicks)*100/SUM(impressions) AS ctr')->
            from('stat_groups')->
            where_in('id_group', $id_list)->
            where('stat_date >=', type_to_str($range['from'], 'databasedate'))->
            where('stat_date <=', type_to_str($range['to'], 'databasedate'))->
            group_by('id_group')->
            get();
         foreach ($res->result() as $row) {
            $groups[$row->id_group]['spent'] = $row->spent;   
            $groups[$row->id_group]['impressions'] = $row->impressions;   
            $groups[$row->id_group]['clicks'] = $row->clicks;   
            $groups[$row->id_group]['ctr'] = $row->ctr;   
         }                  
         $res = $this->db->select('id_group, gsc.status')->
            join('site_channels sc', 'gsc.id_site_channel=sc.id_site_channel')->
            join('sites s','sc.id_site=s.id_site')->
            join('channels c','sc.id_channel=c.id_channel')->
            where_in('id_group', $id_list)->
            where("(gsc.status='unpaid' OR gsc.status='trouble')", "", FALSE)->
            where('c.status','active')->
            where('s.status','active')->
            where('sc.status','active')->
            get('group_site_channels gsc');
         foreach ($res->result() as $row) {
            $groups[$row->id_group][$row->status] = TRUE;
         }         
      }      
      return $groups;      
   } //end select
   
   /**
   * возвращает количество групп, удовлетворяющих заданнам условиям
   *
   * @param integer $id_campaign уникальный код кампании, содержащей группы объявлений
   * @param string $filt фильтр по статусу
   * @return integer количество записей
   */   
   public function total($id_campaign, $filt, $range) {
      $this->db->select('COUNT(DISTINCT groups.id_group) AS cnt,'.
         ' COUNT(DISTINCT id_ad) AS ads,'.
         ' COUNT(DISTINCT id_group_site_channel) AS site_channels', FALSE);
      $this->db->from('groups');
      $this->db->join('ads', 'groups.id_group = ads.id_group AND ads.status!="deleted"', 'LEFT');
      $this->db->join('group_site_channels', 'groups.id_group = group_site_channels.id_group AND group_site_channels.status!="deleted"', 'LEFT');
      $this->db->join('site_channels', 'group_site_channels.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT');
      $this->db->join('sites', 'site_channels.id_site = sites.id_site AND sites.status!="deleted"', 'LEFT');           
      $this->db->join('channels', 'site_channels.id_channel = channels.id_channel AND channels.status!="deleted"', 'LEFT');                 
      $this->db->where('id_campaign', $id_campaign);
      if ($filt == 'all') {
         $this->db->where('groups.status !=', 'deleted');
      } else {
         $this->db->where('groups.status', $filt);
      }
      $res = $this->db->get();
      $groups = array('cnt' => 0);
      foreach ($res->result() as $row) {
         $groups['cnt'] = $row->cnt;
         $groups['ads'] = $row->ads;
         $groups['site_channels'] = $row->site_channels;
      }    

      if ($filt == 'all') {
         $this->db->where('g.status !=', 'deleted');
      } else {
         $this->db->where('g.status', $filt);
      }
      $res = $this->db
         ->select_sum('spent')
         ->select_sum('clicks')
         ->select_sum('impressions')
         ->from('stat_groups sg')
         ->join('groups g', 'sg.id_group=g.id_group')
         ->where('id_campaign', $id_campaign)
         ->where('stat_date >=', type_to_str($range['from'], 'databasedate'))
         ->where('stat_date <=', type_to_str($range['to'], 'databasedate'))
         ->get();
      $row = $res->row();
      $groups['spent'] = $row->spent;
      $groups['impressions'] = $row->impressions;
      $groups['clicks'] = $row->clicks;
      return $groups;      
   } //end total
   
   /**
   * совершает заданное действие над выбранной группой объявлений
   *
   * @param string $action действие совершаемое над кампанией ('delete', 'pause', 'resume')
   * @param integer $id_campaign уникальный код кампании, содержащей нужную группу
   * @param integer $id_group код выбранной группы объявлений
   * @return ничего не возвращает
   */   
   public function action($action, $id_campaign, $id_group) {
      $this->db->where(array('id_campaign' => $id_campaign, 'id_group' => $id_group));
      switch ($action) {
         case 'delete': 
            $this->db->update('groups', array('status' => 'deleted'));
            $this->db->where('id_group', $id_group)->update('ads', array('status' => 'deleted'));
            $this->db->where('id_group', $id_group)->update('group_site_channels', array('status' => 'deleted'));
            $this->db->where('id_group', $id_group)->update('group_sites', array('status' => 'deleted'));
            break;    
         case 'pause':
            $this->db->where('status', 'active');
            $this->db->update('groups', array('status' => 'paused'));
            break;
         case 'resume':
            $this->db->where('status', 'paused');
            $this->db->update('groups', array('status' => 'active'));
            break;
      }              
   } //end action
         
   /**
   * возвращает суммарную статистику по объявлениям выбранной группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @return array массив со статистикой группы (spent, clicks, impressions, ctr)
   */
   public  function summary($id_group, $range) {
   	$res = $this->db->select_sum('spent')->
         select_sum('clicks')->
         select_sum('impressions')->
         select('COUNT(id_group) AS cnt')->
         from('stat_groups')->
         where('id_group', $id_group)->
         where('stat_date >=', type_to_str($range['from'], 'databasedate'))->
         where('stat_date <=', type_to_str($range['to'], 'databasedate'))->
         get();         
      if ($res->num_rows()) {
         $row = $res->row();
         $summary = array(
            'spent' => $row->spent, 
            'clicks' => $row->clicks, 
            'impressions' => $row->impressions,
            'cnt' => $row->cnt
         );         
      } else {
         $summary = array(
         	'spent' => 0, 
         	'clicks' => 0, 
         	'impressions' => 0, 
         	'cnt' => 0
         );
      } 
      $summary['ctr'] = $summary['impressions'] ? $summary['clicks']*100/$summary['impressions'] : 0;
      
      $this->db->select('COUNT(DISTINCT id_ad) AS ads,'.
         ' COUNT(DISTINCT sc.id_site_channel) AS site_channels,'.
         ' SUM(gsc.status="unpaid" AND s.status="active" AND c.status="active" AND sc.status="active") AS unpaid,'.
         ' SUM(gsc.status="trouble" AND s.status="active" AND c.status="active" AND sc.status="active") AS trouble', FALSE);
      $this->db->from('groups');
      $this->db->join('ads', 'groups.id_group = ads.id_group AND ads.status!="deleted"', 'LEFT');
      $this->db->join('group_site_channels gsc', 'groups.id_group = gsc.id_group AND gsc.status!="deleted"', 'LEFT');
      $this->db->join('site_channels sc', 'gsc.id_site_channel = sc.id_site_channel AND sc.status!="deleted"', 'LEFT');
      $this->db->join('sites s', 'sc.id_site = s.id_site', 'LEFT');
      $this->db->join('channels c', 'sc.id_channel = c.id_channel', 'LEFT');
      $this->db->where('groups.id_group', $id_group);
      $res = $this->db->get();
      $row = $res->row();
      $summary['ads'] = $row->ads;
      $summary['site_channels'] = $row->site_channels;         
      $summary['unpaid'] = $row->unpaid>0;         
      $summary['trouble'] = $row->trouble>0;         
      
      return $summary;
   } //end summary
      
   /**
   * добавляет в выбранную кампанию новую группу объявлений
   *
   * @param integer $id_campaign код кампании, в которую добавляется группа
   * @param string $group имя добавляемой группы
   * @param float $default_bid бид по умолчанию (только для CPC)
   * @param float $default_bid_image бид по умолчанию для изображений(только для CPC)
   * @return integer код добавленной группы 
   */
   public function add($id_campaign, $group, $default_bid=0, $default_bid_image=0) {
   	$this->db->insert('groups', 
   	   array(
   	      'id_campaign' => $id_campaign, 
   	      'name' => $group
   	   )
   	);
   	return $this->db->insert_id();
   } //end add  
   
   /**
   * изменяет имя выбранной группы
   *
   * @param integer $id_group код выбранной группы
   * @param string $group новое имя для выбранной группы
   * @return ничего не возвращает
   */   
   public function rename($id_group, $group) {
      $this->db->where('id_group', $id_group);
      $this->db->update('groups', array('name' => $group));
   } //end rename
   
   /**
   * возвращает имя выбранной группы
   *
   * @param integer $id_group код выбранной группы
   * @return string имя группы, NULL - группа не найдена
   */
   public function name($id_group) {
      $this->db->select('name');
      $res = $this->db->get_where('groups', array('id_group' => $id_group));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->name;
      }
      return NULL;         	
   } //end name

   /**
   * возвращает имя родительской кампании для выбранной группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @return string имя родительской кампании, NULL - группа не найдена
   */
   public function parent_campaign($id_group, $field = 'name') {
      $this->db->select('campaigns.name AS name, campaigns.id_campaign');
      $this->db->join('groups', 'campaigns.id_campaign=groups.id_campaign');
      $res = $this->db->get_where('campaigns', array('id_group' => $id_group));   	
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->$field;
      }
      return NULL;            
   } //end parent_campaign   
   
   /**
   * возвращает ID родительской кампании для выбранной группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @return integer ID родительской кампании, NULL - группа не найдена
   */
   public function id_parent_campaign($id_group) {
      $this->db->select('campaigns.id_campaign AS id');
      $this->db->join('groups', 'campaigns.id_campaign=groups.id_campaign');
      $res = $this->db->get_where('campaigns', array('id_group' => $id_group));     
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->id;
      }
      return NULL;            
   } //end id_parent_campaign()
   
   /**
   * возвращает список каналов для заданной группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @return array массив с каналами группы в формате (id_channel, id_program)
   */
  /* public function get_channels($id_group) {
      $this->db->select('group_channels.id_channel AS id_channel,'.
         ' group_channels.id_program AS id_program, program_type');
      $this->db->join('channel_program_types', 'group_channels.id_program = channel_program_types.id_program');
      $res = $this->db->get_where('group_channels', array('id_group' => $id_group));
      if ($res->num_rows()) {
         return $res->result_array();
      }
      return array();      
   } //end get_channels*/
   
   /**
   * возвращает список сайтов-каналов для заданной группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @return array массив с каналами группы в формате (id_site_channel, id_program)
   */
   public function get_site_channels($id_group, $get_deleted = true) {
   	
      $this->db->select("
      		group_site_channels.id_site_channel, 
      		group_site_channels.id_program AS id_program, 
      		group_site_channels.ad_type, 
				IF (FIND_IN_SET('image', group_site_channels.ad_type), group_site_channels.cost_image,
				 IF (FIND_IN_SET('text', group_site_channels.ad_type), group_site_channels.cost_text, 0)) as cost,
      		group_site_channels.volume",
      		FALSE
      );
      $this->db->from('group_site_channels');
      $this->db->join('channel_program_types', 'group_site_channels.id_program = channel_program_types.id_program','left');
      $this->db->where('id_group', $id_group);
      if (!$get_deleted) {
         $this->db->where('status <>', 'deleted');	
      }
      $res = $this->db->get();
      if ($res->num_rows()) {
         return $res->result_array();
      }
    
      return array();      
   } //end get_channels
   
   /**
   * обновляет список сайтов-каналов для заданной группы (с очисткой ранее добавленных)
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $sites_channels массив с данными каналов группы
   * @return ничего не возвращает 
   */
   public function set_sites_channels($id_group, $sites_channels) {
      $this->db->delete('group_site_channels', array('id_group' => $id_group));
      
      $this->add_sites_channels($id_group, $sites_channels); 
   } //end set_sites_channels
   
   /**
   * обновляет список сайтов для заданной группы (с очисткой ранее добавленных) (CPC кампания)
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $sites массив с данными сайтов группы
   * @param bool $keep_paused_status флаг сохранения у добавленных ранее сайтов статуса 'paused'
   * @return ничего не возвращает 
   */
   public function set_sites($id_group, $sites, $keep_paused_status = true) {
   		$old_paused_sites = array();
   	
   		if ($keep_paused_status) {
   	  		$query = $this->db->select('id_group_site')
   	  			->where(array('id_group' => $id_group, 'status' => 'paused'))
   	  			->get('group_sites');
   	  			
   	  		if ($query->num_rows() > 0) {
   	  	 		foreach ($query->result() as $row) {
   	  	 			$old_paused_sites[] = $row->id_group_site;
   	  	 		}
   	  		}
   		}
   	
   		$this->db->where('id_group', $id_group)
               ->update('group_sites',array('status' => 'deleted'));
      
      	$this->add_sites($id_group, $sites);

      	if (count($old_paused_sites) > 0 ) {
        	$this->db->where_in('id_group_site', $old_paused_sites)
        		->where('status', 'active')
        		->update('group_sites',array('status' => 'paused'));
      	}
   } //end set_sites
   
   	/**
     * Добавляет к списоку сайтов-каналов заданной группы новые сайты-каналы (ранее добавленные не изменяет)
     *
     * @param integer $id_group уникальный код выбранной группы
     * @param array $sites_channels массив с данными каналов группы
     * @return array массив со списком созданных пакетов (id_site_channel => id_group_site_channel)
     */
   	public function add_sites_channels($id_group, $sites_channels) {
   		if (0 > count($sites_channels)) {
   			return;
   		}
   	
   		$old_sites_channels = null;
   	
   		$query = $this->db->select('id_site_channel')
   			->from('group_site_channels')
   			->where('id_group', $id_group)
   			->get();
      
      	if ($query->num_rows() > 0) {
      		$old_sites_channels = array();
      		foreach ($query->result() as $result) {
      			$old_sites_channels[$result->id_site_channel] = null;
      		}      	
      	}
   	
      	$programs_list = array();
      	foreach ($sites_channels as $site_channel) {
         	$programs_list[] = $site_channel['id_program'];	
      	}
      
      	$programs_list = array_values($programs_list);
      
      	//Определение типов программ для рассчета средней стоимости
      	$query = $this->db->select('id_program, program_type')
      		->from('channel_program_types')
      		->where_in('id_program', $programs_list)
      		->get();
      
      	$program_types = array();
      	if (0 < $query->num_rows()) {
      		foreach ($query->result() as $program_info) {
            	$program_types[$program_info->id_program] = $program_info->program_type;      		
      		}
      	}
      
      	$list_id = array();
      	
      	foreach ($sites_channels as $id_site_channel => $site_channel) {
      		if ((is_null($old_sites_channels)) || 
      	    	(!is_null($old_sites_channels) && !array_key_exists($id_site_channel, $old_sites_channels))) 
      		{
      			$adTypes = explode(',', $site_channel['ad_type']);
      			
      			if (in_array('image', $adTypes)) {
      				$cost_field = 'cost_image';
	            	$avg_cost_field = 'avg_cost_image';
      			} else {
      				$cost_field = 'cost_text';
	            	$avg_cost_field = 'avg_cost_text';
      			}
      	   		
      			$avg_cost_value = $site_channel['cost']/$site_channel['volume'];
	         
	         	if ('CPM' == $program_types[$site_channel['id_program']]) {
	         		$avg_cost_value*= 1000; //Для CPM храним цену за 1К показов
	         	}
         
	         	$this->db->insert(
	         		'group_site_channels',
	            	array(
	               		'id_group' => $id_group,
	               		'id_site_channel' => $id_site_channel,
	               		$cost_field => $site_channel['cost'],
                   		$avg_cost_field => $avg_cost_value,
                   		'volume' => $site_channel['volume'],
	               		'ad_type' => $site_channel['ad_type'],
	               		'id_program' => $site_channel['id_program']
	            	)
	            );
            	$list_id[$id_site_channel] = $this->db->insert_id();          	            
      		}
      	}
      	return $list_id;     
   	} //end add_sites_channels
   
   /**
   * Добавляет к списоку сайтов заданной группы новые сайты (CPC кампания)
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $sites массив с данными сайтов группы
   * @return none
   */
   public function add_sites($id_group, $sites) {
      
      if (0 > count($sites)) {
         return;
      }
      
      $old_sites = array();
      
      $query = $this->db->select('id_site')->from('group_sites')->where('id_group', $id_group)->get();
      
      if ($query->num_rows() > 0) {
         $old_sites = array();
         foreach ($query->result() as $result) {
            $old_sites[$result->id_site] = null;
         }        
      }
      
      foreach ($sites as $id_site => $site_params) {
         
         if (!array_key_exists($id_site, $old_sites)) {
         
            $this->db->insert('group_sites',
               array(
                  'id_group' => $id_group,
                  'id_site' => $id_site,
                  'cpc' => $site_params['bid'],
                  'cpc_image' => $site_params['bid_image']
               ));                     
         } else {
         	$this->db->where(array('id_group' => $id_group,
                                   'id_site' => $id_site))
         	->update('group_sites',
               array('cpc' => $site_params['bid'],
                     'cpc_image' => $site_params['bid_image'],
                     'status' => 'active'
               ));
         }
      }    
   } //end add_sites
   
   /**
   * Удаляет из списока сайтов-каналов заданной группы указанные сайты-каналы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $channels массив с ID сайтов-каналов для удаления
   * @return ничего не возвращает 
   */
   public function del_sites_channels($id_group, $sites_channels) {
      
      $this->db->where('id_group', $id_group)->where_in('id_site_channel', $sites_channels);
      $this->db->delete('group_site_channels');
      
   } //end del_sites_channels
   
   /**
   * Удаляет из списока сайтов заданной группы указанные сайты (CPC кампания)
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $sites массив с ID сайтов для удаления
   * @return ничего не возвращает 
   */
   public function del_sites($id_group, $sites) {
      $this->db->where('id_group', $id_group)->where_in('id_sites', $sites);
      $this->db->update('group_sites',array('status' => 'deleted'));
      
   } //end del_sites
      
   /**
   * возвращает максимальное количество показов в день для группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @return integer количество показов, NULL - если ограничение не задано
   */
   public function get_frequency_coup($id_group) {
   	$this->db->select('frequency_coup');
   	$res = $this->db->get_where('groups', array('id_group' => $id_group));
   	if ($res->num_rows()) {
   	   $row = $res->row();
   	   return $row->frequency_coup;
   	}
   	return NULL;
   } //end get_frequency_coup
   
   /**
   * устанавливает максимальное число показов для группы
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param integer $frequecy_coup максимальное число показов
   * @return ничего не возвращает 
   */
   public function set_frequency_coup($id_group, $frequecy_coup) {
      $this->db->where('id_group', $id_group);
      $this->db->update('groups', array('frequency_coup' => $frequecy_coup));
   } //end set_frequency_coup   

   /**
    * возвращает тип кампании для заданной группы
    *
    * @param integer $id_group код группы
    * @return string nbg кампании
    */
   public function group_type($id_group) {
   	  $res = $this->db
   	     ->select('id_campaign_type')
   	  	 ->where('id_group', $id_group)
   	  	 ->from('groups g')
   	  	 ->join('campaigns c', 'g.id_campaign=c.id_campaign')
   	  	 ->get();
   	  if ($res->num_rows()) {
   	  	 $row = $res->row();
   	  	 return $row->id_campaign_type;
   	  }
   	  return NULL;
   } //enc campaign_type

   /**
    * возвращает тип кампании для заданной кампании
    *
    * @param integer $id_group код кампании
    * @return string nbg кампании
    */
   public function campaign_type($id_campign) {
   	  $res = $this->db
   	     ->select('id_campaign_type')
   	  	 ->get_where('campaigns', array('id_campaign' => $id_campign));
   	  if ($res->num_rows()) {
   	  	 $row = $res->row();
   	  	 return $row->id_campaign_type;
   	  }
   	  return NULL;
   } //enc campaign_type   
   

   public function get_group_campaign($id_group) {
      $this->db->select('c.*', false)
         ->from('campaigns c')
         ->join('groups g', 'g.id_campaign = c.id_campaign')
         ->where('g.id_group', $id_group);
      $query = $this->db->get();
      if (0 < $query->num_rows()) {
         return $query->row_array();
      }
      return false;
    }
   
    
   /**
    * Get group categories
    * @param int $id_group
    */
   public function get_categories($id_group) {
      $this->db->select('id_category')
         ->from('group_categories')
         ->where('id_group', $id_group);
      $query = $this->db->get();
      $categories = array();
      if (0 < $query->num_rows()) {
         foreach ($query->result_array() as $row) {
            array_push($categories, $row['id_category']);
         }
      }
      return $categories;
   }
   
    /**
    * change status to complete for end fl groups_site_channels 
    * @param date $date
    */
   public function end_fl_status($date=null) {
      if (is_null($date)){
         $date = date('Y-m-d H:i:s');
      }
      $this->db->where('status','active')
      ->where("end_date_time <", $date);
      $this->db->update('group_site_channels',array('status' => 'completed'));
   }
} //end class Groups

?>