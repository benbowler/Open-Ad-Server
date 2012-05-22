<?php

/**
 * Абстрактный класс для отрисовки блока - графиков с табами
 *
 * @author Gennadiy Kozlenko
 */
abstract class Sppc_Dashboard_Block_Chart_Abstract implements Sppc_Dashboard_Block_Interface {
   
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
    * Массив табов с данными следующего формата:
    * 
    * Array(
    *    0 => Array(
    *       'name'  => 'tab-name'
    *       'title' => 'Tab title'
    *       'data' => Array(
    *          0 => Value1
    *          1 => Value2
    *          2 => Value3
    *          ...
    *       )
    *    )
    *    ....
    * )
    * 
    * В данном массиве данные в элементе data сгруппированны по дням, индекс начинается с 0
    *
    * @var array
    */
   protected $tabsData = array();
   
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
      // Подключаем класс для работы с табами
      $this->CI->load->library('tabs');
      /* @var $tabsBuilder tabs */
      $tabsBuilder =& $this->CI->tabs;
      // Подключаем класс для работой с графиками
      $this->CI->load->library('google_chart');
      /* @var $chartBuilder google_chart */
      $chartBuilder =& $this->CI->google_chart;
      // Инициализируем класс
      $chartBuilder->date_range(array(
         'from' => $range->getUnixStartDate(),
         'to'   => $range->getUnixEndDate()
      ));
      // Создаем оболочку табов
      $tabsBuilder->create(get_class($this), 'nn', 'h200 vmid center');
      // Создаем табики и рисуем на них графики
      foreach ($this->tabsData as $data) {
         $chartBuilder->data($data['data'], 2);
         $tabsBuilder->add($data['name'], $data['title'], '<img src="' . $chartBuilder->link() . '" alt="" />');
      }
      // Указываем метод сохранения текущего таба
      // TODO: Нужно реализовать
      //$tabsBuilder->set_ajax($this->site_url.'index.php/admin/dashboard/save_tab');
      // Устанавливаем нужный таб по умолчанию
      //$tabsBuilder->select(1);
      // Выводим данные
      $data = array(
         'TITLE'          => $this->title,
         'SHOW_MORE_LINK' => !empty($this->moreLink) ? array(array('MORE_LINK' => $this->moreLink)) : array(),
         'TABS'           => $tabsBuilder->html()
      );
      return $this->CI->parser->parse('common/dashboard/chart_block.html', $data, true);
   }

   /**
    * Метод загрузки данных
    *
    * @param Sppc_Dashboard_DateRange $range
    */
   abstract protected function loadResults(Sppc_Dashboard_DateRange $range);
   
   /**
    * Добавление нового таба с данными
    *
    * @param string $name
    * @param string $title
    * @param array $data
    */
   protected function addTab($name, $title, $data) {
      array_push($this->tabsData, array(
         'name'  => $name,
         'title' => $title,
         'data'  => $data
      ));
   }
   
   /**
    * Установка всех табов
    *
    * @param array $tabsData
    */
   protected function setTabs($tabsData) {
      $this->tabsData = $tabsData;
   }
   
}
