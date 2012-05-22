<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* библиотека для получения HTML-кода закладок
* 
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Tabs {

   public $list;
   
   protected $ajax = '';
   
   protected $suffix = '';
   
   protected $prefix = '';
 
   /**
   * конструктор класса,
   * инициализация описания tabs по умолчанию
   *
   * @return ничего не возвращает
   */   
   public function Tabs() {
      $this->create('tabs');
   } //end Tabs

   /**
   * создание нового списка закладок
   *  
   * @param integer $id уникальный код набора закладок
   * @param string $tab_class класс блока DIV в котором помещаеются сами закладки
   * @param string $div_class класс блока DIV для содержимого закладок
   * @param string $sfc суффикс класса закладок
   * @return ничего не возвращает
   */   
   public function create($id, $tab_class = '', $div_class = '', $sfx = '',$prefix = '') {
      $this->suffix=$sfx;
      $this->prefix=$prefix;
      $this->list = array(
         'id' => $id,
         'selected' => '', 
         'tab_class' => $tab_class,
         'div_class' => $div_class,
         'tabs' => array());
   } //end create

   /**
   * установка AJAX-URL для вызова при щелчке по вкладке
   *
   * @param string $url URL, содержащий нужную AJAX-функцию
   * @return ничего не возвращает 
   */
   public function set_ajax($url) {
   	$this->ajax = $url;
   } //end set_ajax
   
   
   
   /**
   * добавление в список новой закладки
   *
   * @param integer $sid уникальный код закладки
   * @param string $name метка закладки
   * @param string $content содержимое закладки
   * @param bool $selected опциональный флаг выбранной закладки
   * @return ничего не возвращает
   */
   public function add($sid, $name, $content, $selected = FALSE) {
      $this->list['tabs'][$sid] = 
         array(
            'name' => $name,
            'content' => $content
         );
      if ($selected || $this->list['selected'] == '') {
         $this->list['selected'] = $name;
      }
   } //end add

   /**
   * задает вкладку по умолчанию
   *
   * @param string $name имя вкладки
   * @return ничего не возвращает
   */   
   public function select($name) {
      $this->list['selected'] = $name;    
   }   
   
   /**
   * получение HTML-кода закладок
   *
   * @return string HTML-код
   */   
   public function html() {
      $CI =& get_instance();
      //$template = $CI->load->view('common/tabs/template.html', '', TRUE);
      $selected = $CI->load->view('common/tabs/selected.html', '', TRUE);
      $tabs = '';
      $divs = '';
      $default = '';
      foreach ($this->list['tabs'] as $sid => $info) {
         $params = array(
            'HREF' => $CI->config->site_url($CI->uri->uri_string()) . "/tabs/$sid",
            'NAME' => __($info['name']),
            'SID' => $sid,
            'SELECTED' => '',
            'CLASS' => $this->list['div_class'],
            'CONTENT' => $info['content']
         );
         if ($this->list['selected'] == $info['name'] || $this->list['selected'] == $sid) {
            $default = $sid;
            $params['SELECTED'] = $selected;
         }
         $tabs .= $CI->parser->parse('common/tabs/tab.html', $params, TRUE);
      	$divs .= $CI->parser->parse('common/tabs/div.html', $params, TRUE);
      }
      $params = array(
         'CLASS' => $this->list['tab_class'],
         'ID' => $this->list['id'], 
         'TABS' => $tabs,
         'DIVS' => $divs,
         'DEFAULT' => $default,
         'URL' => $this->ajax,
         'SUFFIX' => $this->suffix,
         'PREFIX' => $this->prefix 
      );      
      return $CI->parser->parse('common/tabs/template.html', $params, TRUE);            
   } //end html
 
} //end class Tabs

?>