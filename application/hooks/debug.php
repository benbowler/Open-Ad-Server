<?php

function set_debug_mode () {
	$debug_mode=config_item('debug_mode');
	if ($debug_mode) {
		$allow_ips=config_item('debug_ips');
		//explode ips
		$allow_ips=str_replace(' ','',$allow_ips);
		$allow_ips=explode(',',$allow_ips);
		foreach ($allow_ips as $key=>$ip) {
			$allow_ips[$key]=explode('.',$ip);
		}
		//explode user ip
		//$user_ip=$_SERVER['REMOTE_ADDR'];
      $user_ip=getenv('REMOTE_ADDR');
      if (!empty($user_ip)) {
		$user_ip=explode('.',$user_ip);
		foreach($allow_ips as $allow_ip){
			if ($allow_ip[0]==$user_ip[0] && 
			($allow_ip[1]==$user_ip[1] || $allow_ip[1]=='*') &&
			($allow_ip[2]==$user_ip[2] || $allow_ip[2]=='*') &&
			($allow_ip[3]==$user_ip[3] || $allow_ip[3]=='*')) {
				define('DEBUG_MODE',true);
				error_reporting(E_ALL);
				ini_set('display_errors',1);
				return;
			}
		}
      }
	//} else {
	//	define('DEBUG_MODE',false);
	//	return;
	}
	define('DEBUG_MODE',false);
}