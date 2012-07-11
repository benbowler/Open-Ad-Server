<?php
require_once 'Sppc/Db/Table/Abstract.php';

/**
 * Model for working with wizard steps
 *
 * @author Sergey Revenko
 * @version $Id:$
 */
class Sppc_Wizard_StepModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'wizard_steps';
	/**
	 * Row class
	 * 
	 * @var string
	 */
	protected $_rowClass = 'Sppc_Wizard_Step';
	/**
	 * Reference map
	 * 
	 * @var array
	 */
	protected $_referenceMap = array(
		'Wizard' => array(
			'columns' 		=> 'id_wizard',
			'refTableClass' => 'Sppc_WizardModel',
			'refColumns' 	=> 'id_wizard',
			'onUpdate'		=> self::CASCADE,
			'onDelete'		=> self::CASCADE
		)
	);
	/**
	 * Return ordered list of steps from specified wizard
	 * 
	 * @return Sppc_Db_Table_Rowset
	 */
	public function findAllByWizard(Sppc_Wizard $wizard) {
		$steps = array();
		
		if (!is_null($wizard)) {
			$where = array(
				'id_wizard = ?' => $wizard->getId());
			
			$steps = $this->fetchAll($where, 'step ASC');
		}
		
		return $steps;
	}
	/**
	 * Find next step after selected
	 * 
	 * @param Sppc_Wizard_Step $step
	 * @return Sppc_Wizard_Step|null
	 */
	public function findNextStep(Sppc_Wizard_Step $step) {
		$nextStep = null;
		
		if (!is_null($step)) {
			$select = $this->select()
						->where('id_wizard = ?', $step->id_wizard)
						->where('step = ?', $step->getStep() + 1);
						
			$nextStep = $this->fetchRow($select);
		}
		
		return $nextStep;
	}
	/**
	 * Find previous step
	 * 
	 * @param Sppc_Wizard_Step $step
	 * @return Sppc_Wizard_Step|null
	 */
	public function findPreviousStep(Sppc_Wizard_Step $step) {
		$previousStep = null;
		if ((!is_null($step)) && ($step->getStep() > 0)) {
			$select = $this->select()
						->where('id_wizard = ?', $step->id_wizard)
						->where('step = ?', $step->getStep() - 1);
						
			$previousStep = $this->fetchRow($select);
		}
		
		return $previousStep;
	}
}