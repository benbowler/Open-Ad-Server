<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Abstract_builder.php';

/**
 * Класс поиска
 *
 * @author Gennadiy Kozlenko
 */
class Search_builder extends Abstract_builder {
   
   /**
    * Разделитель ресурсов в stream mode
    *
    */
   const SOURCES_DELIMITER = "\n*DELIMITER*\n";
   
   /**
    * Идентификатор сайта
    *
    * @var integer
    */
   private $id_site = 0;
   
   /**
    * Инфа по сайту
    *
    * @var array
    */
   private $site_info = null;
   
   /**
    * Идентификатор канала
    *
    * @var integer
    */
   private $id_channel = 0;
   
   /**
    * Инфа по каналу
    *
    * @var array
    */
   private $channel_info = null;
   
   /**
    * Реферер страницы (реальный реферер)
    *
    * @var string
    */
   protected $real_referer = '';

   /**
    * Массив параметров для фидов 
    *
    * @var array
    */
   private $feed_params = array('feeds' => array());

   
   /**
    * Массив результатов поиска
    *
    * @var array
    */
   private $results = array();
   
   /**
    * Массив сгруппированных результатов поиска
    *
    * @var array
    */
   private $group_results = array();
   
   /**
    * Имя директории с результатами запроса на фиды XML-файлами
    *
    * @var string
    */
   private $output_dir = '';
   
   /**
    * Тип поиска. Варианты:
    *    js
    *
    * @var string
    */
   private $search_type = self::SEARCH_TYPE_JS;
   
   /**
    * Тип рекламы. Варианты:
    *    text, image
    *
    * @var string
    */
   private $ad_type = self::TYPE_TEXT;
   
   /**
    * Namespace для айдишников использованных объявлений
    *
    * @var string
    */
   private $nsck = '';
   
   /**
    * Использовать ли flash в объявлениях
    *
    * @var bool
    */
   private $use_flash = true;
   
   /**
    * Режим отображения рекламных объявлений
    *    adv_xml       - Показывать сначала adv, затем xml
    *    adv_only      - Показывать только adv
    *    xml_only      - Показывать только xml
    *
    * @var string
    */
   private $display_ads = self::DISPLAY_ADV_XML;
   
   /**
    * Автоматически ли складывать статистику?
    *
    * Если false, то нужно выполнить дополнительно следующие методы:
    *    setXmlStats
    *    setOurdatabaseStats
    *    setOrganicStats (если нужно)
    *    setAlternativeImpressionStats (если нужно)
    *    registerStat
    *
    * @var bool
    */
   private $enable_stats = true;
   
   /**
    * Опеределять записывать ли данные по alternative impressions
    * 
    * @var bool
    */
   private $enable_alternative_stats = true;
   
   /**
    * Количество рекламных объявлений
    *
    * @var int
    */
   private $count = 0;
   
   
   /**
    * Использовать группировку результатов
    *
    * @var bool
    */
   private $use_group_results = false;
   
   /**
    * Parked domain
    * 
    * @var Sppc_Parked_Domain
    */
   private $parked_domain = null;
   
   /**
    * Current page
    * 
    * @var int
    */
   private $page = 0;
   
   /**
    * Число результатов полученных при последнем поимковом запросе
    * 
    * @var int
    */
   private $total_results_count = 0;
   
   /**
    * Минимальный бид который должно иметь рекламное объявление чтобы быть показанным
    *  
    * @var float
    */
   private $min_bid = null;
   
   /**
    * Instance
    *
    * @var Search_builder
    */
   protected static $_instance = null;
   
   /**
    * Timers
    * @var array
    */
   private $timers = array();
   
   /**
    * get instance of the class
    *
    * @return Search_builder
    */
   public static function getInstance() {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      parent::__construct();
      self::$_instance = $this;
   }
   
   /**
    * Процесс поиска
    *
    */
   public function search() {
      // Загружаем параметры
      $this->loadParameters();
      // Если нужно - проверяем сайт и канал
      if (!$this->checkSiteChannel()) {
         return false;
      }
      $this->loadFeeds();
      $this->prepareFeeds();
      // Получаем результаты
      $this->loadResults();
      if (!$this->obj->config->item('use_show_iframe_wrapper')) {
         // Сохраняем использованные рекламные объявления
         $ids = array();
         foreach ($this->results as $result) {
            if (isset($result['id']) && self::RESULT_OURDATABASE == $result['feed_params']['sb_type']) {
               array_push($ids, $result['id']);
            }
         }
         $this->addUsedIds($ids);
      }
      // Debug
      if ($this->firephp) {
         $trace = array();
         foreach ($this->obj->benchmark->marker as $key => $val) {
            // We match the "end" marker so that the list ends
            // up in the order that it was defined
            if (preg_match("/(.+?)_end/i", $key, $match)) {        
               if (isset($this->obj->benchmark->marker[$match[1].'_end']) AND isset($this->obj->benchmark->marker[$match[1].'_start'])) {
                  $trace[$match[1]] = $this->obj->benchmark->elapsed_time($match[1].'_start', $key);
               }
            }
         }
         if (0 < count($trace)) {
            $this->firephp->log($trace, 'Trace');
         }
      }
      return true;
   }
   
   /**
    * Проверка сайта и канала
    *
    * @return bool
    */
   private function checkSiteChannel() {
      // Получаем данные по каналу
      if (null == $this->site_info) {
         // Не нашли сайт
         $this->error = 'site_not_found';
         if ($this->firephp) {
            $this->firephp->error($this->error);
         }
         return false;
      }
      // Проверяем сайт
      if ('active' != $this->site_info->status) {
         // Канал не активный
         $this->error = 'site_' . $this->site_info->status;
         if ($this->firephp) {
            $this->firephp->error($this->error);
         }
         return false;
      }
      if (null == $this->channel_info) {
         // Не нашли канал
         $this->error = 'channel_not_found';
         if ($this->firephp) {
            $this->firephp->error($this->error);
         }
         return false;
      }
      // Проверяем канал
      if ('active' != $this->channel_info->status) {
         // Канал не активный
         $this->error = 'channel_' . $this->channel_info->status;
         if ($this->firephp) {
            $this->firephp->error($this->error);
         }
         return false;
      }
      if ('active' != $this->channel_info->link_status) {
         // Связь канала и сайта не активная
         $this->error = 'site_channel_' . $this->channel_info->link_status;
         if ($this->firephp) {
            $this->firephp->error($this->error);
         }
         return false;
      }
      return true;
   }
   
   /**
    * Установка фидов
    *
    * @param array $feeds
    */
   public function setFeeds($feeds) {
      $this->feed_params['feeds'] = array_merge($this->feed_params['feeds'], $feeds);
   }
      
   /**
    * Установка Ourdatabase фидов
    *
    * @param array $feeds
    */
   public function setOurdatabase($feeds) {
      $this->feed_params['feeds'] = array_merge($this->feed_params['feeds'], $feeds);
   }
   
   /**
    * EУстанавливает минимальный бид
    * 
    * @param float $bid
    */
   public function setMinBid($bid) {
      $this->min_bid = $bid;
   }
   /**
    * Возвращает минимальный бид
    * 
    * @return float|null
    */
   public function getMinBid() {
      return $this->min_bid;
   }
   /**
    * Установка источников
    *
    * @param array $sources
    *    Должен содержать следующие элементы:
    *       xml - массив xml фидов
    *       ourdatabase - массив запросов к рекламодателям
    */
   public function setSources($sources) {
      if (isset($sources['xml'])) {
         $this->setFeeds($sources['xml']);
      }
      if (isset($sources['ourdatabase'])) {
         $this->setOurdatabase($sources['ourdatabase']);
      }
   }
   
   /**
    * Получение поисковых результатов
    *
    * @return array
    */
   public function getResults() {
      return $this->results;
   }
   
   /**
    * Полученние массива поисковых результатов, сгруппированных по engines
    *
    * @return array
    */
   public function getGroupedResults() {
      return $this->group_results;
   }
   
   /**
    * Парсинг результатов работы клиента (файловый режим)
    *
    * @param string $content
    * @return object
    */
   private function parseSearchResultsFiles($content) {
      // Возвращаемый объект с результатами
      $result = new stdClass();
      
      // Массивы результатов по типам
      $our_db_results = array();
      $xml_feeds_results = array();
//      $organic_results = array();
      
      $path_to_xmls_dir = $this->obj->config->item('path_to_bins') . 'var/tmp/search/' . $this->output_dir;
      
      // create an array to hold directory list
      $dir_list = array();
      
      // create a handler for the directory
      $dir_handler = opendir($path_to_xmls_dir);
      
      // keep going until all files in directory have been read
      while (false !== ($file = readdir($dir_handler))) {
         // if $file isn't this directory or its parent,
         // add it to the results array
         if ($file != '.' && $file != '..' && strpos($file, '.xml')) {
            $dir_list[] = $file;
         }
      }
      
      // tidy up: close the handler
      closedir($dir_handler);
      
      foreach ($dir_list as $file) {
         $contents = file_get_contents($path_to_xmls_dir . '/' . $file);
         try {
            $xml = new SimpleXMLElement($contents);
            $feed_params = array('client_file' => $file, 'total_results' => 0);
            $total = 0;
            if (isset($xml->params)) {
               foreach ($xml->params->children() as $name => $value) {
                  $feed_params[$name] = (string) $value;
               }
            }
            if (isset($xml->total)) {
               $feed_params['total_results'] = (int) $xml->total;
            }
            
            // Определяем тип результата
            switch ($feed_params['sb_type']) {
               case self::RESULT_OURDATABASE:
                  $results_array = &$our_db_results;
                  break;
               case self::RESULT_XML:
                  $results_array = &$xml_feeds_results;
                  break;
            }
            
            // Timers
            if (isset($feed_params['sb_time'])) {
               $name = $feed_params['name'];
               $i = 0;
               while (isset($this->timers[$name])) {
                  $name = $feed_params['name'] . '_' . (++$i);
               }
               $this->timers[$name] = $feed_params['sb_time'];
            }
            
            $xml_results = $xml->xpath('results/result');
            if ($xml_results) {
               foreach ($xml_results as $res) {
                  $result_params = array();
                  foreach ($res->children() as $name => $value) {
                     $result_params[$name] = (string) $value;
                  }
                  // Extra параметры для вывода всякой фигни
                  $result_params['extra'] = '';
                  $result_params['feed_params'] = $feed_params;
                  array_push($results_array, $result_params);
               }
            }
         
         } catch (Exception $e) {
         
         }
      }
      
      // Сохраняем в объект
      $result->our_db_results = $our_db_results;
      $result->xml_feeds_results = $xml_feeds_results;
//      $result->organic_results = $organic_results;
      
      return $result;
   }

   /**
    * Парсинг результатов работы клиента (из массива)
    *
    * @param array $content
    * @return object
    */
   private function parseSearchResultsArray($content) {
      // Возвращаемый объект с результатами
      $result = new stdClass();      
      // Массивы результатов по типам
      if (isset($content['ourdatabase'])){
         foreach ($content['ourdatabase'] as $key => $value) {
            $content['ourdatabase'][$key]['feed_params'] = array(
               'sb_type' => 'ourdatabase'
            );
            $content['ourdatabase'][$key]['extra'] = ""; 
         }
         $result->our_db_results = $content['ourdatabase'];
      }else{
         $result->our_db_results = array();
      }
      if (isset($content['xml_feeds'])){
         foreach ($content['xml_feeds'] as $key => $value) {
            $content['xml_feeds'][$key]['feed_params'] = array(
               'sb_type' => 'xml'
            );
            $content['xml_feeds'][$key]['extra'] = ""; 
         }
         $result->xml_feeds_results = $content['xml_feeds'];
      }else{
      $result->xml_feeds_results = array();
      }
      return $result;
   }      
   
   /**
    * Парсинг результатов работы клиента (поточный режим)
    *
    * @param string $content
    * @return object
    */
   private function parseSearchResultsStream($content) {
      // Возвращаемый объект с результатами
      $result = new stdClass();
      
      // Массивы результатов по типам
      $our_db_results = array();
      $xml_feeds_results = array();
      
      // Парсим
      $sources = array_filter(array_map('trim', explode(Search_builder::SOURCES_DELIMITER, $content)));
      foreach ($sources as $id => $contents) {
         try {
            $xml = new SimpleXMLElement($contents);
            $feed_params = array('client_file' => $id, 'total_results' => 0);
            $total = 0;
            if (isset($xml->params)) {
               foreach ($xml->params->children() as $name => $value) {
                  $feed_params[$name] = (string) $value;
               }
            }
            if (isset($xml->total)) {
               $feed_params['total_results'] = (int) $xml->total;
            }
            
            // Определяем тип результата
            switch ($feed_params['sb_type']) {
               case self::RESULT_OURDATABASE:
                  $results_array = &$our_db_results;
                  break;
               case self::RESULT_XML:
                  $results_array = &$xml_feeds_results;
                  break;
            }
            
            // Timers
            if (isset($feed_params['sb_time'])) {
               $name = $feed_params['name'];
               $i = 0;
               while (isset($this->timers[$name])) {
                  $name = $feed_params['name'] . '_' . (++$i);
               }
               $this->timers[$name] = $feed_params['sb_time'];
            }
            
            $xml_results = $xml->xpath('results/result');
            if ($xml_results) {
               foreach ($xml_results as $res) {
                  $result_params = array();
                  foreach ($res->children() as $name => $value) {
                     $result_params[$name] = (string) $value;
                  }
                  // Extra параметры для вывода всякой фигни
                  $result_params['extra'] = '';
                  $result_params['feed_params'] = $feed_params;
                  array_push($results_array, $result_params);
               }
            }
         
         } catch (Exception $e) {
         
         }
      }
      
      // Сохраняем в объект
      $result->our_db_results = $our_db_results;
      $result->xml_feeds_results = $xml_feeds_results;
      
      return $result;
   }
   
   /**
    * Получение массива результатов поиска
    *
    * @param string $content
    * @return array
    */
   private function getSearchResults($content) {
      $this->timers = array();
      $result = $this->parseSearchResultsArray($content);
      $our_db_results = $result->our_db_results;
      $xml_feeds_results = $result->xml_feeds_results;
//      $organic_results = $result->organic_results;
      // Получаем тип ourdatabase объявлений
      $this->ad_type = self::TYPE_TEXT;
      if (0 < count($our_db_results)) {
         // Если нашлись ourdatabase результаты, то смотрим на их тип
         $result = current($our_db_results);
         if ((array_key_exists('ad_type', $result)) && (self::TYPE_IMAGE == $result['ad_type'])) {
            if ('true' == $result['is_flash']) {
               $this->ad_type = self::TYPE_FLASH;
            } else {
               $this->ad_type = self::TYPE_IMAGE;
            }
         }
      }
      
      if (0 < count($xml_feeds_results)) {
         // Для xml результатов переопределяем биды
         foreach ($xml_feeds_results as $offset => &$result) {
            // Программа для XML фидов - CPC
            $result['program_type'] = self::PROG_CPC;
            $result['pre_bid'] = $result['bid'];
            $result['bid'] = round($result['bid'] * $result['commission'] / 100, Sppc_Pay::PRECISION);
         }
         unset($result);
      }
      
      $this->results = array();
      if (self::TYPE_TEXT == $this->ad_type) {
         // Одновременно разбиваем рекламные объявления на программы
         $prog_results = array();
         $primary_program = self::PROG_CPM;
         $find_primary_program = false;
         foreach ($our_db_results as $result) {
            if (!$find_primary_program && ($result['program_type'] == self::PROG_CPM)) {
               $primary_program = self::PROG_CPM;
               $find_primary_program = true;
            }
            if (!isset($prog_results[$result['program_type']])) {
               $prog_results[$result['program_type']] = array();
            }
            array_push($prog_results[$result['program_type']], $result);
         }
         // Мержим CPC и сортируем
         $prog_results[self::PROG_CPC] = $xml_feeds_results;
         
         usort($prog_results[self::PROG_CPC], array($this, 'compare_bid'));
         
         // Мержим результаты в зависимости от порядка показа программ
         if (isset($prog_results[self::PROG_FLAT_RATE])) {
            $this->results = $prog_results[self::PROG_FLAT_RATE];
         }
         if ($primary_program == self::PROG_CPM) {
            // Сначала показываем CPM, затем CPC
            if (isset($prog_results[self::PROG_CPM])) {
               $this->results = array_merge($this->results, $prog_results[self::PROG_CPM]);
            }
            if (isset($prog_results[self::PROG_CPC])) {
               $this->results = array_merge($this->results, $prog_results[self::PROG_CPC]);
            }
         }
      } else {
         $this->results = $our_db_results;
      }
      
      $this->total_results_count = count($this->results);
      // Отсекаем лишние результаты
      if (0 < $this->count) {
         $offset = $this->count * $this->page;
         $this->results = array_slice($this->results, $offset, $this->count);
      }
      
      // Для статистики
      $xml_stats = array();
      $ourdatabase_stats = array();
      
      // получаем идентификатор паблишера

         $currentPublisherId = 1;
      
      
      // Обрабатываем результаты
      foreach ($this->results as $id => $result) {
         // Вычисляем биды
         $bid = $result['bid'];
         if (self::RESULT_OURDATABASE == $result['feed_params']['sb_type']) {
            // Наша база
            if (self::PROG_CPM == $result['program_type']) {
               $bid = $result['cost'] / 1000;
            }
         }
         $result['bid'] = round($bid, Sppc_Pay::PRECISION);
         if ($this->enable_stats) {
         	
            if (self::RESULT_XML == $result['feed_params']['sb_type']) {
               // Подготавливаем статистику для XML результата
               $temp = array(
                  'id_group_site_channel' => 0,
                  'id_ad' =>                 0,
                  'id_feed' =>               $result['id_feed'],
                  'id_channel' =>            $this->id_channel,
                  'destination_url' =>       $result['click_url'],
                  'spent' =>                 $result['bid']
               );
               // Генерируем идентификатор клика
               $result['clickid'] = Sppc_Stats_Utils::generateClickId($temp);
               $click_data = array(
                  'clickid' => $result['clickid'],
                  'id_feed' => $result['id_feed'], 
                  'id_publisher' => $currentPublisherId, 
                  'id_site'      => $this->id_site, 
                  'id_channel'   => $this->id_channel, 
                  'position' => $id + 1, 
                  'click_url' => $result['click_url'], 
                  'bid'          => $result['bid']
               );

               array_push($xml_stats, $click_data);
            } elseif ((self::RESULT_OURDATABASE == $result['feed_params']['sb_type'])) 
            {
               // Подготавливаем статистику для Ourdatabase результата
               $temp = array(
                  'id_group_site_channel' =>  $result['id_group_site_channel'],
                  'id_ad' =>                  $result['id'],
                  'id_feed' =>                0,
                  'id_channel' =>             $this->id_channel,
                  'destination_url' =>        $result['click_url'],
                  'spent' =>                  0
               );
               
               // Генерируем идентификатор клика
               $result['clickid'] = Sppc_Stats_Utils::generateClickId($temp);
               
               $click_data = array(
                  'clickid' => $result['clickid'], 
                  'program_type' => $result['program_type'], 
                  'ad_type' => $result['ad_type'], 
                  'id_advertiser' => $result['id_advertiser'], 
                  'id_campaign' => $result['id_campaign'], 
                  'id_group' => $result['id_group'], 
                  'id_group_site_channel' => isset($result['id_group_site_channel']) ? $result['id_group_site_channel'] : 0, 
                  'id_group_site'         => isset($result['id_group_site']) ? $result['id_group_site'] : 0, 
                  'id_ad' => $result['id'], 
                  'id_publisher' => $currentPublisherId, 
                  'id_site'               => $this->id_site, 
                  'id_channel'            => $this->id_channel, 
                  'position' => $id + 1, 
                  'click_url' => $result['click_url'], 
                  'bid' => $result['bid'], 
               );

               array_push($ourdatabase_stats, $click_data);
            }
         }
         // Формируем click_url 
         $result['click_url'] = base_url() . 'click?id=' . $result['clickid'];
         // Формируем display_url
         $result['display_url'] = rtrim(preg_replace('~^[a-z]+://~', '', $result['display_url']), '/');
         // Формируем filename
         if (isset($result['filename'])) {
            $result['filename'] = base_url() . 'files/images/' . $result['filename'];
         }
         $this->results[$id] = $result;
      }
      
      if ($this->enable_stats) {
         $this->setXmlStats($xml_stats);
         $this->setOurdatabaseStats($ourdatabase_stats);
      }
      
      if ($this->enable_stats && $this->enable_alternative_stats && (0 == count($this->results))) {
         // Регистрируем alternative impression
         // Собираем данные для регистрации
         $click_data = array(
            'id_publisher' => 0 < $this->id_channel ? $this->channel_info->id_publisher : $this->obj->global_variables->get('admin_entity'), 
            'id_site'      => $this->id_site, 
            'id_channel'   => $this->id_channel
         );
         $this->setAlternativeImpressionStats($click_data);
      }
      
      if ($this->enable_stats && 0 < count($this->clicks)) {
      
         $this->registerStat($this->clicks);
      }
      
      // Группируем результаты (если это нужно)
      $this->group_results = array();
      if ($this->use_group_results) {
         foreach ($this->results as $result) {
            $ident = $result['feed_params']['client_file'];
            if (!isset($this->group_results[$ident])) {
               $this->group_results[$ident] = $result['feed_params'];
               $this->group_results[$ident]['data'] = array();
            }
            array_push($this->group_results[$ident]['data'], $result);
         }
         $this->group_results = array_values($this->group_results);
      }
      
      return $this->results;
   }
   
   /**
    * Универсальный метод для установки статистики
    * 
    * @param array $results
    */
   public function setStats($results) {
      foreach ($results as $row) {
         if (array_key_exists('sb_type', $row)) {
            switch ($row['sb_type']) {
               case self::RESULT_OURDATABASE:
                  $this->setOurdatabaseStats(array($row));
                  break;
               case self::RESULT_XML:
                  $this->setXmlStats(array($row));
                  break;
            }
         }
      }
   }
   /**
    * Установка статистики по показу xml результатов
    *
    * @param array $results
    *    Массив данных для статистики ассоциативных массивов следующего формата:
    *       clickid             - Идентификатор клика (*)
    *       id_feed             - Идентификатор XML фида (*)
    *       id_publisher        - Идентификатор паблишера
    *       id_site             - Идентификатор сайта(*)
    *       id_channel          - Идентификатор канала(*)
    *       position            - Позиция относительно всех остальных объявлений (*)
    *       click_url           - Click URL (*)
    *       bid                 - Bid
    */
   public function setXmlStats($results) {
      $time = time();
      foreach ($results as $row) {
         $click_data = array(
            'impressions'     => 1,
            'id_feed'         => $row['id_feed'], 
            'id_site'         => isset($row['id_site']) ? $row['id_site'] : '', 
            'id_channel'      => isset($row['id_channel']) ? $row['id_channel'] : '', 
            'destination_url' => $row['click_url'], 
            'spent'           => 0,
            'earned_admin'    => 0 
         );
         array_push($this->clicks, $click_data);
      }
   }
   
   /**
    * Усьановка статистики по ourdatabase результатам
    *
    *    Массив данных для статистики ассоциативных массивов следующего формата:
    *       clickid               - Идентификатор клика
    *       program_type          - Тип программы Flat_Rate, CPM
    *       ad_type               - Тип рекламного объявления image, text
    *       id_advertiser         - Идентификатор рекламодателя (*)
    *       id_campaign           - Идентификатор кампании (*)
    *       id_group              - Идентификатор группы (*)
    *       id_ad                 - Идентификатор рекламного объявления (*)
    *       id_publisher          - Идентификатор паблишера
    *       id_site               - Идентификатор сайта(*)
    *       id_channel            - Идентификатор канала(*)
    *       id_group_site_channel - Идентификатор связи группы с сайтом/каналом
    *       id_group_site         - Идентификатор связи группы с сайтом
    *       position              - Позиция относительно всех остальных объявлений
    *       click_url             - Click URL (*)
    *       bid                   - Bid
    */
   public function setOurdatabaseStats($results) {
      $time = time();
      foreach ($results as $row) {
         $click_data = array(
            'impressions'           => 1,
            'id_entity_advertiser'  => $row['id_advertiser'], 
            'id_campaign'           => $row['id_campaign'], 
            'id_group'              => $row['id_group'], 
            'id_ad'                 => $row['id_ad'], 
            'id_site'               => isset($row['id_site']) ? $row['id_site'] : '', 
            'id_channel'            => isset($row['id_channel']) ? $row['id_channel'] : '', 
            'id_group_site_channel' => isset($row['id_group_site_channel']) ? $row['id_group_site_channel'] : '', 
            'destination_url'       => $row['click_url']
         );
         $this->registerImpression($row);
         array_push($this->clicks, $click_data);
      }
   }
   
   /**
    * Установка статистики по альтернативному показу
    *
    * @param array $results
    *    Массив данных для статистики ассоциативных массивов следующего формата:
    *       id_publisher        - Идентификатор паблишера
    *       id_site             - Идентификатор сайта(*)
    *       id_channel          - Идентификатор канала(*)
    */
   public function setAlternativeImpressionStats($row) {
      $time = time();
      $click_data = array(
         'alternative_impressions' => 1,
         'id_site'                 => isset($row['id_site']) ? $row['id_site'] : '', 
         'id_channel'              => isset($row['id_channel']) ? $row['id_channel'] : ''
      );
      array_push($this->clicks, $click_data);
   }
   
   /**
    * Метод загрузки необходимых параметров
    *
    * Вызывается во время поиска
    */
   public function loadParameters() {
      parent::loadParameters();
      // Определяем namespace для айдишников использованных рекламных объявлений
      if (false !== $this->obj->input->get('ck')) {
         $this->nsck = $this->obj->input->get('ck');
      }
   }
   
   /**
    * Получение идентификатора сайта
    *
    * @return integer
    */
   public function getSite() {
      return $this->id_site;
   }
   
   /**
    * Получение идентификатора канала
    *
    * @return integer
    */
   public function getChannel() {
      return $this->id_channel;
   }
   
   /**
    * Получение типа поиска
    *
    * @return string
    */
   public function getSearchType() {
      return $this->search_type;
   }
   
   /**
    * Получение типа рекламы
    *
    * @return string
    */
   public function getAdType() {
      return $this->ad_type;
   }
   
   /**
    * Получение реального реферера страницы
    *
    * @return string
    */
   public function getRealReferer() {
      return $this->real_referer;
   }
   
   /**
    * Используются ли flash объявления
    *
    * @return bool
    */
   public function getUseFlash() {
      return $this->use_flash;
   }
   
   /**
    * Использовать ли flash объявления
    *
    * @param bool $use_flash
    */
   public function setUseFlash($use_flash) {
      $this->use_flash = $use_flash;
   }
   
   /**
    * Установка реального реферера страницы
    *
    * @param string $real_referer
    */
   public function setRealReferer($real_referer) {
      $this->real_referer = $real_referer;
   }
   
   /**
    * Установка сайта
    *
    * @param integer $id_site
    */
   public function setSite($id_site) {
      $this->id_site = $id_site;
      $this->site_info = $this->loadSiteInfo();
   }
   
   /**
    * Установка канала
    *
    * @param integer $id_channel
    */
   public function setChannel($id_channel) {
      $this->id_channel = $id_channel;
      $this->channel_info = $this->loadChannelInfo();
      // Устанавливаем режим отображения рекламных объявлений
      if ($this->channel_info) {
         $this->setDisplayAds($this->channel_info->display_ads);
         // Устанавливаем количество результатов
         $this->setCount($this->channel_info->max_ad_slots);
      }
   }
   
   /**
    * Установка типа поиска
    *
    * @param unknown_type $search_type
    */
   public function setSearchType($search_type) {
      $this->search_type = $search_type;
   }
   /**
    * Получение режима отображения рекламных объявлений
    *
    * @return string
    */
   public function getDisplayAds() {
      return $this->display_ads;
   }
   
   /**
    * Установка режима отображения рекламных объявлений
    *
    * @param string $display_ads
    */
   public function setDisplayAds($display_ads) {
      $this->display_ads = $display_ads;
   }   
   /**
    * Включение статистики
    *
    */
   public function enableStats() {
      $this->enable_stats = true;
   }
   /**
    * Включение статистики по alternative impressions
    */
   public function enableAlternativeStats() {
      $this->enable_alternative_stats = true;
   }
   
   /**
    * Выключение статистики
    *
    */
   public function disableStats() {
      $this->enable_stats = false;
   }
   /**
    * Выключение статистики по alternative impressions
    */
   public function disableAlternativeStats() {
      $this->enable_alternative_stats = false;
   }
   /**
    * Получение количества необходимых рекдамных объявлений
    *
    * @return int
    */
   public function getCount() {
      return $this->count;
   }
   
   /**
    * Установка количества необходимых рекдамных объявлений
    *
    * @param int $count
    */
   public function setCount($count) {
      $this->count = $count;
   }
   
   /**
    * Включение страндарной схемы работы сайтов/каналов
    *
    */
   public function enableUseStandartChannels() {
      $this->use_standart_channels = true;
   }
   
   /**
    * Выключение стандартной схемы работы сайтов/каналов
    *
    */
   public function disableUseStandartChannels() {
      $this->use_standart_channels = false;
   }
   
   /**
    * Включение группировки результатов
    *
    */
   public function enableGroupResults() {
      $this->use_group_results = true;
   }
   
   /**
    * Выключение группировки результатов
    *
    */
   public function disableGroupResults() {
      $this->use_group_results = false;
   }
   
   /**
    * Получение информации по сайту
    *
    * @return unknown
    */
   public function getSiteInfo() {
      return $this->site_info;
   }
   
   /**
    * Получение информации по каналу
    *
    * @return unknown
    */
   public function getChannelInfo() {
      return $this->channel_info;
   }
   
   /**
    * Метод получения информации по сайту
    *
    * @return object
    */
   private function loadSiteInfo() {
      $site = null;
      if (!empty($this->id_site)) {
         $feEngine = $this->obj->config->item('sites_frontend_engine');
         $feOptions = $this->obj->config->item('sites_frontend');
         $beEngine = $this->obj->config->item('sites_backend_engine');
         $beOptions = $this->obj->config->item('sites_backend');
         if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
            mkdir($beOptions['cache_dir']);
            chmod($beOptions['cache_dir'], 0777);
         }
         $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
         if (false === ($site = $cache->load('site_' . $this->id_site))) {
            $site = null;
            // Получаем инфу по сайту
            $this->obj->db->select('s.id_entity_publisher id_publisher, s.id_site, s.name, s.status', false)
               ->from('sites s')
               ->where('s.id_site', $this->id_site)
               ->limit(1);
            $query = $this->obj->db->get();
            if (0 < $query->num_rows()) {
               $site = $query->row();
               $cache->save($site, 'site_' . $this->id_site);
            }
         }
      }
      return $site;
   }
   
   /**
    * Метод получения информации по каналу
    *
    * @return object
    */
   private function loadChannelInfo() {
      $channel = null;
      if (!empty($this->id_channel)) {
         $feEngine = $this->obj->config->item('channels_frontend_engine');
         $feOptions = $this->obj->config->item('channels_frontend');
         $beEngine = $this->obj->config->item('channels_backend_engine');
         $beOptions = $this->obj->config->item('channels_backend');
         if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
            mkdir($beOptions['cache_dir']);
            chmod($beOptions['cache_dir'], 0777);
         }
         $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
         if (false === ($channel = $cache->load('channel_' . $this->id_channel))) {
            $channel = null;
            // Получаем инфу по каналу
            $this->obj->db->select('s.id_entity_publisher id_publisher, s.id_site, c.name, c.ad_type,
               c.channel_type, c.ad_settings, d.id_dimension, d.width, d.height, d.max_ad_slots, c.status,
               sc.status link_status, sc.id_site_channel,
               c.blank_color, c.ad_sources', false)
               ->from('channels c')
               ->join('dimensions d', 'd.id_dimension = c.id_dimension')
               ->join('site_channels sc', 'sc.id_channel = c.id_channel')
               ->join('sites s', 's.id_site = sc.id_site')
               ->where('c.id_channel', $this->id_channel)
               ->where('s.id_site', $this->id_site)
               ->limit(1);
            $query = $this->obj->db->get();
            if (0 < $query->num_rows()) {
               $channel = $query->row();
               $channel->ad_sources = explode(',', $channel->ad_sources);
               if ((in_array(Sppc_Channel::AD_SOURCE_ADVERTISERS, $channel->ad_sources)) && 
            	   (in_array(Sppc_Channel::AD_SOURCE_XMLFEEDS, $channel->ad_sources))) {
            	   $channel->display_ads = self::DISPLAY_ADV_XML;
               } elseif (in_array(Sppc_Channel::AD_SOURCE_ADVERTISERS, $channel->ad_sources)) {
            	  $channel->display_ads = self::DISPLAY_ADV_ONLY;
               } elseif (in_array(Sppc_Channel::AD_SOURCE_XMLFEEDS, $channel->ad_sources)) {
            	  $channel->display_ads = self::DISPLAY_XML_ONLY;
               } else {
            	  $channel->display_ads = null;
               }
               
               $cache->save($channel, 'channel_' . $this->id_channel);
            }
         }
      }

      return $channel;
   }
   
   /**
    * Подготовка протекшенов
    *
    */
   protected function prepareProtections() {
      // Создаем класс протекшенов
      $this->protections = new Sppc_Protection_Target_Search();
   }
   
   /**
    * Подготовка параметров для протекшенов
    *
    */
   protected function prepareProtectionParameters() {
      parent::prepareProtectionParameters();
      // Инициализируем класс поисковых параметров
      $parameters = Sppc_Protection_Parameters::getInstance();
      $parameters->setParameter('referer', $this->real_referer);
      $parameters->setParameter('engine', 'search');
      $parameters->setParameter('ad_type', $this->ad_type);
   }
   
   /**
    * Тест на протекшены
    *
    * @return boolean
    */
   protected function checkProtections() {
      $check = parent::checkProtections();
      if (Sppc_Protection::DENY != $this->result) {
         $check = true;
      }
      return $check;
   }
   
   
   /**
    * Загрузка массива фидов
    *
    * @param bool $use_ourdatabase
    */
   private function loadFeeds($use_ourdatabase = true) {
      $this->feed_params = array_merge($this->feed_params, $this->getParams());
      
      if ($use_ourdatabase && self::DISPLAY_XML_ONLY != $this->display_ads) {
         $this->feed_params['feeds'][self::RESULT_OURDATABASE] = $this->getOurdatabaseFeed(self::RESULT_OURDATABASE);
      }
      
      if (self::DISPLAY_ADV_ONLY != $this->display_ads && false !== strpos($this->channel_info->ad_type, self::CHANNEL_TEXT)) {
         $this->feed_params['feeds'] = array_merge($this->feed_params['feeds'], $this->getXmlFeeds());
      }
   }
   
   /**
    *  feeds for display_search controllers
    */
   public function loadOtherFeeds() {
      if (self::DISPLAY_ADV_ONLY != $this->display_ads) {
         $this->feed_params['feeds']  = array_merge($this->feed_params['feeds'], $this->getXmlFeeds());
      }
   }
   
      /**
    * Get Ourdatabase Feed
    * @return array
    */
   private function getParams() {
      // Подготовливаем ourdatabase
      $params = array(
         'site'          => $this->id_site, 
         'channel'       => $this->id_channel, 
         'publisher'     => 1, 
         'country'       => $this->country, 
         'lang'          => $this->language, 
         'browser'       => $this->browser, 
         'ip'            => $this->ip_address,
         'ua'            => $this->user_agent, 
         'page'          => $this->referer, 
         'referer'       => $this->real_referer, 
         'use_flash'     => $this->use_flash, 
         'count'         => $this->count,
         'ad_type'       => $this->channel_info->ad_type,
         'ad_sources'     => $this->channel_info->ad_sources,
         'dimension'     => $this->channel_info->id_dimension
      );
      return $params;
   }
   
   /**
    * Get XML Feeds
    * @return array 
    */
   private function getXmlFeeds() {
      $feeds = array();
      $feEngine = $this->obj->config->item('feeds_frontend_engine');
      $feOptions = $this->obj->config->item('feeds_frontend');
      $beEngine = $this->obj->config->item('feeds_backend_engine');
      $beOptions = $this->obj->config->item('feeds_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      if (false === ($feeds = $cache->load('feed_for_channel_' . $this->id_channel))) {
         $feeds = array();
         // Подгружаем остальные фиды
         $sql = "
            SELECT
               id_feed,
               name,
               affiliate_id_1,
               affiliate_id_2,
               affiliate_id_3,
               url,
               commission
            FROM
               feeds
            WHERE
               status = 'active'
            ORDER BY
               id_feed
         ";
         $query = $this->obj->db->query($sql);
         if (0 < $query->num_rows()) {
            foreach ($query->result() as $row) {
               $url = $row->url;
               $url = str_replace(array('[ID1]', '[ID2]', '[ID3]', '[COUNT]'), 
                                  array_map('urlencode', array($row->affiliate_id_1, $row->affiliate_id_2, $row->affiliate_id_3, $this->count)),
                                  $url);
               $feed = array(
                  'name'       => strtolower($row->name), 
                  'feed'       => $row->id_feed, 
                  'url'        => $url, 
                  'commission' => $row->commission, // Revenue share
               );
               $feeds[$feed['name']] = $feed;
            }
         }
         $cache->save($feeds, 'feed_for_channel_' . $this->id_channel);
      }
      return $feeds;
   }
   
   /**
    * Get Ourdatabase Feed
    * @return array
    */
   private function getOurdatabaseFeed($name) {
      $ids = array();
      if ($this->obj->config->item('use_show_iframe_wrapper')) {
         $ids = $this->getUsedIds();
      }
      // Подготовливаем ourdatabase
      $db = array();
      include APPPATH . 'config/database.php';
      $feed = array(
         'name'          => $name, 
         'eurl'          => base64_encode('mysql://' . $db['default']['username'] . ':' . $db['default']['password'] . '@' . $db['default']['hostname'] . '/' . $db['default']['database'] . '?charset=' . $db['default']['char_set']), 
         'commission'    => '100', // Revenue share 
         'ids'           => implode(',', $ids)
      );
      return $feed;
   }
   
   /**
    * Обработка массива фидов
    *
    */
   private function prepareFeeds() {
      // Проставляем фид урлам значения
      foreach ($this->feed_params['feeds'] as &$feed) {
         if (isset($feed['url'])) {
            $feed['url'] = str_replace(array('[IP_ADDRESS]', '[IP_PROXY]', '[USER_AGENT]', '[REFERER]','[ACCEPT]'), array_map('urlencode', array($this->ip_address, $this->ip_address_proxy, $this->user_agent, $this->referer, $this->accept)), $feed['url']);
         }
      }
      unset($feed);
   }
   
   /**
    * Метод получения данных с клиента
    *
    * @param int $count
    * @return bool
    */
   private function loadResults() {
      // Формируем запрос на клиент
         // XML feeds
         foreach ($this->feed_params['feeds'] as $feed){
            switch ($feed['name']) {
               case self::RESULT_OURDATABASE:
                  $feed['sb_type'] = $feed['name'];
                  break;
               default:
                  $feed['sb_type'] = self::RESULT_XML;
                  break;
            }                        
         }
         $CI =& get_instance();
         $CI->load->model('ourdatabaseevo');
         $content = $CI->ourdatabaseevo->parse($this->feed_params);
      $this->getSearchResults($content);
      return true;
   }
   
   /**
    * Регистрация показа
    *
    * Здесь происходит заморозка купленного пакета
    *
    * @param array $data
    */
   private function registerImpression($data) {
      $this->obj->benchmark->mark('register_impressions_start');
      if (self::PROG_CPM == $data['program_type']) {
         // CPM
         $sql = "
            UPDATE
               group_site_channels
            SET
               impressions = impressions - 1,
               status = IF(impressions <= 0, 'completed', status),
               current_impressions = current_impressions + 1
            WHERE
               id_group_site_channel = ?
            LIMIT
               1
         ";
         $this->obj->db->query($sql, array($data['id_group_site_channel']));
      }
      
      if (self::PROG_CPC == $data['program_type']) {
         // current_impression
         $sql = "
            UPDATE
               group_sites
            SET
               impressions = impressions + 1
            WHERE
               id_group_site = ?
            LIMIT
               1
         ";
         $this->obj->db->query($sql, array($data['id_group_site']));
      }
      
      if (self::PROG_FLAT_RATE == $data['program_type']) {
         // current_impression
         $sql = "
            UPDATE
               group_site_channels
            SET
               current_impressions = current_impressions + 1
            WHERE
               id_group_site_channel = ?
            LIMIT
               1
         ";
         $this->obj->db->query($sql, array($data['id_group_site_channel']));
      }
         
      if (self::PROG_FLAT_RATE == $data['program_type'] || self::PROG_CPM == $data['program_type']) {
         // Импрешн бюджет
         if (0 < $data['id_group']) {
            $sql = "
               UPDATE
                  groups
               SET
                  frequency_coup_current = IFNULL(frequency_coup_current, 0) + 1
               WHERE
                  id_group = " . $this->obj->db->escape($data['id_group']) . " AND
                  frequency_coup IS NOT NULL AND
                  frequency_coup_current > 0
               LIMIT
                  1
            ";
            $this->obj->db->query($sql);
         }
      }
      $this->obj->benchmark->mark('register_impressions_end');
   }
   
   /**
    * Добавление к использованным рекламным объявлениям в этом namespace еще рекламные объявления
    *
    * @param array $ids
    */
   private function addUsedIds($ids) {
      if (!empty($this->nsck)) {
         $feEngine = $this->obj->config->item('nsck_frontend_engine');
         $feOptions = $this->obj->config->item('nsck_frontend');
         $beEngine = $this->obj->config->item('nsck_backend_engine');
         $beOptions = $this->obj->config->item('nsck_backend');
         if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
            mkdir($beOptions['cache_dir']);
            chmod($beOptions['cache_dir'], 0777);
         }
         $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
         if (false === ($stored_ids = $cache->load($this->nsck))) {
            $stored_ids = array();
         }
         $ids = array_unique(array_merge($stored_ids, $ids));
         $cache->save($ids, $this->nsck);
      }
      if ($this->firephp) {
         $this->firephp->log($this->nsck, 'Namespace');
      }
   }
   
   /**
    * Получение использованных рекламных объявлений в этом namespace
    *
    * @return array
    */
   private function getUsedIds() {
      $ids = array();
      if (!empty($this->nsck)) {
         $feEngine = $this->obj->config->item('nsck_frontend_engine');
         $feOptions = $this->obj->config->item('nsck_frontend');
         $beEngine = $this->obj->config->item('nsck_backend_engine');
         $beOptions = $this->obj->config->item('nsck_backend');
         if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
            mkdir($beOptions['cache_dir']);
            chmod($beOptions['cache_dir'], 0777);
         }
         $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
         if (false === ($ids = $cache->load($this->nsck))) {
            $ids = array();
         }
      }
      return $ids;
   }

   /**
    * Получение сообщения об ошибке
    *
    * @return string
    */
   public function getErrorMessage() {
      $message = '';
      if ('site_channel_blocked' == $this->error || 'site_channel_paused' == $this->error) {
         $message = __('Site or channel was blocked');
      } elseif ('site_channel_deleted' == $this->error) {
         $message = __('Site or channel was deleted');
      } elseif ('site_blocked' == $this->error || 'site_paused' == $this->error) {
         $message = __('Site was blocked');
      } elseif ('site_deleted' == $this->error) {
         $message = __('Site was deleted');
      } elseif ('channel_blocked' == $this->error || 'channel_paused' == $this->error) {
         $message = __('Channel was blocked');
      } elseif ('channel_deleted' == $this->error) {
         $message = __('Channel was deleted');
      } elseif ('site_not_found' == $this->error) {
         $message = __('Site not found');
      } elseif ('channel_not_found' == $this->error) {
         $message = __('Channel not found');
      }
      return $message;
   }
   /**
    * Set parked domain
    * 
    * @param Sppc_Parked_Domain $domain
    * @return void
    */
   public function setParkedDomain(Sppc_Parked_Domain $domain) {
      $this->parked_domain = $domain;
   }
   /**
    * Get parked domain 
    * 
    * @return Sppc_Parked_Domain|null
    */
   public function getParkedDomain() {
      return $this->parked_domain;
   }
   /**
    * Set current results page
    * 
    * @param int $page
    */
   public function setPage($page) {
      $this->page = $page;
   }
   /**
    * Return current results page
    * 
    * @return string
    */
   public function getPage() {
      return $this->page;
   }
   
   
   /**
    * Сравнение элементов массива результатов по биду
    *
    * @param array $a
    * @param array $b
    * @return int
    */
   private function compare_bid($a, $b) {
      if ($a['program_type'] == self::PROG_CPC && $b['program_type'] == self::PROG_CPC) {
         return (float) $b['bid'] > (float) $a['bid'] ? 1 : -1;
      } elseif ($a['program_type'] == self::PROG_CPC) {
         return 1;
      } elseif ($b['program_type'] == self::PROG_CPC) {
         return -1;
      }
      return 0;
   }
   /**
    * Возвращает число результатов полученных при последнем поисковом запросе
    * 
    * @return void
    */
   public function getTotalResultsCount() {
      return $this->total_results_count;
   }

   /**
    * Get client timers
    */
   public function getTimers() {
      return $this->timers;
   }
   
}
