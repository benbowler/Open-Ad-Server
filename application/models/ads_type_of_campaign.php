<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/campaign_wizard.php';

/**
* модель для получения соответствий между типом кампании и типами объявлений.
* В частности нужно чтобы не считать рич-медиа в импорте для цпц кампании.
* 
* @author Timur
* @project SmartPPC6
* @version 1.0.0
*/
class Ads_type_of_campaign extends CI_Model {
   
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
    
   public function getAdTypes($id_campaign,$type = null) {

      //кампания в процессе создания
      if($id_campaign == 'new'){

         /* Определение типа кампании через new_campaign */
         //$campaign_type = Campaign_Wizard::get_new_campaign_type();
         
         $campaign_type = $type;

      }else{ //для уже созданной
         
      
      $this->db->select('id_campaign_type t')
            ->from('campaigns')
            ->where('id_campaign',$id_campaign);
            
      $res = $this->db->get();

      if($res->num_rows()){
         $row = $res->result();
         $campaign_type = $row[0]->t;
      }else{
         return array();
      }
      }
               
      
      switch($campaign_type){
         case 'cpc':
            return array(1,2);
         default:
            return array(1,2,3);
      }
      
 
         
   } //end select
   
}