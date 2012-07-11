<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* контроллер для создания/изменения платежной программы канала
* 
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Common_Edit_Channel_Program extends Parent_controller {

	private $supported_program_types = array('CPM','Flat_Rate');
	
	private $ad_type = null;
	
	private $max_ad_slots = 1;
	
   protected $program_id = null;
   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */
   public function __construct() {
      parent::__construct();
      
      
      $this->load->model('channel_program');
      $this->load->model('channel');
      
      
      $this->load->library("form");
   } //end __construct

   
   public function index($id_program = null) {
      $this->manage(null, null, $id_program);
   }
   
   public function create($id_channel = null, $program_type = null) {
   	$this->manage($id_channel, $program_type);
   }
   
   public function edit($id_program = null) {
      $this->manage(null, null, $id_program);
   }
   
   /**
   * показывает форму для изменения канала или его создания
   *
   * @param $id_channel - идентификатор канала
   * @param $program_type - тип программы
   * @return ничего не возвращает
   */   
   private function manage($id_channel = null, $program_type = null, $id_program = null) {
      $error_flag = false;
      $error_message = '';
   	
      
      if (!is_null($id_program)) { //Пробуем запустить процесс редактирования прайса
      	$program_owner = $this->channel_program->get_program_owner($id_program);
      	if ((!$error_flag) && is_numeric($program_owner) && ($this->user_id != $program_owner)) {
               $error_message = 'Access denied';
               $error_flag = true;
         }
         
         $info = $this->channel_program->get_info($id_program);
      
         if ((!$error_flag) && is_null($info)) {
             $error_message = 'Channel program is not found!';
             $error_flag = true;
         }

         if (!is_null($info)) {
            $id_channel = $info->id_channel;
            $program_type = $info->program_type;
            $mode = "modify";
            $this->program_id = $id_program;
         }
      } else { //Проверка прав на создание прайса в канале
      
	      if ((!$error_flag) && ($this->user_id != $this->channel->get_channel_owner($id_channel))) {
	               $error_message = 'Access denied';
	               $error_flag = true;
	      }
	      
	      if ((!$error_flag) && (!in_array($program_type, $this->supported_program_types))) {
	               $error_message = 'Unsupported program type';
	               $error_flag = true;
	      } 
	      
	      $mode = "create";
      }
      
      if  ($error_flag) {
                $data = array(
               'MESSAGE' => __($error_message),
               'REDIRECT' => $this->site_url . $this->index_page.$this->role . '/manage_sites_channels'
            );
            $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
            $this->_set_content($content);
            $this->_display();
            return;
      }
   	
      $channel_info = $this->channel->get_info($id_channel);
      $channel_info->ad_type = explode(',', $channel_info->ad_type);
      
      switch ($program_type) {
      	case 'CPM':
      	  switch ($mode) {
      	  	case 'create':
      	  	  $form_title = __('Create CPM prices for channel');
      	  	break;
      	  	case 'modify':
              $form_title = __('Edit CPM prices for channel');
            break;
      	  }
      	  $volume_title = 'Impressions';
      	break;
      	case 'Flat_Rate':
           switch ($mode) {
            case 'create':
              $form_title = __('Create Flat Rate prices for channel');
            break;
            case 'modify':
              $form_title = __('Edit Flat Rate prices for channel');
            break;
           }
         
      	  $program_type = 'flat_rate';
           $volume_title = 'Days';
         break;
      }
      
      $this->ad_type = $channel_info->ad_type;
      $form = array(
         "id"          => $this->program_id, 
         "name"        => "channel_program_form",
         "view"        => "common/manage_channel_prices/channel_program_form.html",  
         "vars"        => array(
            'AD_TYPE' => json_encode($this->ad_type),
            'FORM_TITLE' => $form_title, 
            'VOLUME_TITLE' => type_to_str($volume_title,'encode'), 
            'ID_CHANNEL' => $id_channel, 
            'CHANNEL_NAME' => type_to_str($channel_info->name,'encode')),           
         "fields"      => array(                     
            "title" => array(
               "display_name"     => __("Title"),
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "required",
               "max"              => 35
            ),
            "volume" => array(
               "display_name"     => __($volume_title),               
               "id_field_type"    => "string",
               "form_field_type"  => "text",
               "validation_rules" => "trim|required|integer|positive",
               "max"              => 9                       
            ),
            "type" => array(              
               "id_field_type"    => "string",
               "form_field_type"  => "hidden",
               "validation_rules" => "required",
               "default"          => $program_type                     
            ),
            "id_channel" => array(              
               "id_field_type"    => "int",
               "form_field_type"  => "hidden",
               "validation_rules" => "required|integer",
               "default"          => $id_channel                    
            )     
                      
         ) 
      );
      
      $adTypeLabels = array();
      if (in_array(Sppc_Channel::AD_TYPE_TEXT, $channel_info->ad_type)) {
     	$form['fields']['cost_text'] = array(
     		"display_name"     => __("Cost Text Ad"),
     		"id_field_type"    => "float",
     		"form_field_type"  => "text",
     		"validation_rules" => "required|float[2]|positive"
     	);
     	
     	$adTypeLabels[] = __('Text');
     	$form['vars']['COST_IMAGE_AD_TEXT'] = '';
      }
      
      if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $channel_info->ad_type)) {
      	$form['fields']['cost_image'] = array(
      		"display_name"     => __("Cost Image Only Ad"),
      		"id_field_type"    => "float",
      		"form_field_type"  => "text",
      		"validation_rules" => "required|float[2]|positive"
      	);
      	
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $channel_info->ad_type)) {
      		$form['fields']['cost_image']['validation_rules'] = null;
      	}
      	
      	$adTypeLabels[] = __('Image');
      	$form['vars']['COST_IMAGE_AD_TEXT'] = __('Cost Image Only Ad');	
      }
      
      switch ($mode) {
         case 'create':
            $form['vars']['APPLY_BTN_CAPTION'] = __("Create");
            $form['redirect'] = $this->role."/edit_channel_program/create_complete/".$id_channel;
         break;
         case 'modify':
            $form['vars']['APPLY_BTN_CAPTION'] = __("Save");
            $form['redirect'] = $this->role."/edit_channel_program/edit_complete/".$id_channel;
         break;
      }
      
      if (!in_array(Sppc_Channel::AD_TYPE_TEXT, $channel_info->ad_type)) {
      	 $this->max_ad_slots = 1;
      	 $slotsPreview = 'slots_'.$channel_info->id_dimension.'_1';
      } else {
      	 $this->max_ad_slots = $channel_info->max_ad_slots; 
      	 $slotsPreview = 'slots_'.$channel_info->id_dimension;
      }
      
      $form['vars']['AD_TYPE_STRING'] = implode(', ', $adTypeLabels);
      $form['vars']['AD_TYPE'] = json_encode($channel_info->ad_type);
      $form['vars']['CHANNEL_DIMENSIONS'] = $channel_info->width.'&times;'.$channel_info->height;
      $form['vars']['DIMENSION_NAME'] = $channel_info->dimension_name;
      $form['vars']['ID_DIMENSION'] = $channel_info->id_dimension;
      $form['vars']['SLOTS_PREVIEW'] = $slotsPreview;
      $form['vars']['MAX_AD_SLOTS'] = $this->max_ad_slots;
      $form['vars']['MONEYFORMAT'] = get_money_format();
      $form['vars']['NUMBERFORMAT'] = get_number_format();
      
      $this->_set_content($this->form->get_form_content($mode, $form, $this->input, $this));
      $this->_display();
   } //end index
   
   /**
    * Отображение сообщения об успешном создании канала
    *
    */
   public function create_complete($id_channel) {
      $data = array(
         'MESSAGE' => __('Program was created successfully'),
         'REDIRECT' => $this->site_url.$this->index_page.$this->role.'/manage_channel_prices/index/'.$id_channel
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }
   
   /**
    * Отображение сообщения об успешном редактировании канала
    *
    */
   public function edit_complete($id_channel) {
      $data = array(
         'MESSAGE' => __('Program was edited successfully'),
         'REDIRECT' => $this->site_url.$this->index_page.$this->role.'/manage_channel_prices/index/'.$id_channel
      );
      $content = $this->parser->parse('common/infobox.html',$data,FALSE);
      $this->_set_content($content);
      $this->_display();
   }
   
   /**
    * обновление данных программы канала
    *
    * @param array $fields параметры сохраняемой программы
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _save($id, $fields) {
   	$volume_is_changed = false;
      $text_cost_is_changed = false;
      $image_cost_is_changed = false;
   	
      $program_info = $this->channel_program->get_info($id);
      if (is_null($program_info)) {
      	 return "Unknown program";
      }
      
      $params = array(
      	 'title' => $fields['title'],
      	 'program_type' => $fields['type'],
      	 'volume' => $fields['volume']
      );
      
      if (in_array(Sppc_Channel::AD_TYPE_TEXT, $this->ad_type)) {
      	$params['cost_text'] = type_to_str($fields['cost_text'],'mysqlfloat');
      	if ('CPM' == $fields['type']) {
      		$params['avg_cost_text'] = type_to_str(1000*$fields['cost_text']/$fields['volume'],'mysqlfloat');
      	} else {
      		$params['avg_cost_text'] = type_to_str($fields['cost_text']/$fields['volume'],'mysqlfloat');
      	}
      	$text_cost_is_changed = ($program_info->cost_text != $fields['cost_text']);
      }

      if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $this->ad_type)) {
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $this->ad_type)) {
      		$cost_image = $fields['cost_text']*$this->max_ad_slots; 
      	} else {
      		$cost_image = $fields['cost_image'];
      	}
      	
      	$params['cost_image'] = type_to_str($cost_image,'mysqlfloat');
      	
      	if ('CPM' == $fields['type']) {
      		$params['avg_cost_image'] = type_to_str(1000*$cost_image/$fields['volume'],'mysqlfloat'); 	
      	} else {
      		$params['avg_cost_image'] = type_to_str($cost_image/$fields['volume'],'mysqlfloat'); 
      	}
      	
      	$image_cost_is_changed = ($program_info->cost_image != $cost_image);
      }
      
      $volume_is_changed = ($program_info->volume != $fields['volume']);
      
      return $this->channel_program->update($id, $params);
   } 
   
   /**
    * Создание платежной программы
    *
    * @param array $fields параметры создаваемой платежной программы
    * @return string описание ошибки ('' при успешном создании)
    */
   public function _create($fields) {
      $params = array(
      	'title' => $fields['title'],
      	'id_channel' => $fields['id_channel'],
      	'program_type' => $fields['type'],
      	'volume' => $fields['volume']
      );

      if (in_array(Sppc_Channel::AD_TYPE_TEXT, $this->ad_type)) {
      	$params['cost_text'] = type_to_str($fields['cost_text'],'mysqlfloat');
      	if ('CPM' == $fields['type']) {
      		$params['avg_cost_text'] = type_to_str(1000*$fields['cost_text']/$fields['volume'],'mysqlfloat');
      	} else {
      		$params['avg_cost_text'] = type_to_str($fields['cost_text']/$fields['volume'],'mysqlfloat');
      	}
      }

      if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $this->ad_type)) {
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $this->ad_type)) {
      		$cost_image = $fields['cost_text']*$this->max_ad_slots; 
      	} else {
      		$cost_image = $fields['cost_image'];
      	}
      	
      	$params['cost_image'] = type_to_str($cost_image,'mysqlfloat');
      	
      	if ('CPM' == $fields['type']) {
      		$params['avg_cost_image'] = type_to_str(1000*$cost_image/$fields['volume'],'mysqlfloat'); 	
      	} else {
      		$params['avg_cost_image'] = type_to_str($cost_image/$fields['volume'],'mysqlfloat'); 
      	}
      }
      
      return $this->channel_program->create($params);
   } 
   
   public function _validator($fields){
      /**
       * Проверяет максимальное значение продолжительности капмании в днях.
       * Для флэт рэйт и только для 32х битных систем.
       */
      if((0x7FFFFFFF > (int)(0x7FFFFFFF+1)) &&
            isset($fields['type']) && $fields['type'] == 'flat_rate'){
               
               $t_current = time();
               $t_max = mktime(0,0,0,1,18,2038);
               $t_period = $fields['volume'] * 86400;
               if($t_current + $t_period > $t_max){
                  $max_period = floor(($t_max - $t_current) / 86400 );
                  $this->validation->volume_error = '<p class=\'errorP\'>Days max value is ' . $max_period . '</p>';
                  return false;   
               }          
      }
      
      return true;
   }     
   
   /**
    * Загрузка данных платежной программы
    *
    * @param int $id параметры создаваемого канала
    * @return string описание ошибки ('' при успешной загрузке)
    */
   public function _load($id) {
      $program_info = $this->channel_program->get_info($id);
      
      if (!is_null($program_info)) { 
         return array(
         	'title' => $program_info->title,
         	'id_channel' => $program_info->id_channel,
         	'cost_text' => $program_info->cost_text,
         	'cost_image' => $program_info->cost_image,
         	'volume' => $program_info->volume
         );
      } else {
         return "Channel program is not found";
      }
   } 
}