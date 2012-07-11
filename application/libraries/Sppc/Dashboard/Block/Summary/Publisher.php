<?php

/**
 * Класс отрисовки блока суммарной инфы по админу на dashboard
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_Summary_Publisher extends Sppc_Dashboard_Block_Summary_Abstract {
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
      $this->moreLink = site_url($this->CI->get_role() . '/reports_center');
      // Устанавливаем title
      $this->title = __('Total changes for the period');
      
   	  // load hooks
   	  $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->admin->dashboard_blocks->summary_performance)) {
      	 foreach($pluginsConfig->admin->dashboard_blocks->summary_performance as $hookClass) {
      	 	$hookObj = new $hookClass();
      	 	if ($hookObj instanceof Sppc_Admin_Dashboard_SummaryPerformanceInterface) {
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
      $stat = $this->CI->entity->publisher_range_stat(array(
         'from' => $range->getUnixStartDate(),
         'to'   => $range->getUnixEndDate()
      ));
      // Формируем строки для отображения
      $this->addRow(__('Total Earnings'), type_to_str($stat['revenue'], 'money'));
      $this->addRow(__('Total Impressions'), type_to_str($stat['impressions'], 'integer'));
      $this->addRow(__('Total Clicks'), type_to_str($stat['clicks'], 'integer'));
      $this->addRow(__('CTR'), type_to_str($stat['ctr'], 'procent'));
      
      // add additional summary performance fields from plugins
      foreach($this->_hooks as $hookObj) {
      	 $fields = $hookObj->addSummaryPerformanceFields($stat);
      	 foreach($fields as $field) {
      	 	$this->addRow($field['title'], $field['value']);
      	 }
      }
   }
   
}
