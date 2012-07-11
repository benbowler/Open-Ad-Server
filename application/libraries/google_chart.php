<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Библиотека для создания графиков на основе API Google Chart
* доступные методы:
* 
* 
* @author Владимир Юдин
* @project SmartPPC 6
* @version 1.0.0
*/
class Google_chart {

   protected $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
   
   protected $x_resolution = 200;   // максимальное число значений по оси X
   
   protected $type;                 // тип графика
 
   protected $data_format;          // 'text', 'scaling_text', 'simple', 'extended'
   
   protected $width;                // ширина графика
   
   protected $height;               // высота графика
   
   protected $data;                 // данные для графика
   
   protected $date_range;           // диапазон дат по оси X
   
   protected $y_precision;          // точность данных для оси Y (количество знаков после запятой)
    
   /**
   * конструктор класса,
   * установка значений по умолчанию
   *
   * @return ничего не возвращает
   */      
	public function Google_chart() {
		$this->set_defaults();
	}

   /**
   * установка значений по умолчанию
   *
   * @return ничего не возвращает
   */	
	public function set_defaults() {
	   $this->type = 'lc';
	   $this->data_format = 'simple';
	   $this->width = 440;
	   $this->height = 200;
	   $this->y_precision = 0;
      for($x = 0; $x <= 30; $x++) {
         $this->data[] = sin(pi()*$x/15)*20+rand(30,80);   
      }      
	} //end set_defaults

   /**
   * задание типа диаграмы
   *
   * @param string $type код типа диаграммы
   * @return ничего не возвращает
   */	
	public function type($type) {
	   $this->type = $type;
	} //end type
	
   /**
   * задает данные диаграммы
   *
   * @param array $data массив числовых значений диаграммы
   * @param integer $precision точность меток диаграммы (количество знаков после запятой, по умолчанию - 0)
   * @return ничего не возвращает
   */	
	public function data($data, $precision = 0) {
	   if (count($data) > $this->x_resolution) {
	      $this->data = array();
	      $step = count($data)/$this->x_resolution;
	      $stepper = $step;
	      $max = NULL;
	      foreach ($data as $value) {
	         if (is_null($max) || $value>$max) {
	            $max = $value;
	         }
	      	if (++$stepper > $step) {
	      	   $this->data[] = $max;
	      	   $stepper -= $step;
	            $max = NULL;
            }	      
	      }
	      if (!is_null($max)) {
	         $this->data[] = $max;
	      }
	   } else {
         $this->data = $data;
	   }
	   $this->y_precision = $precision;
      if (count($this->data) == 1) {
         $this->data[] = $this->data[0];     
      }
	} //end data

   /**
   * устанавливает временной период по оси X
   *
   * @param array $date_range массив с ключами 'from' и 'to'
   * @return ничего не возвращает
   */	
	public function date_range($date_range) {	   
      $from = getdate($date_range['from']);
      $to = getdate($date_range['to']);
	   $this->date_range = array(
	      'from' => mktime(0,0,0,$from['mon'], $from['mday'], $from['year']), 
	      'to' => mktime(0, 0, 0, $to['mon'], $to['mday'], $to['year']));	   
	} //end date_range
	
   /**
   * рассчет и генерация меток временного периода для оси X
   *
   * @param array $params ссылка на массив с парметрами для вызова Google Charts
   * @return ничего не возвращает
   */	
	protected function date_labels(&$params) {
	   $CI =& get_instance();
	   $CI->load->helper('fields');
	   $from = getdate($this->date_range['from']);
      $to = getdate($this->date_range['to']);
      $per = $this->date_range['to'] - $this->date_range['from'];
      $days = (int)($per/(24*60*60));
	   $period = getdate($per);	  
      $period['year'] -= 1970;	 
      $period['mon']--;    
      $period['yday']--;                
      if ($days>365*12) $step = 'decade';
      elseif ($days>400) $step = 'year';
      elseif ($days>70) $step = 'month';
      elseif ($days>20) $step = 'week';
      else $step = 'day';
      $params['chxl'] = '0:';
      $params['chxp'] = '0';
      switch ($step) {
         case 'decade':
            while ($from['year']%10 != 0) {
               $from['year']++;
            }
            while ($from['year']<=$to['year']) {
               $pos = mktime(0,0,0,1,1,$from['year']);
               $label_pos = ($pos-$this->date_range['from'])*100/$per;
               if ($label_pos > 95) {
                  $params['chxl'] .= '| ';
               } else {
                  $params['chxl'] .= '|' . date('Y', $pos);                
               }
               $params['chxp'] .= ',' . number_format($label_pos, 2);
               $from['year'] += 10;
            }           
            break; 
      	case 'year':
            if($from['yday'] != 1) {
               $from['year']--; 
            }
      	   while ($from['year']<$to['year']) {
               $from['year']++;
               $pos = mktime(0,0,0,1,1,$from['year']);
               $label_pos = ($pos-$this->date_range['from'])*100/$per;
               if ($label_pos > 95) {
                  $params['chxl'] .= '| ';
               } else {
                  $params['chxl'] .= '|' . date('Y', $pos);                
               }
               $params['chxp'] .= ',' . number_format($label_pos, 2);
      	   }      	   
      	   break;
      	case 'month':
      	   if($from['mday'] != 1) {
      	      $from['mon']++; 
      	   }
      	   do {      	      
      	      $pos = mktime(0,0,0,$from['mon'],1,$from['year']);
      	      $label_pos = ($pos-$this->date_range['from'])*100/$per;
      	      if ($label_pos > 95) {
                  $params['chxl'] .= '| ';
      	      } else {
                  $params['chxl'] .= '|' . __(date('M', $pos));      	       
      	      }
               $params['chxp'] .= ',' . number_format($label_pos, 2);
               if (++$from['mon']>12) {
                  $from['mon']=1;
                  $from['year']++;                  
               }               
      	   } while ($from['year']<$to['year'] || ($from['year']==$to['year'] && $from['mon']<=$to['mon']));
      	   break;
      	case 'week':
      	   $week_start = $CI->global_variables->get('WeekFromMonday')?1:0;
            $pos = $this->date_range['from'] + 24*60*60*((7+$week_start-$from['wday'])%7);
        	   while($pos < $this->date_range['to']) {        	       
               $label_pos = ($pos-$this->date_range['from'])*100/$per;
        	      if ($label_pos > 95) {
                  $params['chxl'] .= '| ';
               } else {
                  $params['chxl'] .= '|' . date('d.m', $pos);                
               }
               $params['chxp'] .= ',' . number_format($label_pos, 2);        	    
               $pos += 7*24*60*60; //следующая неделя
        	   }
      	   break;
      	default:
      	   $pos = $this->date_range['from'];
      	   while ($pos <= $this->date_range['to']) {
      	      if ($per < 24*60*60) {      	         
      	         $label_pos = 0;
      	      } else {
                  $label_pos = ($pos-$this->date_range['from'])*100/$per;
      	      }
               $params['chxl'] .= '|' . date('d', $pos);
               $params['chxp'] .= ',' . number_format($label_pos, 2);
               $pos += 24*60*60; //следующий день      	    
      	   }
      }      
	} //end date_labels	
	
   /**
   * создает ссылку на Google Chart API на основе заданных параметров
   *
   * @return string текст ссылки
   */		
	public function link() {
	   $data = '';
      $minv = NULL;
      $maxv = NULL;
	   $index = 0;
	   foreach ($this->data as $value) {
	      if (is_null($minv) || $value<$minv) {
	         $minv = $value; $min_index = $index;
	      }
         if (is_null($maxv) || $value>$maxv) {
            $maxv = $value; $max_index = $index;
         }	
         $index++;      
	   }
	   $max = $maxv*1.1;
	   $min = 0;
	   $range = $max;
	   if ($range == 0) {
	      $range = 1;
	   }
	    
	   $params['cht'] = $this->type;	   
	   switch ($this->data_format) {
         case 'extended':
            $params['chd'] = 'e:';
            foreach ($this->data as $value) {
               $val = ($value-$min)*4095/$range;
               $params['chd'] .= $this->code{$val >> 6} . $this->code{$val & 63}; 
            }                     
            break;
         case 'text':
            $params['chd'] = 't:';
            $first = TRUE;
            foreach ($this->data as $value) {               
               $params['chd'] .= ($first?'':',').number_format(($value-$min)*100/$range, 1);
               $first = FALSE; 
            }                     
            break;
         case 'scaling_text':
            $params['chd'] = 't:';
            $first = TRUE;
            foreach ($this->data as $value) {               
               $params['chd'] .= ($first?'':',').number_format($value, 3, '.', '');
               $first = FALSE; 
            }                               
            $params['chds'] = number_format($min, 3, '.', '') . ',' . number_format($max, 3, '.', '');
            break;
         default:          
	   	   $params['chd'] = 's:';
	   	   foreach ($this->data as $value) {
	   	   	$params['chd'] .= $this->code{($value-$min)*61/$range};
	   	   }	   	   
	   	   break;
	   }
      $params['chxs'] = '0,707070,11,-1,lt,C0C0C0|1,707070,11,-1,lt,C0C0C0';
      $params['chxtc'] = '0,-200|1,-400';	   	   
	   $params['chls'] = '4.0,0.0,0.0';
	   $params['chxt'] = 'x,y,r';
	   $this->date_labels($params);
	   $params['chxl'] .= "|2:|".number_format($minv, $this->y_precision, '.', '').'|'.number_format($maxv, $this->y_precision, '.', '');
	   $params['chxp'] .= "|2,".number_format(($minv-$min)*100/$range, 2).",".number_format(($maxv-$min)*100/$range, 2); 	  
      $params['chs'] = $this->width . 'x' . $this->height;
      $params['chco'] = '76A4FB';
      $params['chxr'] = '1,'.number_format($min, 3, '.', '') . ',' . number_format($max, 3, '.', '');
	   $data = '';
	   foreach ($params as $name => $value) {
	   	if (sizeof($data)) $data .= '&';
	   	$data .= $name . '=' . $value;
	   }	   	   
      return "http://chart.apis.google.com/chart?$data";    	   
	} //end link
	
} //end class Google_chart

?>