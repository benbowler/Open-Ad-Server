<?php // -*- coding: UTF-8 -*-


if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'models/object_model.php';

/**
* класс для настройки параметров сайта
*
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

class Site extends Object_Model {

	public function __construct() {
		parent::__construct ();
		$this->_table_name = 'sites';
		$this->_id_field_name = 'id_site';
	}

   /**
	 * Получение списка сайтов
	 *
	 * @param array $params массив требуемых параметров сайтов
	 * @return array|null - массив, содержащий список сайтов и их параметры
	 */
	public function get_list($params) {

	   if (isset($params['status'])) {
         switch ($params['status']) {
            case 'all':
               continue;
            break;
            case 'non_deleted':
               $this->db->where ( 'sites.status <>', 'deleted' );
            break;
            default:
               $this->db->where ( 'sites.status', $params['status'] );
            break;
         }

      }

      if(isset($params['owner'])) {
         switch ($params['owner']){
            case 'mysites':
               $this->db->where('sites.id_entity_publisher', $params['id_entity']);
               break;
            case 'pubsites':
               $this->db->where('sites.id_entity_publisher <>', $params['id_entity']);
         }
      } else {      
         if (isset($params['id_entity'])) {
            $this->db->where('sites.id_entity_publisher',$params['id_entity']);
         }
      }

		if (isset($params['fields'])) {
			$this->db->select ( $params['fields'] , FALSE);
		}

		$this->db->from ( $this->_table_name );

	   if (isset($params['site_id_filter'])) {
         $this->db->where_in('sites.id_site', $params['site_id_filter'] );
      }

      if (isset($params['join_tables'])) {
      	if (in_array('group_sites',$params['join_tables'])) {
            $this->db->join(" (SELECT COUNT(*) as comp_count, id_site FROM group_sites WHERE group_sites.status = 'active' GROUP BY id_site) gs",$this->_table_name.".id_site = gs.id_site",'left');
      	}
      }

	  if (isset($params['channel_id_filter'])) {
        $this->db->join('site_channels', 'site_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name, 'left')
	              ->where ( 'site_channels.id_channel', $params['channel_id_filter'] );

	     if (isset($params['date_filter'])) {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
             ' AND stat_sites_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"'.
             ' AND stat_sites_channels.id_channel = '.$params['channel_id_filter'], 'left')
                      ->group_by('stat_sites_channels.id_channel');
          } else {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.id_channel = '.$params['channel_id_filter'], 'left')
                      ->group_by('stat_sites_channels.id_channel');
          }
      }

		if (isset($params['order_by'])) {
		   if (isset($params['order_direction'])) {
		      $this->db->order_by ( $params['order_by'], $params['order_direction'] );
		   } else {
		      $this->db->order_by ( $params['order_by']);
		   }
		}

	   if (isset($params['title_hostname_filter'])) {
	   	 $escapedTitleHostnameFilter = $this->db->escape_str($params['title_hostname_filter']); 
	   	 $titleAndHostanmeFilter = "
	   	 	((sites.name LIKE '%".$escapedTitleHostnameFilter."%')
	   	 	 OR (sites.url LIKE '%".$escapedTitleHostnameFilter."%'))
	   	 ";
         $this->db->where($titleAndHostanmeFilter, null, false);
      }

		if (isset($params['limit']) && isset($params['offset'])) {
		   $this->db->limit ( $params['limit'], $params['offset'] );
		}

		if (!isset($params['channel_id_filter'])) {
		  if (isset($params['date_filter'])) {
		  	  $this->db->join('stat_sites', 'stat_sites.id_site = '.$this->_table_name.'.'.$this->_id_field_name.
		  	  ' AND stat_sites.stat_date >= "'.
	        type_to_str( $params['date_filter'] ['from'] ,'databasedatetime').'" AND stat_sites.stat_date <= "'.
	        type_to_str( $params['date_filter'] ['to'] ,'databasedatetime').'"', 'left');
	     } else {
			   $this->db->join('stat_sites', 'stat_sites.id_site = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
	     }
		}

	   if (isset($params['category_filter'])) {
	   	 $this->db->join('site_categories', 'site_categories.id_site = sites.id_site', 'left');
	   	 $this->db->where_in('site_categories.id_category', $params['category_filter']);
       } else if (isset($params['joinCategories'])) {
       	 $this->db->join('site_categories', 'site_categories.id_site = sites.id_site', 'left');
      }

	   if (isset($params['cpc_bid_filter']) and isset($params['cpc_bid_type'])) {
         switch ($params['cpc_bid_type']) {
            case 'text':
               $where_field = 'min_cpc <=';
            break;
            case 'image':
               $where_field = 'min_cpc_image <=';
            break;
         }
         $this->db->where($where_field, $params['cpc_bid_filter']);
      }

		if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
		  	$escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
		  	$quicksearchFilter = "
		  		((sites.id_site LIKE '%".$escapedQuicksearch."%')
		  		  OR (sites.url LIKE '%".$escapedQuicksearch."%')
		  		  OR (sites.name LIKE '%".$escapedQuicksearch."%'))
	        	";
	       	$this->db->where($quicksearchFilter, null, false);
	      }

		$this->db->group_by($this->_table_name.'.'.$this->_id_field_name);

		$this->db->join('entities e', "sites.id_entity_publisher=e.id_entity");
		$this->db->join('entity_roles er', "e.id_entity=er.id_entity AND er.id_role IN (1, 4)"); /* admin & publisher */
	   $this->db->where('er.status', 'active');
		
		$query = $this->db->get ();
		
       
		if ($query && ($query->num_rows () > 0)) {
			return $query->result_array ();
		} else {
			return null;
		}
	} //and get_list()

	/**
	 * Получение количества сайтов
	 *
	 * @param array $params массив требуемых параметров сайтов
	 * @return int количество сайтов в списке
	 */
	public function get_count($params) {
		$result = array();
      if (isset($params['status'])) {
         switch ($params['status']) {
            case 'all':
               continue;
            break;
            case 'non_deleted':
               $this->db->where ( 'sites.status <>', 'deleted' );
            break;
            default:
               $this->db->where ( 'sites.status', $params['status'] );
            break;
         }
      }

	   if (isset($params['site_id_filter'])) {
        $this->db->where_in( 'sites.id_site', $params['site_id_filter'] );
      }

	   if (isset($params['channel_id_filter'])) {
        $this->db->join('site_channels', 'site_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name, 'left')
                 ->where ( 'site_channels.id_channel', $params['channel_id_filter'] );
      }

	   if (isset($params['category_filter'])) {
         //$this->db->where_in('id_category', $params['category_filter']);
      }

        if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
        	$escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
        	$quicksearchFilter = "
        		((sites.id_site LIKE '%".$escapedQuicksearch."%')
        		  OR (sites.url LIKE '%".$escapedQuicksearch."%')
        		  OR (sites.name LIKE '%".$escapedQuicksearch."%'))
        	";
        	$this->db->where($quicksearchFilter, null, false);
        }
		$this->db->join('entities e', "sites.id_entity_publisher=e.id_entity");
		$this->db->join('entity_roles er', "e.id_entity=er.id_entity AND er.id_role IN (1, 4)"); /* admin & publisher */
	   $this->db->where('er.status', 'active');
		$result['count'] = $this->db->count_all_results ($this->_table_name);

      
      if (isset($params['status'])) {
         switch ($params['status']) {
            case 'all':
               continue;
            break;
            case 'non_deleted':
               $this->db->where ( 'sites.status <>', 'deleted' );
            break;
            default:
               $this->db->where ( 'sites.status', $params['status'] );
            break;
         }
      }
      
		if(isset($params['revenue_field'])) {
			$this->db->select_sum($params['revenue_field']);
		}

	   $this->db->select_sum('clicks')
               ->select_sum('impressions')
               ->select_sum('alternative_impressions');

      if(isset($params['owner'])) {
         switch ($params['owner']){
            case 'mysites':
               $this->db->where('sites.id_entity_publisher', $params['id_entity']);
               break;
            case 'pubsites':
               $this->db->where('sites.id_entity_publisher <>', $params['id_entity']);
         }
      } else {
         if (isset($params['id_entity'])) {
            $this->db->where('sites.id_entity_publisher', $params['id_entity']);
         }         
      }

	if (isset($params['channel_id_filter'])) {
        $this->db->join('site_channels', 'site_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name, 'left')
                 ->where ( 'site_channels.id_channel', $params['channel_id_filter'] );

        if (isset($params['date_filter'])) {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
             ' AND stat_sites_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"'.
             ' AND stat_sites_channels.id_channel = '.$params['channel_id_filter'], 'left');
          } else {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_site = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.id_channel = '.$params['channel_id_filter'], 'left');
          }
      } else {
        if (isset($params['date_filter'])) {
           $this->db->join('stat_sites', 'stat_sites.id_site = '.$this->_table_name.'.'.$this->_id_field_name.' AND stat_sites.stat_date >= "'.
           type_to_str( $params['date_filter'] ['from'] ,'databasedatetime').'" AND stat_sites.stat_date <= "'. type_to_str( $params['date_filter'] ['to'] ,'databasedatetime').'"', 'left');
        } else {
            $this->db->join('stat_sites', 'stat_sites.id_site = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
        }
      }

	  if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
	  	$escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
	  	$quicksearchFilter = "
	  		((sites.id_site LIKE '%".$escapedQuicksearch."%')
	  		  OR (sites.url LIKE '%".$escapedQuicksearch."%')
	  		  OR (sites.name LIKE '%".$escapedQuicksearch."%'))
        	";
       	$this->db->where($quicksearchFilter, null, false);
      }

      $query = $this->db->get($this->_table_name);
      $row = $query->row();
      if (isset($params['revenue_field'])) {
      	$result['revenue'] = $row->$params['revenue_field'];
      }
      $result['clicks'] = $row->clicks;
      $result['impressions'] = $row->impressions;
      $result['alternative_impressions'] = $row->alternative_impressions;

	  return $result;
	} //end get_count()

	public function pause($id = -1, $id_entity = -1) {
	   $this->set_status ( $id, 'paused', $id_entity, 'active' );
	}

	/**
	 * Удаление сайта и добавленных в него связок сайт-канал
	 *
	 * @param int $id
	 * @param int $id_entity
	 * @return bool флаг успешного удаления сайта
	 */
   public function delete($id = -1, $id_entity = -1) {
      //Проверка принадлеждности сайта
      $this->db->from($this->_table_name)
      		   ->where(array($this->_id_field_name => $id,
      		   				 'id_entity_publisher' => $id_entity));
      if (0 == $this->db->count_all_results()) {
      	return false;
      }

   	   //Удаление каналов, для которых этот сайт был единственным

      $this->db->select("s_c.id_channel, COUNT(s_c.id_site) as sites_count, joined_s_c.id_site",false)
               ->from("site_channels as s_c")
               ->join("site_channels as joined_s_c","joined_s_c.id_site_channel = s_c.id_site_channel AND s_c.id_site = {$id}",'left')
			      ->where("s_c.status <>", "deleted")
			      ->group_by("s_c.id_channel")
			      ->having("id_site IS NOT NULL")
			      ->having("sites_count < 2");

      $query = $this->db->get();


      $channels_to_delete = array();
      if ($query->num_rows() > 0) {
       foreach ($query->result() as $row) {
       	$channels_to_delete[] = $row->id_channel;
       }

       //удаление каналов, не содержащиеся ни на одном сайте:
       $this->db->where_in('id_channel', $channels_to_delete)
       			->update('channels',array('status' => 'deleted'));
      }

      //обновление статуса сайта
      if (false == $this->set_status($id,'deleted', $id_entity)) {
      	 return false;
      }
      
      //обновление статуса связок сайт-канал
      $this->db->where ( $this->_id_field_name, $id )
      	       ->update ( 'site_channels', array ('status' => 'deleted' ) );

      return true;
   }

   public function resume($id = -1, $id_entity) {
      $this->set_status ( $id, 'active', $id_entity, 'paused' );
   }
   
   public function confirm($id = -1) {
   	  $where = array($this->_id_field_name => $id, 'status <>' => 'deleted');
      $this->db->where($where)->update ( $this->_table_name, array ('status' => 'active' ) );
   }
   
   public function deny($id = -1) {
   	  $where = array($this->_id_field_name => $id, 'status <>' => 'deleted');
   	  $this->db->where($where)->update ( $this->_table_name, array('status' => 'denied'));
   }

   public function pause_channel($id_site = -1, $id_channel = -1, $id_entity = -1) {
      $this->set_channel_status ( $id_site, $id_channel, 'paused' , $id_entity);
   }

   public function resume_channel($id_site = -1, $id_channel = -1, $id_entity = -1) {
      $this->set_channel_status ( $id_site, $id_channel, 'active' , $id_entity);
   }

   /**
    * Удаление канала из сайта
    *
    * @param int $id_site идентификатор сайта
    * @param int $id_channel идентификатор канала
    * @param int $id_entity идентификатор лица, осуществляющего удаление связки
    * @return bool флаг успешного удаления связки сайт-канал
    */

   public function delete_channel($id_site = -1, $id_channel = -1, $id_entity = -1) {
      if (!$this->set_channel_status ( $id_site, $id_channel, 'deleted' , $id_entity)) {
      	return false;
      }
      //удаление канала если он больше не содержится ни на одном сайте
      $query = $this->db->select('id_site')
               ->from('site_channels')
               ->where('id_channel', $id_channel)
               ->where('status <>','deleted')
       		   ->get();

      if (0 == $query->num_rows()) {
         $this->db->where ( 'id_channel', $id_channel)
         	      ->update ( 'channels', array ('status' => 'deleted' ) );
      }

      return true;
   }

   /**
    * Создание связки сайт-канал (добавление канала на сайт)
    *
    * @param int $id_site идентификатор сайта
    * @param int $id_channel идентификатор канала
    * @param int $id_entity идентификатор лица, осуществляющего создание связки
    * @return bool флаг успешного добавления канала на сайт
    */

   public function add_channel($id_site = -1, $id_channel = -1, $id_entity = -1) {
      $site_owner = $this->db->from('sites')
      		   				 ->where(array('id_site' => $id_site,
      		   			                   'id_entity_publisher' => $id_entity))
      		                 ->count_all_results() > 0;

      $channel_owner = $this->db->from('channels c')
      						    ->join('sites s','s.id_site = c.id_parent_site')
      		   				 ->where(array('id_channel ' => $id_channel,
      		   			                   'id_entity_publisher' => $id_entity))
      		                 ->count_all_results() > 0;

   	if (!($site_owner && $channel_owner)) {
   		return false;
   	}


   	$this->db->from('site_channels')
   		     ->where(array ('id_channel' => $id_channel , 'id_site' => $id_site));

      if ($this->db->count_all_results() < 1) {
         $this->db->insert('site_channels', array ('id_channel' => $id_channel , 'id_site' => $id_site));
      } else {
      	$this->db->where(array ('id_channel' => $id_channel , 'id_site' => $id_site))
                 ->update ( 'site_channels', array ('status' => 'active' ) );
      }

     return true;
   }

   public function create($params) {
   	$main_table_params = array();
      $created_site_id = -1;

      if(!$this->check_unique_url($params['url'])) {
      	return "Site whith such URL is already exists";
      }

      $main_table_params['url'] = $params['url'];

      $main_table_params['name'] = $params['name'];

      $main_table_params['creation_date'] = $params['creation_date'];

      $main_table_params['description'] = $params['description'];

      //$main_table_params['id_category'] = $params['id_category'];

      $main_table_params['id_entity_publisher'] = $params['id_entity_publisher'];

      if (isset($params['min_cpc'])) {
      	 $main_table_params['min_cpc'] = $params['min_cpc'];
      }

      if (isset($params['min_cpc_image'])) {
         $main_table_params['min_cpc_image'] = $params['min_cpc_image'];
      }
      
      if (array_key_exists('status', $params)) {
      	 $main_table_params['status'] = $params['status'];
      }

      if (count($main_table_params) > 0) {
         $this->db->insert($this->_table_name, $main_table_params);
         $created_site_id = $this->db->insert_id();
      } else {
         return "Site parameters is not specified";
      }

      if (array_key_exists('id_channel', $params)) {
         $this->db->insert('site_channels',array('id_site' => $created_site_id, 'id_channel' => $params['id_channel']));
      }

      //////////////////////////////////////////
      // Register layout
      try {
          $siteModel = new Sppc_SiteModel();
          $site = $siteModel->findObjectById($created_site_id);
          if(is_null($site)) {
              throw new Exception('Site was not found');
          }
          $siteLayoutModel = new Sppc_Site_LayoutModel();
          $siteLayoutModel->updateFromJson($site, $params['layout_json']);
      }
      catch(Exception $e) {
      }
      //////////////////////////////////////////
      return $created_site_id;
   }

   public function update($id, $params, $id_entity = -1) {
      $jsonLayout = $params['layout_json'];
      unset($params['layout_json']);

      if (isset($params['url'])) {
	       if (!$this->check_unique_url($params['url'],$id)) {
	         return "Site with such URL is already exists";
	      }
      }
      $this->db->where(array($this->_id_field_name => $id,
      					     'id_entity_publisher' => $id_entity))
      		   ->update($this->_table_name, $params);

      //////////////////////////////////////////
      // Register layout
      try {
          $siteModel = new Sppc_SiteModel();
          $site = $siteModel->findObjectById($id);
          if(is_null($site)) {
              throw new Exception('Site was not found');
          }
          $siteLayoutModel = new Sppc_Site_LayoutModel();
          $siteLayoutModel->updateFromJson($site, $jsonLayout);
      }
      catch(Exception $e) {
      }
      //////////////////////////////////////////
      return "";
   }

   /**
    * проверка URL сайта на предмет уникальности
    *
    * @param string $status новый статус сайта
    * @return bool (true - если среди неудаленных сайтов такой URL отсутствует)
    */
   public function check_unique_url($url, $id = null) {
         $this->db->select('id_site')
                  ->from($this->_table_name)
            	  ->where(array('url' => $url,
            	  				'status <>' => 'deleted'));
         if (!is_null($id)) {
            $this->db->where('id_site <>',$id);
         }

         return ( 0 == $this->db->count_all_results() );
   }

   /**
    * изменение статуса сайта
    *
    * @param int $id идентификатор сайта
    * @param string $status новый статус сайта
    * @param int $id_entity идентификатор сущности, изменяющей статус сайта
    * @param string $oldStatus старый статус сайта
    * @return bool
    */
   public function set_status($id = -1, $status = 'active', $id_entity = -1, $oldStatus = null) {
      $where = array($this->_id_field_name => $id);
      if (1 != $id_entity) {
         $where['id_entity_publisher'] = $id_entity;
      }
	   if (!is_null($oldStatus)) {
	  	   $where['status'] = $oldStatus;
	   }
      $this->db->where($where)->update($this->_table_name, array ('status' => $status));
   	return true;
   }
   
   /**
    * Sets ownership verification code for specified site
    * 
    * @param int $id
    * @param string $code
    * @return void
    */
   public function set_ownership_verification_code($id = -1, $code = '') {
   	  $this->db->where(array($this->_id_field_name => $id))
   	  	 ->update($this->_table_name, array('ownership_confirmation_code' => $code));
   }

   /**
    * изменение статуса канала на сайте
    *
    * @param int $id_site идентификатор сайта
    * @param int $id_channel идентификатор канала
    * @param string $status новый статус канала
    * @param int $id_entity идентификатор сущности, удаляющей связку сайт-канал
    * @return none
    */
   protected function set_channel_status($id_site = -1, $id_channel = -1, $status = 'active' , $id_entity = -1) {
     //Проверка принадлежности сайта/канала
      $this->db->from('site_channels sc')
      		   ->join('sites s','s.id_site = sc.id_site','left')
      		   ->join('channels c','c.id_parent_site = sc.id_site','left')
      		   ->where(array('s.id_entity_publisher' => $id_entity,
      		           	     'sc.id_site' => $id_site,
      		   			     'sc.id_channel' => $id_channel));

      if (0 == $this->db->count_all_results()) {
      	return false;
      }

      $this->db->where ( array('id_site' => $id_site,
                               'id_channel' => $id_channel ))
      		   ->update ( 'site_channels', array ('status' => $status ) );
      return true;
   }

   /**
    * Получение информации о сайте (url, title, description, id_category)
    *
    * @param int $id идентификатор сайта
    * @return object|null
    */
   public function get_info($id = -1) {
      $this->db->select('url, name, description, id_entity_publisher, status, ownership_confirmation_code')
      	       ->where($this->_id_field_name, $id);
      $query = $this->db->get($this->_table_name);
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return null;
      }
   }

   /**
   * возвращает список лучших сайтов для заданного паблишера
   *
   * @param integer $id_entity код учетной записи паблишера (NULL - для всех паблишеров)
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param array $range массив задающий отчетный период (from, to), если не задан - за все время
   * @return array массив со списком сайтов в формате (id_site => (name, impressions, clicks, revenue))
   */
   public function top($id_entity, $sort_field, $sort_direction, $range = NULL) {
      $limit = $this->global_variables->get("TopSitesOnPage");
      $this->db->select('
      	 sites.id_site AS id, 
      	 sites.name, sites.url,
      	 SUM(impressions) AS impressions, 
      	 SUM(clicks) AS clicks, 
      	 SUM(clicks)*100/SUM(impressions) AS ctr,
      	 sites.id_entity_publisher, 
      	 entities.name AS publisher, 
      	 entities.e_mail
      ');
      if (!is_null($id_entity)) {
         $this->db->select("SUM(earned_publisher) AS revenue");         
      } else {
         $CI =& get_instance();
         if ($CI->user_id == $id_entity) {
            $this->db->select("SUM(earned_admin+earned_publisher) AS revenue");
         } else {
            $this->db->select("SUM(earned_admin) AS revenue");
         }
      }
      $this->db->from('sites')
         ->join('entities', 'sites.id_entity_publisher = entities.id_entity');
      if (!is_null($range)) {
         $this->db->join('stat_sites',
            'sites.id_site = stat_sites.id_site AND stat_date>="'.type_to_str($range['from'], 'databasedate').
            '" AND stat_date<="'.type_to_str($range['to'], 'databasedate').'"',
            'LEFT');
      } else {
         $this->db->join('stat_sites', 'sites.id_site = stat_sites.id_site', 'LEFT');
      }
      if(!is_null($id_entity)) {
         $this->db->where('id_entity_publisher', $id_entity);
      }
      $this->db
         ->group_by('id')
      	->order_by($sort_field, $sort_direction)
      	->limit($limit);
      $res = $this->db->get();

      $top = array();
      foreach ($res->result() as $row) {
         $top[$row->id]['id_publisher'] = $row->id_entity_publisher;
         $top[$row->id]['publisher'] = $row->publisher;
         $top[$row->id]['email'] = $row->e_mail;
         $top[$row->id]['name'] = $row->name;
         $top[$row->id]['url'] = $row->url;
         $top[$row->id]['revenue'] = $row->revenue;
         $top[$row->id]['impressions'] = $row->impressions;
         $top[$row->id]['clicks'] = $row->clicks;
         $top[$row->id]['ctr'] = $row->ctr;
      }
      return $top;
   } //end top()

  public function directory_total($ad_type_filter, $image_size_filter, $cost_model_filter,
     $category_filter, $keyword_filter, $name_filter = null){
      $this->db
         ->select('s.id_site, s.name AS name, s.url, s.description', FALSE)
         ->from('sites s')
         ->join('site_channels sc', 's.id_site=sc.id_site', 'LEFT')
         ->join('channels c', 'sc.id_channel=c.id_channel', 'LEFT')
         ->join('channel_program_types cpt', 'c.id_channel=cpt.id_channel', 'LEFT')
         ->join('entity_roles er', 'er.id_entity=s.id_entity_publisher') 
         ->where(
         	"`s`.`status` = 'active' AND
         	 `er`.status = 'active' AND
			(
			    (
			    `c`.`status` = 'active'  AND
			    `sc`.`status` = 'active'
			    )
			    OR
			    (
			        exists(
			            select 1 from site_layouts sl
			            where sl.id_site=s.id_site
			        )
			    )
			)"
         )
         ->group_by('s.id_site')
         ->having("(COUNT(DISTINCT program_type)>0)");
      if ($category_filter != '') {
         $this->db->join('site_categories sca', 'sca.id_site = s.id_site');
         $this->db->where('sca.id_category', $category_filter);
      }
      if ($cost_model_filter != '') {
           if ($cost_model_filter == 'flatrate') {
              $cost_model_filter = 'Flat_Rate';
           }
           $this->db->where('program_type', $cost_model_filter);
      }
      
      if ($ad_type_filter) {
      	 $allowedAdTypes = explode(',', $ad_type_filter);
      	 
      	 if (in_array('text', $allowedAdTypes)) {
      	 	$this->db->where("(FIND_IN_SET('text', c.ad_type))");
      	 }
      	 
      	 if (in_array('image', $allowedAdTypes)) {
      	 	$this->db->where("(FIND_IN_SET('image', c.ad_type))");
      	 }
      	 
      }
      
      if ($image_size_filter != '') {
        $this->db->where('id_dimension', $image_size_filter);
      }
      if ($keyword_filter != '') {
        $keyword_filter = str_replace("'", "\\'", $keyword_filter);
        $keyword_filter = str_replace("_", "\\_", $keyword_filter);
        $keyword_filter = str_replace("%", "\\%", $keyword_filter);
        $this->db->where("( s.name LIKE '%$keyword_filter%' OR s.description LIKE '%$keyword_filter%')", NULL, FALSE);
      }
      
      if (!is_null($name_filter)) {
      	$this->db->where('s.name <= ', $name_filter);
      }
      $res = $this->db->get('sites');
      return $res->num_rows();
  } //end directory_total

   public function directory_select($page, $per_page, $sort_field, $sort_direction,
      $ad_type_filter, $image_size_filter, $cost_model_filter, $category_filter, $keyword_filter) {
      $this->db
         ->select('s.id_site, s.name AS name, s.url, s.description', FALSE)
         ->from('sites s')
         ->join('site_channels sc', "s.id_site=sc.id_site",'LEFT')
         ->join('channels c', "sc.id_channel=c.id_channel",'LEFT')
         ->join('channel_program_types cpt', 'c.id_channel=cpt.id_channel', 'LEFT')
         ->join('entity_roles er', 'er.id_entity=s.id_entity_publisher') /* publisher */
         ->where(
         	"`s`.`status` = 'active' AND
         	 `er`.status = 'active' AND
			(
			    (
			    `c`.`status` = 'active'  AND
			    `sc`.`status` = 'active'
			    )
			    OR
			    (
			        exists(
			            select 1 from site_layouts sl     
			            where sl.id_site=s.id_site
			        )
			    )
			)"
         )
         ->group_by('s.id_site')
         ->having("(COUNT(DISTINCT program_type)>0)")
         ->order_by($sort_field, $sort_direction)
         ->limit($per_page, ($page-1)*$per_page);
      
      if ($category_filter != '') {
      	$this->db->join('site_categories sca', 'sca.id_site = s.id_site');
         $this->db->where('sca.id_category', $category_filter);
      }
      if ($cost_model_filter != '') {        
           if ($cost_model_filter == 'flatrate') {
              $cost_model_filter = 'Flat_Rate';
           }
           $this->db->where('program_type', $cost_model_filter);        
      }
      
      if ($ad_type_filter) {
      	 $allowedAdTypes = explode(',', $ad_type_filter);
      	 
      	 if (in_array('text', $allowedAdTypes)) {
      	 	$this->db->where("(FIND_IN_SET('text', c.ad_type))");
      	 }
      	 
      	 if (in_array('image', $allowedAdTypes)) {
      	 	$this->db->where("(FIND_IN_SET('image', c.ad_type))");
      	 }
      	 
      }
      
      if ($image_size_filter != '') {
        $this->db->where('id_dimension', $image_size_filter);
      }
      if ($keyword_filter != '') {
         $keyword_filter = str_replace("'", "\\'", $keyword_filter);
         $keyword_filter = str_replace("_", "\\_", $keyword_filter);
         $keyword_filter = str_replace("%", "\\%", $keyword_filter);
         $this->db->where("( s.name LIKE '%$keyword_filter%' OR s.description LIKE '%$keyword_filter%' OR s.url LIKE '%$keyword_filter%')");
      }
      
      $res = $this->db->get();
     
      $records = array();
      $ids = array();
      foreach ($res->result() as $row) {
         $ids[] = $row->id_site;
         $records[$row->id_site]['name'] = $row->name;
         $records[$row->id_site]['description'] = $row->description;
         $records[$row->id_site]['url'] = $row->url;
      }
      if (count($ids)) {
         $this->db
            ->select("id_site, c.name, c.id_channel, ".
               "GROUP_CONCAT(DISTINCT program_type SEPARATOR ',') AS program_type, c.ad_type", FALSE)
            ->from('site_channels sc')
            ->join('channels c', 'sc.id_channel=c.id_channel')
            ->join('channel_program_types cpt', 'c.id_channel=cpt.id_channel', 'LEFT')
            ->where('sc.status', 'active')
            ->where('c.status', 'active')
            ->where_in('id_site', $ids)
            ->group_by('id_site, c.id_channel');
         if ($cost_model_filter != '') {
            if ($cost_model_filter == 'flatrate') {
               $cost_model_filter = 'Flat_Rate';
            }
            $this->db->where('program_type', $cost_model_filter);
         }
        
         if ($image_size_filter != '') {
            $this->db->where('id_dimension', $image_size_filter);
         }
         $res = $this->db->get();
         foreach ($res->result() as $row) {
            $records[$row->id_site]['channels'][$row->id_channel]['name'] = $row->name;
            $records[$row->id_site]['channels'][$row->id_channel]['cost_model'] = $row->program_type;
            $records[$row->id_site]['channels'][$row->id_channel]['ad_type'] = $row->ad_type;
         }
      }
  	   return $records;
   } //end directory_select

   /**
    * Возвращает сетефой путь иконки сайта
    *
    * @param integer $id_site код сайта
    * @return string сетевой путь иконки сайта
    */
   public function get_thumb($id_site, $id = FALSE) {
      if (!file_exists($this->config->item('path_to_images').'/thumbs/'.$id_site.'.jpeg')) {
         $id_site = 'default';
      }
      $CI =& get_instance();
      $path = $CI->get_siteurl().ltrim($this->config->item('path_to_images'),'./');
      if ($id) return strtoupper($id_site);
      return $path.'thumbs/'.$id_site.'.jpeg';
   } //end get_thumb

}