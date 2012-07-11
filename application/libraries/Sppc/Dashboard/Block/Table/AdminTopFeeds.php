<?php

/**
 * Класс отрисовки блока топовых фидов на дашборде админа
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_Table_AdminTopFeeds extends Sppc_Dashboard_Block_Table_Abstract {
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      parent::__construct();
      // Устанавливаем ссылку
      $this->moreLink = site_url('admin/manage_feeds');
      // Устанавливаем сортировку по умолчанию
      $this->setSort('revenue', 'desc');
   }

   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   protected function loadResults(Sppc_Dashboard_DateRange $range, $sortField = '', $sortDirection = '') {
      // Подключаем объект фидов
      $this->CI->load->model('feeds');
      /* @var $feeds Feeds */
      $feeds = $this->CI->feeds;
      if (empty($sortField)) {
         $sortField = $this->getSortField();
      }
      if (empty($sortDirection)) {
         $sortDirection = $this->getSortDirection();
      }
      // Формируем колонки
      $this->addColumn('name', 'Feed Name');
      $this->addColumn('impressions', 'Impressions', 'numeric', 'desc');
      $this->addColumn('clicks', 'Clicks', 'numeric', 'desc');
      $this->addColumn('ctr', '% CTR', 'numeric', 'desc');
      $this->addColumn('revenue', 'Earnings', 'numeric', 'desc');
      // Получаем данные по фидам
      $rowsData = $feeds->top($sortField, $sortDirection, array(
         'from' => $range->getUnixStartDate(), 
         'to' => $range->getUnixEndDate()
      ));
      foreach ($rowsData as $data) {
         $rowData = array(
            type_to_str($data['title'], 'encode'),
            type_to_str($data['impressions'], 'integer'),
            type_to_str($data['clicks'], 'integer'),
            type_to_str($data['ctr'], 'procent'),
            type_to_str($data['revenue'], 'money')
         );
         $this->addRow($rowData);
      }
      // Устанавливаем title
      $this->title = sprintf(__('Top Feeds (%d)'), count($rowsData));
   }
   
}
