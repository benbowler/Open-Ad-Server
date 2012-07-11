<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

/**
 * Class which represent wizard
 *
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_Wizard extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Wizard steps
	 * 
	 * @var array
	 */
	protected $_steps = null;
	/**
	 * Return wizards steps
	 * 
	 * @return array
	 */
	public function getSteps() {
		if (is_null($this->_steps)) {
			$wizardStepModel = new Sppc_Wizard_StepModel();
			$this->_steps = array();
			
			$steps = $wizardStepModel->findAllByWizard($this);
			foreach($steps as $step) {
				$this->_steps[] = $step;
			} 
		}
		
		return $this->_steps;
	}
}