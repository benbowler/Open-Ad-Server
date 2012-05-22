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
  * @interface IjQTreeNode
  * @author Omar Yepez, Rossana Suarez
  */

  interface IjQTreeNode{

	function add($TreeNode);
	function remove($key);
	function setParent($TreeNode);
	function removeAll();
	function init($type = "html");

  }
?>
