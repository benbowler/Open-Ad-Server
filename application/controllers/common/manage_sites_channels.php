<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
   exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

class Common_Manage_Sites_Channels extends Parent_controller {

   protected $menu_item = "Manage Sites/Channels";

   protected $date_picker = TRUE;

   protected $date_range;

   protected $views_paths = array(); //пути к шаблонам в зависимости от роли

   
   protected $_hooks = array();

   public function __construct() {
   	$this->temporary = array (
	      'manage_sites_channels_from' => 'select',
	      'manage_sites_channels_to'   => 'today',
	      'manage_sites_channels_status_filter' => 'all',
   		'manage_sites_channels_quicksearch' => '',
   	   'manage_sites_columns' => 'all',
   	   'manage_channels_columns' => 'all',
   	   'manage_sites_channels_channel_columns' => 'all',
   	   'manage_sites_channels_site_columns' => 'all'
      );
      parent::__construct ();


      $this->load->model('locale_settings');
      $this->load->model( 'pagination_post' );
      $this->load->model('site');
      $this->load->model('channel');

      $this->load->helper('periods');
      $this->load->library('Table_Builder');
      $this->load->library('form');

      $this->load->model("entity");
      
   }
   
   public function index() {
   	  
   	$this->_add_ajax();
         	switch ($this->input->post('manage_action')) {
         		case 'pause_site':
         		 $this->pause_sites();
         		 $this->_set_notification('Sites were paused successfully');
         		break;
         		case 'resume_site':
                $this->resume_sites();
                $this->_set_notification('Sites were resumed successfully');
               break;
               case 'delete_site':
                $this->delete_sites();
               break;
               case 'activate_site':
               	if ($this->role == 'admin') {
	               	$this->confirm_sites();
	               	$this->_set_notification('Sites were confirmed successfully');
               	}
               break;
               case 'deny_site':
               	if ($this->role == 'admin') {
               		$this->deny_sites();
               		$this->_set_notification('Sites were denied successfully');
               	}
               break;
         	};
      $this->show_list('sites_to_channels');
   } //end index()

   public function create_site() {
      redirect($this->role.'/edit_site');
   }

   public function edit_site($id_site = null) {
      redirect($this->role.'/edit_site/'.$id_site);
   }

   protected function delete_sites() {
      $id_site = $this->input->post ( 'id_site' );
      $totalSiteCount = 0;
      $deletedSitesCount = 0;
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
             	$totalSiteCount++;
                if (true == $this->site->delete ( $id , $this->user_id)) {
                	$deletedSitesCount++;
                }
             }
         }
      }
      
      if ($totalSiteCount == 0) {
      	 $this->_set_notification('No sites were selected', 'error');
      } elseif (($totalSiteCount > $deletedSitesCount) && ($deletedSitesCount > 0)) {
      	 $notificationMsg = sprintf(__('%u site(s) were deleted successfully'), $deletedSitesCount);
      	 $notificationMsg .= '<br />';
      	 $notificationMsg .= sprintf(__('%u site(s) were not removed'), ($totalSiteCount - $deletedSitesCount));
      	 $this->_set_notification($notificationMsg, 'error');
      } elseif (($totalSiteCount > $deletedSitesCount) && ($deletedSitesCount == 0)) { 
         $this->_set_notification('No sites were deleted', 'error');
      } else {
      	 $this->_set_notification('Sites were deleted successfully');
      }
   }

   protected function pause_sites() {
      $id_site = $this->input->post ( 'id_site' );
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
                $this->site->pause ( $id , $this->user_id);
             }
         }
      }
   }

   protected function resume_sites() {
      $id_site = $this->input->post ( 'id_site' );
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
                $this->site->resume ( $id , $this->user_id);
             }
         }
      }
   }
   
   protected function confirm_sites() {
   	  $id_site = $this->input->post('id_site');
   	  if (is_array($id_site)) {
   	  	 foreach($id_site as $id) {
   	  	 	if (is_numeric($id)) {
   	  	 		$this->site->confirm($id);
   	  	 	}
   	  	 }
   	  }
   }
   
   protected function deny_sites() {
   	  $id_site = $this->input->post('id_site');
   	  if (is_array($id_site)) {
   	  	  foreach($id_site as $id) {
   	  	  	 if (is_numeric($id)) {
   	  	  	 	$this->site->deny($id);
   	  	  	 }
   	  	  }
   	  }
   }

   protected function pause_channels() {
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->channel->pause ( $id , $this->user_id);
             }
         }
      }
   }

   protected function resume_channels() {
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->channel->resume ( $id , $this->user_id);
             }
         }
      }
   }

   protected function delete_channels() {
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->channel->delete ( $id , $this->user_id);
             }
         }
      }
   } //end delete_channels()

   protected function show_list($active_tab) {

      // Add plugins to hook
      $this->add_plugins($active_tab);
      
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      $hookObjects = array();
      
      if (isset($pluginsConfig->common->manage_site_channels)) {
         foreach ($pluginsConfig->common->manage_site_channels as $hookClass) {
            $hookObject = new $hookClass;
            if ($hookObject instanceof Sppc_Common_ManageSiteChannels_Interface) {
                 $hookObjects[] = $hookObject;
            }
         }
      }
      $additionalButtons = '';
      $additionalJSSiteButtons = '';
      foreach($hookObjects as $hookObject) {
         $additionalButtons .= $hookObject->registerAdditionalButtons();
         $additionalJSSiteButtons .= $hookObject->registerJSSiteButtons(array($this, $this->role));
      }
      
   	//режим фильтрации по статусу сайта/канала
      $status_filter = $this->input->post ( 'status_filter' );
      if (! $status_filter) {
         $status_filter = $this->temporary ['manage_sites_channels_status_filter'];
      }
      $this->temporary ['manage_sites_channels_status_filter'] = $status_filter;
      $quicksearch = $this->input->post('quicksearch');
      if (false === $quicksearch) {
      	 $quicksearch = $this->temporary['manage_sites_channels_quicksearch'];
      }
      $this->temporary['manage_sites_channels_quicksearch'] = $quicksearch;
      
      
   	$form = array(
         'id' => 'sites_channels',
         'view' => $this->views_paths['body'],
         'redirect' => $this->role.'/manage_sites_channels',
         'vars' => array(
            'DATEFILTER' => period_html('manage_sites_channels'),
   	        'ADDITIONAL_BUTTONS' => $additionalButtons,
            'ADDITIONAL_JS_SITE_BUTTONS' => $additionalJSSiteButtons,
   			'QUICK_SEARCH_FIELDS' => ''
         ),
         'fields' => array(
            'status_filter' => array(
               'id_field_type' => 'string',
               'form_field_type' => 'select',
               'options' => array(
                  'all' => __('all'),
                  'active' => __('pl_active'),
                  'paused' => __('pl_blocked'),
                  'deleted' => __('pl_deleted'),
               ),
               'default' => $status_filter
            ),
            'quicksearch' => array(
            	'id_field_type' => 'string',
            	'form_field_type' => 'text',
            	'default' => $quicksearch
            )
         )
      );
      if($this->role == 'publisher') {
         $form['kill'] = array('owner');
      }

      data_range_fields($form, $this->temporary['manage_sites_channels_from'], $this->temporary['manage_sites_channels_to']);

      /*switch ($active_tab) {
      	case 'sites_to_channels': //Вкладка сайтов:
      */		
      		$quickSearchFields = array(
      			__('ID'),
      			__('Title'),
      			__('Site URL')
      		);
      		$form['vars']['QUICK_SEARCH_FIELDS'] = implode(', ', $quickSearchFields);

      		$form['vars']['TYPE'] = 'sites';
      		$form['name'] = 'manage_sites_form';
      		$html = $this->form->get_form_content('modify', $form, $this->input, $this);

         	$this->pagination_post->clear();
            $this->pagination_post->set_form_name('manage_sites');
            $total_params = array('status'        => $status_filter,
            				'date_filter' =>  $this->date_range,
            				'revenue_field' => $this->revenue_field,
            				'id_entity' => $this->user_id,
            				'quicksearch' => $quicksearch);
            $total = $this->site->get_count($total_params);
            
            $this->pagination_post->set_total_records ($total['count']);
            $this->pagination_post->read_variables('manage_sites', 1, 10);

            //настройка параметров разбиения на страницы
            $pagination = $this->pagination_post->create_form ();

            $col_index = 0;
            $col_alias = array('chkboxes' => $col_index++,
                                  'togglers' => $col_index++,
                                  'id' => $col_index++);
            $col_alias['url'] = $col_index++;
            $col_alias['name'] = $col_index++;
            $col_alias['status'] = $col_index++;
            $col_alias['impressions'] = $col_index++;
            $col_alias['alternative_impressions'] = $col_index++;
            $col_alias['clicks'] = $col_index++;
            $col_alias['ctr'] = $col_index++;
            $col_alias['revenue'] = $col_index++;
            $col_alias['action'] = $col_index++;
            
            // add addtional columns to column map from plugins
            foreach($this->_hooks as $hookObj) {
            	$col_alias = $hookObj->extendColumnMap($col_alias);
            }
            
            $this->table_builder->clear ();
            $this->table_builder->init_sort_vars('manage_sites', 'id', 'desc');
            $this->table_builder->sorted_column($col_alias['id'],'id','ID','asc');
            
            $this->table_builder->sorted_column($col_alias['name'],'sites.name','Title','asc');
            $this->table_builder->sorted_column($col_alias['url'],'url','Site URL','asc');
            $this->table_builder->sorted_column($col_alias['status'],'status','Status','asc');
            $this->table_builder->sorted_column($col_alias['impressions'],'impressions','Impressions','desc');
            $this->table_builder->sorted_column($col_alias['alternative_impressions'],'alternative_impressions','Alternative Impressions','desc');
            $this->table_builder->sorted_column($col_alias['clicks'],'clicks','Clicks','desc');
            $this->table_builder->sorted_column($col_alias['ctr'],'ctr','CTR','desc');
            $this->table_builder->sorted_column($col_alias['revenue'],'revenue','Revenue','desc');

            //добавление ячеек-заголовка
            $this->table_builder->set_cell_content ( 0, $col_alias['chkboxes'], array ('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'manage_sites_channels\', this)"' ), 'checkbox' );
            $this->table_builder->set_cell_content ( 0, $col_alias['togglers'], '&nbsp');
            $this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );

            // create additional columns from plugins
            foreach($this->_hooks as $hookObj) {
            	$hookObj->createColumns($col_alias, $this->table_builder);
            }
            
            $this->table_builder->cell(0,$col_alias['chkboxes'])->add_attribute('class', 'simpleTitle');
            $this->table_builder->cell(0,$col_alias['togglers'])->add_attribute('class', 'simpleTitle');
            $this->table_builder->cell(0,$col_alias['action'])->add_attribute('class', 'simpleTitle');

            //прописывание стилей для ячеек
            $this->table_builder->add_col_attribute($col_alias['chkboxes'], 'class', '"chkbox"');
            $this->table_builder->add_col_attribute($col_alias['togglers'], 'class', '"chkbox"');
            $this->table_builder->add_col_attribute($col_alias['id'], 'class', '"w20"');
            $this->table_builder->add_col_attribute($col_alias['status'], 'class', '"w80 center"');
            $this->table_builder->add_col_attribute($col_alias['impressions'], 'class', '"w80 right"');
            $this->table_builder->add_col_attribute($col_alias['alternative_impressions'], 'class', '"w80 right"');
            $this->table_builder->add_col_attribute($col_alias['clicks'], 'class', '"w50 right"');
            $this->table_builder->add_col_attribute($col_alias['ctr'], 'class', '"w50 right nowrap"');
            $this->table_builder->add_col_attribute($col_alias['revenue'], 'class', '"w100 center"');
            $this->table_builder->add_col_attribute($col_alias['action'], 'class', '"center nowrap"');

		    // add styles for additional columns from plugins
		    foreach($this->_hooks as $hookObj) {
		       $hookObj->defineColumnStyles($col_alias, $this->table_builder);
		    }

            $this->table_builder->add_row_attribute(0,'class', 'th');

            //установка атрибутов таблицы
            $this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here

            // Устанавливаем возможность выбора колонок
            $this->table_builder->use_select_columns();
            $invariable_columns = array(
               $col_alias['togglers'], $col_alias['id'], $col_alias['url'], $col_alias['action']
            );
            $this->table_builder->set_invariable_columns($invariable_columns);
            

            //$periods = period_load('manage_sites_channels', 'select', 'alltime');

            $params = array (
               'fields' => 'sites.id_site as id, sites.name, '.
                  'sites.url, sites.status, SUM(stat_sites.impressions) as impressions, '.
                  'SUM(stat_sites.alternative_impressions) as alternative_impressions, '.
                  'SUM(stat_sites.clicks) as clicks, (SUM(stat_sites.clicks)/SUM(stat_sites.impressions)*100) '.
                  'as ctr, SUM('.$this->revenue_field.') as revenue, UNIX_TIMESTAMP(sites.creation_date) '.
                  'as creation_date, e.name AS pub_name, e.e_mail, e.id_entity AS pub_entity',
               'order_by' => $this->table_builder->sort_field,
               'order_direction' => $this->table_builder->sort_direction,
               'status' => $status_filter,
               'offset' => ($this->pagination_post->get_page() - 1)*$this->pagination_post->get_per_page(),
               'limit' => $this->pagination_post->get_per_page(),
               'date_filter' =>  $this->date_range,
               'id_entity' => $this->user_id,
               'quicksearch' => $quicksearch);

      		// Add addtional fields to $params['fields']
            foreach($this->_hooks as $hookObj) {
            	if (method_exists($hookObj, 'extendColumnQueryFields')) {
            		$params = $hookObj->extendColumnQueryFields($params);
            	}
            }
	    
            $sites_array = $this->site->get_list($params);

            if (is_null($sites_array)) {
               $sites_array = array();
            }

           //$this->table_builder->add_from_array ($sites_array);

           $data_rows_conut = sizeof ( $sites_array );

           $page_total = array(
		         'revenue' => 0,
		         'impressions' => 0,
               'alternative_impressions' => 0,
		         'clicks' => 0
		      );

		      // register additional per page statistic fields
		      foreach($this->_hooks as $hookObj) {
		      	 $page_total = $hookObj->registerPerPageStatisticFields($page_total);
		      }

            //модификация контента отдельных столбцов (ссылки, чекбоксы)
            for($i = 0; $i < $data_rows_conut; $i ++) {
            	
            	$page_total['impressions'] += $sites_array[$i]['impressions'];
            	$page_total['alternative_impressions'] += $sites_array[$i]['alternative_impressions'];
            	$page_total['clicks'] += $sites_array[$i]['clicks'];
            	$page_total['revenue'] += $sites_array[$i]['revenue'];

            	// calculate per page statistic for additional columns from plugins
		        foreach($this->_hooks as $hookObj) {
		           $page_total = $hookObj->calculatePerPageStatistic($page_total, $sites_array[$i]);
		        }

               $this->table_builder->set_cell_content ( $i + 1, $col_alias['id'], $sites_array[$i]['id']);
               $code = type_to_str($sites_array[$i]['pub_entity'], 'textcode');
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['name'], limit_str_and_hint($sites_array[$i]['name'],30));
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['url'], array('name' => $sites_array[$i]['url'], 'href' => 'http://'.$sites_array[$i]['url'],'extra' => 'target="_blank"'),'link');

               if (($sites_array[$i]['status'] == 'unapproved') && ($sites_array[$i]['pub_entity'] == $this->user_id)) {
               	  $this->table_builder->set_cell_content ( $i + 1, $col_alias['status'],
               	  	 array(
                  		'name' => __( 'site_'.$sites_array[$i]['status']), 
                  		'extra' => ' class="red" value="' . __( 'site_'.$sites_array[$i]['status']) .'" title="'.__( 'Verify').'" onclick="top.location=\''.$this->site_url .$this->index_page. $this->role . '/approve_site/index/' . $sites_array [$i] ['id']. '\';"',
                  		'href' => $this->site_url .$this->index_page. $this->role . '/approve_site/index/' . $sites_array [$i] ['id']
               	  	 ),
               	  	 'link',
               	  	 '');  
               } else {
               	  $this->table_builder->set_cell_content ( $i + 1, $col_alias['status'], __( 'site_'.$sites_array[$i]['status']));
               }
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['impressions'], type_to_str($sites_array[$i]['impressions'],'integer'));
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['alternative_impressions'], type_to_str($sites_array[$i]['alternative_impressions'],'integer'));
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['clicks'], type_to_str($sites_array[$i]['clicks'],'integer'));
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['ctr'], type_to_str($sites_array [$i]['ctr'], 'procent'));
               $this->table_builder->set_cell_content( $i + 1, $col_alias['revenue'], type_to_str($sites_array [$i] ['revenue'], 'money'));
               //$this->table_builder->set_cell_content( $i + 1, 10, type_to_str($sites_array [$i] ['creation_date'], 'date'));
               if ('deleted' == $sites_array[$i]['status']) {
               	$this->table_builder->set_cell_content ( $i + 1, $col_alias['action'],'');               	
                  $this->table_builder->set_cell_content ( $i + 1, $col_alias['chkboxes'], '');
                  $this->table_builder->set_cell_content ( $i + 1, $col_alias['togglers'], '');
                  $this->table_builder->add_row_attribute( $i + 1,'class', 'deleted_row');
               } else {
               	if ('paused' == $sites_array[$i]['status']) {
               	  $this->table_builder->add_row_attribute( $i + 1,'class', 'blocked_row');
               	}
               	$this->table_builder->set_cell_content ( $i + 1, $col_alias['togglers'], array ('src' => $this->site_url.'images/pixel.gif', 'onclick' => 'ShowChannelsBySite('.$sites_array[$i]['id'].',this)', 'extra' => 'class="plus"'), 'image' );
               	
               	if ($sites_array[$i]['pub_entity'] == $this->user_id) {
	                $this->table_builder->set_cell_content ( $i + 1, $col_alias['action'], array ('name' => '{@Create Channel@}', 'extra' => ' class="guibutton floatl mr3 mb3 ico ico-plusgreen" value="{@Create Channel@}" title="{@Create Channel@}" onclick="top.create_channel_for_site(' . $sites_array [$i] ['id'] . ');" ', 'href' => '#' ), 'link' );
	               	foreach($hookObjects as $hookObject) {
	                	$hookObject->registerAdditionalSiteButtons($this->table_builder->cell($i + 1, $col_alias['action']), $sites_array [$i] ['id']);
	                }
	                $this->table_builder->cell($i + 1, $col_alias['action'])->add_content(array ('name' => '{@Edit@}', 'extra' => ' class="guibutton floatl mr3 mb3 ico ico-edit" value="{@Edit@}" title="{@Edit@}" onclick="top.location=\'' . $this->site_url .$this->index_page . $this->role . '/edit_site/index/' . $sites_array [$i] ['id'] . '\'" ', 'href' => $this->site_url .$this->index_page. $this->role . '/edit_site/index/' . $sites_array [$i] ['id'] ), 'link', ' ');
	                $this->table_builder->cell($i + 1, $col_alias['action'])->add_content(array ('name' => '{@Layout@}', 'extra' => ' class="guibutton floatl mr3 mb3 ico ico-site-layout" value="{@Layout@}" title="{@Layout@}" onclick="top.location=\'' . $this->site_url .$this->index_page. $this->role . '/edit_site_channel_layout/index/' . $sites_array [$i] ['id'] . '\'" ', 'href' => $this->site_url .$this->index_page. $this->role . '/edit_site_channel_layout/index/' . $sites_array [$i] ['id'] ), 'link', ' ');
	                
               	} else {
	               	$this->table_builder->set_cell_content ( $i + 1, $col_alias['action'],
		               	array (
		                   	'name' => __('Login As'),
		                   	'href' => "#login_as",
		                   	'extra' => "value=\"" . __('Login As') . "\" title=\"" . __('Login As') . "\" class='guibutton floatl ico ico-user' onclick='loginAs(\"$code\");'"
		                ), 'link', ' ');
               	}
               	$this->table_builder->set_cell_content ( $i + 1, $col_alias['chkboxes'], array ('name' => 'id_site[]', 'value' => $sites_array [$i] ['id'], 'extra' => 'id=chk'.$i.' onclick="checktr(\'chk'.$i.'\',\'tr'.($i+1).'\')"'), 'checkbox' );                     
                
               }
               $this->table_builder->add_row_attribute( $i + 1, 'id', 'tr'.($i+1));
               
               // render additional columns from plugins
	           foreach($this->_hooks as $hookObj) {
	              $hookObj->renderRow($i + 1, $col_alias, $sites_array[$i], $this->table_builder);
            }

            } // for($i = 0; $i < $data_rows_conut; $i ++), модификация контента отдельных столбцов (ссылки, чекбоксы)


           if (0 == $data_rows_conut) {
           	$this->table_builder->insert_empty_cells = false;
				$this->table_builder->set_cell_content (1, 0,__('Records not found'));
				$this->table_builder->cell(1, 0)->add_attribute('colspan', count($col_alias));

				 $this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
		         $this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
		         $this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
           } else {
           	   $row = $data_rows_conut + 1;
	           	$this->table_builder->set_cell_content ($row, $col_alias['url'], __("Page total"));
	           	//$this->table_builder->cell($row, 2)->add_attribute('colspan', 2);
		         $this->table_builder->set_cell_content ($row, $col_alias['impressions'], type_to_str($page_total['impressions'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['alternative_impressions'], type_to_str($page_total['alternative_impressions'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['clicks'], type_to_str($page_total['clicks'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['revenue'], type_to_str($page_total['revenue'], 'money'));
		         $ctr = $page_total['impressions']?$page_total['clicks']/$page_total['impressions']*100:0;
		         $this->table_builder->set_cell_content ($row, $col_alias['ctr'], type_to_str($ctr, 'procent'));
		         $this->table_builder->clear_row_attributes($row);
		         $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');
		         
           		 // render per page statistic for additional columns from plugins
		         foreach($this->_hooks as $hookObj) {
		         	$hookObj->renderPageStatisticRow($row, $col_alias, $page_total, $this->table_builder);
		         }
		         
		         $row++;
		         $this->table_builder->set_cell_content ($row, $col_alias['url'], __("Total"));
		         $this->table_builder->set_cell_content ($row, $col_alias['impressions'], type_to_str($total['impressions'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['alternative_impressions'], type_to_str($total['alternative_impressions'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['clicks'], type_to_str($total['clicks'], 'integer'));
		         $this->table_builder->set_cell_content ($row, $col_alias['revenue'], type_to_str($total['revenue'], 'money'));
		         
		         $ctr = $total['impressions']?$total['clicks']/$total['impressions']*100:0;
		         $this->table_builder->set_cell_content ($row, $col_alias['ctr'], type_to_str($ctr, 'procent'));
		         $this->table_builder->clear_row_attributes($row);
		         $this->table_builder->add_row_attribute($row, 'class', 'alltotal');
		         
           		 // render summary statistic for additional columns from plugins
		         foreach($this->_hooks as $hookObj) {
                  $hookObj->renderSummaryRow($row, $col_alias, $total, $this->table_builder);
		         }
           }

            $table = $this->table_builder->get_sort_html ();
            $columns = $this->table_builder->get_columns_html();

            $tab_content = $this->parser->parse($this->views_paths['sites_list'],array('PAGINATION' => $pagination, 'SITES_TABLE' => $table, 'COLUMNS' => $columns),TRUE);
      /*	break;
      }*/

      $html = str_replace('<%SITES_TAB_SELECTED%>', $active_tab == 'sites_to_channels'?'class="sel"':'', $html);

      $html = str_replace('<%TABS%>', $tab_content, $html);

      $html = str_replace('<%PERIOD_BEGIN%>',$this->date_range['from'],$html);
      $html = str_replace('<%PERIOD_END%>',$this->date_range['to'],$html);
      $this->_set_content($html);
      $this->_display ();

   } //end show_list()

   /**
   * Callback-функция, устанавливает значения по умолчанию для фильтров таблицы каналов/сайтов
   *
   * @return array массив со значениями по умолчанию для фильтров
   */
   public function _load($id) {
      $fields = period_load('manage_sites_channels', 'select', 'alltime');
      $this->date_range = data_range($fields);

      return $fields;
   } //end _load

   public function _save($id, $fields) {
      $this->date_range = data_range($fields);
      period_save('manage_sites_channels', $fields);
   	return " ";
   }

   protected function delete_site_channels() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->site->delete_channel ($id_site , $id , $this->user_id);
             }
         }
      }
   }

   protected function resume_site_channels() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->site->resume_channel ($id_site , $id , $this->user_id);
             }
         }
      }
   }

   protected function pause_site_channels() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_channel )) {
         foreach ($id_channel as $id) {
             if (is_numeric($id)) {
                $this->site->pause_channel ($id_site , $id , $this->user_id);
             }
         }
      }
   }

protected function delete_channel_sites() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
                $rez = $this->channel->delete_site($id_channel , $id , $this->user_id);
                if ('' <> $rez ) {
                  return $rez;
                }
             }
         }
      }

      return '';
   }

   protected function resume_channel_sites() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
                $this->channel->resume_site ($id_channel , $id , $this->user_id);
             }
         }
      }
   }

   protected function pause_channel_sites() {
      $id_site = $this->input->post ( 'id_site' );
      $id_channel = $this->input->post ( 'id_channel' );
      if (is_array ( $id_site )) {
         foreach ($id_site as $id) {
             if (is_numeric($id)) {
                $this->channel->pause_site ($id_channel , $id , $this->user_id);
             }
         }
      }
   }

   /**
    * Отправляет ответ (HTML таблицу) на запрос списка каналов выбранного сайта
    * Сайт указывается в GET-параметре id_site
    */
   	public function get_channels($id_site, $date_from = null,$date_to = null) {
   	  
   		// Add plugins to hook
         $this->add_plugins('site_chanels');
   		
   	  	if(is_null($date_from) || is_null($date_to)) {
   	  		$fields = period_load('manage_sites_channels', 'select', 'alltime');
   	  		$this->date_range = data_range($fields);
   	  	} else {
   			$this->date_range = array('from' => $date_from, 'to' => $date_to);
   		}
   		
   		$action = $this->input->post('manage_action');
   		if ($action) {
   			$id_site = $this->input->post('id_site');
   			switch ($action) {
   				case 'pause':
   					$this->pause_site_channels();
   					break;
   				case 'resume':
   					$this->resume_site_channels();
   					break;
   				case 'delete':
   					$this->delete_site_channels();
   					break;
   			}
   		}

   		$col_index = 0;
   		$col_alias = array(
   			'chkboxes' => $col_index++,
   			'id' => $col_index++,
   			'name' => $col_index++,
   			'format' => $col_index++,
   			'status' => $col_index++,
   			'impressions' => $col_index++,
   			'alternative_impressions' => $col_index++,
   			'clicks' => $col_index++,
   			'ctr' => $col_index++,
   			'revenue' => $col_index++,
   			'programs_count' => $col_index++,
   			'action' => $col_index++
   		);

   		// add addtional columns to column map from plugins
        foreach($this->_hooks as $hookObj) {
           $col_alias = $hookObj->extendColumnMap($col_alias);
        }

   		$this->table_builder->clear();
   		$this->table_builder->insert_empty_cells = false;

   		$this->table_builder->init_sort_vars('manage_sites_channels_channel', 'id', 'desc', FALSE, 'manage_sites_channels_channel_'.$id_site);
   		
   		$this->table_builder->sorted_column($col_alias['id'],'id','ID','asc');
   		$this->table_builder->sorted_column($col_alias['name'],'name','Channel Name','asc');
   		$this->table_builder->sorted_column($col_alias['status'],'status','Status','asc');
   		$this->table_builder->sorted_column($col_alias['impressions'],'impressions','Impressions','desc');
   		$this->table_builder->sorted_column($col_alias['alternative_impressions'],'alternative_impressions','Alternative Impressions','desc');
   		$this->table_builder->sorted_column($col_alias['clicks'],'clicks','Clicks','desc');
   		$this->table_builder->sorted_column($col_alias['ctr'],'ctr','CTR','desc');
   		$this->table_builder->sorted_column($col_alias['revenue'],'revenue','Revenue','desc');
   		$this->table_builder->sorted_column($col_alias['programs_count'],'programs_count','Prices','desc');

   		//добавление ячеек-заголовка
   		$this->table_builder->set_cell_content ( 0, $col_alias['chkboxes'], array ('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'manage_sites_channels_channel\', this)"' ), 'checkbox' );
   		$this->table_builder->set_cell_content ( 0, $col_alias['format'], __('Format') );
   		$this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );

   		// create additional columns from plugins
        foreach($this->_hooks as $hookObj) {
           $hookObj->createColumns($col_alias, $this->table_builder);
        }
   		
   		$this->table_builder->cell(0,$col_alias['chkboxes'])->add_attribute('class', 'simpleTitle');
   		$this->table_builder->cell(0,$col_alias['format'])->add_attribute('class', 'simpleTitle');
   		$this->table_builder->cell(0,$col_alias['action'])->add_attribute('class', 'simpleTitle');

   		//прописывание стилей для ячеек
   		$this->table_builder->add_col_attribute($col_alias['chkboxes'], 'class', '"chkbox"');
   		$this->table_builder->add_col_attribute($col_alias['id'], 'class', 'w20 center');

   		$this->table_builder->add_col_attribute($col_alias['format'], 'class', '"w20 center"');
   		$this->table_builder->add_col_attribute($col_alias['status'], 'class', '"w100 center"');
   		$this->table_builder->add_col_attribute($col_alias['impressions'], 'class', '"w100 right"');
   		$this->table_builder->add_col_attribute($col_alias['alternative_impressions'], 'class', '"w100 right"');
   		$this->table_builder->add_col_attribute($col_alias['clicks'], 'class', '"w50 right"');
   		$this->table_builder->add_col_attribute($col_alias['ctr'], 'class', '"w50 right"');
   		$this->table_builder->add_col_attribute($col_alias['revenue'], 'class', '"w50 right"');
   		$this->table_builder->add_col_attribute($col_alias['programs_count'], 'class', 'w100 center');
   		$this->table_builder->add_col_attribute($col_alias['action'], 'class', 'mw150 nowrap center');

   		// add styles for additional columns from plugins
	    foreach($this->_hooks as $hookObj) {
	       $hookObj->defineColumnStyles($col_alias, $this->table_builder);
	    }
   	
   		$this->table_builder->add_row_attribute(0,'class', 'th');

   		//установка атрибутов таблицы
   		$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here

   		$params = array(
   			'fields' => 'channels.id_channel as id, '
   						.'channels.name, channels.ad_type, channels.id_dimension, dimensions.width, dimensions.height, '
   						.'site_channels.status, SUM(impressions) as impressions, SUM(alternative_impressions) as '
   						.'alternative_impressions, SUM(clicks) as clicks, (SUM(clicks)/SUM(impressions)*100) as ctr, '
   						.'SUM(' . $this->revenue_field . ') as revenue, UNIX_TIMESTAMP(channels.creation_date) as creation_date, '
   						.'e.id_entity AS pub_entity',
      		'order_by' => $this->table_builder->sort_field,
      		'order_direction' => $this->table_builder->sort_direction,
      		'site_id_filter' => $id_site,
      		'join_tables' => array('channel_program_types','dimensions', 'sites'),
      		'date_filter' =>  $this->date_range
   		);
   		
   		// Add addtional fields to $params['fields']
        foreach($this->_hooks as $hookObj) {
            if (method_exists($hookObj, 'extendColumnQueryFields')) {
     	       $params = $hookObj->extendColumnQueryFields($params, 'stat_sites_channels');
            }
        }
   
   		$channels_array = $this->channel->get_list($params);

   		// перевод строк таблицы в соответствии с локалью
   		if (is_null($channels_array)) {
   			$channels_array = array();
   		}

   		$data_rows_conut = sizeof ( $channels_array );
     
   		//модификация контента отдельных столбцов (ссылки, чекбоксы)
   		$code_site = type_to_str($id_site, 'textcode');
   		$pub_entity = NULL;
   		for($i = 0; $i < $data_rows_conut; $i++) {
   			$pub_entity = $channels_array[$i]['pub_entity'];
   		 
   			if ('deleted' == $channels_array[$i]['status']) {
   				$this->table_builder->set_cell_content($i + 1, $col_alias['chkboxes'], '');
   				$this->table_builder->set_cell_content($i + 1, $col_alias['programs_count'], '');
   				$this->table_builder->set_cell_content($i + 1, $col_alias['action'], '');
   				$this->table_builder->add_row_attribute($i + 1, 'class', 'deleted_row');
   			} else {
   				$code_channel = type_to_str($channels_array[$i]['id'], 'textcode');
   				if ($channels_array[$i]['pub_entity'] == $this->user_id) {
   					$this->table_builder->set_cell_content($i + 1, $col_alias['chkboxes'], array('name' => 'id_channel[]', 'value' => $channels_array[$i]['id'], 'extra' => 'id=chk' . $i . ' onclick="checktr(\'chk' . $i . '\',\'tr' . ($i + 1) . '\')"'), 'checkbox');
   				} else {
   					$this->table_builder->set_cell_content($i + 1, $col_alias['chkboxes'], '');
   				}
   				
   				if ($channels_array[$i]['pub_entity'] == $this->user_id) {
   					if ($channels_array[$i]['flat_rate_programs_count'] > 0 || $channels_array[$i]['cpm_programs_count'] > 0) {
   						$this->table_builder->set_cell_content($i + 1, $col_alias['programs_count'], array('name' => sprintf(__('Manage Programs'), $channels_array[$i]['flat_rate_programs_count'], $channels_array[$i]['cpm_programs_count']), 'href' => $this->site_url .$this->index_page. $this->role . '/manage_channel_prices/index/' . $channels_array[$i]['id'], 'extra' => 'target="_top"'), 'link');
   					} else {
   						$this->table_builder->set_cell_content($i + 1, $col_alias['programs_count'], array('name' => __('Undefined'), 'href' => $this->site_url .$this->index_page. $this->role . '/manage_channel_prices/index/' . $channels_array[$i]['id'], 'extra' => 'target="_top" class="red"'), 'link');
   					}
   				} else {
   					if ($channels_array[$i]['flat_rate_programs_count'] > 0 || $channels_array[$i]['cpm_programs_count'] > 0) {
   						$this->table_builder->set_cell_content($i + 1, $col_alias['programs_count'], array('name' => sprintf(__('Manage Programs'), $channels_array[$i]['flat_rate_programs_count'], $channels_array[$i]['cpm_programs_count']), 'href' => $this->site_url .$this->index_page. $this->role . '/manage_channel_prices/index/' . $channels_array[$i]['id'], 'extra' => 'target="_top"'), 'link');
   					} else {
   						$this->table_builder->set_cell_content($i + 1, $col_alias['programs_count'], __('Undefined'));
   					}
   				}
   				
   				if ($channels_array[$i]['pub_entity'] == $this->user_id) {
   					$this->table_builder->set_cell_content($i + 1, $col_alias['action'], array('name' => __('Edit'), 'href' => '#edit', 'extra' => 'jframe="no" class=" guibutton floatl ico ico-edit" value="{@Edit@}" title="{@Edit@}" onclick="top.edit_channel_for_site(\'' . $id_site . '\',\'' . $channels_array[$i]['id'] . '\')"'), 'link');
   					$this->table_builder->cell($i + 1, $col_alias['action'])->add_content(array('name' => __('Get code'), 'href' => '#get_code', 'extra' => 'jframe="no" class="guibutton floatl ico ico-puzzle2" value="{@Get code@}" title="{@Get code@}"  onclick="top.get_channel_code(\'' . $id_site . '\',\'' . $channels_array[$i]['id'] . '\')"'), 'link', ' ');
   				} else {
   					$this->table_builder->set_cell_content($i + 1, $col_alias['action'], '');
   				}

   				if ('paused' == $channels_array[$i]['status']) {
   					$this->table_builder->add_row_attribute($i + 1, 'class', 'blocked_row');
   				}
   			}
   		 
   			$this->table_builder->set_cell_content($i + 1, $col_alias['id'], $channels_array[$i]['id']);
   			$this->table_builder->set_cell_content($i + 1, $col_alias['name'], type_to_str($channels_array[$i]['name'], 'encode'));
   			
   			$allowedTypes = explode(',', $channels_array[$i]['ad_type']);
   			$this->table_builder->set_cell_content($i + 1, $col_alias['format'], '');

   			if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedTypes)) {
				$ico_path = $this->site_url . 'images/smartppc6/icons/script_code.png';
				$hint_title = __('Text Ad') . ' (' . $channels_array[$i]['width'] . '&times;' . $channels_array[$i]['height'] . ')';
				$img_prefix = 'txt_';
				$this->table_builder->cell($i + 1, $col_alias['format'])->add_content( 
					array(
						'src' => $ico_path, 
						'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
					),
					'image'
				);
   			}
   			
   			if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedTypes)) {
   				$ico_path = $this->site_url . 'images/smartppc6/icons/image.png';
   				$hint_title = __('Image Ad') . ' (' . $channels_array[$i]['width'] . '&times;' . $channels_array[$i]['height'] . ')';
   				$img_prefix = 'img_';
   				$this->table_builder->cell($i +1, $col_alias['format'])->add_content(
   					array(
   						'src' => $ico_path, 
   						'extra' => 'title="' . $hint_title . '" href="' . $this->site_url . 'images/dimensions_preview/' . $img_prefix . $channels_array[$i]['id_dimension'] . '.png" class="tooltip"'
   					), 
   					'image'
   				);
   			}
   		 
   			$this->table_builder->cell($i + 1, $col_alias['format'])->add_content($channels_array[$i]['width'] . '&times;' . $channels_array[$i]['height'], '', ' ');
   		 
   			$this->table_builder->set_cell_content($i + 1, $col_alias['status'], __('channel_' . $channels_array[$i]['status']));
   			$this->table_builder->set_cell_content($i + 1, $col_alias['impressions'], type_to_str($channels_array[$i]['impressions'], 'integer'));
   			$this->table_builder->set_cell_content($i + 1, $col_alias['alternative_impressions'], type_to_str($channels_array[$i]['alternative_impressions'], 'integer'));
   			$this->table_builder->set_cell_content($i + 1, $col_alias['clicks'], type_to_str($channels_array[$i]['clicks'], 'integer'));
   			$this->table_builder->set_cell_content($i + 1, $col_alias['ctr'], type_to_str($channels_array[$i]['ctr'], 'float') . ' %');
   			$this->table_builder->set_cell_content($i + 1, $col_alias['revenue'], type_to_str($channels_array[$i]['revenue'], 'money'));

   			$this->table_builder->add_row_attribute($i + 1, 'id', 'tr' . ($i + 1));
   			
   		    // render additional columns from plugins
            foreach($this->_hooks as $hookObj) {
               $hookObj->renderRow($i + 1, $col_alias, $channels_array[$i], $this->table_builder);
   		}
   		
   		} //end модификация контента отдельных столбцов (ссылки, чекбоксы)
   		
   		if (0 == $data_rows_conut) {
   			$this->table_builder->set_cell_content (1, 0,__('Records not found'));
   			$this->table_builder->cell(1, 0)->add_attribute('colspan',count($col_alias));
   			$this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
   			$this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
   			$this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
   		}

   		// Устанавливаем возможность выбора колонок
   		$this->table_builder->use_select_columns();
   		$invariable_columns = array(
   			$col_alias['chkboxes'], $col_alias['id'], $col_alias['name'], 
   			$col_alias['format'], $col_alias['programs_count'], $col_alias['action']
   		);
   		$this->table_builder->set_invariable_columns($invariable_columns);

   		$table = $this->table_builder->get_sort_html ();

   		$content = $this->parser->parse(
   			$this->views_paths['iframe_channels_list'],
   			array(
   				'CHANNELS_TABLE' => $table,
   				'COLUMNS' => $this->table_builder->get_columns_html(),
   				'ID_SITE' => $id_site,
   				'JFRAME_ACTION' => '<%SITEURL%><%INDEXPAGE%>'.$this->role.'/manage_sites_channels/get_channels/'.$id_site.'/'.$date_from.'/'.$date_to.'/all'
   			),
   			TRUE
   		);
   		
   		if ($pub_entity != $this->user_id) {
   			$content = preg_replace("/\<a name=\"chan_buttons_begin\"\>\<\/a\>[\s\S]*?\<a name=\"chan_buttons_end\"\>\<\/a\>/", '', $content);
   		}
   		
   		$this->template = "common/parent/jq_iframe.html";
   		$this->_set_content($content);
   		$this->_display();
   	} //end get_channels()

   /**
    * Отправляет ответ (HTML таблицу) на запрос списка сайтов выбранного канала
    * Канал указывается в GET-параметре id_channel
    */
   public function get_sites($id_channel, $date_from = null,$date_to = null) {
   	
   	  // Add plugins to hook
      $this->add_plugins('channel_sites');
   	
      $message = '';
   	if(is_null($date_from) || is_null($date_to)) {
         $fields = period_load('manage_sites_channels', 'select', 'alltime');
         $this->date_range = data_range($fields);
      } else {
         $this->date_range = array('from' => $date_from, 'to' => $date_to);
      }
   $action = $this->input->post('manage_action');
      if ($action) {
         $id_channel = $this->input->post('id_channel');
         switch ($action) {
            case 'pause':
               $this->pause_channel_sites();
            break;
            case 'resume':
               $this->resume_channel_sites();
            break;
            case 'delete':
            	$message = $this->delete_channel_sites();
            break;
         }
      }

      $this->pagination_post->clear();
      $this->pagination_post->set_form_name('manage_sites_channels_site_'.$id_channel);
      
      $total_params = array('channel_id_filter' => $id_channel,
      									    'date_filter' =>  $this->date_range,
      									    'revenue_field' => $this->revenue_field,
                             'id_entity'        => $this->user_id);
      $total = $this->site->get_count($total_params);
      
      $this->pagination_post->set_total_records($total['count']);
      $this->pagination_post->read_variables('manage_sites_channels_site', 1, 10, FALSE, 'manage_sites_channels_site_'.$id_channel);

      //настройка параметров разбиения на страницы
      $pagination = $this->pagination_post->create_form ();

      $col_index = 0;
      $col_alias = array('chkboxes' => $col_index++,
                                  'id' => $col_index++,
                                  'url' => $col_index++,
                                  'name' => $col_index++,
                                  'status' => $col_index++,
                                  'impressions' => $col_index++,
                                  'alternative_impressions' => $col_index++,
                                  'clicks' => $col_index++,
                                  'ctr' => $col_index++,
                                  'revenue' => $col_index++,
                                  'action' => $col_index++);
      // add addtional columns to column map from plugins
      foreach($this->_hooks as $hookObj) {
         $col_alias = $hookObj->extendColumnMap($col_alias);
      }

      $this->table_builder->clear ();

      $this->table_builder->init_sort_vars('manage_sites_channels_site', 'id', 'desc', FALSE, 'manage_sites_channels_site_'.$id_channel);
      $this->table_builder->sorted_column($col_alias['id'],'id','ID','asc');
      $this->table_builder->sorted_column($col_alias['name'],'name','Title','asc');
      $this->table_builder->sorted_column($col_alias['url'],'url','Site URL','asc');
      $this->table_builder->sorted_column($col_alias['status'],'status','Status','asc');
      $this->table_builder->sorted_column($col_alias['impressions'],'impressions','Impressions','desc');
      $this->table_builder->sorted_column($col_alias['alternative_impressions'],'alternative_impressions','Alternative Impressions','desc');
      $this->table_builder->sorted_column($col_alias['clicks'],'clicks','Clicks','desc');
      $this->table_builder->sorted_column($col_alias['ctr'],'ctr','CTR','desc');
      $this->table_builder->sorted_column($col_alias['revenue'],'revenue','Revenue','desc');
      //$this->table_builder->sorted_column(9,'creation_date','Creation Date','desc');


      //добавление ячеек-заголовка
      $this->table_builder->set_cell_content ( 0, $col_alias['chkboxes'], array ('name' => 'checkAll', 'extra' => 'onclick="return select_all(\'manage_sites_channels_site\', this)"' ), 'checkbox' );
      $this->table_builder->set_cell_content ( 0, $col_alias['action'], __('Action') );

      // create additional columns from plugins
      foreach($this->_hooks as $hookObj) {
         $hookObj->createColumns($col_alias, $this->table_builder);
      }
      
      $this->table_builder->cell(0,$col_alias['chkboxes'])->add_attribute('class', 'simpleTitle');
      $this->table_builder->cell(0,$col_alias['action'])->add_attribute('class', 'simpleTitle');

     //прописывание стилей для ячеек
      $this->table_builder->add_col_attribute($col_alias['chkboxes'], 'class', '"chkbox"');
      $this->table_builder->add_col_attribute($col_alias['id'], 'class', 'w20');
      //$this->table_builder->add_col_attribute(3, 'class', 'w150');
      //$this->table_builder->add_col_attribute(3, 'class', '"w80 center"');
      $this->table_builder->add_col_attribute($col_alias['status'], 'class', 'w80 center');
      $this->table_builder->add_col_attribute($col_alias['impressions'], 'class', 'w80 right');
      $this->table_builder->add_col_attribute($col_alias['alternative_impressions'], 'class', 'w80 right');
      $this->table_builder->add_col_attribute($col_alias['clicks'], 'class', 'w50 right');
      $this->table_builder->add_col_attribute($col_alias['ctr'], 'class', 'w50 right');
      //$this->table_builder->add_col_attribute(8, 'class', '"w80 right"');
      $this->table_builder->add_col_attribute($col_alias['revenue'], 'class', 'w100 center');
      $this->table_builder->add_col_attribute($col_alias['action'], 'class', 'nowrap center');

      // add styles for additional columns from plugins
	  foreach($this->_hooks as $hookObj) {
	     $hookObj->defineColumnStyles($col_alias, $this->table_builder);
	  }
      
      $this->table_builder->add_row_attribute(0,'class', 'th');

      //установка атрибутов таблицы
      $this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here

      $params = array ('fields' => 'sites.id_site as id, '
                                  .'sites.url, site_channels.status, sites.name, SUM(impressions) as impressions, '
                                  .'SUM(alternative_impressions) as alternative_impressions, SUM(clicks) as clicks, '
                                  .'(SUM(clicks)/SUM(impressions)*100) as ctr, UNIX_TIMESTAMP(sites.creation_date) as '
                                  .'creation_date, SUM(' . $this->revenue_field . ') as revenue, '
                                  .'e.id_entity AS pub_entity',
      'order_by' => $this->table_builder->sort_field,
      'order_direction' => $this->table_builder->sort_direction,
      'channel_id_filter' => $id_channel,
      'date_filter' =>  $this->date_range,
      'id_entity' => $this->user_id,
      'offset' => ($this->pagination_post->get_page() - 1)*$this->pagination_post->get_per_page(),
      'limit' => $this->pagination_post->get_per_page());

      
      // Add addtional fields to $params['fields']
      foreach($this->_hooks as $hookObj) {
         if (method_exists($hookObj, 'extendColumnQueryFields')) {
     	    $params = $hookObj->extendColumnQueryFields($params, 'stat_sites_channels');
         }
      }
      
      $sites_array = $this->site->get_list($params);
      //echo $this->db->last_query();

      if (is_null($sites_array)) {
         $sites_array = array();
      }

     //$this->table_builder->add_from_array ($sites_array);

     $data_rows_conut = sizeof ( $sites_array );

     //модификация контента отдельных столбцов (ссылки, чекбоксы)

      $code_channel = type_to_str($id_channel, 'textcode');

      $page_total = array(
               'revenue' => 0,
               'impressions' => 0,
               'alternative_impressions' => 0,
               'clicks' => 0
            );

      // register additional per page statistic fields
      foreach($this->_hooks as $hookObj) {
    	 $page_total = $hookObj->registerPerPageStatisticFields($page_total);
      }
            
      $pub_entity = NULL;
      for($i = 0; $i < $data_rows_conut; $i ++) {
         $pub_entity = $sites_array[$i]['pub_entity'];
      	$page_total['impressions'] += $sites_array[$i]['impressions'];
      	$page_total['alternative_impressions'] += $sites_array[$i]['alternative_impressions'];
         $page_total['clicks'] += $sites_array[$i]['clicks'];
         $page_total['revenue'] += $sites_array[$i]['revenue'];

         // calculate per page statistic for additional columns from plugins
         foreach($this->_hooks as $hookObj) {
            $page_total = $hookObj->calculatePerPageStatistic($page_total, $sites_array[$i]);
         }

         $code_site = type_to_str($sites_array[$i]['id'], 'textcode');
         if ('deleted' == $sites_array [$i] ['status']) {
                  $this->table_builder->set_cell_content ( $i + 1, $col_alias['chkboxes'], '');
                  $this->table_builder->set_cell_content ( $i + 1, $col_alias['action'], '');
                  $this->table_builder->add_row_attribute( $i + 1,'class', 'deleted_row');
      	} else {
      	   if ($sites_array [$i]['pub_entity'] == $this->user_id) {
      	      $this->table_builder->set_cell_content($i+1, $col_alias['chkboxes'], array ('name' => 'id_site[]', 'value' => $sites_array [$i] ['id'], 'extra' => 'id=chk'.$i.' onclick="checktr(\'chk'.$i.'\',\'tr'.($i+1).'\')"'), 'checkbox' );
               $this->table_builder->set_cell_content ( $i + 1, $col_alias['action'], array ('name' => __('Edit'), 'href' => $this->site_url .$this->index_page. $this->role . '/edit_site/index/' . $sites_array [$i] ['id'] , 'extra' => 'jframe="no" class="guibutton floatl ico ico-edit" value="{@Edit@}" title="{@Edit@}" onclick="top.location=\'' . $this->site_url .$this->index_page. $this->role . '/edit_site/index/' . $sites_array [$i] ['id'] . '\'" target="_top"' ), 'link' );
      	      $this->table_builder->cell($i+1, $col_alias['action'])->add_content(array('name' => __('Get code'), 'href' => '#get_code', 'extra' => 'jframe="no" class="guibutton floatl ico ico-puzzle2" value="{@Get code@}" title="{@Get code@}" onclick="top.get_channel_code(\''.$sites_array [$i] ['id'].'\',\''.$id_channel.'\')"'), 'link', ' ' );
               $this->table_builder->cell($i+1, $col_alias['action'])->add_content( array ('name' => __('Layout'), 'href' => $this->site_url .$this->index_page. $this->role . '/edit_site_channel_layout/index/' . $sites_array [$i] ['id'] , 'extra' => 'jframe="no" class="guibutton floatl ico ico-site-layout" value="{@Layout@}" title="{@Layout@}" onclick="top.location=\'' . $this->site_url .$this->index_page. $this->role . '/edit_site_channel_layout/index/' . $sites_array [$i] ['id'] . '\'" target="_top"' ), 'link','' );
      	   } else {
      	      $this->table_builder->set_cell_content($i+1, $col_alias['chkboxes'], '');
      	      $this->table_builder->set_cell_content($i+1, $col_alias['action'], '');
      	   }      	         	   
	         $this->table_builder->cell($i+1, $col_alias['action'])->add_content(array('name' => __('View Ads'), 'href' => '#view_ads', 'extra' => 'jframe="no" class="guibutton floatl ico ico-viewads" value="'.__("View Ads").'" title="'.__("View Ads").'"  onclick="top.view_ads(\''.$code_site.'\',\''.$code_channel.'\')"'), 'link', ' ');
            $this->table_builder->add_row_attribute( $i + 1, 'id', 'tr'.($i+1));

	         if ('paused' == $sites_array[$i]['status']) {
	                  $this->table_builder->add_row_attribute( $i + 1,'class', 'blocked_row');
	         }
      	}

      	$this->table_builder->set_cell_content ( $i + 1, $col_alias['id'], $sites_array [$i] ['id']);
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['name'], limit_str_and_hint($sites_array [$i] ['name'],30));
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['url'], array('name' => $sites_array[$i]['url'], 'href' => 'http://'.$sites_array[$i]['url'],'extra' => 'target="_blank"'),'link');
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['status'], __( 'site_'.$sites_array [$i] ['status']));
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['impressions'], type_to_str($sites_array [$i] ['impressions'],'integer'));
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['alternative_impressions'], type_to_str($sites_array [$i] ['alternative_impressions'],'integer'));
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['clicks'], type_to_str($sites_array [$i] ['clicks'],'integer'));
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['ctr'], type_to_str($sites_array [$i]['ctr'], 'float').' %');
            $this->table_builder->set_cell_content ( $i + 1, $col_alias['revenue'], type_to_str($sites_array [$i] ['revenue'], 'money'));
            //$this->table_builder->set_cell_content ( $i + 1, 9, type_to_str($sites_array [$i]['creation_date'], 'date'));
       
        // render additional columns from plugins
        foreach($this->_hooks as $hookObj) {
           $hookObj->renderRow($i + 1, $col_alias, $sites_array[$i], $this->table_builder);
      }
      } //end модификация контента отдельных столбцов (ссылки, чекбоксы)

      if (0 == $data_rows_conut) {
      	$this->table_builder->insert_empty_cells = false;
             $this->table_builder->set_cell_content (1, 0,__('Records not found'));
             $this->table_builder->cell(1, 0)->add_attribute('colspan',count($col_alias));
				$this->table_builder->cell(1, 0)->add_attribute('class', 'nodata');
				$this->table_builder->remove_col_attribute_value(0, 'class', 'chkbox');
				$this->table_builder->cell(0, 0)->add_attribute('class', 'chkbox');
      } else {
               $row = $data_rows_conut + 1;
               $this->table_builder->set_cell_content ($row, $col_alias['name'], __("Page total"));
               //$this->table_builder->cell($row, 2)->add_attribute('colspan', 2);
               $this->table_builder->set_cell_content ($row, $col_alias['impressions'], type_to_str($page_total['impressions'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['alternative_impressions'], type_to_str($page_total['alternative_impressions'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['clicks'], type_to_str($page_total['clicks'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['revenue'], type_to_str($page_total['revenue'], 'money'));
               $ctr = $page_total['impressions']?$page_total['clicks']/$page_total['impressions']*100:0;
               $this->table_builder->set_cell_content ($row, $col_alias['ctr'], type_to_str($ctr, 'procent'));
               $this->table_builder->clear_row_attributes($row);
               $this->table_builder->add_row_attribute($row, 'class', 'pagetotal');
      		   // render per page statistic for additional columns from plugins
		       foreach($this->_hooks as $hookObj) {
		          $hookObj->renderPageStatisticRow($row, $col_alias, $page_total, $this->table_builder);
		       }
               $row++;
               $this->table_builder->set_cell_content ($row, $col_alias['name'], __("Total"));
               //$this->table_builder->cell($row, 2)->add_attribute('colspan', 2);
               $this->table_builder->set_cell_content ($row, $col_alias['impressions'], type_to_str($total['impressions'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['alternative_impressions'], type_to_str($total['alternative_impressions'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['clicks'], type_to_str($total['clicks'], 'integer'));
               $this->table_builder->set_cell_content ($row, $col_alias['revenue'], type_to_str($total['revenue'], 'money'));
               $ctr = $total['impressions']?$total['clicks']/$total['impressions']*100:0;
               $this->table_builder->set_cell_content ($row, $col_alias['ctr'], type_to_str($ctr, 'procent'));
               $this->table_builder->clear_row_attributes($row);
               $this->table_builder->add_row_attribute($row, 'class', 'alltotal');
      		   // render summary statistic for additional columns from plugins
		       foreach($this->_hooks as $hookObj) {
                 $hookObj->renderSummaryRow($row, $col_alias, $total, $this->table_builder);
		       }
           }

      // Устанавливаем возможность выбора колонок
      $this->table_builder->use_select_columns();
      $invariable_columns = array(
         $col_alias['chkboxes'], $col_alias['id'], $col_alias['url'], $col_alias['action']
      );
      $this->table_builder->set_invariable_columns($invariable_columns);

      //$this->table_builder->add_attribute ( 'class', 'xTable' ); //or set style here
      $table = $this->table_builder->get_sort_html ();

      $content = $this->parser->parse($this->views_paths['iframe_sites_list'],
                  array('MESSAGE' => json_encode(array('message' => __($message))),
                        'SITES_TABLE' => $table,
                        'COLUMNS' => $this->table_builder->get_columns_html(),
                        'PAGINATION' => $pagination,
                        'ID_CHANNEL' => $id_channel,
                        'JFRAME_ACTION' => '<%SITEURL%><%INDEXPAGE%>'.$this->role.'/manage_sites_channels/get_sites/'.$id_channel.'/'.$date_from.'/'.$date_to.'/all'),TRUE);
                  
      if ($pub_entity != $this->user_id) {
         $content = preg_replace("/\<a name=\"chan_buttons_begin\"\>\<\/a\>[\s\S]*?\<a name=\"chan_buttons_end\"\>\<\/a\>/", '', $content);                     
      }
                  
      //$this->template = "common/parent/iframe.html";
      $this->template = "common/parent/jq_iframe.html";
      $this->_set_content($content);
      $this->_display();
   } //end 
   
   /**
   * Add 
   * 
   * @author Evgeny Balashov
   * @project 
   * @version 1.0.0
   */
   protected function add_plugins($active_tab) {
   	
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
   	    
      switch ($active_tab) {
      	case 'sites_to_channels':
    		if ('admin' == $this->role) {
		      	if (isset($pluginsConfig->admin->manage_site_channels->sites)) {
		       		foreach($pluginsConfig->admin->manage_site_channels->sites as $hookClass) {
		       			$hookObj = new $hookClass();
		       			if ($hookObj instanceof Sppc_Common_ManageSiteChannels_SitesEventHandlerInterface) {
		       				$this->_hooks[] = $hookObj;
		       			}
		       		}
		      	}
		     } else {
		     	if (isset($pluginsConfig->publisher->manage_site_channels->sites)) {
		       		foreach($pluginsConfig->publisher->manage_site_channels->sites as $hookClass) {
		       			$hookObj = new $hookClass();
		       			if ($hookObj instanceof Sppc_Common_ManageSiteChannels_SitesEventHandlerInterface) {
		       				$this->_hooks[] = $hookObj;
		       			}
		       		}
		      	}
		     }
      	break;
         case 'site_chanels':
         if ('admin' == $this->role) {
               if (isset($pluginsConfig->admin->manage_site_channels->site_channels)) {
                  foreach($pluginsConfig->admin->manage_site_channels->site_channels as $hookClass) {
                     $hookObj = new $hookClass();
                     if ($hookObj instanceof Sppc_Common_ManageSiteChannels_ChannelsEventHandlerInterface) {
                        $this->_hooks[] = $hookObj;
                     }
                  }
               }
            } else {
               if (isset($pluginsConfig->publisher->manage_site_channels->site_channels)) {
                  foreach($pluginsConfig->publisher->manage_site_channels->site_channels as $hookClass) {
                     $hookObj = new $hookClass();
                     if ($hookObj instanceof Sppc_Common_ManageSiteChannels_ChannelsEventHandlerInterface) {
                        $this->_hooks[] = $hookObj;
                     }
   }
}
            }
         break;
         default:
            return false;
         break;
      }
      
      return true;
   } //end add_plugins()
   
} //adn class Common_Manage_Sites_Channels

?>