<?php

/**
 * Model for working with channel categories data
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_Channel_CategoryModel extends Sppc_Db_Table_Abstract {
   /**
    * Table name
    * 
    * @var string
    */
	protected $_name = 'channel_categories';
	
	/**
	 * Reference map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
	   'Channel' => array(
	      'columns' => 'id_channel',
	      'refTableClass' => 'Sppc_ChannelModel',
	      'refColumns' => 'id_channel',
	      'onUpdate' => self::CASCADE,
	      'onDelete' => self::CASCADE
	   ),
	   'Category' => array(
	      'columns' => 'id_category',
	      'refTableClass' => 'Sppc_CategoryModel',
	      'refColumns' => 'id_category',
	      'onUpdate' => self::CASCADE,
	      'onDelete' => self::CASCADE
	   )
	);
}
