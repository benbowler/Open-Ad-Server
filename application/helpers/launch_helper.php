<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Помощник для запуска приложений
 * 
 * @author Gennadiy Kozlenko
 */

/**
 * Запуск команды и ожидание ответа
 *
 * @param string $cmd
 * @return mixed
 */
function launch_and_wait($cmd) {
   $content = '';
   if (false !== ($fh = popen($cmd, 'r'))) {
      $content = stream_get_contents($fh);
      pclose($fh);
   } else {
      return false;
   }
   return $content;
} // end launch_and_wait

/**
 * Запуск команды без ожидания завершения
 *
 * @param string $cmd
 * @return boolean
 */
function launch($cmd) {
   if (!preg_match('~&\s*$~', $cmd)) {
      $cmd .= ' &';
   }
   if (false !== ($fh = popen($cmd, 'w'))) {
      fwrite($fh, "\n");
      pclose($fh);
   } else {
      return false;
   }
   return true;
} // end launch

/**
 * Запуск команды, передача запроса и ожидание ответа
 *
 * @param string $cmd
 * @param string $request
 * @return mixed
 */
function launch_and_send($cmd, $request) {
   $content = '';
   $descriptorspec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w')
   );
   $pipes = array();
   $process = proc_open($cmd, $descriptorspec, $pipes);
   if (is_resource($process)) {
      fwrite($pipes[0], $request);
      fclose($pipes[0]);
      $content = stream_get_contents($pipes[1]);
      proc_close($process);
   }
   return $content;
} // end launch_and_send
