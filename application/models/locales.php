<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Класс для получения данных о локалях
* 
* @author Semerenko
* @project SmartPPC6
* @version 1.0.0
*/
class Locales extends CI_Model {
 

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end __construct

   /**
    * Все локали
    *
    * @return 
    */  
   public function findStats($page, $perPage, $sortField, $sortDirection){
      
      $locales = array();
      $query = $this->db->select('locale, lang, country, flag, is_default, status,ldirection')
                        ->from('locales')
                        ->order_by($sortField,$sortDirection)
                        ->limit($perPage, ($page - 1) * $perPage)                        
                        ->get();
      
      foreach ($query->result() as $row) {
         $locales[$row->locale]['locale'] = $row->locale;
         $locales[$row->locale]['lang'] = $row->lang;
         $locales[$row->locale]['country'] = $row->country;
         $locales[$row->locale]['flag'] = $row->flag;
         $locales[$row->locale]['is_default'] = $row->is_default;
         $locales[$row->locale]['status'] = $row->status;
         $locales[$row->locale]['ldirection'] = $row->ldirection;
      }
      
      
      return $locales;     
   }
   
   /**
    * Get locale if it exists
    *
    * @param unknown_type $id
    * @return Object or NULL
    */
   public function getLocale($id){
      
      $locale = $this->db->select('locale, lang, country, flag, is_default, template, status, ldirection, date_format, time_format')
                  ->from('locales')
                  ->where('locale = "' . $id . '"')
                  ->get()
                  ->result();
                  
      if(count($locale)){
         return $locale[0];
      }
      
      return NULL;
   }
   
   public function getActiveLocales(){
      $locales = array();
      $query = $this->db->select('locale, lang, country, flag, is_default,ldirection')
                        ->from('locales')
                        ->where('status','enabled')
                        ->order_by('lang','ASC')
                        ->get();
                        
      foreach ($query->result() as $row) {
         $locales[] = array('locale' => $row->locale, 'lang' => $row->lang,
                             'flag' => $row->flag, 'is_default' => $row->is_default,
                             'ldirection' => $row->ldirection);
         
      }
     
      
      return $locales;
   }
   
   /**
    * Количество доступных локалей, поддерживаемых в проекте.
    *
    * @return int
    */
   public function get_total(){
      $query = $this->db->select('Count(*) qty')
                        ->from('locales')
                        ->get();
                        
      $row = $query->result();
      
      return $row[0]->qty;
   }
   
   /**
    * Делает локаль $locale дефолтной
    *
    * @param string $locale
    */
   public function setDefault($locale){
      
      if($this->isLocale($locale)){
         // reset 
         $this->db->query('UPDATE locales SET is_default="false"');
         // set
         $this->db->query('UPDATE locales SET is_default="true", status="enabled" ' .
                           'WHERE locale="'.$locale.'"'); 
      }
   }
   
   /**
    * Делает локаль $locale дефолтной
    *
    * @param string $locale
    */
   public function setStatus($locale){

      $obj = $this->getLocale($locale);

      if(!is_null($obj)){
         ($obj->status == 'enabled')?$status = 'disabled':$status = 'enabled';
         $this->db->query('UPDATE locales SET status="' . $status . '" ' .
                           'WHERE locale="'.$locale.'"');         
      }

   }   
   
   /**
    * Проверка если $locale в базе
    *
    * @param String $locale
    * @return TRUE or FALSE
    */
   public function isLocale($locale){
      
      $query = $this->db->select('locale')
                        ->from('locales')
                        ->where('locale = "' . $locale . '"')
                        ->get();
      if($query->num_rows() > 0){
         return TRUE;
      }
      
      return FALSE;
   }
   
   /**
    * All available locales
    *
    * @return array
    */
   public function getLocalesList(){
      $locales = array_keys(Zend_Locale::getLocaleList());
      
      // нужно убрать локали с трёх буквенным языком
      // и локали без региона
      foreach($locales as $key => $loc){
         $temp = explode('_',$loc);
         if(strlen($temp[0]) > 2 || !isset($temp[1])){
            unset($locales[$key]);
         }
      }
      $locales = array_combine($locales,$locales);
      asort($locales);
      return $locales;
   }
   
   /**
    * Get languages by locale
    *
    * @param String $locale
    * @return Array
    */   
   public function getLanguagesByLocale($locale){
      $tmp_locales = Zend_Locale::getLocaleList();
      if(!isset($tmp_locales[$locale])){
         return array();
      }
      
      return Zend_Locale::getTranslationList('language', $locale);
      
   }
   
   /**
    * Get language by locale
    *
    * @param String $locale
    * @return ''
    */   
   public function getLanguageByLocale($locale, $code){

      $languages = $this->getLanguagesByLocale($locale);

      if(count($languages)){
         foreach($languages as $key => $lang){
            if($key == $code){
               return $lang;       
            }
         }
      }
      
      return '';
   }   
    
   /**
    * Add new locale
    *
    * @param array $data
    * @return Boolean
    */
   public function add($data){
      
      if($this->isLocale($data['locale'])){
         return FALSE;
      }
      
      $res = $this->db->insert('locales',$data);
      if($res){
         return TRUE;
      }else{
         return FALSE;   
      }
   }
   
   /**
    * Delete locale from db
    *
    * @param string $locale
    * @return TRUE or FALSE
    */
   public function delete($locale){

      if(!$this->isLocale($locale)){
         return FALSE;
      }      
      
      $this->db->delete('locales',array('locale' => $locale));
      return TRUE;
   }
   

   /**
    * Update existing locale
    *
    * @param String $data
    * @return TRUE or FALSE
    */
   public function update($data){
      
      $this->db->where('locale', $data['locale'])
               ->update('locales',$data);
      
      return TRUE;
   }
   
   /**
    * get base locales
    */
   public function getBaseLocales($path){
      
      $d = new DirectoryIterator($path);
      $locales = array();
      foreach($d as $node){
         if($node->isDot()){
            continue;
         }
         if($node->isDir() && $node->getFilename() != 'template'){
            $locales[$node->getFilename()] = $node->getFilename();
         }
      }

      return $locales;
   }
   
   /**
    * код страны из названия локали
    *
    * @param  string $locale
    * @return string
    */
   public function getCountryOfLocale($locale){
      preg_match('|.{2,3}_(.{2,3})|isU',$locale,$matches);
      return $matches[1];   
   }
   
   /**
    *
    */
   public function trueCountry($name){
      return !in_array($name, array('UN','HM','CS','SJ','UM'));
   }
   
   /**
    * Формат даты для текущей локали
    */
   public function getDateFormat(){
      $locale = get_instance()->locale;
      
      $query = $this->db->select('date_format')
                        ->from('locales')
                        ->where('locale',$locale)
                        ->get();
                        
      $row = $query->result();      
      
      try{
         return $row[0]->date_format;   
      }catch(Exception $e){
         return '';
      }
   }
   
   public function getDateInput(){
      $locale = get_instance()->locale;
      
      $query = $this->db->select('date_input')
                        ->from('locales')
                        ->where('locale',$locale)
                        ->get();
                        
      $row = $query->result();      
      
      try{
         return $row[0]->date_input;   
      }catch(Exception $e){
         return '';
      }   
   }
   
   /**
    * Формат времени для текущей локали
    */
   public function getTimeFormat(){
      $locale = get_instance()->locale;
      
      $query = $this->db->select('time_format')
                        ->from('locales')
                        ->where('locale',$locale)
                        ->get();
                        
      $row = $query->result();      
      
      try{
         return $row[0]->time_format;   
      }catch(Exception $e){
         return '';
      }
   }
   
   public function getTimeInput(){
      $locale = get_instance()->locale;
      
      $query = $this->db->select('time_input')
                        ->from('locales')
                        ->where('locale',$locale)
                        ->get();
                        
      $row = $query->result();      
      
      try{
         return $row[0]->time_input;   
      }catch(Exception $e){
         return '';
      }   
   }   

   
   
}
