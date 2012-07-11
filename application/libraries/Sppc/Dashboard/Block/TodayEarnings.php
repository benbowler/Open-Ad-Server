<?php

/**
 * Класс отрисовки блока с заработком админа
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_Block_TodayEarnings implements Sppc_Dashboard_Block_Interface {

   /**
    * CI Instance
    *
    * @var object
    */
   private $CI;
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      // Подключаем объект CI
      $this->CI =& get_instance();
   }
   
   /**
    * @see Sppc_Dashboard_Block_Interface::getContent
    *
    * @param Sppc_Dashboard_DateRange $range
    * @return string
    */
   public function getContent(Sppc_Dashboard_DateRange $range) {
      // Получаем заработок за период
      $this->CI->load->model('entity');
      $stat = $this->CI->entity->publisher_range_stat(array(
         'from' => mktime(0, 0, 0),
         'to'   => time()
      ));
      // Выводим
      $data = array(
         'REVENUE' => type_to_str($stat['revenue'], 'money')
      );
      return $this->CI->parser->parse('common/dashboard/today_earnings.html', $data, true);
   }
   
}
