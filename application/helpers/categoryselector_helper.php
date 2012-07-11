<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Хелпер для отрисовки элементов управления для выбора категорий
 */

if (!(function_exists('render_selected_categories'))) {
	/**
	 * Функция для отрисовки элемента управления для выбора нескольких категорий
	 * 
	 * @param array $categories Текущие выбранные категори
	 * @param string $form_field Id 
	 * @param $dialog_button
	 * @param $container
	 */
	function render_category_selector_field($categories = array(), $form_field, $dialog_button, $container, $max_categories = null) {
		
		$selectedCategories = array();
		foreach ($categories as $category) {
			/* @var $category Sppc_Category */
			$selectedCategories[] = array(
				'id' => $category->getId(),
				'name' => $category->getName()
			);
		}
		
		$viewData = array(
			'SELECTED_CATEGORIES' => json_encode($selectedCategories),
			'FORM_FIELD' => $form_field,
			'DIALOG_BUTTON' => $dialog_button,
			'CONTAINER_ELEMENT' => $container,
			'MAX_CATEGORIES' => (!is_null($max_categories)) ? $max_categories : 'null'
		);
		
		$CI = &get_instance();
		
		return $CI->parser->parse('common/category_selector/control.html', $viewData, true); 
	}
}