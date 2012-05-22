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
  * @class ListNode
  * @autor Omar Yepez, Rossana Suarez
  */

  class ListNode{
	private $nodeArray = array();
	private $key = 0;
	private $idArray = array();

	public function addNode($newNode)
	{
		$this->nodeArray[$this->getKey()] = $newNode;
		$this->setKey($this->getKey() + 1);
	}

	public function getChildCount(){
		(int) $count = 0;
			foreach($this->nodeArray as $key => $val){
				if($val != null){
					$count++;
				}
			}
		return $count;
	}

	public function getKey(){
		return $this->key;
	}

	public function getValue(){
		return $this->value;
	}

	public function deleteNode($key){
		try{
			unset($this->nodeArray[$key]);
		}catch (Exception $e){}
	}

	public function setKey($key){
		$this->key = $key;
	}

	public function getMatchValues(){
		$array = array();
		(int) $i=0;
		foreach($this->nodeArray as $key => $val){
			if($val != null){
				$array[++$i] = $val;
			}
		}
		return $array;
	}


  }
?>
