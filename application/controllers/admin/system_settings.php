<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для изменения системных настроек администратора
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class System_settings extends Parent_controller {
     
   protected $role = "admin";
   
   protected $menu_item = "System Settings";

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function System_settings() {
      parent::Parent_controller();
      $this->_set_title ( implode(self::TITLE_SEP, array(__("Administrator"),__("System Settings"))));
      $this->_set_help_index("system_settings");
   } //end System_settings

   /**
   * показывает форму для изменения настроек или результат изменения настроек
   *
   * @return ничего не возвращает
   */   
   public function index() {
        $this->load->model('ourdatabaseevo');        
      
   	  $this->load->library('Plugins', array(
   	      'path' => array('admin', 'system_settings'),
   	  	  'interface' => 'Sppc_Admin_SystemSettings_Interface'
   	  ));
   /**
   * Добавляет Display 'Your Ad Here' link:
   *
   * @return ничего не возвращает
   */
      $your_ad_here_file = APPPATH.'views/admin/system_settings/your_ad_here.html';
      if (file_exists($your_ad_here_file)) {
         $your_ad_here = $this->load->view('admin/system_settings/your_ad_here.html', '', TRUE);
      } else {
         $your_ad_here = '';
      }
	  
      $afile = APPPATH.'views/admin/system_settings/approve_advertisers.html';
      if (file_exists($afile)) {
         $advsignup = $this->load->view('admin/system_settings/approve_advertisers.html', '', TRUE);
      } else {
         $advsignup = '';
      }
      
      $pfile = APPPATH.'views/admin/system_settings/approve_publishers.html';
      if (file_exists($pfile)) {
         $pubsignup = $this->load->view('admin/system_settings/approve_publishers.html', '', TRUE);
      } else {
         $pubsignup = '';
      }

      $mfile = APPPATH.'views/admin/system_settings/approve_members.html';
      if (file_exists($mfile)) {
         $memsignup = $this->load->view('admin/system_settings/approve_members.html', '', TRUE);
      } else {
         $memsignup = '';
      }
      
      
      
      $form = array(
         "id"          => $this->user_id,
         "name"        => "system_settings",                   
         "view"        => "admin/system_settings/form.html",
         //"success_view" => "admin/system_settings/success.html",
         'redirect'    => 'admin/system_settings/success',
         'vars' => array(
            'USERIDCODE' => type_to_str($this->user_id, 'textcode'),
			'YOUR_AD_HERE' => $your_ad_here,
            'PUBSIGNUP' => $pubsignup,
            'ADVSIGNUP' => $advsignup,
            'MEMSIGNUP' => $memsignup  ,
      		'ADDITIONAL_SETTINGS' => ''
         ),
         "fields"      => array(                     
            "mail" => array(
               "display_name"     => "Administrator E-mail",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required|valid_email"
            ),
            "reserve" => array(               
               "id_field_type"    => "bool",
               "form_field_type"  => "checkbox"                        
            ),                     
            "campaigns" => array(
               "id_field_type"    => "bool",
               "form_field_type"  => "checkbox"                        
            ),        
            "signup" => array(
               "id_field_type"    => "bool",
               "form_field_type"  => "checkbox"                        
            ),
            "pubsignup" => array(
               "id_field_type"    => "bool",
               "form_field_type"  => "checkbox"                        
            ),
            "memsignup" => array(
               "id_field_type"    => "bool",
               "form_field_type"  => "checkbox"                        
            ),            
            "title" => array(
               "display_name"     => "System Title",
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required"
            ),
            "show_your_ad_here_link" => array(
               "display_name"	  => "Show 'Your Ad Here' link",
               "id_field_type"	  => "bool",
               "form_field_type"  => "checkbox"
            ),
            "your_ad_here_link_text" => array(
               "display_name"	  => "'Your Ad Here' link text",
               "id_field_type"	  => "string",
               "form_field_type"  => "text",
               "max"			  => 15
            )                     
         ) 
      );
      $this->plugins->run('registerFieldsForAdditionalSettings', $form);
      $additionalSettings = $this->plugins->run('getAdditionalSettingsHTML', null);
      $form['vars']['ADDITIONAL_SETTINGS'] = implode(' ', $additionalSettings);
      
      $this->load->library("form");
      $this->_set_content($this->form->get_form_content("modify", $form, $this->input, $this));
      $this->_display();
   } //end index

   /**
   * получает текущие данные системных настроек
   *
   * @param type1 var1 cmt1
   * @param type2 var2 cmt2
   * @param type3 var3 cmt3
   * @param type4 var4 cmt4
   * @return array массив с текущими системными настройками
   */   
   public function _load($id) {
      $fields = array(
         "mail"      => $this->global_variables->get("SystemEMail"),
         "reserve"   => $this->global_variables->get("ReserveCPMSlot"),
         "campaigns" => $this->global_variables->get("ApproveCampaigns"),    
         "signup"    => $this->global_variables->get("ApproveSignUp"),      
         "pubsignup" => $this->global_variables->get("ApprovePubSignUp"),      
         "memsignup" => $this->global_variables->get("ApproveMemberSignUp"),
         "title"     => $this->global_variables->get("SiteName"),
     	 "show_your_ad_here_link" => $this->global_variables->get('ShowYourAdHereLink'),
      	 "your_ad_here_link_text" => $this->global_variables->get('YourAdHereLinkText')      
      );
      $this->plugins->run('loadAdditionalSettings', $fields);
   	  return $fields;
   } //end _load
   
   /**
   * сохраняет новые данные системных настроек
   *
   * @param int $id код пользователя (не используется)
   * @param array $fields массив со значениями для системных настроек из полей формы
   * @return string при успехе - пустая строка, иначе текст ошибки
   */   
   public function _save($id, $fields) {
      $this->global_variables->set("SystemEMail", $fields["mail"]);
      $this->global_variables->set("ReserveCPMSlot", $fields["reserve"]);
      $this->global_variables->set("ApproveCampaigns", $fields["campaigns"]);
      $this->global_variables->set("ApproveSignUp", $fields["signup"]);
      $this->global_variables->set("ApprovePubSignUp", $fields["pubsignup"]);
      $this->global_variables->set("ApproveMemberSignUp", $fields["memsignup"]);
      $this->global_variables->set("SiteName", $fields["title"]);
   	  $this->global_variables->set('ShowYourAdHereLink', $fields['show_your_ad_here_link']);
      if ($fields['show_your_ad_here_link']) {
      	 $this->global_variables->set('YourAdHereLinkText', $fields['your_ad_here_link_text']);
      }
      $this->plugins->run('saveAdditionalSettings', $fields);
      return "";
   } //end _save  

   public function success() {
      $data = array(
         'MESSAGE' => 
            __('Congratulations! System settings was successfully updated!'),
         'REDIRECT' => $this->site_url.$this->index_page.'admin/system_settings'
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_approve   
      
}

?>