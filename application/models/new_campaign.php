<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Campaign_Targeting {
	public $countries = array();
	public $languages = array();
}

class Campaign_Schedule {
   	public $schedule = array();
   	public $schedule_is_set = false;
   
   	public function __construct() {
      	for ($i = 0; $i < 24*7; $i++) {
           	$this->schedule[$i] = false;
      	}
   	}
}

class Ad {
	public function __construct() {}
	
   	public $ad_type;
   	public $title;
   	public $display_url;
   	public $destination_url;
   	public $destination_protocol;
   	public $email_title;
}

class Text_Ad extends Ad {
   	public $description1;
   	public $description2;
   
   	public function __construct() {
		parent::__construct();
   		$this->ad_type = "text";
   	}
}

class Image_Ad extends Ad {
  	public $image_id; //идентификатор загруженного изображения
   	public $bgcolor; //фоновый цвет для flash-баннера
   	public $id_dimension; //идентификатор размерности загруженного изображения
   
   	public function __construct() {
      	parent::__construct();
      	$this->ad_type = "image";
   	}
}

/**
* модель для работы с XML файлом создаваемой кампании
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class New_Campaign extends CI_Model {
	//const PATH_TO_XML = '/tmp/campaign_creation/'; //На NFS могут быть проблемы с загрузкой файла parser error : Document is empty './system/files/campaign_creation/'
	
	/**
	 * ОБъект SimpleXMLElement со свойствами, соответствующими создаваемой пользователем кампании
	 *
	 * @var object
	 */
	private $campaign;
	
	/**
	 * Путь к XML-файлу с данными о создаваемой кампании
	 *
	 * @var string
	 */
	private $xml_filepath;
	
	public function __construct() {
		parent::__construct(); 
		$this->xml = NULL;
		$this->xml_filepath = '';
		$this->xml_dir = $this->config->item('path_to_campaign_creation');
	}
	
	/**
	 * Инициализация файла-хранилища временных данных создаваемой кампании
	 *
	 * @param string $id_xml_storage ID-XML файла для хранения временных данных кампании
	 * @return int код инициализации (1 - загружен существующий файл, 2 - создан новый XML-файл в RAM)
	 */
   	public function init_storage($id_xml_storage) {
   		$this->xml_filepath = $this->xml_dir.$id_xml_storage.'.xml';
	   	if (file_exists($this->xml_filepath)) {
        	$this->campaign = simplexml_load_file($this->xml_filepath);
         	return 1;
      	} else {
        	$this->campaign = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><campaign></campaign>');
         	return 2;
      	}
	}
	
   	/**
     * Удаление файла-хранилища временных данных создаваемой кампании (вызывать при успешном создании кампании или создании новой кампании)
     *
     * @param string $id_xml_storage ID-XML файла для хранения временных данных кампании
     * @return bool результат удаления файла (TRUE в случае отсутствия файла или успешного удаления)
     */
   	public function free_storage($id_xml_storage) {
   		$this->xml_filepath = $this->xml_dir.$id_xml_storage.'.xml';
   		if (file_exists($this->xml_filepath)) {
   			return unlink($this->xml_filepath);         
      	} 
      	return TRUE;
   	}
	
   	/**
     * Сохранение XML-файла, соответствующего создаваемой пользователем кампании
     *
     */
   	public function save_data() {
   		$this->campaign->asXML($this->xml_filepath);
   	}
	
   	/**
     * Функция задания бида по-умолчанию
     *
     * @param float $bid_value - значение бида по-умолчанию
     * @param string $bid_type тип бида (text или image)
     * 
     */
   	public function set_default_bid($bid_value, $bid_type) {
   		switch($bid_type) {
   			case 'text':
   				$bid_field = 'default_bid';
            	break;
            case 'image':
               $bid_field = 'default_bid_image';
            	break;
            default:
               return FALSE;
        }
  
       	if (isset($this->campaign->attributes()->$bid_field)) {
       		$this->campaign->attributes()->$bid_field = $bid_value;
       	} else {
       		$this->campaign->addAttribute($bid_field, $bid_value);
       	};
       	return TRUE;
   }
   
   	/**
     * Функция получения бида по-умолчанию
     *
     * @param string $bid_type тип бида (text или image)
     * @return float|null - значение бида по-умолчанию
     */
   	public function get_default_bid($bid_type) {
   		switch($bid_type) {
   			case 'text':
   				$bid_field = 'default_bid';
            	break;
            case 'image':
               	$bid_field = 'default_bid_image';
            	break;
            default:
            	return null;
   		}
   		
   		if (isset($this->campaign->attributes()->$bid_field)) {
   			return $this->campaign->attributes()->$bid_field;
   		} else {
   			return null;
   		};
   }
   
   
   /**
    * Функция задания группы таргетинга создаваемой кампании
    *
    * @param integer $id_targeting_group - идентификатор группы таргетинга
    * @param boolean $is_temporary_targeting_group - признак того, что группа является временной
    */
   public function set_targeting($id_targeting_group, $is_temporary_targeting_group = false) {
      if (!isset($this->campaign->targeting)) {
         $targeting = $this->campaign->addChild('targeting');           
      } else {
      	$targeting = $this->campaign->targeting;
      }
      if ($is_temporary_targeting_group) {
      	if (isset($targeting->attributes()->id_temporary_targeting_group)) {
      		$targeting->attributes()->id_temporary_targeting_group = $id_targeting_group;
      	} else {
      	   $targeting->addAttribute('id_temporary_targeting_group', $id_targeting_group);
      	}
      } else {
      	if (isset($targeting->attributes()->id_targeting_group)) {
      		$targeting->attributes()->id_targeting_group = $id_targeting_group;
      	} else {
            $targeting->addAttribute('id_targeting_group', $id_targeting_group);
      	}
      }
   }
   
   /**
    * Функция получения id группы таргетинга создаваемой кампании
    *
    * @param boolean $is_temporary_targeting_group - признак того, что группа является временной
    */
   public function get_targeting($is_temporary_targeting_group = false) {
      if (isset($this->campaign->targeting)){
         if ($is_temporary_targeting_group) {
            return (string)$this->campaign->targeting->attributes()->id_temporary_targeting_group;
         } else {
            return (string)$this->campaign->targeting->attributes()->id_targeting_group;
         }
      }else{
         return "";
      }
   }
	
	/**
	 * Задать страны кампании
	 *
	 * @param array $countries
	 */
	public function set_countries($countries) {
	   if (!isset($this->campaign->targeting)) {
         $targeting = $this->campaign->addChild('targeting');      
      } else {
      	$targeting = $this->campaign->targeting;
      }
      
      //Задание стран кампании 
      if (isset($targeting->countries)) {
         unset($targeting->countries);
      }
      
      $xml_countries = $targeting->addChild('countries');
      
      foreach ($countries as $target_country) {
         $country = $xml_countries->addChild('country');
         $country->addAttribute('iso',$target_country);
      }
	}
	
   /**
    * Задать языки кампании
    *
    * @param array $languages
    */
   public function set_languages($languages) {
      if (!isset($this->campaign->targeting)) {
         $targeting = $this->campaign->addChild('targeting');      
      } else {
      	$targeting = $this->campaign->targeting;
      }
      
      //Задание языков кампании 
      if (isset($targeting->languages)) {
         unset($targeting->languages);
      }
      $xml_languages = $targeting->addChild('languages');
       
      foreach ($languages as $target_language) {
         $language = $xml_languages->addChild('language');
         $language->addAttribute('id_language',$target_language);
      }
   }
   
   /**
    * Задание типа таргетинга (Basic/Advanced)
    *
    * @param string $type
    */
   public function set_targeting_type($type) {
      if (!isset($this->campaign->targeting)) {
         $targeting = $this->campaign->addChild('targeting');      
      } else {
         $targeting = $this->campaign->targeting;
      }
   	
   	switch ($type) {
   		case 'advanced':
   	     if (isset($targeting->attributes()->type)) {
   	     	 $targeting->attributes()->type = $type;
   	     } else {
   	     	 $targeting->addAttribute('type',$type);
   	     }
   		break;
   		
   		default:
   	     if (isset($targeting->attributes()->type)) {
             $targeting->attributes()->type = 'basic';
           } else {
             $targeting->addAttribute('type','basic');
           }
   		break;
   	}
   }
   
   /**
    * Получение типа таргетинга (Basic/Advanced)
    *
    * @param string $type
    */
   public function get_targeting_type() {
      if (!isset($this->campaign->targeting)) {
         return NULL;
      } else {
      	return (string)$this->campaign->targeting->attributes()->type;
      }
   }

   /**
    * Задать расписание кампании
    *
    * @param Campaign_Schedule $schedule
    */
   public function set_schedule($schedule_object) {
      
      if (isset($this->campaign->schedule)) {
         unset($this->campaign->schedule);
      }

      $xml_schedule = $this->campaign->addChild('schedule');      
      
      $xml_schedule->addAttribute('schedule_is_set', $schedule_object->schedule_is_set?'true':'false');
      
      if ($schedule_object->schedule_is_set) {
	      $day_num = 0;
	      $hour_num = 0;
	      $day_is_created = false;
	      foreach ($schedule_object->schedule as $hour) {
	         if($hour) {
	            if(!$day_is_created) {
	               $target_day = $xml_schedule->addChild('day');
	               $target_day->addAttribute('num',$day_num);
	               $day_is_created = true;
	            }
	            
	            $target_hour = $target_day->addChild('hour');
	            $target_hour->addAttribute('num',$hour_num);
	         }
	         $hour_num++;
	         if ($hour_num > 23) {
	            $hour_num = 0;
	            $day_num ++;
	            $day_is_created = false;
	         }
	      }
      }
   }
	
   /**
    * Функция получения таргетинга создаваемой кампании
    *
    * @return array ( 'schedule'  - array массив часов недели (начиная с 0 часов понедельника)
    *                 'shedule_is_set' - bool)
    */
   public function get_schedule() {
      $result = new Campaign_Schedule();
      
      $nodes = $this->campaign->xpath('schedule/@schedule_is_set');

      if($nodes) {
         $result->schedule_is_set = ("true" == $nodes[0]);
      } else {
      	return $result;
      }
      
      $nodes = $this->campaign->xpath('schedule/day'); 
      
      if ($nodes) {
         
         foreach ($nodes as $day) {
            $day_num = $day['num'];
            foreach ($day->children() as $hour) {
               $hour_num = $hour['num'];
               $result->schedule[$day_num*24 + $hour_num] = true;
            }  
         }
      }
      
      return $result;
   }
	
   /**
    * Функция задания типа компании
    *
    * @param array $params - параметры кампании: 'name' - название
    *                                            
    */
   public function set_type($type) {
      if (!isset($this->campaign->attributes()->name)) {
         $this->campaign->addAttribute('type',$type);
      } else {
         $this->campaign->attributes()->type = $type;
      }

   }
   
   /**
    * Функция получения типа кампании
    *
    * @return string
    */
   public function get_type() {
      $result = '';
      
      if (!isset($this->campaign->attributes()->type)) {
         $result = NULL;
      } else {
         $result = (string)$this->campaign->attributes()->type;
      }
      return $result;
   }   
   	
   /**
    * Функция задания названия и сроков проведения кампании
    *
    * @param array $params - параметры кампании: 'name' - название
    *                                            
    */
   public function set_name_date($params) {
      if (!isset($this->campaign->attributes()->name)) {
      	$this->campaign->addAttribute('name',$params['name']);
      } else {
      	$this->campaign->attributes()->name = $params['name'];
      }
      /*
      if (!isset($this->campaign->attributes()->start_date)) {
         $this->campaign->addAttribute('start_date',$params['start_date']);
      } else {
         $this->campaign->attributes()->start_date = $params['start_date'];
      }
      
      if (!isset($this->campaign->attributes()->end_date)) {
         $this->campaign->addAttribute('end_date',$params['end_date']);
      } else {
         $this->campaign->attributes()->end_date = $params['end_date'];
      }*/
   }
   
   /**
    * Функция получения названия и сроков проведения кампании
    *
    * @return array $params - параметры кампании: 'name' - название,
    *                                             'start_date' - UNIXTIMESTAMP дата начала кампании
    *                                             'end_date' - UNIXTIMESTAMP дата завершения кампании
    */
   public function get_name_date() {
   	$result = array();
   	
      if (!isset($this->campaign->attributes()->name)) {
         $result['name'] = NULL;
      } else {
         $result['name'] = (string)$this->campaign->attributes()->name;
      }
      
      /*if (!isset($this->campaign->attributes()->start_date)) {
         $result['start_date'] = NULL;
      } else {
         $result['start_date'] = (string)$this->campaign->attributes()->start_date;
      }
      
      if (!isset($this->campaign->attributes()->end_date)) {
         $result['end_date'] = NULL;
      } else {
         $result['end_date'] = (string)$this->campaign->attributes()->end_date;
      }*/
      
      return $result;
   }
   
   /**
    * Функция задания названия группы кампании
    *
    * @param string $name - название группы кампании
    */
   public function set_group_name($name) {
   	$group = $this->campaign->xpath('group');
   	if ($group) {
   		$group[0]->attributes()->name = $name;
   	} else {
   		$group = $this->campaign->addChild('group');
   		$group->addAttribute('name',$name);
   		$group->addAttribute('places','allsites');
   	}
   }
   
   /**
    * Функция получения названия группы кампании
    *
    * @return  string $name - название группы кампании
    */
   public function get_group_name() {
      $group = $this->campaign->xpath('group');
      if ($group) {
         return (string)$group[0]->attributes()->name;
      } else {
         return '';
      }
   }
   
   
   /**
    * Функция получения списка сайтов-каналов добавленных в группу кампании и выбранной схемы оплаты
    * @param array $params параметры запрашиваемых каналов ('status_filter' - 'new','old')
    * @return array (id_site_channel => id_program), содержащихся в группе кампании
    */
   public function get_sites_channels($params = array('status' => 'old')) {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $site_channels = $group->xpath('site_channels/site_channel');
         if ($site_channels) {
            $result = array();
            foreach ($site_channels as $site_channel) {
            
            	if (($params['status'] == (string)$site_channel['status']) || ($params['status'] == 'all')) {
            	  $id_site_channel = (int)$site_channel['id_site_channel'];
            	  $result[$id_site_channel] = array('id_program' => (int)$site_channel['id_program'],
            	                                    'cost' => (double)$site_channel['cost'],
            	                                    'volume' => (int)$site_channel['volume'],
                                                   'ad_type' => (string)$site_channel['ad_type'],
            	                                    'status' => (string)$site_channel['status']);	
            	}
            }
            //print_r($result);
            return $result;
         } else {
            return array();
         }
      } else {
         return array();
      }
   }
   
   public function get_sites_channels_new_cost() {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $site_channels = $group->xpath('site_channels/site_channel');
         if ($site_channels) {
            $result = 0;
            foreach ($site_channels as $site_channel) {
            
            	if (((string)$site_channel['status'] == 'new')) {
            	  $id_site_channel = (int)$site_channel['id_site_channel'];
            	  $result += (double)$site_channel['cost'];
            	}
            }
            return $result;
         } else {
            return 0;
         }
      } else {
         return 0;
      }
   }
   
   /**
    * Функция получения информации о добавленном в группу сайте-канале
    * @param integer $id_site_channel
    * @return array (id_program,cost,volume,ad_typestatus) или пустой массив, если сайт-канал не найден в группе
    */
   public function get_site_channel_info($id_site_channel) {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $site_channels = $group->xpath('site_channels/site_channel');
         if ($site_channels) {
            foreach ($site_channels as $site_channel) {
               if ($id_site_channel == (int)$site_channel['id_site_channel']) {
                 $id_site_channel = (int)$site_channel['id_site_channel'];
                  
                 return array('id_program' => (int)$site_channel['id_program'],
                                                   'cost' => (double)$site_channel['cost'],
                                                   'volume' => (int)$site_channel['volume'],
                                                   'ad_type' => (string)$site_channel['ad_type'],
                                                   'status' => (string)$site_channel['status']);   
               }
            }
            return array();
         } else {
            return array();
         }
      } else {
         return array();
      }
   }
   
   /**
    * Добавление сайта в группу (для CPC программ)
    *
    * @param integer $id_site идентификатор сайта
    * @param double|null $bid бид для сайта
    */
   public function add_site($id_site, $bid = null, $bid_image = null) {

      if (isset($this->campaign->group)) {
         if (isset($this->campaign->group->sites)) {
               foreach ($this->campaign->group->sites->children() as $site) {
                  if ($id_site == $site['id_site']) {
                     if (is_null($bid)) {
                        if (isset($site->attributes()->bid)) {
                           unset($site->attributes()->bid);
                        }
                     } else {
                        if (isset($site->attributes()->bid)) {
                           $site->attributes()->bid = $bid;
                        } else {
                           $site->addAttribute('bid',$bid);
                        }
                     }
                     if (is_null($bid_image)) {
                        if (isset($site->attributes()->bid_image)) {
                           unset($site->attributes()->bid_image);
                        }
                     } else {
                        if (isset($site->attributes()->bid_image)) {
                           $site->attributes()->bid_image = $bid_image;
                        } else {
                           $site->addAttribute('bid_image',$bid_image);
                        }
                     }
                     return TRUE;
                  }
               }
         } else {
            $this->campaign->group->addChild('sites');
         }
        
         $site = $this->campaign->group->sites->addChild('site');
         $site->addAttribute('id_site', $id_site);
         if (!is_null($bid)) {
            $site->addAttribute('bid', $bid);
         }        
         if (!is_null($bid_image)) {
            $site->addAttribute('bid_image', $bid_image);
         }     

         
         return TRUE;
      } else {
         return FALSE; //групп в кампании нет
      }
   }
   
   /**
    * Обновление бида для сайта
    *
    * @param integer $id_site
    * @param float|null $bid
    * @param string $bid_type тип обновляемого бида (text или image) 
    * @return bool
    */
   public function update_site_bid($id_site, $bid, $bid_type) {
   	switch ($bid_type) {
   		case 'text':
   			$bid_field = 'bid';
   		break;
   		case 'image':
   			$bid_field = 'bid_image';
   		break;
   		default:
   			return FALSE;
   	}
   	
      if (isset($this->campaign->group)) {
         if (isset($this->campaign->group->sites)) {
               foreach ($this->campaign->group->sites->children() as $site) {
                  if ($id_site == $site['id_site']) {
                  	if (is_null($bid)) {
                  		if (isset($site->attributes()->$bid_field)) {
                  			unset($site->attributes()->$bid_field);
                  		}
                  	} else {
	                  	if (isset($site->attributes()->$bid_field)) {
	                  		$site->attributes()->$bid_field = $bid;
	                  	} else {
	                  		$site->addAttribute($bid_field,$bid);
	                  	}
                  	}
                     return TRUE;
                  }
               }
               return TRUE;
         } else {
            return FALSE; //Сайт не найден
         }
      } else {
         return FALSE; //групп в кампании нет
      }
   }
   
   
   /**
    * Функция удаления сайта-канала из списока каналов группы CPC кампании
    *
    * @param int $id_site ID сайта, удаляемого из группы кампании
    */
   public function del_site($id_site) {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $sites = $group->xpath('sites');
         if ($sites) {
            $sites = $sites[0];
            $sites_nodes = $sites->xpath('site');
            foreach ($sites_nodes as $key => $site) {
               if ($id_site == $site['id_site']) {
                  unset($this->campaign->group->sites->site[$key]);
                  break;
               }
            }
         }
      } 
   }
   
   /**
    * Удаляет все сайты из группы CPC-кампании
    */
   public function del_sites(){
   	  unset($this->campaign->group->sites);
   } //end del_sites
   
   /**
    * Функция получения списка сайтов добавленных в группу CPC кампании и заданного бида
    * @return array (id_site => array('bid' => bid_value, 'bid_image' => bid_image_value))
    */
   public function get_sites() {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $sites = $group->xpath('sites/site');
         if ($sites) {
            $result = array();
            foreach ($sites as $site) {
                 $id_site = (int)$site->attributes()->id_site;

                 if (isset($site->attributes()->bid)) {
                     $result[$id_site] = array('bid' => $site->attributes()->bid);	
                 } else {
                 	   $result[$id_site] = array('bid' => NULL);
                 }
                 
                 if (isset($site->attributes()->bid_image)) {
                     $result[$id_site]['bid_image'] = $site->attributes()->bid_image;  
                 } else {
                     $result[$id_site]['bid_image'] = NULL;
                 }
            }
            //print_r($result);
            return $result;
         } else {
            return array();
         }
      } else {
         return array();
      }
   }
   
   
   
   /**
    * Функция добавления сайта-канала в список сайтов-каналов группы кампании
    *
    * @param array $params параметры сайта-канала, добавляемого в группу кампании ('id_site_channel' - ID сайта-канала ,
    *                      'id_program' - ID программы,
    *                      'cost' - стоимость рекламного пактета,
    *                      'volume' - объем рекламного пакета,
    *                      'ad_type' - выбранный тип объявлений,
    * @return bool результат добавления канала в группу кампании (TRUE в случае успеха)
    */
   public function add_site_channel($site_channel_params, $overwrite_existing = true) {
      if (isset($this->campaign->group)) {
         if (isset($this->campaign->group->site_channels)) {
               foreach ($this->campaign->group->site_channels->children() as $site_channel) {
                  if ($site_channel_params['id_site_channel'] == $site_channel['id_site_channel']) {
                     if ($overwrite_existing) {	
                       $site_channel['id_program'] = $site_channel_params['id_program'];
                       $site_channel['cost'] = $site_channel_params['cost'];
                       $site_channel['volume'] = $site_channel_params['volume'];
                       $site_channel['ad_type'] = $site_channel_params['ad_type'];
                     }
                     return TRUE;
                  }
               }
         } else {
            $this->campaign->group->addChild('site_channels');
         }
        
         $site_channel = $this->campaign->group->site_channels->addChild('site_channel');
         $site_channel->addAttribute('id_site_channel',$site_channel_params['id_site_channel']);
         $site_channel->addAttribute('id_program',$site_channel_params['id_program']);
         $site_channel->addAttribute('cost',$site_channel_params['cost']);
         $site_channel->addAttribute('volume',$site_channel_params['volume']);
         $site_channel->addAttribute('ad_type',$site_channel_params['ad_type']);
         $site_channel->addAttribute('status','new');
         
         return TRUE;
      } else {
         return FALSE; //групп в кампании нет
      }
   }
   
   /**
    * Функция удаления сайта-канала из списока сайтов-каналов группы кампании
    *
    * @param int $id_site_channel ID сайта-канала, удаляемого из группы кампании
    */
   public function del_site_channel($id_site_channel) {
      $group = $this->campaign->xpath('group');
      if ($group) {
         $group = $group[0];
         $site_channels = $group->xpath('site_channels');
         if ($site_channels) {
            $site_channels = $site_channels[0];
            $site_channels_nodes = $site_channels->xpath('site_channel');
            foreach ($site_channels_nodes as $key => $site_channel) {
               if ($id_site_channel == $site_channel['id_site_channel']) {
               	unset($this->campaign->group->site_channels->site_channel[$key]);
                  break;
               }
            }
         }
      } 
   }
   
   
   /**
    *  Функция обновления статуса сайтов-каналов на 'old'
    */
   public function apply_sites_channels() {
      if (isset($this->campaign->group->site_channels)) {
         foreach ($this->campaign->group->site_channels->children() as $site_channel) {
            $site_channel['status'] = 'old';
         }
         return TRUE;
      } else {
         return FALSE; //групп в кампании нет
      }
   }
   
   /**
    * Получение ограничения на максимальное число ежедневных показов
    *
    */
   public function get_daily_impressions() {
      if(isset($this->campaign->group)) {
      	if (isset($this->campaign->group['daily_impressions'])) {
      	  return $this->campaign->group['daily_impressions'];
      	} else {
      		return NULL;
      	}
      }
      else return NULL;
   } //end get_daily_impressions
   
   /**
    * Установка ограничения на максимальное число ежедневных показов
    *
    * @params int $value величина ограничения
    */
   public function set_daily_impressions($value) {
   	if(isset($this->campaign->group)) {
         if (!isset($this->campaign->group['daily_impressions'])) {
           $this->campaign->group->addAttribute('daily_impressions',$value);
         } else {
           $this->campaign->group['daily_impressions'] = $value;
         }
      }
   } //end set_daily_impressions

   /**
    * Получение типа выбранной программы (CPC, Flat Rate, CPM)
    *
    */
   public function get_program_type() {
      if(isset($this->campaign->group)) {
         if (isset($this->campaign->group['program_type'])) {
           return $this->campaign->group['program_type'];
         } else {
            return NULL;
         }
      }
      else return NULL;
   } //end get_program_type
   
   /**
    * Установка типа выбранной программы (CPC, Flat Rate, CPM)
    *
    * @params string $value величина типа выбранной программы
    */
   public function set_program_type($value) {
      if(isset($this->campaign->group)) {
         if (!isset($this->campaign->group['program_type'])) {
           $this->campaign->group->addAttribute('program_type',$value);
         } else {
           $this->campaign->group['program_type'] = $value;
         }
      }
   } //end set_program_type
   
   /**
    * Получение ограничения на ежедневный бюджет
    *
    */
   public function get_daily_budget() {
      if(isset($this->campaign->group)) {
         if (isset($this->campaign->group['daily_budget'])) {
           return $this->campaign->group['daily_budget'];
         } else {
            return NULL;
         }
      }
      else return NULL;
   } //end get_daily_clicks
   
   /**
    * Установка ограничения на ежедневный бюджет
    *
    * @params int $value величина ограничения
    */
   public function set_daily_budget($value) {
      if(isset($this->campaign->group)) {
         if (!isset($this->campaign->group['daily_budget'])) {
         	if (!is_null($value)) {
               $this->campaign->group->addAttribute('daily_budget',$value);
         	}
         } else {
         	if (!is_null($value)) {
               $this->campaign->group['daily_budget'] = $value;
         	} else {
         		unset($this->campaign->group['daily_budget']);
         	}
         }
      }
   } //end set_daily_clicks
   
  	/**
     * Получение объекта-объявления
     *
     * @param int $id идентификатор объявления
     * @return Ad|NULL объявление или NULL если оно не найдено
     */
   	public function get_ad($id) {
   		$ads = $this->campaign->xpath('ads/ad');
   		if($ads) {
   			foreach ($ads as $ad) {
   				if ($ad->attributes()->id == $id) {
   					if ("text" == $ad->attributes()->ad_type) {
   						$result = new Text_Ad();
   						$result->title = (string) $ad->attributes()->title;
   						$result->description1 = (string) $ad->attributes()->description1;
   						$result->description2 = (string) $ad->attributes()->description2;
   						$result->display_url = (string) $ad->attributes()->display_url;
   						$result->destination_url = (string) $ad->attributes()->destination_url;
   						$result->destination_protocol = (string) $ad->attributes()->destination_protocol;  
   						$result->email_title = (string) $ad->attributes()->email_title;
   						return $result;
   					} elseif('image' == $ad->attributes()->ad_type) {
   						$result = new Image_Ad();
   						$result->title = (string) $ad->attributes()->title;
   						$result->display_url = (string) $ad->attributes()->display_url;
   						$result->destination_url = (string) $ad->attributes()->destination_url;
   						$result->destination_protocol = (string) $ad->attributes()->destination_protocol;
   						$result->image_id = (string) $ad->attributes()->image_id;
   						$result->bgcolor = (string) $ad->attributes()->bgcolor;
   						$result->id_dimension = (string) $ad->attributes()->id_dimension;
   						return $result;
   					} 
   				}
   			}
   			return NULL;
   		} else {
   			return NULL;
   		}
   	}
   
   /**
    * Получение самого последнего объекта-объявления заданного типа
    *
    * @param int $id идентификатор объявления
    * @return Ad|NULL объявление или NULL если оно не найдено
    */
   public function get_last_ad($ad_type) {
      $ads = $this->campaign->xpath('ads/ad');
      $result = NULL;
      if($ads) {
         foreach ($ads as $ad) {
            if ($ad['ad_type'] == $ad_type) {
               switch ($ad_type) {
               	case "text":
	               	$result = new Text_Ad();
	                  $result->title = $ad['title'];
	                  $result->description1 = $ad['description1'];
	                  $result->description2 = $ad['description2'];
	                  if (isset($ad['email_title'])) {
	                    $result->email_title = $ad['email_title'];
	                  }	                  
	                  $result->display_url = $ad['display_url'];
	                  $result->destination_url = $ad['destination_url'];
	                  $result->destination_protocol = $ad['destination_protocol'];
               	break;
               	case "image":
               	   $result = new Image_Ad();
                     $result->title = $ad['title'];
                     $result->display_url = $ad['display_url'];
                     $result->destination_url = $ad['destination_url'];
                     $result->destination_protocol = $ad['destination_protocol'];
                     $result->id_dimension = $ad['id_dimension'];
                     $result->image_id = $ad['image_id'];
                     $result->bgcolor = $ad['bgcolor'];
                  break;
               }
            }
         }
         return $result;
         
      } else {
         return $result;
      }
   }
   
   	/**
     * Добавление объекта-объявления
     *
     * @param int $id идентификатор объявления
     * @param Ad $ad_info объявление
     */
   	public function set_ad($id,$ad_info) {
      	if (isset($this->campaign->ads)) {
      		$ads = $this->campaign->ads;
      	} else {
      		$ads = $this->campaign->addChild('ads');
      	}
      
      	$ads = $this->campaign->xpath('ads/ad');
      	if($ads) { 
         	foreach ($ads as $key => $ad) {
         		//для текстовых объявлений - проверка на существование объявления с такими же значениями полей
         		if ('text' == $ad_info->ad_type) {
         			if (($ad['title'] == (string) $ad_info->title) &&
         		    	($ad['display_url'] == (string) $ad_info->display_url) &&
         		    	($ad['destination_url'] == (string) $ad_info->destination_url) &&
         		    	($ad['destination_protocol'] == (string) $ad_info->destination_protocol) &&
         		    	($ad['description1'] == (string) $ad_info->description1) &&
                        ($ad['email_title'] == (string) $ad_info->email_title) &&
         		    	($ad['description2'] == (string) $ad_info->description2))
         		    {
         				return;
         		    }
         		}

            	if ($id == $ad['id']) {
               		unset($this->campaign->ads->ad[$key]); //удаление старого объявления с заданным идентификатором
               		break;
            	}
         	}
      	}
      
      	$ad = $this->campaign->ads->addChild('ad');
      	$ad->addAttribute('id',$id);
      	$ad->addAttribute('ad_type',$ad_info->ad_type);
      	$ad->addAttribute('title',$ad_info->title);
      	$ad->addAttribute('display_url',$ad_info->display_url);
      	$ad->addAttribute('destination_url',$ad_info->destination_url);
      	$ad->addAttribute('destination_protocol',$ad_info->destination_protocol);
      
      	if ("text" == $ad_info->ad_type) {
        	$ad->addAttribute('description1',$ad_info->description1);
         	$ad->addAttribute('description2',$ad_info->description2);
         	$ad->addAttribute('email_title',$ad_info->email_title);
      	} elseif("image" == $ad_info->ad_type){
         	if (!preg_match("/#([A-F0-9]{6}|[A-F0-9]{3})/i", $ad_info->bgcolor)) {
            	$ad_info->bgcolor = '';
         	}
         	$ad->addAttribute('image_id',$ad_info->image_id);
         	$ad->addAttribute('bgcolor',$ad_info->bgcolor);
         	$ad->addAttribute('id_dimension',$ad_info->id_dimension);
         
      	} 
   	}
   
   	private function addCData(&$node,$cdata_text){
       	$node = dom_import_simplexml($node);
       	$no = $node->ownerDocument;
       	$node->appendChild($no->createCDATASection($cdata_text));
   	} 
   
   	/**
     * Удаление объекта-объявления
     *
     * @param int $id идентификатор объявления
     */
   	public function del_ad($id) {
      	$ads = $this->campaign->xpath('ads/ad');
	   	if($ads) { //удаление старого объявления с заданным идентификатором
	      	foreach ($ads as $key => $ad) {
	         	if ($id == $ad->attributes()->id) {
	            	unset($this->campaign->ads->ad[$key]);
	            	break;
	         	}
	      	}
	   	}
  	}
   
   	/**
     * Получение списка объявлений для создаваемой кампании
     */
   	public function get_ads_list() {
   		$result = array();
      	$ads = $this->campaign->xpath('ads/ad');
      	if($ads) {
         	foreach ($ads as $key => $ad) {
         		if ('text' == $ad->attributes()->ad_type) {
               		$ad_info = new Text_Ad(); 
               		$ad_info->description1 = (string) $ad->attributes()->description1;
               		$ad_info->description2 = (string) $ad->attributes()->description2;
               		$ad_info->email_title = (string) $ad->attributes()->email_title;
            	} elseif('image' == $ad->attributes()->ad_type) { //image
            		$ad_info = new Image_Ad(); 
               		$ad_info->image_id = (string) $ad->attributes()->image_id;
               		$ad_info->bgcolor = (string) $ad->attributes()->bgcolor;
               		$ad_info->id_dimension = (int) $ad->attributes()->id_dimension;
            	} 
            
            	$ad_info->title = (string) $ad->attributes()->title;
            	$ad_info->display_url = (string) $ad->attributes()->display_url;
            	$ad_info->destination_url = (string) $ad->attributes()->destination_url;
            	$ad_info->destination_protocol = (string) $ad->attributes()->destination_protocol; 
               
            	$result[(string) $ad->attributes()->id] = $ad_info;
         	}
      	}
      	return $result;
   	}
   
   /**
    * Получение списка изображений и их размерностей, загруженных при создании текущей кампании
    */
   public function get_images_list() {
      $result = array();
      $images = $this->campaign->xpath('images/image');
      if($images) { 
         foreach ($images as $key => $image) {
            $result[(string)$image->attributes()->id_image] = (integer)$image->attributes()->id_dimension;
         }
      }
      return $result;
   }
   
   	/**
     * Определение стоимости кампании в соответствии с выбранными программами
     *
     */
   	public function get_campaign_cost() {
   		$campaign_cost = 0;
   	
      	$group = $this->campaign->xpath('group');
      	if ($group) {
         	$group = $group[0];
         	$site_channels = $group->xpath('site_channels/site_channel');
         	if ($site_channels) {            
            	foreach ($site_channels as $site_channel) {
            	  	$campaign_cost+= (double)$site_channel['cost'];
            	}

            	return $campaign_cost;
         	} else {
            	return $campaign_cost;
         	}
      	} else {
         	return $campaign_cost;
      	}
   	}
   
   	/**
     * Определение стоимости каждой отдельной программы в кампании
     *
     */
   	public function get_campaign_detailed_cost() {
      	$result = null;
      
      	$group = $this->campaign->xpath('group');
      	if ($group) {
         	$group = $group[0];
         	$site_channels = $group->xpath('site_channels/site_channel');
         	if ($site_channels) {
         		foreach ($site_channels as $site_channel) {
         			if ('new' == (string)$site_channel['status']) {
         				$id_site_channel = (int)$site_channel['id_site_channel'];
         				$cost = (double)$site_channel['cost'];
         				$volume = (int)$site_channel['volume'];
         				$ad_type = (string)$site_channel['ad_type'];
         				$id_program = (int)$site_channel['id_program'];
	                    
	                 	$info = array(
	                 		'id_site_channel' => $id_site_channel, 
	                 		'cost' => $cost, 
	                 		'volume' => $volume,
	                 		'ad_type' => $ad_type, 
	                 		'id_program' => $id_program, 
	                 	);
	                 
	                 	$result[] = $info;
                 	}
              	}
         	} 
      }
      return $result;
   	}
   
   	/**
     * Метод проверки рекламных объявлений
     */
   	public function check_ads() {
   	    // Проверяем объявления
      	$ads = $this->get_ads_list();
      	
   		if (0 == count($ads)) {
         	return __('At least one Ad is required');
      	}
      	
      	$CI = &get_instance();
      	$CI->load->model('channel_program');
      	
      	// Собираем размеры картиночных объявлений и попутно анализируем ситуацию с объявлениями
      	$image_dimensions = array();
      	$num_images = 0;
      	$num_texts = 0;
      	
      	foreach ($ads as $ad) {
      		switch ($ad->ad_type) {
      			case 'text':
      				$num_texts++;
      				break;
      			case 'image':
      				if (!in_array($ad->id_dimension, $image_dimensions)) {
               			array_push($image_dimensions, $ad->id_dimension);
            		}
            		$num_images++;
            		break;
      		}
      	}
      	
      	// Получаем список программ
      	$programs = $this->get_sites_channels(array('status' => 'all'));
      	
      	// Check each program requirements
      	foreach ($programs as $program) {
      		$programAdTypes = explode(',', $program['ad_type']);
      		$programSatisfied = false;
      		
      		if (in_array('text', $programAdTypes)) {
      			if (($num_texts == 0) && (count($programAdTypes) == 1)) {
      				return __('At least one Text Ad is required');
      			}
      			
      			if ($num_texts > 0) {
      				$programSatisfied = true;
      			}
      		}
      		
      		if ((in_array('image', $programAdTypes)) && (false == $programSatisfied)) {
      			if (($num_images == 0) && (count($programAdTypes) == 1)) {
      				return __('At least one Image Ad is required');
      			}
      			
      			$programDimension = $CI->channel_program->get_program_dimension($program['id_program']);
      			if (($programDimension) && (in_array($programDimension, $image_dimensions))) {
      				$programSatisfied = true;
      			} else {
      				if (count($programAdTypes) == 1) {
      					return __('You must create Image Ads for all dimensions');
      				}
      			}
      		}
      		      		
      		if (false == $programSatisfied) {
      			return __('You have to create at least one suitable ad for each selected channel');
      		}
      	}
      
      	return '';
   	}
   	   	
	/**
	 * Сохраняет список мест, где будет показываться реклама
	 * 
	 * @param array $places массив со списком мест
	 */
   	public function set_places($places){

   	   if(isset($this->campaign->group->attributes()->places)) {
   	   	  $this->campaign->group->attributes()->places = implode(',', $places);
   	   } else {
   	      $this->campaign->group->addAttribute('places', $places);
   	   }
   	   
   	   if(!in_array('sites', $places)) {
   	   	  $this->del_sites();
   	   }
   	} //end set_places

  /**
	* Возвращает список мест, где будет показываться реклама
	* 
	* @return array массив со списком мест
	*/
	public function get_places(){
	   
	   if(!isset($this->campaign->group)){
	       return array();
	   }
	
	   $attr = $this->campaign->group->attributes();
	   if(isset($attr->places)) {
	   	  $places = $this->campaign->group->attributes()->places;
	   	  if ($places == '' || is_null($places)) {
	   	  	 return array();
	   	  }
	      return explode(',', $places);
	   }
	   return array();
	} //end get_places

	/**
	 * Возвращает имя вкладки, на которую произойдет возврат после редактирования
	 */
	public function get_tab() {
	   if(isset($this->campaign->group->attributes()->tab)) {
	   	  return $this->campaign->group->attributes()->tab;
	   }
	   return 'summary';		
	} //get_tab
	
	/**
	 * Сохраняет имя вкладки, на которую произойдет возврат после редактирования
	 */
	public function set_tab($tab) {
	   if(isset($this->campaign->group->attributes()->tab)) {
	   	  $this->campaign->group->attributes()->tab = $tab;
	   } else {
	      $this->campaign->group->addAttribute('tab', $tab);
	   }
	} //set_tab
	
	
   /**
    * Добавление временного изображения,
    * когда, например, заполняются не все поля и шаг create ad 
    * перезагружается.
    *
    * @param int $id - имя изображения 
    */
   public function set_temp_img($img_name, $img_w, $img_h) {

      if (isset($this->campaign->img_temp)) {
         $img_temp = $this->campaign->img_temp;
         $img_temp->attributes()->src = $img_name;
         $img_temp->attributes()->width = $img_w;
         $img_temp->attributes()->height = $img_h;
      } else {
         $img_temp = $this->campaign->addChild('img_temp');
         $img_temp->addAttribute('src',$img_name);
         $img_temp->addAttribute('width',$img_w);
         $img_temp->addAttribute('height',$img_h);
      }
      
   }

   /**
    * Получить временное изображения
    *
    * @return string
    */
   public function get_temp_img() {

      if (isset($this->campaign->img_temp) &&
             isset($this->campaign->img_temp->attributes()->src)) {
         return array(
                      'src' => $this->campaign->img_temp->attributes()->src,
                      'width' => $this->campaign->img_temp->attributes()->width,
                      'height' => $this->campaign->img_temp->attributes()->height
               );
      } else {
         return null;
      } 
      
   }
	
   /**
    * Удалить элемент - временное изображение
    *
    * @return void
    */
   public function remove_temp_img() {

      if (isset($this->campaign->img_temp)) {
         unset($this->campaign->img_temp);
      } else {
         return null;
      } 
      
   }
	
   /**
    * Sets group categories
    *
    * @param array $categories Array with category id
    * @return bool
    */
   public function set_categories($categories = array()) {
      if (isset($this->campaign->group)) {
         if (isset($this->campaign->group->categories)) {
            unset($this->campaign->group->categories);
         }
         $categoriesNode = $this->campaign->group->addChild('categories');
         foreach($categories as $category) {
            $categoriesNode->addChild('category', $category);
         }

         return false;
      }
   } //end set_categories()
   
   /**
    * Return group categories
    *
    * @return array
    */
   public function get_categories() {
      $categories = array();
      if (isset($this->campaign->group->categories->category)) {
         foreach($this->campaign->group->categories->category as $category) {
            $categories[] = (int) $category;
         }
}
      return $categories;
   } //end get_categories()

} //end class new_campaign
