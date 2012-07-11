<?php

interface Sppc_Common_ManageSiteChannels_SitesEventHandlerInterface {
	/**
	 * Extends (add/remove columns) column map for campaigns table
	 * 
	 * @param array $colMap
	 * @return array Modified column map
	 */
	public function extendColumnMap(array $colMap);
	/**
	 * Create additional columns
	 * 
	 * @param array $colMap
	 * @param Table_Builder $tableBuilder
	 * @return void
	 */
	public function createColumns(array $colMap, Table_Builder $tableBuilder);
	/**
	 * Define styles for additional columns
	 * 
	 * @param array $colMap
	 * @param Table_Builder $tableBuilder
	 * @return void
	 */
	public function defineColumnStyles(array $colMap, Table_Builder $tableBuilder);
	/**
	 * Register additional per page statistic fields
	 * 
	 * @param array $fields
	 * @return array
	 */
	public function registerPerPageStatisticFields(array $fields);
	/**
	 * Calculate perpage statistic for additional columns
	 * 
	 * @param array $perPageStatistic
	 * @param array $rowData
	 * @return array
	 */
	public function calculatePerPageStatistic(array $perPageStatistic, array $rowData);
	/**
	 * Render row data
	 * 
	 * @param integer $row Row number
	 * @param array $colMap Columns map
	 * @param array $data Row data
	 * @param Table_Builder $tableBuilder
	 * @return void
	 */
	public function renderRow($row, array $colMap, array $data, Table_Builder $tableBuilder);
	/**
	 * Render page statistic for additional columns
	 * 
	 * @param int $row
	 * @param array $colMap
	 * @param array $rowData
	 * @param Table_Builder $tableBuilder
	 * @return void
	 */
	public function renderPageStatisticRow($row, array $colMap, array $rowData, Table_Builder $tableBuilder);
	/**
	 * Render summary row
	 * 
	 * @param int $row
	 * @param array $colMap
	 * @param array $rowData
	 * @param Table_Builder $tableBuilder
	 * @return void
	 */
	public function renderSummaryRow($row, array $colMap, array $rowData, Table_Builder $tableBuilder);
	
	/**
	 * Extends (add) query fields to params
	 * 
	 * @param  array  $params
	 * @param  string $tableName
	 * @return array Modified $params
	 */
	public function extendColumnQueryFields(array $params, $tableName = 'stat_sites');
	
} //end interface Sppc_Publisher_ManageSiteChannels_SitesEventHandlerInterface