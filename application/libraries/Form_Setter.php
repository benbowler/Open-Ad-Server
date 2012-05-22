<?php // -*- coding: UTF-8 -*-

if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

/**
* библиотека для установки значений формы
* доступные методы:
* set_html($content)                      загрузка HTML кода
* use_form($name)                         выбор формы
* set_action($action)                     установка параметра ACTION формы
* set_state($name, $value, $state)        установка полей CHECKBOX или RADIO
* set_text($name, $value)                 установка полей TEXT,FILE,PASSWORD,HIDDEN или TEXTAREA
* set_options($name, $options, $default)  установка поля SELECT
* add_hidden($name, $value)               добавление дополнительного поля типа HIDDEN
* set_error($value)                       установка текста ошибки для всей формы
* set_field_error($name, $value)          установка текста ошибки для конкретного поля
* kill_blocks($div_list)                  удаляет заданные блоки HTML-кода
* get_html()                              получение модифицированного HTML кода
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Form_Setter {
 
   protected $content;  // HTML код с формами   
   protected $form;     // HTML код выбранной формы   
   protected $name;     // имя выбранной формы

   /**
   * возвращает HTML код именованного блока, ограниченный парными тегами
   * 
   * @param string $text HTML код, в котором ищется нужный блок
   * @param string $tag значение парного тега, ограничивающего блок кода
   * @param string $name имя блока ограниченного парными тегами
   * @return string HTML код, или пустая строка если такой блок не найден
   */   
   protected function extract_tags($text, $tag, $name) {
      if (preg_match("/\<{$tag}[^>]*(?:name|id)=[\"']{$name}[\"'][\s\S]*?\/$tag\>/", $text, $matches)) {
         return $matches[0];
      } else {
         return ""; 
      }
   } //end extract_tags

   /**
   * меняет HTML код именованного блока, ограниченный парными тегами
   *
   * @param string $text HTML код, в котором заменяется блок
   * @param string $tag значение парного тега, ограничивающего блок кода
   * @param string $name имя блока ограниченного парными тегами
   * @param string $value HTML код, который заменит код блока
   * @return string HTML код, с замененным блоком
   */
   protected function set_tags($text, $tag, $name, $value) {
      $value = str_replace('$', '\\$', $value);
      return preg_replace("/\<{$tag}[^>]*(?:name|id)=[\"']{$name}[\"'][\s\S]*?\/$tag\>/", $value, $text); 
   } //end set_tags

   /**
   * ищет в HTML коде формы блок, ограниченный тегами INPUT с заданными NAME (и VALUE) 
   *  
   * @param string $name значение параметра NAME тега INPUT
   * @param string $value значение параметра VALUE тега INPUT (опционально) 
   * @return string HTML код заданного блока, или пустая строка если не найдено
   */
   protected function extract_input($name, $value = "") {
      if($value == "") {
         $res = preg_match("/\<input[^>]*?name=[\"']{$name}[\"'][^<>]*?[\/]?\>/", $this->form, $matches);
      } else {
         $res = preg_match("/\<input[^>]*?name=[\"']{$name}[\"'][^<>]*?value=[\"']{$value}[\"'][^<>]*?[\/]?\>/", $this->form, $matches);       
      }
      if ($res) {
         return $matches[0];
      } else {
         return ""; 
      }
   } //end extract_input

   /**
   * заменяет в HTML коде формы блок, ограниченный тегами INPUT с заданными NAME (и VALUE)
   *
   * @param string $text HTML код, которым заменится содержимое блока
   * @param string $name значение параметра NAME тега INPUT
   * @param string $value значение параметра VALUE тега INPUT (опционально)
   * @return ничего не возвращает
   */
   protected function set_input($text, $name, $value = "") {
      $text = str_replace('$', '\\$', $text);         
   	if ("" == $value) {
         $this->form = preg_replace("/\<input[^>]*name=[\"']{$name}[\"'][^>]*[\/]?\>/", $text, $this->form);
      } else {
         $this->form = preg_replace("/\<input[^>]*name=[\"']{$name}[\"'][^>]*value=[\"']{$value}[\"'][^>]*[\/]?\>/", $text, $this->form);
      }
   } //end set_input
   
   /**
   * устанавливает новое значение для заданного параметра тега HTML кода,
   * если такой параметр отсутствует - добавляет его
   *
   * @param string $text HTML код тега, в котором будет устанавливаться параметр
   * @param string $name имя парметра тега
   * @param string $value новое значение параметра тега
   * @return string HTML код тега с установленным параметром
   */
   protected function set_param($text, $name, $value) {
   	$value = str_replace('$', '\\$', $value);
      $text = preg_replace("/ $name=[\"'][^\"']*[\"']/", "", $text);
      //return str_replace(">", " $name=\"$value\">", $text);
/*      $pos = strpos($text, '/>');
      if (!$pos) {
         $pos = strpos($text, '>');
      }
      return substr_replace($text, " $name=\"".htmlentities($value,ENT_COMPAT,"utf-8")."\"", $pos, 0);*/                  

      return preg_replace('/[\s]*(\/?[\s]*>)/', " $name=\"".htmlentities($value,ENT_COMPAT,"utf-8").'"$1', $text, 1);      
   } //end set_param

   /**
   * возвращает по имени параметр тега HTML-кода
   *
   * @param string $text HTML код тега, в котором будет устанавливаться параметр
   * @param string $name имя парметра тега
   * @return string значение параметра тега HTML-кода
   */
   protected function get_param($text, $name) {
      $text = preg_match("/ $name=[\"']([^\"']*)[\"']/", $text, $matches);
      if (isset($matches[1])) {
         return $matches[1];
      } else {
         return "";
      }
   } //end get_param   
   
   /**
   * заменяет блок HTML кода, расположенный между парными тегами
   *
   * @param string $text HTML код с парными тегами, между которыми будет меняться текст
   * @param string $value HTML код, который будет вставляться между тегами
   * @return string HTML код с замененным блоком между парными тегами
   */
   protected function set_between_tags($text, $value) {
   	$value = str_replace('$', '\\$', $value);
      $value = preg_replace("/(?<=\>)[\s\S]*(?=\<)/", $value, $text);
      return $value;
   } //end set_between_tags

   /**
   * добавляет или удаляет параметр CHECKED в HTML код тега INPUT 
   *
   * @param string $text HTML код тега INPUT
   * @param boolean $flag значение параметра CHECKED
   * @return string обновленный HTML код тега INPUT
   */
   protected function set_checked($text, $flag) {
      $text = preg_replace("/[\s]*checked=\"checked\"/", "", $text);
      if ($flag) {
         return preg_replace('/[\s]*\/?[\s]*>/', " checked=\"checked\"/>", $text); 
         //str_replace(">", " checked>", $text);
      } else {
         return $text;
      }
   } //end set_between_tags

   /**
   * заменяет параметр ACTION формы на переданное значение
   *
   * @param string $action новое значение параметра ACTION формы
   * @return ничего не возвращает
   */
   public function set_action($action) {
      $res = preg_match("/\<form[^>]*\>/", $this->form, $matches);
      if ($res == 0) return;
      $text = preg_replace("/ action=\"[^\"]*\"/", "",$matches[0]);
      $text = str_replace(">", " action=\"$action\">", $text);
      $this->form = preg_replace("/\<form[^>]*\>/", $text, $this->form);            
   }
   
   /**
   * устанавливает HTML код, в котором содержится нужная нам форма
   *
   * @param string $content HTML код с формой
   * @return ничего не возвращает
   */
   public function set_html($content) {    
      $this->content = $content;
   } //end set_html
   
   /**
   * возвращает HTML код с обновленной формой
   *
   * @return string подготовленный HTML код
   */
   public function get_html() {
      return $this->set_tags($this->content, "form", $this->name, $this->form);
   } //end get_html
   
   /**
   * выбирает форму с заданным именем в HTML коде
   *
   * @param string $name имя нужной нам формы
   * @return ничего не возвращает
   */
   public function use_form($name) {
      $this->name = $name;
      $this->form = $this->extract_tags($this->content, "form", $this->name);
   } //end use_form

   /**
   * устанавливает параметр CHECKED для заданного поля INPUT типа CHECKBOX или RADIO   
   *
   * @param string $name параметр NAME поля INPUT
   * @param string $value параметр VALUE поля INPUT
   * @param boolean $state новое значение параметра CHECKED
   * @return boolean TRUE если успех, FALSE если такое поле не найдено
   */
   public function set_state($name, $value, $state) {

      $input = $this->extract_input($name, $value);
      
      if ("" == $input) {
         return FALSE;
      }
      $input = $this->set_checked($input,$state);
      $this->set_input($input, $name, $value);
      return TRUE;
   } //end set_state

   /**
   * устанавливает значение по умолчанию для заданного поля формы
   * типов INPUT (TEXT, PASSWORD, FILE, HIDDEN) или TEXTAREA
   *
   * @param string $name имя нужного поля формы
   * @param string $value новое значение поля формы
   * @return boolean TRUE если успех, FALSE если такое поле не найдено
   */
   public function set_text($name, $value, $max = NULL) {
   	if (!is_null($max)) {
   	  $limitation_string = '<span class="limitation">' . sprintf(__('Max %s characters'), $max) . '</span>';
   	}
      
   	$input = $this->extract_input($name);
      if ("" == $input) {
         $input = $this->extract_tags($this->form, "textarea", $name);
         if ("" == $input) {
            return FALSE;
         }
         $input = $this->set_between_tags($input, htmlentities($value,ENT_COMPAT,"utf-8"));
         if (!is_null($max)) {
            $input = $this->set_param($input, 'maxlength', $max);
         	//$input = $this->set_param($input, 'onkeyup', "limitTextareaChars(this, $max);");
            $input .= $limitation_string;
         }
         $this->form = $this->set_tags($this->form, "textarea", $name, $input);
         return TRUE;      
      }
      $input = $this->set_param($input, "value", $value);
      if (!is_null($max)) {
         $input = $this->set_param($input, 'maxlength', $max);
         //$input = $this->set_param($input, 'onkeyup', "limitInputChars(this, $max);");
         $input .= $limitation_string;
      }
      $this->set_input($input, $name);
      return TRUE;
   } //end set_text

   /**
   * устанавливает список возможных значений для заданного поля SELECT формы,
   * и устанавливае значение выбранное по умолчанию
   *
   * @param string $name имя заданного поля SELECT формы 
   * @param array $options массив со значением VALUE и текстом для каждого пункта списка
   * @param string $default значение VALUE, выбранное по умолчанию
   * @return boolean TRUE если успех, FALSE если такое поле не найдено
   */
   public function set_options($name, $options, $default) {
      
      

      $input = $this->extract_tags($this->form, "select", $name);
      $text = "";
      foreach ($options as $value => $option) {

         if (is_array($option)) {
         	$group_label = str_replace("{@" . $value . "@}", __($value), $value);
            $text .= '<optgroup label="'.$group_label.'">';
            foreach ($option as $sub_value => $sub_option) {
               $disabled = is_null($value) ? ' disabled' : '';
               if ($sub_value == $default) {
                  $selected = " selected";
               } else {
                  $selected = "";          
               }
               preg_match_all("/{@([\s\S]*?)@}/", $sub_option, $matches); //Перевод текста элементов списка перед конвертацией текста в HTML
               foreach ($matches[1] as $message) {
                  $option = str_replace("{@" . $message . "@}", __($message), $sub_option);
	            } 
	               $text .= "<option$disabled$selected value=\"$sub_value\">".htmlentities($sub_option,ENT_COMPAT,'UTF-8')."</option>\n";
	            }
            $text .= '<optgroup>';
         } else {
            $disabled = $value === 'html_disabled' ? ' disabled="disabled"' : '';
            if ($value == $default && !($value === 0 && $default === '')) {
               $selected = " selected";
            } else {
               $selected = "";          
            }
            preg_match_all("/{@([\s\S]*?)@}/", $option, $matches); //Перевод текста элементов списка перед конвертацией текста в HTML
            foreach ($matches[1] as $message) {
               $option = str_replace("{@" . $message . "@}", __($message), $option);
            } 
         	$text .= "<option$disabled$selected value=\"$value\">".htmlentities($option,ENT_COMPAT,'UTF-8')."</option>\n";
         }
      }      
      
      $input = $this->set_between_tags($input, $text);
     
      $this->form = $this->set_tags($this->form, "select", $name, $input);      
   } //end set_options
   
   /**
   * устанавливает код HTML для блока DIV с id="error_message_div",
   * если передана пустая строка, то весь блок DIV удаляется
   *
   * @param string $value содержимое для блока DIV
   * @return ничего не возвращает
   */
   public function set_error($text) {
      $input = $this->extract_tags($this->form, "div", "error_message_div");
      if ("" == $text) {
         $input = "";
      } else {
         $input = $this->set_between_tags($input, $text);
      }
      $this->form = $this->set_tags($this->form, "div", "error_message_div", $input);      
   } //end set_error

   /**
   * удаляет блоки HTML-кода, заключенные между парными тегами DIV с заданными ID
   *
   * @param  array $div_list параметр ID заданного блока DIV
   * @return ничего не возвращает
   */
   public function kill_blocks($div_list){
      foreach ($div_list as $id) {
         $this->form = preg_replace("/\<a name=[\"']{$id}_begin[\"']\>\<\/a\>[\s\S]*?\<a name=[\"']{$id}_end[\"']\>\<\/a\>/", '', $this->form);
         
         //$this->form = $this->set_tags($this->form, "div", $id_div, "");      
      }      
   } //end kill_blocks
      
   /**
   * добавляет в форму скрытое поле с нужным именем и значением
   *
   * @param string $name имя поля
   * @param string $value значение поля
   * @return ничего не возвращает
   */
   public function add_hidden($name, $value) {
      $this->form = str_replace("</form>",
         "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n</form>",
         $this->form);
   } //end add_hidden

   /**
   * помечает теги поля классом invalidfield
   *
   * @param string $input HTML-код поля формы
   * @param string $value текст сообщения об ошибке
   * @return string измененный HTML-код поля формы
   */
   protected function set_invalid_class($input, $value) {
      $class = $this->get_param($input, "class");
      if ($class == "") {
         $class = "invalidfield";
      } else {
         $class .= " invalidfield";
      }
      $input = $this->set_param($input, "class", $class);
      return $input . $value;
   }   

   /**
   * добавляет сообщение об ошибке после поля с неправильными данными
   *
   * @param string $name имя поля
   * @param string $value сообщение об ошибке
   * @return bool FALSE, если поля с заданным именем не найдено
   */
   public function set_field_error($name, $value){
      $input = $this->extract_input($name);      
      if ("" == $input) {
         $input = $this->extract_tags($this->form, "textarea", $name);
         if ("" == $input) {
            $input = $this->extract_tags($this->form, "select", $name);
            if ("" == $input) {
               return FALSE;
            }
            $input = $this->set_invalid_class($input, $value);
            $this->form = $this->set_tags($this->form, "select", $name, $input);
            return TRUE;
         }
         $input = $this->set_invalid_class($input, $value);
         $this->form = $this->set_tags($this->form, "textarea", $name, $input);
         return TRUE;      
      }
      $input = $this->set_invalid_class($input, $value);
      $this->set_input($input, $name);
      return TRUE;
   } //end set_field_error
   
} //end Form_Setter

?>