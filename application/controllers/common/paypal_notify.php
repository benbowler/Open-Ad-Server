<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

/**
* контроллер для уведомления о платеже PayPal
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Paypal_notify extends Controller {

   /**
   * конструктор, инициализация базового класа
   *
   * @return ничего не возвращает
   */   
   public function Paypal_notify() {
      parent::Controller();
   } //end Paypal_notify
   
   public function index() {
   	  $LOG =& load_class('Log'); $LOG->write_log('ERROR', 'Paypal notify');
      $req = 'cmd=_notify-validate';
      while (list($key, $value) = each($_POST)) {
         $req .= "&$key=".urlencode($value);
      }
      $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
      $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
      $errno = 0; $errstr = '';
      $fp = fsockopen("www.paypal.com", 80, $errno, $errstr, 30);
      $LOG->write_log('ERROR', 'Request:'.$req);
      if (!$fp) {
      	 $LOG->write_log('ERROR', 'Socket error');      
         return; 
      } else {
         fputs($fp, $header.$req);
         while (!feof($fp)) {
            $res = fgets($fp, 1024);
            if (strcmp($res, "VERIFIED") == 0) {               
      	      $LOG->write_log('ERROR', 'Verified');      
               $time = $this->input->post('invoice');
               $paymentGross = $this->input->post('mc_gross');      
               $business = $this->input->post('receiver_email');
               $this->load->model('payment_gateways', '', TRUE);
               $this->load->model('gateway_payments', '', TRUE);
               $gateway_data = $this->payment_gateways->data('PayPal');
               $email_to = $gateway_data->account_id_1;               
               $custom = $this->input->post('custom');
               $this->load->model('paypal', '', TRUE);
               $data = $this->paypal->load_transaction($custom);
               //if ($data->time == $time && $data->amount == $paymentGross && $business == $email_to) {                                                         
      	          $LOG->write_log('ERROR', 'Transaction OK');      
               	  $paymentStatus = $this->input->post('payment_status');
               	  $LOG->write_log('ERROR', 'Payment Status: '.$paymentStatus);
                  if ($paymentStatus == "Completed" || $paymentStatus == "Pending") {
                     $transaction_id = $this->input->post('txn_id');
                     $this->payment_gateways->deposit($data->e_mail, "paypal", $data->money, $transaction_id);
                     $LOG->write_log('ERROR', 'Deposit OK');
                  }
               //}
            } elseif (strcmp($res, "INVALID") == 0) {
      	       $LOG->write_log('ERROR', 'Invalid');
               exit;
            }
         }
         fclose ($fp);
      }        
   } //end index
   
   public function withdraw() {
      $req = 'cmd=_notify-validate';
      while (list($key, $value) = each($HTTP_POST_VARS)) {
         $req .= "&$key=".urlencode($value);
      }
      $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
      $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
      $fp = fsockopen("www.paypal.com", 80, $errno, $errstr, 30);
      if (!$fp) {
         return; 
      } else {
         fputs($fp, $header.$req);
         while (!feof($fp)) {
            $res = fgets($fp, 1024);
//            $res = "VERIFIED";
            if (strcmp($res, "VERIFIED") == 0) {               
               $time = $this->input->post('invoice');
               $paymentGross = $this->input->post('mc_gross');      
               $this->load->model('payment_gateways', '', TRUE);
               $this->load->model('gateway_payments', '', TRUE);
               $this->load->model('payment_requests', '', TRUE);
               $gateway_data = $this->payment_gateways->data('PayPal');
               $email_to = $gateway_data->account_id_1;               
               $custom = $this->input->post('custom');
               $this->load->model('paypal', '', TRUE);
               $data = $this->paypal->load_transaction($custom);
               if ($data->time == $time) {                                                         
                  $paymentStatus = $this->input->post('payment_status');
                  if ($paymentStatus == "Completed" || $paymentStatus == "Pending") {
                     $transaction_id = $this->input->post('txn_id');
                     
                     //$this->payment_gateways->withdraw($data->e_mail, "paypal", $data->money, $transaction_id);
                     
                     $id_flow = $this->payment_requests->accept($data->id_payment_request);
                     $this->gateway_payments->insert(array('id_flow' => $id_flow,
                                            'transaction_id' => $transaction_id));
                  }
               }
            } elseif (strcmp($res, "INVALID") == 0) {
               exit;
            }
         }
         fclose ($fp);
      }        
   } //end index
   

} //end class Paypal_notify

?>