<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* модель для осуществления платежей по платежной системе PayPal
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Paypal extends CI_Model {
 
   public function __construct() {
      parent::__construct();
   } //end Paypal

   /**
   * сохранение в базе данных о транзакции
   *
   * @param string $e_mail почтовый адрес получателя платежа
   * @param integer $time время создания транзакции
   * @param string $value сумма транзакции
   * @param string $money сумма для зачисления на счет
   * @return integer код записанной транзакции
   */
   protected  function save_transaction($e_mail, $time, $value, $money, $id_payment_request = NULL) {
   	$this->db->insert('paypal_payment_transactions', 
   	   array('e_mail' => $e_mail, 'time' => type_to_str($time, 'databasedatetime'), 'amount' => $value, 'money' => $money, 'id_payment_request' => $id_payment_request));
   	return $this->db->insert_id();
   } //end  save_transaction   
   
   /**
   * чтение из базы данных о транзакции с последующим их удалением
   *
   * @param integer $trans_id код транзакции
   * @return object объект с данными транзакции в формате (time, e_mail, amount), NULL - если не найдено
   */
   public function load_transaction($trans_id) {
      $this->db->select('UNIX_TIMESTAMP(time) AS time, e_mail, amount, money, id_payment_request');
   	  $res = $this->db->get_where('paypal_payment_transactions', array('id_transaction' => $trans_id));
   	  if ($res->num_rows() > 0) {
       //$this->db->delete('paypal_payment_transactions', array('id_transaction' => $trans_id));
   	     return $res->row();
   	  }
   	  return NULL;
   } //end load_transaction      
   
   /**
   * осуществление платежа 
   * 
   * @param array $fields массив с полями формы
   * @return ничего не возвращает
   */   
   public function deposit($fields) {
      $CI =& get_instance();
      $CI->load->model('payment_gateways', '', TRUE);
      $data = $CI->payment_gateways->data('paypal');

      
      if ($fields['amount'] < $data->minimal_payment){
      	return _('Payment amount can\'t be less then ').type_to_str($data->minimal_payment,'money').".";
      }
      
      // Деньги для зачисления
      $value = number_format(type_cast($fields['amount'], 'float'), 2, '.', '');
      
      // Общая сумма
      $amount = $value + $value * $data->fund_comm / 100;
      $amount = number_format(type_cast((string) $amount, 'float'), 2, '.', '');
      
      $sitename = $CI->global_variables->get('SiteName');
      $time = time();
      $email_from = $CI->get_email();
      $email_to = $data->account_id_1;
      $site_url = $CI->get_siteurl();
      $trans_id = $this->save_transaction($email_from, $time, $amount, $value);
      $data = array(
         'AMOUNT' => $amount,
         'EMAIL' => $email_to,
         'ITEMNAME' => $sitename.': Deposit from '.$email_from.' ($'.type_to_str($amount, 'float').')',
         'TIME' => $time,
         'RETURN' => $site_url.'advertiser/add_funds/success',
         'CANCEL' => $site_url.'advertiser/add_funds',
         'NOTIFY' => $site_url.'common/paypal_notify',
         'TRANSID' => $trans_id
      );
      $html = $this->parser->parse('common/payments/paypal.html', $data, TRUE);
      $LOG =& load_class('Log'); $LOG->write_log('ERROR', 'Request: '.$html);
      echo $html;
      exit();
   } //end deposit   
   
   /**
   * осуществление выплаты 
   * 
   * @param array $fields массив с полями формы
   * @return ничего не возвращает
   */   
   public function withdraw($fields) {
      $CI =& get_instance();
      $CI->load->model('payment_gateways', '', TRUE);
      $CI->load->model('payment_requests', '', TRUE);
      $CI->load->model('entity', '', TRUE);
      
      $data = $CI->payment_gateways->data('paypal');
      $payment_request = $CI->payment_requests->get($fields['id_request']);
      
      // Деньги для вывода из системы       
         
         $value = type_cast($fields['payout'], 'float') * (1 - $data->withdraw_comm / 100);
         
         // Общая сумма
         $amount = number_format(type_cast((string) $fields['charge'], 'float'), 2, '.', '');
         
         $sitename = $CI->global_variables->get('SiteName');
         $time = time();
         $email_from = $data->account_id_1;
         $email_to = $CI->entity->get_name_and_mail($payment_request->id_entity)->e_mail;
         $site_url = $CI->get_siteurl();
         $this->payment_requests->update($fields['id_request'],$fields['payout'],$fields['charge']);
         $trans_id = $this->save_transaction($email_from, $time, $amount, $value, $fields['id_request']);
         $data = array(
            'AMOUNT' => $amount,
            'EMAIL' => $email_to,
            'ITEMNAME' => $sitename.': Withdraw for '.$email_to.' ($'.type_to_str($amount, 'float').')',
            'TIME' => $time,
            'RETURN' => $site_url.'admin/pay_to_publishers',
            'CANCEL' => $site_url.'admin/pay_to_publishers',
            'NOTIFY' => $site_url.'common/paypal_notify/withdraw',
            'TRANSID' => $trans_id
         );
         echo $this->parser->parse('common/payments/paypal.html', $data, TRUE);
      exit();
   } //end withdraw  
 
} //end class Paypal
?>