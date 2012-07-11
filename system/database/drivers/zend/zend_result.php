<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// --------------------------------------------------------------------

/**
 * MySQL Result Class
 *
 * This class extends the parent result class: CI_DB_result
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_zend_result extends CI_DB_result {

   var $pdo_results = '';
   var $pdo_index = 0;
   
   /**
	 * Number of rows in the result set
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_rows()
	{
        if (!$this->pdo_results)
        {
            $this->pdo_results = $this->result_id->fetchAll(PDO::FETCH_ASSOC);
        }

        return sizeof($this->pdo_results);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Number of fields in the result set
	 *
	 * @access	public
	 * @return	integer
	 */
	function num_fields()
	{
        if (is_array($this->pdo_results))
        {
            return sizeof($this->pdo_results[$this->pdo_index]);
        }
        else
        {
            return $this->result_id->columnCount();
        }
	}
	
   // --------------------------------------------------------------------

	/**
	 * Result - associative array
	 *
	 * Returns the result set as an array
	 *
	 * @access	private
	 * @return	array
	 */
	function _fetch_assoc()
	{
        if (is_array($this->pdo_results))
        {
            $i = $this->pdo_index;
            $this->pdo_index++;

            if (isset($this->pdo_results[$i]))
            {
                return $this->pdo_results[$i];
            }

            return null;
        }

        return $this->result_id->fetch();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Result - object
	 *
	 * Returns the result set as an object
	 *
	 * @access	private
	 * @return	object
	 */
	function _fetch_object()
	{
        if (is_array($this->pdo_results))
        {
            $i = $this->pdo_index;
            $this->pdo_index++;

            if (isset($this->pdo_results[$i]))
            {
                $back = '';

                foreach ($this->pdo_results[$i] as $key => $val)
                {
                    $back->$key = $val;
                }

                return $back;
            }

            return null;
        }

        return $this->result_id->fetchObject();
	}
	
}


/* End of file mysql_result.php */
/* Location: ./system/database/drivers/mysql/mysql_result.php */