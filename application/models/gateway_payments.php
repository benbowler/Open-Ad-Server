<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Класс для получения истории транзакций через платежные шлюзы
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Gateway_Payments extends CI_Model {
 
   const TABLE = 'payment_transactions';
   	
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
   } //end __construct

   /**
   * возвращает список выплат
   *
   * @param array параметры списка выплат
   * 			   int id_entity идентификатор сущности, которой выплачивались деньги, - опционально
   * 			   array flow_program фильтр по типам денежных потоков,  - опционально
   *               array fields поля БД для запроса списка - опционально
   *               array order ('by' => 'field', 'direction' => 'asc') параметры сортировки списка - опционально
   * 			   array date_range ('from' => unixtimestamp, 'to' => unixtimestamp) фильтр по дате выплаты - опционально
   *           int id_gateway фильтр по платежным шлюзам - опционально (0 - все шлюзы)
   * @return array
   */   
   public function get_list($params) {
   	
   	$result = array();
   	
   	if(isset($params['id_entity'])) {
   		$this->db->where('id_entity_receipt',$params['id_entity']);
   	}
   	
   	if (isset($params['id_gateway'])) {
   	   if ($params['id_gateway']) {
   	      $this->db->where('payment_gateways.id_entity', $params['id_gateway']);
   	   }
   	}
   	
      if(isset($params['flow_program'])) {
   		$this->db->where_in('flow_program',$params['flow_program']);
   	}
   	
   	if(isset($params['fields'])) {
   		$this->db->select($params['fields']);
   	}
   	
   	if(isset($params['order'])) {
   		$this->db->order_by($params['order']['by'],$params['order']['direction']);
   	}
   	
    if(isset($params['date_range'])) {
   		$this->db->where(array('flow_date >=' => type_to_str($params['date_range']['from'], 'databasedatetime'),
   							   'flow_date <=' => type_to_str($params['date_range']['to'], 'databasedatetime')
   		));
   	}
   	
   	$this->db->join('money_flows',self::TABLE.'.id_flow = money_flows.id_flow')
   	         ->join('payment_gateways', 'money_flows.id_entity_expense = payment_gateways.id_entity')
   	         ->join('entities', 'payment_gateways.id_entity = entities.id_entity')
   	         ->join('entities pe', 'money_flows.id_entity_receipt = pe.id_entity')
   	         ->join('payment_requests', 'money_flows.id_flow = payment_requests.id_flow','left');
   	
   	$query = $this->db->get(self::TABLE);
   	
   	//echo $this->db->last_query();
   	
   	foreach ($query->result() as $row) {
   	   //$ids[] = $row->id_entity_receipt;
   		$result[] = $row;                  
   	}   	   	
   	                  
   	return $result;
   } //end get_list
   
   /**
    * Добавление записи о проведенной транзакции в таблицу успешных транзакций
    *   
    * @param array $params массив параметров транзакции:
    *                      'id_flow' - идентификатор платежного потока,
    *                      'transaction_id' - идентификатор транзакции, полученный от платежного шлюза,
    *                      'description' - описание транзакции
    * @return none
    */
   public function insert($params) {
   	$this->db->insert(self::TABLE, array('id_flow' => $params['id_flow'],
   										 'transaction_id' => $params['transaction_id']
   	)); 
   }
 
   /**
    * возвращает информацию по выбранной проводке
    *
    * @param integer $id_flow
    */
   public function flow_info($id_flow) {
   	$res = $this->db
   	   ->select('UNIX_TIMESTAMP(flow_date) AS flow_date, transaction_id, description, value')
   	   ->from('money_flows mf')
   	   ->join('payment_transactions pt', 'mf.id_flow=pt.id_flow', 'LEFT')
         ->join('payment_requests', 'mf.id_flow = mf.id_flow', 'LEFT')
   	   ->where('mf.id_flow', $id_flow)
   	   ->get();
   	if ($res->num_rows()){
   	   $row = $res->row_array();
   	   return $row;
   	}
   	return NULL;
   } //end flow_info   
   
}

?>