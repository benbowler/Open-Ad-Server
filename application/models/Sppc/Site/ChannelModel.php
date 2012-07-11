<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_Site_ChannelModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'site_channels';
    /**
     * Reference map
     * @var array
     */
    protected $_referenceMap = array (
            'Site' => array (
                    'columns' => 'id_site',
                    'refTableClass' => 'Sppc_SiteModel',
                    'refColumns' => 'id_site',
                    'onUpdate' => self::CASCADE,
                    'onDelete' => self::CASCADE ),
            'Channel' => array (
                    'columns' => 'id_channel',
                    'refTableClass' => 'Sppc_ChannelModel',
                    'refColumns' => 'id_channel',
                    'onUpdate' => self::CASCADE,
                    'onDelete' => self::CASCADE ) );

}
