<?php
if (! defined ( 'BASEPATH' ) || ! defined ( 'APPPATH' )) exit ( 'No direct script access allowed' );
require_once APPPATH . 'controllers/parent_controller.php';

/**
 * Controller for getting info about categories
 * 
 * @author Sergey Revenko
 * @version $Id:$
 */
class Categories extends Parent_controller {
	/**
	 * Role
	 * 
	 * @var string
	 */
	protected $role = 'guest';
	/**
	 * Get category description
	 * 
	 * @return void
	 */
	public function get_description() {
		try {
			$categoryId = $this->input->post('id_category');
			if (empty($categoryId)) {
				throw new Sppc_Exception('Category not specified');
			}
			
			$categoryModel = new Sppc_CategoryModel();
			$category = $categoryModel->findObjectById($categoryId);
			if (is_null($category)) {
				throw new Sppc_Exception('Specified category not found');
			}
			
			$response = array(
				'result' => 'ok',
				'description' => $category->getDescription()
			);
			
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array('result' => 'error', 'error' => $e->getMessage()));
		}
	}
	
	/**
	 * Return children of specified category
	 * 
	 * @retunr string Children data in JSON format
	 */
	public function get_children() {
		header('Content-Type: application/json');
		
		try {
			$categoryId = $this->input->get('id');
			
			if (false === $categoryId) {
				throw new Exception('Parent category not specified');
			}
			
			$categoryModel = new Sppc_CategoryModel();
			$category = $categoryModel->findObjectById($categoryId);
			
			if (is_null($category)) {
				throw new Exception('Specified category not found');
			}
			
			$children = $category->getChildCategories();
			$response = array();
			
			foreach ($children as $child) {
				$childCategory = array(
					'attributes' => array('id' => 'category_'.$child->getId()),
					'data' => $child->getName()
				);
				if ($child->hasChildren()) {
					$childCategory['state'] = 'closed';
				}
				 
				$response[] = $childCategory; 
			}
			
			
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array());
		}
	}
}