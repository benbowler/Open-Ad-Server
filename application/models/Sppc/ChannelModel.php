<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_ChannelModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'channels';
    
    /**
     * Row class
     * 
     * @var string
     */
    protected $_rowClass = 'Sppc_Channel';
    /**
     * Reference map
     * @var array
     */
    protected $_referenceMap = array (
       'Dimension' => array (
          'columns' => 'id_dimension',
          'refTableClass' => 'Sppc_DimensionModel',
          'refColumns' => 'id_dimension',
          'onUpdate' => self::CASCADE,
          'onDelete' => self::CASCADE ),
       'ParentSite' => array (
          'columns' => 'id_parent_site',
          'refTableClass' => 'Sppc_SiteModel',
          'refColumns' => 'id_site',
          'onUpdate' => self::CASCADE,
          'onDelete' => self::CASCADE));
    /**
     * Dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array (
            'Sppc_Site_ChannelModel',
            'Sppc_Site_Layout_ChannelModel',
            'Sppc_Channel_CategoryModel');

}
