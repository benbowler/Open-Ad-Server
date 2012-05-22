<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

class Add_funds extends Parent_controller {

   protected $role = "advertiser";
   
   protected $menu_item = "Add Funds";

   /**
    * Конструктор класса, вызов конструктора базового класса
    *
    * @return Add_funds
    */
   public function Add_funds() {
      parent::Parent_controller();
      $this->content['paypalNEEDAGREE'] = '';
      $this->content['authoriseNEEDAGREE'] = '';
   } //end Add_funds
   
   
   /**
   * функция по умолчанию, отображает формы для внесения платежей
   *
   * @return ничего не возвращает
   */   
   public function index() {
      $LOG =& load_class('Log'); $LOG->write_log('ERROR', 'Add Funds Index');
   		$this->_set_title ( implode(self::TITLE_SEP, array(__("Advertiser") , __("Add Funds"))));
      
   		
      $this->_set_help_index("advertiser_add_funds");
      $this->load->model('payment_gateways', '', TRUE);
//      $list = $this->payment_gateways->get_list(array('name' => TRUE, 'filter' => 'enabled'));
      $list = $this->payment_gateways->get_list(
         array(
            'fields' => array('id_gateway', 'name', 'fund_comm','minimal_payment'), 
            'filter' => 'enabled',
            'deposit' => 'true'));
      $forms = '';
      $options = '';
      $this->load->library("form");
      $select_id = $this->input->post('id');
      if (is_array($list)) {
         $row = current($list);
         
            include APPPATH."controllers/common/gateways/paypal.php";
            if (!isset($this->content['paypalNEEDAGREE'])) {
               $this->content['paypalNEEDAGREE'] = '';
            }
         
      }

 $vars = array(
         'GATEWAYFORMS' => $forms,
         'LIST' => $options,
         'MONEYFORMAT' => get_money_format(),
         'NUMBERFORMAT' => get_number_format()
      );
      $this->_set_content($this->parser->parse('advertiser/add_funds/form.html', $vars, TRUE));
      $this->_display();      
   } //end index

   /**
   * осуществление платежа, callback-функция для библиотеки forms
   *
   * @param array $fields массив с полями формы
   * @return string текст ошибки или '' в случае удачи
   */
   public function _save($id, $fields) {
   	  $LOG =& load_class('Log'); $LOG->write_log('ERROR', "Deposit: $id - ".var_export($fields, true));
      $this->load->model($id, '', TRUE);
      $res=$this->$id->deposit($fields);
      if ($res == '') {
         $this->load->model('sites_channels', '', TRUE);         
         $this->sites_channels->autopay($this->user_id);               
      }
      return $res;
   } //end _save
   
   /**
   * функция - заглушка для загрузки данных форм по умолчанию
   *
   * @return array пустой массив
   */
   public function _load($id) {
      return array();
   } //end _load
   
   /**
   * вывод страницы об успешном совершении платежа
   *
   * @return ничего не возвращает
   */
   public function success() {
      $this->_set_title(implode(self::TITLE_SEP, array(__("Advertiser") , __("Add Funds") , __("Success"))));
      $this->_set_help_index("advertiser_add_funds");
      $data = array(
         'MESSAGE' => 
            __('Congratulations! Payment completed.'),
         'REDIRECT' => $this->site_url.$this->index_page.'advertiser/add_funds'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();      
   } //end success
   
   /**
   * внешний валидатор для формы
   *
   * @param array $fields массив с полями формы
   * @return bool истина, если валидация прошла успешна
   */
   public function _validator($fields, $int_validator) {
      $id = $this->input->post('id');
      $val = type_cast($fields['amount'], 'float');
      if (is_numeric($val) && $val>0) {
      	if (!$fields['agree']) {
            $this->content[$id.'NEEDAGREE'] = "<p class='errorP'>".__("You must agree to pay amount with commission.")."</p>";
      	   return FALSE;
      	}
      }
   	return TRUE;
   } //end _validator   
   
}

?>