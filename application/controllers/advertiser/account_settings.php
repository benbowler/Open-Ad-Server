<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/common/parent_entity.php';

/**
 * контроллер для изменения настроек рекламодателя
 *
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Account_settings extends Parent_entity {

	protected $role = "advertiser";
	
	protected $menu_item = "Account Settings";

	/**
	 * конструктор класса,
	 * вносит изменения в структуру базового класса
	 *
	 * @return ничего не возвращает
	 */
	public function Account_settings() {
		parent::Parent_entity();
		$this->form_data["id"] = $this->user_id;
		$this->form_data["kill"] = array("terms_and_conditions", "confirm_password", "cancel_button");
		$this->form_data["redirect"] = "advertiser/account_settings/success";
		unset($this->form_data["fields"]["confirm"]);
		$this->button_name = "{@Update Account@}";
		$this->content['SROLE'] = $this->role;
		$this->content['CODE'] = type_to_str($this->user_id, 'textcode');
		$this->content["CANCEL"] = $this->site_url.$this->index_page."advertiser/account_settings";
		$this->content["INFO"] = $this->load->view("advertiser/account_settings/info.html", "", TRUE);
		$this->_set_title ( implode(self::TITLE_SEP, array(__("Advertiser"),__("Account Settings"))));
		$this->_set_help_index("advertiser_account_settings");
	}

	/**
	 * вызывает функцию редактирования данных адвертайзера
	 *
	 * @return ничего не возвращает
	 */
	public function index() {
		parent::index("modify");
	}

	public function success() {
		$data = array(
			'MESSAGE' => '{@Your account settings successfully updated!@}',
			'REDIRECT' => $this->site_url.$this->index_page.$this->role.'/dashboard');
		$content = $this->parser->parse('common/infobox.html',$data,FALSE);
		$this->_set_content($content);
		$this->_display();
	}
}
?>