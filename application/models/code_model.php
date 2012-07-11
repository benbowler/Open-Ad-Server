<?php  // -*- coding: UTF-8 -*-
if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'models/object_model.php';

class Code_Model extends Object_Model {
	
	public function __construct()	{
		parent::__construct();
		$this->_table_name = 'codes';
		$this->_id_field_name = 'id_code';
		log_message('debug', 'Code_Model Class Initialized');
	}
	
	/**
	 * Получение шаблона для кода заданного типа
	 *
	 * @param array $params
	 */
	public function get_code_template() {
	return "<script type=\"text/javascript\">
   var sppc_site      = '<%ID_SITE%>';
   var sppc_channel   = '<%ID_CHANNEL%>';
   var sppc_dimension = '<%ID_DIMENSION%>';
   var sppc_width     = '<%WIDTH%>';
   var sppc_height    = '<%HEIGHT%>';
   var sppc_palette   = '<%COLOR_SCHEME_ID%>';
   var sppc_user      = '<%ID_USER%>';
</script>
<script type=\"text/javascript\" src=\"<%SITEURL%><%INDEXPAGE%>show.js\"></script>";	
		
	}
}  