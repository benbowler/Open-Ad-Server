<?php

/**
 * Model for working with tags
 * 
 * @author Sergey Revenko
 * @version $Id$
 */
class Sppc_TagModel extends Sppc_Db_Table_Abstract {
   /**
    * Table name
    * 
    * @var string
    */
	protected $_name = 'tags';
	
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Tag';
	
   /**
	 * Create new row
	 * 
	 * @param Sppc_Channel $channel
	 * @param $code
	 * @return Sppc_Tag
	 */
	public function createRow(Sppc_Channel $channel,  $code) {
		$data = array(
		   'id_tag' => $channel->getId(),
		   'code' => $code,
		);
		
		return parent::createRow($data);
	}
}