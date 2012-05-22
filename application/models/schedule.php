<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы с графиками показа объявлений  
* 
* @author Юдин Владимир
* @project SmartPPC6
* @version 1.0.0
*/
class Schedule extends CI_Model {
 
   /**
   * конструктор класса, инициализация базового класса Model
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end Schedule

   /**
   * добавляет новый график на день в таблицу timetables 
   *
   * @param integer $hours битовая маска часов шрафика (1 - активный час)
   * @return ничего не возвращает
   */
   public function add_timetable($hours) {
      $insert = array('id_timetable' => $hours);
      for ($bit = 0; $bit<24; $bit++) {
         $insert[sprintf('h_%02d', $bit)] = ($hours & (1<<$bit)) ? 1 : 0;
      }
   	$this->db->insert('timetables', $insert);
   } //end name
   
   /**
   * проверяет есть ли уже в наличии нужный график на день
   *
   * @param integer $hours битовая маска часов графика (1 - активный час)
   * @return bool TRUE-если нужный график существует
   */
   public function exist_timetable($hours) {
      $this->db->select('id_timetable');
      $res = $this->db->get_where('timetables', array('id_timetable' => $hours));
      return $res->num_rows()>0;	
   } //end exist_timetable
   
   /**
   * возвращает id_timetable по массиву с активными часами  
   *
   * @param array $hours массив с часами (TRUE - активный час)
   * @return код дневного графика
   */
   public function timetable($hours) {
      $id_timetable = 0;
      $bit = 0;
   	foreach ($hours as $hour) {
   	   $id_timetable |= ($hour ? (1<<$bit) : 0);
   		$bit++;
   	}
   	if (!$this->exist_timetable($id_timetable)) {
   	   $this->add_timetable($id_timetable);
   	} 
   	return $id_timetable;
   } //end timetable
   
   /**
   * добавляет все 7 дневных графиков в timetables
   *
   * @param array $schedule массив с часами по всей неделе (TRUE - активный час)
   * @return array массив с кодами дневных графиков для всех дней недели
   */
   public function timetables($schedule) {
      $timetables = array();
   	for ($day=0; $day<7; $day++) {
   	   $timetables[$day] = $this->timetable(array_slice($schedule, $day*24, 24)); 
   	}
   	return $timetables;
   } //end schedule
   
   /**
   * проверяет надичие заданного графика
   *
   * @param array $timetables массив с кодами дневных графиков для всх дней недели
   * @return integer код графика, 0 - если не существует
   */
   public function exist($timetables) {
      $this->db->select('id_schedule, COUNT(id_timetable) AS timetables'); 
      $where = '';
      for ($day=0; $day<7; $day++) {
         if ($where != '') {
            $where .= ' OR ';
         }
         $weekday = $day+1;
         $where .= "(weekday=$weekday AND id_timetable=$timetables[$day])";
      }           
      $this->db->where($where);
      $this->db->group_by('id_schedule');
      $this->db->having('timetables' ,7);
      $res = $this->db->get('schedule_timetables');
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->id_schedule;
      }
      return 0;
   } //end exist
   
   /**
   * добавляет новый график
   *
   * @param array $timetables массив с кодами дневных графиков для всх дней недели
   * @return integer уникальный код графика
   */
   public function add($timetables) {      
      $this->db->insert('schedules', array('id_schedule' => NULL));
      $id_schedule = $this->db->insert_id();
      for ($day=0; $day<7; $day++) {
         $this->db->insert('schedule_timetables',
            array(
               'weekday' => $day+1,
               'id_schedule' => $id_schedule,
               'id_timetable' => $timetables[$day]
            ));
      }
      return $id_schedule;
   } //end add
   
   /**
   * проверяет наличие графика и при его отсутствии добавляет его
   *
   * @param array $hours массив с указанием, какой час в заданный день активен
   * @return integer уникальный код недельного графика, NULL - полный график
   */
   public function set($hours) {
      $not_empty_schedule = FALSE;
      for ($hour=0; $hour<7*24; $hour++) {
         $not_empty_schedule = $not_empty_schedule || $hours[$hour]; 
      }
      if (!$not_empty_schedule) {
         return NULL;
      }
      $timetables = $this->timetables($hours);
      $id_schedule = $this->exist($timetables);
      if (!$id_schedule) {
   	   $id_schedule = $this->add($timetables);
      }
      return $id_schedule;
   } //end set
   
   /**
   * возвращает график по его коду
   *
   * @param integer $id_schedule уникальный код графика, NULL - если полный график
   * @return array массив с установленными в TRUE активными часами
   */
   public function get($id_schedule) {
      $hours = array();
   	if (is_null($id_schedule)) {   	   
   	   for ($hour=0; $hour<7*24; $hour++) {
   	      $hours[] = TRUE;
   	   }
   	   return $hours;
   	}
   	$this->db->select('id_timetable');
   	$this->db->order_by('weekday');
   	$res = $this->db->get_where('schedule_timetables', array('id_schedule' => $id_schedule));
   	foreach ($res->result() as $row) {
   		for ($hour=0; $hour<24; $hour++) {
   		   $hours[] = ($row->id_timetable & (1<<$hour)) > 0;
   		}
   	}
   	return $hours;
   } //end get
   
   /**
   * очищает базу данных от неиспользуемых недельных и дневных графиков
   *
   * @return ничего не возвращает 
   */
   public function kill_unused() {
      $this->db->select('schedules.id_schedule AS id');
      $this->db->from('schedules')->
         join('campaigns', 'schedules.id_schedule = campaigns.id_schedule', 'LEFT');
      $this->db->where('campaigns.id_schedule IS NULL');
      $res = $this->db->get();
      foreach ($res->result() as $row) {
      	$this->db->delete('schedules', array('id_schedule' => $row->id));
         $this->db->delete('schedule_timetables', array('id_schedule' => $row->id));
      }      
      $this->db->select('timetables.id_timetable AS id');
      $this->db->from('timetables')->
         join('schedule_timetables', 'timetables.id_timetable = schedule_timetables.id_timetable', 'LEFT');
      $this->db->where('schedule_timetables.id_timetable IS NULL');
      $res = $this->db->get();
      foreach ($res->result() as $row) {
         $this->db->delete('timetables', array('id_timetable' => $row->id));
      }         	
   } //end kill_unused
   
} //end class Schedule

?>