<?php
if (!defined('BASEPATH') || !defined('APPPATH'))
   exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * родительский контроллер для чтения, изменения или записи данных пользователя
 *
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Parent_entity extends Parent_controller {
   
   protected $form_data;
   
   protected $upgrade_form;
   
   protected $formname = NULL;
   
   protected $autoactivation = false;
   
   protected $view = 'common/sign_up/form.html';
   
   protected $sign_up_role = 'advertiser';
   
   protected $button_name;
   
   protected $cancel_redirect;
   
   protected $form_mode = TRUE;
   
   protected $need_fields = FALSE;
   
   protected $upgrade_mail = '';
   
   protected $select_mode = FALSE;
   
   protected $description = '';
   
   protected $mail_disabled = 'false';
   
   protected $show_upgrade_form = true;
   
   /*
    * список полей которые хранятся в таблице роли такой как advertiser, 
    * member, paublisher и т.д.
    * формат 'название поля' => 'тип для type_to_str()'
    * @author Anton Potekhin
    */
   protected $_fields_from_role_table = array();
   
   /*
    * Список полей формы для form_data.
    * Добавляются в $this->form_data['fields']
    * @author Anton Potekhin
    */
   protected $_additional_form_fields = array();
   
   /**
    * конструктор класса,
    * описывает форму для редактирования данных пользователя
    *
    * @return ничего не возвращает
    */
   public function Parent_entity() {
      parent::Parent_controller();
      $this->load->library('Plugins', array(
         'path' => array('advertiser', 'sign_up'),
         'interface' => 'Sppc_Advertiser_SignUp_Interface'
      ));
      $this->form_mode = $this->input->post('upgrade_mail') === FALSE;
      $this->content['NEEDAGREE'] = '';
      $this->content['ERROR_BIRTHDAY'] = '';
      $this->load->model("entity");
      $password_min = $this->global_variables->get("MinPasswordLen");
      //$default_country = $this->global_variables->get("DefaultCountry");
      $this->form_data = array(
            "name" => "sign_up", 
            "view" => $this->view, 
            "vars" => array("COUPONS_SETTINGS" => ''), 
            "fields" => array(
                  "name" => array(
                        "display_name" => "Full name", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        "validation_rules" => "required", 
                        'max' => 50), 
                  "mail" => array(
                        "display_name" => "E-mail", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        "validation_rules" => "required|valid_email", 
                        'max' => 100), 
                  "password" => array(
                        "display_name" => "Password", 
                        "id_field_type" => "string", 
                        "form_field_type" => "password", 
                        "validation_rules" => "required|min_length[$password_min]", 
                        "default" => NULL, 
                        'max' => 20), 
                  "confirm" => array(
                        "display_name" => "Confirm Password", 
                        "id_field_type" => "string", 
                        "form_field_type" => "password", 
                        "validation_rules" => "required|matches[password]", 
                        'max' => 20), 
                  "country" => array(
                        "display_name" => "Country", 
                        "id_field_type" => "string", 
                        "form_field_type" => "select", 
                        //"validation_rules" => "required",
                        "options" => "countries", 
                        'params' => array(
                              'add_select' => true)/*,
               "default"          => 'html_disabled' //$default_country*/
            ), 
                  "timezone" => array(
                        "display_name" => "TimeZone", 
                        "id_field_type" => "int", 
                        "form_field_type" => "select", 
                        //"validation_rules" => "required",
                        "options" => "timezones", 
                        'params' => array(
                              'add_select' => true), 
                        "default" => ''), 
                  "city" => array(
                        "display_name" => "City", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        'max' => 100), 
                  "state" => array(
                        "display_name" => "State", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        'max' => 100), 
                  "address" => array(
                        "display_name" => "Address", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        'max' => 100), 
                  "zip_postal" => array(
                        "display_name" => "ZIP/Postal Code", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        'max' => 100), 
                  "phone" => array(
                        "display_name" => "Phone #", 
                        "id_field_type" => "string", 
                        "form_field_type" => "text", 
                        'max' => 100), 
                  "agree" => array(
                        "display_name" => "Agree", 
                        "form_field_type" => "checkbox")));


      foreach ($this->_additional_form_fields as $field_name => $field) {
         $this->form_data['fields'][$field_name] = $field;
      }
      
      if ($this->show_upgrade_form == true) {
         $this->upgrade_form = array(
               "name" => "upgrade_form", 
               "view" => 'common/sign_up/upgrade_form.html', 
               "fields" => array(
                     "upgrade_mail" => array(
                           "display_name" => "E-mail", 
                           "id_field_type" => "string", 
                           "form_field_type" => "text", 
                           "validation_rules" => "required|valid_email", 
                           'max' => 100), 
                     "upgrade_password" => array(
                           "display_name" => "Password", 
                           "id_field_type" => "string", 
                           "form_field_type" => "password", 
                           "validation_rules" => "required|min_length[$password_min]", 
                           "default" => NULL, 
                           'max' => 20)),
               "vars" => array(
                     "COUPONS_SETTINGS" => ""
               ));
      }
   } //end Parent_entity
   

   /**
    * выводит на экран форму для редактирования данных пользователя,
    * либо сообщение об успешном выполнении операции
    *
    * @param string $mode режим работы с формой
    *     create - создание нового пользователя
    *     save   - проверка и сохранение данных пользователя
    *     modify - редактирование данных пользователя
    * @param type2 var2 cmt2
    * @param type3 var3 cmt3
    * @param type4 var4 cmt4
    * @return type cmt
    */
   public function index($mode = "create") {
      $this->load->library("form");
      $this->_set_content($this->form->get_form_content($mode, $this->form_data, $this->input, $this));
      $this->content["FORMNAME"] = is_null($this->formname) ? __($this->menu_item) : __($this->formname);
      $upgrade_form = '';
      if ($this->show_upgrade_form == true) {
         $upgrade_form = $this->form->get_form_content($mode, $this->upgrade_form, $this->input, $this);
         $upgrade_form = str_replace('<%CANCEL%>', $this->cancel_redirect, $upgrade_form);
      }
      $upgrade_form = str_replace('<%ERROR_COUPON_CODE%>', '', $upgrade_form);
      $this->content['UPGRADEFORM'] = $upgrade_form;
      $this->content["BUTTONNAME"] = $this->button_name;
      $this->content['CANCEL'] = $this->cancel_redirect;
      $this->content['FORMMODE'] = $this->form_mode ? 'true' : 'false';
      $this->content['NEEDFIELDS'] = $this->need_fields ? 'true' : 'false';
      $this->content['UPGRADEMAIL'] = $this->upgrade_mail;
      $this->content['DESCRIPTION'] = $this->description;
      $this->content['MAILDISABLED'] = $this->mail_disabled;
      $this->content['SELECTMODE'] = '';
      
      // check if more then one role aviable in the system 
      if ($this->select_mode) {
          $aviableRolesCount = 0;
          
          if (file_exists(APPPATH.'views/admin/system_settings/approve_advertisers.html')) {
             $aviableRolesCount++;
          }
          
          if (file_exists(APPPATH.'views/admin/system_settings/approve_publishers.html')) {
             $aviableRolesCount++;
          }
          
          if (file_exists(APPPATH.'views/admin/system_settings/approve_members.html')) {
             $aviableRolesCount++;
          }
          
          if ($aviableRolesCount > 1) {
            $this->content['SELECTMODE'] = $this->load->view('common/sign_up/select_mode.html', '', TRUE);
          }
      }
      
      $this->_display();
   } //end index
   

   /**
    * кодирование заголовков письма в MIME формате
    *
    * @param string $str текст
    * @return string закодированная строка
    */
   public function utf8($str) {
      return '=?UTF-8?B?' . base64_encode($str) . '?=';
   } //end utf8
   

   /**
    * вызывается при успешной регистрации пользователя
    *
    * @param string $reg_mail электронный адрес зарегистрированного пользователя
    * @param intger $user_id код учетной записи
    */
   public function on_registration($reg_mail, $user_id, $role) {
      switch ($role) {
         case 'advertiser':
            $need_activation = $this->global_variables->get("ApproveSignUp");
            break;
         case 'publisher':
            $need_activation = $this->global_variables->get("ApprovePubSignUp");
            break;
         case 'member':
            $need_activation = $this->global_variables->get('ApproveMemberSignUp');
            break;
      }
      
      if ($need_activation) {
         $this->load->library('email');
         $config['charset'] = 'utf-8';
         $config['wordwrap'] = FALSE;
         $this->email->initialize($config);
         $system_email = $this->global_variables->get("SystemEMail");
         $site_name = $this->global_variables->get("SiteName");
         $this->email->from($system_email, $this->utf8($site_name . ' ' . __("Robot")));
         $this->email->to($system_email);
         $this->email->subject($this->utf8($site_name . ' ' . __("User Sign Up Notification")));
         $params = array(
               "LINK" => $reg_mail, 
               'SYSTEM' => $site_name);
         $mail = $this->parser->parse("mails/$this->locale/new_user.html", $params, TRUE);
         $this->email->message($mail);
         $this->email->send();
      } else {
         $need_validation = $this->global_variables->get("ValidateUserEMail");
         if (!$this->autoactivation && !$need_validation) {
            $this->user_name = $reg_mail;
            $this->role = $this->sign_up_role;
            $this->user_id = $user_id;
            $this->_save_session();
         }
      }
   } //end on_registration
   
   /**
    * дополнительная проверка данных и добавление их в базу данных
    *
    * @param array $fields значения полей формы
    * @return string сообщение об ошибке, "" - при успешной операции
    */
   public function _create($fields) {
      switch ($this->sign_up_role) {
         case 'advertiser':
            $need_activation = $this->global_variables->get("ApproveSignUp");
            break;
         case 'publisher':
            $need_activation = $this->global_variables->get("ApprovePubSignUp");
            break;
         case 'member':
            $need_activation = $this->global_variables->get('ApproveMemberSignUp');
            break;
      }
      if ($need_activation && $this->autoactivation) {
         $need_activation = false;
      }
      if (isset($fields['upgrade_mail'])) {
         $id_entity = $this->entity->get_id_by_email($fields["upgrade_mail"]);
         if (is_null($id_entity)) {
            return "There is no account with such E-Mail.";
         }
         $code = $this->entity->password($id_entity);
         if ($code != md5($fields["upgrade_password"])) {
            return __("You must input correct password.");
         }
         $current_roles = $this->entity->get_roles($id_entity);
         if (in_array($this->sign_up_role, $current_roles)) {
            return "User with such E-Mail and Role already exist in the system.";
         }
         if ($this->sign_up_role == 'publisher') {
            if (!$this->entity->have_all_contacts($id_entity)) {
               $this->need_fields = TRUE;
               $this->upgrade_mail = $fields['upgrade_mail'];
               return "You need fill all required fields for register publisher account.";
            }
         }
         if (!$this->entity->add_role($id_entity, $this->sign_up_role, $need_activation)) {
            return __('User with such E-Mail and Role already exist in the system.');
         }
         $this->on_registration($fields["upgrade_mail"], $id_entity, $this->sign_up_role);
         return '';
      }
      
      if (isset($this->form_data['fields']['agree'])) {
         if ($fields["agree"] != "true") {
            return "You must agree with our Terms and Conditions.";
         }
      }
      $admin_create = $this->entity->has_role($this->user_id, 'admin', 'active');
      $id_entity = $this->entity->get_id_by_email($fields["mail"]);
      if (!is_null($id_entity)) {
         if (!$admin_create) {
            return "User with such E-Mail already exist in the system.";
         } else {
            if ($this->entity->has_role($id_entity, $this->sign_up_role)) {
               return __('User with such E-Mail and Role already exist in the system.');
            }
         }
      }
      if (is_null($id_entity) || !$admin_create) {
         $user_id = $this->entity->sign_up(
            $fields, 
            $this->sign_up_role, 
            $need_activation, 
            $this->_fields_from_role_table);
      } else {
         $this->entity->add_role(
            $id_entity,
            $this->sign_up_role,
            false, $fields,
            $this->_fields_from_role_table);
         $user_id = $id_entity;
      }
      if ((isset($fields['photo'])) && (!empty($fields['photo']))) {
         if (file_exists($this->config->item('temp_path') . $fields['photo'])) {
            rename($this->config->item('temp_path') . $fields['photo'], $this->config->item('path_to_photos') . $fields['photo']);
         }
      }
      if (is_null($user_id)) {
         return "Database error! Can't sign up new user into system.'";
      }
      $this->on_registration($fields['mail'], $user_id, $this->sign_up_role);
      return '';
   } //end _create
   

   /**
    * чтение данных пользователя из базы
    *
    * @param int $id код пользователя
    * @return array значения полей формы, в случае ошибке - текст ошибки
    */
   public function _load($id) {
      if (!$id) {
         return array();
      }
      
      $fields = $this->entity->read($id);
      if (sizeof($this->_fields_from_role_table) > 0) {
         $fields_tmp = $this->entity->read_role_data($id, $this->sign_up_role);
         
         foreach ($this->_fields_from_role_table as $field_name => $type) {
            if ('databasedate' == $type) {
               $zend_date = new Zend_Date($fields_tmp[$field_name], Zend_Date::ISO_8601);
               $fields[$field_name] = $zend_date->getTimestamp();
            } else {
               $fields[$field_name] = $fields_tmp[$field_name];
            }
         }
      }
      if (is_null($fields)) {
         return "Database error! Can't read user data.";
      }
      
     
      return $fields;
   } //end _load
   

   /**
    * дополнительная проверка данных и запись их в базу данных
    *
    * @param int $id код пользователя
    * @param array $fields значения полей формы
    * @return string сообщение об ошибке, "" - при успешной операции
    */
   public function _save($id, $fields) {
      $id_by_email = $this->entity->get_id_by_email($fields['mail']);
      if ($id != $id_by_email) {
         if (!is_null($id_by_email)) {
            return __("Account with such e-mail already exist in the system.");
         }
      }
      if (isset($fields["password"])) {
         $code = $this->entity->password($id);
         if ($code != md5($fields["password"])) {
            return __("You must input correct password.");
         }
      }
      $res = $this->entity->save($id, $fields, $this->sign_up_role, $this->_fields_from_role_table);
      if ($res != "") {
         return $res;
      }
      if ($id == $this->user_id) {
         $this->user_name = $fields["mail"];
         $this->_save_session();
      }
      return '';
   } //end _save

   public function create_validation() {
      $data = array(
         'MESSAGE' =>
            __('We need validate your E-Mail.<br>Soon you recieve mail with link. Folow it and you make you registration process completed.'),
         'REDIRECT' => $this->site_url.$this->index_page.$this->sign_up_role.'/login'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_validate
   
   public function create_wait() {
      $data = array(
         'MESSAGE' =>
            __('You successfully sign up in our system!<br>Now you need wait until administrator of project approve your account.<br>After this you will recieve confirmation mail.'),
         'REDIRECT' => $this->site_url.$this->index_page.$this->sign_up_role.'/login'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_wait

}

?>