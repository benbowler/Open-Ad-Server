<?php

interface Sppc_Menu_HelpItems_Interface {
	
	/**
	 * Return additional items for help submenu
	 * 
	 * @param string $role Current user role
	 * @param array $menuItems Menu items which already registered by other plugins
	 * @return array
	 */
	public function getMenuItems($role, $menuItems);
}