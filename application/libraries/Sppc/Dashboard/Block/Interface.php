<?php

/**
 * Интерфейс для работы с dashboard блоком
 *
 * @author Gennadiy Kozlenko
 */
interface Sppc_Dashboard_Block_Interface {
   
   /**
    * Получение контента блока
    *
    * @param Sppc_Dashboard_DateRange $range
    * @return string
    */
   public function getContent(Sppc_Dashboard_DateRange $range);

}
