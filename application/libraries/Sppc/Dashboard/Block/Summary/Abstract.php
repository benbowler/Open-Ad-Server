<?php

/**
 * Абстрактный класс отрисовки таблицы с суммарной информацией для dashboard
 *
 * @author Gennadiy Kozlenko
 */
abstract class Sppc_Dashboard_Block_Summary_Abstract implements Sppc_Dashboard_Block_Interface {
   
   /**
    * CI Instance
    *
    * @var object
    */
   protected $CI;
   
   /**
    * Заголовок таблицы
    *
    * @var string
    */
   protected $title;
   
   /**
    * Ссылка на расширенную таблицу с данными
    *
    * @var unknown_type
    */
   protected $moreLink;
   
   /**
    * Массив с данными для отображения
    * Массив ассоциативных массивов следующего формата
    *    title - Заголовок
    *    value - Значение
    *
    * @var array
    */
   protected $rows = array();
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      $this->CI =& get_instance();
   }
   
   /**
    * Получение контента блока
    *
    * @param Sppc_Dashboard_DateRange $range
    * @return string
    */
   public function getContent(Sppc_Dashboard_DateRange $range) {
      // Загружаем данные
      $this->loadResults($range);
      // Формируем массив для парсера
      $data = array(
         'TITLE' => $this->title,
         'SHOW_MORE_LINK' => !empty($this->moreLink) ? array(array('MORE_LINK' => $this->moreLink)) : array(),
         'ROWS' => $this->rows
      );
      return $this->CI->parser->parse('common/dashboard/summary_block.html', $data, true);
   }
   
   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   abstract protected function loadResults(Sppc_Dashboard_DateRange $range);
   
   /**
    * Добавление строки в массив данных
    *
    * @param string $title
    * @param string $value
    */
   protected function addRow($title, $value) {
      array_push($this->rows, array(
         'title' => $title,
         'value' => $value
      ));
   }
   
   /**
    * Установка массива значений
    *
    * @param array $rows
    */
   protected function setRows($rowsData) {
      $this->rows = $rowsData;
   }
   
}
