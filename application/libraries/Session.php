<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Session Class
 *
 */
class CI_Session {

   var $sess_table_name         = 'sessions';
   var $sess_expiration         = 7200;
   var $sess_match_ip           = FALSE;
   var $sess_match_useragent    = TRUE;
   var $sess_cookie_name        = 'ci_session';
   var $sess_browser            = TRUE;
   var $cookie_prefix           = '';
   var $cookie_path             = '';
   var $cookie_domain           = '';
   var $flashdata_key           = 'flash';
   var $time_reference          = 'time';
   var $gc_probability          = 5;
   var $userdata                = array();
   var $CI;
   var $now;

   /**
    * Session Constructor
    *
    * The constructor runs the session routines automatically
    * whenever the class is instantiated.
    */      
   function CI_Session($params = array())
   {

      // Set the super object to a local variable for use throughout the class
      $this->CI =& get_instance();
      
      // Set all the session preferences, which can either be set 
      // manually via the $params array above or via the config file
      foreach (array('sess_table_name', 'sess_expiration', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'time_reference', 'cookie_prefix', 'sess_browser') as $key)
      {
         $this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
      }      
   
      // Load the string helper so we can use the strip_slashes() function
      $this->CI->load->helper('string');
      $this->CI->load->database();

      // Set the "now" time.  Can either be GMT or server time, based on the
      // config prefs.  We use this to set the "last activity" time
      $this->now = $this->_get_time();

      // Set the session length. If the session expiration is
      // set to zero we'll set the expiration two years from now.
      if ($this->sess_expiration == 0)
      {
         $this->sess_expiration = (60*60*24*365*2);
      }
                   
      // Set the cookie name
      $this->sess_cookie_name = $this->cookie_prefix.$this->sess_cookie_name;
   
      // Run the Session routine. If a session doesn't exist we'll 
      // create a new one.  If it does, we'll update it.
      if (!$this->sess_read()) {
         $this->sess_create();
      }
      
      // Delete 'old' flashdata (from last request)
      $this->_flashdata_sweep();
      
      // Mark all new flashdata as old (data will be deleted before next request)
      $this->_flashdata_mark();

      // Delete expired sessions if necessary
      $this->_sess_gc();

      log_message('debug', "Session routines successfully run");
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Fetch the current session data if it exists
    *
    * @access   public
    * @return   void
    */
   function sess_read() {
      $session_id = $this->CI->input->cookie($this->sess_cookie_name);
      if (false === $session_id) {
         return false;
      }
      $this->CI->db->select('user_data')
         ->from($this->sess_table_name)
         ->where('session_id', $session_id);
      if ($this->sess_match_ip) {
         $this->CI->db->where('ip_address', $this->CI->input->ip_address());
      }
      if ($this->sess_match_useragent) {
         $this->CI->db->where('user_agent', trim(substr($this->CI->input->user_agent(), 0, 50)));
      }
      $query = $this->CI->db->get();
      if (0 < $query->num_rows()) {
         $row = $query->row_array();
         $session = $this->_unserialize($row['user_data']);
         if (is_array($session)) {
            $this->userdata = $session;
         }
      } else {
         $this->sess_destroy();
         return false;
      }
      return true;
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Write the session data
    *
    * @access   public
    * @return   void
    */
   function sess_write() {
      $session_id = $this->CI->input->cookie($this->sess_cookie_name);
      if (false === $session_id) {
         return;
      }
      $userdata = $this->_serialize($this->userdata);
      $this->CI->db->where('session_id', $session_id)
         ->update($this->sess_table_name, array('last_activity' => $this->now, 'user_data' => $userdata));
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Create a new session
    *
    * @access   public
    * @return   void
    */
   function sess_create() {   
      $sessid = '';
      while (strlen($sessid) < 32) {
         $sessid .= mt_rand(0, mt_getrandmax());
      }
      
      // To make the session ID even more secure we'll combine it with the user's IP
      $sessid .= $this->CI->input->ip_address();
      
      $session_id = md5(uniqid($sessid, TRUE));
      $data = array(
         'session_id'    => $session_id,
         'ip_address'    => $this->CI->input->ip_address(),
         'user_agent'    => trim(substr($this->CI->input->user_agent(), 0, 50)),
         'last_activity' => $this->now
      );
      $this->CI->db->insert($this->sess_table_name, $data);
      // Write the cookie
      $this->_set_cookie($session_id);
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Destroy the current session
    *
    * @access   public
    * @return   void
    */
   function sess_destroy() {   
      // Kill the cookie
      setcookie(
         $this->sess_cookie_name,
         null,
         ($this->now - 31500000),
         $this->cookie_path,
         $this->cookie_domain,
         0
      );
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Fetch a specific item from the session array
    *
    * @access   public
    * @param   string
    * @return   string
    */      
   function userdata($item)
   {
      return ( ! isset($this->userdata[$item])) ? FALSE : $this->userdata[$item];
   }

   // --------------------------------------------------------------------
   
   /**
    * Fetch all session data
    *
    * @access   public
    * @return   mixed
    */   
   function all_userdata()
   {
      return ( ! isset($this->userdata)) ? FALSE : $this->userdata;
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Add or change data in the "userdata" array
    *
    * @access   public
    * @param   mixed
    * @param   string
    * @return   void
    */      
   function set_userdata($newdata = array(), $newval = '')
   {
      if (is_string($newdata))
      {
         $newdata = array($newdata => $newval);
      }
   
      if (count($newdata) > 0)
      {
         foreach ($newdata as $key => $val)
         {
            $this->userdata[$key] = $val;
         }
      }

      $this->sess_write();
   }
   
   // --------------------------------------------------------------------
   
   /**
    * Delete a session variable from the "userdata" array
    *
    * @access   array
    * @return   void
    */      
   function unset_userdata($newdata = array())
   {
      if (is_string($newdata))
      {
         $newdata = array($newdata => '');
      }
   
      if (count($newdata) > 0)
      {
         foreach ($newdata as $key => $val)
         {
            unset($this->userdata[$key]);
         }
      }
   
      $this->sess_write();
   }
   
   // ------------------------------------------------------------------------

   /**
    * Add or change flashdata, only available
    * until the next request
    *
    * @access   public
    * @param   mixed
    * @param   string
    * @return   void
    */
   function set_flashdata($newdata = array(), $newval = '')
   {
      if (is_string($newdata))
      {
         $newdata = array($newdata => $newval);
      }
      
      if (count($newdata) > 0)
      {
         foreach ($newdata as $key => $val)
         {
            $flashdata_key = $this->flashdata_key.':new:'.$key;
            $this->set_userdata($flashdata_key, $val);
         }
      }
   } 
   
   // ------------------------------------------------------------------------

   /**
    * Keeps existing flashdata available to next request.
    *
    * @access   public
    * @param   string
    * @return   void
    */
   function keep_flashdata($key)
   {
      // 'old' flashdata gets removed.  Here we mark all 
      // flashdata as 'new' to preserve it from _flashdata_sweep()
      // Note the function will return FALSE if the $key 
      // provided cannot be found
      $old_flashdata_key = $this->flashdata_key.':old:'.$key;
      $value = $this->userdata($old_flashdata_key);

      $new_flashdata_key = $this->flashdata_key.':new:'.$key;
      $this->set_userdata($new_flashdata_key, $value);
   }
   
   // ------------------------------------------------------------------------

   /**
    * Fetch a specific flashdata item from the session array
    *
    * @access   public
    * @param   string
    * @return   string
    */   
   function flashdata($key)
   {
      $flashdata_key = $this->flashdata_key.':old:'.$key;
      return $this->userdata($flashdata_key);
   }

   // ------------------------------------------------------------------------

   /**
    * Identifies flashdata as 'old' for removal
    * when _flashdata_sweep() runs.
    *
    * @access   private
    * @return   void
    */
   function _flashdata_mark()
   {
      $userdata = $this->all_userdata();
      foreach ($userdata as $name => $value)
      {
         $parts = explode(':new:', $name);
         if (is_array($parts) && count($parts) === 2)
         {
            $new_name = $this->flashdata_key.':old:'.$parts[1];
            $this->set_userdata($new_name, $value);
            $this->unset_userdata($name);
         }
      }
   }

   // ------------------------------------------------------------------------

   /**
    * Removes all flashdata marked as 'old'
    *
    * @access   private
    * @return   void
    */

   function _flashdata_sweep()
   {
      $userdata = $this->all_userdata();
      foreach ($userdata as $key => $value)
      {
         if (strpos($key, ':old:'))
         {
            $this->unset_userdata($key);
         }
      }

   }

   // --------------------------------------------------------------------
   
   /**
    * Get the "now" time
    *
    * @access   private
    * @return   string
    */
   function _get_time()
   {
      if (strtolower($this->time_reference) == 'gmt')
      {
         $now = time();
         $time = mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
      }
      else
      {
         $time = time();
      }
   
      return $time;
   }

   // --------------------------------------------------------------------
   
   /**
    * Write the session cookie
    *
    * @access   public
    * @return   void
    */
   function _set_cookie($cookie_data)
   {
      // Set the cookie
      setcookie(
         $this->sess_cookie_name,
         $cookie_data,
         $this->sess_browser ? 0 : $this->sess_expiration + time(),
         $this->cookie_path,
         $this->cookie_domain,
         0
      );
      
      if (!array_key_exists($this->sess_cookie_name, $_COOKIE)) {
         $_COOKIE[$this->sess_cookie_name] = $cookie_data;
      }
		       
   }

   // --------------------------------------------------------------------
   
   /**
    * Serialize an array
    *
    * This function first converts any slashes found in the array to a temporary
    * marker, so when it gets unserialized the slashes will be preserved
    *
    * @access   private
    * @param   array
    * @return   string
    */   
   function _serialize($data)
   {
      if (is_array($data))
      {
         foreach ($data as $key => $val)
         {
            $data[$key] = str_replace('\\', '{{slash}}', $val);
         }
      }
      else
      {
         $data = str_replace('\\', '{{slash}}', $data);
      }
      
      return serialize($data);
   }

   // --------------------------------------------------------------------
   
   /**
    * Unserialize
    *
    * This function unserializes a data string, then converts any
    * temporary slash markers back to actual slashes
    *
    * @access   private
    * @param   array
    * @return   string
    */      
   function _unserialize($data)
   {
      $data = @unserialize(strip_slashes($data));
      
      if (is_array($data))
      {
         foreach ($data as $key => $val)
         {
            $data[$key] = str_replace('{{slash}}', '\\', $val);
         }
         
         return $data;
      }
      
      return str_replace('{{slash}}', '\\', $data);
   }

   // --------------------------------------------------------------------
   
   /**
    * Garbage collection
    *
    * This deletes expired session rows from database
    * if the probability percentage is met
    *
    * @access   public
    * @return   void
    */
   function _sess_gc()
   {
      srand(time());
      if ((rand() % 100) < $this->gc_probability)
      {
         $expire = $this->now - $this->sess_expiration;
         
         $this->CI->db->where("last_activity < {$expire}");
         $this->CI->db->delete($this->sess_table_name);

         log_message('debug', 'Session garbage collection performed.');
      }
   }

   
}
// END Session Class
