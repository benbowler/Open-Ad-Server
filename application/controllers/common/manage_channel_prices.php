<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Контроллер настроек цен канала
 *
 * @author Немцев Андрей
 * @project SmartPPC 6
 * @version 1.0.0
 */
class Common_Manage_Channel_Prices extends Parent_controller {
	
	protected $id_channel = null;
	
	protected $channel_info = NULL;
	
	protected $menu_item = "Manage Sites/Channels";

	protected $action = "show_list"; //Действие над списком цен, которое осуществляет пользователь: show_list, delete, create
	
	/**
	 * Конструктор контроллера настроек цен
	 *
	 */
	public function __construct() {
	   $this->temporary = array ('manage_channel_prices_cpm_sort_direction' => 'asc',
                                'manage_channel_prices_flat_rate_sort_direction' => 'asc'
                              );
		parent::__construct ();
      
      $this->load->model('channel');
      $this->load->model('channel_program');
      
	}
	
	/**
	 * Вызывает функцию соответствующую действию пользователя по управлению списком цен c последующим отображением списка 
	 *
	 * @return ничего не возвращает
	 */
	public function index($id_channel = null) {
	   $this->id_channel = $id_channel;
	   
	   $error_flag = false;
       $error_message = '';
	   
       if (is_null($id_channel)) {
       	  $error_flag = true;
       	  $error_message = 'Channel is not specified';
       }
       
      $id_owner = $this->channel->get_channel_owner($id_channel);
      $this->admin_view = ($this->user_id != $id_owner) && ($this->role == 'admin');
       
	   if ((!$error_flag) && ($this->user_id != $id_owner) && $this->role != 'admin') {
	  	  $error_flag = true;
       	  $error_message = 'Access denied';
	   }
	   
	   if  ($error_flag) {
      		    $data = array(
	            'MESSAGE' => __($error_message),
	            'REDIRECT' => $this->site_url .$this->index_page. $this->role . '/manage_sites_channels'
	         );
	         $content = $this->parser->parse('common/errorbox.html',$data,FALSE);
	         $this->_set_content($content);
	         $this->_display();
	         return;
      }
	   
		$this->action = $this->input->post ( 'action' );
		switch ( $this->action) {
		   case 'create_CPM' :
				redirect($this->role.'/edit_channel_program/create/'.$id_channel.'/CPM/');
			break;
			case 'create_Flat_Rate' :
            redirect($this->role.'/edit_channel_program/create/'.$id_channel.'/Flat_Rate/');
         break;
			case 'delete' :
				$this->delete ();
			break;
		}
		
		$this->action = "show_list";
		$this->show_list ();
	}
	
	/**
	 * Удаление ценовых программ
	 *
	 * @return ничего не возвращает
	 */
	protected function delete() {
		$success_flag = true;
		$id_programs = $this->input->post ( 'id_program' );
		if (is_array ( $id_programs )) {
		   foreach ($id_programs as $id_program) {
		       if (is_numeric($id_program)) {
		          $success_flag &= $this->channel_program->delete ( $id_program, $this->user_id);
		       }
		   }
		}

		if ($success_flag) {
			$this->_set_notification('Program(s) were succesfully deleted.');
		} else {
			$this->_set_notification('Some programs were not deleted due to be used in campaign(s).','error');
		}
	}
	
	/**
	* возвращает HTML-код таблицы со списком минимальных бидов сайтов канала
	*
	* @param integer $id_channel код канала
	* @return string HTML-код таблицы
	*/
	public function bid_table($id_channel) {
      $this->load->library ( 'Table_Builder' );
      $this->table_builder->clear ();
      $this->table_builder->insert_empty_cells = FALSE;
      $this->table_builder->init_sort_vars('bid_table', 's.id_site', 'asc');
      
	   $col_index = 0;
      $col_alias = array(
         'id_site' => $col_index++,
         'name' => $col_index++
      );
      
      $this->table_builder->sorted_column($col_alias['id_site'], 's.id_site', 'ID', 'asc');
      $this->table_builder->sorted_column($col_alias['name'], 'name', 'Site', 'asc');
      $this->table_builder->add_col_attribute($col_alias['id_site'], 'class', 'w20 center');
      $this->table_builder->add_col_attribute($col_alias['name'], 'class', 'left');
      
      $this->table_builder->add_row_attribute(0, 'class', 'th');
      $this->table_builder->add_attribute ('class', 'xTable');
      $this->load->model('channel', '', TRUE);
      $bid_list = $this->channel->bid_table(
         $id_channel,            
         $this->table_builder->sort_field,
         $this->table_builder->sort_direction);
      $row = 1;
      foreach ($bid_list as $id_site => $bid_row) {
         $this->table_builder->set_cell_content ($row, $col_alias['id_site'], $id_site);
         $this->table_builder->set_cell_content ($row, $col_alias['name'],
            type_to_str($bid_row['name'], 'encode')." (<a href='http://".$bid_row['url']."'>".$bid_row['url']."</a>)");
         
         $row++;      
      }
      if (1 == $row) {
         $this->table_builder->set_cell_content (1, 0,__('Records not found'));
         $this->table_builder->cell(1, 0)->add_attribute('colspan', count($col_alias));
         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
      }      
      return $this->table_builder->get_sort_html();       
	} //end bid_table	
	
private function build_programs_table($program_type = NULL) {
      
      if (is_null($program_type)) return '';
      
      $this->table_builder->clear ();
      $this->table_builder->insert_empty_cells = false;
      
      $col_index = 0;
      $col_alias = array(
         'checkbox' => $col_index++,
         'id' => $col_index++,
         'title' => $col_index++,
         'volume' => $col_index++
      );
      
      $this->table_builder->init_sort_vars("manage_channel_{$program_type}_programs", 'id', 'desc');
      $this->table_builder->sorted_column($col_alias['id'],'id','ID','asc');
      $this->table_builder->sorted_column($col_alias['title'],'title','Title','asc');
      
      switch ($program_type) {
         case 'CPM':
            $volume_title = 'Impressions';
         break;
         case 'Flat_Rate':
            $volume_title = 'Days';
         break;
      }
      
      
      $this->table_builder->sorted_column($col_alias['volume'],'volume',$volume_title,'asc');
      
      switch ($this->channel_info->ad_type) {
         case "text":
           $col_alias['cost_text'] = $col_index++;
           $col_alias['avg_cost_text'] = $col_index++;
           
           switch ($program_type) {
             case 'CPM':
               $this->table_builder->sorted_column($col_alias['avg_cost_text'],'avg_cost_text','Price per 1K','asc',1);
             break;
             case 'Flat_Rate':
               $this->table_builder->sorted_column($col_alias['avg_cost_text'],'avg_cost_text','Price per day','asc',1);
             break;
           }
           $this->table_builder->sorted_column($col_alias['cost_text'],'cost_text','Price','asc',1);
           $this->table_builder->add_col_attribute($col_alias['cost_text'], 'class', 'w100 right');
           $this->table_builder->add_col_attribute($col_alias['avg_cost_text'], 'class', 'w100 right');
           
           $this->table_builder->set_cell_content(0,$col_alias['cost_text'],__('Text Ads'));
           $this->table_builder->cell(0,$col_alias['cost_text'])->setColspan(2);
         break;
         case "image":
           $col_alias['cost_image'] = $col_index++;
           $col_alias['avg_cost_image'] = $col_index++;
           switch ($program_type) {
             case 'CPM':
               $this->table_builder->sorted_column($col_alias['avg_cost_image'],'avg_cost_image','Price per 1K','asc',1);
             break;
             case 'Flat_Rate':
               $this->table_builder->sorted_column($col_alias['avg_cost_image'],'avg_cost_image','Price per day','asc',1);
             break;
           } 
           $this->table_builder->sorted_column($col_alias['cost_image'],'cost_image','Price','asc',1);
           $this->table_builder->add_col_attribute($col_alias['cost_image'], 'class', 'w100 right');
           $this->table_builder->add_col_attribute($col_alias['avg_cost_image'], 'class', 'w100 right');
           
           $this->table_builder->set_cell_content(0,$col_alias['cost_image'],__('Image Ads'));
           $this->table_builder->cell(0,$col_alias['cost_image'])->setColspan(2);
         break;
         case "text,image":
           $col_alias['cost_text'] = $col_index++;
           $col_alias['avg_cost_text'] = $col_index++;
           $col_alias['cost_image'] = $col_index++;
           $col_alias['avg_cost_image'] = $col_index++; 
           switch ($program_type) {
             case 'CPM':
               $this->table_builder->sorted_column($col_alias['avg_cost_image'],'avg_cost_image','Price per 1K','asc',1);
               $this->table_builder->sorted_column($col_alias['avg_cost_text'],'avg_cost_text','Price per 1K','asc',1);
             break;
             case 'Flat_Rate':
               $this->table_builder->sorted_column($col_alias['avg_cost_image'],'avg_cost_image','Price per day','asc',1);
               $this->table_builder->sorted_column($col_alias['avg_cost_text'],'avg_cost_text','Price per day','asc',1);
             break;
           } 
           $this->table_builder->sorted_column($col_alias['cost_text'],'cost_text','Price','asc',1);
           $this->table_builder->sorted_column($col_alias['cost_image'],'cost_image','Price','asc',1);
           $this->table_builder->add_col_attribute($col_alias['cost_text'], 'class', 'w100 right');
           $this->table_builder->add_col_attribute($col_alias['avg_cost_text'], 'class', 'w100 right');
           $this->table_builder->add_col_attribute($col_alias['cost_image'], 'class', 'w100 right');
           $this->table_builder->add_col_attribute($col_alias['avg_cost_image'], 'class', 'w100 right');
           
           $this->table_builder->set_cell_content(0,$col_alias['cost_text'],__('Text Ads'));
           $this->table_builder->set_cell_content(0,$col_alias['cost_image'],__('Image Ads'));
           $this->table_builder->cell(0,$col_alias['cost_text'])->setColspan(2);
           $this->table_builder->cell(0,$col_alias['cost_image'])->setColspan(2);
         break;
      }
      
      if (!$this->admin_view) {
         $col_alias['action'] = $col_index++;
      }
      
      //добавление ячеек-заголовка
      $this->table_builder->set_cell_content ( 0, $col_alias['checkbox'], array ('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'manage_channel_'.$program_type.'_programs\', this)"' ), 'checkbox' );     

      if (!$this->admin_view) {
         $this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );
      }

      
      foreach($col_alias as $key => $field_index) {
         if (!in_array($key,array('cost_text','cost_image','avg_cost_text','avg_cost_image'))) {
           $this->table_builder->cell(0,$field_index)->setRowspan(2);
         }
      }
      
      //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($col_alias['checkbox'], 'class', 'chkbox');
      $this->table_builder->add_col_attribute($col_alias['id'], 'class', 'w20 center');
      $this->table_builder->add_col_attribute($col_alias['title'], 'class', 'center');
      $this->table_builder->add_col_attribute($col_alias['volume'], 'class', 'w100 right');

      if (!$this->admin_view) {
         $this->table_builder->add_col_attribute($col_alias['action'], 'class', 'w100 nowraper center');
      }
      
      
      $this->table_builder->add_row_attribute(0,'class', 'th');
      $this->table_builder->add_row_attribute(1,'class', 'th');
      
      //установка атрибутов таблицы
      $this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
      
      $params = array ('fields' => 'id_program as id, title, volume, cost_text, avg_cost_text, cost_image, avg_cost_image',
            'order_by' => $this->table_builder->sort_field, 
            'order_direction' => $this->table_builder->sort_direction,
            'program_filter' => $program_type,
            'id_channel' => $this->id_channel);
      $programs_array = $this->channel_program->get_list($params);
      
      if (is_null($programs_array)) {
         $programs_array = array();
      }
            
      //$this->table_builder->add_from_array ($programs_array);     
                  
      $data_rows_conut = sizeof ( $programs_array );
            
      //модификация контента отдельных столбцов (ссылки, чекбоксы)
      
      $row = 2;
      for($i = 0; $i < $data_rows_conut; $i ++) {
      	      
               if ($this->admin_view) {
                  $this->table_builder->set_cell_content ( $row, $col_alias['checkbox'], '');
               } else {
                  $this->table_builder->set_cell_content ( $row, $col_alias['checkbox'], array ('name' => 'id_program[]', 'value' => $programs_array [$i] ['id'], 'extra' => 'id=chk'.$i.' onclick="checktr(\'chk'.$i.'\',\'tr'.($i+1).'\')"'), 'checkbox' );
               }
               $this->table_builder->set_cell_content ( $row, $col_alias['id'], $programs_array [$i] ['id']);
               $this->table_builder->set_cell_content ( $row, $col_alias['title'], type_to_str($programs_array [$i] ['title'],'encode'));
               $this->table_builder->set_cell_content ( $row, $col_alias['volume'], type_to_str($programs_array [$i] ['volume'], 'integer'));
               
               switch ($this->channel_info->ad_type) {
                  case "text":
                    $this->table_builder->set_cell_content ( $row, $col_alias['cost_text'], type_to_str($programs_array [$i] ['cost_text'], 'money'));
                    $this->table_builder->set_cell_content ( $row, $col_alias['avg_cost_text'], type_to_str($programs_array [$i] ['avg_cost_text'], 'money'));
                  break;
                  case "image":
                    $this->table_builder->set_cell_content ( $row, $col_alias['cost_image'], type_to_str($programs_array [$i] ['cost_image'], 'money'));
                    $this->table_builder->set_cell_content ( $row, $col_alias['avg_cost_image'], type_to_str($programs_array [$i] ['avg_cost_image'], 'money'));
                  break;
                  case "text,image":
                    $this->table_builder->set_cell_content ( $row, $col_alias['cost_text'], type_to_str($programs_array [$i] ['cost_text'], 'money'));
                    $this->table_builder->set_cell_content ( $row, $col_alias['avg_cost_text'], type_to_str($programs_array [$i] ['avg_cost_text'], 'money'));
                    $this->table_builder->set_cell_content ( $row, $col_alias['cost_image'], type_to_str($programs_array [$i] ['cost_image'], 'money'));
                    $this->table_builder->set_cell_content ( $row, $col_alias['avg_cost_image'], type_to_str($programs_array [$i] ['avg_cost_image'], 'money'));
                  break;
               }

               if (!$this->admin_view) {
                  $this->table_builder->set_cell_content ( $row, $col_alias['action'], array ('name' => __('Edit'), 'extra' => 'class="guibutton floatl ico ico-edit" value="{@Edit@}" title="{@Edit@}" onclick="top.location=\'' . $this->site_url .$this->index_page. $this->role . '/edit_channel_program/edit/' . $programs_array[$i]['id'] . '\'"', 'href' => $this->site_url .$this->index_page. $this->role . '/edit_channel_program/edit/' . $programs_array [$i] ['id'] ), 'link' );
               }
               
               $this->table_builder->add_row_attribute( $row, 'id', 'tr'.$row);
               $row++;
            }
      
      if (0 == $data_rows_conut) {
            $this->table_builder->set_cell_content ($row, 0,__('Records not found'));
         
            $this->table_builder->cell($row, 0)->add_attribute('colspan',count($col_alias));
         
            $this->table_builder->cell($row, 0)->add_attribute('class', 'nodata');
            $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
            $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      }
      
      $program_table = $this->table_builder->get_sort_html(); 
      
      switch ($program_type) {
         case 'CPM':
            $form_title = __('CPM prices');
         break;
         case 'Flat_Rate':
            $form_title = __('Flat Rate prices');
         break;
      }
      
      $form_data = array ("name" => $program_type."_program_form",
                     "vars" => array(
                                     'PROGRAM_TYPE' => $program_type,
                                     'FORM_TITLE' => $form_title,
                                     'TABLE' => $program_table,
                                     'HIDE' => ''),
                     "view" => "common/manage_channel_prices/list.html",
                     "fields" => array());
      if ($this->admin_view) {         
         $form_data['kill'] = array('eb');
         $form_data['vars']['HIDE'] = 'class="hide"';
      }      
      
      return $this->form->get_form_content ( "create", $form_data, $this->input, $this );
   }
	
	
	/**
	 * Отображение списка платежных программ канала
	 *
	 * @return ничего не возвращает
	 */
	protected function show_list() {
		$this->load->library ( 'Table_Builder' );
		$this->load->library ( "form" );
      	$this->channel_info = $this->channel->get_info($this->id_channel);
      
      	$cpm_form = $this->build_programs_table('CPM');
      	$flat_rate_form = $this->build_programs_table('Flat_Rate');
      
      	$channelAdTypes = explode(',', $this->channel_info->ad_type);
      	$channelAdTypesLabels = array();
      	
      	if (in_array(Sppc_Channel::AD_TYPE_TEXT, $channelAdTypes)) {
      		$channelAdTypesLabels[] = __('Text');
      	}
		if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $channelAdTypes)) {
      		$channelAdTypesLabels[] = __('Image');
      	}
      	
      	if (!in_array(Sppc_Channel::AD_TYPE_TEXT, $channelAdTypes)) {
      		$slotsPreview = 'slots_'.$this->channel_info->id_dimension.'_1';
      		$maxAdSlots = 1; 
      	} else {
      		$slotsPreview = 'slots_'.$this->channel_info->id_dimension;
      		$maxAdSlots = $this->channel_info->max_ad_slots; 
      	}
      
      	$content = $this->parser->parse(
      		'common/manage_channel_prices/programs_list_body.html', 
	      	array(
		      	'CHANNEL_NAME' => type_to_str($this->channel_info->name,'encode'),
		      	'CPM_FORM' => $cpm_form, 
		      	'FLAT_RATE_FORM' => $flat_rate_form,
		      	'AD_TYPE_STRING' => implode(', ', $channelAdTypesLabels),
		      	'CHANNEL_DIMENSIONS' => $this->channel_info->width.'&times;'.$this->channel_info->height,
		      	'MAX_AD_SLOTS' => $maxAdSlots,
		      	'ID_DIMENSION' => $this->channel_info->id_dimension,
	         	'SLOTS_PREVIEW' => $slotsPreview,
		      	'AD_TYPE' => json_encode($channelAdTypes),
		      	'DIMENSION_NAME' => $this->channel_info->dimension_name,
	         	'ROLE' => $this->role
	      	),
	      	TRUE
	 	);
	 	
      	$this->_set_content($content);
		$this->_display ();
	}
} //end class Manage_channel_prices

?>