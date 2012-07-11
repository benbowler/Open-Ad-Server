<?php
require_once 'Sppc/Db/Table/Row/Abstract.php';

/**
 * Class which represent single wizard step
 *
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_Wizard_Step extends Sppc_Db_Table_Row_Abstract {
	/**
	 * Wizard to which this step belongs
	 * 
	 * @var Sppc_Wizard
	 */
	protected $_wizard = null;
	/**
	 * Next step
	 * 
	 * Sppc_Wizard_Step
	 */
	protected $_nextStep = null;
	/**
	 * Previous step
	 * 
	 * Sppc_Wizard_Step
	 */
	protected $_previousStep = null;
	/**
	 * Get current step wizard
	 * 
	 * @return Sppc_Wizard
	 */
	public function getWizard() {
		if (is_null($this->_wizard)) {
			$this->_wizard = $this->findParentRow('Sppc_WizardModel', 'Wizard');
		}
		return $this->_wizard;
	}
	/**
	 * Return next wizard step
	 * 
	 * @return Sppc_Wizard_Step|null
	 */
	public function getNextStep() {
		if (is_null($this->_nextStep)) {
			$this->_nextStep = $this->_table->findNextStep($this);
		}
		return $this->_nextStep;
	}
	/**
	 * Return previous wizard step
	 * 
	 * @return Sppc_Wizard_Step|null
	 */
	public function getPreviousStep() {
		if (is_null($this->_previousStep)) {
			$this->_previousStep = $this->_table->findPreviousStep($this);
		}
		
		return $this->_previousStep;
	}
	/**
	 * Check if step is first in the wizard
	 * 
	 * @return bool
	 */
	public function isFirstStep() {
		if ($this->step == 0) {
			return true;
		}
		return false;
	}
	/**
	 * Check if step is last in the wizard
	 * 
	 * @return bool
	 */
	public function isLastStep() {
		if (is_null($this->getNextStep())) {
			return true;
		}
		
		return false;
	}
}