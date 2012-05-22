<?php // -*- coding: UTF-8 -*-

/**
 * Класс, расширяющий базовый функционал CI_Input
 *
 */
class MY_Input extends CI_Input {
   
   /**
    * Хранилище альтернативных глобальных массивов
    *
    * @var array
    */
   var $storage = array();
   
   /**
    * Конструктор класса
    *
    * @return MY_Input
    */
   function MY_Input() {
      log_message('debug', "Input Class Initialized");
      $CFG =& load_class('Config');
      $this->use_xss_clean = ($CFG->item('global_xss_filtering') === TRUE) ? TRUE : FALSE;
      $this->allow_get_array  = true;
      $this->_sanitize_globals();
   }
   
   /**
    * Установка хранилища альтернативных глобальных массивов
    *
    * @param array $storage
    */
   function set_storage($storage) {
      $this->storage = $storage;
   }
   
   /**
    * Sanitize Globals
    *
    */
   function sanitize_globals() {
      $this->_sanitize_globals();
   }
   
   /**
    * Fetch an item from the GET array (use storage)
    *
    * @param string $index
    * @param bool $xss_clean
    * @return string
    */
   function storage_get($index = '', $xss_clean = false) {
      if (isset($this->storage['_GET'])) {
         return $this->_fetch_from_array($this->storage['_GET'], $index, $xss_clean);
      } else {
         return $this->_fetch_from_array($_GET, $index, $xss_clean);
      }
   }
   
   /**
    * Fetch an item from the POST array (use storage)
    *
    * @param string $index
    * @param bool $xss_clean
    * @return string
    */
   function storage_post($index = '', $xss_clean = false) {
      if (isset($this->storage['_POST'])) {
         return $this->_fetch_from_array($this->storage['_POST'], $index, $xss_clean);
      } else {
         return $this->_fetch_from_array($_POST, $index, $xss_clean);
      }
   }
   

   /**
    * Скорее всего этот хак можно удалить будет. 
    *
    * @access  public
    * @param   string
    * @param   bool
    * @return  string
    */
   function post_old($index = '', $xss_clean = FALSE)
   {
      
      /**
       * Dirty hack для datepicker.
       * Суть проблемы: у каждой локали есть свой формат даты.
       * Этот формат никак не совпадает с форматом даты в яваскрипте.
       * И ко всему прочему сокращённое название месяца для разных языков разное.
       * Поэтому для дэйтпикера формат даты текущей локали форматируется в простой формат: mm/dd/yyyy,
       * и соответсвенно из дэйтпикера приходит даты в таком формате.
       * Вот их и нужно скнонвертировать в формат текущей локали.
       * 
       * При каждом обращении к переменным post или get происходит их форматирование.
       * --
       * А можно при первом удачном обращении отформатировать from & to правильно и записать
       * их в POST, а datepicker_flag - убрать    
       * 
       * @author Semerenko
       */
      if (FALSE !== $this->_fetch_from_array($_POST, 'datepicker_flag', $xss_clean) 
              && in_array($index,array('from','to','birthday'))) {
       
         $d = $this->_fetch_from_array($_POST, $index, $xss_clean); // mm.dd.yyyy

         $date_parts = strptime($d, '%m.%d.%Y');
         $ts = mktime(0,0,0,$date_parts["tm_mon"]+1,$date_parts["tm_mday"],$date_parts["tm_year"]);
         $res = type_to_str($ts,'date');

         return $res;            
      }

      return $this->_fetch_from_array($_POST, $index, $xss_clean);
   }   
   
   /**
    * Fetch an item from the COOKIE array (use storage)
    *
    * @param string $index
    * @param bool $xss_clean
    * @return string
    */
   function storage_cookie($index = '', $xss_clean = false) {
      if (isset($this->storage['_COOKIE'])) {
         return $this->_fetch_from_array($this->storage['_COOKIE'], $index, $xss_clean);
      } else {
         return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
      }
   }
   
   /**
    * Fetch an item from the SERVER array (use storage)
    *
    * @param string $index
    * @param bool $xss_clean
    * @return string
    */
   function storage_server($index = '', $xss_clean = false) {
      if (isset($this->storage['_SERVER'])) {
         return $this->_fetch_from_array($this->storage['_SERVER'], $index, $xss_clean);
      } else {
         return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
      }
   }
   
}
