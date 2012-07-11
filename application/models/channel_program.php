<?php
if (! defined ( 'BASEPATH' ))
   exit ( 'No direct script access allowed' );

/**
 * Модель для работы с платежными программами каналов
 * 
 * @author Немцев Андрей
 * @project SmartPPC6
 * @version 1.0.0
 */

class Channel_Program extends CI_Model {
   /**
    * конструктор класса
    *
    * @return ничего не возвращает
    */
   public function __construct() {
      parent::__construct ();
      $this->_table_name = 'channel_program_types';
      $this->_id_field_name = 'id_program';
   } //end __construct()

   
   /**
    * Получение идентификатора владельца платежной программы
    *
    * @param int $id_program идентификатор программы
    * @return int|null
    */
   public function get_program_owner($id_program) {
   		$query = $this->db->select('s.id_entity_publisher')
   						  ->join('channels ch','ch.id_channel = '.$this->_table_name.'.id_channel')
   						  ->join('sites s','s.id_site = ch.id_parent_site')
   		                  ->where($this->_id_field_name, $id_program)
   						  ->get($this->_table_name);
       if (0 < $query->num_rows()) {
       	  return $query->row()->id_entity_publisher;					  
       } else {
       	  return null;
       }
   }
   
   /**
    * Получение типа объявления и ID программы по-умолчанию для канала
    * Приоритет при выборе - text & image лучше чем text, Flat Rate лучше чем CPM
    *
    * @param array $params (массив параметров для поиска программы id_site_channel) 
    */
   
   	public function get_default_program($params) {
      	$default_program = NULL;
      	$default_ad_type = NULL;
   	
	   	//определение id_channel
      	if (array_key_exists('id_site_channel',$params)) {
         	$this->db->select('sc.id_channel, c.ad_type')
         		->from('site_channels as sc')
         		->join('channels as c','c.id_channel = sc.id_channel')
         		->where('id_site_channel',$params['id_site_channel']);
         	$query = $this->db->get();
         	if ($query->num_rows() > 0) {
            	$id_channel = $query->row()->id_channel;
            	$channel_type = $query->row()->ad_type;        
         	} else {
            	return NULL;
         	}
         
      	} else {
      		return NULL;
      	}
         
      	$CI =& get_instance();
      	$CI->load->model('sites_channels');
      
      	$slot_info = $CI->sites_channels->get_slot_info($params['id_site_channel']);
      
      	$allowedAdTypes = explode(',', $slot_info['type']);
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedAdTypes)) {
      	 	$order_by = 'cost_text';
      	}
      	if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) {
      	 	$order_by = 'cost_image';
      	} 
      	$query_flat_rate = $this->db->where(array('id_channel' => $id_channel, 'program_type' => 'Flat_Rate'))
      		->order_by($order_by,'desc')
      		->get($this->_table_name);
                  
      	$query_cpm = $this->db->where(array('id_channel' => $id_channel, 'program_type' => 'CPM'))
      		->order_by($order_by,'desc')
      		->get($this->_table_name);
      
      	$allowed_ad_type = '';
      	$allowed_program_type = '';                  
                  
      	$channelAllowedAdTypes = explode(',', $channel_type);
      	$textAllowed = (in_array(Sppc_Channel::AD_TYPE_TEXT, $channelAllowedAdTypes)) ? true : false;
      	$imageAllowed = (in_array(Sppc_Channel::AD_TYPE_IMAGE, $channelAllowedAdTypes)) ? true : false;
      
		if ($textAllowed && !$imageAllowed) {
      		$allowed_ad_type = $channel_type;
			if ((($query_flat_rate->num_rows() > 0)) && ($slot_info['free'] > 0)) { //Предлагаем Flat Rate с типом text
				$allowed_program_type = 'Flat_Rate';
			} else { //Если Flat Rate нет, то предлагаем CPM с типом text
				if ($query_cpm->num_rows() > 0) {
					$allowed_program_type = 'CPM';
				} else {
					return NULL; //Доступных программ нет
				}
			}
      	} else if ( ($imageAllowed && !$textAllowed) ||
      				(!$imageAllowed && $textAllowed)
      			  )
      	{
      		$allowed_ad_type = $channel_type;
      		if ($slot_info['free'] != $slot_info['max']) { //Если есть CPM, то предлагаем ее т.к. image для Flat Rate уже заблокирован
      			if ($query_cpm->num_rows() > 0) {
      				$allowed_program_type = 'CPM';
      			} else { //Если CPM программ нет, то канал занят полностью
      				return NULL; //Доступных программ нет
      			}
      		} else {
      			if (($query_flat_rate->num_rows() > 0)) { //Предлагаем Flat Rate с типом image
      				$allowed_program_type = 'Flat_Rate';
      			} else { //Если Flat Rate нет, то предлагаем CPM
      				if ($query_cpm->num_rows() > 0) {
      					$allowed_program_type = 'CPM';
      				} else {
      					return NULL; //Доступных программ нет
      				}
      			}
      		}
      	} else {
      		if ($slot_info['free'] != $slot_info['max']) { //Если есть CPM, то предлагаем ее т.к. image для Flat Rate уже заблокирован
      			if ($query_cpm->num_rows() > 0) {
      				$allowed_program_type = 'CPM';
      				$allowed_ad_type = $channel_type;
      			} else { //Если CPM программ нет и есть свободные слоты, то предлагаем Flat Rate, но c типом text
      				if (($query_flat_rate->num_rows() > 0) && ($slot_info['free'] > 0)) {
      					$allowed_program_type = 'Flat_Rate';
                  		$allowed_ad_type = 'text';
      				} else {
      					return NULL; //Доступных Flat Rate программ нет
      				}
      			}
      		} else {
      	    	if (($query_flat_rate->num_rows() > 0)) { //Предлагаем Flat Rate с типом image
                  	$allowed_program_type = 'Flat_Rate';
                  	$allowed_ad_type = $channel_type;
             	} else { //Если Flat Rate нет, то предлагаем CPM
             	 	if ($query_cpm->num_rows() > 0) {
	                	$allowed_program_type = 'CPM';
	                	$allowed_ad_type = $channel_type;
             	 	} else {
             	 	 	return NULL; //Доступных программ нет
             	 	}
             	}
      		}
      	}
      
      	switch ($allowed_program_type) {
      		case 'CPM':
      	  		$program = $query_cpm->row();
      			break;
      		case 'Flat_Rate':
           		$program = $query_flat_rate->row();
         		break;
      	}
      	
      	$result = array(
      		'id_program' => $program->id_program, 
      		'ad_type' => $allowed_ad_type, 
      		'volume' => $program->volume
      	);

      	$allowedAdTypes = explode(',', $allowed_ad_type);
      	$textAllowed = (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedAdTypes)) ? true : false;
      	$imageAllowed = (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) ? true : false;
      	
      	if ($textAllowed && !$imageAllowed) {
      		$result['cost'] = $program->cost_text;
      	} else if (($imageAllowed && !$textAllowed) ||
      			   ($imageAllowed && $textAllowed))
      	{
      		$result['cost'] = $program->cost_image;
      	} 
      	
      	return $result;
   	}
   
   /**
    * Получение списка поддерживаемых каналом типов программ
    *
    * @param int $id_channel ID  канала
    * @return array массив типов програм, поддерживаемых каналом
    */
   public function get_channel_program_types($id_channel) {
   	$result = array();
   	$this->db->distinct()->select('program_type')->from($this->_table_name)->where('id_channel',$id_channel)->order_by('program_type');
   	$query = $this->db->get();
   	if ($query->num_rows() > 0) {
   		foreach ($query->result_array() as $row) {
   			$result[] = $row['program_type'];
   		}
   	}
   	return $result;
   }
   
   /**
    * Получение списка цен для заданного типа программ канала
    *
    * @param int $id_channel ID канала
    * @param int $program_type тип программы (CPM/Flat Rate)
    * @return array массив типов програм, поддерживаемых каналом
    */
   	public function get_channel_program_options($id_channel,$program_type, $ad_type = null) {
      	$result = array();
      	$this->db->select('id_program, title, volume');
      	if(!is_null($ad_type)) {
      		$allowedAdTypes = explode(',', $ad_type);
      		$costField = 'cost_text';

      		if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) {
      			$costField = 'cost_image';
      		}
      		
      		$this->db->select($costField.' as cost');
      		
      		$this->db->order_by('title','asc');	
      	}
      	
      	$this->db->from($this->_table_name)->where(
      		array(
      			'id_channel' => $id_channel, 
      			'program_type' => $program_type
      		))->order_by('title');
      		
      	$query = $this->db->get();
      	
      	if ($query->num_rows() > 0) {
         	foreach ($query->result_array() as $row) {
         		if (!is_null($ad_type)) {
         			$result[$row['id_program']] = array(
         				'title' => $row['title'], 
         				'cost' => $row['cost'], 
         				'volume' => $row['volume']
         			);
         		} else {
               		$result[$row['id_program']] = $row['title'];
         		}
         	}
      	}
      	return $result;
   	}
   
   /**
    * Получение типа программы по ее ID
    *
    * @param int $id_program ID программы
    * @return string тип программы
    */
   public function get_program_type($id_program) {
      $this->db->select('program_type')->from($this->_table_name)->where(array('id_program' => $id_program));
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->row()->program_type;
      }
      return '';
   }
   
/**
    * Получение списка программ
    *
    * @param array $params массив требуемых параметров программ
    * @return array|null - массив, содержащий список программ и их параметры
    */
   public function get_list($params) {
      if (array_key_exists('fields', $params)) {
         $this->db->select ( $params['fields'] );
      }
      
      $this->db->from ( $this->_table_name );
      
      if (array_key_exists('program_filter', $params)) {
         $this->db->where ( 'channel_program_types.program_type', $params['program_filter'] );
      }
      
      if (array_key_exists('id_channel', $params)) {
         $this->db->where ( 'channel_program_types.id_channel', $params['id_channel'] );
      }

      $this->db->join('channels', "$this->_table_name.id_channel=channels.id_channel");
      if (isset($params['ad_type_filter'])) {
         $this->db->where('channels.ad_type', $params['ad_type_filter']);
      }

      if (isset($params['sitechannel_filter'])) {
         $this->db->join('site_channels', "$this->_table_name.id_channel=site_channels.id_channel");
         $this->db->where('site_channels.id_site_channel', $params['sitechannel_filter']);
      }
      
      if (array_key_exists('order_by', $params)) {
         if (array_key_exists('order_direction', $params)) {
            $this->db->order_by ( $params['order_by'], $params['order_direction'] );
         } else {
            $this->db->order_by ( $params['order_by']);
         }
      }
      
      $query = $this->db->get();
      
      if ($query->num_rows () > 0) {
         return $query->result_array ();
      } else {
         return null; 
      }
   }
   
   public function create($params) {
      $this->db->insert($this->_table_name, $params);
   }
   
   public function update($id, $params) {
      $this->db->where($this->_id_field_name, $id);
      $this->db->update($this->_table_name, $params);
   }
   
   /**
    * Удаление программы в случае, если она не использована ни одним рекламодателем
    *
    * @param int $id - идентификатор программы
    * @return bool - true в случае успешного удаления программы
    */
   public function delete($id) {
   	
   	$this->db->where('id_program', $id);
   	$this->db->from('group_site_channels');  
   	
   	if (1 > $this->db->count_all_results()) {
         $this->db->delete($this->_table_name, array($this->_id_field_name => $id));
         return true;
   	} else {
   		return false;
   	}
      //echo $this->db->last_query();
   }
   
   /**
    * Получение информации о программе 'title','program_type','id_channel','cost_image','cost_text','volume'
    *
    * @param int $id идентификатор программы
    * @return object|null
    */
   public function get_info($id = -1) {
      $this->db->select('title, program_type, id_channel, cost_image, cost_text, volume');
      $this->db->from($this->_table_name);
      $this->db->where($this->_id_field_name, $id);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return null;
      }
   }
   
   	public function get_program_dimension($id = -1) {
   		$this->db->select('id_dimension')
   			->from($this->_table_name)
   			->join('channels', "$this->_table_name.id_channel=channels.id_channel")
   			->where($this->_id_field_name, $id);

   		$query = $this->db->get();
   		if ($query->num_rows() > 0) {
   			return (int) $query->row()->id_dimension;
   		} else {
   			return null;
   		}
   	}
}

