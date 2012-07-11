<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для работы со списком платежных систем
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Payment_gateways extends CI_Model {
 
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function __construct() {
      parent::__construct();
      $this->_table_name = 'payment_gateways';
      $this->_id_field_name = 'id_entity';
   } //end Payment_gateways

   /**
   * возвращает список платежных шлюзов доступных для инсталляции в систему
   *
   * @return array список платежных шлюзов
   */
   public function get_request_list() {  
      $res = $this->db->order_by('title')->get('payment_gateways_to_request');
      $list = array();
      if ($res->num_rows()) {
         foreach ($res->result() as $row) {
            $list[$row->title] = $row->title;
         }         
      }
      $list[__('Other..')] = __('Other..'); 
      return $list;      
   } //end get_request_list   
   
   /**
   * возвращает список платежных систем
   *
   * @param array $params фильтр для выбора
   *  "filter" =>
   *     "all"      - список всех платежных шлюзов
   *     "enabled"  - список разрешенных платежных шлюзов (по умолчанию) 
   *     "disabled" - список запрещенных платежных шлюзов
   *  "name"   - (опционально) возвращает в формате (id_gateway => name)
   *  "fields" - (опционально) список необходимых полей
   *  "sort"   - (опционально) array ('field','direction') параметры сортировки списка
   *  "subset" - (опционально) array ('limit','offset') параметры для возврата части результатов
   *  "type"   - (опционально) возвращает список платежных шлюзов доступных для инсталляции  
   *  "withdraw" - (опционально) возвращает платежные шлюзы, которые можно использовать для вывода средств
   *  "deposit"  - (опционально) возвращает платежные шлюзы, которые можно использовать для ввода средств
   * @return null|array список платежных шлюзов (по-умолчанию в формате id => name)
   */   
   public function get_list($params) {
      if (isset($params["type"])) {
         return $this->get_request_list();
      }
      if (isset($params["filter"])) {
         switch ($params["filter"]) {
         	case "disabled":
         	   $this->db->where('payment_gateways.status', 'disabled');
               break;
         	case "enabled":
               $this->db->where('payment_gateways.status', 'enabled');               
         }       
      }
      if (isset($params['withdraw'])) {
         $this->db->where("FIND_IN_SET('withdraw', mode)", null, false);
      }
      if (isset($params['deposit'])) {
         $this->db->where("FIND_IN_SET('deposit', mode)", null, false);
      }
      
      if (isset($params["fields"])) {
         $this->db->select($params['fields']);       
      } else {
         $this->db->select('entities.id_entity AS entity, id_gateway, name, fund_comm');   
      }
      
      if (isset($params["sort"])) {
         $this->db->order_by($params['sort']['field'],$params['sort']['direction']);       
      }
      
      if (isset($params["subset"])) {
         $this->db->limit($params['subset']['limit'],$params['subset']['offset']);       
      }
      $this->db->join('entities', 'payment_gateways.id_entity=entities.id_entity');
      $res = $this->db->get('payment_gateways');
      
      if (isset($params["fields"])) {
         if ($res->num_rows () > 0) {
            return $res->result_array ();
         } else {
            return null; 
         }   
      } else {
         $gateways = array();
         if (isset($params['all'])) {
            $gateways[0] = __('all');
         }
         foreach ($res->result() as $row) {
            if (isset($params['name'])) {
               $gateways[$row->id_gateway] = $row->name;            
            } else {
               $gateways[$row->entity] = $row->name;
            }
         }
         return $gateways;
      }
   } //end get_list
 
   /**
   * возвращает количество платежных систем
   *
   * @param array $params фильтр для выбора
   *  "filter" =>
   *     "all"      - список всех платежных шлюзов
   *     "enabled"  - список разрешенных платежных шлюзов (по умолчанию) 
   *     "disabled" - список запрещенных платежных шлюзов  
   * @return int количество платежных шлюзов
   */   
   public function get_count($filter = "all") {
         switch ($filter) {
            case "disabled":
               $this->db->where('payment_gateways.status', 'disabled');
               break;
            case "enabled":
               $this->db->where('payment_gateways.status', 'enabled');
               break;               
         }       
      
         $this->db->join('entities', 'payment_gateways.id_entity=entities.id_entity');
         return $this->db->count_all_results ('payment_gateways');
   } //end get_count
   
   /**
    * изменение статуса платежного шлюза
    *
    * @param int $id идентификатор платежного шлюза
    * @param string $status новый статус платежного шлюза
    * @return none
    */
   public function set_status($id = -1, $status = 'enabled') {
      $this->db->where ( $this->_id_field_name, $id )
               ->update ( $this->_table_name, array ('status' => $status ) );
   } //end set_status
   
   /**
    * Получение названия платежного шлюза
    *
    * @param int $id идентификатор платежного шлюза
    * @return null|string название платежного шлюза
    */
   public function get_name($id = -1) {
      $this->db->select('name');
      $query = $this->db->get_where('entities', array('id_entity' => $id));
      if ($query->num_rows() > 0) {
         return $query->row()->name;
      } else {
         return null;
      }
   } //end get_name
   
    /**
    * Получение условного кода платежного шлюза
    *
    * @param int $id идентификатор платежного шлюза
    * @return null|string условный код платежного шлюза
    */
   public function get_id_gateway($id = -1) {
      $query = $this->db->select('id_gateway')
                    ->get_where('payment_gateways', array('id_entity' => $id));
                    
      if ($query->num_rows() > 0) {
         return $query->row()->id_gateway;
      } else {
         return null;
      }
   } //end get_name
   
   /**
    * Получение данных платежного шлюза, подлежащих редактированию администратором
    *
    * @param int $id идентификатор платежного шлюза
    * @return null|object 
    */
   public function get_edit_info($id = -1) {
      $query = $this->db->get_where($this->_table_name, array($this->_id_field_name => $id));
      
      if ($query->num_rows() > 0) {
         return $query->row();
      } else {
         return NULL;
      }
   } //end get_edit_info
   
   /**
    * Обновление данных платежного шлюза, подлежащих редактированию администратором
    *
    * @param int $id идентификатор платежного шлюза
    * @param array $info массив новых параметров платежного шлюза
    * @return none 
    */
   public function set_edit_info($id = -1, $info = array()) {
      $this->db->where( $this->_id_field_name, $id )
               ->update($this->_table_name, $info);
   } //end set_edit_info  

   /**
   * возвращает данные для заданного платежного шлюза
   *
   * @param string $id_gateway кодовое имя платежного шлюза
   * @return object массив с данными платежного шлюза
   */
   public function data($id_gateway) {
      $res = $this->db->get_where('payment_gateways', array('id_gateway' => $id_gateway));
      if ($res->num_rows() > 0) {
         return $res->row();
      }  
      return NULL;           
   } //end data   

   /**
   * проводит через систему перевод дененжных средств
   *
   * @param integer $id_entity_from уникальный код пользователя отправителя
   * @param integer $id_entity_to уникальный код пользователя получателя
   * @param float $value денежная сумма
   * @param float $balance_receipt баланс получателя после транзакции
   * @param float $balance_expense баланс отправителя после транзакции
   * @param string $flow_program программа перевода средств
   * @param integer $id_flow код родительского перевода, если такой имеется
   * @param bool $is_processed флаг завершенной операции перевода
   * @param string $transaction_id опциональный код транзакции
   * 
   * @return integer уникальный код осуществленного перевода
   */
   public function money_flow($id_entity_from, $id_entity_to, $value, $balance_receipt, $balance_expense, $flow_program, $id_flow, $is_processed, $transaction_id = NULL) {
      
      if (is_null($balance_receipt)) {
         $CI =& get_instance();
         $CI->load->model('entity', '', TRUE);
         $balance_receipt = $CI->entity->ballance($id_entity_to);         
      }
      if (is_null($balance_expense)) {
         $CI =& get_instance();
         $CI->load->model('entity', '', TRUE);
         $balance_expense = $CI->entity->ballance($id_entity_from);                  
      }

      $this->db->insert('money_flows',
         array(
            'id_entity_receipt' => $id_entity_to,
            'id_entity_expense' => $id_entity_from,
            'flow_date' => type_to_str(time(), 'databasedatetime'),
            'value' => $value,
            'flow_program' => $flow_program,
            'id_flow_parent' => $id_flow,
            'is_processed' => $is_processed ? 'true' : 'false',
            'balance_receipt' => $balance_receipt,
            'balance_expense' => $balance_expense
         ));
   
      return $this->db->insert_id();         
   } //end money_flows   
   
   /**
   * зачисление платежа на счет заданного пользователя
   *
   * @param string $e_mail почтовый адрес пользователя
   * @param string $gateway название платежного шлюза
   * @param float $amount зачисляемая сумма
   * @param string $transaction_id (опционально) уникальный индентефикатор транзакции возвращаемый платежным шлюзом 
   * @return ничего не возвращает   
   */
   public function deposit($e_mail, $gateway, $amount, $transaction_id = NULL) {
      $data = $this->data($gateway);
      $CI =& get_instance();
      $CI->load->model('entity', '', TRUE);
      $entity_id = $CI->entity->get_id_by_email($e_mail);
      $ballance = number_format($CI->entity->ballance($entity_id) + $amount, 2, '.', '');
      $CI->entity->set_ballance($entity_id, $ballance);
      $id_flow = $this->money_flow(
         $data->id_entity, $entity_id, $amount, $ballance, 0, 'deposit', 0, true);
      
      $CI->load->model('gateway_payments', '', TRUE);
      
      $CI->gateway_payments->insert(array('id_flow' => $id_flow,
      									  'transaction_id' => $transaction_id,
      									  'description' => 'Deposit from '.$CI->entity->get_name($data->id_entity)
      									));
      $CI->load->model('sites_channels', '', TRUE);         
      $CI->sites_channels->autopay($entity_id);               
      									
   } //end deposit     
   
   /**
   * перевод средств со счета заданного пользователя
   *
   * @param string $e_mail почтовый адрес пользователя
   * @param string $gateway название платежного шлюза
   * @param float $amount переводимая сумма
   * @param string $transaction_id (опционально) уникальный индентефикатор транзакции возвращаемый платежным шлюзом
   * @param UNIX_TIMESTAMP $valid_thru (опционально) срок до которого чек должен быть обналичен 
   * @return ничего не возвращает   
   */
   public function withdraw($e_mail, $gateway, $amount, $transaction_id = NULL, $valid_thru = 0) {
      $data = $this->data($gateway);
      $CI =& get_instance();
      $CI->load->model('entity', '', TRUE);
      $entity_id = $CI->entity->get_id_by_email($e_mail);
      $ballance = number_format($CI->entity->ballance($entity_id) - $amount, 2, '.', '');
      $CI->entity->set_ballance($entity_id, $ballance);
      $id_flow = $this->money_flow(
         $data->id_entity, $entity_id, $amount, $ballance, 0, 'withdraw', 0, true);
      
      $CI->load->model('gateway_payments', '', TRUE);
      
      $CI->gateway_payments->insert(array('id_flow' => $id_flow,
      									  'transaction_id' => $transaction_id,
      									  'description' => 'Withdraw to '.$CI->entity->get_name($data->id_entity),
                                   'valid_thru' => $valid_thru
      									));
   } //end withdraw 
}

?>