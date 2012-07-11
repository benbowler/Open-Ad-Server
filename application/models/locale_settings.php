<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/**
* модель для работы с региональными настройками (локалями)
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Locale_settings extends CI_Model {
 
   public function __construct() {
      parent::__construct();
   }

   /**
   * возвращает региональные настройки для заданной локали
   *
   * @return array список настроек в формате 'name => value' (date, date_input, number, money)
   */   
   public function get($locale) {
      $settings = array(
         'date' => 'm.d.Y',
         'time' => 'H:i',
         'date_input' => '%m.%d.%Y',
         'time_input' => '%m.%d.%Y',
         'number' => "2.'",
         'money' => '$ %',
         'week' => 7
      );
      $res = $this->db->get_where('locale_settings', array('locale' => $locale));
      if($res->num_rows()) {
         $row = $res->row();
         $settings['date'] = $row->date_format;
         $settings['time'] = $row->time_format;
         $settings['date_input'] = $row->date_input;
         $settings['time_input'] = $row->time_input;
         $settings['number'] = $row->number_format;
         $settings['money'] = $row->money_format; 
         $settings['week'] = $row->week_start; 
      }
      return $settings;
   }
   
   /**
   * возвращает HTML-код с JavaScript-переменными для datepicker'a
   *
   * @return string HTML-код
   */   
   public function date_picker() {
      return
         "<script language='javascript'>
             jQuery(function($){
                $.datepicker.messages = {
                   clearText: '".__('Clear')."', clearStatus: '',
                   closeText: '".__('Close')."', closeStatus: '',
                   prevText: '&#x3c;".__('Prev')."',  prevStatus: '',
                   prevBigText: '&#x3c;&#x3c;', prevBigStatus: '',
                   nextText: '".__('Next')."&#x3e;', nextStatus: '',
                   nextBigText: '&#x3e;&#x3e;', nextBigStatus: '',
                   currentText: '".__('Today')."', currentStatus: '',
                   monthNames: ['".__('January')."','".__('February')."','".__('March')."','".__('April')."','".__('May')."','".__('June')."',
                   '".__('July')."','".__('August')."','".__('September')."','".__('October')."','".__('November')."','".__('December')."'],
                   monthNamesShort: ['".__('Jan')."','".__('Feb')."','".__('Mar')."','".__('Apr')."','".__('May')."','".__('Jun')."',
                   '".__('Jul')."','".__('Aug')."','".__('Sep')."','".__('Oct')."','".__('Nov')."','".__('Dec')."'],
                   monthStatus: '', yearStatus: '',
                   weekHeader: '".__('Not')."', weekStatus: '',
                   dayNames: ['".__('sunday')."','".__('monday')."','".__('tuesday')."','".__('wednesday')."','".__('thursday')."','".__('friday')."','".__('saturday')."'],
                   dayNamesShort: ['".__('sun')."','".__('mon')."','".__('tue')."','".__('wed')."','".__('thu')."','".__('fri')."','".__('sat')."'],
                   dayNamesMin: ['".__('Sn')."','".__('Mn')."','".__('Ts')."','".__('Wd')."','".__('Th')."','".__('Fr')."','".__('St')."'],
                   dayStatus: 'DD', dateStatus: 'D, M d',
                   dateFormat: 'dd.mm.yy', firstDay: 1, 
                   initStatus: '', isRTL: false};
                $.datepicker.setDefaults($.datepicker.messages);
             });
          </script>";
   } //date_picker
   
}          

?>