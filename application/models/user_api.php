<?php
if (!defined('BASEPATH'))
   exit('No direct script access allowed');

/**
 *  класс для работы с user_api     
 * 
 * @author Evgenii Cherenov
 * @project SmartPPC6
 * @version 1.0.0
 */
class User_api extends CI_Model {
  /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */  
   public function __construct() {
      parent::__construct();
   }
   /**
   * возвращает список ролей пользователей
   *
   * @return array список цветовых схем
   */    
	public function get_color_schemes($id_publisher=null) {
	  $fields = array('id_color_scheme',
	                  'color_schemes.name as color_schemes_name',
	                  'border_color',
	                  'background_color',
	                  'title_color',
	                  'title_id_font',
	                  'title_font.name as title_font_name',
	                  'title_font_size',
	                  'title_font_style',
	                  'title_font_weight',
	                  'text_color',
                     'text_id_font',
                     'text_font.name as text_font_name',
                     'text_font_size',
                     'text_font_style',
                     'text_font_weight',
	                  'url_color',
                     'url_id_font',
                     'url_font.name as url_font_name',
                     'url_font_size',
                     'url_font_style',
                     'url_font_weight',
	                 'IF(id_entity_publisher,false,true) as is_system'
	  );
          $this->db->select($fields)
              ->from('color_schemes')
              ->join('fonts as title_font','title_font.id_font = color_schemes.title_id_font','left')
              ->join('fonts as text_font','text_font.id_font = color_schemes.text_id_font','left')
              ->join('fonts as url_font','url_font.id_font = color_schemes.url_id_font','left')
              ->where('id_entity_publisher', 0); 
             $query = $this->db->get();
             if ($query->num_rows() > 0) {
                return $query->pdo_results;
             } else {
                return null;
            }
    }
		  
   /**
   * возвращает список ролей пользователей
   *
   * @return array список сайтов и каналов
   */   
	public function get_sites_channels($id_publisher=null,$id_site=null) {
	   $fields = array('s.id_site', 
	                 'id_entity_publisher',
	                 's.name as site_name', 
	                 's.description', 
	                 'c.id_channel',
	                 'c.name as channel_name', 					 
	                 'c.description', 
	                 'd.id_dimension', 
	                 'd.width', 
	                 'd.height'
	   );
      if ((isset($id_publisher)) and ($id_publisher!=null)){
	         $this->db->select($fields)
                     ->from('sites as s')
                     ->join('site_channels as sc','sc.id_site = s.id_site','left')
                     ->join('channels as c','c.id_channel = sc.id_channel','left')
                     ->join('dimensions as d','d.id_dimension = c.id_dimension','left')
                  ->where('s.id_entity_publisher', $id_publisher);
         if (isset($id_site) and ($id_site!=null)){         
            $this->db->where('s.id_site', $id_site);
         }
         $this->db->where('sc.id_channel IS NOT NULL')
                  ->where('s.status','active')
                  ->where('sc.status','active')
                  ->where('c.status','active')
                     ->order_by('s.id_site');
       	$query = $this->db->get();
        //echo $this->db->last_query();
         if ($query->num_rows() > 0) {
              return $query->pdo_results;
         } 
	   }
	   return null;
	}	
   
   /**
   * возвращает статистику сайтов и каналов
   *
   * @return array статистику сайтов и каналов
   */  
	public function get_site_stat($id_site=null,$period='today') {
	   $fields = array('s.id_site',
	                 'c.id_channel',
                    'SUM(ssc.clicks)AS clicks',
                    'SUM(ssc.impressions) AS impressions',
                    'SUM(ssc.alternative_impressions) AS alternative_impressions',
                    'SUM(ssc.earned_admin) AS earned_admin');
      if ($id_site){
         switch ($period) {
            case 'today': //от начала суток до настоящего момента времени
               $start_date = mktime(0, 0, 0, date("m"), date("d"), date("Y")); 
               $end_date = mktime();
               break;
            case 'yesterday': //за весь вчерашний день
               $start_date = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")); 
               $end_date = mktime(0, 0, -1, date("m"), date("d"), date("Y"));
               break;
            case 'lastweek': //за последнюю полную неделю
               $firstWeekDay = 2; //с воскресенья
               if ($firstWeekDay == 2) {
                  $start_date = mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y"));
                  $end_date = mktime(0, 0, -1, date("m"), date("d") - date("w") + 1, date("Y"));
               } else {
                  $start_date = mktime(0, 0, 0, date("m"), date("d") - date("w") - 7, date("Y"));
                  $end_date = mktime(0, 0, -1, date("m"), date("d") - date("w"), date("Y")); 
               }
               break;
            case 'lastbusinessweek': //за последнюю полную рабочую неделю 
               $start_date = mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y"));
               $end_date = mktime(0, 0, -1, date("m"), date("d") - date("w") - 1, date("Y"));
               break;
            case 'thismonth': //от начала текущего месяца до настоящего момента времени 
               $start_date  = mktime(0, 0, 0, date("m"), 1, date("Y"));
               $end_date = mktime();
               break;
            case 'lastmonth': //за весь предыдущий месяц
               $start_date = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
               $end_date = mktime(0, 0, -1, date("m"), 1, date("Y"));
               break;
            case 'all': //от начала Open Orbit Ad Server - эпохи до настоящего момента времени 
               $start_date = mktime(0, 0, 0, 1, 8, 2010);
               $end_date = mktime();
               break;
            default: //от начала суток до настоящего момента времени
               $start_date = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
               $end_date = mktime();
               break;
         }
         //var_dump(array($start_date,$end_date));
         $this->db->select($fields)
               ->from('sites as s')
               ->join('stat_sites_channels as ssc','ssc.id_site = s.id_site','left')
               ->join('channels as c','c.id_channel = ssc.id_channel','left')
               ->where('s.id_site', $id_site)
               ->where('ssc.id_channel IS NOT NULL')
               ->where('s.status','active')
               ->where('c.status','active')
               ->where('ssc.stat_date >= ',date('Y-m-d',$start_date))
               ->where('ssc.stat_date <= ',date('Y-m-d',$end_date))
               ->group_by('c.id_channel')
               ->order_by('s.id_site,c.id_channel');
         $query = $this->db->get();
         if ($query->num_rows() > 0) {
           return $query->pdo_results;
         } 
	   }
	   return null;
	}	
   
   
   /**
   * возвращает список ролей пользователей
   *
   * @return id пользователя по ключу
   */  
   function searchUser ($apiKey){
      $this->db->select('id_entity')
               ->from('settings')
               ->where('name','apiKey')
               ->where('value',$apiKey);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {   
         $row = $query->row();
         return $row->id_entity;
      } else {
        return null;
      }
   }

    function create_site ($id, $url, $name, $desc, $cat_id = null) {
        $sites = $this->db->select('id_site')
                 ->where('url',$url)
                 ->get('sites');
        if ($sites->num_rows() > 0){
            $sites = $sites->row();
            $id_site = $sites->id_site;
        } else {
            if (is_null($cat_id)) {
                //get random category
                $cat = $this->db->select('id_category')
                         ->where('id_category_parent', 1)
                         ->order_by('rand()')
                         ->limit(1)
                         ->get('categories');
                if ($cat->num_rows() > 0){
                    $cat = $cat->row();
                    $cat_id = $cat->id_category;
                }
            }
            //create site
            $data = array(
                'id_entity_publisher' => $id,
                'url' => $url,
                'name' => $name,
                'description' => $desc,
                'status' => 'active',
            );
            $this->db->insert('sites', $data);
            $id_site = $this->db->insert_id();

            //add site to category
            $data = array (
                'id_site' => $id_site,
                'id_category' => $cat_id
            );
            $this->db->insert('site_categories', $data);
        }
        return $id_site;
    }//end create_site()
   /**
    * Создание канала
    *
    * @param array $params параметры канала
    * @return int|string ID созданного канала либо текст ошибки
    */
    public function create_channel($params) {
        $main_table_params = array();
        $created_channel_id = -1;

        $main_table_params['name'] = $params['name'];
        $main_table_params['id_parent_site'] = $params['id_parent_site'];
        $main_table_params['description'] = $params['description'];
        $main_table_params['id_dimension'] = $params['id_dimension'];
        
        $main_table_params['ad_type'] = $params['ad_type'];
        $main_table_params['ad_sources'] = $params['ad_sources'];
        $main_table_params['channel_type'] = 'contextual';
        $main_table_params['ad_settings'] = 'blank';
        $main_table_params['creation_date'] = type_to_str(time(),'databasedatetime');

        $this->db->insert('channels', $main_table_params);
        $created_channel_id = $this->db->insert_id();

        $this->db->insert('site_channels', array('id_site' => $params['id_parent_site'], 'id_channel' => $created_channel_id, 'status' => 'active'));
        $this->db->insert('tags', array('id_tag' => $created_channel_id, 'code' => ''));
        return $created_channel_id;
    }
}