<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2010 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @module Ads by Orbitscripts
 * @copyright Copyright (C) OrbitScripts LLC, orbitscripts.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');// no direct access

$keys = $params->get('keys');
if (!empty($keys)) { 
	$keys = unserialize(str_replace("'",'"',$keys));
    $module_css_style	= trim( $params->get( 'module_css_style' ) );
	if ($module_css_style) echo '<div style="'.$module_css_style.'">';

	echo "<script type=\"text/javascript\">
 		var sppc_site      = '".$keys['idsite']."';
    	var sppc_channel   = '".$keys['idchannel']."';
    	var sppc_dimension = '".$keys['dimension']."';
    	var sppc_width     = '".$keys['width']."';
    	var sppc_height    = '".$keys['height']."';
    	var sppc_palette   = '".$keys['idpalette']."';
    	var sppc_user      = '".$keys['iduser']."';
		</script>
		<script type=\"text/javascript\" src=\"".$keys['siteurl']."/show.js\"></script>";
	
	if ($module_css_style) echo '</div>';
}
?>