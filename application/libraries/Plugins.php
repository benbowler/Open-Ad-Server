<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* библиотека для работы с плагинами
* 
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Plugins {

   protected $_hooks = array();
   protected $default_alias = NULL;

   /**
   * конструктор - инициализация набора классов
   *
   * @return ничего не возвращает
   */   
   public function Plugins($params) {
   	$path = implode('->', $params['path']);
   	if (isset($params['interface'])) {
   	   $interface = $params['interface'];	
   	} else {
   		$interface = 'Sppc_'.implode('_', $params['path']).'_Interface';
   	}   	
   	$alias = (isset($params['alias']))?$params['alias']:$interface;   	
   	if (is_null($this->default_alias)) {
   		$this->default_alias = $alias;
   	}
   	
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');

      $isset = eval('return isset($pluginsConfig->'.$path.');');            

      if ($isset) {
   		$hook_classes = eval('return $pluginsConfig->'.$path.';');
   		
         foreach ($hook_classes as $hookClass) {

               $hookObject = new $hookClass;       
    
               if ($hookObject instanceof $interface) {
                  $this->_hooks[$alias][] = $hookObject;
               }

         }
      }

      
   } //end Hooks

   /**
    * Вызываем заданный метод для всех классов
    *
    * @param string $method вызываемый метод класса
    * @param misc $params набор аргументов метода
    */
   public function run($method, $params, $alias = NULL) {
   	$result = array();
   	if (is_null($alias)) {
   		$alias = $this->default_alias;
   	}
   	if (isset($this->_hooks[$alias])) {
	      foreach($this->_hooks[$alias] as $hook) {
	         
	      	if (method_exists($hook, $method)) {
	            $result[] = $hook->$method($params);
	      	}
	      }
   	}
      return $result;
   } //end run
   
    
} //end class Hooks

?>