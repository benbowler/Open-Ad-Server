<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/MY_Controller.php';

class Welcome extends MY_Controller {

	public function __construct()
	{
		parent::__construct();	
	}
	
	function index()
	{
           redirect("guest/home");
	}
}

?>