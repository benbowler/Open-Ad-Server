<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller
 *
 */
class MY_Controller extends CI_Controller {

   public $locale = '';
   
   public $user_id = NULL; // код пользователя
   
   /**
    * Constructor
    *
    * Calls the initialize() function
    */
   public function __construct() {
      parent::__construct();
      $this->load->helper('translate');
      $this->_init_zend_translation();
   }

   /**
    * определяет локаль пользователя, открывшего контроллер
    *
    * @return string код локали
    */
   protected function _get_locale() {
      // Первая попытка
      //      $zlocale = new Zend_Locale();
      //      $locale = $zlocale->toString();

      if (Plugin_Manager::isPlugin('locales')) {
         $this->load->helper('cookie');
         $locale = get_cookie(Plugin_Locales::$cookie_name);
         $exists = FALSE;
         // если есть куки
         if ('' != $locale) {
            $this->load->model('locales');
            if ($this->locales->isLocale($locale)) {
               $exists = TRUE;
            }
            // если нет куки
            // попробуем определить из переменных окружения
         } else {
            if (isset($_ENV['LANG'])) {
               if (preg_match('|[a-z]{2,4}_[a-z]{2,4}|i', $_ENV['LANG'], $matches)) {
                  $locale = $matches[0];
                  $this->load->model('locales');
                  if ($this->locales->isLocale($locale)) {
                     $exists = TRUE;
                  }
               }
            }
         }

         if ($exists) {
            $this->locale = $locale;
            return $locale;
         }
      }

      // Вторая попытка
      // Дефолтное значение из settings
      if ($this->locale == "") {
         $this->locale = $this->global_variables->get("Locale", $this->user_id);
         if (is_null($this->locale)) {
            $this->locale = $this->global_variables->get("DefaultLocale");
         }
      }

      if (!defined('CRON')) {
         // Init global locale
         try {
            Zend_Registry::set('Zend_Locale', new Zend_Locale($this->locale));
         } catch (Sppc_Exception $e) {
            // Something wrong with locale initialization:
         }
      }

      return $this->locale;
   } //end _get_locale

   /**
    * Zend_Translation init
    */
   protected function _init_zend_translation(){
      
      $locale = $this->_get_locale();
      
      $feEngine = $this->config->item('locales_frontend_engine');
      $feOptions = $this->config->item('locales_frontend');
      $beEngine = $this->config->item('locales_backend_engine');
      $beOptions = $this->config->item('locales_backend');
      if (isset($beOptions['cache_dir']) && !file_exists($beOptions['cache_dir'])) {
         mkdir($beOptions['cache_dir']);
         chmod($beOptions['cache_dir'], 0777);
      }
      $cache = Zend_Cache::factory($feEngine, $beEngine, $feOptions, $beOptions);
      Zend_Translate::setCache($cache);
    
      // end init cache
      $this->load->helper('translate');
      $translate = new Zend_Translate ( 'gettext', 
                      APPPATH . '/locale', 
                      $locale,
                      array ('scan' => Zend_Translate::LOCALE_DIRECTORY ) );
            
      //существует ли такая локаль
      if(!Zend_Locale::isLocale($this->locale, TRUE, FALSE)){
         $this->locale = $this->_default_locale;   
      }                            

      $translate->setLocale($this->locale);     
      Zend_Registry::set ( 'Zend_Translate' , $translate );
   
   }
   
}
