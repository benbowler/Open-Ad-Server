<?php

/**
 * Класс для отображения блока с графиками для паблишеров
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_Chart_Publisher extends Sppc_Dashboard_Block_Chart_Abstract {
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
      // Устанавливаем title
      $this->title = __('Graphs for the period');
      
   	  // load hooks
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->admin->dashboard_blocks->graphs)) {
      	 foreach($pluginsConfig->admin->dashboard_blocks->graphs as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Admin_Dashboard_GraphsInterface) {
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
   protected function loadResults(Sppc_Dashboard_DateRange $range) {
      // Получаем данные за период
      $this->CI->load->model('entity');
      $stat = $this->CI->entity->publisher_get_line_stat($range->getUnixStartDate(), $range->getUnixEndDate());
      // Формируем данные для графиков
      $this->addTab('revenue', __('Earnings'), $stat['revenue']);
      $this->addTab('impressions', __('Impressions'), $stat['impressions']);
      $this->addTab('clicks', __('Clicks'), $stat['clicks']);
      $this->addTab('ctr', __('% CTR'), $stat['ctr']);
      
      foreach($this->_hooks as $hookObj) {
      	 $tabs = $hookObj->addTabs($stat);
      	 foreach($tabs as $tab) {
      	 	$this->addTab($tab['name'], $tab['title'], $tab['data']);
      	 }
      }
   }
   
}
