<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* извлечение объявлений из базы данных
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Ourdatabaseevo extends CI_Model {

   const SOURCE_ADVERTISERS = "advertisers";
   const SOURSE_XML_FEEDS = "xml_feeds";
   const TYPE_TEXT = "text";
   const TYPE_IMAGE = "image";   
   const PROG_FLAT_RATE = "Flat_Rate";
   const PROG_CPM = "CPM";
   const OURDATABASE = 'ourdatabase';
   const BYTECITY = 'bytecity';   

   protected $advertisers = array();
   protected $imageResults = array();
   protected $textResults = array();
   protected $feedTextResults = array();
   protected $cpmGroups = array();
   
   public function __construct() {
      parent::__construct();
   }
   
   /**
    * Формирование SQL запроса выборки Flat Rate объявлений
    */
   protected function getFlatRateSql($type, $data, $date = null) {
      if (is_null($date)){
         $date = date('Y-m-d H:i:s');
      }
      $this->db
         ->select("
            a.id_ad AS id,
            c.id_entity_advertiser AS id_advertiser,
            c.id_campaign,
            c.id_targeting_group,
            g.id_group,
            gsc.id_group_site_channel,
            0 AS bid,
            a.title,
            a.description,
            a.description2,
            a.display_url,
            CONCAT(a.protocol, '://', a.click_url) AS click_url,
            gsc.avg_cost_$type AS cost,
            '$type' AS ad_type,
            '".Ourdatabaseevo::PROG_FLAT_RATE."' AS program_type", false);
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db->select("
            i.filename,
            i.is_flash,
            i.bgcolor");
      }
      $this->db
         ->from("ads a")
         ->join("groups g", "a.id_group = g.id_group")
         ->join("group_site_channels gsc", "gsc.id_group = g.id_group")
         ->join("site_channels sc", "sc.id_site_channel = gsc.id_site_channel")
         ->join("channel_program_types cpt", 
            "cpt.id_channel = sc.id_channel AND cpt.id_program = gsc.id_program")
         ->join("campaigns c", "g.id_campaign = c.id_campaign")
         ->join("entity_roles er", 
            "c.id_entity_advertiser = er.id_entity AND er.id_role=3")
         ->join("ad_types at", "a.id_ad_type = at.id_ad_type");
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db
            ->join("images i", "i.id_ad = a.id_ad")
            ->join("dimensions d", "d.id_dimension = i.id_dimension")
            ->join("channels ch", "sc.id_channel = ch.id_channel");         
      }
      $this->db
         // channel/site
         ->where("sc.id_channel", $data["id_channel"])
         ->where("sc.id_site", $data["id_site"])
         // program
         ->where("cpt.program_type", Ourdatabaseevo::PROG_FLAT_RATE)
         ->where("at.name", $type);
      if (isset($data["ids"]) && count($data["ids"]) > 0) {
         $this->db->where_not_in("a.id_ad", $data["ids"]);
      }
      
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db
            ->where("FIND_IN_SET('".Ourdatabaseevo::TYPE_IMAGE."', gsc.ad_type)", "", false)
            ->where("i.id_dimension", "ch.id_dimension", false);
         if ("" == $data["use_flash"]) {
            $this->db->where("i.is_flash", false);
         }
      }
      $this->db
         // statuses
         ->where("a.status", "active")
         ->where("g.status", "active")
         ->where("c.status", "active")
         ->where("er.status", "active")
         ->where("sc.status", "active")
         ->where("gsc.status", "active")
         // check end time
         ->where("gsc.end_date_time >", $date);
      $this->getAdvertisersRestriction($type);
      $this->db->order_by("RAND()");
   } //end getFlatRateSql   

   /**
    * Формирование SQL запроса выборки CPM объявлений
    */
   protected function getCpmSql($type, $data) {
      $this->db
         ->select("         
         	a.id_ad AS id,
            c.id_entity_advertiser AS id_advertiser,
            c.id_campaign,
            c.id_targeting_group,
            g.id_group,
            gsc.id_group_site_channel,
            0 AS bid,
            a.title,
            a.description,
            a.description2,
            a.display_url,
            CONCAT(a.protocol, '://', a.click_url) AS click_url,
            gsc.avg_cost_$type AS cost,
            '$type' AS ad_type,
            '".Ourdatabaseevo::PROG_CPM."' AS program_type", false);
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db->select("
           	i.filename,
            i.is_flash,
            i.bgcolor");
      }
      $this->db
         ->from("ads a")
         ->join("groups g", "a.id_group = g.id_group")
         ->join("group_site_channels gsc", "gsc.id_group = g.id_group")
         ->join("site_channels sc", "sc.id_site_channel = gsc.id_site_channel")
         ->join("channel_program_types cpt", 
            "cpt.id_channel = sc.id_channel AND cpt.id_program = gsc.id_program")
         ->join("campaigns c", "g.id_campaign = c.id_campaign")
         ->join("entity_roles er", 
            "c.id_entity_advertiser = er.id_entity AND er.id_role=3")
         ->join("ad_types at", "a.id_ad_type = at.id_ad_type");
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db
            ->join("images i", "i.id_ad = a.id_ad")
            ->join("dimensions d", "d.id_dimension = i.id_dimension")
            ->join("channels ch", "sc.id_channel = ch.id_channel");
      }
      $this->db
         // channel/site
         ->where("sc.id_channel", $data["id_channel"])
         ->where("sc.id_site", $data["id_site"])
         // program
         ->where("cpt.program_type", Ourdatabaseevo::PROG_CPM)
         ->where("at.name", $type);

      if (isset($data["ids"]) && count($data["ids"]) > 0) {
         $this->db->where_not_in($data["ids"]);
      }

      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db
            ->where("FIND_IN_SET('".Ourdatabaseevo::TYPE_IMAGE."', gsc.ad_type)","",false)
            ->where("i.id_dimension", "ch.id_dimension", false);
         if ("" == $data["use_flash"]) {
            $this->db->where("i.is_flash", false);
         }
      }
      $this->db
         // statuses 
         ->where("a.status", "active")
         ->where("g.status", "active")
         ->where("c.status", "active")
         ->where("er.status", "active")
         ->where("sc.status", "active")
         ->where("gsc.status", "active")
         // schedule
         ->where("(
            c.id_schedule IS NULL OR
            EXISTS (
               SELECT
                  1
               FROM
                  schedule_timetables st
                     INNER JOIN timetables t ON (st.id_timetable = t.id_timetable)
               WHERE
                  c.id_schedule = st.id_schedule AND
                  st.weekday = WEEKDAY(NOW()) + 1 AND
                  t.{$this->getTimetableField()} = 1
            ))", "", false);         
      $this->getAdvertisersRestriction($type);
      $this->db->order_by("RAND()");
   } //end getCpmSql   

   /**
    * Формирование SQL запроса выборки CPM групп
    */
   protected function getCpmGroupsSql($data) {
      $this->db
         ->select(
         	"c.id_entity_advertiser AS id_advertiser,
             c.id_campaign,
             c.id_targeting_group,
             g.id_group,
             gsc.id_group_site_channel,
             sc.id_site_channel,
             gsc.avg_cost_text AS text_cost,
             gsc.avg_cost_image AS image_cost,
             e.ballance AS balance,
             SUM(IF(a.id_ad_type=1,1,0)) AS text_ads,
             SUM(IF(a.id_ad_type=2,1,0)) AS image_ads")
         ->from("groups g")
         ->join("group_site_channels gsc", "gsc.id_group = g.id_group")
         ->join("site_channels sc", "sc.id_site_channel = gsc.id_site_channel")
         ->join("campaigns c", "g.id_campaign = c.id_campaign")
         ->join("entities e", "c.id_entity_advertiser = e.id_entity")
         ->join("entity_roles er", "c.id_entity_advertiser = er.id_entity AND er.id_role=3")
         ->join("ads a", "a.id_group=g.id_group", "left")
         ->where("sc.id_channel", $data["id_channel"])
         ->where("sc.id_site", $data["id_site"])
         ->where("c.id_campaign_type", "cpm_flatrate")
         ->where("g.status", "active")
         ->where("c.status", "active")
         ->where("er.status", "active")
         ->where("sc.status", "active")
         ->where("gsc.status", "active")
         ->where("(
            c.id_schedule IS NULL OR
            EXISTS (
               SELECT 1
               FROM schedule_timetables st
                  INNER JOIN timetables t ON (st.id_timetable = t.id_timetable)
               WHERE
                   c.id_schedule = st.id_schedule AND
                   st.weekday = WEEKDAY(NOW()) + 1 AND
                   t.{$this->getTimetableField()} = 1
            ))","",false)
         ->group_by("g.id_group");
   } //end getCpmGroupsSql   

   /**
    * Получение названия поля таблицы timetables (текущий час)
    */
   protected function getTimetableField() {
      return strftime("h_%H");
   } //end getTimetableField   
   
   /**
    * Get random ad by group id
    */
   protected function getAdByGroup($idGroup, $type, $data, &$ad) {
      $this->db->select("
         a.id_ad AS id,
         a.title,
         a.description,
         a.description2,
         a.display_url,
         CONCAT(a.protocol, '://', a.click_url) AS click_url", false);
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db->select("
            i.filename,
            i.is_flash,
            i.bgcolor");
      }
      $this->db
         ->from("ads a")
         ->join("ad_types at", "a.id_ad_type = at.id_ad_type");
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db->join("images i", "i.id_ad = a.id_ad");
      }
      $this->db
         ->where("a.id_group", $idGroup)
         ->where("at.name", $type)
         ->where("a.status", "active");
      if (isset($data["ids"]) && count($data["ids"]) > 0) {
         $this->db->where_not_in("a.id_ad", $data["ids"]);
      }
      if (Ourdatabaseevo::TYPE_IMAGE == $type) {
         $this->db->where("i.id_dimension", $data["id_dimension"]);
         if ("" == $data["use_flash"]) {
            $this->db->where("i.is_flash", false);
         }
      }
      $this->db->order_by("RAND()")->limit(1);
      $query = $this->db->get();
      if ($query->num_rows()) {
         $ad = $query->row_array();
         return true;
      }
      return false;
   } //end getAdByGroup   

   
   /**
    * Получение SQL ограничения по адвертизерам
    */
   protected function getAdvertisersRestriction($type) {
      if (isset($this->advertisers[$type]) && count($this->advertisers[$type]) > 0) {
         $this->db->where_not_in($this->advertisers[$type]);
      }
   } //end getAdvertisersRestriction

   /**
    * Получение рекламных групп (с автоматической проверкой на таргетинг)
    *
    * @param string $type Тип объявления: text, image
    * @param string $program Тип программы: Flat_Rate, CPM
    * @param array $data Критерии поиска
    * @param array &$results Выходной массив с результатами
    * @return int Количество результатов
    */
   protected function getGroups($program, $data, &$results) {
      $CI =& get_instance();
      $CI->load->model("targeting");
      $count = 0;
      if (Ourdatabaseevo::PROG_CPM == $program) {
         $this->getCpmGroupsSql($data);
      }
      $query = $this->db->get();
      if ($query->num_rows()) {
         $listings = array();
         foreach ($query->result_array() as $row) {
            $listings[] = $row;
            $CI->targeting->addTargetingGroup($row["id_targeting_group"]);
         }
      } else {
         // Failed to execure query
         return false;
      }
      foreach ($listings as $listing) {
         if($CI->targeting->checkRules($listing["id_targeting_group"])) {
            unset($listing["id_targeting_group"]);
            $results[] = $listing;
            $count++;
         }         
      }
      return $count;
   } //end getGroups
   
   /**
    * Получение рекламных объявлений (с автоматической проверкой на таргетинг)
    *
    * @param string $type Тип объявления: text, image
    * @param string $program Тип программы: Flat_Rate, CPM, CPC
    * @param array $data Критерии поиска
    * @param array &$results Выходной массив с результатами
    * @return int Количество результатов
    */
   protected function getAds($type, $program, $data, &$results) {
      $CI =& get_instance();
      $CI->load->model("targeting");
      $count = 0;
      if (Ourdatabaseevo::PROG_FLAT_RATE == $program) {
         $this->getFlatRateSql($type, $data);
      } else if (Ourdatabaseevo::PROG_CPM == $program) {
         $this->getCpmSql($type, $data);
      }
      $query = $this->db->get();
      if (0 < $query->num_rows()) {
         $listings = array();
         
         foreach ($query->result_array() as $row) {
            $listings[] = $row;
            $CI->targeting->addTargetingGroup($row["id_targeting_group"]);
            $count++;            
         }         
         foreach ($listings as $ad) {
            if ((Ourdatabaseevo::PROG_FLAT_RATE == $program) || $CI->targeting->checkRules($ad["id_targeting_group"])) {                              
               $results[] = $ad;
            }
         }
      } else {
         return false;
      }
      return $count;
   } //end getAds   
   
   /**
    * Получение результатов из базы
    */
   public function parse($params) {
      $CI =& get_instance();
      $CI->load->model("targeting");
      
      // Идентификатор сайта
      $idSite = isset($params["site"])?$params["site"]:0;
      
      // Идентификатор канала
      $idChannel = isset($params["channel"])?$params["channel"]:0;
   
      // Использовать ли CPC
      $useCpc = isset($params["use_cpc"])?$params["use_cpc"]:false;
   
      // Страна
      $country = isset($params["country"])?$params["country"]:"UN";
      $CI->targeting->setCountry($country);
      
      // Использовать ли flash в картиночных объявлениях
      $useFlash = isset($params["use_flash"])?$params["use_flash"]:false;
   
      // Заюзаные рекламные объявления
      $ids = isset($params["ids"])?$params["ids"]:array();
   
      // Ad Type
      $adType = isset($params["ad_type"])?$params["ad_type"]:"text,image";
      
      // Dimension ID
      $idDimension = isset($params["dimension"])?$params["dimension"]:0;
   
      // Count
      $maxCount = isset($params["count"])?$params["count"]:1;
   
      // adTypes
      $adTypes = array();
      foreach (explode(",", $adType) as $type) {
         $adTypes[$type] = $type;
      };
      
      // adSources
      $adSources = array();
      foreach ($params["ad_sources"] as $ad_source) {
         $adSources[$ad_source] = $ad_source;
      };
      
      $params['feeds'] = isset($params["feeds"])?$params["feeds"]:false;
      // Получаем результаты
      $this->total = 0;
   
      // Вектор приоритетов программ
      $priorityPrograms[] = Ourdatabaseevo::PROG_FLAT_RATE;   
      $priorityPrograms[] = Ourdatabaseevo::PROG_CPM;
   
      // Получаем программы, поддерживаемые каналом
      $programs = array();
      $query = $this->db
         ->select("program_type")
         ->from("channel_program_types")
         ->where("id_channel", $idChannel)
         ->group_by("program_type")
         ->get();
      if ($query->num_rows()) {
         foreach ($query->result() as $row) {
            $programs[$row->program_type] = $row->program_type;
         }         
      }

      $data = array(
         "id_channel" => $idChannel,
         "id_site" => $idSite
      );
      if (isset($adSources[Ourdatabaseevo::SOURCE_ADVERTISERS]) && isset($params['feeds'][Ourdatabaseevo::OURDATABASE])) {
         // Получаем группы
         $countGroups = 0;      
         if (isset($programs[Ourdatabaseevo::PROG_CPM])) {
             $countGroups = $this->getGroups(Ourdatabaseevo::PROG_CPM, $data, $this->cpmGroups);
             if (!$countGroups) {
                 unset($programs[Ourdatabaseevo::PROG_CPM]);
             }
         }
      
         // Получаем рекламные объявления
         $data["use_flash"] = $useFlash;
         $data["ids"] = $ids;
         $data["count"] = 1; //очень странно
      
         $imageTotal = 0;
         if (isset($adTypes[Ourdatabaseevo::TYPE_IMAGE])) {
            // Получаем баннерные объявления
            $data["id_dimension"] = $idDimension;
         
            // Получаем картиночные объявления
            foreach ($priorityPrograms as $program) {
               $count = 0;
               if (isset($programs[$program])) {
                  $count = $this->getImageAds($program, $data);
                  $imageTotal += $count;
                  if ($imageTotal > 0) {
                     break;
                  }
               }
            }
         }
         
         $textTotal = 0;
         if (isset($adTypes[Ourdatabaseevo::TYPE_TEXT])) {
            // Получаем текстовые объявления
            $data["count"] = $maxCount;
            foreach ($priorityPrograms as $program) {
               $count = 0;
               if (isset($programs[$program])) {
                  $count = $this->getTextAds($program, $data);
                  $textTotal += $count;
                  if ($textTotal >= $maxCount) {
                     break;
                  } else {
                     // Урезаем количество получаемых листингов
                     $data["count"] = $maxCount - $textTotal;
                  }
               }
            }         
         }
      
         // Выбираем что выводить - image или text
      
         if (0 == $imageTotal) { 
            // Первый частный случай (если нет графичкских обьявлений)
            if (isset($adTypes[Ourdatabaseevo::TYPE_TEXT])){
               //Если разрешены текстовык
               if (($textTotal < $maxCount) && (isset($adSources[Ourdatabaseevo::SOURSE_XML_FEEDS]))){
                  $total = $textTotal;
                  $results[Ourdatabaseevo::OURDATABASE] = $this->textResults;
                  $feedTextTotal = 0;
                  // Получаем фидовые текстовые объявления
                  if (isset($params['feeds'][Ourdatabaseevo::BYTECITY])){
                     $count = $this->getFeedTextAds($params['feeds'][Ourdatabaseevo::BYTECITY]['url'],$maxCount-$textTotal);
                     $feedTextTotal += $count;
                     $feedTextResults = $this->feedTextResults;
                     $results[Ourdatabaseevo::SOURSE_XML_FEEDS] = $feedTextResults;
                  }
                  $total += $feedTextTotal;
               } else {
                  $total = $textTotal;
                  $results[Ourdatabaseevo::OURDATABASE] = $this->textResults;
               }
            } else {
            $total = 0;
            $results[Ourdatabaseevo::OURDATABASE] = array();
            }
         } elseif (0 == $textTotal) {
            if (isset($adTypes[Ourdatabaseevo::TYPE_IMAGE])){
               // Второй частный случай (графические у нас точно есть поэтому фид не нужен)
               $total = $imageTotal;
               $results[Ourdatabaseevo::OURDATABASE] = $this->imageResults;
            }//проверить нужно ли else
         } else {
            // Общий случай
            // Расчитываем денежный эквивалент блока для текстовой и банерной рекламы
            $textCost = 0;
            $imageCost = 0;
            $textHasFlatRate = false; 
            $imageHasFlatRate = false;
            foreach ($this->textResults as $result) {
               $textCost += $result["cost"];
               if (Ourdatabaseevo::PROG_FLAT_RATE == $result["program_type"]) {
                  $textHasFlatRate = true;
               }
            }
            foreach ($this->imageResults as $result) {
               $imageCost += $result["cost"];
               if (Ourdatabaseevo::PROG_FLAT_RATE == $result["program_type"]) {
                  $imageHasFlatRate = true;
               }
            }
            
            // Choose type of ads
            $distrib = array();
            $sumCost = 0;
            if ($textHasFlatRate || $imageHasFlatRate) {
               // FlatRate
               if ($textHasFlatRate && 0 < $textCost) {
                  $distrib[Ourdatabaseevo::TYPE_TEXT] = $textCost;
                  $sumCost += $textCost;
               }
               if ($imageHasFlatRate && 0 < $imageCost) {
                  $distrib[Ourdatabaseevo::TYPE_IMAGE] = $imageCost;
                  $sumCost += $imageCost;
               }
            } else {
               // No FlatRate
               if ($textCost > 0) {
                  $distrib[Ourdatabaseevo::TYPE_TEXT] = $textCost;
                  $sumCost += $textCost;
               }
               if ($imageCost > 0) {
                  $distrib[Ourdatabaseevo::TYPE_IMAGE] = $imageCost;
                  $sumCost += $imageCost;
               }
            }
            
            $resType = "";
            $rnd = rand(0, $sumCost);
            $oldCost = 0;
            foreach ($distrib as $distribType => $distribCost) {
               $cost = $distribCost;
               $oldCost += $cost;
               if ($oldCost - $cost <= $rnd && $oldCost >= $rnd) {
                  $resType = $distribType;
                  break;
               }
            }
            
            if (Ourdatabaseevo::TYPE_IMAGE == $resType) {
               $total = $imageTotal;
               $results[Ourdatabaseevo::OURDATABASE] = $this->imageResults;
            } else {               
               if (($textTotal < $maxCount) && (isset($adSources[Ourdatabaseevo::SOURSE_XML_FEEDS]))){
                  $total = $textTotal;
                  $results[Ourdatabaseevo::OURDATABASE] = $this->textResults;
                  $feedTextTotal = 0;
                  // Получаем фидовые текстовые объявления
                  if (isset($params['feeds'][Ourdatabaseevo::BYTECITY])){
                     $count = $this->getFeedTextAds($params['feeds'][Ourdatabaseevo::BYTECITY]['url'],$maxCount-$textTotal);
                     $feedTextTotal += $count;
                     $feedTextResults = $this->feedTextResults;
                     $results[Ourdatabaseevo::SOURSE_XML_FEEDS] = $feedTextResults;
                  }
                  $total += $feedTextTotal;
               } else {
                  $total = $textTotal;
                  $results[Ourdatabaseevo::OURDATABASE] = $this->textResults;
               }
            }   
         }
      } elseif (isset($params['feeds'][Ourdatabaseevo::BYTECITY])){ 
         $feedTextTotal = 0;
         // Получаем фидовые текстовые объявления
         $data["count"] = $maxCount;
         $count = $this->getFeedTextAds($params['feeds'][Ourdatabaseevo::BYTECITY]['url'],$maxCount);
         $feedTextTotal += $count;
         $total = $feedTextTotal;

         $results[Ourdatabaseevo::SOURSE_XML_FEEDS] = $this->feedTextResults;
      }else{
         $total = 0;
         $results[Ourdatabaseevo::OURDATABASE] = array();
      }
      if (isset($results[Ourdatabaseevo::OURDATABASE])){
         foreach($results[Ourdatabaseevo::OURDATABASE] as $id => $result) {
            $results[Ourdatabaseevo::OURDATABASE][$id]["commission"] = $params['feeds'][Ourdatabaseevo::OURDATABASE]['commission'];
         }
      }      
      if (isset($results[Ourdatabaseevo::SOURSE_XML_FEEDS])){
         foreach($results[Ourdatabaseevo::SOURSE_XML_FEEDS] as $id => $result) {
            $results[Ourdatabaseevo::SOURSE_XML_FEEDS][$id]["id_channel"] = $idChannel;
            $results[Ourdatabaseevo::SOURSE_XML_FEEDS][$id]["id_site"] = $idSite;
            $results[Ourdatabaseevo::SOURSE_XML_FEEDS][$id]["id_feed"] = $params['feeds'][Ourdatabaseevo::BYTECITY]['feed'];
            $results[Ourdatabaseevo::SOURSE_XML_FEEDS][$id]["commission"] = $params['feeds'][Ourdatabaseevo::BYTECITY]['commission'];
         }
      }
      return $results;
   } //end parse      

   /**
    * Получение текстовых объявлений
    */
   protected function getFeedTextAds($url,$count) {
      $xml_file = $url;
      $rez = $this->parse_xml($xml_file);
      return $count;
   } //end getFeedTextAds
   
    /**
    * Осуществляет парсинг XML файла и размещение
    *
    * @param string $path путь к XML-файлу с рекламой
    * @return null|string строка ошибки в случае невозможности парсинга файла
    */
   public function parse_xml($filepath) {
      $contents = file_get_contents($filepath);
      try {
         $xml = new SimpleXMLElement($contents);
         
         if (isset($xml->result)) {
            $this->parse_results($xml);   
         }
         
         return null;
      } catch (Exception $e) {
         return $e->getMessage();
      }
   }
    /**
    * Добавление новостей админа в БД
    *
    * @param object $nodes объекты <result>
    */
   private function parse_results($nodes) {
   
      foreach ($nodes->result as $result) {
         $ad["ad_type"] = Ourdatabaseevo::TYPE_TEXT;
         $ad["cost"] = floatval($result->bid);
         $ad["bid"] = $ad["cost"];
         $ad["title"] = strval($result->title);
         $ad["display_url"] = strval($result->display_url);
         $ad["click_url"] = strval($result->click_url);
         $ad["description"] = strval($result->description);
         $this->feedTextResults[] = $ad;
      }
   }
   /**
    * Получение текстовых объявлений
    */
   protected function getTextAds($program, $data) {
      $count = 0;
      if (Ourdatabaseevo::PROG_FLAT_RATE == $program) {
         $count = $this->getFlatRateTextAds($data);
      } else if (Ourdatabaseevo::PROG_CPM == $program) {
         $count = $this->getCpmTextAds($data);
      }
      return $count;      
   } //end getTextAds   
   
   /**
    * Получение картиночных объявлений
    */
   protected function getImageAds($program, $data) {
      $count = 0;
      if (Ourdatabaseevo::PROG_FLAT_RATE == $program) {
         $count = $this->getFlatRateImageAds($data);
      } else if (Ourdatabaseevo::PROG_CPM == $program) {
         $count = $this->getCpmImageAds($data);
      } 
      return $count;
   } //end getImageAds   

   /**
    * Получение FlatRate текстовых объявлений
    */
   protected function getFlatRateTextAds($data) {
      return $this->getFlatRateAds(Ourdatabaseevo::TYPE_TEXT, $data);
   } //end getFlatRateTextAds
   
   /**
    * Получение FlateRate картиночных объявлений
    */
   protected function getFlatRateImageAds($data) {
      return $this->getFlatRateAds(Ourdatabaseevo::TYPE_IMAGE, $data);
   } //end getFlatRateImageAds
      
   /**
    * Получение FlateRate объявлений
    */
   protected function getFlatRateAds($type, $data) {
      $results = array();
      $count = 0;
      $c = $this->getAds($type, Ourdatabaseevo::PROG_FLAT_RATE, $data, $results);
      if (0 < $c) {
         foreach ($results as $result) {
            if (!isset($this->advertisers[$type][$result["id_advertiser"]])) {
               if (Ourdatabaseevo::TYPE_TEXT == $type) {
                  $this->textResults[] = $result;
               } else {
                  $this->imageResults[] = $result;
               }
               $this->advertisers[$type][$result["id_advertiser"]] = true;
               $count++;
               if ($count >= $data["count"]) {
                  break;
               }
            }
         }
      }
      return $count;
   } //end getFlatRateAds
   
   /**
    * Получение Cpm текстовых объявлений
    */
   protected function getCpmTextAds($data) {
      return $this->getCpmAds(Ourdatabaseevo::TYPE_TEXT, $data);
   } //end getCpmTextAds
      
   /**
    * Получение Cpm картиночных объявлений
    */
   protected function getCpmImageAds($data) {
      return $this->getCpmAds(Ourdatabaseevo::TYPE_IMAGE, $data);
   } //end getCpmImageAds

   /**
    * Получение Cpm объявлений
    */
   protected function getCpmAds($type, $data) {
      $groups = array();
      $sumCost = 0;
      foreach ($this->cpmGroups as $group) {
         if ($group[$type."_ads"] > 0) {
            $group["cost"] = $group[$type."_cost"];
            $groups[] = $group;
            $sumCost += $group["cost"];
         }
      }
      $ad = array();
      $count = 0;
      // Сортируем результаты по схеме вероятностного бида
      $totalCount = $data["count"];
      while (count($groups) && $totalCount > $count) {
         // Генерим случайное число (float) от 0 до sumCost;
         $rnd = rand(0, $sumCost);
         $oldCost = 0;
         foreach ($groups as $key => $group) {
            $cost = $group["cost"];
            $oldCost += $cost;
            if ($oldCost - $cost <= $rnd && $oldCost >= $rnd) {
               if (!isset($this->advertisers[$type][$group["id_advertiser"]]) && $this->getAdByGroup($group["id_group"], $type, $data, $ad)) {
                  $ad["id_advertiser"] = $group["id_advertiser"];
                  $ad["id_campaign"] = $group["id_campaign"];
                  $ad["id_group"] = $group["id_group"];
                  $ad["id_group_site_channel"] = $group["id_group_site_channel"];
                  $ad["id_site_channel"] = $group["id_site_channel"];
                  $ad["ad_type"] = $type;
                  $ad["program_type"] = Ourdatabaseevo::PROG_CPM;
                  if (Ourdatabaseevo::TYPE_TEXT == $type) {
                     $ad["cost"] = $group["text_cost"];
                     $ad["bid"] = $ad["cost"];
                     $this->textResults[] = $ad;
                  } else if (Ourdatabaseevo::TYPE_IMAGE == $type) {
                     $ad["cost"] = $group["image_cost"];
                     $ad["bid"] = $ad["cost"];
                     $this->imageResults[] = $ad;
                  }
                  $this->advertisers[$type][$group["id_advertiser"]] = true;
                  $count++;
               }
               $sumCost -= $cost;
               unset($groups[$key]);
               break;
            }
         }
      }
      return $count;
   } //end getCpmAds   
   
} //end class Ourdatabaseevo

?>