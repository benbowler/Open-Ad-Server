<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с сайтами и каналами
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Sites_channels extends CI_Model {
   
   const SUCCESS = 1;
   const UNPAID_SUCCESS = 2;
   const COST_DATA_MISMATCH = 3;
   const NO_SLOTS = 4;
   
   const UNKNOWN_PROGRAM = -1;
   const UNKNOWN_ADVERTISER = -2;
   const UNKNOWN_PUBLISHER = -3;
   const TOO_LOW_BALANCE = -4;
   const TOO_LOW_PROGRAM_COST = -5;
   const UNKNOWN_AD_TYPE = -6;
   
   
   /** 
   * конструктор класса, инициализация базового класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::__construct();
   } //end Sites_channels
   
   /**
   * возвращает список сайтов/каналов для заданной группы объявлений
   *
   * @param integer $id_entity уникальный код выбранного рекламодателя
   * @param integer $id_group код выбранной группы объявлений
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @return array массив с данными кампаний (id => (title, type, status, date)) 
   */   
   public function select($id_entity, $id_group, $page, $per_page, $sort_field, $sort_direction, $filt, $range) {
      $this->db->select('
      	 site_channels.id_site_channel, 
      	 sites.name AS site_name, 
      	 url, 
      	 channels.name AS name,
      	 IF(sites.status="active" AND channels.status="active" AND site_channels.status="active", gsc.status, "non-active") AS tstatus,
      	 gsc.ad_type, 
      	 SUM(sac.spent) AS spent, 
      	 SUM(sac.impressions) AS impressions,
      	 SUM(sac.clicks) AS clicks, 
      	 SUM(sac.clicks)*100/SUM(sac.impressions) AS ctr,
      	 program_type, 
      	 gsc.cost_text, 
      	 gsc.cost_image, 
      	 gsc.volume,
      	 d.width, 
      	 d.height, 
      	 d.id_dimension, 
      	 channels.ad_type AS cat, 
      	 id_group_site_channel', 
         FALSE);
      $this->db->from('group_site_channels gsc')->
         join('site_channels', 'gsc.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT')->
         join('channels', 'site_channels.id_channel = channels.id_channel', 'LEFT')->
         join('sites', 'site_channels.id_site = sites.id_site', 'LEFT');
      $this->db->join('dimensions d', 'channels.id_dimension = d.id_dimension');                   
      $this->db->join('channel_program_types', 'gsc.id_program = channel_program_types.id_program');
      $this->db->join(
         'stat_advertiser_channels sac', 'channels.id_channel = sac.id_channel'.      
         ' AND sites.id_site = sac.id_site'.
         ' AND gsc.id_group = sac.id_group'.
         ' AND (stat_date BETWEEN "'.type_to_str($range['from'], 'databasedate').
         '" AND "'.type_to_str($range['to'],'databasedate').'")', 'LEFT');
      $this->db->where('site_channels.id_site_channel IS NOT NULL', '', FALSE);            
      $this->db->where('gsc.id_group', $id_group);
      $this->db->where("(id_entity_advertiser=$id_entity OR id_entity_advertiser IS NULL)", '', FALSE);
      $this->db->where('gsc.status !=','deleted');
      switch($filt) {
         case 'active':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="active")', '', FALSE);
            break;
         case 'unpaid':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="unpaid")', '', FALSE);
            break;
         case 'trouble':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="trouble")', '', FALSE);
            break;
         case 'non-active':
            $this->db->where('!(sites.status="active" AND channels.status="active" AND site_channels.status="active")', '', FALSE);
            break;
         case 'completed':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="complete")', '', FALSE);
            break;
         case 'paused':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="paused")', '', FALSE);
            break;
      }
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($per_page, ($page-1)*$per_page);
      $this->db->group_by('site_channels.id_site_channel'); //'channels.id_channel, sites.id_site');
      $res = $this->db->get();
      $channels = array();
      foreach ($res->result() as $row) {
         $channels[$row->id_group_site_channel]['id_site_channel'] = $row->site_name;
         $channels[$row->id_group_site_channel]['site_name'] = $row->site_name;
         $channels[$row->id_group_site_channel]['url'] = $row->url;
         $channels[$row->id_group_site_channel]['name'] = $row->name;
         $channels[$row->id_group_site_channel]['status'] = $row->tstatus; //($row->tstatus == 1) ? 'active' : 'non-active';
         $channels[$row->id_group_site_channel]['format'] = $row->ad_type;
         $channels[$row->id_group_site_channel]['spent'] = $row->spent;
         $channels[$row->id_group_site_channel]['impressions'] = $row->impressions;
         $channels[$row->id_group_site_channel]['clicks'] = $row->clicks;
         $channels[$row->id_group_site_channel]['ctr'] = $row->ctr;
         $channels[$row->id_group_site_channel]['type'] = $row->program_type;
         $channels[$row->id_group_site_channel]['cost_text'] = $row->cost_text;
         $channels[$row->id_group_site_channel]['cost_image'] = $row->cost_image;
         $channels[$row->id_group_site_channel]['volume'] = $row->volume;
         $channels[$row->id_group_site_channel]['width'] = $row->width;
         $channels[$row->id_group_site_channel]['height'] = $row->height;
         $channels[$row->id_group_site_channel]['id_dimension'] = $row->id_dimension;
         $channels[$row->id_group_site_channel]['ch_format'] = $row->cat;
      }    
      return $channels;      
   } //end select
      
   /**
   * возвращает количество каналов в заданной группе
   *
   * @param integer $id_group код учетной записи пользователя
   * @param string $filt фильтр по статусу
   * @return integer количество записей
   */   
   public function total($id_group, $filt, $range) {
      $this->db->select('
      	 SUM(sac.spent) AS spent, 
      	 SUM(sac.impressions) AS impressions,
      	 SUM(sac.clicks) AS clicks, 
      	 COUNT(DISTINCT id_group_site_channel) AS cnt,', 
      	 FALSE
      );
      $this->db->from('group_site_channels gsc')->
         join('site_channels', 'gsc.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT')->
         join('channels', 'site_channels.id_channel = channels.id_channel', 'LEFT')->
         join('sites', 'site_channels.id_site = sites.id_site', 'LEFT');
      $this->db->join(
         'stat_advertiser_channels sac', 'channels.id_channel = sac.id_channel'.      
         ' AND sites.id_site = sac.id_site'.
         ' AND gsc.id_group = sac.id_group'.
         ' AND (stat_date BETWEEN "'.type_to_str($range['from'], 'databasedate').
         '" AND "'.type_to_str($range['to'],'databasedate').'")', 'LEFT');
      $this->db->where('site_channels.id_site_channel IS NOT NULL', '', FALSE);            
      $this->db->where('gsc.id_group', $id_group);
      $this->db->where('gsc.status !=','deleted');
      switch($filt) {
         case 'active':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="active")', '', FALSE);
            break;
         case 'unpaid':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="unpaid")', '', FALSE);
            break;
         case 'trouble':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="trouble")', '', FALSE);
            break;
         case 'non-active':
            $this->db->where('!(sites.status="active" AND channels.status="active" AND site_channels.status="active")', '', FALSE);
            break;
         case 'completed':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="complete")', '', FALSE);
            break;
         case 'paused':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="paused")', '', FALSE);
            break;
      }
      $res = $this->db->get();
      if ($res->num_rows()) {
      $row = $res->row();
         $channels['cnt'] = $row->cnt;
         $channels['spent'] = $row->spent;
         $channels['impressions'] = $row->impressions;
         $channels['clicks'] = $row->clicks;
         return $channels;
      }
      return array('cnt' => 0);          
   } //end total         
/*   public function total($id_group, $filt) {
      $this->db->select('COUNT(DISTINCT site_channels.id_site_channel) AS numrows');
      $this->db->from('group_site_channels gsc')->
         join('site_channels', 'gsc.id_site_channel = site_channels.id_site_channel AND site_channels.status!="deleted"', 'LEFT')->
         join('channels', 'site_channels.id_channel = channels.id_channel', 'LEFT')->
         join('sites', 'site_channels.id_site = sites.id_site', 'LEFT');
      $this->db->where('id_group', $id_group);
      $this->db->where('gsc.status !=','deleted');
      switch($filt) {
         case 'unpaid':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="unpaid")', '', FALSE);
            break;
         case 'trouble':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="trouble")', '', FALSE);
            break;
         case 'active':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="active")', '', FALSE);
            break;
         case 'non-active':
            $this->db->where('!(sites.status="active" AND channels.status="active" AND site_channels.status="active")', '', FALSE);
            break;
         case 'completed':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="complete")', '', FALSE);
            break;
         case 'paused':
            $this->db->where('(sites.status="active" AND channels.status="active" AND site_channels.status="active" AND gsc.status="paused")', '', FALSE);
            break;
      }      
      $res = $this->db->get();
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->numrows;            
      }
      return 0;
   } //end total         
*/
   /**
   * совершает заданное действие над выбранным каналом группы
   *
   * @param string $action действие совершаемое над каналом ('delete')
   * @param integer $id_group_site_channel уникальный код выбранного пакета объявлений объявлений
   * @return ничего не возвращает
   */   
   public function action($action, $id_group_site_channel) {
      $this->db->where('id_group_site_channel', $id_group_site_channel);
      switch ($action) {
         case 'delete': 
            $this->db->update('group_site_channels', array('status' => 'deleted'));
            break;
         case 'pause':
            $this->db->where('status', 'active');
            $this->db->update('group_site_channels', array('status' => 'paused'));
            break;    
         case 'resume':
            $this->db->where('status', 'paused');
            $this->db->update('group_site_channels', array('status' => 'active'));
            break;    
      }              
   } //end action     

   /**
   * получаем код паблишера владельца связки сайт/канал
   *
   * @param integer $id_sitechannel уникальный код связки сайт/канал
   * @return integer уникальный код паблишера, NULL - связка не найдена
   */
   public function get_publisher_id($id_sitechannel) {
      $query = $this->db->select('e.id_entity')
         ->from('entities e')
         ->join('sites s', 'e.id_entity = s.id_entity_publisher')      
         ->join('site_channels sc', 's.id_site = sc.id_site')
         ->where('id_site_channel', $id_sitechannel)
         ->limit(1)->
         get();
      if (0 < $query->num_rows()) {
         $row = $query->row();
         return $row->id_entity;
      }
      return NULL;
   } //end get_publisher_id      


   /**
   * возвращает статистическую информацию по выбранной группе/сайту/каналу
   *
   * @param integer $id_group_site_channel уникальный код для идентефикации группы/сайта/канала
   * @return array массив со статистикой, NULL - пакет объявлений не найден
   */
   public function get_info($id_group_site_channel) {
      $res = $this->db->select('clicks, current_impressions, gsc.volume, spent, DATEDIFF(NOW(),'.
         ' start_date_time ) AS days, UNIX_TIMESTAMP(start_date_time) AS start_date_time, ad_type,'.
         ' gsc.cost_text, gsc.cost_image, program_type')->
         from('group_site_channels gsc')->
         join('channel_program_types cpt', 'gsc.id_program=cpt.id_program')->
         where('id_group_site_channel', $id_group_site_channel)->
         get();
      if($res->num_rows()){
         $info = $res->row();
         switch ($info->ad_type == 'text') {
            case 'text' : $cost = $info->cost_text; break;
            default: $cost = $info->cost_image;
         }
         $info->cost = $cost;
         switch ($info->program_type) {
            case 'Flat_Rate':
               $days = $info->days;
               if ($days>$info->volume) $days=$info->volume;  
               $info->used = ($cost*($days/$info->volume)); break;
            case 'CPM': 
               $info->used = ($cost*($info->clicks/$info->volume)); break;
         }
         return $info;
      }
      return NULL;
   } //end get_info
   
   /**
   * возвращает данные рекламодателя для заданного пакета объявлений
   *
   * @param integer $id_group_site_channel уникальный код пакета объявлений
   * @param type2 name2 comm2
   * @param type3 name3 comm3
   * @param type4 name comm4
   * @return array данные рекламодателя (id_entity, name, balance, e_mail), NULL - пакет или рекламодатель не найден
   */
   public function get_advertiser_info($id_group_site_channel) {
      $id_advertiser = 0;
      $balance = 0;
      $query = $this->db->select('e.id_entity, e.ballance, e.name, e.e_mail, e.bonus')
         ->from('entities e')
         ->join('campaigns c', 'e.id_entity = c.id_entity_advertiser')      
         ->join('groups g', 'c.id_campaign = g.id_campaign')
         ->join('group_site_channels gsc', 'g.id_group=gsc.id_group')
         ->where('id_group_site_channel', $id_group_site_channel)
         ->get();
      if (0 < $query->num_rows()) {
         return $query->row_array();
      } else {
         // Неизвестный рекламодатель
         return NULL;
      }      
   } //end get_advertiser_info         
   
   	/**
     * Продление купленной программы
     *
     * @param integer $id_group_site_channel идентификатор группы
     * @param bool $check_complete делать ли проверку на конченность программы
     * @return int код ошибки
     */
   	public function renew($id_group_site_channel, $check_complete = true, $new_ad_type = '', $new_id_program = 0, $make_unpaid = FALSE) {  
      
      	// Получаем айдишники программы, сайта, группы, компании, а также тип и код сайта/канала   
      	$id_program = 0;
      	$ad_type = '';
      	$this->db->select('g.id_group,g.id_campaign,sc.id_site,sc.id_channel,gsc.id_program,gsc.ad_type,gsc.id_site_channel')
         	->from('group_site_channels gsc')
            ->join('groups g','gsc.id_group=g.id_group')
            ->join('site_channels sc','sc.id_site_channel=gsc.id_site_channel')
         	->where('id_group_site_channel', $id_group_site_channel);
      	if ($check_complete) {
         	$this->db->where('status', 'completed');
      	}
      	$this->db->limit(1);
 	
      	$query = $this->db->get();
               
      	if (0 < $query->num_rows()) {
         	$row = $query->row();
         	$id_program = $row->id_program;
         	$ad_type = $row->ad_type;
            $id_site_channel = $row->id_site_channel;
            $id_group = $row->id_group;
            $id_campaign = $row->id_campaign;
            $id_site = $row->id_site;
            $id_channel = $row->id_channel;
      	} else {
         	// Неизвестная программа
         	return self::UNKNOWN_PROGRAM;
      	}
         
      	if (empty($new_ad_type)) {
         	$new_ad_type = $ad_type;
      	}

      	$old_program = ($new_id_program == 0);
      	if ($old_program) {
         	$new_id_program = $id_program;
      	}
      
      	// Получаем стоимость программы для купленного пакета и тип
      	$cost = 0;
      	$program_type = '';
      	$volume = 0;
      	$cost_text = 0;
      	$cost_image = 0;
      	$avg_cost_text = 0;
      	$avg_cost_image = 0;
      	
      	$this->db->select('program_type, cost_text, cost_image, volume, avg_cost_text, avg_cost_image')
         	->from('channel_program_types')
         	->where('id_program', $new_id_program)
         	->limit(1);
         	
        $adTypes = explode(',', $new_ad_type);
        
      	$query = $this->db->get();
      	if (0 < $query->num_rows()) {
         	$row = $query->row();
         	$program_type = $row->program_type;
         	$volume = $row->volume;
         	$cost_text = $row->cost_text;
         	$cost_image = $row->cost_image;
         	$avg_cost_text = $row->avg_cost_text;	
         	$avg_cost_image = $row->avg_cost_image;
         	
         	if (in_array('image', $adTypes)) {
         		$cost = $row->cost_image; 
         	} else {
         		$cost = $row->cost_text;
         	}
      	} else {
         	// Неизвестная программа
         	return self::UNKNOWN_PROGRAM;
      	}
      
      	if (in_array('image', $adTypes)) {
      		$cost_field = 'cost_image';
      	} else if (in_array('text', $adTypes)){
      		$cost_field = 'cost_text';
      	} else {
      		return self::UNKNOWN_AD_TYPE;
      	}
      
      	if ($old_program) {
         	//Проверка совпадения цены и объема оплачиваемой программы с прайсом в канале 
         	$query = $this->db->select($cost_field.' as cost, volume')
         		->from('group_site_channels')
         		->where('id_group_site_channel', $id_group_site_channel)
         		->get();
         
         	if (0 < $query->num_rows()) {
         		$row = $query->row();
         		if (($row->cost != $cost) || ($row->volume != $volume)) {
         			//Установка статуса trouble
         			$this->db->where('id_group_site_channel', $id_group_site_channel)
         		    	->update('group_site_channels',array('status' => 'trouble'));
         			return self::COST_DATA_MISMATCH;
         		}
         	} else {
         		// Неизвестная программа
            	return self::UNKNOWN_PROGRAM;
         	}
      	}
      	
      	//Для Flat Rate проверка доступности по количеству слотов
      	if ('Flat_Rate' == $program_type) {
      		$slots_info = $this->get_slot_info($id_site_channel);
 
      		if ((0 == $slots_info['free']) || //нет свободных
      	    	(($slots_info['free'] != $slots_info['max']) && (in_array('image', $adTypes)))) 
      	    { //для картинок должны быть свободны все слоты
      			//Установка статуса trouble
            	$this->db->where('id_group_site_channel', $id_group_site_channel)
                	->update('group_site_channels',array('status' => 'trouble'));
            	return self::NO_SLOTS;
      		}
      	}
      
      	// Получаем данные по рекламодателю
      	$advertiser_info = $this->get_advertiser_info($id_group_site_channel);

      	$id_advertiser = $advertiser_info['id_entity'];
      	$balance = $advertiser_info['ballance'];                  
         $bonus = $advertiser_info['bonus'];

      	// Проверяем баланс
      	$make_payment = TRUE;
         if ($balance + $bonus < $cost) {
         	// Денег на балансе не хватает для renew этой программы
         	if (!$make_unpaid) {
            	return self::TOO_LOW_BALANCE;
         	} else {
            	$make_payment = FALSE;      
         	}
      	} elseif ($cost <= 0) {
        	// Подозрительно низкая стоимость программы
         	return self::TOO_LOW_PROGRAM_COST;
      	}
      
      	if ($make_payment) {      
         	$instance =& get_instance();
         	$instance->load->model('entity', 'entity_obj');
         	// Сначала снимаем
         	$instance->entity_obj->subtract_money($id_advertiser, $cost);
            // Затем платим
            $instance->entity_obj->add_money(1, $cost);


         	$instance->load->model('payment_gateways', 'pg_obj');

         	$instance->pg_obj->money_flow($id_advertiser, 1, $cost, NULL, NULL, 'program', 0, true, '', NULL, NULL);
      	}   
         
      	// для renew completed сохраняем историю
      	if ($check_complete) {
         	//clicks, current_impressions, volume, spent, start_date_time
         	$info = $this->get_info($id_group_site_channel);
         	$this->db->insert('renew_history', 
            	array(
               		'start_date_time' => type_to_str($info->start_date_time, 'databasedatetime'),
               		'id_group_site_channel' => $id_group_site_channel,
               		'impressions' => $info->current_impressions,
               		'clicks' => $info->clicks,
               		'days' => $info->days,
               		'spent' => $info->spent,
               		'used' => $info->used
            	)
            );         
      	}
      
      	// А теперь самое интересное - продление программы
      	$new_status = $make_payment?'active':'unpaid';
      	$db_data = array(
         	'cost_text'       => $cost_text,
         	'cost_image'      => $cost_image,
         	'volume'          => $volume,
         	'impressions'     => $volume,
         	'clicks'          => 0,
         	'current_impressions' => 0,
         	'avg_cost_text'   => $avg_cost_text,
         	'avg_cost_image'  => $avg_cost_image,
         	'ad_type'         => $new_ad_type,
         	'id_program'      => $new_id_program,
         	'status'          => $new_status,
         	'start_date_time' => date('Y-m-d H:i:s', mktime(0, 0, 0)),
         	'spent'           => $cost,
         	'pay_escrow'      => 'false'
      	);
      	if ('Flat_Rate' == $program_type) {
         	//$db_data['start_date_time'] = date('Y-m-d H:i:s', mktime(0, 0, 0));
         	$db_data['end_date_time'] = date('Y-m-d H:i:s', mktime(0, 0, 0) + ($volume * 86400) - 1);
      	} else {
         	$db_data['impressions'] = $volume;
      	}

         $stats = new Sppc_Stats();
         $stat = array(
            'id_group'             => $id_group,
            'id_campaign'          => $id_campaign,
            'id_site'              => $id_site,
            'id_channel'           => $id_channel,
            'id_entity_advertiser' => $id_advertiser,
            'earned_admin'         => $cost,
            'spent'                => $cost
      	);
         $stats->writeStatstoDb(array($stat));
      	$this->db->where('id_group_site_channel', $id_group_site_channel)
         ->update('group_site_channels', $db_data);
      
   		return $make_payment?self::SUCCESS:self::UNPAID_SUCCESS;
   	} //end renew

   /**
   * возвращает имя сайта и имя канала по коду связки сайт/канал
   *
   * @param integer $id_sitechannel уникальный код связки сайт/канал
   * @return array массив с именами (site, channel), NULL - если связка не найдена
   */
   public function get_names($id_sitechannel) {
      $res = $this->db->select('s.name AS site, c.name AS channel, c.ad_type')->
         from('site_channels sc')->
         join('sites s', 'sc.id_site=s.id_site')->
         join('channels c', 'sc.id_channel=c.id_channel')->
         where('id_site_channel', $id_sitechannel)->
         get();
      if ($res->num_rows()) {
         return $res->row_array();         
      }
      return NULL;
   } //end get_names   
   
   /**
   * возвращает модель оплаты и тип рекламы для заданного пакета рекламы
   *
   * @param integer $id_group_site_channel уникальный код пакета
   * @return array массив с нужными атрибутами (model, type), , NULL - если связка или группа не найдена
   */
   	public function get_attributes($id_group_site_channel) {
      	$res = $this->db->select('
      		program_type AS model, ad_type AS type, gsc.id_program, '.
         	'gsc.cost_text, cpt.cost_image, cpt.cost_text AS c_text, gsc.cost_image AS g_image, cpt.volume'
      		)
      		->from('group_site_channels gsc')
      		->join('channel_program_types cpt', 'gsc.id_program=cpt.id_program')
      		->where('id_group_site_channel', $id_group_site_channel)
      		->get();
      	if ($res->num_rows()) {
         	return $res->row_array();         
      	}
      	return NULL;      
   	} //end get_attributes
   
   /**
    * Возвращает информацию о Flat Rate слотах в заданной связке сайт/канал (всего, свободно)
    *
    * @param integer $id_sitechannel уникальный код связки сайт/канал
    * @return array количество слотов (total, free), NULL - не найдена связка сайт/канал
    */
   public function get_slot_info($id_sitechannel) {
      $res=$this->db->select('gsc.ad_type AS type, COUNT(gsc.ad_type) AS count, d.max_ad_slots AS max', FALSE)->
         from('group_site_channels gsc')->
         join('channel_program_types cpt', 'gsc.id_program=cpt.id_program')->
         join('channels c', 'cpt.id_channel=c.id_channel')->
         join('dimensions d', 'c.id_dimension=d.id_dimension')->
         where(
            array(
               'program_type' => 'Flat_Rate', 
               'id_site_channel' => $id_sitechannel,
            ))->
         where_in('gsc.status', array('active', 'paused'))->
         group_by('type')->
         get();         
      if(!$res->num_rows()) {
         $res=$this->db->select('d.max_ad_slots AS max, c.ad_type', FALSE)->
            from('site_channels sc')->
            join('channels c', 'sc.id_channel=c.id_channel')->
            join('dimensions d', 'c.id_dimension=d.id_dimension')->
            where('id_site_channel', $id_sitechannel)->
            get();
         if($res->num_rows()) {
            $row=$res->row();
            return array('max' => $row->max, 'free' => $row->max, 'type' => $row->ad_type);                 
         }                  
         return NULL;
      }
      foreach ($res->result() as $row) {
         $info['max'] = $row->max;
         $info['type'] = $row->type;
         if ($row->type != 'text') {
            $info['free'] = 0; 
            break;
         }
         $info['free'] = $row->max - $row->count;
      }
      return $info;                  
   } //end get_slot_info
   
   /**
   * устанавливает тип объявлений для выбранного пакета объявлений
   *
   * @param integer $id_group уникальный код gfrtnf объявлений
   * @param string $new_ad_type наимнование типа объявление (text, text_and_image)
   * @return ничего не возвращает 
   */
   public function set_ad_type($id_group_site_channel, $new_ad_type) {
   	$this->db->
   	   where('id_group_site_channel', $id_group_site_channel)->
   	   update('group_site_channels', array('ad_type' => $new_ad_type));
   } //end set_ad_type   
   
   /**
   * устанавливает сумму затрат для выбранного пакета объявлений
   *
   * @param integer $id_group уникальный код gfrtnf объявлений
   * @param double $spent затраты на пакет
   * @return ничего не возвращает 
   */   
   public function set_spent($id_group_site_channel, $spent) {
      $this->db->
         where('id_group_site_channel', $id_group_site_channel)->
         update('group_site_channels', array('spent' => $spent));
   } //end set_spent      
   
   /**
   * проверяет, имеются ли связки сайт/канал с заданным статусом
   *
   * @param integer $id_entity уникальный код пользователя
   * @param string $status проверяемый статус
   * @return bool TRUE - если имеются неоплаченные связки
   */
   public function have_status($id_entity, $status) {
      $records = $this->db->from('group_site_channels gsc')->
         join('groups g', 'gsc.id_group=g.id_group')->
         join('campaigns c', 'g.id_campaign=c.id_campaign')->
         where('id_entity_advertiser', $id_entity)->
         where('gsc.status', $status)->
         count_all_results();
      return $records>0;
   } //end have_status
   
   /**
   * автоматически оплачивает сайты/каналы пользователя исходя из имеющихся наличных средств
   *
   * @param integer $id_entity уникальный код учетной записи пользователя
   * @return integer количество оплаченных сайтов/каналов
   */
   public function autopay($id_entity) {
      	$CI =& get_instance();
   		$CI->load->helper('knapsack');
   		$CI->load->model('entity', '', TRUE);
   		$res = $this->db->select('gsc.id_group_site_channel, ad_type, cost_text, cost_image')
   			->from('group_site_channels gsc')
   			->join('groups g', 'gsc.id_group=g.id_group')
   			->join('campaigns c', 'g.id_campaign=c.id_campaign')
   			->where('id_entity_advertiser', $id_entity)
   			->where('gsc.status', 'unpaid')
   			->get();
      	$prg_list = array();
      	$index=1;   	  
   		foreach ($res->result() as $row) {
   			$prg_list[$index]['id_group_site_channel'] = $row->id_group_site_channel;
   			
   			$adTypes = explode(',', $row->ad_type);
   			if (in_array('image', $adTypes)) {
   				$prg_list[$index]['cost'] = $row->cost_image;
   			} else if (in_array('text', $adTypes)) {
   				$prg_list[$index]['cost'] = $row->cost_text;
   			}
         	$index++;
   		}   	   	   	
   	
   		$balance = $CI->entity->ballance($id_entity);
   		$pay_list = knapsack($prg_list, $balance);

   		$payed = 0;
   		foreach ($pay_list as $ind) {
   			$r = $this->renew($prg_list[$ind]['id_group_site_channel'], FALSE);
   			if ($r == self::SUCCESS) {
   		   		$payed++;
   			}
   		}
      	return $payed;   	   	   	   	
   	} //end autopay
   
   /**
   * возвращает общее количество созданных групп для выбронного канала на сайте
   *
   * @param integer $id_site уникальный код сайта
   * @param integer $id_channel уникальный код канала 
   * @param ansistring $status фильтр по статусу
   * @return integer количество найденых записей   
   */
   public function groups_total($id_site, $id_channel, $status = 'all') {
   	$this->db->
   	   select('COUNT(id_group_site_channel) AS cnt, SUM(DATEDIFF(NOW(), gsc.start_date_time)) AS days,'.
   	      ' SUM(gsc.clicks) AS clicks, SUM(current_impressions) AS current_impressions', FALSE)->
   	   from('site_channels sc')->
   	   join('group_site_channels gsc', 'sc.id_site_channel=gsc.id_site_channel')->
   	   where('id_site', $id_site)->
   	   where('id_channel', $id_channel)->
   	   group_by('id_site');
   	if ($status != 'all') {
   	   $this->db->where('gsc.status', $status);
   	}
   	$res = $this->db->get();
   	if ($res->num_rows()){
   	   $row = $res->row();
   	   return array(
   	     'cnt' => $row->cnt,
   	     'clicks' => $row->clicks,
   	     'days' => $row->days,
   	     'current_impressions' => $row->current_impressions
   	   );
   	}
   	return NULL;
   } //end groups_total   

   /**
   * возвращает общее количество созданных групп для выбронного канала на сайте
   *
   * @param integer $id_site уникальный код сайта
   * @param integer $id_channel уникальный код канала 
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param ansistring $status фильтр по статусу
   * @return integer количество найденых записей   
   */
   public function groups_select($id_site, $id_channel, $page, $per_page, $sort_field, $sort_direction, $status = 'all') {
      $this->db->
         select('gsc.ad_type, cpt.program_type, gsc.cost_text, gsc.cost_image, gsc.volume, gsc.end_date_time,'.
            ' cpt.cost_text AS ch_cost_text, cpt.cost_image AS ch_cost_image, cpt.volume ch_volume, id_entity,'.
            ' current_impressions, DATEDIFF(NOW(), gsc.start_date_time ) AS days, DATEDIFF(gsc.end_date_time, NOW()) AS days_left,'.
            ' gsc.clicks, gsc.status, e_mail, g.id_group, 100 * ( gsc.clicks / current_impressions ) AS ctr, gsc.id_group_site_channel,'.
            ' COUNT(DISTINCT rh.id_group_site_channel ) AS cnt, e.name')->
         from('site_channels sc')->
         join('group_site_channels gsc', 'sc.id_site_channel=gsc.id_site_channel')->
         join('channel_program_types cpt', 'gsc.id_program=cpt.id_program', 'LEFT')->
         join('groups g', 'gsc.id_group=g.id_group')->
         join('campaigns c', 'g.id_campaign=c.id_campaign')->
         join('entities e', 'c.id_entity_advertiser=e.id_entity')->
         join('renew_history rh', 'gsc.id_group_site_channel=rh.id_group_site_channel', 'LEFT')->         
         where('id_site', $id_site)->
         where('sc.id_channel', $id_channel)->
         order_by($sort_field, $sort_direction)->
         group_by('gsc.id_group_site_channel')->
         limit($per_page, ($page-1)*$per_page);
      if ($status != 'all') {
         $this->db->where('gsc.status', $status);
      }
      $res=$this->db->get();
      $list = array();
      $list_id = array();
      foreach ($res->result() as $row) {
      	$list_id[] = $row->id_group_site_channel;
      	$list[$row->id_group_site_channel]['ad_type'] = $row->ad_type;
         $list[$row->id_group_site_channel]['program_type'] = $row->program_type;
         $list[$row->id_group_site_channel]['cost_text'] = $row->cost_text;
         $list[$row->id_group_site_channel]['cost_image'] = $row->cost_image;
         $list[$row->id_group_site_channel]['volume'] = $row->volume;
         $list[$row->id_group_site_channel]['ch_cost_text'] = $row->ch_cost_text;
         $list[$row->id_group_site_channel]['ch_cost_image'] = $row->ch_cost_image;
         $list[$row->id_group_site_channel]['ch_volume'] = $row->ch_volume;
         $list[$row->id_group_site_channel]['id_entity'] = $row->id_entity;
         $list[$row->id_group_site_channel]['current_impressions'] = $row->current_impressions;
         $list[$row->id_group_site_channel]['days'] = $row->days;
         $list[$row->id_group_site_channel]['clicks'] = $row->clicks;
         $list[$row->id_group_site_channel]['status'] = $row->status;
         $list[$row->id_group_site_channel]['e_mail'] = $row->e_mail;
         $list[$row->id_group_site_channel]['id_group'] = $row->id_group;
         $list[$row->id_group_site_channel]['ctr'] = $row->ctr;
         $list[$row->id_group_site_channel]['cnt'] = $row->cnt;
         $list[$row->id_group_site_channel]['name'] = $row->name;
         $list[$row->id_group_site_channel]['text_ads'] = 0;
         $list[$row->id_group_site_channel]['image_ads'] = 0;
         $list[$row->id_group_site_channel]['days_left'] = $row->days_left;         
      }
      if (count($list_id)) {
         $res = $this->db->select("id_group_site_channel, name, COUNT(id_ad) AS cnt")->
            from('ads a')->
            join('group_site_channels gsc', 'gsc.id_group=a.id_group')->
            join('ad_types at', 'a.id_ad_type=at.id_ad_type')->
            where_in('id_group_site_channel', $list_id)->
            group_by('id_group_site_channel')->
            group_by('name')->
            get();
         foreach ($res->result() as $row) {
         	$list[$row->id_group_site_channel][$row->name.'_ads'] = $row->cnt;
         }
      }            
      return $list;
   } //end groups_select   

   /**
   * возвращает нужное поле для заданного рекламного пакета
   *
   * @param integer $id_group_site_channel код рекламного пакета
   * @param string $field имя нужного поля
   * @return variable значение поля, NULL - пакет не найден
   */
   public function get_packet_field($id_group_site_channel, $field) {
      $res=$this->db->select($field)->
         where('id_group_site_channel', $id_group_site_channel)->
         get('group_site_channels');
      if ($res->num_rows()) {
         $row=$res->row();
         return $row->$field;   	
      }
      return NULL;
   } //end get_id_group
   
   /**
   * возвращает код группы для заданного рекламного пакета
   *
   * @param integer $id_group_site_channel код рекламного пакета
   * @return integer код группы содержащей пакет, NULL - пакет не найден
   */
   public function get_id_group($id_group_site_channel) {
      return $this->get_packet_field($id_group_site_channel, 'id_group');
   } //end get_id_group
   
   /**
   * возвращает код сайта/канала для заданного рекламного пакета
   *
   * @param integer $id_group_site_channel код рекламного пакета
   * @return integer код сайта/канала в котором находится пакет, NULL - пакет не найден
   */
   public function get_id_sitechannel($id_group_site_channel) {
      return $this->get_packet_field($id_group_site_channel, 'id_site_channel');
   } //end get_id_group   

   /**
   * возвращает код сайта и код канала для заданного рекламного пакета
   *
   * @param integer $id_group_site_channel код рекламного пакета
   * @return array код сайта и канала в котором находится пакет, NULL - пакет не найден
   */
   public function get_site_channel($id_group_site_channel) {
      $res = $this->db->
         select('id_site, id_channel')->
         from('site_channels sc')->
         join('group_site_channels gsc', 'sc.id_site_channel=gsc.id_site_channel')->
         where('id_group_site_channel', $id_group_site_channel)->
         get();
      if ($res->num_rows()) {
         $row = $res->row();
         return array('site' => $row->id_site, 'channel' => $row->id_channel);   
      }
      return NULL;
   } //end get_site_channel
   
   /**
   * возвращает статус заданного рекламного пакета
   *
   * @param integer $id_group_site_channel код рекламного пакета
   * @return integer статус пакета, NULL - пакет не найден
   */
   public function get_status($id_group_site_channel) {
      return $this->get_packet_field($id_group_site_channel, 'status');
   } //end get_status
      
   /**
   * возвращает тип платежной модели для заданного пакета объявлений
   *
   * @param integer $id_group_site_channel код пакета объявлений
   * @return string тип платежной модели, NULL - пакет не найден
   */
   public function get_cost_model($id_group_site_channel) {
      $res = $this->db
         ->select('program_type')
         ->from('group_site_channels gsc')
         ->join('channel_program_types cpt', 'gsc.id_program = cpt.id_program')
         ->where('id_group_site_channel', $id_group_site_channel)
         ->get(); 
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->program_type;        
      }
      return NULL;
   } //end get_cost_model   

   /**
    * возвращает код сайта для выбранного сайта группы
    *
    * @param integer $id_group_site код сайта группы
    * @return integer код сайта
    */
   public function get_site_by_groupsite($id_group_site) {
   	$res = $this->db
   	   ->select('id_site')
   	   ->get_where('group_sites', array('id_group_site' => $id_group_site));
   	if ($res->num_rows()) {
   		return $res->row()->id_site;
   	}
   	return NULL;
   } //end get_site_by_groupsite   
   
   public function get_id_site_channel($id_site, $id_channel) {
   	$query = $this->db->get_where('site_channels',array('id_site' => $id_site, 'id_channel' => $id_channel));
      if ($query->num_rows() > 0) {
      	return $query->row();
      } else {
      	return null;
      } 	
   }

   public function check_group_site_channel($id_site, $id_channel, $id_group) {
      $query = $this->db->get_where('site_channels',array('id_site' => $id_site, 'id_channel' => $id_channel));
      if ($query->num_rows() > 0) {
         $id_site_channel = $query->row()->id_site_channel;
	      $query = $this->db->get_where('group_site_channels',array('id_group' => $id_group, 'id_site_channel' => $id_site_channel));
	      if ($query->num_rows() > 0) {
	         return false;
	      } else {
	         return true;
	      }
      } else {
         return null;
      }  
   }

   public function get_program_info($id_programm) {
   	if (!$id_programm) {
   	   return null;
   	}
      $query = $this->db->get_where('channel_program_types',array('id_program' => $id_programm));
      if ($query->num_rows() == 1) {
         return $query->row();
      } else {
         return null;
      }  
   }
   
} //end class Sites_cahannels

?>