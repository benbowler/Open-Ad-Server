<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Хелпер для определения максимально выгодного для оплаты набора сайтов-каналов
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

if (!function_exists('knapsack')) {
   /**
    * Поиск программ для максимальной растраты денег
    * @author Gennadiy Kozlenko
    * 
    * @param array $prgs массив возможных программ для анализа
    * @param float $balance баланс адверта
    * @return array массив айдишников программ
    */
   function knapsack(&$prgs, $balance) {
      $t = array(0 => 0);
      $solutions = array(0 => array());
      //print_r($prgs);
      foreach ($prgs as $i => $program) {
         $t_old = $t;
         foreach (array_keys($t_old) as $x) {
            if (isset($program['cost']) && $x + $program['cost'] <= $balance) {
               $key = (string) ($x + $program['cost']);
               if (!isset($t[$key]) || ($t[$key] < $t_old[$x] + 1)) {
                  $t[$key] = $t_old[$x] + 1;
                  $solutions[$key] = array_merge($solutions[$x], array($i));
               }
            }
         }
      }
      return $solutions[max(array_keys($t))];
   }
}
?>