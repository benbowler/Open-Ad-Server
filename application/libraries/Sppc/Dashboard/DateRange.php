<?php

/**
 * Класс для работы с диапазоном дат
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard_DateRange {
   
   /**
    * Дата начала диапазона
    *
    * @var DateTime
    */
   private $startDate;
   
   /**
    * Дата конца диапазона
    *
    * @var DateTime
    */
   private $endDate;
   
   /**
    * Флаг, показывающий, установлен ли период ручками
    *
    * @var bool
    */
   private $specifiedRange = false;
   
   /**
    * Конструктор класса
    *
    */
   public function __construct() {
      // Название класса
      $className = get_class($this);
      // Подключаем объект CI
      $CI =& get_instance();
      // Подключаем хелпер периодов дат
      $CI->load->helper('periods');
      // Подготавливаем массив для чтения из поста даты
      $fields = array(
         'from'   => type_cast($CI->input->post('from'), 'date'),
         'to'     => type_cast($CI->input->post('to'), 'date'),
         'period' => $CI->input->post('period'),
         'mode'   => $CI->input->post('mode')
      );
      if (false !== $fields['mode']) {
         // Устанавливаем период
         $period = data_range($fields);
         $this->setUnixRange($period['from'], $period['to']);
         // Сохраняем период
         period_save($className, $fields);
      } else {
         // Загружаем период
         $fields = period_load($className, 'select', 'today');
         // Устанавливаем период
         $period = data_range($fields);
         $this->setUnixRange($period['from'], $period['to']);
      }
      $this->specifiedRange = false;
   }

   /**
    * Установка диапазона дат
    *
    * @param string $startDate
    * @param string $endDate
    */
   public function setRange($startDate, $endDate) {
      $this->setStartDate($startDate);
      $this->setEndDate($endDate);
   }
   
   /**
    * Установка диапазона дат в UNIXTIME
    *
    * @param int $startDate
    * @param int $endDate
    */
   public function setUnixRange($startDate, $endDate) {
      $this->setUnixStartDate($startDate);
      $this->setUnixEndDate($endDate);
   }
   
   /**
    * Установка даты начала диапазона
    *
    * @param string $startDate
    */
   public function setStartDate($startDate) {
      $this->startDate = new DateTime($startDate);
      $this->specifiedRange = true;
   }
   
   /**
    * Установка даты конца диапазона
    *
    * @param string $endDate
    */
   public function setEndDate($endDate) {
      $this->endDate = new DateTime($endDate);
      $this->specifiedRange = true;
   }
   
   /**
    * Установка даты начала диапазона в UNIXTIME
    *
    * @param int $startDate
    */
   public function setUnixStartDate($startDate) {
      $this->startDate = new DateTime(date('Y-m-d H:i:s', $startDate));
      $this->specifiedRange = true;
   }
   
   /**
    * Установка даты конца диапазона в UNIXTIME
    *
    * @param int $endDate
    */
   public function setUnixEndDate($endDate) {
      $this->endDate = new DateTime(date('Y-m-d H:i:s', $endDate));
      $this->specifiedRange = true;
   }
   
   /**
    * Получение даты начала в определенном формате
    *
    * @param string $format
    * @return string
    */
   public function getStartDate($format) {
      return $this->startDate->format($format);
   }
   
   /**
    * Получение даты конца в определенном формате
    *
    * @param string $format
    * @return string
    */
   public function getEndDate($format) {
      return $this->endDate->format($format);
   }
   
   /**
    * Получение даты начала в UNIXTIME
    *
    * @return int
    */
   public function getUnixStartDate() {
      return strtotime($this->getStartDate('Y-m-d H:i:s'));
   }
   
   /**
    * Получение даты конца в UNIXTIME
    *
    * @return int
    */
   public function getUnixEndDate() {
      return strtotime($this->getEndDate('Y-m-d H:i:s'));
   }
   
   /**
    * Установлен ли период ручками?
    *
    * @return bool
    */
   public function isSpecifiedRange() {
      return $this->specifiedRange;
   }
   
}
