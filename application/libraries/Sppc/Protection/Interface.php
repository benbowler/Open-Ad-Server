<?php

/**
 * Интерфейс валидатора протекшенов
 *
 */
interface Sppc_Protection_Interface {
   
   /**
    * Валидирование цепочек протекшенов
    *
    * @param mixed $value
    * @return bool
    */
   public function isValid($value = null);
   
   /**
    * Получение сообщений об ошибках
    *
    * @return array
    */
   public function getMessages();
   
}
