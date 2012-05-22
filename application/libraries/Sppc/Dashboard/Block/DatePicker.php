<?php

/**
 * Класс отрисовки блока с выбором дат для dashboard
 *
 */
class Sppc_Dashboard_Block_DatePicker implements Sppc_Dashboard_Block_Interface {
   
   /**
    * CI Instance
    *
    * @var object
    */
   private $CI;
   
   /**
    * Период
    *
    * @var Sppc_Dashboard_DateRange
    */
   private $range;
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      // Подключаем объект CI
      $this->CI =& get_instance();
      // Подключаем хелпер периодов дат
      $this->CI->load->helper('periods');
   }
   
   /**
    * @see Sppc_Dashboard_Block_Interface::getContent
    *
    * @param Sppc_Dashboard_DateRange $range
    * @return string
    */
   public function getContent(Sppc_Dashboard_DateRange $range) {
      // Сохраняем range для callback функций
      $this->range = $range;
      // Подключаем библиотеку для работы с формами
      $this->CI->load->library('form');
      /* @var $formBuilder Form */
      $formBuilder =& $this->CI->form;
      
      // Название
      $className = get_class($this);
      
      // Строим массив данных для отображения через Form
      $form = array(
         'name' => $className . '_form',
         'view' => 'common/dashboard/datepicker.html',
         'no_errors' => 'true',
         'vars' => array(
            'CLASS_NAME' => $className,
            'DATEFILTER' => period_html($className) 
         )
      );
      
      // Добавляем в данные для отображения поля для DatePicker
      data_range_fields($form, $range->getUnixStartDate(), $range->getUnixEndDate());

      return $formBuilder->get_form_content('modify', $form, $this->CI->input, $this);
   }
   
   /**
    * подготавливает данные статистики для периода времени заданного пользователем в форме,
    * callback-функция для библиотеки form
    *
    * @param array $fields список полей формы и их сначений
    * @return string всегда 'error' - запрещает выход из формы
    */   
   public function _create($fields) {
      return 'error';
   }

   /**
    * подготавливает данные статистики для периода времени по умолчанию,
    * callback-функция для библиотеки form
    *
    * @param integer $id не используется (обязательный параметр callback-функции)
    * @return array пустой массив (обязательное возвращаемое значение callback-функции)
    */      
   public function _load() {
      // Загружаем данные по класса Sppc_Dashboard_DateRange
      $className = get_class($this->range);
      
      if (!$this->range->isSpecifiedRange()) {
         $fields = period_load($className, $this->range->getUnixStartDate(), $this->range->getUnixEndDate());
      } else {
         $fields = array(
            'from'   => $this->range->getUnixStartDate(),
            'to'     => $this->range->getUnixEndDate(),
            'period' => '',
            'mode'   => 'range'
         );
      }
      return $fields;
   }

}
