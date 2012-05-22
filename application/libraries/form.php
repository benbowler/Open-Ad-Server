<?php
if (!defined('BASEPATH'))
   exit('No direct script access allowed');

/**
 * Библиотека обработки структурированных форм
 * доступные методы:
 * show_form($mode, $form, $input, $caller)         работа с формой в различных режимах и вывод на экран результата
 * get_form_content($mode, $form, $input, $caller)  работа с формой в различных режимах и возвращение результата
 *
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Form {
   
   /**
    * конструктор класса
    *
    * @return ничего не возвращает
    */
   public function Form() {
      $CI = & get_instance();
      $CI->load->helper("fields");
   } //end Form
   

   /**
    * работа с формой и вывод на экран результатов работы,
    * для получения/сохранения данных для полей формы
    * вызывает либо методы модели данных
    *  - load($id), create($fields), save($id, $fields),
    * либо, если модель не задана, методы вызывавшего класа
    *  - _load($id), _create($fields), _save($id, $fields),
    *
    * @param string $mode режим работы с формой
    *                       "create" - создание новой записи данных
    *                       "modify" - изменение существующей записи данных
    *                       "save"   - проверка и сохранение записи данных
    *                       "filter" - форма фильтрации, изменения объектов нет
    * @param array $form описание формы и ее полей
    *  $form = array(
    *     "name"         => "formname",                имя формы в HTML коде
    *     "id"           => "id_record",               переданный код записи данных, "" - новая запись
    *     "model"        => "account",                 модель для работы с данными формы, при отсутствии - используются методы _load и _save
    *     "view"         => "form_test",               отображение формы
    *     "vars"         => array ("tag" => "value"),  (опционально) массив с переменными для отображения
    *     "redirect"     => "controller",              если задан, то при успехе вызывается контроллер, если не задан - success_view
    *     "success_view" => "form_test_success",       отображение при успешном изменении данных (если не задан redirect)
    *     "no_errors"    => "true",                    (опционально) не выводить текст ошибки под неправильными полями
    *     "action"       => 'http://site.com/page.php' (опционально) перебивает сгенерированный action на заданный
    *     "fields"       => array(                     описание полей формы
    *        "field_name" => array(                    имя поля
    *           "id_field_type"    => "string",        тип данных поля (string, int, money, procent, date, time, date_time, float, bool)
    *           "form_field_type"  => "textarea",      тип HTML поля (text, password, hidden, file, textarea, checkbox, radio, select)
    *           "validation_rules" => "required",      (опционально) правила проверки поля (required, matches, min_length, max_length, exact_length, alpha, alpha_numeric, alpha_dash, numeric, integer, valid_email, valid_ip, valid_base64)
    *           "options" => array(                    (опционально) либо модель списка, либо массив со значениями элементов списка
    *              "value" => "text",                  (только для массива) список значений и строк
    *              ...
    *           ),
    *           "params" => array(                     (только для модели, опционально) список параметров для извлечения списка моделью
    *              "name" => "value",                  (для параметров) список имен параметров и значений
    *              ...
    *           ),
    *           "default"          => "default",       (опционально) значение поля по умолчанию,
    *                                                  если NULL данные при чтении формы не обновляются
    *           "max"    => 32                         (опционально для text) максимальная длина вводимой строки
    *        ),
    *        ...
    *     ),
    *     "kill"         => array(                     список удаляемых блоков в HTML коде
    *        "id_div", ...                             параметр id блока div, который необходимо удалить
    *     )
    *  );
    * @param array $input массив с входными параметрами контроллера
    * @param object $caller объект контроллера, вызвавшего данную модель
    * @return ничего не возвращвет
    */
   public function show_form($mode, &$form, $input, $caller) {
      
      $id = array_key_exists("id", $form) ? $form["id"] : "";
      
      if ($input->post("form_mode")) {
         $old_mode = $mode;
         $mode = $input->post("form_mode");
      }
      $active_form = '';
      if ($input->post('active_form')) {
         $active_form = $input->post('active_form');
      }
      $CI = & get_instance();
      $CI->load->library("Form_Setter");
      $error = "";
      $field_errors = array();
      $fields = array();
      $rules = array();
      
      if ("save" == $mode && $active_form == $form['name']) {
         
         foreach ($form["fields"] as $fname => $field) {
            //$fields[$fname] = isset($fields[$fname]) ? $fields[$fname].', ' : '';
            if (array_key_exists('id_field_type', $form["fields"][$fname])) {
               
               $fields[$fname] = type_cast($input->post($fname), $form["fields"][$fname]["id_field_type"]);
            
            } else {
               $fields[$fname] = $input->post($fname);
            }
            
            $form["fields"][$fname]["default"] = $input->post($fname);
            
            /**
             * Всё те же dirty hacks для datepickera
             * @author Semerenko
             */
            /*         
            if(in_array($fname,array('from','to'))){
 
               $form['fields'][$fname]['default'] = date('m.d.Y', type_cast($form["fields"][$fname]["default"],'date'));
                        
            }*/
            
            if (array_key_exists("validation_rules", $field)) {
               $rules_arr = explode('|', $field["validation_rules"]);
               foreach ($rules_arr as &$rules_arr_val) {
                  switch ($rules_arr_val) {
                     case "date":
                     $rules_arr_val = "callback_check_date";
                     break;
                     
                     case "datetime":
                     $rules_arr_val = "callback_check_datetime";
                     break;
                     
                     case "hostname":
                     $rules_arr_val = "callback_check_hostname";
                     break;
                     
                     case "url":
                     $rules_arr_val = "callback_check_url";
                     break;
                     
                     case "float":
                     $rules_arr_val = "callback_check_float";
                     break;

                     case "positive":
                     $rules_arr_val = "callback_check_positive";
                     break;
                     
                     case "non_negative":
                     $rules_arr_val = "callback_check_non_negative";
                     break;
                     
                     case "int":
                     case "integer":
                     $rules_arr_val = "callback_check_integer";
                     break;
                     
                     case "ip":
                     $rules_arr_val = "callback_check_ip";
                     break;
                     
                     case "ip_part":
                     $rules_arr_val = "callback_check_ip_part";
                     break;
                     
                     case "ip_re":
                     $rules_arr_val = "callback_check_ip_re";
                     break;
                     
                     default:
                        if (0 === strpos($rules_arr_val, 'min_val') ||
                            0 === strpos($rules_arr_val, 'max_val') ||
                            0 === strpos($rules_arr_val, 'interval') ||
                            0 === strpos($rules_arr_val, 'float') ||
                            0 === strpos($rules_arr_val, 'full_url')) {
                           $rules_arr_val = "callback_check_" . $rules_arr_val;
                        }
                  }
                  $rules_arr_val = str_replace('md5', 'callback_check_password', $rules_arr_val);
               }
               $rules[$fname] = implode('|', $rules_arr);
            }
         }
         $CI->load->library('validation');
         $CI->validation->set_rules($rules);
         
         $int_validator = ((count($rules) == 0) || $CI->validation->run());
         $ext_validator = TRUE;
         
         if (method_exists($caller, '_validator')) {
            $ext_validator = $caller->_validator($fields, $int_validator);
         }
         
         if ($ext_validator && $int_validator) {
            $error = '';
            if ("save" == $mode) {
               if (array_key_exists("model", $form)) {
                  $CI->load->model($form["model"]);
                  if ("" == $id) {
                     $error = $CI->$form["model"]->create($fields);
                  } else {
                     $error = $CI->$form["model"]->save($id, $fields);
                  }
               } else {
                  if ("" == $id) {
                     $error = $caller->_create($fields);
                  } else {
                     $error = $caller->_save($id, $fields);
                  }
               }
            }
            
            if ("" == $error) {
               if (array_key_exists("redirect", $form)) {
                  
                  redirect($form["redirect"]);
               } else {
                  $CI->load->view($form["success_view"]);
                  return;
               }
            }
         } else {
            foreach ($form["fields"] as $fname => $field) {
               if (isset($CI->validation->{$fname . "_error"})) {
                  if ($CI->validation->{$fname . "_error"} != "") {
                     $field_errors[$fname] = $CI->validation->{$fname . "_error"};
                  }
               }
            }
            $error = "You must fill all required fields!"; //$CI->validation->error_string;
            // call form validation error callback:
            if (method_exists($caller, '_validationFailed')) {
               $caller->_validationFailed($fields, $field_errors);
            }
         }
      }
      
      if ("modify" == $mode || ('save' == $mode && $active_form != $form['name'] && $old_mode == "modify")) {
         
         if (array_key_exists("model", $form)) {
            $CI->load->model($form["model"]);
            $fields = $CI->$form["model"]->load($id);
         } else {
            
            $fields = $caller->_load($id);
         }
         
         foreach ($form["fields"] as $fname => $field) {
            
            if (array_key_exists($fname, $fields)) {
               if (array_key_exists("default", $form["fields"][$fname]) && is_null($form["fields"][$fname]["default"])) {
                  $form["fields"][$fname]["default"] = "";
               } else {
                  
                  $form["fields"][$fname]["default"] = type_to_str($fields[$fname], $form["fields"][$fname]["id_field_type"]);
               
               /**
                * Для дэйтпикера dirty hack
                * Если это поля to & from, то их не нужно форматировать
                * @author Semerenko
                */
               
               /*
                  if(in_array($fname,array('to','from')) && $form["fields"][$fname]["id_field_type"] == 'date'){
                     // ничего не делать. А всё форматирование пусть будет в else.
                  }else{
                     $form["fields"][$fname]["default"] =
                        type_to_str($fields[$fname], $form["fields"][$fname]["id_field_type"]);
                  }
                  */
               
               }
            }
         }
      
      }
      if (array_key_exists('vars', $form)) {
         $CI->form_setter->set_html($CI->parser->parse($form["view"], $form['vars'], TRUE));
      } else {
         $CI->form_setter->set_html($CI->load->view($form["view"], '', TRUE));
      }
      $CI->form_setter->use_form($form["name"]);
      if (isset($form['action'])) {
         $CI->form_setter->set_action($form['action']);
      } else {
         $CI->form_setter->set_action($CI->config->site_url($CI->uri->uri_string()));
      }
      
      foreach ($form["fields"] as $fname => $field) {
         
         switch ($field["form_field_type"]) {
            case "text":
            case "textarea":
            case "password":
            case "file":
            case "hidden":
            $text = "";
            if (array_key_exists("default", $field)) {
               $text = $field["default"];
            }
            $max = isset($field['max']) ? $field['max'] : NULL;
            $CI->form_setter->set_text($fname, $text, $max);
            break;
            case "checkbox":
            case "radio":
            if (array_key_exists("default", $field)) {
               $value = $field["default"];
               if ("" != $value) {
                  $CI->form_setter->set_state($fname, $value, TRUE);
               }
            }
            break;
            case "select":
            if (is_array($field["options"])) {
               $options = $field["options"];
            } else {
               $options_model = $field["options"];
               $CI->load->model($options_model, 'form_temp_model', TRUE);
               $params = array();
               if (array_key_exists("params", $field)) {
                  $params = $field["params"];
               }
               $options = $CI->form_temp_model->get_list($params);
               $CI->load->unload_model('form_temp_model');
            }
            $default = array_key_exists("default", $field) ? $field["default"] : NULL;
            $CI->form_setter->set_options($fname, $options, $default);
            break;
         }
      }
      
      if ("" != $id) {
         $CI->form_setter->add_hidden("id", $id);
      }
      if ($mode != "filter") {
         $CI->form_setter->add_hidden("form_mode", "save");
         $CI->form_setter->add_hidden("active_form", $form['name']);
      }
      $CI->form_setter->set_error(__($error));
      
      foreach ($field_errors as $fname => $ferror) {
         $matches = NULL;
         preg_match_all('/{@([\S]*?)@}/', $ferror, $matches);
         foreach ($matches[1] as $sfname) {
            if (array_key_exists($sfname, $form["fields"])) {
               if (array_key_exists("display_name", $form["fields"][$sfname])) {
                  $ferror = str_replace("{@$sfname@}", "'" . __($form["fields"][$sfname]["display_name"]) . "'", $ferror);
               }
               if (array_key_exists('no_errors', $form)) {
                  $ferror = '';
               }
            }
         }
         $CI->form_setter->set_field_error($fname, $ferror);
      }
      
      if (array_key_exists("kill", $form)) {
         $CI->form_setter->kill_blocks($form["kill"]);
      }
      
      $CI->output->set_output($CI->form_setter->get_html());
   
   } //end process
   

   /**
    * аналогично методу process, только данные не выводятся на экран,
    * а возвращаются в строке
    *
    * @param string $mode режим работы с формой (см. описание в методе process)
    * @param array $form описание формы и ее полей (см. описание в методе process)
    * @param array $input массив с входными параметрами контроллера
    * @param object $caller объект контроллера, вызвавшего данную модель
    * @return ничего не возвращвет
    */
   public function get_form_content($mode, &$form, $input, $caller) {
      $CI = & get_instance();
      $cur_output = $CI->output->get_output();
      $CI->output->set_output("");
      $this->show_form($mode, $form, $input, $caller);
      $form_content = $CI->output->get_output();
      $CI->output->set_output($cur_output);
      return $form_content;
   }

} //end Form


?>