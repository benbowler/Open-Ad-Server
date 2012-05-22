<?php
/**
 * Опции для ImageBuilder'а
 * 
 * @author Sergey Revenko
 */
class Sppc_ImageBuilder_Options {
	/**
	 * Image library which will be used by ImageBuilder
	 * @var string
	 */
	protected $_imageLibrary = 'GD2';
	/**
	 * Absolute path to the source file
	 * @var string
	 */
	protected $_source = null;
	/**
	 * Max image width (in px)
	 * 
	 * @var int
	 */
	protected $_maxWidth = 800;
	/**
	 * Max image height (in px)
	 * 
	 * @var int
	 */
	protected $_maxHeight = 600;
	/**
	 * Image width
	 * 
	 * @var int
	 */
	protected $_width = null;
	/**
	 * Image height
	 * 
	 * @var int
	 */
	protected $_height = null;
	/**
	 * Use cache or not
	 * 
	 * @var bool
	 */
	protected $_useCache = true;
	/**
	 * Generated image quality
	 * 
	 * @var int
	 */
	protected $_quality = 90;
	/**
	 * Constructor
	 * 
	 * @param array $config Configration options
	 * @return void
	 */
	function __construct() {
	
	}
	/**
	 * Return image library
	 * 
	 * @return string
	 */
	public function getImageLibrary() {
		return $this->_imageLibrary;
	}

	/**
	 * Set image library
	 * @param string $_imageLibrary See constants in class Sppc_ImageBuilder_Library
	 */
	public function setImageLibrary($_imageLibrary) {
		$this->_imageLibrary = $_imageLibrary;
	}

	/**
	 * Get absolute path to the source file
	 * 
	 * @return string $_src
	 */
	public function getSource() {
		return $this->_source;
	}

	/**
	 * Set absolute path to source file
	 * 
	 * @param string $_src
	 */
	public function setSource($_src) {
		$this->_source = $_src;
	}

	/**
	 * Return max width (in px) of generated image
	 * 
	 * @return int
	 */
	public function getMaxWidth() {
		return $this->_maxWidth;
	}

	/**
	 * Set max width (in px) of generated image
	 * 
	 * @param int $_maxWidth 
	 */
	public function setMaxWidth($_maxWidth) {
		$this->_maxWidth = $_maxWidth;
	}
	
	/**
	 * Return max height (in px) of generated image
	 * 
	 * @return int
	 */
	public function getMaxHeight() {
		return $this->_maxHeight;
	}

	/**
	 * Set max height (in px) of generated image
	 * @param int $_maxHeight
	 */
	public function setMaxHeight($_maxHeight) {
		$this->_maxHeight = $_maxHeight;
	}

	/**
	 * Get if cache need to used or not
	 * 
	 * @return bool
	 */
	public function getUseCache() {
		return $this->_useCache;
	}
	/**
	 * Set if cache need to used or not
	 * 
	 * @param bool $useChache
	 */
	public function setUseCache(bool $useChache) {
		$this->_useCache = $useChache;
	}
	/**
	 * Return width (in px) of generated image
	 * 
	 * @return int
	 */
	public function getWidth() {
		return $this->_width;
	}

	/**
	 * Set width (in px) of generated image
	 * 
	 * @param int $_width
	 */
	public function setWidth($_width) {
		$this->_width = $_width;
	}

	/**
	 * Return height (in px) of genereated image
	 * 
	 * @return int
	 */
	public function getHeight() {
		return $this->_height;
	}

	/**
	 * Set height (in px) of generated image
	 * 
	 * @param int $_height
	 */
	public function setHeight($_height) {
		$this->_height = $_height;
	}
	/**
	 * Return qualite on generated images
	 * 
	 * @return int
	 */
	public function getQuality() {
		return $this->_quality;
	}
	/**
	 * Set qulity of generated image
	 * 
	 * @param int $quality From 1 to 100
	 */
	public function setQuality($quality) {
		$this->_quality = $quality;
	}

	/**
	 * Return string representation of object
	 * 
	 * @return string
	 */
	public function __toString() {
		return 	'Source:'.$this->getSource().';'.
				'Width:'.$this->getWidth().';'.
				'Height:'.$this->getHeight().';'.
				'MaxWidth:'.$this->getMaxWidth().';'.
				'MaxHeight:'.$this->getMaxHeight().';';
	}
}