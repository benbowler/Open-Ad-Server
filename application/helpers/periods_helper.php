<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Хелпер для задания периодов времени 
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/

if ( ! function_exists('get_period')) {   
   /**
    * Определение времени начала и конца периода по его коду
    *
    * @param string $type - код периода (today, yesterday, lastweek, lastbusinessweek, thismonth, lastmonth, alltime)
    * @param bool $week_start_monday - флаг, указывающий считать понедельник началом недели
    * @return array - массив, содержащий дату начала и конца периода в формате UNIXTIMESTAMP 
    */
   function get_period($type, $week_start_monday = true) {
      $period = array('start' => 0, 'end' => 0);
      switch ($type) {
      	case 'today': //от начала суток до настоящего момента времени
      	   $period['start'] = mktime(0, 0, 0, date("m"), date("d"), date("Y")); 
      	   $period['end'] = mktime();
      	   break;
      	case 'yesterday': //за весь вчерашний день
            $period['start'] = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")); 
            $period['end'] = mktime(0, 0, -1, date("m"), date("d"), date("Y"));
            break;
         case 'lastweek': //за последнюю полную неделю
            $firstWeekDay = period_first_day_of_the_week();
            if ($firstWeekDay == 2) {
               $period['start'] = mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y"));
               $period['end'] = mktime(0, 0, -1, date("m"), date("d") - date("w") + 1, date("Y"));
            } else {
               $period['start'] = mktime(0, 0, 0, date("m"), date("d") - date("w") - 7, date("Y"));
               $period['end'] = mktime(0, 0, -1, date("m"), date("d") - date("w"), date("Y")); 
            }
            break;
         case 'lastbusinessweek': //за последнюю полную рабочую неделю 
            $period['start'] = mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y"));
            $period['end'] = mktime(0, 0, -1, date("m"), date("d") - date("w") - 1, date("Y"));
            break;
         case 'thismonth': //от начала текущего месяца до настоящего момента времени 
            $period['start'] = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $period['end'] = mktime();
            break;
         case 'lastmonth': //за весь предыдущий месяц
            $period['start'] = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
            $period['end'] = mktime(0, 0, -1, date("m"), 1, date("Y"));
            break;
         default: //от начала Unix-эпохи до настоящего момента времени 
            $period['start'] = mktime(0, 0, 0, 1, 1, 2008);
            $period['end'] = mktime();
            break;
      }      
      return $period;    
   }
}

if (!function_exists('period_name')) {   
   /**
   * возвращает имя периода
   *
   * @param string $period код периода
   * @return string имя периода
   */
   function period_name($period) {

   	static $names = NULL;
   	if (is_null($names)) {
   	   $names =	array(
            'today' => __('Today'), 
            'yesterday' => __('Yesterday'),
            'lastweek' => '',
            'lastbusinessweek' => __('Last business week (Mon-Fri)'),
            'thismonth' => __('This month'),
            'lastmonth' => __('Last month'),
            'alltime' => __('All time')
      	);
 	
	      $firstWeekDay = period_first_day_of_the_week();
         if ($firstWeekDay == 2) {
            $names['lastweek'] = __('Last week (Mon-Sun)');
         } else {
            $names['lastweek'] = __('Last week (Sun-Sat)');
         }
         
   	}
   	return $names[$period];      
   } //end period_name   
}

if ( ! function_exists('get_filter_fields'))
{
   /**
   * получение массива полей фильтра дат для класса forms
   *
   * @param string $prefix префикс для имени полей календаря
   * @param array $defaults значения по-умолчанию для полей формы $defaults = array('predefined_period','period_mode','user_defined_start','user_defined_end')
   * @param bool $week_start_monday флаг, указывающий считать понедельник началом недели
   * @return array массив, содержащий поля календаря в формате, понятном классу forms 
   */
   function get_filter_fields($prefix = '', $defaults = array(), $week_start_monday = true) {
      
      $calendar_fields[$prefix.'_predefined_period'] = array(
               "id_field_type"    => "string",
               "form_field_type"  => "select",
               "options" => array(
                 "today" => "{@Today@}", 
                 "yesterday" => "{@Yesterday@}",
                 "lastweek" => "",
                 "lastbusinessweek" => "{@Last business week (Mon-Fri)@}",
                 "thismonth" => "{@This month@}",
                 "lastmonth" => "{@Last month@}",
                 "alltime" => "{@All time@}"
                 ),
                "default" => array_key_exists('predefined_period', $defaults) ? $defaults['predefined_period'] : "today" 
               );
     
     $firstWeekDay = period_first_day_of_the_week();
                    
     if ($firstWeekDay == 2) {
        $calendar_fields[$prefix.'_predefined_period']['options']['lastweek'] = "{@Last week (Mon-Sun)@}";
     } else {
        $calendar_fields[$prefix.'_predefined_period']['options']['lastweek'] = "{@Last week (Sun-Sat)@}";
     }
     
     $calendar_fields[$prefix.'_period_mode'] = array(
               "id_field_type"    => "string",
               "form_field_type"  => "radio",
               "default" => array_key_exists('period_mode', $defaults) ? $defaults['period_mode'] : "predefined"
            );

     $calendar_fields[$prefix.'_user_defined_period_start'] = array(
               "id_field_type"    => "date",
               "form_field_type"  => "text",
               "default" => array_key_exists('user_defined_start', $defaults) ? $defaults['user_defined_start'] : "",
               "validation_rules" => "date"
            );
     $calendar_fields[$prefix.'_user_defined_period_end'] = array(
               "id_field_type"    => "date",
               "form_field_type"  => "text",
               "default" => array_key_exists('user_defined_end', $defaults) ? $defaults['user_defined_end'] : "",
               "validation_rules" => "date"
            );
     return $calendar_fields; 
   }
}

if ( ! function_exists('data_range_fields'))
{
   /**
   * добавление в форму полей фильтра периода дат 
   *
   * @param array &$form массив описания формы
   * @param mixed $from для предопределенного периода - 'select', иначе дата начала периода
   * @param mixed $to для предопределенного периода - выбранный option, иначе дата конца периода
   * @return ничего не возвращает 
   */
   function data_range_fields(&$form, $from, $to) {

      
      $CI =& get_instance();
      $form['fields']['period'] = array(
         "id_field_type"    => "string",
         "form_field_type"  => "select",
         "options" => array(
            "today" => __("Today"), 
            "yesterday" => __("Yesterday"),
            "lastweek" => "",
            "lastbusinessweek" => __("Last business week (Mon-Fri)"),
            "thismonth" => __("This month"),
            "lastmonth" => __("Last month"),
            "alltime" => __("All time")
         )
      );
      
      $firstWeekDay = period_first_day_of_the_week();

      if ($firstWeekDay == 2) {
         $form['fields']['period']['options']['lastweek'] = __("Last week (Mon-Sun)");
      } else {
         $form['fields']['period']['options']['lastweek'] = __("Last week (Sun-Sat)");
      }     
      $form['fields']['mode'] = 
         array(
            "id_field_type"    => "string",
            "form_field_type"  => "radio"
         );
      $form['fields']['from'] = 
         array(
            "id_field_type"    => "date",
            "form_field_type"  => "text",
            "validation_rules" => "date"
         );
      $form['fields']['to'] = 
         array(
            "id_field_type"    => "date",
            "form_field_type"  => "text",
            "validation_rules" => "date"
         );
      if ($from == 'select') {
         $form['fields']['mode']['default'] = 'select';
         $form['fields']['period']['default'] = $to;
      } else {

         $form['fields']['mode']['default'] = 'range';       
         
         $form['fields']['from']['default'] = type_to_str($from, 'date'); 
         $form['fields']['to']['default'] = type_to_str($to, 'date');
         
        
         /**
          * Нужно передать в дэйтпикер дату в формате mm.dd.yyyy
          * 
          * @author Semerenko
          */
         /*
         $form['fields']['from']['default'] = date('m.d.Y', $from);
         $form['fields']['to']['default'] = date('m.d.Y', $to);
         */
         
      }
      

   }
   
}

if ( ! function_exists('data_range')) {   
   /**
    * Определение даты начала и конца периода по полям формы 
    *
    * @param array $fields массив со значениями полей формы
    * @return array - массив, содержащий дату начала и конца периода в формате UNIXTIMESTAMP 
    */
   function data_range($fields) {
      
      if (is_null($fields)) {
         return array(
            'from' => mktime(0, 0, 0, date("m"), date("d"), date("Y")), 
            'to' => mktime());
      }
  
      
      
      if ($fields['mode'] != 'select') {

         if ($fields['to'] == 0) {
            $fields['to'] = mktime(); 
         } else {
            //модификация UNIXTIMESTAMP для включения в период времени до 23:59:59
         	$fields['to'] = mktime(23,59,59,date("m",$fields['to']), 
         	                                date("d",$fields['to']), 
         	                                date("Y",$fields['to'])); 
         }
         
        
         if ($fields['from'] <= $fields['to']) {
            return array('from' => $fields['from'], 'to' => $fields['to']);
         } else {
            return array('from' => $fields['to'], 'to' => $fields['from']);         
         }
      }      
      switch ($fields['period']) {
         case 'today':        //от начала суток до настоящего момента времени
            return array(
               'from' => mktime(0, 0, 0, date("m"), date("d"), date("Y")), 
               'to' => mktime());
         case 'yesterday':    //за весь вчерашний день
            return array(
               'from' => mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")), 
               'to' => mktime(0, 0, -1, date("m"), date("d"), date("Y")));
         case 'lastweek':     //за последнюю полную неделю
            $firstWeekDay = period_first_day_of_the_week();
            if ($firstWeekDay == 2) {
               return array(
                  'from' => mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y")),
                  'to' => mktime(0, 0, -1, date("m"), date("d") - date("w") + 1, date("Y")));
            } else {
               return array(
                  'from' => mktime(0, 0, 0, date("m"), date("d") - date("w") - 7, date("Y")),
                  'to' => mktime(0, 0, -1, date("m"), date("d") - date("w"), date("Y"))); 
            }
         case 'lastbusinessweek': //за последнюю полную рабочую неделю 
            return array(
               'from' => mktime(0, 0, 0, date("m"), date("d") - date("w") - 6, date("Y")),
               'to' => mktime(0, 0, -1, date("m"), date("d") - date("w") - 1, date("Y")));
         case 'thismonth':    //от начала текущего месяца до настоящего момента времени
            return array(          
               'from' => mktime(0, 0, 0, date("m"), 1, date("Y")),
               'to' => mktime());
         case 'lastmonth':    //за весь предыдущий месяц
            return array(
               'from' => mktime(0, 0, 0, date("m") - 1, 1, date("Y")),
               'to' => mktime(0, 0, -1, date("m"), 1, date("Y")));
         default:             //от начала Unix-эпохи до настоящего момента времени
            return array('from' => mktime(0, 0, 0, 1, 1, 2008), 'to' => mktime());
      }      
   }
}

if ( ! function_exists('get_all_periods')) {
   /**
   * возвращает javascript-код для определения текста периода по выбору options
   *
   * @return string javascript-код
   */ 
   function get_all_periods() {
      $all = array('today', 'yesterday', 'lastweek', 'lastbusinessweek', 'thismonth', 'lastmonth', 'alltime');
      $fields['mode'] = 'select';
      $code = '';
      foreach ($all as $per) {
         $fields['period'] = $per;
         $range = data_range($fields);
         $from = type_to_str($range['from'], 'date');
         $to = type_to_str($range['to'], 'date');
         $code .= "case '$per': return '$from-$to';\n"; 
      }
      $code .= "case 'start': return '".type_to_str(0, 'date')."';\n";
      $code .= "case 'end': return '".type_to_str(mktime(), 'date')."';\n";
      return $code;
   }
}   

if ( ! function_exists('datepicker_vars')) {
   /**
   * устанавливает переменные для скрипта datepicker'а
   *
   * @param array $content массив с переменными
   * @return ничего не возвращает
   */
   function datepicker_vars(&$content) {
     
      $week_start = period_first_day_of_the_week()-1;
      // period_first_day_of_the_week() возвращает:
      // 1 - воскресенье, 2 - понедельник, 7 - суббота,
      // а нужно для datepicker - 1 - понедельник, 7 - воскресенье.
      // т.е. если $week_start == 0, то это воскресенье 
      if(!$week_start){
          $week_start = 7;
      }
      
      // OLD WAY
      /*
      $date_format = get_format('date_input');
      
      $date_format = str_replace('%m', 'mm', $date_format);   	
      $date_format = str_replace('%d', 'dd', $date_format);    
      $date_format = str_replace('%Y', 'yy', $date_format);
      
      
      $content['DPDATEFORMAT'] = "'$date_format'";
      */
      
      $date_format = get_date_format();
      $date_format = str_replace('m', 'mm', $date_format);    
      $date_format = str_replace('d', 'dd', $date_format);    
      $date_format = str_ireplace('y', 'yy', $date_format);
      /**
       * текущий датапикер форматирует год только как yyyy
       * или я не знаю как он это делает.
       * поэтому год в датах только в полном формате
       * @author Semerenko
       */
      //$date_format = str_replace('Y', 'yyyy', $date_format);
      
      
      
      // Zend date format
      //$date_format = Zend_Locale_Format::getDateFormat(get_instance()->locale);
   
      // simple way
      //$date_format = 'mm.dd.yy';
      
      $content['DPDATEFORMAT'] = '"' . $date_format . '"';
      $content['DPWEEKSTART'] = $week_start;
   }      
}

if ( ! function_exists('date_html')) {
   /**
   * возвращает HTML-код для поля ввода даты
   *
   * @param string $name идентефикатор(и имя) элемента ввода даты
   * @var array $options Дополнительные опции (changeMonth и changeYear)
   * @return string HTML-код элемента ввода даты
   */
   function date_html($name, $options = array()) {
      
      $CI =& get_instance();
      $html = $CI->load->view('common/date_field.html', '', TRUE);
      
      $date_params = array();
      datepicker_vars($date_params);
      
      $html = str_replace('<%NAME%>', $name, $html);
      $html = str_replace('<%DPDATEFORMAT%>', $date_params['DPDATEFORMAT'], $html);
      $html = str_replace('<%DPWEEKSTART%>', $date_params['DPWEEKSTART'], $html);
      
      $changeMonth = 'false';
      if ((isset($options['changeMonth'])) && ($options['changeMonth'] == true)) {
      	 $changeMonth = 'true';
      }
      $html = str_replace('<%CHANGEMONTH%>', $changeMonth, $html);
      
      $changeYear = 'false';
      if ((isset($options['changeYear'])) && ($options['changeYear'] == true)) {
      	 $changeYear = 'true';
      }
      $html = str_replace('<%CHANGEYEAR%>', $changeYear, $html);
      
      $yearRange = '-10:+10';
      if ((isset($options['yearRange']))) {
      	 $yearRange = $options['yearRange'];
      }
      $html = str_replace('<%YEARRANGE%>', $yearRange, $html);
      
      $minDate = 'null';
      if ((isset($options['minDate']))) {
      	 $minDate = $options['minDate'];
      }
      $html = str_replace('<%MINDATE%>', $minDate, $html);
      
      $maxDate = 'null';
      if ((isset($options['maxDate']))) {
      	 $maxDate = $options['maxDate'];
      }
      $html = str_replace('<%MAXDATE%>', $maxDate, $html);
      
      $defaultDate = 'null';
      if (isset($options['defaultDate'])) {
      	 $defaultDate = $options['defaultDate'];
      }
      $html = str_replace('<%DEFAULT_DATE%>', $defaultDate, $html);
      
      $tabIndex = '';
      if (isset($options['tabIndex'])) {
      	 $tabIndex = $options['tabIndex'];
      }
      $html = str_replace('<%TAB_INDEX%>', $tabIndex, $html);
      
      return $html;        
   } //end date_html
}   

if ( ! function_exists('period_html')) {
   /**
   * возвращает HTML-код для стандартного селектора периода дат
   *
   * @param string $form (опционально) префикс идентефикаторов (при наличии нескольких селекторов на странице)
   * @param string $caption (опционально) подпись к селектору
   * @param string $button имя кнопки сабмита, по умолчанию 'Update', если '' - кнопка не отображается 
   * @param type4 var4 cmt4
   * @return type cmt
   */
   function period_html($form = '', $caption = '', $button = 'Update') {
      
     
      $CI =& get_instance();
      $html = $CI->load->view('common/date_filter.html', '', TRUE);
      
      if ($caption != '') {
         $html = str_replace('<%STYLE%>', '', $html);
         $html = str_replace('<%CAPTION%>', __($caption), $html);
      } else {
         $html = str_replace('<%STYLE%>', 'style="display:none;"', $html);       
      }
      $prefix = ($form == '') ? '' : $form.'_';
      $html = str_replace('<%FORM%>', $prefix, $html);
      if ($button != '') {
         $html = str_replace('<%BSTYLE%>', '', $html);
         $html = str_replace('<%BUTTON%>', __($button), $html);         
      } else {
         $html = str_replace('<%BSTYLE%>', ' style="display:none;"', $html);       
      }
      
      
   	return $html;        
   } //end period_html
}   

if (!function_exists('period_load')) {
   /**
   * загружает из временных переменных пользователя значения полей для заданного селектора
   *
   * @param string $form уникальный префикс переменных для каждого селектора (обычно имя формы)
   * @param string $mode режим селектора по умолчанию
   * @param string $period значение периода по умолчанию
   * @return array массив со значенеими полей селектора
   */ 
   function period_load($form, $mode, $period) {

      $CI =& get_instance();            
      $fields['mode'] = $CI->global_variables->get($form.'_mode', $CI->user_id); 
      if (!$fields['mode']) {
         $fields['mode'] = $mode;
         $fields['period'] = $period;
         return $fields;
      }
      if ($fields['mode'] == 'select') {
         $fields['period'] = $CI->global_variables->get($form.'_period', $CI->user_id, $period);
         return $fields;
      }
    
      $fields['from'] = $CI->global_variables->get($form.'_from', $CI->user_id);
      $fields['to'] = $CI->global_variables->get($form.'_to', $CI->user_id);
      return $fields;         
   } //end period_load
}

if (!function_exists('period_save')) {
   /**
   * сохраняет текущие значения селектора периода во временные переменные пользователя
   *
   * @param string $form уникальный префикс переменных для каждого селектора (обычно имя формы)
   * @param array $fields массив со значениями полей селектора
   * @return ничего не возвращает
   */   
   function period_save($form, $fields) {
   	  $CI =& get_instance();
      $CI->temporary[$form.'_mode'] = $fields['mode'];
      if ($fields['mode'] == 'select') {
         $CI->temporary[$form.'_period'] = $fields['period'];
      } else {
         $CI->temporary[$form.'_from'] = $fields['from'];
         $CI->temporary[$form.'_to'] = $fields['to'];
      }   	
   } //end period_save
}   

/* Get first day of the week by Zend */

function period_first_day_of_the_week(){
      $days = Zend_Locale_Data::getList(get_instance()->locale, 'days');
      $weekInfo = Zend_Locale_Data::getList(get_instance()->locale, 'week');
      $firstDayAbbr = $weekInfo['firstDay'];
      /*
       * Здесь возникает ошибка..временами, когда не существует 
       * $days['format']['narrow']['sun'],
       * и локально не получается воспрозвести эту ошибку.
       */
      if(isset($days['format']['narrow'][$firstDayAbbr])){
         $firstDayNr = (int)$days['format']['narrow'][$firstDayAbbr];
      }else{
         $firstDayNr = 1;
      }
      return $firstDayNr;
}
/* end getting first day of the week */    