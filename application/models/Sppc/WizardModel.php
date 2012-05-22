<?php
require_once 'Sppc/Db/Table/Abstract.php';

/**
 * Model for working with wizards
 * 
 * @author Sergey Revenko
 * @version $Id:$
 *
 */
class Sppc_WizardModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'wizards';
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Wizard';
	/**
	 * Dependent tables
	 * 
	 * @var array
	 */
	protected $_dependentTables = array (
		'Sppc_Wizard_StepModel'
	);
	/**
	 * Create new row
	 * 
	 * @param string $id Wizard Id
	 * @param string $title Wizard title
	 * @return Sppc_Wizard
	 */
	public function createRow($id, $title) {
		$data = array(
			'id_wizard' => $id,
			'title' => $title
		);
		
		return parent::createRow($data);
	}
	/**
	 * Create new row
	 * 
	 * @param string $id Wizard Id
	 * @param string $title Wizard title
	 * @return Sppc_Wizard
	 */
	public function fetchNew($id, $title) {
		return $this->createRow($id, $title);
	}
}