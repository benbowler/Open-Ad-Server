<?php // -*- coding: UTF-8 -*-

require_once(APPPATH . 'libraries/MY_Loader_SSL.php');

class MY_Loader extends CI_Loader {
   // All these are set automatically. Don't mess with them.
   var $_my_licence = NULL;

   function __construct() {
      parent::__construct();

      $this->_my_licence = $this->_load_licence_file(APPPATH . '/signs/licence.xml');
   }
   // --------------------------------------------------------------------
   /**
    * @fn mixed _load_x509_cert(string $filename)
    * @brief A function which loads X509 formated certificate.
    *
    * This function loads X509 formated certificate with its attributes
    * into associative array. The attributes are:
    *    MD5_FINGERPRINT  - the fingerprint of X509 certificate (by MD5 hash algorithm)
    *    SERIAL           - the serial number of X509 certificate in hex representation
    *    SERIAL_DEC       - the serial number of X509 certificate in decimal representation
    *    SUBJECT_KEY_IDENTIFIER - the extended key identifier
    *    CERT             - the X509 object (must be free with openssl_x509_free(...) when unneeded
    *
    * @param $filename a string which contains name of the X509 certificate file.
    * @return an associative array which contains X509 cerificate object and its attributes or FALSE
    *         on failures.
    */
   function _load_x509_cert($filename) {
      if (file_exists($filename)) {
         $x509_cert = array();

         // Some X509 attributes require for us.
         $cmd = 'openssl x509 -serial -md5 -fingerprint -noout -in ' . escapeshellcmd($filename) . ' 2> /dev/null';
         $handle = popen($cmd, 'r');
         if ($handle) {
            while (!feof($handle)) {
               $read = fgets($handle, 4096);
               if (trim($read) == "") continue;

               $tmp = split("=", $read);
               $x509_cert[strtoupper(preg_replace('/\s/', '_',trim($tmp[0])))] = trim($tmp[1]);
            }
            pclose($handle);
         }

         // Read some extra X509 attributes via x509 functions.
         $ssl_CA_cert = file_get_contents($filename);

         $ssl_X509_cert = openssl_x509_read($ssl_CA_cert);
         if ($ssl_X509_cert !== false) {
            $ssl_X509_cert_info = openssl_x509_parse($ssl_X509_cert,false);

            if (array_key_exists("serialNumber", $ssl_X509_cert_info)) {
               $x509_cert['SERIAL_DEC'] = trim($ssl_X509_cert_info["serialNumber"]);
            }

            if (array_key_exists("extensions", $ssl_X509_cert_info)) {
               if (array_key_exists("subjectKeyIdentifier", $ssl_X509_cert_info["extensions"])) {
                  $x509_cert["SUBJECT_KEY_IDENTIFIER"] = trim($ssl_X509_cert_info["extensions"]["subjectKeyIdentifier"]);
               }

               if (array_key_exists("authorityKeyIdentifier", $ssl_X509_cert_info["extensions"])) {
                  $x509_cert["AUTHORITY_KEY_IDENTIFIER"] = trim($ssl_X509_cert_info["extensions"]["authorityKeyIdentifier"]);
               }
            }
            $x509_cert['CERT'] = $ssl_X509_cert;
            //openssl_x509_free($ssl_X509_cert);

            ksort($x509_cert);
            return $x509_cert;
         }
      }
      return false;
   }
   // --------------------------------------------------------------------
   /**
    * @fn resource _load_CA(void)
    * @brief A function which loads SmartPPC6 CA certificate.
    *
    * This function loads SmartPPC6 CA certificate from file and checks its attributes
    * (such as SERIAL, MD5_FINGERPRINT) for validity of SmartPPC6 system.
    *
    * The SmartPPC6 constants for validity checking are defined in helpers/_sppc6_private.php
    * file.
    *
    * @return a x509 resource object or FALSE on failures
    */
   function _load_CA() {
      if (!defined('SSL_CA_SERIAL') || !defined('SSL_CA_MD5_FINGERPRINT') || !defined('SSL_CA_SUBJECT_KEY_IDENTIFIER')) return false;

      $x509_cert = $this->_load_x509_cert(SSL_CA_FILE);

      if ($x509_cert !== false) {
         $is_valid = true;

         if ($is_valid === true && $x509_cert['SERIAL'] != SSL_CA_SERIAL) $is_valid = false;
         if ($is_valid === true && $x509_cert['MD5_FINGERPRINT'] != SSL_CA_MD5_FINGERPRINT) $is_valid = false;
         if ($is_valid === true && $x509_cert['SUBJECT_KEY_IDENTIFIER'] != SSL_CA_SUBJECT_KEY_IDENTIFIER) $is_valid = false;
         if ($is_valid === true) return $x509_cert['CERT'];

         openssl_x509_free($x509_cert['CERT']);
         unset($x509_cert['CERT']);
      }
      return false;
   }
   // --------------------------------------------------------------------
   /**
    * @fn resource _load_cert(void)
    * @brief A function which loads SmartPPC6 main certificate.
    *
    * This function loads SmartPPC6 main certificate from file and checks its attributes
    * (such as SERIAL, MD5_FINGERPRINT) for validity of SmartPPC6 system.
    *
    * The SmartPPC6 constants for validity checking are defined in helpers/_sppc6_private.php
    * file.
    *
    * @return a x509 resource object or FALSE on failures
    */
   function _load_cert() {
      if (!defined('SSL_CERT_SERIAL') || !defined('SSL_CERT_MD5_FINGERPRINT') || !defined('SSL_CERT_SUBJECT_KEY_IDENTIFIER')) return false;

      $x509_cert = $this->_load_x509_cert(SSL_CERT_FILE);

      if ($x509_cert !== false) {
         $is_valid = true;

         if ($is_valid === true && $x509_cert['SERIAL'] != SSL_CERT_SERIAL) $is_valid = false;
         if ($is_valid === true && $x509_cert['MD5_FINGERPRINT'] != SSL_CERT_MD5_FINGERPRINT) $is_valid = false;
         if ($is_valid === true && $x509_cert['SUBJECT_KEY_IDENTIFIER'] != SSL_CERT_SUBJECT_KEY_IDENTIFIER) $is_valid = false;
         if ($is_valid === true) return $x509_cert['CERT'];

         openssl_x509_free($x509_cert['CERT']);
         unset($x509_cert['CERT']);
      }
      return false;
   }
   // --------------------------------------------------------------------
   /**
    * @fn string _load_signed_file(string $filename)
    * @brief A function which loads the data from SMIME siggned file.
    *
    * This function loads the data from SMIME (PKCS7) signed file, verify file sign for validity of
    * SmartPPC6 system.
    *
    * @param $filename a string which contains name of the SMIME signed file.
    * @return a string which contains contents of SMIME signed file if sign is valid and sign was made
    *         with valid SmartPPC6 cerificate or FALSE on failures.
    */
   function _load_signed_file($filename) {
      if (!file_exists($filename)) return false;

      // check CA certificate for validity of smartppc6
      $x509 = $this->_load_CA();
      if ($x509 === false) return false;
      openssl_x509_free($x509);

      // check signer certificate for validity of smartppc6
      $x509 = $this->_load_cert();
      if ($x509 === false) return false;
      openssl_x509_free($x509);

      $signer_cert_out = tempnam('/tmp', 'smartppc6');
      $content_out = tempnam('/tmp', 'smartppc6');

      $result_content = false;
      $phpversion =  phpversion();

      if (version_compare($phpversion, "5.1.0", "<")) {
         $cmd = sprintf('openssl smime -signer %s -verify -in %s -nointern -nochain -CAfile %s -certfile %s -out %s 2> /dev/null',
            escapeshellcmd($signer_cert_out), escapeshellcmd($filename),
            escapeshellcmd(SSL_CA_FILE), escapeshellcmd(SSL_CERT_FILE),
            escapeshellcmd($content_out));

         system($cmd, $retval);
         if ($retval == 0) $result = true;
      }
      else {
         $result = openssl_pkcs7_verify(
            $filename, PKCS7_NOVERIFY, $signer_cert_out,
            array( SSL_CA_FILE ), SSL_CERT_FILE,
            $content_out);
      }

      if ($result === true) $result_content = file_get_contents($content_out);

      if (file_exists($signer_cert_out)) unlink($signer_cert_out);
      if (file_exists($content_out)) unlink($content_out);

      return $result_content;
   }
   // --------------------------------------------------------------------
   /**
    * @fn DOMDocument _load_licence_file(string $licence_xml)
    * @brief A function which loads the data from SMIME siggned licence file.
    *
    * This function loads the data from SMIME (PKCS7) signed licence file, verify file sign for validity of
    * SmartPPC6 system.
    *
    * @param $licence_xml a string which contains name of the SMIME signed licence file.
    * @return a dom object which contains LICENCE data or FALSE on failures.
    */
   function _load_licence_file($licence_xml) {
      if (!file_exists($licence_xml)) return false;

      $licence = $this->_load_signed_file($licence_xml); // file_get_contents
      if ($licence == "") return false;

      $dom = new DOMDocument('1.0', 'UTF-8');

      if ($dom->loadXML($licence) !== false && $dom->documentElement->tagName === "licence") {
         // Look for licence expiration time
         $valid = NULL;
         foreach ($dom->documentElement->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) continue;
            if ($node->tagName === 'valid') {
               $valid = $node;
               break;
            }
         }

         // If the licence expiration date was found, then check this.
         if (!is_null($valid) && is_a($valid,'DOMElement')) {
            $date = trim($valid->getAttribute('till'));
            if (strtotime($date) < strtotime('now') /*|| TRUE*/) {
               // The licence is expired
               return false;
            }
         }

         return $dom;
      }
      return false;
   }
   // --------------------------------------------------------------------
   /**
    * @fn array _module_lookup(string $module_name)
    * @brief A function which searchs the data of plugin in SmartPPC6 LICENCE.
    *
    * This function searchs the data of plugin in SmartPPC6 LICENCE.
    *
    * @param $licence a dom object which contains LICENCE data.
    * @param $module_name a string which contains name/identifier of module_name.
    * @return an array which contains all plugin attributes was given form SmartPPC6 LICENCE
    *         or FALSE on failures.
    */
   function _module_lookup($module_name) {
      if (is_null($this->_my_licence)) return false;
      if (!is_a($this->_my_licence,'DOMDocument')) return false;

      $modules = NULL;
      foreach ($this->_my_licence->documentElement->childNodes as $node) {
         if ($node->nodeType !== XML_ELEMENT_NODE) continue;
         if ($node->tagName === 'plugins') { //!!! 'modules'
            $modules = $node;
            break;
         }
      }

      if (is_null($modules)) return false;
      if (!is_a($modules,'DOMElement')) return false;

      $module = NULL;
      foreach ($modules->childNodes as $node) {
         if ($node->nodeType !== XML_ELEMENT_NODE) continue;
         if ($node->tagName !== 'plugin') continue; //!!! 'module'
         if ($node->hasAttribute('id') &&  $node->getAttribute('id') == $module_name) {
            $module = $node;
            break;
         }
      }

      if (is_null($modules)) return false;
      if (!is_a($modules,'DOMElement')) return false;

      $module = NULL;
      foreach ($modules->childNodes as $node) {
         if ($node->nodeType !== XML_ELEMENT_NODE) continue;
         if ($node->tagName !== 'plugin') continue;
         if ($node->hasAttribute('id') &&  $node->getAttribute('id') == $module_name) {
            $module = $node;
            break;
         }
      }

      if (is_null($module)) return false;
      if (!is_a($module,'DOMElement')) return false;

      $result = array (
         'id'        => $module->getAttribute('id'),
         'path'      => $module->getAttribute('path'),
         'md5'       => $module->getAttribute('md5'),
         'sha1'      => $module->getAttribute('sha1'),
         'required'  => $module->getAttribute('required'),
         'version'   => $module->getAttribute('version')
      );

      return $result;
   }
   // --------------------------------------------------------------------
   /**
    * Autoloader (oveload)
    *
    * The config/autoload.php file contains an array that permits sub-systems,
    * libraries, plugins, and helpers to be loaded automatically.
    *
    * @access  private
    * @param   array
    * @return  void
    */
   function _ci_autoloader() {
      include_once(APPPATH.'config/autoload'.EXT);

      if ( ! isset($autoload))  {
         return FALSE;
      }

      // Load any custom config file
      if (count($autoload['config']) > 0) {
         $CI =& get_instance();
         foreach ($autoload['config'] as $key => $val)  {
            $CI->config->load($val);
         }
      }

      // Autoload plugins, helpers and languages
      foreach (array('helper', 'plugin', 'language') as $type) {
         if (isset($autoload[$type]) AND count($autoload[$type]) > 0) {
            $this->$type($autoload[$type]);
         }
      }

      // A little tweak to remain backward compatible
      // The $autoload['core'] item was deprecated
      if ( ! isset($autoload['libraries'])) {
         $autoload['libraries'] = $autoload['core'];
      }

      // Load libraries
      if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)  {
         // Load the database driver.
         if (in_array('database', $autoload['libraries'])) {
            $this->database();
            $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
         }

         // Load scaffolding
         if (in_array('scaffolding', $autoload['libraries'])) {
            $this->scaffolding();
            $autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
         }

         // Load all other libraries
         foreach ($autoload['libraries'] as $item) {
            $this->library($item);
         }
      }

      // Autoload models
      if (isset($autoload['model'])) {
         $this->model($autoload['model']);
      }

      // Autoload modules
      if (isset($autoload['modules'])) {
         $this->module($autoload['modules']);
      }
   }
   // --------------------------------------------------------------------
   /**
    * Class Loader
    *
    * This function lets users load and instantiate classes.
    * It is designed to be called from a user's app controllers.
    *
    * @access      public
    * @param       string  the name of the class
    * @param       mixed   the optional parameters
    * @return      void
    */
   function module($library = '', $params = NULL) {
      if (is_null($this->_my_licence)) return FALSE;
      if (!is_a($this->_my_licence,'DOMDocument')) return FALSE;

      if ($library == '') {
         return FALSE;
      }

      if (is_array($library)) {
         foreach ($library as $class) {
            $this->_my_load_class($class, $params);
         }
      }
      else {
         $this->_my_load_class($library, $params);
      }

      $this->_ci_assign_to_models();
   }
   // --------------------------------------------------------------------
   /**
    * Load class
    *
    * This function loads the requested class.
    *
    * @access      private
    * @param       string  the item that is being loaded
    * @param       mixed   any additional parameters
    * @return      void
    */
   function _my_load_class($class, $params = NULL) {
      $module =  $this->_module_lookup($class);

      // check the hash of the file
      if (array_key_exists('md5',$module)) {
         $md5 = trim($module['md5']);
         if (!$this->_my_check_md5($class,$md5)) {
            return FALSE;
         }
      }

      // load all requred modules
      if (array_key_exists('required',$module)) {
         $requires = split(',',$module['required']);

         foreach ($requires as $require) {
            if (trim($require) == '') continue;
            if ($this->_my_load_class(trim($require), $params) === false) return false;
         }
      }
      parent::_ci_load_class($class, $params);
   }
   // --------------------------------------------------------------------
   /**
    * Check md5 hash for a module
    *
    * This function checks the md5 hash for the requred module
    *
    * @access private
    * @param $class - the name of the class to check
    * @param $md5 - stored md5 hash for the module
    * @return If the md5 hash is correct return TRUE, else return FALSE
    */
   function _my_check_md5($class,$md5) {
      // Get the class name
      $class = str_replace(EXT, '', $class);

      // We'll test for both lowercase and capitalized versions of the file name
      foreach (array(ucfirst($class), strtolower($class)) as $class) {
         // Lets search for the requested library file and load it.
         for ($i = 1; $i < 3; $i++) {
            $path = ($i % 2) ? APPPATH : BASEPATH; 
            $filepath = $path.'libraries/'.$class.EXT;

            // Does the file exist?  No?  Bummer...
            if ( ! file_exists($filepath)) {
               continue;
            }

            $md5_calc = md5(file_get_contents($filepath));
            if ($md5_calc == $md5 || TRUE) return TRUE;
         }
      } // END FOREACH
      return FALSE;
   }
   
  	/**
	 * Метод отгрузки модельки
	 *
	 * @param string $name
	 */
	function unload_model($name) {
	   $key = array_search($name, $this->_ci_models);
	   if (false !== $key) {
	      unset($this->_ci_models[$key]);
	   }
	   $CI =& get_instance();
	   unset($CI->$name);
	}

}

?>