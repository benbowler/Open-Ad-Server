<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс для постраничного вывода
 * 
 * @version 1.0
 * @author Nemtsev Andrew
 */
class Pagination_Post extends CI_Model{
   
   private $enable_if_one_page = TRUE; // показывать элементы управления даже если всего одна страница
   
   private $show_total = TRUE;         // отображать общее количество записей
   
   private $prefix = '';               // префикс переменных в базе данных
   
   /**
    * Шаблон для вывода общего количества записей
    * 
    * @var string
    */
   private $_totalTemplate = '/common/pagination/total.html';
   /**
    * Шаблон для всего пагинатора
    * 
    * @var string
    */
   private $_paginationTemplate = '/common/pagination/pagination.html';
 
   /**
    * Номер текущей страницы
    *
    * @access private
    * @var integer
    */
   private $page;
   
   /**
    * Количество страниц
    *
    * @access private
    * @var integer
    */
   private $num_pages;
   
   /**
    * Максимальное количество записей на странице
    *
    * @access private
    * @var integer
    */
   private $per_page;
   
   /**
    * Имя формы, которая будет отправляться методом POST
    *
    * @access private
    * @var string
    */
   private $form_name;
   
   /**
    * Общее число записей
    *
    * @var integer
    */
   private $total_records;
   
   public $temporary;
   
   public function __construct() {
      parent::__construct();
      $this->clear();
      //$this->load->library('parser');
   }
   
   /**
    * Очистка свойств класса
    *
    * @access public
    * @var string
    */
   function clear() {
      $this->page = 1;
      $this->num_pages = 1;
      $this->per_page = 10;
      $this->form_name = '';
      $this->total_records = 0;
      $this->show_total = TRUE;
      $this->enable_if_one_page = TRUE; 
   }
   /**
    * Установка номера текущей страницы
    *
    * @access public
    * @param integer $page
    */
   function set_page($page) {
   	if (is_numeric($page) && ($page > 0)) {
         $this->page = $page;
   	}
   } // end set_page
   
   /**
    * Получение номера текущей страницы
    *
    * @access public
    * @return integer
    */
   function get_page() {
      return $this->page;
   } // end get_page
   
   /**
    * Установка количества страниц
    *
    * @access public
    * @param integer $num_pages
    */
   function set_num_pages($num_pages) {
   if (is_numeric($num_pages) && ($num_pages > 0)) {
         $this->num_pages = $num_pages;   
      }
   } // end set_num_pages
   
   /**
    * Получение количества страниц
    *
    * @access public
    * @return integer
    */
   function get_num_pages() {
      return $this->num_pages;
   } // end get_num_pages
   
   /**
    * Установка количества отображаемых записей на странице
    *
    * @access public
    * @param integer $num_items
    */
   function set_per_page($per_page) {
   	if (is_numeric($per_page) && ($per_page > 0)) {
         $this->per_page = $per_page;
   	}
   } // end set_per_page
   
   /**
    * Получение количества отображаемых записей на странице
    *
    * @access public
    * @return integer
    */
   function get_per_page() {
      return $this->per_page;
   } // end get_per_page
   
   /**
    * Установка имени формы, которая будет отправляться методом POST
    *
    * @access public
    * @param string $name
    */
   function set_form_name($name) {
      $this->form_name = $name;
   } // end set_form_name
   
   /**
    * Получение имени формы, которая будет отправляться методом POST
    *
    * @access public
    * @return string
    */
   function get_form_name() {
      return $this->form_name;
   } // end get_form_name
   
   /**
    * Указание общего числа записей
    *
    * @param int $rec_count общее число записей
    * @param bool $show показывать количество записей (по-умолчанию - да)
    */
   function set_total_records($rec_count, $show = TRUE) {
      $this->show_total = $show;
      if (is_numeric($rec_count) && ($rec_count > 0)) {
      	$this->total_records = $rec_count;
      } else {      
         $this->total_records = 0;
      }
   }
   
   /**
    * Получении ранее установленного общего числа записей
    *
    * @return int общее число записей
    */
   function get_total_records() {
      return $this->total_records;
   }

   /**
   * подавляет вывод Pagination, если имеется толька одна страница
   *
   * @return ничего не возвращает
   */   
   function disable_if_one_page() {
   	$this->enable_if_one_page = FALSE;
   } //disable_if_one_page   
         
   /**
    * Генерирование HTML текста формы
    *
    * @access public
    * @return string
    */
   function create_form() {
    
      if (!$this->enable_if_one_page && $this->num_pages==1) {
         return "";
      }
      
      $pagination_data = array(
            'FIRST_PAGE_FUNC' => '',
            'PREV_PAGE_FUNC' => '',
            'CURRENT_PAGE' => '',
            'NUM_PAGES' => '',
            'NEXT_PAGE_FUNC' => '',
            'LAST_PAGE_FUNC' => '',
            'PER_PAGE' => '',
            'FROM_NAME' => '',
            'UPDATE_PAGINATION_FUNC' => '',
            'TOTAL_COUNT' => ''
            );
      
      $pagination_data['FIRST_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \'1\')';
      
      if ($this->page > 1) {
         $pagination_data['PREV_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \''. ($this->page - 1) .'\')';
      } else {
         $pagination_data['PREV_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \''. $this->page .'\')';
      }
      
      $pagination_data['CURRENT_PAGE'] = $this->page;
      $pagination_data['NUM_PAGES'] = $this->num_pages;
      
      if ($this->page < $this->num_pages) {
         $pagination_data['NEXT_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \''. ($this->page + 1) .'\')';
      } else {
         $pagination_data['NEXT_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \''. $this->page .'\')';
      }
         
      $pagination_data['LAST_PAGE_FUNC'] = 'set_page(\''.$this->form_name.'\', \''. $this->num_pages .'\')';
      $pagination_data['PER_PAGE'] = $this->per_page;
      $pagination_data['FORM_NAME'] = $this->form_name;
      $pagination_data['UPDATE_PAGINATION_FUNC'] = 'update_pagination(\''.$this->form_name.'\')';
            
      $pagination_data['TOTAL'] = "";
      if ($this->show_total) {
         $pagination_data['TOTAL'] = $this->parser->parse($this->_totalTemplate, 
            array('TOTAL_COUNT' => $this->total_records), TRUE);
      }
      
      return $this->parser->parse($this->_paginationTemplate, $pagination_data, TRUE);
   } // end create_form

   /**
   * считывает состояние переменных для Pagination
   *
   * @param string $prefix префикс имен переменных (по умолчанию совпадает с именем формы)
   * @param integer $page страница выбранная по умолчанию
   * @param integer $per_page количество записей на странице по умолчанию
   * @param bool $use_default использовать значения по умолчанию (независимо от переменных в БД)
   * @return type cmt
   */      
   function read_variables($prefix = '', $page = 1, $per_page = 10, $use_default = FALSE, $form_id = NULL) {
      if ($prefix == '') {
         $prefix = $this->form_name;
      }
      $this->prefix = $prefix;      
      
      $CI =& get_instance();
      
      if ($use_default) {
         $this->set_page($page);
         $this->set_per_page($per_page);         
      } else {
      	if (is_null($form_id)) {
      		$page_id = NULL;
      		$per_page_id = NULL;
      	} else { //используем идентификатор формы для определения требуемых имен параметров в POST
      		$page_id = $form_id.'_page';
      		$per_page_id = $form_id.'_per_page';
      	}
         $this->set_page($CI->global_variables->temporary_var($prefix.'_page', $page, $page_id));
         $this->set_per_page($CI->global_variables->temporary_var($prefix.'_per_page', $per_page, $per_page_id));
      }                       
      
      $this->set_num_pages(ceil($this->total_records/$this->per_page));
      
      if ($this->page > $this->num_pages) {
         $this->page = 1;
      }
      
      $this->temporary[$prefix.'_per_page'] = $this->per_page;
      $this->temporary[$prefix.'_page'] = $this->page;
            
   } 
	/**
   	 * считывает состояние переменных для Pagination из сессии
   	 *
   	 * @param string $prefix префикс имен переменных (по умолчанию совпадает с именем формы)
   	 * @param integer $page страница выбранная по умолчанию
   	 * @param integer $per_page количество записей на странице по умолчанию
   	 * @param bool $use_default использовать значения по умолчанию (независимо от переменных в БД)
   	 * @return type cmt
   	 */
   	function read_variables_from_session($prefix = '', $page = 1, $per_page = 10, $use_default = FALSE, $form_id = NULL) {
   		if ($prefix == '') {
        	$prefix = $this->form_name;
      	}
      	$this->prefix = $prefix;      
      
      	$CI =& get_instance();
      
      	if ($use_default) {
        	$this->set_page($page);
         	$this->set_per_page($per_page);         
      	} else {
      		if (is_null($form_id)) {
      			$page_id = NULL;
      			$per_page_id = NULL;
      		} else { //используем идентификатор формы для определения требуемых имен параметров в POST
      			$page_id = $form_id.'_page';
      			$per_page_id = $form_id.'_per_page';
      		}
      		$page = ($CI->input->get_post($prefix.'_page')) ? intval($CI->input->get_post($prefix.'_page')) : $CI->session->userdata($prefix.'_page');
      		if (!$page) $page = 1;
      		
      		$perPage = ($CI->input->get_post($prefix.'_per_page')) ? intval($CI->input->get_post($prefix.'_per_page')) : $CI->session->userdata($prefix.'_per_page');
      		if (!$perPage) $perPage = 10;
      		  
         	$this->set_page($page);
         	$this->set_per_page($perPage);
      	}                       
      
      	$this->set_num_pages(ceil($this->total_records/$this->per_page));
      
      	if ($this->page > $this->num_pages) {
        	$this->page = 1;
      	}
      
      	$this->session->set_userdata($prefix.'_page', $this->page);
      	$this->session->set_userdata($prefix.'_per_page', $this->per_page);
   	}
	/**
	 * Устанавливает шаблон для вывода общего кол-ва записей
	 * 
	 * @param string $template
	 * @return void
	 */   
   	public function setTotalTemplate($template) {
   		$this->_totalTemplate = $template;
   	}
   	/**
   	 * Устанавливает шаблон для вывода пагинатора
   	 * 
   	 * @param string $template
   	 * @return void
   	 */
   	public function setTemplate($template) {
   		$this->_paginationTemplate = $template;
   	}
}
