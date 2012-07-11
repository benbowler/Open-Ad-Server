<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' ))
	exit ( 'No direct script access allowed' );
/**
 * Контроллер обработки поиска по новой схеме
 *
 * @author Maxim Savenko
 * @version $Id$
 */
class Display_Search extends Controller {
	private $_idSite = 0;
	private $_width = 0;
	private $_height = 0;
	private $_idPalette = 0;
	private $_idUser = 0;
	private $_referer = '';
	private $_realReferer = '';
	private $_szTitle = false;
	private $_szDescription = false;
	private $_szUrl = false;
	private $_limit = 10;
	private $_query = "";
	
	public function __construct() {
		parent::__construct ();
		$this->load->library ( 'parser' );
		$this->load->model ( 'global_variables' );
		
		// Получаем нужные нам данные
		if (false !== $this->input->get ( 'id_site' )) {
			$this->_idSite = ( int ) $this->input->get ( 'id_site' );
		}
		if (false !== $this->input->get ( 'width' )) {
			$this->_width = ( int ) $this->input->get ( 'width' );
		}
		if (false !== $this->input->get ( 'height' )) {
			$this->_height = ( int ) $this->input->get ( 'height' );
		}
		if (false !== $this->input->get ( 'id_palette' )) {
			$this->_idPalette = ( int ) $this->input->get ( 'id_palette' );
		}
		if (false !== $this->input->get ( 'szt' )) {
			$this->_szTitle = ( int ) $this->input->get ( 'szt' );
		}
		if (false !== $this->input->get ( 'szd' )) {
			$this->_szDescription = ( int ) $this->input->get ( 'szd' );
		}
		if (false !== $this->input->get ( 'szu' )) {
			$this->_szUrl = ( int ) $this->input->get ( 'szu' );
		}
		if (false !== $this->input->get ( 'limit' )) {
			$this->_limit = ( int ) $this->input->get ( 'limit' );
		}
		if (false !== $this->input->get ( 'q' )) {
			$this->_query = $this->input->get ( 'q' );
		}
		if (false !== $this->input->get ( 'id_user' )) {
			$this->_idUser = ( int ) $this->input->get ( 'id_user' );
		}
		if (false !== $this->input->get ( 'ref' )) {
			$this->_referer = $this->input->get ( 'ref' );
		} else {
			$this->_referer = $this->input->server ( 'HTTP_REFERER' );
		}
		if (false !== $this->input->get ( 'sr' )) {
			$this->_realReferer = $this->input->get ( 'sr' );
		} else {
			$this->_realReferer = '';
		}
		// Избавляемся от кеширования
		header ( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header ( 'Cache-Control: post-check=0, pre-check=0', false );
		header ( 'Pragma: no-cache' );
		// Security
		header ( 'P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"' );
	}
	
	public function index() {
		
		$palette = $this->_getPalette ();
		$viewData = $palette;
		if ($this->_szTitle !== false) {
			$viewData ['title_font_size'] = $this->_szTitle;
		}
		if ($this->_szDescription !== false) {
			$viewData ['text_font_size'] = $this->_szDescription;
		}
		if ($this->_szUrl !== false) {
			$viewData ['url_font_size'] = $this->_szUrl;
		}
		
		$this->_width-=2;
		$this->_height-=2;
		
		$viewData['width'] = $this->_width;
		$viewData['height'] = $this->_height;
		
		$this->load->library ( 'search_builder' );
		$sb = Search_builder::getInstance ();
		$sb->disableUseStandartChannels ();
		$sb->disableGroupResults ();
		$sb->disableAlternativeStats ();
		
		$sb->setSearchType ( 'js' );
		$sb->setSite ( $this->_idSite );
		$sb->setCount ( $this->_limit );
		$sb->setDisplayAds(Search_builder::DISPLAY_ADV_XML);
		
		$sb->loadParameters ();
		$sb->setReferer ( $this->_referer );
		$sb->setRealReferer ( $this->_realReferer );
		
		$sb->loadOtherFeeds(false);
		
		$results = array ();
		if ($sb->search ()) {
			// Получаем результаты
			$results = $sb->getResults ();
		}
		$viewData['item_width'] = $this->_width;
		if(count($results)>0) {
			$viewData['item_height'] = floor($this->_height/count($results));
		}
		// Получаем код ошибки
		$error = $this->search_builder->getLastError ();	
		foreach ( $results as &$result ) {
			$result ['title'] = htmlentities($result ['title'],ENT_QUOTES,'UTF-8');
			$result ['display_url'] = htmlentities($result ['display_url'],ENT_QUOTES,'UTF-8');
			if (isset ( $result ['description2'] )) {
				$result ['description'] = '<span>' . htmlentities($result ['description1'],ENT_QUOTES,'UTF-8') . 
					'</span> <span>' . htmlentities($result ['description2'],ENT_QUOTES,'UTF-8') . '</span>';
			}
		}
		$viewData['results'] = $results;
		
		$this->parser->parse ( 'show_ads/iframe_search.html', $viewData );
	}
	/**
	 * Подгружаем инфу по палитре
	 * @return array
	 */
	private function _getPalette() {
		$palette = array ();
		$sql = "
         SELECT
            cs.border_color,
            tf.name AS title_font,
            cs.title_color,
            cs.title_font_size,
            cs.title_font_style,
            cs.title_font_weight,
            cs.background_color,
            xf.name AS text_font,
            cs.text_color,
            cs.text_font_size,
            cs.text_font_style,
            cs.text_font_weight,
            uf.name AS url_font,
            cs.url_color,
            cs.url_font_size,
            cs.url_font_style,
            cs.url_font_weight
         FROM
            color_schemes cs
               LEFT JOIN fonts tf ON (cs.title_id_font = tf.id_font)
               LEFT JOIN fonts xf ON (cs.text_id_font = xf.id_font)
               LEFT JOIN fonts uf ON (cs.url_id_font = uf.id_font)
         WHERE
            cs.id_color_scheme = " . $this->db->escape ( $this->_idPalette ) . " OR
            cs.id_entity_publisher = 0
         ORDER BY
            cs.id_entity_publisher DESC
         LIMIT
            1";
		
		$query = $this->db->query ( $sql );
		if (($query === false) || (0 < $query->num_rows ())) {
			$palette = $query->row_array ();
		}
		return $palette;
	}
}

