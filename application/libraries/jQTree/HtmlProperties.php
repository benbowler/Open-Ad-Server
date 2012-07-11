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
  * @class HtmlProperties public class
  * @autor Omar Yepez, Rossana Suarez
  */

  class HtmlProperties
  {
	public $class;
	public $closed = "true";
	public $id;
	public $onclick;
	public $ondblclick;
	public $onkeydown;
	public $onkeypress;
	public $onkeyup;
	public $onmousedown;
	public $onmousemove;
	public $onmouseout;
	public $onmouseover;
	public $onmouseup;
	public $style;
	public $title;
	public $openChar = '<';
	public $barChar = '/';
	public $closeChar = '>';
	public $ul = 'ul';
	public $li = 'li';
	public $span = 'span';
	public $htmlProperties;
	public $htmlUlGroupProperties = array("class","id");
	public $htmlLiGroupProperties = array("class","id");
	public $htmlSpanGroupProperties = array("class","id","onclick",
									 "ondblclick","onkeydown",
									 "onkeypress","onkeyup","onmousedown",
									 "onmousemove","onmouseout","onmouseover",
									 "onmouseup","style","title");

	/*
	 * @function predefinicda de PHP 5.2 crea los set y los get de esta clase
	 * @param $method El nombre del metodo que es llamado
	 * @param $arguments Argumentos pasados a la funcion
	 */

	function __call($method, $arguments) {
        $prefix = strtolower(substr($method, 0, 3));
        $property = strtolower(substr($method, 3));
        if (empty($prefix) || empty($property)) {
            return;
        }
        if ($prefix == "get" && isset($this->$property)) {
            return $this->$property;
        }
        if ($prefix == "set") {       	
        	   $this->$property = $arguments[0];
        }
    }

    // public function : simple getters

    public function getOpenChar(){
		return $this->openChar;
    }

   	public function getCloseChar(){
		return $this->closeChar;
    }

    public function getBarChar(){
		return $this->barChar;
    }

    /*
     * @function getTagLi
     * @return retorna la etiqueta <li> de HTML
     */
    public function getLiTag(){
		(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getLi();
		if ($this->isClosed()){
			$tag .= ' class = "closed" ';
		}
		$tag .= $this->getClosechar();

		return $tag;
    }

	/*
     * @function getCloseTagLi
     * @return retorna la etiqueta HTML </li>
     */
    public function getCloseLiTag(){
    	(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getBarChar();
		$tag .= $this->getLi();
		$tag .= $this->getClosechar();
		return $tag;
    }

	/*
     * @function getUlTag
     * @return retorna la etiqueta HTML <ul> con algunas propiedades si estan 'seteadas'
     */
    public function getUlTag(){
		(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getUl();
		$tag .= $this->getHtmlGroupProperties("UL");
		$tag .= $this->getClosechar();

		return $tag;
    }

	/*
     * @function getCloseUlTag
     * @return retorna la etiqueta HTML </ul>
     */
    public function getCloseUlTag(){
    	(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getBarChar();
		$tag .= $this->getUl();
		$tag .= $this->getClosechar();
		return $tag;
    }


	/*
     * @function getSpanTag
     * @return la etiqueta HTML <span> con algunas propiedades si estan 'seteadas'
     */
    public function getSpanTag(){
		(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getSpan();
		$tag .= $this->getHtmlGroupProperties("SPAN");
		$tag .= $this->getClosechar();

		return $tag;
    }

    /*
     * @function getCloseSpanTag
     * @return la etiqueta HTML </span>
     */
    public function getCloseSpanTag(){
    	(string) $tag = "";
		$tag .= $this->getOpenChar();
		$tag .= $this->getBarChar();
		$tag .= $this->getSpan();
		$tag .= $this->getClosechar();
		return $tag;
    }

    /*
     * @function getArrayProperties
     * @param $type Indica cual de los arreglos de pro�edades se quiere.
     * @return un arreglo con las propiedades segun $type
     */

    public function getArrayProperties($type){
    	switch (strtoupper($type)) {
			case "UL":
				return $this->htmlUlGroupProperties;
				break;
			case "SPAN":
				return $this->htmlSpanGroupProperties;
				break;
			case "LI":
				return $this->htmlLiGroupProperties;
				break;
			default:
				break;
		}

    }

    /*
     * @function getHtmlGroupProperties
     * @param $type Indica cual de los arreglos de pro�edades se quiere.
     * @return un arreglo solamente con la propiedades que han sido 'seteadas'
     */
	public function getHtmlGroupProperties($type){
		(string) $htmlProperties = " ";
		foreach(get_class_vars(get_class($this)) as $key => $value){
			if(in_array($key,$this->getArrayProperties($type)) && isset($this->$key)){
				$htmlProperties .= $key . '="' . $this->$key . '" ' ;
			}
		}
		return $htmlProperties;
    }

    /*
     * @function isClosed
     * @return si el nodo comenzara desplegado o no
     */

    public function isClosed(){
		return $this->closed;
    }

	// public function : simple setters
    public function setIsClosed($isClosed){
		$this->closed = $isClosed;
    }

}

?>
