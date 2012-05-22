<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_system'][0] = array(
  'class'    => '',
  'function' => 'set_debug_mode',
  'filename' => 'debug.php',
  'filepath' => 'hooks'
);

$hook['pre_system'][1] = array(
  'class'    => '',
  'function' => 'init_zend',
  'filename' => 'zend.php',
  'filepath' => 'hooks'
);

$hook['pre_system'][2] = array(
  'class'    => '',
  'function' => 'init_zend_db',
  'filename' => 'zend.php',
  'filepath' => 'hooks'
);

$hook['pre_system'][3] = array(
  'class'    => '',
  'function' => 'start_zend_profiler',
  'filename' => 'zend.php',
  'filepath' => 'hooks'
);

$hook['pre_system'][4] = array(
  'class'    => '',
  'function' => 'init_plugins',
  'filename' => 'init_plugins.php',
  'filepath' => 'hooks'
);

$hook['post_system'][0] = array(
  'class'    => '',
  'function' => 'stop_zend_profiler',
  'filename' => 'zend.php',
  'filepath' => 'hooks'
);


/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */