<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );

require_once APPPATH . 'controllers/parent_controller.php';

class Common_Site_Directory extends Parent_controller {
	
	protected $ad_type_filter = '';
	protected $image_size_filter = '';
	protected $cost_model_filter = '';
	protected $category_filter = '';
	protected $keyword_filter = '';
	
	protected $item_active = '';
	protected $item_passive = '';
	
	public function __construct() {
		parent::__construct ();
		$this->item_passive = $this->load->view ( 'common/site_directory/passive_item.html', '', TRUE );
		$this->item_active = $this->load->view ( 'common/site_directory/active_item.html', '', TRUE );
	}
	
	protected function sites_table($sitecode = null) {
		$this->load->library ( 'Table_Builder' );
		$this->table_builder->clear ();
		$this->table_builder->insert_empty_cells = false;
		$this->table_builder->init_sort_vars ( 'site_directory', 'name', 'asc' );
		$this->load->model ( 'pagination_post' );
		$this->pagination_post->set_form_name ( 'site_directory' );
		$this->load->model ( 'site' );
		
		$this->pagination_post->set_total_records( 
			$this->site->directory_total(
				$this->ad_type_filter, 
				$this->image_size_filter, 
				$this->cost_model_filter, 
				$this->category_filter, 
				$this->keyword_filter 
			) 
		);
		
		$siteInfo = null;
		if (! is_null ( $sitecode )) {
			$this->load->helper ( 'fields' );
			$siteId = type_cast ( $sitecode, 'textcode' );
			
			$siteInfo = $this->site->get_info ( $siteId );
			
			if (! is_null ( $siteInfo )) {
				$currentSitePosition = $this->site->directory_total(
					$this->ad_type_filter,
					$this->image_size_filter,
					$this->cost_model_filter,
					$this->category_filter,
					$this->keyword_filter,
					$siteInfo->name
				);
			}
		}
		
		if (is_null($siteInfo)) {
			$this->pagination_post->read_variables ( 'site_directory', 1, $this->global_variables->get ( 'SiteDirectoryPerPage' ) );
			$records = $this->site->directory_select ( 
				$this->temporary ['site_directory_page'], 
				$this->temporary ['site_directory_per_page'], 
				$this->table_builder->sort_field, 
				$this->table_builder->sort_direction, 
				$this->ad_type_filter, 
				$this->image_size_filter, 
				$this->cost_model_filter, 
				$this->category_filter, 
				$this->keyword_filter 
			);			
		} else {
			$sitesPerPage = $this->global_variables->get('SiteDirectoryPerPage');
			$currentPage = 1;
			if ($currentSitePosition > $sitesPerPage) {
				$currentPage = ceil($currentSitePosition / $sitesPerPage);	
			}
			 
			$this->pagination_post->read_variables ( 'site_directory', $currentPage, $sitesPerPage, true);
			
			$records = $this->site->directory_select (
				$currentPage,
				$sitesPerPage,
				$this->table_builder->sort_field, 
				$this->table_builder->sort_direction, 
				$this->ad_type_filter, 
				$this->image_size_filter, 
				$this->cost_model_filter, 
				$this->category_filter, 
				$this->keyword_filter 
			);
		}

		$this->table_builder->sorted_column ( 0, "name", __( "Site Description" ), "asc" );
		$this->table_builder->set_cell_content ( 0, 1, __( "Channels" ) );
		$this->table_builder->set_cell_content ( 0, 2, __( "Cost Models" ) );
		
		$this->table_builder->add_col_attribute ( 0, 'class', 'nohl cursor-hand' );
		$this->table_builder->add_col_attribute ( 1, 'class', 'center w100' );
		$this->table_builder->add_col_attribute ( 2, 'class', 'center w100' );
		$this->table_builder->add_row_attribute ( 0, 'class', 'th' );
		$this->table_builder->add_attribute ( 'class', 'xTable w100p' );
		
		$description_template = $this->parser->parse ( 'common/site_directory/description.html', array ('ROLE' => $this->role ) );
		
		$row = 1;
		foreach ( $records as $id => $record ) {
			$description = str_replace ( '<%NAME%>', limit_str_and_hint ( type_to_str ( $record ['name'], 'encode' ), 20 ), $description_template );
			$description = str_replace ( '<%URL%>', $record ['url'], $description );
			
			$description = str_replace ( '<%URLQANCAST%>', implode ( ".", array_reverse ( explode ( ".", preg_replace ( '/^.*?\.([a-zA-Z0-9-]+\.[a-zA-Z0-9-]+)/si', '$1', $record ['url'] ) ) ) ), $description );
			
			$site_code = type_to_str ( $id, 'textcode' );
			$description = str_replace ( '<%CODESITE%>', $site_code, $description );
			$record ['description'] = str_replace ( "\n", '<br>', type_to_str ( $record ['description'], 'encode' ) );
			$description = str_replace ( '<%DESCRIPTION%>', $record ['description'], $description );
			$description = str_replace ( '<%IMAGE%>', $this->site->get_thumb ( $id ), $description );
			
			$this->table_builder->set_cell_content ( $row, 0, $description );
			$ch_count = 0;
			$f_row = $row;
			$ad_type = array ();
			$cost_models = array ();
			if (isset ( $record ['channels'] )) {
				foreach ( $record ['channels'] as $channel ) {
					$cost_mods = explode ( ',', $channel ['cost_model'] );
					//$cost_models = array();
					foreach ( $cost_mods as $cost_model ) {
						if (trim ( $cost_model ) != '') {
							$cost_models [] = __( trim ( $cost_model ) );
						}
					}
					$allowedAdTypes = explode(',', $channel['ad_type']);
					if (in_array(Sppc_Channel::AD_TYPE_TEXT, $allowedAdTypes)) {
						$ad_type[] = __('Text');
					}
					if (in_array(Sppc_Channel::AD_TYPE_IMAGE, $allowedAdTypes)) {
						$ad_type[] = __('Image');
					}
				}
			}
			if ($ch_count == 0) {
				$this->table_builder->set_cell_content ( $row, 1, implode ( ', ', array_unique ( $ad_type ) ) );
				$this->table_builder->set_cell_content ( $row, 2, implode ( ', ', array_unique ( $cost_models ) ) );
			}
			$row ++;
			$this->table_builder->set_cell_content ( $row, 0, "<a name='$site_code'></a><div id='$site_code' class='site_info'></div>" );
			$this->table_builder->cell ( $row, 0 )->add_attribute ( 'colspan', 3 );
			$this->table_builder->cell ( $row, 0 )->add_attribute ( 'style', 'padding-top: 0px; padding-bottom: 0px; height: 0px;' );
			$row ++;
		}
		if (0 == count ( $records )) {
			$this->table_builder->insert_empty_cells = false;
			$this->table_builder->set_cell_content ( 1, 0, __( 'Records not found' ) );
			$this->table_builder->cell ( 1, 0 )->add_attribute ( 'colspan', 3 );
			$this->table_builder->cell ( 1, 0 )->add_attribute ( 'class', 'nodata' );
		}
		return array ('TABLE' => $this->table_builder->get_sort_html (), 'PAGINATION' => $this->pagination_post->create_form () );
	} //end sites_table   
	

	protected function item($name, $level = 0, $filter = NULL) {
		$html = '';
		while ( $level ) {
			$html .= '&nbsp;';
			$level --;
		}
		$template = is_null ( $filter ) ? 'item_passive' : 'item_active';
		$temp = str_replace ( '<%NAME%>', __( $name ), $this->$template );
		if (! is_null ( $filter )) {
			$temp = str_replace ( '<%FILTER%>', $filter, $temp );
		}
		return $html . $temp;
	} //end item   
	

	protected function get_ad_type_filter() {
		if ($this->ad_type_filter == '') {
			$html = $this->item ( 'Ad Type' );
			$html .= $this->item ( 'Text Ads', 1, 'adtype/text' );
			$html .= $this->item ( 'Image Ads', 1, 'adtype/image' );
		} else {
			$html = $this->item ( 'Ad Type', 0, 'adtype/all' );
			if ($this->ad_type_filter == 'text') {
				$html .= $this->item ( 'Text Ads', 1 );
			} else {
				$this->load->model ( 'dimension' );
				if ($this->image_size_filter == '') {
					$html .= $this->item ( 'Image Ads', 1 );
					$list = $this->dimension->get_list_all ();
					foreach ( $list as $record ) {
						$html .= $this->item ( $record ['name'], 2, "imagesize/{$record['id_dimension']}" );
					}
				} else {
					$html .= $this->item ( 'Image Ads', 1, 'imagesize/all' );
					$info = $this->dimension->get_info ( $this->image_size_filter );
					$html .= $this->item ( $info->name, 2 );
				}
			}
		}
		return $html;
	} //end get_ad_type_filter   
	

	protected function get_cost_model_filter() {
		if ($this->cost_model_filter == '') {
			$html = $this->item ( 'Cost Model' );
			$html .= $this->item ( 'CPM', 1, 'costmodel/cpm' );
			$html .= $this->item ( 'Flat Rate', 1, 'costmodel/flatrate' );
		} else {
			$html = $this->item ( 'Cost Model', 0, 'costmodel/all' );
			switch ($this->cost_model_filter) {
				case 'cpm' :
					$html .= $this->item ( 'CPM', 1 );
					break;
				case 'flatrate' :
					$html .= $this->item ( 'Flat Rate', 1 );
					break;
			}
		}
		return $html;
	} //end get_cost_model_filter   
	

	protected function get_category_filter() {
	   
		$this->load->model ( 'category_model' );
		if ($this->category_filter == '') {
			$html = $this->item ( 'Category' );
			$ids = $this->category_model->get_child_level ();
			$names = $this->category_model->get_names ( $ids );
			foreach ( $names as $id => $name ) {
				$html .= $this->item ( $name, 1, "category/$id" );
			}
		} else {
			$html = $this->item ( 'Category', 0, 'category/all' );
			$names = $this->category_model->get_chain ( $this->category_filter );
			$level = 1;
			foreach ( $names as $id => $name ) {
				$html .= $this->item ( $name, $level, ($id == $this->category_filter) ? NULL : "category/$id" );
				$level ++;
			}
			$ids = $this->category_model->get_child_level ( $this->category_filter );
			$names = $this->category_model->get_names ( $ids );
			foreach ( $names as $id => $name ) {
				$html .= $this->item ( $name, $level, "category/$id" );
			}
		}
		return $html;
	} //end get_cost_model_filter      
	

	/**
	 * возвращает HTML-код фильтра по ключевому слову
	 *
	 */
	protected function get_keyword_filter() {
		return $this->parser->parse ( 'common/site_directory/keyword_filter.html', array ('ROLE' => $this->role ) );
	} //end get_keyword_filter   
	

	public function index() {
		$this->load->library ( 'form' );
      $this->load->model('groups', '', TRUE);
      $this->groups->end_fl_status();
		$form = array ( 
		   'id' => 0,
		   'name' => 'site_directory_form',
		   'view' => 'common/site_directory/template.html',
		   'redirect' => $this->role . '/site_directory',
		   'no_errors' => 'true',
		   'vars' => array (
		      'ROLE' => $this->role,
		      'CURRENT_SITE_CODE' => $this->input->get ( 'site' ),
		      'CURRENT_CHANNEL_CODE' => $this->input->get ( 'channel' ) ),
		   'fields' => array (
		      'keyword_filter' => array (
		         'id_field_type' => 'string', 
		         'form_field_type' => 'text' 
		      ),
		      'ad_type_filter' => array (
		         'id_field_type' => 'string', 
		         'form_field_type' => 'select', 
		         'options' => array (
		            '' => __( 'All types' ), 
		            'text' => __( 'Text Ads' ), 
		            'image' => __( 'Image Ads' ),
		            'text,image' => __( 'Text & Image Ads' ),
		         )
		      ), 
		      'cost_model_filter' => array (
		         'id_field_type' => 'string', 
		         'form_field_type' => 'select', 
		         'options' => array (
		            '' => __( 'All models' ), 
		            'cpm' => __( 'CPM' ), 
		            'flatrate' => __( 'Flat Rate' )
		         ) 
		      ), 
		      'category_filter' => array (
		         'id_field_type' => 'string', 
		         'form_field_type' => 'select', 
		         'options' => 'category_model' 
		      ), 
		      'image_size_filter' => array (
		         'id_field_type' => 'string', 
		         'form_field_type' => 'select', 
		         'options' => 'dimension', 
		         'params' => array (
		            'all' => true 
		         ) 
		      ) 
		   ) 
		);
		
		$this->_set_title ( "Site Directory" );
		$this->_set_help_index ( "site_directory" );
		$html = $this->form->get_form_content ( 'modify', $form, $this->input, $this );
		
		$sitecode = null;
		if (false !== $this->input->get ( 'site' )) {
			$sitecode = $this->input->get ( 'site' );
		}
		$table = $this->sites_table ( $sitecode );
		$html = str_replace ( '<%TABLE%>', $table ['TABLE'], $html );
		$html = str_replace ( '<%PAGINATION%>', $table ['PAGINATION'], $html );
		$html = str_replace ( '<%SITE_CODE%>', $sitecode, $html );
		$this->_set_content ( $html );
		$this->_display ();
	
	} //end index
	

	/**
	 * Callback-функция, устанавливает значения по умолчанию для фильтров таблиц
	 *
	 * @return array массив со значениями по умолчанию для фильтров
	 */
	public function _load() {
		$fields = array ();
		
		$fields ['ad_type_filter'] = $this->ad_type_filter;
		$fields ['cost_model_filter'] = $this->cost_model_filter;
		$fields ['keyword_filter'] = $this->keyword_filter;
		$fields ['category_filter'] = $this->category_filter;
		$fields ['image_size_filter'] = $this->image_size_filter;
		
		return $fields;
	} //end _load   
	

	/**
	 * Callback-функция, проверяет введенные данные и сохраняет новый запрос отчета
	 *
	 * @param array $fields массив с полями заполненными пользователем
	 * @return string при неудаче - текст ошибки
	 */
	public function _create($fields) {
		$this->ad_type_filter = $fields ['ad_type_filter'];
		$this->cost_model_filter = $fields ['cost_model_filter'];
		$this->keyword_filter = $fields ['keyword_filter'];
		$this->category_filter = $fields ['category_filter'];
		if ($this->ad_type_filter == 'image') {
			$this->image_size_filter = $fields ['image_size_filter'];
		}
		return 'error';
	} //end create   


}

?>