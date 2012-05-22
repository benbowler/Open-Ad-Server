<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* базовый контроллер для просмотра отчетов
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Parent_View_Report extends Parent_Controller {

   public $code = NULL;
   
   protected $role = '';
   
   public $from_controller = 'reports_center';
   
   protected $menu_item = "Reports Center";
   
  public function __construct() {
      parent::__construct();
      $this->_add_java_script('j');
      $this->_add_java_script('table');
      $this->_add_java_script('stuff'); 
   } //end __construct
   
   /**
   * возвращает управление вызывавшему контроллеру
   *
   * @return ничего не возвращает
   */      
   public function _reports_center() {
      redirect($this->role.'/reports_center');
   } //end _reports_center
   
   /**
   * функция по умолчанию, выводит данные выбранного отчета на экран
   *
   * @param POST view_code зашифрованный код выбранного отчета
   * @return ничего не возвращает
   */   
   public function index() {
      $on_start = $this->input->post('on_start');
      if ($on_start) {
         $this->global_variables->kill_vars($this->user_id,
            array(
               'viewreport_page',
               'viewreport_sort_direction',
               'viewreport_sort_field',
               'viewreport_per_page'
            ));
      }
      $code = $this->code;
      if (is_null($code)) {
         $code = $this->input->post('view_code');   
      } 
      
      if (!$code) {
         $this->_report_not_found();
         return;      
      }
      $id_report = type_cast($code, 'textcode');
      $this->load->model('report_types');
      $this->load->model('report_generator', '', TRUE);
      $report = $this->report_types->report($this->user_id, $id_report);

      $info = $this->report_generator->get_report_info($id_report);
      if (!$report) {
         $this->_report_not_found();
         return;      
      }           
      if ($report['id_entity'] != $this->user_id || $info['role'] != $this->role) {
         $this->_report_not_found();
         return;                           
      }
      $this->load->library('Table_Builder');      
      $this->table_builder->clear();
      $this->table_builder->insert_empty_cells = true;

      // Page Total
      $page_total = array();
      $total = array();
            
      $all_columns = $this->report_types->columns_info($report['type'], -1);
      
      foreach ($all_columns['columns'] as $name => $data) {
         if ($data['is_total']) {
            $page_total[$name] = 0;
            $total[$name] = 0;
         }
      }
      
      $columns = $this->report_types->columns_info($report['type'], $report['vis']);
      
      $this->table_builder->init_sort_vars('viewreport', $columns['sort']['name'], $columns['sort']['direction']);
      $this->load->model('pagination_post');
      $this->pagination_post->set_form_name('viewreport');      
      $this->load->model('sqlite');
      $sqlite_file = $this->report_generator->get_sqlite_file($id_report);
      if(!file_exists($sqlite_file) || filesize($sqlite_file) == 0) {
         
         //redirect($this->from_controller.'/not_found');
         //exit();
         $this->_report_not_found();
         return;
         
         
         $this->_set_notification('Sorry! Report file not found.');
         $this->_set_content($this->load->view('common/view_report/file_not_found.html', '', TRUE));
         $this->_display();  
         return;
      }
      $sqlite = $this->sqlite->get_database($sqlite_file);      
      if (!$sqlite) {
         //$this->_set_content($this->load->view('common/view_report/file_not_found.html', '', TRUE));
         //$this->_display();  
         $this->_report_not_found();
         return;       
      }
      
      $this->pagination_post->set_total_records($this->report_generator->total($sqlite));            
      $this->pagination_post->read_variables('viewreport', 1, $this->global_variables->get('ReportRowsPerPage'));
      $reports = $this->report_generator->report(
         $sqlite,
         $this->table_builder->sort_field,
         $this->table_builder->sort_direction,
         $this->temporary['viewreport_page'],
         $this->temporary['viewreport_per_page']
      );         
      $count = 0;
      foreach ($columns['columns'] as $name => $data) {
      	$this->table_builder->sorted_column($count, $name, $data['title'], $data['direction']);
      	$count++;
      }
      
      // Total
      $total = $this->report_generator->total_info($sqlite, array_keys($total));
      
      $this->table_builder->add_row_attribute(0,'class', 'th');      
      $this->table_builder->add_attribute('class', 'xTable');
      $clone_icon = "<img class='icon' src='{$this->site_url}images/pixel.gif'/>".__('Clone');
      $row = 1;
      
      $invariable_columns = array();

      foreach ($reports as $report_data) {
         $column = 0;
         foreach ($report_data as $name => $value) {
            if (isset($page_total[$name])) {
               $page_total[$name] += $value;
            }
            if (isset($columns['columns'][$name])) {
               $c = $columns['columns'][$name];
               if ($c['is_unchanged']) {
                  array_push($invariable_columns, $column);
               }
               $this->table_builder->set_cell_content($row, $column, 
                  type_to_str(type_to_str($value, $c['type']), 'encode'));
               $column++;
            }
         }
         $row++;
      }
      
      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $this->table_builder->set_invariable_columns($invariable_columns);
      
      if (0 == count($reports)) {
         $this->table_builder->insert_empty_cells = false;      
         $this->table_builder->set_cell_content (1, 0, __('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('class', '"nodata"');
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($columns['columns']));         
      } else {
         // Display page total
         foreach (array('Page total' => 'page_total', 'Total' => 'total') as $title => $var) {
            $arr = $$var;
            $this->table_builder->set_cell_content($row, 0, __($title));
            $i = 0;
            foreach ($all_columns['columns'] as $name => $data) {
               if ($data['is_total'] && 'ctr' == $name) {
                  if (isset($arr['clicks']) && isset($arr['impressions'])) {
                     $arr[$name] = $arr['impressions'] ? $arr['clicks'] / $arr['impressions'] * 100 : 0;
                  } else {
                     $arr[$name] = 0;
                  }
               } elseif ($data['is_total'] && 'cpc' == $name) {
                  // TODO: В будущем при добавлении новых типов отчетов нужно покопаться здесь
                  $col = $data['role'] == 'advertiser' ? 'spent' : 'earned';
                  if (isset($arr['clicks']) && isset($arr[$col])) {
                     $arr[$name] = $arr['clicks'] ? $arr[$col] / $arr['clicks'] : 0;
                  } else {
                     $arr[$name] = 0;
                  }
               }
               if (isset($columns['columns'][$name])) {
                  if ($data['is_total']) {
                     $this->table_builder->set_cell_content($row, $i, type_to_str(type_to_str($arr[$name], $data['type']), 'encode'));
                  }
                  $i++;
               }
            }
            $this->table_builder->clear_row_attributes($row);
            $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');
            $row++;
         }
      }
      
      $vars = array(
         'FROM' => __($this->menu_item),
         'REPORTNAME' => $report['title'],
         'TABLE' => $this->table_builder->get_sort_html(),
         'PAGINATION' => $this->pagination_post->create_form(),
         'RETURN' => $this->site_url.$this->index_page.$this->from_controller,
         'FROMCONTROLLER' => $this->from_controller,
         'SORT' => $this->table_builder->sort_field,
         'DIRECTION' => $this->table_builder->sort_direction,
         'ROLE' => $this->role,
         'CODE' => $code,
         'COLUMNS' => $this->table_builder->get_columns_html()
      );
      $this->_set_content($this->parser->parse('common/view_report/template.html', $vars, TRUE));
      $this->_set_help_index("view_report");
      $this->_display();      
   } //end index

   /**
   * то же что и index, но обрабатывает параметр в строке браузера
   *
   * @param string $code шифрованный код отчета
   * @return ничего не возвращает 
   */
   public function code($code) {
   	$this->code = $code;
   	$this->index();   	
   } //end code
         
   private function _report_not_found() {
   	$data = array(
         'MESSAGE' => __('Sorry! Report file not found.'),
         'REDIRECT' => $this->site_url.$this->index_page.$this->role.'/'.$this->from_controller
      );
      $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   } 
   
   function translate($text) {
        preg_match_all("/{@([\s\S]*?)@}/", $text, $matches);
	foreach ($matches[1] as $message) {
	$text = str_replace("{@" . $message . "@}", __($message), $text);
        }       
        return $text;
   } //end _translate
       
} //end class Parent_view_report

?>