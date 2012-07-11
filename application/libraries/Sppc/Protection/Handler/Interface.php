<?php

/**
 * Интерфейс обработчика результатов
 *
 */
interface Sppc_Protection_Handler_Interface {
   
   /**
    * Обработка результатов
    *
    * @param array $results
    */
   public function processResults(&$results);
   
}
