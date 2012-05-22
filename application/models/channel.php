<?php // -*- coding: UTF-8 -*-
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'models/object_model.php';

/**
* класс для настройки параметров канала
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

class Channel extends Object_Model {
	
	public function __construct() {
		parent::__construct ();
		$this->_table_name = 'channels';
		$this->_id_field_name = 'id_channel';
	}
	
	public function get_sites_channels($params) {
	   if (array_key_exists('fields', $params)) {
         $this->db->select ( $params['fields']);
      }
      
      $this->db->where('channels.id_channel IS NOT NULL', null, false);
      
	   if (isset($params['id_entity'])) {
         $this->db->where('sites.id_entity_publisher',$params['id_entity']); 
      }
      
      $this->db->from('site_channels')
      		   ->join('sites','sites.id_site = site_channels.id_site','left')
      		   ->join('channels','channels.id_channel = site_channels.id_channel','left')
      	       ->join('dimensions', 'dimensions.id_dimension = channels.id_dimension', 'left');
      
      $this->db->join('channel_program_types as cpm', 'cpm.id_channel = site_channels.id_channel AND cpm.program_type = "CPM"', 'left');
      $this->db->select('MIN(cpm.volume) as min_cpm_volume, MIN(cpm.cost_text) as min_cpm_cost_text, MIN(cpm.cost_image) as min_cpm_cost_image, cpm.avg_cost_text as avg_cpm_cost_text, cpm.avg_cost_image as avg_cpm_cost_image', FALSE);
            
      $this->db->join('channel_program_types as flat_rate', 'flat_rate.id_channel = site_channels.id_channel AND flat_rate.program_type = "Flat_Rate"', 'left');        
      $this->db->select('MIN(flat_rate.volume) as min_flat_rate_volume, MIN(flat_rate.cost_text) as min_flat_rate_cost_text, MIN(flat_rate.cost_image) as min_flat_rate_cost_image, flat_rate.avg_cost_text as avg_flat_rate_cost_text, flat_rate.avg_cost_image as avg_flat_rate_cost_image', FALSE);
      $this->db->group_by('site_channels.id_site, site_channels.id_channel');
      
      if (array_key_exists('hide_wo_programs', $params)) {
         $this->db->having( '(min_flat_rate_volume IS NOT NULL OR min_cpm_volume IS NOT NULL)','',FALSE);
      }
      
	   if (array_key_exists('date_filter', $params)) {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_channel = site_channels.id_channel'.
             ' AND stat_sites_channels.id_site = site_channels.id_site'.
             ' AND stat_sites_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
             ' AND stat_sites_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"', 'left');
      }
      
	   if (array_key_exists('category_id_filter', $params)) { 
	   	  $this->db->join('site_categories', 'site_categories.id_site = sites.id_site', 'left');
	   	  $this->db->where('site_categories.id_category', $params['category_id_filter'] );
       }
      
	   if (array_key_exists('channel_category_id_filter', $params)) {
	   	$this->db->join('site_categories', 'site_categories.id_site = sites.id_site', 'left');
	   	$this->db->join('channel_categories', 'channel_categories.id_channel = channels.id_channel', 'left');
	   	
	   	$channelCategoryWhere = array(
	   	   'channel_categories.id_category = ' . $this->db->escape($params['channel_category_id_filter']),
	   	   '(channel_categories.id_category IS NULL) AND (site_categories.id_category = ' . $this->db->escape($params['channel_category_id_filter']) . ')'
	   	);
	   	$this->db->where('((' . implode(') OR (', $channelCategoryWhere) . '))', null, false);
      }
      
      if (array_key_exists('site_id_filter', $params)) {
         $this->db->where_in('site_channels.id_site', $params['site_id_filter'] );
      }
      
	   if (array_key_exists('channel_id_filter', $params)) {
         $this->db->where_in('site_channels.id_channel', $params['channel_id_filter'] );
      }
      
	   if (array_key_exists('site_channel_id_filter', $params)) {
         $this->db->where_in('site_channels.id_site_channel', $params['site_channel_id_filter'] );
      }
	    
	   if (array_key_exists('price_filter', $params)) {
	      $price_field = '';   
         switch ($params['price_filter']['price_program']) {
	         case 'cpm':
	           if ('text' == $params['price_filter']['ads_type']) {	
	              $price_field = 'cpm.avg_cost_text';	
	           } else {
	           	  $price_field = 'cpm.avg_cost_image';
	           }
	         break;
	         case 'flat_rate':
              if ('text' == $params['price_filter']['ads_type']) {  
                 $price_field = 'flat_rate.avg_cost_text';  
              } else {
                 $price_field = 'flat_rate.avg_cost_image';
              }
	         break;
         }
         
         $this->db->where ($price_field.' <= ', $params['price_filter']['price'],FALSE);
         $this->db->where ($price_field.' > ', 0, FALSE);
     }
     
	   if (array_key_exists('show_deleted_channels', $params) && (!$params['show_deleted_channels'])) {
         $this->db->where('site_channels.status <>','deleted');
      }
	   if (array_key_exists('status', $params) && ($params['status'] != "")) {
         $this->db->where_in('site_channels.status',$params['status']);
         $this->db->where_in('sites.status',$params['status']);
         $this->db->where_in('channels.status',$params['status']);
      }
     
     if (!array_key_exists('disable_site_ordering', $params)) {
      $this->db->order_by ('site_url','asc');
      $this->db->order_by ('id_site','asc');
     }
     
	  if (array_key_exists('order_by', $params)) {
         if (array_key_exists('order_direction', $params)) {
            $this->db->order_by ( $params['order_by'], $params['order_direction'] );
         } else {
            $this->db->order_by ( $params['order_by']);
         }
      }
      
      $query = $this->db->get ();
      //echo $this->db->last_query();
      
      if ($query->num_rows () > 0) {
         return $query->result_array ();
      } else {
         return null; 
      }
      
	}
	
	/**
	 * Получение списка каналов
	 *
	 * @param array $params массив требуемых параметров каналов
	 * @return array|null - массив, содержащий список каналов и их параметры
	 */
	public function get_list($params) {
		if (isset($params['fields'])) {
			$this->db->select ( $params['fields']);
		}

		$this->db->from ( $this->_table_name );
		
		if (isset($params['status']) && ('all' != $params['status'])) {
			$this->db->where ( $this->_table_name.'.status', $params['status'] );
		}
		
		if (isset($params['show_deleted_channels']) && (!$params['show_deleted_channels'])) {
			$this->db->where($this->_table_name.'.status <>','deleted');
		}
		
	   if (isset($params['show_deleted_in_site_channels']) && (!$params['show_deleted_in_site_channels'])) {
         $this->db->where('site_channels.status <>','deleted');
      }
		
	   if (isset($params['site_id_filter'])) {
        $this->db->join('site_channels', 'site_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left')
                 ->where('site_channels.id_site', $params['site_id_filter'] );
        
		   if (isset($params['date_filter'])) {
	          $this->db->join('stat_sites_channels', 'stat_sites_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
	          ' AND stat_sites_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
	          ' AND stat_sites_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"'.
	          ' AND stat_sites_channels.id_site = '.$params['site_id_filter'], 'left');
	          $this->db->group_by('stat_sites_channels.id_site');
	       } else {
	          $this->db->join('stat_sites_channels', 'stat_sites_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
	          ' AND stat_sites_channels.id_site = '.$params['site_id_filter'], 'left');
	          $this->db->group_by('stat_sites_channels.id_site');
	       }
      }
      
	   if (isset($params['channel_id_filter'])) { //фильтрация по списку id-каналов
     
        $this->db->where_in ( $this->_table_name.'.'.$this->_id_field_name, $params['channel_id_filter'] );
      }
		
      if (isset($params['site_category_id_filter'])) {
        //$this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left'); 
        //$this->db->where ( 'sites.id_category', $params['site_category_id_filter'] );
      }
      
	  if (isset($params['site_parent_id_filter'])) {
	     $this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left'); 
        $this->db->where_in( $this->_table_name.'.id_parent_site', $params['site_parent_id_filter'] );
      }
      
	  if (isset($params['price_filter'])) {
	     $this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left');
	     switch ($params['price_filter']['price_program']) {
	     	case 'cpm':
	     	  $this->db->where ('cpm.avg_cost <= ', $params['price_filter']['price'],FALSE);
	     	break;
	     	case 'flat_rate':
           $this->db->where ('flat_rate.avg_cost <= ', $params['price_filter']['price'],FALSE);
         break;
	     }
     }
		
		if (isset($params['limit']) && isset($params['offset'])) {
		   $this->db->limit ( $params['limit'], $params['offset'] );
		}
		
		
		if (isset($params['join_tables'])){ 
		   if (in_array('dimensions', $params['join_tables'])) {
		    $this->db->join('dimensions', 'dimensions.id_dimension = '.$this->_table_name.'.id_dimension', 'left');
		   }
		   
		   if (in_array('sites', $params['join_tables'])) {
		      $this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left');
		      $this->db->join('entities e', 'sites.id_entity_publisher=e.id_entity');
		      $this->db->join('entity_roles er', 'sites.id_entity_publisher=er.id_entity AND er.id_role IN (1,4)'); /* admin & publisher */
            $this->db->where('er.status', 'active');
          if(!array_key_exists('disable_parent_site_ordering', $params)) {
            $this->db->order_by ('id_parent_site','asc');
          }
         }
         
		   if (in_array('channel_program_types_cpm', $params['join_tables'])) {
          $this->db->join('channel_program_types as cpm', 'cpm.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.' AND cpm.program_type = "CPM"', 'left');
          $this->db->select('MIN(cpm.volume) as cpm_volume, MIN(cpm.cost) as cpm_cost', FALSE);
         }
         
		   if (in_array('channel_program_types_flat_rate', $params['join_tables'])) {
          $this->db->join('channel_program_types as flat_rate', 'flat_rate.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.' AND flat_rate.program_type = "Flat_Rate"', 'left');
       
          $this->db->select('MIN(flat_rate.volume) as flat_rate_volume, MIN(flat_rate.cost) as flat_rate_cost', FALSE);
         }
         
         
		   if (in_array('channel_program_types', $params['join_tables'])) {
		      $this->db->select("(
               SELECT
                  COUNT(*)
               FROM
                  channel_program_types
               WHERE
                  channel_program_types.id_channel = channels.id_channel AND
                  channel_program_types.program_type = 'Flat_Rate'
            ) AS flat_rate_programs_count", FALSE);
            $this->db->select("(
               SELECT
                  COUNT(*)
               FROM
                  channel_program_types
               WHERE
                  channel_program_types.id_channel = channels.id_channel AND
                  channel_program_types.program_type = 'CPM'
            ) AS cpm_programs_count", FALSE);
            $this->db->select("(
               SELECT
                  COUNT(*)
               FROM
                  channel_program_types
               WHERE
                  channel_program_types.id_channel = channels.id_channel
            ) AS programs_count", FALSE);
         }
		}
		
	   if (isset($params['hide_wo_programs']) && (true == $params['hide_wo_programs'])){
	   	   $this->db->join('(SELECT id_channel, COUNT(id_program) as programs_count FROM channel_program_types GROUP BY id_channel) as program_types', 'program_types.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
            $this->db->select('programs_count', FALSE);
            $this->db->having('programs_count > ', '0');
      }
      
      if (!isset($params['site_id_filter'])) {
			 if (isset($params['date_filter'])) {
			    $this->db->join('stat_channels', 'stat_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
			    ' AND stat_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
			    ' AND stat_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"', 'left');
			 } else {
			    $this->db->join('stat_channels', 'stat_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
			 }
      }
		 
		 $this->db->group_by($this->_table_name.'.'.$this->_id_field_name);
		
	   if (isset($params['order_by'])) {
         if (isset($params['order_direction'])) {
            $this->db->order_by ( $params['order_by'], $params['order_direction'] );
         } else {
            $this->db->order_by ( $params['order_by']);
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
            $this->db->where('sites.id_entity_publisher', $params['id_entity']);
         }         
      }
      
	  if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
	  	 //$this->db->join('dimensions', 'channels.id_dimension = dimensions.id_dimension', 'left');
	  	 
	  	 $escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
	  	 $quicksearchFilter = "
	  		((channels.id_channel LIKE '%".$escapedQuicksearch."%')
	  		  OR (channels.name LIKE '%".$escapedQuicksearch."%')
	  		  OR (dimensions.name LIKE '%".$escapedQuicksearch."%'))
        	";
       	 $this->db->where($quicksearchFilter, null, false);
       }
      //$this->db->join("entities e", "sites.id_entity_publisher=e.id_entity");
      
	   /*if (isset($params['id_entity'])) {
         $this->db->where('sites.id_entity_publisher',$params['id_entity']); 
      }*/
      
		$query = $this->db->get ();
      //print $this->db->last_query();
		
		if ($query->num_rows () > 0) {
			return $query->result_array ();
		} else {
			return null; 
		}
	}
	
	/**
	 * Получение количества каналов
	 *
	 * @param array $params массив требуемых параметров каналов
	 * @return int количество каналов в списке
	 */
	public function get_count($params) {
		$result = array();
		
	   if (array_key_exists('status', $params) && ('all' != $params['status'])) {
         $this->db->where ( $this->_table_name.'.status', $params['status'] );
      }
      
	   if (array_key_exists('site_id_filter', $params)) {
        $this->db->join('site_channels', 'site_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left'); 
        $this->db->where ( 'site_channels.id_site', $params['site_id_filter'] );
      }

      $this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left');
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
      
     $this->db->join('entity_roles er', 'sites.id_entity_publisher=er.id_entity AND er.id_role IN (1,4)'); /* admin & publisher */
     $this->db->where('er.status', 'active');
	  if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
	  	 $this->db->join('dimensions', 'channels.id_dimension = dimensions.id_dimension', 'left');
	  	 
	  	 $escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
	  	 $quicksearchFilter = "
	  		((channels.id_channel LIKE '%".$escapedQuicksearch."%')
	  		  OR (channels.name LIKE '%".$escapedQuicksearch."%')
	  		  OR (dimensions.name LIKE '%".$escapedQuicksearch."%'))
        	";
       	 $this->db->where($quicksearchFilter, null, false);
       }
		$result['count'] = $this->db->count_all_results ($this->_table_name);
		
		
	   if(isset($params['revenue_field'])) {
         $this->db->select_sum($params['revenue_field']);   
      }
		
		$this->db->select_sum('clicks')
               ->select_sum('impressions')
               ->select_sum('alternative_impressions');
      
	if (isset($params['site_id_filter'])) {
        $this->db->join('site_channels', 'site_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
        $this->db->where('site_channels.id_site', $params['site_id_filter'] );
        
         if (isset($params['date_filter'])) {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
             ' AND stat_sites_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"'.
             ' AND stat_sites_channels.id_site = '.$params['site_id_filter'], 'left');
          } else {
             $this->db->join('stat_sites_channels', 'stat_sites_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_sites_channels.id_site = '.$params['site_id_filter'], 'left');
          }
      } else {
          if (isset($params['date_filter'])) {
             $this->db->join('stat_channels', 'stat_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name.
             ' AND stat_channels.stat_date >= "'. type_to_str($params['date_filter']['from'],'databasedatetime').'"'.
             ' AND stat_channels.stat_date <= "'. type_to_str($params['date_filter']['to'],'databasedatetime').'"', 'left');
          } else {
             $this->db->join('stat_channels', 'stat_channels.id_channel = '.$this->_table_name.'.'.$this->_id_field_name, 'left');
          }
      
      }

	   if (isset($params['status']) && ('all' != $params['status'])) {
         $this->db->where ($this->_table_name.'.status', $params['status'] );
      }

      $this->db->join('sites', 'sites.id_site = '.$this->_table_name.'.id_parent_site', 'left');
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
      
	  if ((isset($params['quicksearch'])) && (!empty($params['quicksearch']))) {
	  	 $this->db->join('dimensions', 'channels.id_dimension = dimensions.id_dimension', 'left');
	  	 
	  	 $escapedQuicksearch = $this->db->escape_str($params['quicksearch']);
	  	 $quicksearchFilter = "
	  		((channels.id_channel LIKE '%".$escapedQuicksearch."%')
	  		  OR (channels.name LIKE '%".$escapedQuicksearch."%')
	  		  OR (dimensions.name LIKE '%".$escapedQuicksearch."%'))
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
      $this->set_status ( $id, 'paused' , $id_entity);
   }
   
   public function resume($id = -1, $id_entity = -1) {
      $this->set_status ( $id, 'active' , $id_entity);
   }
   
   public function delete($id = -1, $id_entity) {
   	if ($this->set_status ( $id, 'deleted', $id_entity )) {
     
      $this->db->where ( 'id_channel', $id )
      		   ->update ( 'site_channels', array ('status' => 'deleted' ) );
   	}
   }
   
   public function pause_site($id_channel = -1, $id_site = -1, $id_entity = -1) {
      $this->set_channel_status ( $id_channel, $id_site, 'paused' , $id_entity);
   }
   
   public function resume_site($id_channel = -1, $id_site = -1, $id_entity = -1) {
      $this->set_channel_status ( $id_channel, $id_site, 'active' , $id_entity);
   }
   
   public function delete_site($id_channel = -1, $id_site = -1, $id_entity = -1) {
   	 //Проверка принадлежности канала  
      if ($id_entity != $this->get_channel_owner($id_channel)) {  
      	return "Access denied";      		   
      }
   	
      //блокируем удаление сайта, если канал больше не содержится ни на одном сайте
      $this->db->select('id_site')
      		   ->from('site_channels')
               ->where('id_channel', $id_channel)
               ->where('status <>','deleted');
      if (2 > $this->db->count_all_results()) {
         return "Unable to delete last site containing this channel";
      }
	   	if ($this->set_channel_status ( $id_channel, $id_site, 'deleted' , $id_entity)) {
	   		return "";
	   	} else {
	   		return "Access denied";
	   	}
   }
   
/**
    * изменение статуса сайта в канале
    *
    * @param int $id_channel идентификатор канала
    * @param int $id_site идентификатор сайта
    * @param string $status новый статус сайта
    * @return bool флаг успешного изменения статуса
    */
   protected function set_channel_status($id_channel = -1, $id_site = -1, $status = 'active', $id_entity = -1) {
   	
     //Проверка принадлежности сайта/канала
      if ($id_entity != $this->get_channel_owner($id_channel)) {  
      	return false;      		   
      }
      
      $this->db->where (array( 'id_site' => $id_site, 'id_channel' => $id_channel ))
       	       ->update ( 'site_channels', array ('status' => $status ) );
      
      return true;
   }
   
   /**
    * Определение владельца канала
    *
    * @param int $id_channel идентификатор канала
    */
   public function get_channel_owner($id_channel) {
   	 $query = $this->db->select('s.id_entity_publisher')
   	 		   		   ->from('site_channels sc')
      		   	       ->join('sites s','s.id_site = sc.id_site','left')
      		   		   ->join('channels c','c.id_parent_site = sc.id_site','left')
      		   		   ->where('sc.id_channel',$id_channel)
      		   		   ->limit(1)
      		   		   ->get();
     if (0 < $query->num_rows()) {
     	return $query->row()->id_entity_publisher;
     } else {
     	return NULL;
     }
   }
   
   /**
    * изменение статуса канала
    *
    * @param int $id идентификатор канала
    * @param string $status новый статус канала
    * @param int $id_entity идентификатор сущности, меняющей статус канала
    * @return bool флаг успешного изменения статуса
    */
   protected function set_status($id = -1, $status = 'active' , $id_entity = -1) {
      //Проверка принадлежности сайта/канала  
      if ($id_entity != $this->get_channel_owner($id)) {  
      	return false;      		   
      }
   	
      $this->db->where ( $this->_id_field_name, $id )
      		   ->update ( $this->_table_name, array ('status' => $status ) );
      
      return true;
   }
   
   /**
    * Создание канала
    *
    * @param array $params параметры канала
    * @return int|string ID созданного канала либо текст ошибки
    */
   public function create($params) {
   	$main_table_params = array();
   	$created_channel_id = -1;
   	
      $main_table_params['name'] = $params['name']; 
      if (isset($params['id_parent_site'])) {
         $main_table_params['id_parent_site'] = $params['id_parent_site'];
      }
      
      $main_table_params['id_dimension'] = $params['id_dimension'];
      
      $main_table_params['description'] = $params['description']; 
      
      $main_table_params['id_dimension'] = $params['id_dimension'];
      
      $main_table_params['id_category'] = $params['id_category']; 
      
      $main_table_params['ad_type'] = $params['ad_type']; 
      
      $main_table_params['channel_type'] = $params['channel_type']; 
      
      $main_table_params['ad_settings'] = $params['ad_settings']; 
     
      $main_table_params['creation_date'] = type_to_str(time(),'databasedatetime'); 

      //$main_table_params['display_ads'] = $params['display_ads'];       
      
      if (count($main_table_params) > 0) {
         $this->db->insert($this->_table_name, $main_table_params);
         $created_channel_id = $this->db->insert_id();
      } else {
      	return "Channel parameters is not specified";
      }
      
      switch ($params['ad_settings']) {
      	/*
            case 'google_adsense':
             $this->db->insert('channels_ad_settings_adsense',array( $this->_id_field_name => $created_channel_id, 'code' => $params['adsense_code']));
            break;
          */  
            case 'blank_color':
             $this->db->insert('channels_ad_settings_blank_color',array( $this->_id_field_name => $created_channel_id, 'color' => $params['blank_color']));
            break;
         }
      
      return $created_channel_id;
   }
   
   public function update($id, $params, $id_entity = -1) {
      if ($id_entity != $this->get_channel_owner($id)) {  
      	return "Access denied";      		   
      }
      
      $main_table_params = array();
      
   if (isset($params['name'])) {
         $main_table_params['name'] = $params['name']; 
      }
      
      if (isset($params['description'])) {
         $main_table_params['description'] = $params['description'];	
      }
      
      if (isset($params['id_category'])) {
         $main_table_params['id_category'] = $params['id_category']; 
      }
      
      if (isset($params['id_dimension'])) {
         $main_table_params['id_dimension'] = $params['id_dimension']; 
      }
      
      if (isset($params['ad_type'])) {
         $main_table_params['ad_type'] = $params['ad_type']; 
      }
      
      if (isset($params['channel_type'])) {
         $main_table_params['channel_type'] = $params['channel_type']; 
      }
      
      if (isset($params['ad_settings'])) {
         $main_table_params['ad_settings'] = $params['ad_settings']; 
      }
/*
      if (isset($params['display_ads'])) {
         $main_table_params['display_ads'] = $params['display_ads'];
      }      
     */ 
      if (count($main_table_params) > 0) {
      	 $this->db->where($this->_id_field_name, $id)
         		  ->update($this->_table_name, $main_table_params);
      }
      
      if (array_key_exists('ad_settings',$params)) {
         //$this->db->delete ( 'channels_ad_settings_adsense', array ($this->_id_field_name => $id) );
         //$this->db->delete ( 'channels_ad_settings_blank_color', array ($this->_id_field_name => $id) );
      
      	/*
	   	switch ($params['ad_settings']) {
	   		case 'google_adsense':
	   		 $this->db->insert('channels_ad_settings_adsense',array( $this->_id_field_name => $id, 'code' => $params['adsense_code']));
	   		break;
	   		
	   		case 'blank_color':
	          $this->db->insert('channels_ad_settings_blank_color',array( $this->_id_field_name => $id, 'color' => $params['blank_color']));
	         break;
	   	}
	   	*/
      }
      return "";
   }
   
   /**
    * Получение информации о канале 'name','description','id_dimension','ad_type','channel_type'
    *
    * @param int $id идентификатор канала
    * @return object|null
    */
   public function get_info($id = -1) {
      $this->db->select($this->_table_name.'.name')
               ->select($this->_table_name.'.id_dimension')
               //->select($this->_table_name.'.id_category')
               ->select('dimensions.name as dimension_name')
               ->select('description, width, height, max_ad_slots, channels.ad_type, channel_type, ad_settings, blank_color')
               ->where($this->_table_name.'.'.$this->_id_field_name, $id)
               //->join('channels_ad_settings_adsense as adsense','adsense.'.$this->_id_field_name.'='.$this->_table_name.'.'.$this->_id_field_name,'left')
               //->join('channels_ad_settings_blank_color as blank_color','blank_color.'.$this->_id_field_name.'='.$this->_table_name.'.'.$this->_id_field_name,'left')
               ->join('dimensions','dimensions.id_dimension = '.$this->_table_name.'.id_dimension');
      $query = $this->db->get($this->_table_name);
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return null;
      }
   } //end get_info
   
   /**
    * сохраняет параметры для CPC объявлений канала
    * 
    * @param integer $id_channel код канала
    * @param bool $use_cpc флаг использования в канале CPC объявлений
    * @param string $ratio строка определяющая соотношение CPM и CPC объявлений
    * @return ничего не возвращает
    */
   public function cpc_settings($id_channel, $use_cpc, $ratio) {
   	$fields = array(
   	   'use_cpc' => $use_cpc?'true':'false'
   	);
   	if ($use_cpc) {
   		$fields['cpm_cpc_ratio'] = $ratio;
   	}
   	$this->db 
   	   ->where('id_channel', $id_channel)
   	   ->update('channels', $fields);
   } //end cpc_settings
   
   /**
    * возвращает список фидов для выбранного канала (с учетом индивидуальных настроек)
    *
    * @param unknown_type $id_channel
    */
   function feeds($id_channel) {
   	$id_channel = $id_channel?$id_channel:0;
   	$res = $this->db
   	   ->select('f.id_feed, f.name, f.title, f.affiliate_id_1, f.affiliate_id_2, f.affiliate_id_3, f.status, f.commission')
   	   ->from('feeds f')
   	   ->order_by('name')
   	   ->get();
      $list = array();
   	if ($res->num_rows()) {   		
	   	foreach ($res->result() as $row) {
	   		$list[$row->id_feed]['name'] = $row->name;
	   		$list[$row->id_feed]['title'] = $row->title;
	   		$list[$row->id_feed]['affiliate_id_1'] = $row->affiliate_id_1;
	   		$list[$row->id_feed]['affiliate_id_2'] = $row->affiliate_id_2;
	   		$list[$row->id_feed]['affiliate_id_3'] = $row->affiliate_id_3;
            $list[$row->id_feed]['commission'] = $row->commission;
            $list[$row->id_feed]['status'] = $row->status;
	   	}
      }
   	return $list;   	
   } //end feeds
   
   /**
   * возвращает таблицу минимальных бидов для сайтов канала
   *
   * @param integer $id_channel код канала
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @return array таблица с информацией
   */
   public function bid_table($id_channel, $sort_field, $sort_direction, $id_site = NULL) {
   	$this->db
   	   ->select('name, url, min_cpc, min_cpc_image, s.id_site')
         ->from('sites s')
			->join('site_channels sc', 's.id_site=sc.id_site')
			->where(array('sc.status' => 'active',
							  's.status' => 'active',
							  's.use_cpc' => 'true',
							  'sc.id_channel' => $id_channel))
			->order_by($sort_field, $sort_direction);
	   if (!is_null($id_site)) {
	      $this->db->where('s.id_site', $id_site);
	   }
      $res = $this->db->get();
   	$table = array();
   	if ($res->num_rows()) {
      	foreach ($res->result() as $row) {
      		$table[$row->id_site]['name'] = $row->name;
      		$table[$row->id_site]['url'] = $row->url;
      	   $table[$row->id_site]['min_bid'] = $row->min_cpc;
      	   $table[$row->id_site]['min_bid_image'] = $row->min_cpc_image;
      	}
   	}
   	return $table;
   } //end bid_table
   
   /**
    * Возвращает список категорий связанных с выбранным каналом
    * 
    * @param int $channelId
    * @return array
    */
   public function get_categories($channelId) {
   	$this->db->select('c.id_category, c.name')
   	  -> from ('categories c')
   	  -> join ('channel_categories cc', 'cc.id_category = c.id_category', 'inner')
   	  -> where ('cc.id_channel', $channelId);
   	
   	$res = $this->db->get();
   	$categories = array();
   	
   	if ($res->num_rows()) {
   		$categories = $this->db->result();
   	}
   	
   	return $categories;
   }
} //end model Channel