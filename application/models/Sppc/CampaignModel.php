<?php

class Sppc_CampaignModel extends Sppc_Db_Table_Abstract {
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'campaigns';
	
	/**
	 * Final all campaigns which belongs to specified entity and have specified status
	 * @param int $idEntity
	 * @param string $status
	 * @param string $order
	 * @return Sppc_Db_Table_Rowset
	 */
	public function findAllByEntityAndStatus($idEntity, $status = null, $order = null) {
		$select = $this->select()->where('id_entity_advertiser = ?', $idEntity);
		
		if (!is_null($status)) {
			if ($status == 'not_deleted') {
				$select->where('status <> ?', 'deleted');
			} else {
				$select->where('status = ?', $status);
			}
		}
		
		if (!is_null($order)) {
			$select->order($order);
		}
		
		return $this->fetchAll($select);
	}
	
	/**
	 * Return campaign with specified id and owner
	 * 
	 * @param int $campaignId
	 * @param int $entityId
	 * @return Sppc_Db_Table_Row
	 */
	public function findOneByIdAndOwner($campaignId, $entityId) {
		$select = $this->select()
			->where('id_campaign = ?', $campaignId)
			->where('id_entity_advertiser = ?', $entityId);
			
		return $this->fetchRow($select);
	}
}