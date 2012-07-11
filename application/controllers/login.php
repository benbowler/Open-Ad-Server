<?php

class Login extends Controller {

	function Login()
	{
		parent::Controller();	
	}
	
	function index()
	{
      redirect("guest");
	}
}

?>