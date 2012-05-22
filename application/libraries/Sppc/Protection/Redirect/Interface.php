<?php

/**
 * Интерфейс редиректа
 *
 */
interface Sppc_Protection_Redirect_Interface {
   
   /**
    * Проверка на нужность редиректа
    *
    * @return bool
    */
   public function isRedirect();
   
   /**
    * Получение урла для редиректа
    *
    * @return string
    */
   public function getRedirectUrl();
   
}
