<?php

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50207) {
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}

class Checker_PHP {
	private $_phpVersion = null;
	private $_extensions = array ();
	private $_extensionsTest=array(
//		'Curl',
//		'Gettext',
		'Mbstring',
		'Pdo',
		'PdoSqlite',
		'PdoMysql',
		'Iconv',
		'Simplexml',
//		'Soap',
		'Json'
//		'ZendOptimizer'
	);
	private $_extensionsMissed=array();
	private $_optionsWrong=array();
   private $_permissionWrong=array();
   private $_permissionTest=array(
      '../install',
      '../uploads',
      '../js',
      '../css',
      '../application/logs',
      '../application/cache',
      '../files',
      '../application/config/plugins',
      '../application/config'
   );
	public function __construct() {
		$this->_extensions = get_loaded_extensions ();
		$this->_extensions = array_flip($this->_extensions);
	}
	
	public function checkPhpVersion() {
		try {
			if(PHP_MAJOR_VERSION != 5) {
				throw new Exception(PHP_VERSION_ID);
			}
			if(PHP_MINOR_VERSION < 1) {
				throw new Exception(PHP_VERSION_ID);
			}
		}
		catch(Exception $e) {
			$this->_phpVersion = "PHP version 5.1.x and higher is required.\nYou have to install new PHP";
		}
	}
	
	public function checkCurl() {
		if(array_key_exists('curl',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='Curl';
	}
	public function checkGettext() {
		if(array_key_exists('gettext',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='Gettext';		
	}
	public function checkMbstring() {
		if(array_key_exists('mbstring',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='Mbstring';		
	}
	public function checkPdo() {
		if(array_key_exists('PDO',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='PDO';		
	}
	public function checkPdoSqlite() {
		if(array_key_exists('pdo_sqlite',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='PDO Sqlite';		
	}
	public function checkPdoMysql() {
		if(array_key_exists('pdo_mysql',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='PDO MySQL';		
	}
	public function checkIconv() {
		if(array_key_exists('iconv',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='iconv';		
	}
	public function checkSimplexml() {
		if(array_key_exists('SimpleXML',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='SimpleXML';		
	}
	public function checkSoap() {
		if(array_key_exists('soap',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='SOAP';		
	}
	public function checkJson() {
		if(array_key_exists('json',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='Json';		
	}
	public function checkZendOptimizer() {
		if(array_key_exists('Zend Optimizer',$this->_extensions)===true) {
			return;
		}
		$this->_extensionsMissed[]='Zend Optimizer';		
	}
   
	public function checkOptions() {
		$value = ini_get('allow_url_fopen');
		if($value!=='1') {
			$this->_optionsWrong[]= 'allow_url_fopen - have to be enabled';
		}
		$value = ini_get('disable_functions');		
		if(!empty($value)) {
			$this->_optionsWrong[]= "disable_functions - have to be disabled";
		}
//		$value = ini_get('enable_dl');
//		if($value!=='1') {
//			$this->_optionsWrong[]= 'enable_dl - have to be enabled';
//		}
		$value = ini_get('safe_mode');
		if(!empty($value)) {
			$this->_optionsWrong[]= 'safe_mode - have to be disabled';
		}
	}
   	public function checkReadable() {
         $permission = is_readable('install.xml');
         $permission = $permission && is_readable('../application/config/database.php');
         $permission = $permission && is_readable('../application/config/config.php');
         foreach ($this->_permissionTest as $test){
            $permission = $permission && is_readable($test);
         }
         return($permission);
      }
   	public function checkWritable() {
         $permission = is_writable('install.xml');
         $permission = $permission && is_writable('../application/config/database.php');
         $permission = $permission && is_writable('../application/config/config.php');
         foreach ($this->_permissionTest as $test){
            $permission = $permission && is_writable($test);
         }
         return($permission);
      }
      
	public function checkPermission() {
      if(!($this->checkWritable() && $this->checkReadable())){
         system('chmod -R 777 ../install ../uploads ../application/logs ../application/cache ../system/files ../temp ../js ../css ../application/config/plugins ../application/config');
         $change=`chmod -R 777 ../install ../uploads ../application/logs ../application/cache ../system/files ../temp ../js ../css ../application/config/plugins ../application/config`;
         clearstatcache();
         if(!($this->checkWritable() && $this->checkReadable())){
            $this->_permissionWrong[]= 'install';
            $this->_permissionWrong[]= 'uploads';
            $this->_permissionWrong[]= 'js';
            $this->_permissionWrong[]= 'css';
            $this->_permissionWrong[]= 'application/logs';
            $this->_permissionWrong[]= 'application/cache';
            $this->_permissionWrong[]= 'files';
            $this->_permissionWrong[]= 'application/config/plugins';
            $this->_permissionWrong[]= 'application/config';
            $this->_permissionWrong[]= 'Execute following command: chmod -R 777 install  uploads application/logs application/cache files js css application/config/plugins application/config';
         }
      }
		
	}

	public function run() {
		$this->checkPhpVersion();
		foreach($this->_extensionsTest as $ext) {
			$method = 'check'.$ext;
			$this->{$method}();
		}
		$this->checkOptions();
      $this->checkPermission();
	}

	public function toString() {
		if(!is_null($this->_phpVersion)) {
			$lines[]=$this->_phpVersion;
		}
		if(count($this->_extensionsMissed)>0) {
			$lines[]="You have to install the following PHP extensions: \n ";
			$lines[]=implode(",\n",$this->_extensionsMissed);
		}
		if(count($this->_optionsWrong)>0) {
			$lines[]="You have to configure your PHP with following option's values:";
			foreach ($this->_optionsWrong as $o) {
				$lines[]=$o;
			}
		}
		if(count($this->_permissionWrong)>0) {
			$lines[]="You need to set permission '777' (recursively) for the following folders:";
			foreach ($this->_permissionWrong as $p) {
				$lines[]=$p;
			}
		}
		if(count($lines)>0) {
			array_unshift($lines,"Your server's PHP doesn't meet our requirements.");
		} else {
			$lines[]="Your server's PHP meets our requirements";
		}		
		return implode("\n", $lines);		
	}

	public function __toString() {
		return $this->toString();	
	}
	
	public function is_ok() {
	   return 
	      is_null($this->_phpVersion) &&
         count($this->_extensionsMissed) == 0 &&
         count($this->_optionsWrong) == 0 &&
         count($this->_permissionWrong) == 0;
	}
	
}