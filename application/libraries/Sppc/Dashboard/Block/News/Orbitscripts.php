<?php

/**
 * Класс отображения новостей от OrbitScripts
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_News_Orbitscripts extends Sppc_Dashboard_Block_News_Abstract {
   
   public function __construct() {
      parent::__construct();
      $this->title = __('Orbitscripts.com News');
   }

   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   protected function loadResults(Sppc_Dashboard_DateRange $range) {
      // Получаем новости
      $this->CI->load->model('admin_news');
      
      $need_update = (time() - $this->CI->global_variables->get('orbitscripts_news_time'))>60*60*12;
      if($need_update){
         $xml_file = $this->CI->global_variables->get('orbitscripts_news_xml');
         $this->CI->load->model('orbit_news','',TRUE);
         $rez = $this->CI->orbit_news->parse_xml($xml_file);
         if (is_null($rez)) { 
            $this->CI->global_variables->set('orbitscripts_news_time',time()); 
         }
         
      }
      $news = $this->CI->admin_news->get_admin_news();
      // Добавляем новости для вывода
      foreach ($news as $row) {
         $rowData = array(
            'date'        => type_to_str($row['date'], 'mysqldate'),
            'title'       => type_to_str($row['title'], 'encode'),
            'description' => nl2br($row['content']),
            'link'        => $row['link']
         );
         $this->addRow($rowData);
      }
   }
   
}
