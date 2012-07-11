<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MY_Controller.php';
/**
 * Контроллер обработки поиска
 *
 * @author Gennadiy Kozlenko
 */
class Show extends MY_Controller {
   
   /**
    * Идентификатор сайта
    *
    * @var integer
    */
   var $id_site = 0;
   
   /**
    * Идентификатор канала
    *
    * @var integer
    */
   var $id_channel = 0;
   
   /**
    * Идентификатор размеров
    *
    * @var integer
    */
   var $id_dimension = 0;
   
   /**
    * Ширина
    *
    * @var integer
    */
   var $width = 0;
   
   /**
    * Высота
    *
    * @var integer
    */
   var $height = 0;
   
   /**
    * Идентификатор пальтры
    *
    * @var integer
    */
   var $id_palette = 0;
   
   /**
    * Идентификатор пользователя
    *
    * @var integer
    */
   var $id_user = 0;
   
   /**
    * Реферер страницы
    *
    * @var string
    */
   var $referer = '';
   
   /**
    * Реальный реферер страницы
    *
    * @var string
    */
   var $real_referer = '';
   
   /**
    * Рандомная составляющая
    *
    * @var string
    */
   var $ident= '';
   
   /**
    * Использовать ли flash в объявлениях
    *    1 - Использовать
    *    2 - Не использовать
    *    -1 - Не знаю (на всякий случай использовать)
    *
    * @var int
    */
   var $use_flash = 1;

   /**
    * It is iframe?
    */
   var $is_iframe = false;

   /**
    * Конструктор класса
    *
    * @return Show
    */
   public function __construct() {
      parent::__construct();
      $this->load->library('parser');
      // Получаем нужные нам данные
      if (false !== $this->input->get('id_site')) {
         $this->id_site = (int) $this->input->get('id_site');
      }
      if (false !== $this->input->get('id_channel')) {
         $this->id_channel = (int) $this->input->get('id_channel');
      }
      if (false !== $this->input->get('id_dimension')) {
         $this->id_dimension = (int) $this->input->get('id_dimension');
      }
      if (false !== $this->input->get('width')) {
         $this->width = (int) $this->input->get('width');
      }
      if (false !== $this->input->get('height')) {
         $this->height = (int) $this->input->get('height');
      }
      if (false !== $this->input->get('id_palette')) {
         $this->id_palette = (int) $this->input->get('id_palette');
      }
      if (false !== $this->input->get('id_user')) {
         $this->id_user = (int) $this->input->get('id_user');
      }
      if (false !== $this->input->get('ref')) {
         $this->referer = $this->input->get('ref');
      } else {
         $this->referer = $this->input->server('HTTP_REFERER');
      }
      if (false !== $this->input->get('sr')) {
         $this->real_referer = $this->input->get('sr');
      } else {
         $this->real_referer = '';
      }
      if (false !== $this->input->get('ident')) {
         $this->ident = $this->input->get('ident');
      } else {
         srand(Sppc_Stats_Utils::makeSeed());
         $this->ident = rand(100000, 999999);
      }
      if (false !== $this->input->get('uf')) {
         $this->use_flash = $this->input->get('uf');
      }
      if (false !== $this->input->get('ifr')) {
         $this->is_iframe = true;
      }
      // Security
      header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
   }
   
   /**
    * Метод по умолчанию
    *
    */
   public function index() {
      $this->noCache();
      $this->load->model('global_variables');
      // Создаем объект поиска
      $this->load->library('search_builder');
      
      $this->search_builder->setSite($this->id_site);
      $this->search_builder->setChannel($this->id_channel);
      $this->search_builder->setReferer($this->referer);
      $this->search_builder->setRealReferer($this->real_referer);
      $this->search_builder->setSearchType('js');
      $this->search_builder->setUseFlash(0 != $this->use_flash ? true : false);
      $this->search_builder->loadParameters();
      // Запускаем процесс поиска
      $site = $this->search_builder->getSiteInfo();
      $channel = $this->search_builder->getChannelInfo();
      
      // Выводим
      $next_page = true;
      $data = array();
      $view = '';
      if (null !== $channel && null !== $site) {
      	 $results = array();
      	 
         $this->load->model('entity');
//         $user_status = $this->entity->get_role_status($this->id_user, 'publisher');
//         if ('active' == $user_status) {
         $user_status = false;
         $roles = $this->entity->get_roles($this->id_user);
         if (in_array2(array ('admin', 'publisher'), $roles)) {
            $user_status = true;
            $dimension = $this->getDimension($channel->id_dimension);

            if ($this->search_builder->search()) {
               // Получаем результаты
               $results = $this->search_builder->getResults();
            }

            // Получаем код ошибки
            $error = $this->search_builder->getLastError();
         }
      
         if ('active' != $user_status) {
            $view = 'show_ads/error.html';
            $data = array(
               'error'  => __('Publisher has been blocked or deleted'),
               'width'  => $this->width - 2,
               'height' => $this->height - 2,
            );
         } elseif (0 < count($results)) {
            // Выводим объявления
            if (Search_builder::TYPE_TEXT == $this->search_builder->getAdType()) {
               $this->prepareResults($results);
               // Подгружаем данные по палитре
               $palette = $this->getPalette();
               $data = $palette;
               
               $view = 'show_ads/' . $channel->width . 'x' . $channel->height . '.html';
               $data['results'] = $results;
               
               $data['width'] = $channel->width - 2;
               $data['height'] = $channel->height - 2;
               $data['item_width'] = floor(($channel->width - 2) / $channel->max_ad_slots);
               $data['item_height'] = floor(($channel->height - 2) / count($results));
               $data['rows_count'] = $dimension['rows_count'];
               $data['columns_count'] = $dimension['columns_count'];
               
            } elseif (Search_builder::TYPE_IMAGE == $this->search_builder->getAdType()) {
               $view = 'show_ads/image.html';
               $data = current($results);
               $data['width'] = $channel->width;
               $data['height'] = $channel->height;
               $data['results'] = $results;
            } elseif (Search_builder::TYPE_FLASH == $this->search_builder->getAdType()) {
               $view = 'show_ads/flash.html';
               $data = current($results);
               $data['width'] = $channel->width;
               $data['height'] = $channel->height;
               $data['loader'] = 'loader_u_' . $channel->width . 'x' . $channel->height . '.swf';
               $data['click_url'] = urlencode($data['click_url']);
               $data['results'] = $results;
            }
         } elseif ('' == $error || 'protection_fail' == $error || 'targeting' == $error) {
            // Выводим то, что настроил владелец канала
            if ('blank' == $channel->ad_settings || 'blank_color' == $channel->ad_settings) {
               $view = 'show_ads/blank_color.html';
               $data['color'] = 'ffffff';
               $data['width'] = $channel->width - 2;
               $data['height'] = $channel->height - 2;
               if ('blank_color' == $channel->ad_settings) {
                  $data['color'] = $channel->blank_color;
               }
            } else if('tag' == $channel->ad_settings) {
            	$tag = $this->getTag();
            	if (!is_null($tag)) {
               		$next_page = false;
               		$view = 'show_ads/js_wrapper.html';
               		if ($this->is_iframe) {
                  		$view = 'show_ads/js_iframe_wrapper.html';
               		}
               		$data['code'] = $tag->getCode();
            	} else {
            		$view = 'show_ads/blank_color.html';
               		$data['color'] = 'ffffff';
               		$data['width'] = $channel->width - 2;
               		$data['height'] = $channel->height - 2;
            	}
            }
         }
      } else {
      	$view = 'show_ads/blank_color.html';
      	$data['color'] = 'ffffff';
      	$data['width'] = $channel->width - 2;
      	$data['height'] = $channel->height - 2;
      }
      
      $data['base_url'] = base_url();
      
      if (empty($view)) {
         // Получаем локаль
         $locale = $this->global_variables->get('Locale', $this->id_user);
         if (is_null($locale)) {
            $locale = $this->global_variables->get('DefaultLocale');
         }
         // Подгружаем языковые файлы
         $this->initTranslate($locale);
         
         $view = 'show_ads/error.html';
         $data = array(
            'error'  => $this->search_builder->getErrorMessage(),
            'width'  => $this->width - 2,
            'height' => $this->height - 2,
         );
      }
      
      if (!empty($view)) {
         if ($next_page) {
            // Сохраняем вывод в сессию
            $id = time() . '_' . md5(uniqid(Sppc_Stats_Utils::makeSeed()));
            
            $showYourAdHereLink = $this->global_variables->get('ShowYourAdHereLink'); 
            
         	if ($showYourAdHereLink) {
	      	 	$data['your_ad_here_link'] = array(
	      	 		array(
	      	 			'siteurl' 	=> base_url(),
		      	 		'your_ad_here_link_text' => $this->global_variables->get('YourAdHereLinkText'),
		      	 		'site_id' 	=> type_to_str($this->id_site, 'textcode'),
		      	 		'channel_id' => type_to_str($this->id_channel, 'textcode')
	      	 		)
	      	 	);
	      	 } else {
	      	 	$data['your_ad_here_link'] = array();
	      	 }
            
            $content = $this->parser->parse($view, $data, true);
            
            if (!$this->is_iframe) {
               // Сохраняем контент в кеш
               $cache = $this->getCache();
               $cache->save($content, $id);
               $data = array(
                  'id_site'    => $this->id_site,
                  'id_channel' => $this->id_channel,
                  'width'      => $this->width,
                  'height'     => ($showYourAdHereLink) ? $this->height + 15 : $this->height,
                  'id'         => $id,
                  'ident'      => $this->ident,
                  'base_url'   => base_url()
               );
               $view = 'show_ads/iframe_wrapper.html';
               $this->output->set_header('Content-Type: text/javascript');
               $this->parser->parse($view, $data);
            } else {
               $this->output->set_output($content);
            }
            return;
         }
         if (!$this->is_iframe) {
            $this->output->set_header('Content-Type: text/javascript');
         }
         $this->parser->parse($view, $data);
      }
   }
   
   /**
    * Отображение контента
    *
    */
   public function content() {
      $id = $this->input->get('id');
      if (!empty($id)) {
         $hash = '"' . $id . '"';
         $this->output->set_header('Etag: ' . $hash);
         if (false !== $this->input->server('HTTP_IF_NONE_MATCH') && $hash == $this->input->server('HTTP_IF_NONE_MATCH')) {
            $this->output->set_header('HTTP/1.0 304 Not Modified');
         } else {
            $cache = $this->getCache();
            $content = $cache->load($id);
            $this->output->set_output($content);
         }
      }
   }
   
   /**
    * Враппер
    *
    */
   public function wrapper() {
      $view = '';
      if (false !== $this->config->item('use_show_iframe_wrapper')) {
         $view = 'show_ads/first_iframe_wrapper.html';
      } else {
         $view = 'show_ads/first_wrapper.html';
         $this->output->set_header('Content-Type: text/javascript');
      }
      $lastmodified = filemtime(APPPATH . 'views/' . $view);
      $hash = '"' . $lastmodified . '-' . md5($view) . '"';
      $this->output->set_header('Etag: ' . $hash);
      $this->output->set_header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
      if (false !== $this->input->server('HTTP_IF_NONE_MATCH') && $hash == $this->input->server('HTTP_IF_NONE_MATCH')) {
         $this->output->set_header('HTTP/1.0 304 Not Modified');
      } else {
         $showYourAdHereLink = (int) $this->global_variables->get('ShowYourAdHereLink');
         $data = array(
            'ident'        => $this->ident,
            'your_ad_here' => 0 == $showYourAdHereLink ? '' : '1',
            'referer'      => urlencode($this->input->server('HTTP_REFERER')),
            'base_url'     => base_url()
         );
         $this->parser->parse($view, $data);
      }
   }
   
   /**
    * Подгружаем инфу по палитре
    *
    */
   protected function getPalette() {
      $palette = array();
      $feEngine = $this->config->item('palettes_frontend_engine');
      $feOptions = $this->config->item('palettes_frontend');
      $beEngine = $this->config->item('palettes_backend_engine');
      $beOptions = $this->config->item('palettes_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      if (false === ($palette = $cache->load('palette_' . $this->id_palette))) {
      $sql = "
         SELECT
            cs.border_color,
            tf.name AS title_font,
            cs.title_color,
            cs.title_font_size,
            cs.title_font_style,
            cs.title_font_weight,
            cs.background_color,
            xf.name AS text_font,
            cs.text_color,
            cs.text_font_size,
            cs.text_font_style,
            cs.text_font_weight,
            uf.name AS url_font,
            cs.url_color,
            cs.url_font_size,
            cs.url_font_style,
            cs.url_font_weight
         FROM
            color_schemes cs
               LEFT JOIN fonts tf ON (cs.title_id_font = tf.id_font)
               LEFT JOIN fonts xf ON (cs.text_id_font = xf.id_font)
               LEFT JOIN fonts uf ON (cs.url_id_font = uf.id_font)
         WHERE
            cs.id_color_scheme = " . $this->db->escape($this->id_palette) . " OR
            cs.id_entity_publisher = 0
         ORDER BY
            cs.id_entity_publisher DESC
         LIMIT
            1
      ";
      $query = $this->db->query($sql);
      if (0 < $query->num_rows()) {
         $palette = $query->row_array();
      }
         $cache->save($palette, 'palette_' . $this->id_palette);
      }
      return $palette;
   }
   
   protected function prepareResults(&$results) {
      $dimension = $this->getDimension($this->id_dimension);
      
	   foreach ($results as &$result) {
	      if ($dimension['title_size'] < mb_strlen($result['title'], 'utf-8')) {
	         $result['title'] = mb_substr($result['title'], 0, $dimension['title_size'], 'utf-8') . '...';
	      }
	      if ($dimension['text_size'] < mb_strlen($result['description'], 'utf-8')) {
	         $result['description'] = mb_substr($result['description'], 0, $dimension['text_size'], 'utf-8') . '...';
	      }
	      if ($dimension['url_size'] < mb_strlen($result['display_url'], 'utf-8')) {
	         $result['display_url'] = mb_substr($result['display_url'], 0, $dimension['url_size'], 'utf-8') . '...';
	      }
	      if (isset($result['description2'])) {
	            //$result['description'] = '<span>' . $result['description'] . '</span> <span>' . $result['description2'] . '</span>';
	         $result['description'] = $result['description'] . ' ' . $result['description2'];
	            /*
	            if ($dimension->max_ad_slots > count($results) && $dimension->width <= 160) {
	               $result['description'] = $result['description'] . '<br />' . $result['description2'];
	            } else {
	               $result['description'] = '<span>' . $result['description'] . '</span> <span>' . $result['description2'] . '</span>';
	            }
	            */
	      }
	         
	   	if (isset($result['title'])) {
	         $result['title'] = type_to_str($result['title'], 'encode');
	      }
                  
	      if (isset($result['description'])) {
	      	$result['description'] = type_to_str($result['description'], 'encode');
	      }
                  
	      if (isset($result['display_url'])) {
	      	$result['display_url'] = type_to_str($result['display_url'], 'encode');
	      }
	   }
   }
   
   /**
    * подготавливает механизм локализации
    *
    */
   protected function initTranslate($locale = '') {
      setlocale(LC_ALL, $locale);
      bindtextdomain('messages', BASEPATH . 'locale');
      textdomain('messages');
   }
   
   /**
    * Переопределение метода по умолчанию
    *
    */
   function _remap() {
      if (false === $this->input->get('id_channel')) {
         $this->wrapper();
      } elseif (false !== $this->input->get('id')) {
         $this->content();
      } else {
         $this->index();
      }
   }
   
   /**
    * Получение объекта для работы с кешем
    * @param $prefix String - префикс для кэша
    * @param $lifetime Integer - Время жизни
    * @return Zend_Cache
    */
   function getCache() {
      $feEngine = $this->config->item('show_contents_frontend_engine');
      $feOptions = $this->config->item('show_contents_frontend');
      $beEngine = $this->config->item('show_contents_backend_engine');
      $beOptions = $this->config->item('show_contents_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      return Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
   }
   
   /**
    * Избавление от кеширования
    *
    */
   function noCache() {
      header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Cache-Control: post-check=0, pre-check=0', false);
      header('Pragma: no-cache');
   }

   /**
    * Return tag in current channel 
    * 
    * @return Sppc_Tag|null
    */
   function getTag() {
   	  $tagModel = new Sppc_TagModel();
        $tag = $tagModel->findObjectById($this->id_channel);
   	  return $tag;
   }

   /**
    * Return info about specified dimension
    * 
    * @param int $id
    * @return array
    */
   protected function getDimension($id) {
      $feEngine = $this->config->item('dimensions_frontend_engine');
      $feOptions = $this->config->item('dimensions_frontend');
      $beEngine = $this->config->item('dimensions_backend_engine');
      $beOptions = $this->config->item('dimensions_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      
      if (false === ($dimension = $cache->load('dimension_' . $id))) {
      	 $dimensionModel = new Sppc_DimensionModel();
      	 $dimensionObj = $dimensionModel->findObjectById($id);
      	 
      	 if (!is_null($dimensionObj)) {
      	 	$dimension = $dimensionObj->toArray();
      	 	$cache->save($dimension, 'dimension_' . $id);
      	 } else {
      	 	$dimension = array(
      	 		'id_dimension'  => 0,
		        'title_size'    => 35,
		        'text_size'     => 75,
		        'url_size'      => 35,
		        'max_ad_slots'  => 1,
		        'width'         => 0,
		        'height'        => 0,
		        'rows_count'    => 1,
		        'columns_count' => 1,
		        'type'          => 'standart'
      	 	);
      	 }
      }
      
      return $dimension;
   }
}
