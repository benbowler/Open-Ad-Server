<?php
require_once 'Sppc/Entity/Role/Abstract.php';

class Sppc_Entity_Role_Member extends Sppc_Entity_Role_Abstract {
	/**
	 * Member data
	 * @var Sppc_Member
	 */
	protected $_member = null;
	/**
	 * Get member data
	 * @return Sppc_Member
	 */
	protected function _getMember() {
		if (is_null($this->_member)) {
			$memberModel = new Sppc_MemberModel();
			$this->_member = $memberModel->findByEntity($this->getEntity());
		}
		return $this->_surfer;
	}
	
	/**
	 * Add custom logic on row insert
	 * @return void
	 */
	protected function _insert() {
		$memberModel = new Sppc_MemberModel();
		$member = $memberModel->createRow($this->getEntity());
		$member->save();
	}
	/**
	 * Add custom logic on row delete
	 * @return void
	 */
	protected function _delete() {
		$member = $this->_getMember();
		if (!is_null($member)) $member->delete();
	}
}