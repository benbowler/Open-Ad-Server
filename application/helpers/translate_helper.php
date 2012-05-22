<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для перевода
 *
 * @author Semerenko
 */

/**
 * Перевод
 *
 * @param string $value
 */
function __($value,$locale=null) {
   
//   if(get_instance()->config->item('translate_way') == 'new'){
   
      $translate = Zend_Registry::get ( 'Zend_Translate' );
      
      if(!is_null($locale)){
         $translate->setLocale($locale);
      }

      return $translate->translate($value);
/*      
   }else{
      
      return _($value);
      
   }
/**/      

}
