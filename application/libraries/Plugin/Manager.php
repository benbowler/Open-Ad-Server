<?php
/**
 * Запускает нужный метод нужных плагинов
 *
 *  
 */
class Plugin_Manager {
   
   /**
    * This object instance
    *
    * @var Plugins_Manager
    */
   private static $_instance = null;   
   
   /**
    * Данные для всех плагинов
    *
    * @var Array
    */
   protected $data = array(
         'DEFAULT' => array(),
         'DISPLAY' => array());
   
   /**
    * Prefix for plugins settings in global_variables
    *
    * @var String
    */
   protected $_prefix = 'plugin';
   
   /**
    * Get singleton instance
    *
    * @return Sppc_Search_Settings
    */
   public static function getInstance() {
      if (is_null(self::$_instance)) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }
   
   /**
    * Сохраняются данные, которые нужны для выполнения плагина
    * и которые приходится собирать в разных частях кода.
    *
    */
   public function setData($values, $type = 'DEFAULT'){
      foreach($values as $key => $val){
         $this->data[$type][$key] = $val;
      }
   }
   
   /**
    * Получение сохранённых ранее значений 
    * непосредственно из самого плагина
    * 
    * @return String
    */
   public function getData($key,$type = 'DEFAULT'){
      return isset($this->data[$type][$key])?$this->data[$type][$key]:'';
   }
   
   /**
    * Запускаем для всех плагинов, связанных с контроллером,
    * 
    * @param String $controller
    * @param String $method
    * @param Function callback $func
    * 
    * @version 1.0.0
    */
   
   public function execute($controller, $method, $instance, $data = array(), $callback = 'add_content') {
     
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      $hookObjects = array();
      if (isset($pluginsConfig->$controller)) {
         foreach ($pluginsConfig->$controller as $hookClass) {
            $hookObject = new $hookClass();
            if ($hookObject instanceof Plugin_Abstract) {
               if(method_exists($hookObject,$method)){
                  
                  if($method == 'display' && !$this->enabled($hookObject->getName())){
                     $instance->$callback($hookObject->ifDisabled());
                  }else{ // главная ветка
                     $hookObject->processData($data);
                     $instance->$callback($hookObject->$method());
                  }
               }  
            }
         }
      }
      
   }
   
   /**
    * Check if plugin $name is defined for $controller
    *
    * @param String $controller
    * @param String $name
    * @return Boolean
    */
   public function exists($controller,$name){
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      return isset($pluginsConfig->$controller->$name);      
   }
   
   /**
    * Check if plugin is enabled 
    *
    * @param String $name
    * @return Boolean
    */
   public function enabled($name){
       if(get_instance()->global_variables->get($this->_prefix . $name . 'Enabled') == '0'){
          return false;       
       }
       return true;
   }
   
   /**
    * Проверяет наличие плагина
    *
    * @param String $name
    * @return TRUE or FALSE
    */
   public function isPlugin($name,$controller = 'parent_controller'){
      
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if(isset($pluginsConfig->$controller)){
         foreach($pluginsConfig->$controller as $key => $val){
            if($key == $name){
               return TRUE;
            }
         }     
      }
      return FALSE;
   }
}