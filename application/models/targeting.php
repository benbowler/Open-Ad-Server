<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* класс для работы с таргетингом при показе объявлений
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Targeting extends CI_Model {
 
   protected $targeting_groups = array();
   protected $rules = array();
   protected $country = "UN";
   protected $language = "UN";
   protected $browser = "unknown";
   protected $ip_address = "";
   protected $operating_system = "unknown";
   protected $user_agent = "";
   protected $page_url = "";
   protected $referer = "";
   
   public function __construct() {
      parent::__construct();
   }

   /**
    * Добавление группы таргетинга для последующей проверки
    */
   public function addTargetingGroup($id_targeting_group) {
      $this->targeting_groups[$id_targeting_group] = false;
   } //end addTargetingGroup

   /**
    * Загрузка правил групп таргетинга (которые еще не подружены)
    */
   protected function loadTargetingRules() {
      if (count($this->targeting_groups) == 0) {
         return;
      } 
      $query = $this->db
         ->select("id_targeting_group, `group`, name, value, compare")
         ->from("targeting_group_values")
         ->where_in("id_targeting_group", array_keys($this->targeting_groups))
         ->get();
      if ($query->num_rows() == 0) {
         return;
      }
      foreach ($query->result_array() as $row) {
         $this->rules[$row["id_targeting_group"]][$row["group"]][] = $row;
      }
   } // loadTargetingRules
      
   /**
    * Проверка группы таргетинга
    */
   public function checkRules($id_targeting_group) {
      $this->loadTargetingRules();
      if (!isset($this->rules[$id_targeting_group])) {
         return true;
      }
      foreach ($this->rules[$id_targeting_group] as $group => $rules) {
            if(!$this->checkGroup($group, $rules)) {
               return false;
            }
      }
      return true;
   } //end checkRules
   
   /**
    * Factory метод для проверки групп правил
    */
   protected function checkGroup($group, $rules) {
      if ($group == "countries") {
         return $this->checkCountries($rules);
      }
      return false;
   } //checkGroup         
   
   /**
    * Проверка на страну
    */
   protected function checkCountries($rules) {
      return $this->checkList($this->country, $rules);
   } //checkCountries
   
   /**
    * Проверка на списочные значения
    */
   protected function checkList($value, $rules) {
      foreach ($rules as $rule) {
         if ($rule["value"] == $value) {
            return true;
         }
      }
      return false;
   } //end checkList   
   
   /**
    * Set country
    */
   public function setCountry($country) {
      $this->country = $country;
   } //end setCountry
   
} //end class Targeting

?>