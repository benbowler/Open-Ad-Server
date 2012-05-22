<?php
if (! defined( 'BASEPATH' ) || ! defined( 'APPPATH' ))
    exit( 'No direct script access allowed' );

require_once APPPATH . 'controllers/common/edit_site_channel_layout.php';

/**
 * Set position of channels on the site
 *
 * @version $Id$
 */
class Edit_Site_Channel_Layout extends Common_Edit_Site_Channel_Layout {

    protected $role = "admin";

    public function __construct() {
        parent::__construct();
        
        $this->_set_title( implode( self::TITLE_SEP, array (
                __( 'Administrator' ),
                __( 'Ad Placing' ),
                __( 'Set channel layout for this site' ) ) ) );

    } //end __construct


}
