<?php

/**
 * Класс отрисовки блока топовых рекламодателей на дашборде админа
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_Table_AdminTopAdvertisers extends Sppc_Dashboard_Block_Table_Abstract {
    /**
	 * Hook objects which extend block functionality
	 * 
	 * @var array
	 */
	protected $_hooks = array();
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      parent::__construct();
      // Устанавливаем ссылку
      $this->moreLink = site_url('admin/manage_advertisers');
      // Устанавливаем сортировку по умолчанию
      $this->setSort('revenue', 'desc');
      
   	  // load hooks
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->admin->dashboard_blocks->top_advertisers)) {
      	 foreach($pluginsConfig->admin->dashboard_blocks->top_advertisers as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Admin_Dashboard_TopAdvertisersInterface) {
      	 		$this->_hooks[] = $hookObj;
      	 	}
      	 }
      }
   }

   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   protected function loadResults(Sppc_Dashboard_DateRange $range, $sortField = '', $sortDirection = '') {
      // Подключаем нужный объект
      $this->CI->load->model('entity');
      /* @var $entities Entity */
      $entities = $this->CI->entity;
      if (empty($sortField)) {
         $sortField = $this->getSortField();
      }
      if (empty($sortDirection)) {
         $sortDirection = $this->getSortDirection();
      }
      // Формируем колонки
      $this->addColumn('name', 'Advertiser');
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
      $rowsData = $entities->top_advertisers($sortField, $sortDirection, array(
         'from' => $range->getUnixStartDate(), 
         'to' => $range->getUnixEndDate()
      ));
      
      foreach ($rowsData as $idEntity => $data) {
         $code = type_to_str($idEntity, 'textcode');
         $rowData = array(
            type_to_str($data['name'], 'encode') . " ({$data['email']})",
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
      $this->title = sprintf(__('Top Advertisers (%d)'), count($rowsData));
   }
   
}
