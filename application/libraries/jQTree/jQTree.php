<?php
/*
 *****************************************************************
 *  jQTree4PHP 1.0.0 PHP class to create Tree Views
 *  Copyright (c) 2008 YepSua soluciones tecnologicas
 *  Dual licensed under the MIT (MIT-LICENSE.txt)
 *  and GPL (GPL-LICENSE.txt) licenses.
 *
 *  $Date: 2008-08-08 01:35:25 -0400 (Fry, 08 Aug 2008) - (Viernes 08 de agosto de 2008) $
 *  IMPLEMENT
 *  ***** jquery 1.2.1 Copyright (c) 2007 John Resig (jquery.com)
 *  ***** jquery.treeview.js 1.4 Copyright (c) 2007 Jörn Zaefferer (http://docs.jquery.com/Plugins/Treeview)
 *
 ******************************************************************
 */

 /* @package com.ve.yepsua.UI.jQTree
  * @class jQTree
  * @autor Omar Yepez, Rossana Suarez
  */

  include_once('jQTreeNode.php');

  class jQTree extends jQTreeNode{
	private $type;
	private $model;
	private $treeId;

	/*
	 * @function 'setea' el modelo del arbol el nodo padre con todos sus nodos herederos.
	 */

	public function setModel($treeNode){
		if(get_class($treeNode) == 'jQTreeNode')
		{
			if($treeNode->isNull()){
				$this->model = $treeNode;
			}else{
				throw new Exception("The node is null");
			}

		}else{
			throw new Exception("Can't add this");
		}
	}

	/*
	 * @function getModel
	 * @return El modelo del arbol -> El nodo padre con el nivel mas alto de la herencia.
	 */

	public function getModel(){
		return $this->model;
	}

	/*
	 * @function getModel
	 * Funcion de tipo 'render' es la funcion que imprime los tags HTML que crean el arbol.
	 */
	public function getTree(){
		(string)$output = "";
	   $this->setId($this->getId());
		$this->setClass("filetree hide");
		$output .= $this->createUl();
		$output .= $this->getModel()->init();
		$output .= $this->closeUl();
		return $output;
	}


	public function setTreeId($treeId){
		$this->treeId = $treeId;
	}

	public function getTreeId(){
		return $this->treeId;
	}

  }

?>
