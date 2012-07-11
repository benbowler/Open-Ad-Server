<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с рекламодателями
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Advertisers extends CI_Model {
 
   const ID_ROLE = 3;

   /**
   * конструктор, инициализация базового класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end Advertisers
 
   /**
   * возвращает список рекламодателей
   *
   * @param integer $page номер запрошенной страницы
   * @param integer $per_page количество записей на странице
   * @param string $sort_field имя поля, по которому осуществляется сортировка
   * @param string $sort_direction направление сортировки
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param string $quickSearch 
   * @return array массив с данными рекламодателей (id => (name, email, join_date, status, ballance, impressions, clicks, ctr, spent)) 
   */   
   public function get($page, $per_page, $sort_field, $sort_direction, $filt, $range, $quickSearch = '') {
   	  $sql = "
   	  	SELECT 
   	  		a.id_entity_advertiser AS id,
   	  		e.name,
   	  		e_mail,
   	  		UNIX_TIMESTAMP(creation_date) as join_date,
   	  		er.status,
   	  		ballance,
   	  		e.bonus,
   	  		(SUM(clicks)*100/SUM(impressions)) AS ctr,
   	  		SUM(sa.impressions) AS impressions,
   	  		SUM(sa.clicks) AS clicks,
   	  		SUM(sa.spent) AS spent
   	  	FROM advertisers a
      		JOIN entities e ON a.id_entity_advertiser = e.id_entity
      		JOIN entity_roles er ON (er.id_entity = e.id_entity AND er.id_role = " . self::ID_ROLE . ")
      		LEFT JOIN stat_advertisers sa ON (a.id_entity_advertiser = sa.id_entity_advertiser
      			AND sa.stat_date BETWEEN '".type_to_str($range['from'], 'databasedate')."' AND '" .type_to_str($range['to'], 'databasedate')."')
      	WHERE 1 = 1 
   	  ";
   	  
      if ($filt != 'all') {
      	 $sql .= ' AND er.status = '.$this->db->escape($filt);
      }
      
      if (!empty($quickSearch)) {
      	 $escapedQuickSearch = $this->db->escape_str($quickSearch);
      	 $mysql_date = type_to_str(type_cast($quickSearch, 'date'), 'databasedate');      
      	 $sql .= " AND ((e.id_entity = '$escapedQuickSearch')
      	 				OR (e.name LIKE '%". $escapedQuickSearch . "%')
      	 				OR (e.e_mail LIKE '%" . $escapedQuickSearch . "%')
      	 				OR (e.creation_date LIKE '%" . $mysql_date ."%'))
      	 		 ";
      }
      
      $sql .= "
      	GROUP BY id
      	ORDER BY ".$sort_field." ".$sort_direction."
      	LIMIT ".(($page-1)*$per_page).", ".$per_page."
      ";
      $res = $this->db->query($sql);
      
      $list = array();
      $id_list = array();
      foreach ($res->result() as $row) {
         $id_list[] = $row->id;
         $list[$row->id]['id'] = $row->id;
         $list[$row->id]['name'] = $row->name;
         $list[$row->id]['email'] = $row->e_mail;
         $list[$row->id]['join_date'] = $row->join_date;
         $list[$row->id]['status'] = $row->status;
         $list[$row->id]['ballance'] = $row->ballance;
         $list[$row->id]['bonus'] = $row->bonus;
         $list[$row->id]['impressions'] = $row->impressions;
         $list[$row->id]['clicks'] = $row->clicks;
         $list[$row->id]['spent'] = $row->spent;
         $list[$row->id]['ctr'] = $row->ctr;
      }
      return $list;      
   } //end get
   
   /**
   * возвращает количество рекламодателей, попадающих под указанный фильтр
   *
   * @param string $filt фильтр по статусу
   * @param array $range массив с двумя датами - начала и конце периода ('from', 'to')
   * @param string $quickSearch Параметр для поиска рекламодателя по id, fullname, email и join date
   * @return integer количество
   */
   public function total($filt, $range, $quickSearch = '') {
      $total = array(
         'cnt'         => 0,
         'balance'     => 0,
         'impressions' => 0,
         'clicks'      => 0,
         'spent'       => 0,
         'bonus'       => 0
      );
      
      
      // All pages total
      $sql = "
      	SELECT 
      		SUM(sa.impressions) AS impressions,
      		SUM(sa.clicks) AS clicks,
      		SUM(sa.spent) AS spent,
      		SUM(e.bonus) AS bonus
      	FROM advertisers a
      		JOIN entities e ON a.id_entity_advertiser = e.id_entity
      		JOIN entity_roles er ON (er.id_entity = e.id_entity AND er.id_role = " . self::ID_ROLE . ")
      		LEFT JOIN stat_advertisers sa ON (a.id_entity_advertiser = sa.id_entity_advertiser
      			AND sa.stat_date BETWEEN '".type_to_str($range['from'], 'databasedate')."' AND '" .type_to_str($range['to'], 'databasedate')."')
      	WHERE 1 = 1 
      	";
      
      if ($filt != 'all') {
      	 $sql .= ' AND er.status = '.$this->db->escape($filt);
      }
      
      if (!empty($quickSearch)) {
      	 $escapedQuickSearch = $this->db->escape_str($quickSearch);
      	 $sql .= " AND ((e.id_entity LIKE '%" . $escapedQuickSearch . "%')
      	 				OR (e.name LIKE '%". $escapedQuickSearch . "%')
      	 				OR (e.e_mail LIKE '%" . $escapedQuickSearch . "%')
      	 				OR (e.creation_date LIKE '%" . $escapedQuickSearch ."%'))
      	 		 ";
      }
      
      $query = $this->db->query($sql);
      
      if (0 < $query->num_rows()) {
         $row = $query->row();
         $total['impressions'] = $row->impressions;
         $total['clicks'] = $row->clicks;
         $total['spent'] = $row->spent;
         $total['bonus'] = $row->bonus;
      }
      
      // All count
      $sql = "
      	SELECT 
      		SUM(e.ballance) AS ballance,
      		COUNT(*) AS cnt
      	FROM advertisers a
      		JOIN entities e ON a.id_entity_advertiser = e.id_entity
      		JOIN entity_roles er ON er.id_entity = e.id_entity AND er.id_role = " . self::ID_ROLE . "
      	WHERE 1 = 1
      ";
      
      if ($filt != 'all') {
      	 $sql .= ' AND er.status = '.$this->db->escape($filt);
      }
      
      if (!empty($quickSearch)) {
      	 $escapedQuickSearch = $this->db->escape_str($quickSearch);
      	 $sql .= " AND ((e.id_entity LIKE '%" . $escapedQuickSearch . "%')
      	 				OR (e.name LIKE '%". $escapedQuickSearch . "%')
      	 				OR (e.e_mail LIKE '%" . $escapedQuickSearch . "%')
      	 				OR (e.creation_date LIKE '%" . $escapedQuickSearch ."%'))
      	 		 ";
      }
      
      $query = $this->db->query($sql);
      
      if (0 < $query->num_rows()) {
         $row = $query->row();
         $total['balance'] = $row->ballance;
         $total['cnt'] = $row->cnt;
      }
            
      return $total;
   } //end total

   /**
   * приостанавливает работу учетной записи рекламодателя
   *
   * @param integer $id_entity код учтеной записи рекламодателя
   * @return ничего не возвращает
   */   
   public function pause($id_entity) {
      $this->db->where('id_entity', $id_entity);
      $this->db->where('status', 'active');
      $this->db->where('id_role', self::ID_ROLE);
      $this->db->update('entity_roles', array('status' => 'blocked'));
      return (0 < $this->db->affected_rows());
   } //end pause
   
   /**
   * возобновляет работу учетной записи рекламодателя
   *
   * @param integer $id_entity код учтеной записи рекламодателя
   * @return ничего не возвращает
   */   
   public function resume($id_entity) {
      $this->db->where('id_entity', $id_entity);
      $this->db->where('status', 'blocked');
      $this->db->where('id_role', self::ID_ROLE);
      $this->db->update('entity_roles', array('status' => 'active'));
      return (0 < $this->db->affected_rows());
   } //end resume

   /**
   * кодирование заголовков письма в MIME формате
   *
   * @param string $str текст
   * @return string закодированная строка
   */
   public function utf8($str) {
      return '=?UTF-8?B?'.base64_encode($str).'?=';
   } //end utf8   
   
   /**
   * высылает рекламодателю письмо с уведомлением об активации учетной записи
   *
   * @param integer $id_entity уникальный код пользователя 
   * @return ничего не возвращает
   */
   public function send_approve_email($id_entity) {
      $CI =& get_instance();
      $this->load->library('email');
      $config['charset'] = 'utf-8';
      $config['wordwrap'] = FALSE;
      $this->email->initialize($config);
      $system_email = $this->global_variables->get("SystemEMail");
      $site_name = $this->global_variables->get("SiteName");
      $this->email->from(
         $system_email,
         $this->utf8($site_name.' '.__("Robot")));
      $this->load->model('entity', '', TRUE);
      $user_mail = $this->entity->get_name_and_mail($id_entity); 
      $this->email->to($user_mail->e_mail);
      $this->email->subject($this->utf8($site_name.' '.__("Account Approve Notification")));
      $params = array(
         "USERMAIL" => $user_mail->e_mail,
         'SYSTEM' => $site_name,
         'USERNAME' => $user_mail->name,
         'USERROLE' => 'advertiser',
         'USERROLE_LOCALE' => __('advertiser'),
         'SITEURL' => $CI->get_siteurl()
      );
      $mail = $this->parser->parse("mails/$this->locale/approve.html", $params, TRUE);
      $this->email->message($mail);
      $send_status = $this->email->send();      
   } //end send_approve_email
         
   /**
   * активирует учетную запись рекламодателя
   *
   * @param integer $id_entity код учтеной записи рекламодателя
   * @return ничего не возвращает
   */   
   public function approve($id_entity) {
      $this->db->where('id_entity', $id_entity);
      $this->db->where('status', 'activation');
      $this->db->where('id_role', self::ID_ROLE);
      $this->db->update('entity_roles', array('status' => 'active'));      
      if ($rows = $this->db->affected_rows()) {
      $this->send_approve_email($id_entity);                        
      }
      return (0 < $rows);
   } //end pause     

   /**
   * удаляет учетную запись рекламодателя
   *
   * @param integer $id_entity код учтеной записи рекламодателя
   * @return ничего не возвращает
   */   
   public function delete($id_entity) {
	//set deleted status for advertiser's campaigns
        $CI =& get_instance();
        $CI->load->model('campaigns');
        $res = $this->db->select('id_campaign')->get_where('campaigns', array('id_entity_advertiser' => $id_entity));
        foreach ($res->result() as $row) {
           $CI->campaigns->action('delete', $id_entity, $row->id_campaign);
	     }
				             
	     //set deleted status for advertiser
	     $this->db->where('id_entity', $id_entity);
        $this->db->where('id_role', self::ID_ROLE);
	     $this->db->update('entity_roles', array('status' => 'deleted'));   
        return (0 < $this->db->affected_rows());
    }
    
   /**
   * восстанавливает учетную запись рекламодателя
   *
   * @param integer $id_entity код учтеной записи рекламодателя
   * @return ничего не возвращает
   */   
   public function restore($id_entity) {
	//set deleted status for advertiser's campaigns
        $CI =& get_instance();
        $CI->load->model('campaigns');
      $res = $this->db->select('id_campaign')
                      ->join('entity_roles', 'entity_roles.id_entity=campaigns.id_entity_advertiser AND entity_roles.id_role=' . self::ID_ROLE)
                      ->get_where('campaigns', array('id_entity_advertiser' => $id_entity, 'entity_roles.status' => 'deleted'));
        foreach ($res->result() as $row) {
           $CI->campaigns->action('restore', $id_entity, $row->id_campaign);
	}
				             
	//set deleted status for advertiser
	 $this->db->where('id_entity', $id_entity);
      $this->db->where('id_role', self::ID_ROLE);
	 $this->db->where('status', 'deleted');
      $this->db->update('entity_roles', array('status' => 'active'));
      return (0 < $this->db->affected_rows());
   } //end restore
   
} //end class Advertisers

?>