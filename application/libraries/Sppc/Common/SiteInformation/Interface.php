<?php

interface Sppc_Common_SiteInformation_Interface {
	/**
	 * Возвращает дополнительные каналы плугинов для сайта
	 * 
	 * @return array
	 */
	public function get_channels($pObj);
	
	/**
	 * Возвращает детализацию по каналу плагина (если это в самом деле канал плагина)
	 *
	 * @param array $params набор идентефикаторов канала
	 * @return bool false - канал не принадлежит плагину 
	 */
	public function get_channel_details($params);
	
}