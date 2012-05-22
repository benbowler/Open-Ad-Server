<?php

/**
 * Класс дашбоарда
 *
 * @author Gennadiy Kozlenko
 */
class Sppc_Dashboard implements Sppc_Dashboard_Ajax_Interface {
   
   /**
    * Title по умолчанию
    *
    */
   const DEFAULT_TITLE = 'Dashboard';

   /**
    * Идентификатор роли пользователя, для которого строится dashboard
    *
    * @var int
    */
   private $idRole = 0;
   
   /**
    * Роль пользователя, для которого строится dashboard
    *
    * @var unknown_type
    */
   private $role;
   
   /**
    * Ассоциативный массив блоков для отображения
    * Ключ - колонка
    * Значения - массив классов реализующих интерфейс Sppc_Dashboard_Block_Interface
    *
    * @var array
    */
   private $blocks = array();
   
   /**
    * Шаблон dashboard
    *
    * @var string
    */
   private $template = 'common/dashboard/template.html';
   
   /**
    * Заголовок dashboard
    *
    * @var string
    */
   private $title;
   
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
      $this->CI =& get_instance();
   }
   
   /**
    * Получение контента блоков dashboard
    *
    * @param Sppc_Dashboard_DateRange $range
    * @return string
    */
   public function getContent(Sppc_Dashboard_DateRange $range) {      
      // Подгружаем блоки
      $this->loadBlocks();
      // Данные для парсера
      $data = array(
         'TITLE' => $this->getTitle()
      );
      // Обрабатываем блоки
      foreach ($this->blocks as $column => $blocks) {
         // Собираем контент блоков
         $blocksContent = '';
         foreach ($blocks as $className) {
            try {
               Sppc_Loader::tryLoadClass($className);
               /* @var $block Sppc_Dashboard_Block_Interface */
               $block = new $className;
               $blocksContent .= $block->getContent($range);
            } catch (Exception $e) {
               // Class $className not found
            }
         }
         // Загоняем этот контент в данные для парсера
         $data[strtoupper($column) . '_BLOCKS'] = $blocksContent;
      }
      // Добавляем служебные теги
      $data['SITE_URL'] = base_url();
      /* @var $router CI_Router */
      $router =& load_class('Router');
      $data['CONTROLLER'] = $router->fetch_class();
      if ('' != $router->fetch_directory()) {
         $data['CONTROLLER'] = $router->fetch_directory() . '/' . $router->fetch_class();
      }
      $content = $this->CI->parser->parse($this->template, $data, true);
      // Убираем лишние BLOCK теги
      $content = preg_replace('~<%[A-Z]+_BLOCKS%>~', '', $content);
      return $content;
   }
   
   /**
    * Обработка Ajax вызова
    *
    */
   public function doAjax() {
      // Смотрим какой блок вызывать
      $className = $this->CI->input->post('block');
      // Пытаемся загрузить
      try {
         Sppc_Loader::tryLoadClass($className);
         /* @var $block Sppc_Dashboard_Ajax_Interface */
         $block = new $className;
         if ($block instanceof Sppc_Dashboard_Ajax_Interface) {
            $block->doAjax();
         }
      } catch (Exception $e) {
         // Class $className not found
      }
   }
   
   /**
    * Установка роли
    *
    * @param string $role
    */
   public function setRole($role) {
      $this->role = $role;
      // Получаем объект для работы с ролями
      $this->CI->load->model('roles');
      $this->idRole = $this->CI->roles->get_role_by_name($role)->id_role;
   }
   
   /**
    * Установка шаблона dashboard
    *
    * @param string $template
    */
   public function setTemplate($template) {
      $this->template = $template;
   }
   
   /**
    * Установка title
    *
    * @param string $title
    */
   public function setTitle($title) {
      $this->title = $title;
   }
   
   /**
    * Получение title
    *
    * @return string
    */
   public function getTitle() {
      if (!empty($this->title)) {
         return $this->title;
      }
      return __(self::DEFAULT_TITLE);
   }
   
   /**
    * Загрузка блоков для отображения из базы
    *
    */
   private function loadBlocks() {
      $this->blocks = array();
      $this->CI->db->select('column, name')
         ->from('dashboard_blocks')
         ->where('id_role', $this->idRole)
         ->order_by('position');
      $query = $this->CI->db->get();
      if (0 < $query->num_rows()) {
         foreach ($query->result() as $row) {
            if (!isset($this->blocks[$row->column])) {
               $this->blocks[$row->column] = array();
            }
            array_push($this->blocks[$row->column], $row->name);
         }
      }
   }
   
   /**
    * Получение блоков для определенной колонки
    *
    * @param string $column
    * @return array
    */
   private function getBlocks($column) {
      if (isset($this->blocks[$column])) {
         return $this->blocks[$column];
      }
      return array();
   }
   
}
