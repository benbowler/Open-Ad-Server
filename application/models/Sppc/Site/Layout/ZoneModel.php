<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_Site_Layout_ZoneModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'site_layout_zones';
    /**
     * Reference map
     * @var array
     */
    protected $_referenceMap = array (
            'Layout' => array (
                    'columns' => 'id_site_layout',
                    'refTableClass' => 'Sppc_Site_LayoutModel',
                    'refColumns' => 'id_site_layout',
                    'onUpdate' => self::CASCADE,
                    'onDelete' => self::CASCADE ) );
    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array (
            'Sppc_Site_Layout_ZoneModel' );

    /**
     * Find zone by JsonZone
     *
     * @param Sppc_Site_Layout $siteLayout
     * @param integer $idJsonZone
     * @return Sppc_Db_Table_Row_Abstract
     */
    public function findByJsonZone(Sppc_Site_Layout $siteLayout, $idJsonZone) {
        $select = $this->select();
        $select->where( 'id_site_layout=?', $siteLayout->getId() );
        $select->where( 'id_json_zone=?', $idJsonZone );
        return $this->fetchRow( $select );
    }
}
