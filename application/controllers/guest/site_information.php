<?php
if (! defined( 'BASEPATH' ) || ! defined( 'APPPATH' ))
    exit( 'No direct script access allowed' );

require_once APPPATH . 'controllers/common/site_information.php';

class Site_Information extends Common_Site_Information {

    protected $role = "guest";

    protected $menu_item = "Site Directory";

    public function __construct() {
        parent::__construct();
    }
}

?>