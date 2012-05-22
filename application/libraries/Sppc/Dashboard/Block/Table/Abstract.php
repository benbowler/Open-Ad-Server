<?php

/**
 * Абстрактный класс для отрисовки блока - таблицы
 *
 * @author Gennadiy Kozlenko
 */
abstract class Sppc_Dashboard_Block_Table_Abstract implements Sppc_Dashboard_Block_Interface {
   
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
    * Массив данных по колонкам
    * Каждая строка - ассоциативный массив
    *    column - название колонки
    *    header - заголовок колонки
    *    type - тип колонки
    *       text, numeric
    *    direction - направление сортировки по умолчанию
    *
    * @var array
    */
   protected $columns = array();
   
   /**
    * Массив строк для таблицы
    * Каждая строка - массив значений
    *
    * @var array
    */
   protected $rows = array();
   
   /**
    * Полес сортировки по умолчанию
    *
    * @var string
    */
   protected $sortField;
   
   /**
    * Направление сортировки по умолчанию
    *
    * @var string
    */
   protected $sortDirection;
   
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
      // Подключаем объект отрисовки таблиц
      $this->CI->load->library('Table_Builder');
      /* @var $tableBuilder Table_Builder */
      $tableBuilder =& $this->CI->table_builder;
      // Переинициализируем объект
      $tableBuilder->clear();
      // Устанавливаем режим отображения данных
      $tableBuilder->insert_empty_cells = true;
      // Задаем название таблицы и поля сортировки
      $tableName = get_class($this);
      $tableBuilder->init_sort_vars($tableName, $this->sortField, $this->sortDirection);
      // Устанавливаем стиль таблицы
      $tableBuilder->add_attribute('class', 'xTable');
      // Загружаем данные
      $this->loadResults($range, $tableBuilder->sort_field, $tableBuilder->sort_direction);
      // Устанавливаем заголовки
      foreach ($this->columns as $i => $colData) {
         $tableBuilder->sorted_column($i, $colData['column'], $colData['header'], $colData['direction']);
         if ('numeric' == $colData['type']) {
            // Если это numeric поле, тогда делаем выравнивание по правому краю
            $tableBuilder->add_col_attribute($i, 'class', 'w100 right');
         }
      }
      $tableBuilder->add_row_attribute(0, 'class', 'th');
      if (0 < count($this->rows)) {
         // Устанавливаем данные
         foreach ($this->rows as $row => $rowData) {
            foreach ($rowData as $col => $colData) {
               $tableBuilder->set_cell_content($row + 1, $col, $colData);
            }
         }
      } else {
         // Выводим No records found
         $tableBuilder->insert_empty_cells = false;
         $tableBuilder->set_cell_content(1, 0, __('Records not found'));
         $tableBuilder->cell(1, 0)->add_attribute('class', 'nodata');
         $tableBuilder->cell(1, 0)->add_attribute('colspan', count($this->columns));
      }
      
      // Формируем массив для парсера
      $data = array(
         'TABLE_NAME' => $tableName,
         'TITLE' => $this->title,
         'SHOW_MORE_LINK' => !empty($this->moreLink) ? array(array('MORE_LINK' => $this->moreLink)) : array(),
         'TABLE' => $tableBuilder->get_sort_html()
      );
      return $this->CI->parser->parse('common/dashboard/table_block.html', $data, true);
   }
   
   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   abstract protected function loadResults(Sppc_Dashboard_DateRange $range, $sortField = '', $sortDirection = '');
   
   /**
    * Добавление строки в таблицу
    *
    * @param array $rowData
    */
   protected function addRow($rowData) {
      array_push($this->rows, $rowData);
   }
   
   /**
    * Установка строк таблицы
    *
    * @param array $rowsData
    */
   protected function setRows($rowsData) {
      $this->rows = $rowsData;
   }
   
   /**
    * Установка значения в конкретную ячейку
    *
    * @param int $row
    * @param int $col
    * @param string $value
    */
   protected function setCell($row, $col, $value) {
      $this->rows[$row][$col] = $value;
   }
   
   /**
    * Добавление колонки
    *
    * @param string $column
    * @param string $header
    * @param string $type
    * @param string $direction
    */
   protected function addColumn($column, $header, $type = 'text', $direction = 'asc') {
      array_push($this->columns, array(
         'column' => $column, 
         'header' => $header, 
         'type' => $type, 
         'direction' => $direction
      ));
   }
   
   /**
    * Установка колонок
    *
    * @param array $colsData
    */
   protected function setColumns($colsData) {
      $this->columns = $colsData;
   }
   
   /**
    * Установка конкретной колонки
    *
    * @param int $col
    * @param string $column
    * @param string $header
    * @param string $direction
    */
   protected function setColumn($col, $column, $header, $type = 'text', $direction = 'asc') {
      $this->columns[$col] = array(
         'column' => $column, 
         'header' => $header, 
         'type' => $type, 
         'direction' => $direction
      );
   }
   
   /**
    * Установка сортировки по умолчанию
    *
    * @param string $sortField
    * @param string $sortDirection
    */
   protected function setSort($sortField, $sortDirection) {
      $this->setSortField($sortField);
      $this->setSortDirection($sortDirection);
   }
   
   /**
    * Установка поля сортировки по умолчанию
    *
    * @param string $sortField
    */
   protected function setSortField($sortField) {
      $this->sortField = $sortField;
   }
   
   /**
    * Получение поля сортировки по умолчанию
    *
    * @return unknown
    */
   protected function getSortField() {
      return $this->sortField;
   }
   
   /**
    * Установка направтения сортировки по умолчанию
    *
    * @param string $sortDirection
    */
   protected function setSortDirection($sortDirection) {
      $this->sortDirection = $sortDirection;
   }
   
   /**
    * Получение направления сортировки по умолчанию
    *
    * @return string
    */
   protected function getSortDirection() {
      return $this->sortDirection;
   }

}
