<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Хелпер для конвертирования кнопок формы
* 
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/

if (!function_exists('get_attr')) {
   /**
   * заменяет нужные теги html-кода на код кнопок 
   *
   * @param string $html исходный html-код
   * @return string результирующий html-код
   */ 
   function get_attr($tag, $attr) {
   	$matches = array();
   	$res = preg_match('~(?i:'.$attr.')=(\'|")([\s\S]*?)\1~', $tag, $matches);
   	if (count($matches) == 0) {
   		return '';
   	}
   	return $matches[2];
   }
}

if (!function_exists('make_buttons')) {
   /**
   * заменяет нужные теги html-кода на код кнопок 
   *
   * @param string $html исходный html-код
   * @return string результирующий html-код
   */ 
   function make_buttons($html) {
      
   	$matches = array();

   	$res = preg_match_all('~<(?i:a)[^>]*?>([^<]*?)</(?i:a)>~', $html, $matches);
   	for($i=0; $i<$res; $i++){
   		$classes = get_attr($matches[0][$i], 'class');
   		if (is_numeric(strpos($classes, 'guibutton'))) {
				$href = '#';
				$alt = '';
				if (is_numeric(strpos($classes, 'thickbox'))) {
	     			$href = get_attr($matches[0][$i], 'alt');
					$alt = '';
				}
   			$onclick = str_replace('"', "'", get_attr($matches[0][$i], 'onclick'));
            $id = get_attr($matches[0][$i], 'id');
            // thickbox needs that
            $jframe = (get_attr($matches[0][$i], 'jframe') == 'no') ? "jframe=\"no\"" : "";
            $disabled = (get_attr($matches[0][$i], 'disabled') == 'disabled') ? "disabled=\"disabled\"" : "";
            $title = get_attr($matches[0][$i], 'title');
            $text = $matches[1][$i];
	         $html = str_replace($matches[0][$i], 
	            "<a id=\"$id\" $disabled class=\"$classes\" alt=\"$alt\" href=\"$href\" title=\"$title\" onclick=\"$onclick\"><b><b><b>$text</b></b></b></a>",$html);
   		}
     	}

     	$res = preg_match_all('~<(?i:input)[^>]*?(?i:type)=(\'|")(?i:submit|button)\1[^>]*?>~', $html, $matches);
     	
      for($i=0; $i<$res; $i++){
         $classes = get_attr($matches[0][$i], 'class');
         if (is_numeric(strpos($classes, 'guibutton'))) {
            $onclick = str_replace('"', "'", get_attr($matches[0][$i], 'onclick'));
            $value = get_attr($matches[0][$i], 'value');
            $id = get_attr($matches[0][$i], 'id');
            $disabled = (get_attr($matches[0][$i], 'disabled') == 'disabled') ? "disabled=\"disabled\"" : "";
            // thickbox needs that
				$href = '#';
				$alt = '';
				if (is_numeric(strpos($classes, 'thickbox'))) {
	     			$href = get_attr($matches[0][$i], 'alt');
					$alt = '';
				}
            $jframe = (get_attr($matches[0][$i], 'jframe') == 'no') ? "jframe=\"no\"" : "";
            $title = get_attr($matches[0][$i], 'title');
            $text = $value;
            $html = str_replace($matches[0][$i], 
               "<a id=\"$id\" $disabled class=\"$classes\" alt=\"$alt\" href=\"$href\" title=\"$title\" onclick=\"$onclick\"><b><b><b>$text</b></b></b></a>",$html);
         }
      }
      
      $res = preg_match_all('~<(?i:button)[^>]*?(?i:type)=(\'|")(?i:submit|button)\1[^>]*?>([^<]*?)</(?i:button)>~', $html, $matches);
      for($i=0; $i<$res; $i++){
         $classes = get_attr($matches[0][$i], 'class');
         if (is_numeric(strpos($classes, 'guibutton'))) {
            $onclick = str_replace('"', "'", get_attr($matches[0][$i], 'onclick'));
            $id = get_attr($matches[0][$i], 'id');
            $disabled = (get_attr($matches[0][$i], 'disabled') == 'disabled') ? "disabled=\"disabled\"" : "";
            // thickbox needs that
            $href = '#';
				$alt = '';
				if (is_numeric(strpos($classes, 'thickbox'))) {
	     			$href = get_attr($matches[0][$i], 'alt');
					$alt = '';
				}
            $jframe = (get_attr($matches[0][$i], 'jframe') == 'no') ? "jframe=\"no\"" : "";
            $title = get_attr($matches[0][$i], 'title');
				$text = $matches[2][$i];
            $html = str_replace($matches[0][$i], 
               "<a id=\"$id\" $disabled class=\"$classes\" alt=\"$alt\" href=\"$href\" title=\"$title\" onclick=\"$onclick\"><b><b><b>$text</b></b></b></a>",$html);
         }
      }

      $res = preg_match_all('~<(?i:select)[^>]*?>[\s\S]*?</(?i:select)>~', $html, $matches);
      for($i=0; $i<$res; $i++){
         $classes = get_attr($matches[0][$i], 'class');
         if (is_numeric(strpos($classes, 'guibutton'))) {
            $id = get_attr($matches[0][$i], 'id');
            $disabled = (get_attr($matches[0][$i], 'disabled') == 'disabled') ? "disabled=\"disabled\"" : "";
            $jframe = (get_attr($matches[0][$i], 'jframe') == 'no') ? "jframe=\"no\"" : "";
            $options = array();
            $ocnt = preg_match_all('~<(?i:option)[^>]*?>([\s\S]*?)</(?i:option)>~', $matches[0][$i], $options);
            $new_select = '';
            $selected_text = $options[1][0];
            $selected_onclick = str_replace('"', "'", get_attr($options[0][0], 'onclick'));
            for($j=0; $j<$ocnt; $j++) {
            	$onclick = str_replace('"', "'", get_attr($options[0][$j], 'onclick'));
            	$value = get_attr($options[0][$j], 'value');
            	$selected = '';
            	if (strtolower(get_attr($options[0][$j], 'selected')) == 'selected') {
	               $selected = " class=\"selected\"";
	               $selected_text = $options[1][$j];
            	}
               $new_select .= "<a$selected href=\"#\" onclick=\"$onclick\" rel=\"$value\">{$options[1][$j]}</a>";
            }
            $title = get_attr($matches[0][$i], 'title');
            if ($title != '') {
            	$selected_text = $title;
            }
            $new_select = "<span id=\"$id\" $disabled onclick=\"$selected_onclick\" class=\"$classes\"><u class=\"\"><i>$new_select</i></u><b><b><b class=\"hasmenu\">$selected_text</b></b></b></span>";
	         $html = str_replace($matches[0][$i], $new_select, $html);
         }
      }
      
     	return $html;
   }
}

?>