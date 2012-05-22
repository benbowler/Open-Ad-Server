<?php

/**
 * Класс отрисовки блока топовых сайтов на дашборде админа
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_Table_AdminTopSites extends Sppc_Dashboard_Block_Table_Abstract {
   
   /**
	 * Hook objects which extend block functionality
    *
	 * @var array
	 */
	protected $_hooks = array();
	/**
	 * Constructor
    */
   public function __construct() {
      parent::__construct();

   // load hooks
   $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->admin->dashboard_blocks->top_sites)) {
         foreach($pluginsConfig->admin->dashboard_blocks->top_sites as $hookClass) {
            $hookObj = new $hookClass();
      	       if ($hookObj instanceof Sppc_Admin_Dashboard_TopSitesInterface) {
      	          $this->_hooks[] = $hookObj;
      	       }
         }
      }


      // Устанавливаем ссылку
      $this->moreLink = site_url('admin/manage_sites_channels');
      // Устанавливаем сортировку по умолчанию
      $this->setSort('revenue', 'desc');
   }


   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   protected function loadResults(Sppc_Dashboard_DateRange $range, $sortField = '', $sortDirection = '') {
      // Подключаем нужный объект
      $this->CI->load->model('site');
      /* @var $sites Site */
      $sites = $this->CI->site;
      if (empty($sortField)) {
         $sortField = $this->getSortField();
      }
      if (empty($sortDirection)) {
         $sortDirection = $this->getSortDirection();
      }
      // Формируем колонки
      $this->addColumn('name', 'Site Name');
      $this->addColumn('impressions', 'Impressions', 'numeric', 'desc');
      $this->addColumn('clicks', 'Clicks', 'numeric', 'desc');
      $this->addColumn('ctr', '% CTR', 'numeric', 'desc');
      $this->addColumn('revenue', 'Earnings', 'numeric', 'desc');
      
   	  // add additional columns from plugins
      foreach($this->_hooks as $hookObj) {
      	 $columns = $hookObj->addColumns($this); 
      	 foreach($columns as $column) {
      	 	$this->addColumn($column['name'], $column['title'], $column['type'], $column['order']);
      	 }
      }
      
      // Получаем данные по фидам
      $rowsData = $sites->top(null, $sortField, $sortDirection, array(
         'from' => $range->getUnixStartDate(), 
         'to' => $range->getUnixEndDate()
      ));
      
      foreach ($rowsData as $data) {
         $code = type_to_str($data['id_publisher'], 'textcode');
         $rowData = array(
            limit_str_and_hint($data['name'], 20) . " (<a target='_blank' href='http://{$data['url']}'>{$data['url']}</a>)",
            type_to_str($data['impressions'], 'integer'),
            type_to_str($data['clicks'], 'integer'),
            type_to_str($data['ctr'], 'procent'),
            type_to_str($data['revenue'], 'money')
         );
         
      	 // add data for addtional columns from plugins
         foreach($this->_hooks as $hookObj) {
         	$rowData = $hookObj->addColumnsData($data, $rowData);
         }
         
         $this->addRow($rowData);
      }
      // Устанавливаем title
      $this->title = sprintf(__('Top Sites (%d)'), count($rowsData));
   }
   
}
