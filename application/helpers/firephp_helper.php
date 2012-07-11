<?php

/**
 * Получение объекта FirePHP
 * 
 * @return FirePHP
 */
function get_firephp() {
   require_once APPPATH . 'libraries/FirePHPCore/FirePHP.class.php';
   $firephp=FirePHP::getInstance(true);
   if (DEBUG_MODE) {
   	$firephp->setEnabled(true);
   } else {
   	$firephp->setEnabled(false);
   }
   return $firephp;
}