<?php

require_once APPPATH . 'controllers/show.php';
/**
 * Контроллер обработки поиска по новой схеме
 *
 * @author Anton Potekhin
 */
class Display extends Show {
   
   /**
    * Конструктор класса
    *
    * @return Show
    */
   function __construct() {
      parent::Show();
   }
   
   /**
    * Метод по умолчанию
    *
    */
   function index() {
      $this->noCache();
      $this->load->library('Log');
      $this->load->model('global_variables');
      
      // Создаем объект поиска
      $this->load->library('search_builder');
      $this->search_builder->setSite($this->id_site);
      $this->search_builder->setChannel($this->id_channel);
      $channel = $this->search_builder->getChannelInfo();
      
      $this->log->write_log('sppc', 'id_site = ' . $this->id_site . ' id_channel = ' . $this->id_channel);
      /* Если у ченела установлено показывать внешний javascript (google adsens или что-то подобное)
      * при отсутствие рекламы. Тогда запускаем старую схему. 
      * В противном случае показываем сразу новый iframe
      */
      if ('google_adsense' == $channel->ad_settings) {
         parent::wrapper();
      } else {
         $data = array(
               'id_site' => $this->id_site, 
               'id_channel' => $this->id_channel, 
               'width' => $channel->width, 
               'height' => $channel->height, 
               'id_dimension' => $channel->id_dimension, 
               'id_publisher' => $channel->id_publisher, 
               'id' => 'iframe', 
               'ident' => $this->ident, 
               'referer' => urlencode($this->input->server('HTTP_REFERER')), 
               'base_url' => base_url());
         $view = 'show_ads/iframe_wrapper_ex.html';
         $this->output->set_header('Content-Type: text/javascript');
         $this->parser->parse($view, $data);
      }
   }
   
   /**
    * Отображение контента в iframe
    *
    */
   function content() {
      $content = '';
      $this->load->model('fraud_settings');
      $cache = $this->getCache('display_content', $this->fraud_settings->get('MaximumIntervalSearchClick'));
      $idPage = md5($this->referer.$this->input->server('HTTP_HOST').$this->input->server('REQUEST_URI'));
      $content = $cache->load($idPage);
      
      if (!$content) {
         $this->load->model('entity');
//         if ('active' != $this->entity->get_role_status($this->id_user, 'publisher')) {
         $roles = $this->entity->get_roles($this->id_user);
         if (!in_array2(array ('admin', 'publisher'), $roles)) {
            $view = 'show_ads/error.html';
            $data = array(
               'error' => __('Publisher has been blocked or deleted'), 
               'width' => $this->width - 2, 
               'height' => $this->height - 2);
            $content = $this->parser->parse($view, $data, true);
            print $content;
            return;
         }
         
         // Создаем объект поиска
         $this->load->library('search_builder');
         $this->search_builder->setSite($this->id_site);
         $this->search_builder->setChannel($this->id_channel);
         $this->search_builder->setReferer($this->referer);
         $this->search_builder->setRealReferer($this->real_referer);
         $this->search_builder->setSearchType('js');
         $this->search_builder->setUseFlash(0 != $this->use_flash ? true : false);
         // Запускаем процесс поиска
         $site = $this->search_builder->getSiteInfo();
         $channel = $this->search_builder->getChannelInfo();
         
         $results = array();
         if ($this->search_builder->search()) {
            // Получаем результаты
            $results = $this->search_builder->getResults();
         }
         
         // Получаем код ошибки
         $error = $this->search_builder->getLastError();
         
         // Выводим
         $next_page = true;
         $data = array();
         $view = '';
         if (null !== $channel && null !== $site) {
            if (0 < count($results)) {
               for ($i = 0, $j = count($results); $i < $j; $i++) {
                  if (isset($results[$i]['title'])) {
                     $results[$i]['title'] = type_to_str($results[$i]['title'], 'encode');
                  }
                  if (isset($results[$i]['description'])) {
                     $results[$i]['description'] = type_to_str($results[$i]['description'], 'encode');
                  }
                  if (isset($results[$i]['description2'])) {
                     $results[$i]['description2'] = type_to_str($results[$i]['description2'], 'encode');
                  }
                  if (isset($results[$i]['display_url'])) {
                     $results[$i]['display_url'] = type_to_str($results[$i]['display_url'], 'encode');
                  }
               }
               // Выводим объявления
               if (Search_builder::TYPE_TEXT == $this->search_builder->getAdType()) {
                  $this->prepareResults($results);
                  // Подгружаем данные по палитре
                  $palette = $this->getPalette();
                  $view = 'show_ads/' . $channel->width . 'x' . $channel->height . '.html';
                  $data = $palette;
                  $data['width'] = $channel->width - 2;
                  $data['height'] = $channel->height - 2;
                  $data['item_width'] = floor(($channel->width - 2) / $channel->max_ad_slots);
                  $data['item_height'] = floor(($channel->height - 2) / count($results));
                  $data['results'] = $results;
               } elseif (Search_builder::TYPE_IMAGE == $this->search_builder->getAdType()) {
                  $view = 'show_ads/image.html';
                  $data = current($results);
                  $data['width'] = $channel->width;
                  $data['height'] = $channel->height;
                  $data['results'] = $results;
               } elseif (Search_builder::TYPE_FLASH == $this->search_builder->getAdType()) {
                  $view = 'show_ads/flash.html';
                  $data = current($results);
                  $data['width'] = $channel->width;
                  $data['height'] = $channel->height;
                  $data['loader'] = 'loader_u_' . $channel->width . 'x' . $channel->height . '.swf';
                  $data['click_url'] = urlencode($data['click_url']);
                  $data['results'] = $results;
               }
               
            } elseif ('' == $error || 'protection_fail' == $error || 'targeting' == $error) {
               // Выводим то, что настроил владелец канала
               if ('blank' == $channel->ad_settings || 'blank_color' == $channel->ad_settings) {
                  $view = 'show_ads/blank_color.html';
                  $data['color'] = 'ffffff';
                  if ('blank_color' == $channel->ad_settings) {
                     $data['color'] = $channel->ad_settings_arg;
                  }
               }
            }
         }      
         
         $data['base_url'] = base_url();
         if (empty($view)) {
            // Получаем локаль
            $locale = $this->global_variables->get('Locale', $this->id_user);
            if (is_null($locale)) {
               $locale = $this->global_variables->get('DefaultLocale');
            }
            // Подгружаем языковые файлы
            $this->initTranslate($locale);
            
            $view = 'show_ads/error.html';
            $data = array(
                  'error' => $this->search_builder->getErrorMessage(), 
                  'width' => $this->width - 2, 
                  'height' => $this->height - 2);
         }
         $content = $this->parser->parse($view, $data, true);
         $cache->save($content, $idPage);
         
      }
      echo $content;
   }
   
   /**
    * Переопределение метода по умолчанию
    *
    */
   function _remap() {
      if (false !== $this->input->get('id')) {
         $this->content();
      } else {
         $this->index();
      }
   }

}
