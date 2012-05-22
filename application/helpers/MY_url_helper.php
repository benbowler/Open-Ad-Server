<?php

if (!function_exists('site_rel_path')) {
	/**
	 * Return public path to file
	 * 
	 * @param string $filePath
	 * @return string
	 */
	function site_rel_path($filePath) {
		$rootPath = str_replace('system/', '', BASEPATH);
		$siteRelPath = str_replace($rootPath, '', $filePath);
		return base_url().$siteRelPath;
	}
}