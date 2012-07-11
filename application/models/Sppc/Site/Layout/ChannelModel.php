<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_Site_Layout_ChannelModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'site_layout_channels';
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
                    'onDelete' => self::CASCADE ),
            'Channel' => array (
                    'columns' => 'id_channel',
                    'refTableClass' => 'Sppc_ChannelModel',
                    'refColumns' => 'id_channel',
                    'onUpdate' => self::CASCADE,
                    'onDelete' => self::CASCADE ) );

    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array ();

}
