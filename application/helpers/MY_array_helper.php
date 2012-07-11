<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('in_array2')) {
   /**
   * возвращает региональный формат для заданного типа данных
   *
   * @param string $type имя типа данных
   * @return string строка формата
   */ 
   function in_array2($needle, $haystack, $strict = false) {
      if (is_array($needle)) {
         foreach ($needle as $item) {
            if (in_array($item, $haystack, $strict)) {
               return true;
            }
         }
         return false;
      }
      return in_array($needle, $haystack, $strict);
   }
}
