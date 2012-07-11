<?php

/**
 * Абстрактный класс отрисовки блока новостей
 *
 * @author Gennadiy Kozlenko
 */
abstract class Sppc_Dashboard_Block_News_Abstract implements Sppc_Dashboard_Block_Interface {
   
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
    * Массив ассоциативных массивов новостей
    * Ключи ассоциативного массива:
    *    date        - Дата новости
    *    title       - Заголовок новости
    *    description - Описание новости
    *    link        - Ссылка на новость
    *
    * @var array
    */
   protected $rows = array();
   
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
      // Загружаем данные
      $this->loadResults($range);
      // Подготавливаем контент
      $content = '';
      if (0 < count($this->rows)) {
         // Обрабатываем новости для вывода
         $rows = array();
         foreach (array_values($this->rows) as $i => $row) {
            $news = array(
               'DATE'        => $row['date'],
               'TITLE'       => $row['title'],
               'DESCRIPTION' => $row['description'],
               'LINK'        => $row['link'],
               'NOT_LAST'    => $i != count($this->rows) - 1 ? array(array()) : array()
            );
            array_push($rows, $news);
         }
         // Выводим
         $data = array(
            'ROWS'  => $rows,
            'TITLE' => $this->title
         );
         $content = $this->CI->parser->parse('common/dashboard/news_block.html', $data, true);
      }
      return $content;
   }
   
   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   abstract protected function loadResults(Sppc_Dashboard_DateRange $range);
   
   /**
    * Добавление новости
    *
    * @param array $rowData
    */
   protected function addRow($rowData) {
      array_push($this->rows, $rowData);
   }
   
   /**
    * Установка новостей
    *
    * @param array $rowsData
    */
   protected function setRows($rowsData) {
      $this->rows = $rowsData;
   }
   
}
