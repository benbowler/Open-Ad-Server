<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Контроллер дашборда админа
 * 
 * @author Gennadiy Kozlenko
 * @project SmartPPC 6
 * @version 2.0.0
 */
class Dashboard extends Parent_controller implements Sppc_Dashboard_Ajax_Interface {

   protected $role = 'admin';
   
   protected $menu_item = 'Dashboard';

   protected $date_picker = true;

   /**
    * Объект dashboard
    *
    * @var Sppc_Dashboard
    */
   protected $_dashboard;
   
   /**
   * конструктор класса,
   * подключает необходимые js библиотеки, загружает модели и хелперы
   *
   * @return ничего не возвращает
   */   
   public function __construct() {
      parent::Parent_controller();     
      $this->_add_ajax(); 
      $this->load->model('entity', '', TRUE);
      $this->load->helper('fields');
      $this->load->library('Table_Builder');
      // Загружаем dashboard      
      $this->_dashboard = new Sppc_Dashboard();
      $this->_dashboard->setRole('admin');
		
   }
   
   /**
    * создание и вывод HTML-кода дашборда
    *
    * @return ничего не возвращает
    */
   public function index() {
      $range = new Sppc_Dashboard_DateRange();
      $this->_set_title(implode(self::TITLE_SEP, array(__('Administrator'), $this->_dashboard->getTitle())));
      $this->_set_content($this->_dashboard->getContent($range));
      $this->_display();
   } //end index
   
   /**
    * Обработка ajax событий
    *
    */
   public function doAjax() {
      $this->_dashboard->doAjax();
   }

}
