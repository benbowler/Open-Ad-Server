<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_entity.php';

/**
* контроллер для регистрации адвертайзера в системе
*
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Sign_up extends Parent_entity {

   protected $role = "guest";

   protected $menu_item = "Sign Up";

   protected $select_mode = TRUE;
   
   /**
   * конструктор класса,
   * вносит изменения в структуру базового класса
   *
   * @return ничего не возвращает
   */
   public function Sign_up() {
      parent::Parent_entity();
      $this->form_data["kill"] = array("change_password");
      $need_activation = $this->global_variables->get("ApproveSignUp") == "1";
      if ($need_activation) {
         $this->form_data['redirect'] = "/advertiser/sign_up/create_wait";
         $this->upgrade_form['redirect'] = "/advertiser/sign_up/create_wait";
      } else {
         $this->form_data['redirect'] = "/advertiser/sign_up/create_approve";
         $this->upgrade_form['redirect'] = "/advertiser/sign_up/create_approve";
      }
      $need_validation = $this->global_variables->get("ValidateUserEMail");
      if($need_validation) {
         $this->form_data['redirect'] = "/advertiser/sign_up/create_validation";        
      }
      $this->button_name = "{@Do Sign Up@}";
      $this->content['CANCEL'] = $this->site_url.$this->index_page."advertiser/login";
      $this->content["TERMS"] = $this->load->view("common/sign_up/terms.html", "", TRUE);
      $this->content["INFO"] = $this->load->view("common/sign_up/info.html", "", TRUE);
      $this->_set_title("Advertiser Sign Up");
      $this->_set_help_index("sign_up");
      
   } //end sign_up

   public function create_approve() {
      $data = array(
         'MESSAGE' =>
            __('Congratulations! You successfully sign up in our system!'),
         'REDIRECT' => $this->site_url.$this->index_page.'advertiser/login'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   }  //end create_approve

   /**
   * внешний валидатор формы
   *
   * @param array $fields массив с полями формы
   * @return bool возвращает истину если валидация прошла успешно
   */
   public function _validator($fields) {

      if(isset($fields['coupon']) && trim($fields['coupon']) != '' && strlen(trim($fields['coupon'])) != 10) {
	    $this->content['ERROR_COUPON_CODE'] = "<p class='errorP'>".__("Error coupon code!")."</p>";
	    return FALSE;
	 }
      
	  if(isset($fields['upgrade_mail'])) {
     	  return TRUE;
      }
      $this->content['NEEDAGREE'] = '';
      if ($fields['agree'] != 'true') {
         $this->content['NEEDAGREE'] = "<p class='errorP'>".__("You must agree with our Terms and Conditions.")."</p>";
         return FALSE;
      }
      return TRUE;
   } //end _validator


} //end Sign_up

?>