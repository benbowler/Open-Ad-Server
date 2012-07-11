<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Хелпер для конвертирования и проверки значений полей формы
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/

if (!function_exists('get_format')) {
   /**
   * возвращает региональный формат для заданного типа данных
   *
   * @param string $type имя типа данных
   * @return string строка формата
   */ 
   function get_format($type) {
      static $setting = array();
      if (0 == sizeof($setting)) {
         
         $CI=& get_instance();
         $CI->load->model('locale_settings');
         
         if(isset($CI->locale)) {
         	$locale = $CI->locale;
         } else {
            $locale = 'en_US';
         }
         
         $setting = $CI->locale_settings->get($locale);
         
         
      }
      return $setting[$type];
   }
}

if (!function_exists('get_number_format')) {
   /**
   * замена get_format('number');
   * Решили, что везде человек будет вводит число только с точкой  
   * @return string строка формата
   */ 
   function get_number_format() {
      
      static $number_format = '2.';
      
      return $number_format;
   }
}

if (!function_exists('get_money_format')) {
   /**
   * замена get_format('money');
   * 
   * @return string строка формата
   */ 
   function get_money_format() {
      
      static $money_format = '$%';
      
      return $money_format;
   }
}



if (!function_exists('get_date_format')) {
   /**
   * возвращает формат даты, 
   * который пользователь выбирает на странице редактирования локали
   *
   * @return string строка формата
   */ 
   function get_date_format() {
      static $date_format = null;

      if (is_null($date_format)) {
         $CI =& get_instance();
         $CI->load->model('locales');
         $date_format = $CI->locales->getDateFormat();
      }
      
      return $date_format;
   }
}

if (!function_exists('get_time_format')) {
   /**
   *
   * @return string строка формата
   */ 
   function get_time_format() {

      static $time_format = null;

      if (is_null($time_format)) {
         $CI =& get_instance();
         $CI->load->model('locales');
         $time_format = $CI->locales->getTimeFormat();
         if('' == $time_format){
            $time_format = 'H:i';
         }
      }      
      return $time_format;
   }
}

if (!function_exists('get_time_input')) {
   /**
   *
   * @return string строка формата
   */ 
   function get_time_input() {

      return '%R';
   }
}

if (!function_exists('get_date_input')) {
   /**
   * возвращает формат ввода для даты 
   *
   * @return string строка формата
   */ 
   function get_date_input() {
      static $date_input = null;

      if (is_null($date_input)) {
         $CI =& get_instance();
         $CI->load->model('locales');
         $date_input = $CI->locales->getDateInput();
      }
      
      return $date_input;
   }
}

if ( ! function_exists('get_float')) {
   /**
   * проверяет строку на соответствие формату числа с плавающей точкой
   *
   * @param string $str строка с числом
   * @return string в случае успеха - запись числа в формате PHP, иначе пустая строка
   */
   function get_float($str, $format = NULL) {
   	
      // 0.00 0.0 0
      // 0,00 0,0
      
      $value = floatval($str);

      if($value == 0 && !in_array($str,array('0.00','0.0','0'))){
         return '';
      }else{
         return $value; 
      }
   } //end get_float      
}

if ( ! function_exists('get_integer')) {
   /**
   * проверяет строку на соответствие формату числа integer
   *
   * @param string $str строка с числом
   * @param string $format формат числа ('abc' - a - десятичных знаков, b - символ точки, c - символ разделителя тысяч)
   * @return string в случае успеха - запись числа в формате PHP, иначе пустая строка
   */
   function get_integer($str, $format = NULL) {
      $value = (int)$str;
      $str = str_replace(',', '', $str);
      if(preg_match('~^[-+]?[0-9]{1,15}$~isU',$str)){ // 15 ??
         return (int)$str;
      }else{
         return '';
      }

   } //end get_float      
}

if ( ! function_exists('type_cast'))
{
   /**
   * конвертирует строковые значения полей в заданный тип
   *
   * @param string $value значение переменной
   * @param string $type тип переменной ('bool','date') 
   * @return mixed значение нужного типа
   */
   function type_cast($value, $type) {
      switch ($type) {
      	case "bool":
      	   return $value == "true";
      	case 'float':
      	   return get_float(trim($value));
      	case 'int':
      	case 'integer':
      	   return get_integer(trim($value));
      	case "date":
      	   $date_parts = strptime($value, get_date_input());
        	   return mktime(0,0,0,$date_parts["tm_mon"]+1,$date_parts["tm_mday"],1900 + $date_parts["tm_year"]);
         case 'textcode':
            $num = 0;
            for ($i = strlen($value)-1; $i>=0; $i--) {
               $num = $num*26+(ord($value[$i])-ord('a'));
            }
            return (int)($num)^67894523;         	   
      	default:
      	   return $value;
      }
   } //end type_cast 
}

if (!function_exists('type_to_str')) {
   /**
   * переводит значение заданного типа в строку
   *
   * @param mixed $data значение, которое нужно сконвертировать
   * @param string $type тип данных ('bool','date')
   * @param int $precision_default задание точного количества знаков после запятой
   * @return string полученная строка, представляющая данные
   */ 
   function type_to_str($data, $type, $precision_default = -1) {
      switch ($type) {
         case 'encode' :
            return htmlentities($data, ENT_QUOTES, 'utf-8');
         case 'translate' :
            return __($data); 
         case "integer" :
	    
            /**
             * Локально пустые значения спокойно форматируется зендом в 0,
             * но на сервере, на orbitscipts.com? после форматирования пустого значение,
             * оно так и остаётся пустое. Я не знаю почему. 
             */
            if(is_null($data) || $data == ''){
               $data = 0;
            }
	    
	    
            $res = Zend_Locale_Format::toInteger($data, array('locale'=> get_instance()->locale)); 
	    
	         return $res;

	      case "float" :
            
            $precision = 2;
            
            /**
             * Определние нужной точности для денег
             */
            if($precision_default < 0){
               $vals = explode('.',strval((float)$data));
               if(isset($vals[1])){
                  $len = strlen($vals[1]); 
                  if($len > 3){
                     $precision = 4;  
                  }elseif($len > 2){
                     $precision = 3;
                  }         
               }            
            }else{
               $precision = $precision_default;
            }
            
            return number_format($data,$precision,'.', '');   
            
            //return Zend_Locale_Format::toFloat($data, array('locale'=> get_instance()->locale));
         case 'procent' :
            return type_to_str($data, 'float',2).' %'; 
         case "money" :
         
            //return str_replace('%', type_to_str($data, 'float'), get_format('money'));
            if(is_null($data)){
               $data = 0;
            }

            /**
             * @todo Возможно прийдётся определять тип валюты как-то глобально, 
             *  когда понадобится использовать что-нибудь отличное от доллара. 
             */
            try
            {
               $currency = new Zend_Currency(get_instance()->locale, 'USD');
            }
            catch (Zend_Currency_Exception $e)
            {
               $currency = new Zend_Currency(get_instance()->default_locale,'USD');
            }
            
            $precision = 2;
            
            /**
             * Определние нужной точности для денег
             */
            $vals = explode('.',strval((float)$data));
            if(isset($vals[1])){
               $len = strlen($vals[1]); 
            }
            
            $currency->setFormat(array('precision' => $precision));
            
            
            try
            {
               $value = trim($currency->toCurrency($data));
               
               /**
                * проверка preg_matchем нужна потмоу что, например в китайском валюта отображается
                * как: US$0.00 
                */
               if(get_instance()->locale <> 'en_US' && preg_match('|^[0-9,\.\$\s\xc2\xa0]+$|',$value,$matches) ){
                  $value = '$' . trim(str_replace('$','',$value));
               }
            }
            catch (Exception $e)
            {
               
            	
               $value = '0.00';
            }
            
            return $value;
         case "nonzeromoney" :
            if ($data == 0) return "—";
            return type_to_str($data, "money"); 
         case "mysqldate" :
            $data = mktime(0, 0, 0, substr($data, 5, 2), substr($data, 8, 2), substr($data, 0, 4));
            return date(get_date_format(), $data);
         case "mysqlfloat" :
            //return str_replace(',','.',$data);
            return number_format($data, 8, '.', '');
         case 'databasedate' :
            return date("Y-m-d", $data);
         case 'databasedatetime' :
            return date("Y-m-d H:i:s", $data);            
         case "date" :
            if ($data == 0 || is_null($data)) {
               return '';
            }
            return date(get_date_format(), $data);
         case "datetime" :
            $res = date(get_date_format(), $data).' '.date(get_time_format(), $data);
          
            return $res;
         case "bool":
            return $data?"true":"false";
         case 'textcode':
            $num = (int)($data^67894523);
            $text = '';
            while ($num) {
               $text .= chr(ord('a')+($num%26)); $num=(int)($num/26);
            }
            return $text;
         case "impressions" :
            if ($data < 1000)
             return __('< 1 K');
            if ($data < 5000)
             return __('1~5 K');
            if ($data < 10000)
             return __('5~10 K');
            if ($data < 50000)
             return __('10~50 K');
            if ($data < 100000)
             return __('50~100 K');
             else return __('> 100 K'); 
         case "clicks" :
            if ($data < 100) {
               return __('< 100');
            } 
            if ($data < 900) {
               return '~ '.(round($data/100)*100);
            } 
            else {
            	return '~ '.round($data/1000).' K';
            }
         case "mime":
            return '=?UTF-8?B?'.base64_encode($data).'?=';
         default: 
   	      return $data;
      }
   } //end type_to_str
   
}

if ( ! function_exists('field_to_bool'))
{
   /**
   * переводит значения полей CHECKBOX в тип BOOLEAN
   *
   * @param strign $value значение возвращаемое для поля методом POST
   * @return boolean логическое значение
   */
   function field_to_bool($value) {
      return ! ("" == $value);    
   }
}

if ( ! function_exists('field_to_date'))
{
   /**
   * подготавливает дату в формате mm/dd/yyyy в дату для сохранения в MySQL в формате yyyy-mm-dd
   *
   * @param string $value дата в формате mm/dd/yyyy
   * @return string дата в формате yyyy-mm-dd
   */
   function date_field($value) {
      $date = explode("/", $value);
      return $date[2] . $date[0] . $date[1];
   }
}

   function timestamp_to_mysql($timestamp) {
   	return date("'Y-m-d H:i:s'", $timestamp);
   }

if (!function_exists('check_date')) {

   /**
   * проверка поля на соответствие заданному формату даты,
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */ 
   function check_date($str) {
      $CI = & get_instance();      
      $date_template = get_date_input(); 

      if (strptime($str, $date_template)) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_date', 'The %s field must contain a date.');
         }
         return FALSE;
      }
      
   } //end check_date
}

if (!function_exists('check_datetime')) {
   /**
   * проверка поля на соответсвие заданному формату даты и времени,
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_datetime($str) {
      $CI =& get_instance();
      $date_template = get_date_input().' '.get_time_input();
      if (strptime($str, $date_template)) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_datetime', 'The %s field must contain a date and time.');
         }
         return FALSE;
      }
   } //end check_datetime
}

if (!function_exists('check_hostname')) {
   /**
   * проверка поля на соответсвие требованиям к имени или IP хоста,
   * вызывается валидатором.
   * Максимальная длина доменного имени согласно RFC 1035 составляет 63 символа.
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_hostname($str) {
      $CI =& get_instance();
      if (@preg_match('/^(?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+(com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum|travel|[a-zA-Z]{2,2})$/', $str) || ( $CI->validation->valid_ip($str))) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_hostname', 'The %s field must contain a valid hostname or IP.');
         }
         return FALSE;
      }
   } //end check_hostname
}

if (!function_exists('check_url')) {
   /**
   * проверка поля на соответсвие требованиям к URL ( БЕЗ УКАЗАНИЯ ПРОТОКОЛА),
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_url($str) {
      $CI =& get_instance(); 
		
      if (preg_match('/^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_url', 'The %s field must contain a valid URL without protocol prefix.');
         }
         return FALSE;
      }
   } //end check_url
}

if (!function_exists('check_full_url')) {
   /**
   * проверка поля на соответсвие требованиям к URL с заданным(и) протоколами,
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @param string $val список протоколов, разделённых "|" (http|https|ftp). При отсутствии значения, будет валидировать строку без протокола (аналогично фильтру "url")
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_full_url($str, $val) {
      $CI =& get_instance(); 
      
      try {
         $val = strtolower($val);
         if (preg_match('/[^a-z\,]/', $val)) {
            throw new Exception('Filter must contain only uries protocol name, separated with ",", like "http,https"');
         }
	 if (empty($val)) {
            if (preg_match('/^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) {
               return TRUE;
            }
	    throw new Exception('The %s field must contain a valid URL without protocol prefix.');
	 } else {
            $val = str_replace(',', '|', $val);
            if (preg_match('/^(' . $val . '):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) {
               return TRUE;
            }
	    throw new Exception('The %s field must contain a valid URL with <b>"' . str_replace('|', '</b> or <b>', $val) . '"</b> protocol prefix.');
	 }
      } catch (Exception $e) {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_full_url', $e->getMessage());
         }
         return FALSE;
      }
   } //end check_url
}

if (!function_exists('check_float')) {
   /**
   * проверка поля на соответствие числу с плавающей точкой
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_float($str, $param = null) {
      $CI =& get_instance();
      
      /**
       *  Упрощение. В полях ввода будут просто обычный float
       */
      
     // if (is_numeric(type_cast($str, 'float'))) {
      if(is_numeric($str)){
      	 /**
      	  * Если правило задано как float[D], то проверяем
      	  * количество знаков после запятой.
      	  * 
      	  * @author Grechkin V.
      	  */
      	 if (!is_null($param) && $param != '') {
      	 	if (preg_match('/^(\d+)$/ui', $param, $matches)) {
      	 		$after_point = (int)$matches[1];
      	 	} else {
      	 		return TRUE;
      	 	}
      	 	
	        $str_t = (float)$str;
	        $number = floor($str_t);
	        $decimal = $str_t - $number;
	        $decimal = $decimal.'';
	        $decimal = preg_replace('/^\d+\./ui', '', $decimal);
	        if (strlen($decimal) <= $after_point) {
	        	return TRUE;
	        } else {
		        if (class_exists("CI_Validation")) {   
		           $CI->validation->set_message('check_float', 'The %s field must contain a valid float number with '. $after_point .' digits after the decimal point.');
		        }
		        return FALSE;
	        }
      	 }
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {   
            $CI->validation->set_message('check_float', 'The %s field must contain a valid float number.');
         }
         return FALSE;
      }
   } //end check_float
}

if (!function_exists('check_password')) {
   /**
   * проверка поля на соответствие заданному паролю
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_password($str, $md5) {
      $CI =& get_instance();
      if (md5($str) == $md5) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_password', 'The %s field contain wrong password.');
         }
         return FALSE;
      }
   } //end check_password
}

if (!function_exists('limit_str')) {
	/**
   * ограничение строки по максимальной длине с добавлением троеточия
   *
   * @param string $str значение поля
   * @param int $limit максимальная длина строки
   * @return string результат ограничения строки
   */
	function limit_str($str, $limit = 30) { 
      if (mb_strlen($str,'UTF-8') > $limit) {
         $str = mb_substr($str, 0, $limit - 3,'UTF-8') . '...';
      } 
      return $str; 
	}
}

if (!function_exists('limit_str_and_hint')) {
   /**
   * ограничение строки по максимальной длине и добавление аттрибута title для всплывающей подсказки
   * '<span title="full string length">limited stri...</span>'
   *
   * @param string $str значение поля
   * @param int $limit максимальная длина строки
   * @return string результат ограничения строки
   */
   function limit_str_and_hint($str, $limit = 30) { 
      //return '<span title="'.type_to_str($str,'encode').'">'.limit_str($str,$limit).'</span>'; 
   //   return '<span title="'.type_to_str($str,'encode').'" class="truncate" max_len="'.$limit.'">'.type_to_str($str,'encode').'</span>';
      $cutstr=limit_str($str, $limit);
      return '<span title="'.type_to_str($str,'encode').'">'.type_to_str($cutstr,'encode').'</span>';
   }
}

if (!function_exists('check_positive')) {
  /**
   * проверка поля на положительное числовое значение
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_positive($str) {
      $CI =& get_instance();
      $str_val = type_cast($str, 'float');
      if (is_numeric($str_val) && ($str_val > 0)) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_positive', 'The %s field must contain a positive number.');
         }
         return FALSE;
      } 
   }
}

if (!function_exists('check_integer')) {
  /**
   * проверка поля на целое число
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_integer($str) {
      $CI =& get_instance();
      $str_val = type_cast($str, 'integer');
      if (is_numeric($str_val)) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_integer', __('The %s field must contain a integer number.'));
         }
         return FALSE;
      } 
   }
}

if (!function_exists('check_non_negative')) {
  /**
   * проверка поля на число с плавающей точкой
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_non_negative($str) {
      $CI =& get_instance();
      $str_val = type_cast($str, 'float');
      if (is_numeric($str_val) && $str_val>=0) {
         return TRUE;
      } else {
         if (class_exists("CI_Validation")) {       
            $CI->validation->set_message('check_non_negative', 'The %s field must contain a non-negative number.');
         }
         return FALSE;
      } 
   }
}

if (!function_exists('check_ip')) {
  /**
   * проверка поля на IP
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_ip($str) {
      $CI =& get_instance();
      if (preg_match('~^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$~', $str)) {
         $nums = explode('.', $str);
         for ($i = 0; $i < 4; $i++) {
            if (255 < $nums[$i]) {
               if (class_exists("CI_Validation")) {
                  $CI->validation->set_message('check_ip', 'The %s field must contain a valid IP.');
               }
               return FALSE;
            }
         }
         return TRUE;
      }
      if (class_exists("CI_Validation")) {
         $CI->validation->set_message('check_ip', 'The %s field must contain a valid IP.');
      }
      return FALSE;
   }
}

if (!function_exists('check_ip_part')) {
  /**
   * проверка поля на IP
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_ip_part($str) {
      $CI =& get_instance();
      if (preg_match('~[^\\d\\.]+|\\d{4,}|(\\.\\d+){4,}|(\\d+\\.){4,}|\\.{2,}~', $str)) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_ip_part', 'The %s field must contain a valid IP part.');
         }
         return FALSE;
      }
      $nums = explode('.', $str);
      for ($i = 0, $j = count($nums); $i < $j; $i++) {
         if (255 < intval($nums[$i])) {
            if (class_exists("CI_Validation")) {
               $CI->validation->set_message('check_ip_part', 'The %s field must contain a valid IP part.');
            }
            return FALSE;
         }
      }
      return TRUE;
   }
}

if (!function_exists('check_ip_re')) {
  /**
   * проверка поля на IP
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_ip_re($str) {
      $CI =& get_instance();
      //if (preg_match('~\\d{4,}|(\\[\\d-\\d\\]|\\[?\\\\d\\]?)\\{([4-9]|\\d{2,})|(\\.\\d+){4,}|(\\d+\\.){4,}|[^\\]\\w~', $str)) {
      if (preg_match('~\\d{4,}|(\\..+){4,}|(.+\\.){4,}|[^\\d\\.\\^\\$\\+\\*]|\\.{2,}~', $str)) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_ip_re', 'The %s field must contain a valid IP RegExp.');
         }
         return FALSE;
      }
      $nums = explode('.', $str);
      for ($i = 0, $j = count($nums); $i < $j; $i++) {
         if (255 < intval($nums[$i])) {
            if (class_exists("CI_Validation")) {
               $CI->validation->set_message('check_ip_re', 'The %s field must contain a valid IP RegExp.');
            }
            return FALSE;
         }
      }
      return TRUE;
   }
}

if (!function_exists('check_min_val')) {
  /**
   * проверка поля на минимальное значение
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @param string $val граничное значение
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_min_val($str, $val) {
      $CI =& get_instance();
      if ($str < $val) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_min_val', 'The %s field must not be less then ' . $val);
         }
         return FALSE;
      }
      return TRUE;
   }
}

if (!function_exists('check_max_val')) {
  /**
   * проверка поля на максимальное значение
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @param string $val граничное значение
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_max_val($str, $val) {
      $CI =& get_instance();
      if ($str > $val) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_max_val', 'The %s field must not be greater then ' . $val);
         }
         return FALSE;
      }
      return TRUE;
   }
}

if (!function_exists('check_interval')) {
  /**
   * проверка поля на минимальное и максимальное значения
   * вызывается валидатором
   *
   * @param string $str значение поля
   * @param string $val граничные значения
   * @return bool истина, если поле успешно прошло проверку
   */
   function check_interval($str, $val) {
      $CI =& get_instance();
      $val = explode(';', $val);
      if ($str < $val[0]) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_interval', 'The %s field must not be less then ' . $val[0]);
         }
         return FALSE;
      } elseif ($str > $val[1]) {
         if (class_exists("CI_Validation")) {
            $CI->validation->set_message('check_interval', 'The %s field must not be greater then ' . $val[1]);
         }
         return FALSE;
      }
      return TRUE;
   }
}

?>