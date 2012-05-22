<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper functions for image generation
 * 
 * @author Sergey Revenko
 */
if (!function_exists('image_builder')) {
	/**
	 * Generate copy of source image according to specified options. Generated images are cached.
	 * 
	 * @param Sppc_ImageBuilder_Options $options
	 * @return string 
	 * @throws Sppc_ImageBuilder_Exception
	 */
	function image_builder(Sppc_ImageBuilder_Options $options) {
		try {
			$CI=& get_instance();
			
			$source = $options->getSource();
			if (empty($source)) {
				throw new Sppc_ImageBuilder_Exception('Source file not specified');
			}
			
			if (!file_exists($options->getSource())) {
				throw new Sppc_ImageBuilder_Exception('Source file not found');
			}
			
			//check if file is image
			$sourceInfo  = getimagesize($options->getSource());
			if ($sourceInfo === false) {
				throw new Sppc_ImageBuilder_Exception('Specified source is not image file');
			}
			//get source file width and height
			$sourceWidth = $sourceInfo[0];
			$sourceHeight = $sourceInfo[1];
			
			$pathInfo = pathinfo($options->getSource());
			$filename = md5($options->__toString()).'.'.$pathInfo['extension']; 			
			$absoluteFilePath = BASEPATH.'files/images/tmp/';
			
			if (($options->getUseCache() == false) || 
				(($options->getUseCache() == true) && (image_cache_exist($options) == false)))
			{
				//check if file exist
				if (!file_exists($options->getSource())) {
					throw new Sppc_ImageBuilder_Exception('Source file not found');
				}
				
				
				
				$imglibConfig = array();
				$imglibConfig['quality'] = $options->getQuality();
				$imglibConfig['source_image'] = $options->getSource();
				$imglibConfig['width'] = $sourceWidth;
				$imglibConfig['height'] = $sourceHeight;
				$imglibConfig['new_image'] = $absoluteFilePath.$filename;
				
				$CI->load->library('image_lib');
				
				if ((!is_null($options->getWidth())) || (!is_null($options->getHeight()))) {
					if (!is_null($options->getWidth()) && ($sourceWidth != $options->getWidth())) {
						$imglibConfig['width'] = $options->getWidth();
					}
					if (!is_null($options->getHeight()) && ($sourceHeight != $options->getHeight())) {
						$imglibConfig['height'] = $options->getHeight();
					}
					
					if ($sourceWidth > $sourceHeight) {
						$imglibConfig['master_dim'] = 'height';
					} else {
						$imglibConfig['master_dim'] = 'width';
					}

					
					$CI->image_lib->initialize($imglibConfig);
					if (!$CI->image_lib->resize()) {
						throw new Sppc_ImageBuilder_Exception($CI->image_lib->display_lib());
					}
					
					$generatedImageInfo = getimagesize($absoluteFilePath.$filename);
					if (($generatedImageInfo[0] != $options->getWidth()) ||
						($generatedImageInfo[1] != $options->getHeight()))
					{
						
						$imglibConfig['source_image'] = $imglibConfig['new_image'];
						unset($imglibConfig['new_image']);
						$imglibConfig['maintain_ratio'] = false;
						$imglibConfig['x_axis'] = 0;
						$imglibConfig['y_axis'] = 0;
						
						$CI->image_lib->initialize($imglibConfig);
						if (!$CI->image_lib->crop()) {
							throw new Sppc_ImageBuilder_Exception($CI->image_lib->display_lib());
						}
					}
				} else {
					if ((!is_null($options->getMaxWidth())) && ($sourceWidth > $options->getImageLibrary())) {
						$imglibConfig['width'] = $options->getMaxWidth();
					}
					
					if ((!is_null($options->getMaxHeight())) && ($sourceHeight > $options->getMaxHeight())) {
						$imglibConfig['height'] = $options->getMaxHeight();
					}
					
					$CI->image_lib->initialize($imglibConfig);
					if (!$CI->image_lib->resize()) {
						throw new Sppc_ImageBuilder_Exception($CI->image_lib->display_errors());
					}
				}
			}
			
			$result = $CI->config->item('base_url').'system/files/images/tmp/'.$filename;
		}
		catch (Exception $e) {
			return '';
		}
		return $result; 	
	}
}

if (!function_exists('image_cache_exist')) {
	/**
	 * Check if copy of source image exist in cache
	 * 
	 * @param Sppc_ImageBuilder_Options $options
	 * @return bool
	 * @throws Sppc_ImageBuilder_Exception
	 */
	function image_cache_exist(Sppc_ImageBuilder_Options $options) {
		$source = $options->getSource();
		if (empty($source)) {
			throw new Sppc_ImageBuilder_Exception('Source file not specified');
		}
		
		$pathInfo = pathinfo($options->getSource());
		
		$cacheImageFileName = BASEPATH . 'files/images/tmp/'.
			md5($options->__toString()).'.'.$pathInfo['extension'];
			
		return file_exists($cacheImageFileName);
	}
}