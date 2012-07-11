<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с объявлениями
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Ads extends CI_Model {
 
   
   /** 
   * конструктор класса, инициализация базового класса
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::__construct();
   } //end Ads
   
   /**
   * возвращает отфильтрованный список объявлений для заданной группы 
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param int $id_ignored_group игнорируемая группа
   * @return array массив с данными объявлений (id => (title, status, spent, impressions, clicks, ctr, type)) 
   */   
   public function select($id_group, $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group = 0,$ad_types = array()) {
      return $this->_select($id_group, 'group', $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group,$ad_types);
   } //end select
   
   /**
   * возвращает отфильтрованный список объявлений для заданной кампании 
   *
   * @param integer $id_campaign уникальный код выбранной кампании
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param int $id_ignored_group игнорируемая группа
   * @return array массив с данными объявлений (id => (title, status, spent, impressions, clicks, ctr, type)) 
   */   
   public function select_by_campaign($id_campaign, $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group = 0,$ad_types = array()) {
      return $this->_select($id_campaign, 'campaign', $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group,$ad_types);
   } //end select
   
   /**
   * возвращает отфильтрованный список объявлений для заданного рекламодателя 
   *
   * @param integer $id_advertiser уникальный код рекламодателя
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param int $id_ignored_group игнорируемая группа
   * @return array массив с данными объявлений (id => (title, status, spent, impressions, clicks, ctr, type)) 
   */   
   public function select_by_advertiser($id_advertiser, $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group = 0,$ad_types = array()) {
      return $this->_select($id_advertiser, 'advertiser', $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group,$ad_types);
   } //end select
   
   /**
   * возвращает отфильтрованный список объявлений
   *
   * @param integer $id уникальный код
   * @param integer $field к чему этот код относится: group, campaign, advertiser
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param int $id_ignored_group игнорируемая группа
   * @return array массив с данными объявлений (id => (title, status, spent, impressions, clicks, ctr, type)) 
   */   
   private function _select($id, $field, $page, $per_page, $sort_field, $sort_direction, $filt, $range, $id_ignored_group = 0,$ad_types) {
      $this->db->select('
      	 ads.id_ad, 
      	 ads.title, 
      	 ads.status, 
      	 SUM(spent) AS spent,
      	 SUM(impressions) AS impressions, SUM(clicks) AS clicks,
      	 SUM(clicks)*100/SUM(impressions) AS ctr, 
      	 ad_types.name', 
      	 FALSE
      );
      $this->db->from('ads');
      $this->db->join(
         'stat_ads', 'ads.id_ad = stat_ads.id_ad AND (stat_date BETWEEN "'.
         type_to_str($range['from'], 'databasedate').'" AND "'.type_to_str($range['to'],'databasedate').'")', 'LEFT');
      $this->db->join('ad_types', 'ads.id_ad_type = ad_types.id_ad_type');
      
      if (0 < $id) {
         if ('group' == $field) {
            $this->db->where('id_group', $id);
         } elseif ('campaign' == $field) {
            $this->db->join('groups', 'groups.id_group = ads.id_group');
            $this->db->where('groups.id_campaign', $id);
         } elseif ('advertiser' == $field) {
            $this->db->join('groups', 'groups.id_group = ads.id_group');
            $this->db->join('campaigns', 'campaigns.id_campaign = groups.id_campaign');
            $this->db->where('campaigns.id_entity_advertiser', $id);
         }
      }
      
      if ($filt == 'all') {
         $this->db->where('ads.status !=', 'deleted');
      } else {
         $this->db->where('ads.status', $filt);
      }
      
      if(count($ad_types)){
         $this->db->where( 'ads.id_ad_type IN ('.implode(',',$ad_types).')' );
      }           
      
      if (0 < $id_ignored_group) {
         $this->db->where('ads.id_group <>', $id_ignored_group);
      }
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($per_page, ($page-1)*$per_page);
      $this->db->group_by('id_ad');
      $res = $this->db->get();
      $ads = array();
      foreach ($res->result() as $row) {
         $ads[$row->id_ad]['title'] = $row->title;
         $ads[$row->id_ad]['status'] = $row->status;
         $ads[$row->id_ad]['spent'] = $row->spent;
         $ads[$row->id_ad]['impressions'] = $row->impressions;
         $ads[$row->id_ad]['clicks'] = $row->clicks;
         $ads[$row->id_ad]['ctr'] = $row->ctr;
         $ads[$row->id_ad]['type'] = $row->name;
      }    
      return $ads;      
   }

   /**
   * возвращает количество объявлений, удовлетворяющих заданнам условиям (по кампании)
   *
   * @param integer $id_campaign код выбранной кампании
   * @param string $filt фильтр по статусу
   * @param int $id_ignored_group игнорируемая группа
   * @return integer количество записей
   */   
   public function total_by_campaign($id_campaign, $filt, $range, $id_ignored_group = 0, $ad_types = array()) {
      return $this->_total($id_campaign, 'campaign', $filt, $range, $id_ignored_group,$ad_types);
   } //end total         
   
   /**
   * возвращает количество объявлений, удовлетворяющих заданнам условиям (по рекламодателю)
   *
   * @param integer $id_campaign код выбранного рекламодателя
   * @param string $filt фильтр по статусу
   * @param int $id_ignored_group игнорируемая группа
   * @return integer количество записей
   */   
   public function total_by_advertiser($id_advertiser, $filt, $range, $id_ignored_group = 0, $ad_types = array()) {
      return $this->_total($id_advertiser, 'advertiser', $filt, $range, $id_ignored_group,$ad_types);
   } //end total         
   
   /**
   * возвращает количество объявлений, удовлетворяющих заданнам условиям
   *
   * @param integer $id_group код выбранной группы объявлений
   * @param string $filt фильтр по статусу
   * @param int $id_ignored_group игнорируемая группа
   * @return integer количество записей
   */   
   public function total($id_group, $filt, $range, $id_ignored_group = 0, $ad_types = array()) {
      return $this->_total($id_group, 'group', $filt, $range, $id_ignored_group,$ad_types);
   } //end total         
   
   /**
   * возвращает количество объявлений, удовлетворяющих заданнам условиям
   *
   * @param integer $id уникальный код
   * @param integer $field к чему этот код относится: group, campaign, advertiser
   * @param string $filt фильтр по статусу
   * @param int $id_ignored_group игнорируемая группа
   * @return integer количество записей
   */   
   private function _total($id, $field, $filt, $range, $id_ignored_group = 0, $ad_types) {
      $this->db->select('
      	 COUNT(DISTINCT ads.id_ad) AS cnt, 
      	 SUM(spent) AS spent,
      	 SUM(impressions) AS impressions, 
      	 SUM(clicks) AS clicks',
      	 FALSE
      );
      $this->db->from('ads');
      $this->db->join(
         'stat_ads', 'ads.id_ad = stat_ads.id_ad AND (stat_date BETWEEN "'.
         type_to_str($range['from'], 'databasedate').'" AND "'.type_to_str($range['to'],'databasedate').'")', 'LEFT');

  
      $this->db->join('ad_types', 'ads.id_ad_type = ad_types.id_ad_type', 'LEFT');            
      if (0 < $id) {
         if ('group' == $field) {
            $this->db->where('id_group', $id);
         } elseif ('campaign' == $field) {
            $this->db->join('groups', 'groups.id_group = ads.id_group');
            $this->db->where('groups.id_campaign', $id);
         } elseif ('advertiser' == $field) {
            $this->db->join('groups', 'groups.id_group = ads.id_group');
            $this->db->join('campaigns', 'campaigns.id_campaign = groups.id_campaign');
            $this->db->where('campaigns.id_entity_advertiser', $id);
         }
      }
      if ($filt == 'all') {
         $this->db->where('ads.status !=', 'deleted');
      } else {
         $this->db->where('ads.status', $filt);
      }
      
      if(count($ad_types)){
         $this->db->where( 'ads.id_ad_type IN ('.implode(',',$ad_types).')' );
      }       
      
      if (0 < $id_ignored_group) {
         $this->db->where('ads.id_group <>', $id_ignored_group);
      }
      //$this->db->group_by('id_group');
      $res = $this->db->get();

      if ($res->num_rows()) {
         $row = $res->row();
         $ads['cnt'] = $row->cnt;
         $ads['spent'] = $row->spent;
         $ads['impressions'] = $row->impressions;
         $ads['clicks'] = $row->clicks;
         return $ads;      
      }
      return array('cnt' => 0);
   } //end total         
   
   /**
   * совершает заданное действие над выбранным объявлением
   *
   * @param string $action действие совершаемое над объявлением (delete, pause, resumr)
   * @param integer $id_group код группы с объявлениями
   * @param integer $id_ad код объявления
   * @return ничего не возвращает
   */   
   public function action($action, $id_group, $id_ad) {
      $this->db->where(array('id_group' => $id_group, 'id_ad' => $id_ad));
      switch ($action) {
         case 'delete':
            $this->db->update('ads', array('status' => 'deleted'));
            $this->kill_image($id_ad); 
            break;    
         case 'pause':
            $this->db->where('status', 'active');
            $this->db->update('ads', array('status' => 'paused'));
            break;
         case 'resume':
            $this->db->where('status', 'paused');
            $this->db->update('ads', array('status' => 'active'));
            break;
      }              
   } //end action     
   
   /**
   * возвращает HTML-код выбранного объявления для предпросмотра
   *
   * @param integer $id_ad код выбранного объявления
   * @param integer $max_width максимальная ширина изображения (по умолчанию - не ограничена)
   * @param integer $max_height максимальная высота изображения (по умолчанию - не ограничена)
   * @return string HTML-код
   */
   public function get_html($id_ad, $max_width = NULL, $max_height = NULL, $id_prefix = '') {
      $CI =& get_instance();
   	$this->db->select('name, title, description, description2, display_url, click_url');
   	$this->db->from('ads');
   	$this->db->join('ad_types', 'ads.id_ad_type = ad_types.id_ad_type');
   	$this->db->where('id_ad', $id_ad);
   	$res = $this->db->get();
      $html = __('Ad not found!');
      if ($res->num_rows()) {
         $row = $res->row();
         if ($row->name == 'text') {
		 
		// Подгружаем стили дефолтной color scheme
		 
				$this->db->select('cs.*, ft.name title_font_name, ft2.name text_font_name, ft3.name url_font_name', false);
				$this->db->from('color_schemes cs');
				$this->db->join('fonts ft', 'ft.id_font = cs.title_id_font');
				$this->db->join('fonts ft2', 'ft2.id_font = cs.text_id_font');
				$this->db->join('fonts ft3', 'ft3.id_font = cs.url_id_font');

				$query = $this->db->get();
				$row2 = $query->row();	
			
            $data = array(
               'DESTINATION_URL' => type_to_str($row->click_url, "encode"),
               'TITLE' => type_to_str($row->title, "encode"),
               'DESCRIPTION' => type_to_str($row->description, "encode"),      
               'DESCRIPTION2' => type_to_str($row->description2, "encode"),
               'DISPLAY_URL' => type_to_str($row->display_url, "encode"),
			   'BACKGROUND_COLOR'  => $row2->background_color,
							'BORDER_COLOR'  => $row2->border_color,
							'TITLE_COLOR'  => $row2->title_color,
							'TITLE_FONT_NAME'  => $row2->title_font_name,
							'TITLE_FONT_SIZE'  => $row2->title_font_size,
							'TITLE_FONT_STYLE'  => $row2->title_font_style,
							'TITLE_FONT_WEIGHT'  => $row2->title_font_weight,
							'TEXT_COLOR' => $row2->text_color,
							'TEXT_FONT_NAME'  => $row2->text_font_name,
							'TEXT_FONT_SIZE'  => $row2->text_font_size,
							'TEXT_FONT_STYLE'  => $row2->text_font_style,
							'TEXT_FONT_WEIGHT'  => $row2->text_font_weight,
							'URL_COLOR' => $row2->url_color,
							'URL_FONT_NAME'  => $row2->url_font_name,
							'URL_FONT_SIZE'  => $row2->url_font_size,
							'URL_FONT_STYLE'  => $row2->url_font_style,
							'URL_FONT_WEIGHT'  => $row2->url_font_weight
            );
            $html = $CI->parser->parse('common/text_ad_example.html', $data, TRUE);
            /*$html = "<h3><a target='_blank' href='http://$row->click_url'>$row->title</a>
               </h3>$row->description<br/>$row->description2<br/><cite>$row->display_url</cite>";*/ 
         } else {
            $this->db->select('filename, width, height, bgcolor');
            $this->db->from('images');
            $this->db->join('dimensions', 'images.id_dimension = dimensions.id_dimension');
            $this->db->order_by('width', 'desc');
            $this->db->where('id_ad', $id_ad);
            $res = $this->db->get();            
            if ($res->num_rows()) {
               $CI =& get_instance();
               $path = $CI->get_siteurl() . ltrim($this->config->item('path_to_images'), './');               
               $row = $res->row();
               $width = $row->width;                              
               $height = $row->height;
               $set_width = NULL;                              
               if (!is_null($max_width)) {
                  if (!is_null($max_height)) {
                     $set_width = (($width/$height) > ($max_width/$max_height));
                  } else {
                     $set_width = true;
                  }                  
               } elseif (!is_null($max_height)) {
                  $set_width = false;
               }
               
               if (preg_match("/\.swf$/i", $row->filename)) { //Отображение swf
                  $container_id = str_replace(array('.', '/'), '', $row->filename);	
               
               	$html = '<div id="'.$id_prefix.$container_id.'"></div>';
               	$html.= '<script type="text/javascript"><!-- 
               	  $(\'#'.$id_prefix.$container_id.'\').flash({wmode: "opaque", bgcolor: "' . $row->bgcolor . '", src: \''.$CI->get_siteurl().'images/jquery_flash/loader_u_'.$width.'x'.$height.'.swf\'';

                  if (!is_null($set_width)) {  
                  	   $aspect_ratio = $height/$width;
                  	   
                  	   $resized_width = $width;
                  	   $resized_height = $height;
                  	   
                  	   if ($width > $max_width) {
                  	    	$resized_width = $max_width;
                  	    	$resized_height = round($aspect_ratio*$max_width);
                  	   }
                  	   
                  	   if ($resized_height > $max_height) {
                  	   	$resized_height = $max_height;
                  	   	$resized_width = round($max_height/$aspect_ratio);
                  	   }
                  	   
                        $html.= ', width: '.$resized_width;
                        
                        $html.= ', height: '.$resized_height;
                  } else {
                  	$html.= ', width: '.$width;
                        
                     $html.= ', height: '.$height;
                  }
                  
               	$html.=', flashvars: { banner: \''.$path.$row->filename.'\'}});
               	-->
               	</script>';
               } else {
               
	               $size_str = '';
	               if (!is_null($set_width)) {               
	                  if ($set_width) {
	                     $size_str = " width='".(($width > $max_width) ? $max_width : $width)."px'";
	                  } else {
	                     $size_str = " height='".(($height > $max_height) ? $max_height : $height)."px'";
	                  }       
	               }        
               $html = "<img$size_str src='" . $path . $row->filename . "'>";
               }
            } else {            
               $html = __('Image not found!');
            }
         }
      }
      header("cache-control:no-cache");
      return $html;
   } //end get_html
   
   /**
    * Метод получения текстового превью рекламного объявления
    *
    * @param int $id_ad
    * @return string
    */
   function get_text_preview($id_ad) {
      $html = '';
      $this->db->select('id_ad, name, title, description, description2, display_url, click_url, protocol')
         ->from('ads')
         ->join('ad_types', 'ads.id_ad_type = ad_types.id_ad_type')
         ->where('id_ad', $id_ad)
         ->limit(1);
      $query = $this->db->get();
      if (0 < $query->num_rows()) {
         $row = $query->row();                  
         
         $data = array(
            'CODE'                 => type_to_str($id_ad, 'textcode'),
            'NAME'                 => $row->name,
            'ORIG_DESTINATION_URL' => type_to_str($row->click_url, "encode"),
            'DESTINATION_URL'      => type_to_str(limit_str($row->click_url, 45), "encode"),
            'TITLE'                => type_to_str($row->title, "encode"),
            'DESCRIPTION'          => type_to_str($row->description, "encode"),      
            'DESCRIPTION2'         => type_to_str($row->description2, "encode"),
            'DISPLAY_URL'          => type_to_str($row->display_url, "encode"),
            'PROTOCOL'             => $row->protocol,
            'SHOW_DESCRIPTION'     => 'text' == $row->name ? array(array()) : array()
         );
         $html = $this->parser->parse('common/ad_example.html', $data, TRUE);
      
      }
      return $html;
   }
         
   /**
   * возвращает код типа объявления по его наименованию
   *
   * @param string $name наименование типа объявления (text, image)
   * @return integer код типа объявления, 0 - тип не найден
   */
   public function get_ad_type_id($name) {
      $res = $this->db->get_where('ad_types', array('name' => $name));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->id_ad_type;   	
      }
      return 0;
   } //end get_ad_type_id
      
   /**
   * возвращает расширение файла
   *
   * @param string $filename имя файла
   * @return string расширение файла
   */
   public function get_file_extension($filename) {
      $pathinfo = pathinfo($filename);
      if (isset($pathinfo['extension'])) {
         return '.'.$pathinfo['extension'];
      }
      return '';            
   } //end get_file_extension
   
   /**
   * возвращает код размера рисунка
   *
   * @param integer $width ширина рисунка
   * @param integer $height высота рисунка
   * @return integer код размера рисунка, NULL - такой размер не найден
   */
   public function get_dimension($width, $height) {
      $res = $this->db->get_where('dimensions', array('width' => $width, 'height' => $height));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->id_dimension;
      }
      return NULL;
   } //end get_dimension
   
   
   /**
   * добавляет изображение к выбранному объявлению
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @param string $uploadedfilename имя загруженного файла изображения
   * @param bool $is_copy признак копирования рекламного объявления
   * @return ничего не возвращает 
   */
   public function add_image($id_ad, $uploadedfilename, $is_copy = false) {
      $CI =& get_instance();
      $filename = '';
      while (empty($filename) || file_exists($filename)) {
         $filename = $CI->user_id . '/' . md5(uniqid(time())) . $this->get_file_extension($uploadedfilename);
      }
      $path = $this->config->item('path_to_images');
      if (!file_exists($path . $CI->user_id)) {
         mkdir($path . $CI->user_id);
         @chmod($path . $CI->user_id, 0777);
      }
      $uploadfile = '';
      if (!$is_copy) {
         $uploadfile = $this->config->item('path_to_campaign_creation_images') . $uploadedfilename;
      } else {
         $uploadfile = $path . $uploadedfilename;
      }
      $imagefile = $path . $filename;      
      copy($uploadfile, $imagefile);
      if (!$is_copy) {
         @unlink($uploadfile);
      }
      $imagesize = getimagesize($imagefile);                 
      $id_dimension = $this->get_dimension($imagesize[0], $imagesize[1]);
      if (is_null($id_dimension)) {
         return false; 
      }
      $bgcolor = $this->get_bgcolor($id_ad);
      if (!$is_copy) {
         $this->kill_image($id_ad);      
      }
      $this->db->insert('images',
         array(
            'id_ad' => $id_ad,
            'id_dimension' => $id_dimension,
            'filename' => $filename,
            'is_flash' => preg_match("/\.swf$/i", $filename) ? 'true' : 'false',
            'bgcolor' => $bgcolor
         )
      );
      return true;
   } //end add_image   
   
   /**
   * возвращает цвет фона flash
   *
   * @param integer $id_ad ID рисунка
   * @return string цвет фона, NULL
   */
   public function get_bgcolor($id_ad) {
      $res = $this->db->get_where('images', array('id_ad' => $id_ad));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->bgcolor;
      }
      return NULL;
   } //end get_bgcolor
   
   /**
   * инменяет цвет фона flash
   *
   * @param integer $id_ad ID рисунка
   * @param string $bgcolor цвет фона, NULL
   */
   public function set_bgcolor($id_ad, $bgcolor) {
      $this->db->where(array('id_ad' => $id_ad));
      $this->db->update('images', array('bgcolor' => $bgcolor));
   } //end set_bgcolor

   /**
   * возвращает имя файла изображения для выбранного объявления
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @param bool $filenameonly вернуть только имя файла, без пути
   * @return string полный путь к имени файла изображения, NULL - не найдено объявление или изображение
   */
   public function image($id_ad, $filenameonly = FALSE) {
      $res = $this->db->get_where('images', array('id_ad' => $id_ad));
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
         	$filename = $row->filename;
         	if ($filenameonly) {
         	   return $filename;
         	}
            $CI =& get_instance();         
            $path = $CI->get_siteurl() . ltrim($this->config->item('path_to_images'), './');               
            $imagefile = $path . $filename;
            return $imagefile;
         }
      }
      return NULL;         
   } //end image   
   
   /**
   * удаляет изображение у заданного объявления
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @return ничего не возвращает 
   */
   public function kill_image($id_ad) {
      $filename = $this->image($id_ad, TRUE);
      if (!is_null($filename)) {                                            
         $CI =& get_instance();    
         $path = ltrim($this->config->item('path_to_images'), './');               
         $imagefile = $path . $filename;
         @unlink($imagefile);
      }
      $this->db->delete('images', array('id_ad' => $id_ad));    	
   } //end kill_image   
   
   /**
   * добавление нового объявления в выбранную группу
   *
   * @param integer $id_group уникальный код выбранной группы
   * @param array $fields параметры объявления
   *     ad_type - тип объявления (text, image)
   *     title - заголовок объявления
   *     description - первая строка описания
   *     description2 - вторая строка описания
   *     display_url - отображаемый адрес
   *     destination_url - реальный адрес
   *     protocol - протокол передачи данных (http, https)
   * @param bool $is_copy признак копирования рекламного объявления
   * @return ничего не возвращает
   */
   	public function add($id_group, $fields, $type, $is_copy = false) {
      	if ($type == 'text') {
      		$this->db->insert(
      			'ads',
      	   		array(
      	      		'id_group' => $id_group,
      	      		'id_ad_type' => $this->get_ad_type_id($type), 
      	      		'title' => $fields['title'],
      	      		'description' => $fields['description1'],
      	      		'description2' => $fields['description2'],
               		'display_url' => $fields['display_url'],
               		'click_url' => $fields['destination_url'],
      	      		'protocol' => $fields['destination_protocol']
      	   		)
         	);
         	return $this->db->insert_id();
      	} elseif($type == 'image') {
         	$this->db->insert(
         		'ads',
            	array(
               		'id_group' => $id_group,
               		'id_ad_type' => $this->get_ad_type_id($type), 
               		'title' => $fields['title'],
               		'display_url' => $fields['display_url'],
               		'click_url' => $fields['destination_url'],
               		'protocol' => $fields['destination_protocol']
            	)
         	);
         	$id_ad = $this->db->insert_id();
         	if (!$this->add_image($id_ad, $fields['id_image'], $is_copy)) {
            	// Удаляем ад если не удалось закачать картинку
            	$this->db->where('id_ad', $id_ad)->delete('ads');
            	return 0;
         	}
         	if ((!array_key_exists('bgcolor', $fields)) || (!preg_match("/#([A-F0-9]{6}|[A-F0-9]{3})/i", $fields['bgcolor']))) {
            	$fields['bgcolor'] = '';
         	}
         	$this->set_bgcolor($id_ad, $fields['bgcolor']);
         	return $id_ad;
      	} 
   	} //end add
   
   /**
   * возвращает данные выбранного объявления
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @return array параметры объявления
   */
   public function get($id_ad) {
      
      $this->db->select('ad_types.name, title, ads.description, ads.description2,'.
                        'display_url,ads.display_url,ads.click_url,ads.protocol,'.
                        'images.filename,images.id_dimension,images.bgcolor');
            
      $this->db->join('ad_types', 'ads.id_ad_type=ad_types.id_ad_type');
      $this->db->join('images', 'images.id_ad=ads.id_ad', 'LEFT');
      $this->db->join('groups', 'ads.id_group=groups.id_group', 'LEFT');
      $res = $this->db->get_where('ads', array('ads.id_ad' => $id_ad));
      
      if ($res->num_rows()) {         
         $row = $res->row();
         if ($row->name == 'text') {
         	return array(
         	   'ad_type' => $row->name,
         	   'title' => $row->title,
         	   'description1' => $row->description,
               'description2' => $row->description2,
         	   'display_url' => $row->display_url,
         	   'destination_url' => $row->click_url,
         	   'destination_protocol' => $row->protocol 
         	);
         }
         
         return array(
            'ad_type' => $row->name,
            'title' => $row->title,
            'display_url' => $row->display_url,
            'destination_url' => $row->click_url,
            'destination_protocol' => $row->protocol,
            'id_image' => $row->filename,
            'id_dimension' => $row->id_dimension,
            'bgcolor' => $row->bgcolor
         );                              
      }
      return array();      
   } //end get   

   /**
   * возвращает код группы для выбранного объявления 
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @return integer код группы, 0 - объявление не найдено  
   */
   public function group($id_ad) {
      $res = $this->db->get_where('ads', array('id_ad' => $id_ad));
      foreach ($res->result() as $row) {
         return $row->id_group;
      }                     
      return 0;      
   } //end group   
   
   /**
   * обновление параметров выбранного объявления
   *
   * @param integer $id_ad уникальный код выбранного объявления
   * @param array $fields параметры выбранного
   * @param string $type тип объявления (text, image)
   * @return ничего не возвращает 
   */
   public function save($id_ad, $fields, $type) {
      if ($type == 'text') {
         $this->db->where('id_ad', $id_ad);
         $this->db->update('ads',
            array(
               'title' => $fields['title'],
               'description' => $fields['description1'],
               'description2' => $fields['description2'],
               'display_url' => $fields['display_url'],
               'click_url' => $fields['destination_url'],
               'protocol' => $fields['destination_protocol']
            )
         );
      } else {
      	
         $this->db->where('id_ad', $id_ad);
         $this->db->update('ads',
            array(
               'title' => $fields['title'],
               'display_url' => $fields['display_url'],
               'click_url' => $fields['destination_url'],
               'protocol' => $fields['destination_protocol']
            )
         );
         
         if (isset($fields['id_image']) && !empty($fields['id_image'])) {
            //Проверка необходимости добавления нового изображения в базу
            $query = $this->db->select('filename, bgcolor')
                          ->from('images')
                          ->where('id_ad',$id_ad)->get();
            
            if (!preg_match("/#([A-F0-9]{6}|[A-F0-9]{3})/i", $fields['bgcolor'])) {
            	$fields['bgcolor'] = '';
            }
            if ((0 < $query->num_rows()) && ($query->row()->filename != $fields['id_image'])) {
               //Удаление старого изображения
               $this->kill_image($id_ad);
               $this->add_image($id_ad, $fields['id_image']);
            } elseif (0 == $query->num_rows()) { // Если запись об изображении была удалена
               $this->add_image($id_ad, $fields['id_image']);
            }
            $this->set_bgcolor($id_ad, $fields['bgcolor']);
         }
      }
   } //end save   

   /**
   * возвращает список объявлений заданного типа для выбранной группы
   *
   * @param integer $id_group_site_channel уникальный код группы
   * @param string $ad_type тип объявления (text, image)
   * @return array массив с кодами объявлений
   */
   public function get_group_ads($id_group_site_channel, $ad_type, $sort_field, $sort_direction) {
   	$list = array();
      if($ad_type != 'all') {
         $this->db->where('name', $ad_type);
      }
   	$res = $this->db->select('a.id_ad, a.status, SUM( sap.clicks ) AS clicks,'.
   	   ' SUM( sap.impressions ) AS impressions, SUM( sap.clicks)/SUM(sap.impressions) AS ctr,'.      
   	   ' SUM( sap.spent) AS spent, ad_types.name')->
   	   from('ads a')->
   	   join('group_site_channels gsc', 'a.id_group=gsc.id_group')->
   	   join('stat_ads_packet sap', 'a.id_ad=sap.id_ad AND gsc.id_group_site_channel=sap.id_group_site_channel', 'LEFT')->
         join('ad_types', 'a.id_ad_type=ad_types.id_ad_type')->         
   	   where('gsc.id_group_site_channel', $id_group_site_channel)->
   	   group_by('a.id_ad')->
   	   order_by($sort_field, $sort_direction)->
     	   get();
   	foreach ($res->result() as $row) {
   		$list[$row->id_ad]['status']=$row->status;
         $list[$row->id_ad]['impressions']=$row->impressions;
   	   $list[$row->id_ad]['clicks']=$row->clicks;
   	   $list[$row->id_ad]['ctr']=$row->ctr;
         $list[$row->id_ad]['used']=$row->spent;
         $list[$row->id_ad]['ad_type']=$row->name;
   	}   	
   	return $list;
   } //end get_group_ads

   /**
   * возвращает общее количество объявлений заданного типа в группе
   *
   * @param integer $id_group_site_channel уникальный код группы
   * @param string $ad_type тип объявлений (text, image), '' - все объявления
   * @return integer количество объявлений
   */
   public function get_group_ads_total($id_group_site_channel, $ad_type) {
      if($ad_type != 'all') {
         $this->db->where('name', $ad_type)->
            join('ad_types at', 'a.id_ad_type=at.id_ad_type');         
      }
      return $this->db->
         from('ads a')->
         join('group_site_channels gsc', 'a.id_group=gsc.id_group')->
         where('gsc.id_group_site_channel', $id_group_site_channel)->
         count_all_results();
   } //end get_group_ads_total   
   
   /**
   * возвращает размеры для изображения заданного обявления
   *
   * @param integer $id_ad код объявления
   * @return array размеры изображения (width, height), NULL - объявление не найдено, нет картинки, только текст
   */
   public function get_ad_dimensions($id_ad) {
      $res = $this->db->
   	   select('width, height')->
   	   from('dimensions d')->
   	   join('images i', 'd.id_dimension=i.id_dimension')->
   	   where('id_ad', $id_ad)->
   	   get();
      if ($res->num_rows()) {
         $row = $res->row();
         return array('width' => $row->width, 'height' => $row->height);
      }
      return NULL;
   } //end get_ad_dimensions   
   
   /**
    * Копирование объявления в группу
    *
    * @param int $id_ad
    * @param int $id_group
    * @return int
    */
   public function copy_ad($id_ad, $id_group) {
      $id = 0;
      // Получаем данные по объявлению
      $data = $this->get($id_ad);
      if (0 < count($data)) {
         $id = $this->add($id_group, $data, $data['ad_type'], true);
      }
      return $id;
   }
   
   /**
    * Проверка принадлежности объявления рекламодателю
    *
    * @param int $id_ad
    * @param int $id_advertiser
    * @return bool
    */
   public function check_advert_ad($id_ad, $id_advertiser) {
      $count = $this->db->from('ads a')
         ->join('groups g', 'a.id_group = g.id_group')
         ->join('campaigns c', 'g.id_campaign = c.id_campaign')
         ->where('a.id_ad', $id_ad)
         ->where('c.id_entity_advertiser', $id_advertiser)
         ->count_all_results();
      if (0 < $count) {
         return true;
      }
      return false;
   }
   
   /**
    * Получение списка идентификаторов объявлений для заданной группы
    *
    * @param int $id_group
    * @return array
    */
   public function get_ads_ids_by_group($id_group) {
      $ads = array();
      $this->db->select('id_ad')
         ->from('ads')
         ->where('id_group', $id_group)
         ->where('status <>', 'deleted');
      $query = $this->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result_array() as $row) {
            array_push($ads, $row['id_ad']);
         }
      }
      return $ads;
   }
}
?>