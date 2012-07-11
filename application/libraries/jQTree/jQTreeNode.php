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
  * @class jQTreeNode
  * @autor Omar Yepez, Rossana Suarez
  */

  include_once('IjQTreeNode.php');
  include_once('ListNode.php');
  include_once('HtmlProperties.php');

  class jQTreeNode extends HtmlProperties implements IjQtreeNode{
  	private $listNode;
    private $caption;
    private $isText;
    private $isLink;
    private $link;
    private $nodeId;

	public function __construct(){
		$this->setListNode();
	}

	/*
	 * Void function add Agrega un nodo ("Hijo") a otro nodo ("Padre")
	 * @param treeNode De tipo jQTreenode Es el nodo ("Hijo") a agregar.
	 * @exception Si el parametro no es de clase jQTreeNode o si el objeto es nulo;
	 * @see Funcion isNull()
	 */
	public function add($treeNode){
		if(get_class($treeNode) == get_class($this))
		{
			if($treeNode->isNull()){
				$listNode = $this->getListNode();
				$listNode->addNode($treeNode);
			}else{
				throw new Exception("The node is null");
			}

		}else{
			throw new Exception("Can't add this");
		}
	}

	/*
	 * Void function remove Elimina un nodo hijo
	 * 		dado su posicion detro del nodo padre
	 * @param key Posicion del nodo a eliminar
	 * @exception No Exception
	 * @see Class ListNode()
	 */
	public function remove($key){
		$listNode = $this->getListNode();
		$listNode->deleteNode($key);

	}

	/*
	 * Void function setParent Agrega el objeto actual de tipo
	 * 		jQTreeNode como nodo hijo de $nodeParent
	 * @param nodeParent De tipo jQTreenode Es el nodo ("Padre")
	 * 		del nodo actual una vez llamada la funcion.
	 * @exception Si nodeParent parametro no es de clase jQTreeNode;
	 * @see Function add()
	 */
	public function setParent($nodeParent){
		if(get_class($nodeParent) == get_class($this))
		{
			$nodeParent->add($this);
		}else{
			throw new Exception("Can't add this");
		}
	}

	/*
	 * Void function removeAll Elimina todos los nodos hijos
	 * 		que contenga el objeto actual
	 * @see Class ListNode()
	 */
	public function removeAll(){
		$this->setListNode();
	}

	/*
	 * @function init
	 * Iniciializa las funciones de creacion del arbol
	 * @param $type = html devuelve valores html
	 * $type = json devuelve objetos json, ($type default value = html)
	 */
	public function init($type = "html"){
		if(strtolower($type) == "html"){
		   return $this->createHtmlTree();
		}else{
		   if(strtolower($type) == "json"){
		   return "json";
		   }
		   else{
		   	throw new Exception ("Only type 'HTML' & 'JSON'" );
		   }

		}

	}
	/*
	 * @function inicia la creacion del arbol.
	 * Deberia ser llamada por el nodo de nivel mas alto (Modelo del arbol)
	 */
 	public function createHtmlTree(){
		(string)$output = "";
 	   if($this->isLeaf()){
			//$this->setClass("file");
         $this->setClass("file ".$this->getClass());
			$output .= $this->createLi();
			$output .= $this->closeLi();
		}else{
			//$this->setClass("folder");
         $this->setClass("folder ".$this->getClass());
			$output .= $this->createLi();
			$output .= $this->createUl();
			foreach($this->getChilds() as $node){
				$output .= $node->init();
			}
			$output .= $this->closeUl();
			$output .= $this->closeLi();
		}
		return $output;
	}

	/*
	 * @funciton Imprime por pantalla <ul>
	 */
	public function createUl(){
		(string)$rend = "";
		$rend .= $this->getUlTag();
		return $rend;
	}

	/*
	 * @funciton Imprime por pantalla <li>
	 */
	public function createLi(){
		(string)$rend = "";
		$rend .= $this->getLiTag();
		$rend .= $this->getSpanTag();
		$rend .= $this->getCaption();
		$rend .= $this->getCloseSpanTag();
		return $rend;
	}

	/*
	 * @funciton Imprime por pantalla </ul>
	 */
	public function closeUl(){
		(string)$rend = "";
		$rend .= $this->getCloseUlTag();
		return $rend;
	}

	/*
	 * @funciton Imprime por pantalla </li>
	 */
	public function closeLi(){
		(string)$rend = "";
		$rend .= $this->getCloseLiTag();
		return $rend;
	}


	/*
	 * Boolean function isNull ver @Return
	 * @return true si el objeto no tiene valores en sus propiedades
	 *  	excepto listNode de lo contrario devuelve false
	 * @see PHP_FUNCTIONS get_class_vars(), get_class()
	 */
	public function isNull(){
		(bool) $miBoolean = false;
		foreach (get_class_vars(get_class($this)) as $vars => $value){
			if(isset($this->$vars) && $vars != "listNode"){
				return true;
			}
		}
		return $miBoolean;
	}

	/*
	 * Integer function getChildCount ver @Return
	 * @return La catindad de nodos ("Hijos") que posee el actual objeto
	 * @see getListNode()
	 */
	public function getChildCount(){
		$count = $this->getListNode();
		return $count->getChildCount();
	}

	/*
	 * Array function getChilds
	 * @return Devuelve un arreglo de objetos jQTreeNode
	 * 		representando los nodos ("Hijos") del actual objeto
	 * @see getListNode()
	 */
	public function getChilds(){
		$array = array();
		$listNode = $this->getListNode();
		$array = $listNode->getMatchValues();
		return $array;
	}

	/*
	 * Boolean function isLeaf
	 * @return true si el objeto no tiene nodos("Hijos")
	 * 		"Es hoja" de lo contrario de vuelve false ("Es Rama - Padre")
	 * @see PHP_FUNCTIONS sizeof()
	 */
	public function isLeaf(){
		$numChilds = sizeof($this->getChilds());
		(bool) $isLeaf = ($numChilds <= 0) ? true : false;
		return $isLeaf;
	}


	/*
	 * Simple getters functions
	 */
	public function getListNode(){
		return $this->listNode;
	}

	public function getCaption(){
		return $this->caption;
	}

	/*
	 * Simple setters functions
	 */

	public function setCaption($caption){
		$this->caption = htmlentities($caption,ENT_QUOTES,"utf-8");
	}

	public function setListNode(){
		$this->listNode = new ListNode();
	}

  }





?>
