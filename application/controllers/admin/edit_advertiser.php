<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_entity.php';

/**
* контроллер для редактирования рекламодателя админом
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Edit_advertiser extends Parent_entity {

   protected $role = "admin";
   
   protected $menu_item = "Manage Advertisers";
   
   protected $formname = 'Edit Advertiser';
   
   protected $form_data;

   /**
   * конструктор класса,
   * вносит изменения в структуру базового класса
   *
   * @return ничего не возвращает
   */   
   public function Edit_advertiser() {
      parent::Parent_entity();
      $this->content["CANCEL"] = $this->site_url.$this->index_page.'admin/manage_advertisers';
      $code = $this->input->post('edit_code');
      $this->content["IDENTITY"] = $code;       
      $this->content['SROLE'] = 'admin';    
      $id_entity = type_cast($code, 'textcode');            
      $this->form_data["id"] = $id_entity;
      $this->form_data["kill"] = array("terms_and_conditions", "confirm_password", "password");
      $this->form_data['vars']['CODE'] = $code;
      $this->form_data["redirect"] = "admin/edit_advertiser/success";  
      unset($this->form_data["fields"]["confirm"]);      
      unset($this->form_data["fields"]["password"]);      
      $this->button_name = __("Update Account");    
      $this->cancel_redirect = $this->site_url .$this->index_page. 'admin/manage_advertisers';
      $this->content["INFO"] = $this->load->view("advertiser/account_settings/info.html", "", TRUE);
      $this->_set_title ( implode(self::TITLE_SEP, array(__( 'Administrator' ) , __( 'Edit Advertiser' ))));
      $this->_set_help_index("edit_advertiser");
      
      $this->load->model('entity', '', TRUE);
      $entity = $this->entity->get_name_and_mail($id_entity);
      if (is_null($entity)) {
         return;
      }
      $this->formname = '<a href="'.$this->site_url.$this->index_page.'admin/manage_advertisers'.'">'.
         __("Manage Advertisers").'</a> &rarr; '.__("Edit Advertiser").':&nbsp;&nbsp;<span class="green i">&bdquo;'.
         $entity->name."($entity->e_mail)&ldquo;</span> ";
   } //end Edit_advertiser         

   /**
   * вызывает функцию редактирования данных адвертайзера
   *
   * @return ничего не возвращает
   */   
   public function index() {
      parent::index("modify");
   } //end index      

   public function success() {
      $data = array(
         'MESSAGE' => 
            __('Congratulations! Advertiser account was successfully updated!'),
         'REDIRECT' => $this->site_url.$this->index_page."admin/manage_advertisers"
      );
      $content = $this->parser->parse('common/infobox.html', $data, FALSE);
      $this->_set_content($content);
      $this->_display();
   } //end create_approve      
   
} //end Sign_up

?>