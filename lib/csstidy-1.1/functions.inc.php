<?php
/**
 * Various functions
 *
 * This file contains a few functions which are needed to optimise CSS Code. These
 * functions are not part of the main class since they are not directly related to the
 * parsing process.
 *
 * This file is part of CSSTidy.
 *
 * CSSTidy is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * CSSTidy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CSSTidy; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package csstidy
 * @author Florian Schmitz (floele at gmail dot com) 2005
 */


/**
 * Color compression function. Converts all rgb() values to #-values and uses the short-form if possible. Also replaces 4 color names by #-values.
 * @param string $color
 * @return string
 * @version 1.1
 */
function cut_color($color)
{
	$replace_colors =& $GLOBALS['csstidy']['replace_colors'];
	
	// rgb(0,0,0) -> #000000 (or #000 in this case later)
	if(strtolower(substr($color,0,4)) == 'rgb(')
	{
		$color_tmp = substr($color,4,strlen($color)-5);
		$color_tmp = explode(',',$color_tmp);
		for ( $i = 0; $i < count($color_tmp); $i++ )
		{
			$color_tmp[$i] = trim ($color_tmp[$i]);
			if(substr($color_tmp[$i],-1) == '%')
			{
				$color_tmp[$i] = round((255*$color_tmp[$i])/100);
			}
			if($color_tmp[$i]>255) $color_tmp[$i] = 255;
		}
		$color = '#';
		for ($i = 0; $i < 3; $i++ )
		{
			if($color_tmp[$i]<16) $color .= '0'.dechex($color_tmp[$i]);
				else $color .= dechex($color_tmp[$i]);
		}
	}
	
	// Fix bad color names
	if(isset($replace_colors[strtolower($color)]))
	{
		$color = $replace_colors[strtolower($color)];
	}
	
	// #aabbcc -> #abc
	if(strlen($color) == 7)
	{
		$color_temp = strtolower($color);
		if($color_temp{0} == '#' && $color_temp{1} == $color_temp{2} && $color_temp{3} == $color_temp{4} && $color_temp{5} == $color_temp{6})
		{
			$color = '#'.$color{1}.$color{3}.$color{5};
		}
	}
	
	switch(strtolower($color))
	{
		/* color name -> hex code */
		case 'black': return '#000';
		case 'fuchsia': return '#F0F';
		case 'white': return '#FFF';
		case 'yellow': return '#FF0';
				
		/* hex code -> color name */
		case '#800000': return 'maroon';
		case '#ffa500': return 'orange';
		case '#808000': return 'olive';
		case '#800080': return 'purple';
		case '#008000': return 'green';
		case '#000080': return 'navy';
		case '#008080': return 'teal';
		case '#c0c0c0': return 'silver';
		case '#808080': return 'gray';
		case '#f00': return 'red';	
	}

	return $color;
}

/**
 * Compresses numbers (ie. 1.0 becomes 1 or 1.100 becomes 1.1 )
 * @param string $subvalue
 * @param string property needed to check wheter <number>-values are allowed or not
 * @return string
 * @version 1.1
 */
function compress_numbers($subvalue, $property = NULL)
{
	$units =& $GLOBALS['csstidy']['units'];
	$number_values =& $GLOBALS['csstidy']['number_values'];
	$color_values =& $GLOBALS['csstidy']['color_values'];

	// for font:1em/1em sans-serif...;
	if($property == 'font')
	{
		$temp = explode('/',$subvalue);
	}
	else
	{
		$temp = array($subvalue);
	}
	for ($l = 0; $l < count($temp); $l++)
	{
		// continue if no numeric value
		if(!(strlen($temp[$l]) > 0 && ( is_numeric($temp[$l]{0}) || $temp[$l]{0} == "+" || $temp[$l]{0} == "-" ) ))
		{
			continue;
		}

		// Fix bad colors
		if(in_array($property,$color_values))
		{
			$temp[$l] = '#'.$temp[$l];
		}
	
		if(floatval($temp[$l]) == 0 && ( is_numeric($temp[$l]{0}) || $temp[$l]{0} == "+" || $temp[$l]{0} == "-" ) )
		{
			$temp[$l] = 0;
		}
		elseif(is_numeric($temp[$l]{0}) || $temp[$l]{0} == "+" || $temp[$l]{0} == "-")
		{
			$unit_found = FALSE;
			for( $m = 0, $size_4 = count($units); $m < $size_4; $m++ )
			{
				if(strpos(strtolower($temp[$l]),$units[$m]) !== FALSE)
				{
					$temp[$l] = floatval($temp[$l]).$units[$m];
					$unit_found = TRUE;
					break;
				}
			}
			if(!$unit_found && !in_array($property,$number_values,TRUE))
			{
				$temp[$l] = floatval($temp[$l]).'px';
			}
			else if(!$unit_found)
			{
				$temp[$l] = floatval($temp[$l]);
			}
		}
	}
	$subvalue = (count($temp) > 1) ? $temp[0].'/'.$temp[1] : $temp[0];
	return "$subvalue";
}

/**
 * Dissolves properties like padding:10px 10px 10px to padding-top:10px;padding-bottom:10px;...
 * @param string $property
 * @param string $value
 * @return array
 * @version 1.0
 * @see merge_4value_shorthands()
 */
function dissolve_4value_shorthands($property,$value)
{
	$shorthands =& $GLOBALS['csstidy']['shorthands'];
	if(!is_array($shorthands[$property]))
	{
		$return[$property] = $value;
		return $return;
	}
	
	$important = '';
	if(csstidy::is_important($value))
	{
		$value = csstidy::gvw_important($value);
		$important = ' !important';
	}
	$values = explode(' ',$value);


	$return = array();
	if(count($values) == 4)
	{
		for($i=0;$i<4;$i++)
		{
			$return[$shorthands[$property][$i]] = $values[$i].$important;
		}
	}
	elseif(count($values) == 3)
	{
		$return[$shorthands[$property][0]] = $values[0].$important;
		$return[$shorthands[$property][1]] = $values[1].$important;
		$return[$shorthands[$property][3]] = $values[1].$important;
		$return[$shorthands[$property][2]] = $values[2].$important;
	}
	elseif(count($values) == 2)
	{
		for($i=0;$i<4;$i++)
		{
			$return[$shorthands[$property][$i]] = (($i % 2 != 0)) ? $values[1].$important : $values[0].$important;
		}
	}
	else
	{
		for($i=0;$i<4;$i++)
		{
			$return[$shorthands[$property][$i]] = $values[0].$important;
		}	
	}
	
	return $return;
}

/**
 * Explodes a string as explode() does, however, not if $sep is escaped or within a string.
 * @param string $sep seperator
 * @param string $string
 * @return array
 * @version 1.0
 */
function explode_ws($sep,$string)
{
	$status = 'st';
	$to = '';
	
	$output = array();
	$num = 0;
	for($i = 0, $len = strlen($string);$i < $len; $i++)
	{
		switch($status)
		{
			case 'st':
			if($string{$i} == $sep && !csstidy::escaped($string,$i))
			{
				++$num;
			}
			elseif($string{$i} == '"' || $string{$i} == '\'' || $string{$i} == '(' && !csstidy::escaped($string,$i))
			{
				$status = 'str';
				$to = ($string{$i} == '(') ? ')' : $string{$i};
				(isset($output[$num])) ? $output[$num] .= $string{$i} : $output[$num] = $string{$i};
			}
			else
			{
				(isset($output[$num])) ? $output[$num] .= $string{$i} : $output[$num] = $string{$i};
			}
			break;
			
			case 'str':
			if($string{$i} == $to && !csstidy::escaped($string,$i))
			{
				$status = 'st';
			}
			(isset($output[$num])) ? $output[$num] .= $string{$i} : $output[$num] = $string{$i};
			break;
		}
	}
	
	if(isset($output[0]))
	{
		return $output;
	}
	else
	{
		return array($output);
	}
}

/**
 * Merges Shorthand properties again, the opposite of dissolve_4value_shorthands() 
 * @param array $array
 * @return array
 * @version 1.2
 * @see dissolve_4value_shorthands()
 */
function merge_4value_shorthands($array)
{
	$return = $array;
	$shorthands =& $GLOBALS['csstidy']['shorthands'];
	
	foreach($shorthands as $key => $value)
	{
		if(isset($array[$value[0]]) && isset($array[$value[1]])
		&& isset($array[$value[2]]) && isset($array[$value[3]]) && $value !== 0)
		{
			$return[$key] = '';
			
			$important = '';
			for($i = 0; $i < 4; $i++)
			{
				$val = $array[$value[$i]];
				if(csstidy::is_important($val))
				{
					$important = '!important';
					$return[$key] .= csstidy::gvw_important($val).' ';
				}
				else
				{
					$return[$key] .= $val.' ';
				}
				unset($return[$value[$i]]);
			}
			$return[$key] = csstidy::shorthand(trim($return[$key].$important));		
		}
	}
	return $return;
}

/**
 * Dissolve background property
 * @param string $str_value
 * @return array
 * @version 1.0
 * @see merge_bg()
 * @todo full CSS 3 compliance
 */
function dissolve_short_bg($str_value)
{
	$background_prop_default =& $GLOBALS['csstidy']['background_prop_default'];
	$repeat = array('repeat','repeat-x','repeat-y','no-repeat','space');
	$attachment = array('scroll','fixed','local');
	$clip = array('border','padding');
	$origin = array('border','padding','content');
	$pos = array('top','center','bottom','left','right');
	$important = '';
	$return = array('background-image' => NULL,'background-size' => NULL,'background-repeat' => NULL,'background-position' => NULL,'background-attachment'=>NULL,'background-clip' => NULL,'background-origin' => NULL,'background-color' => NULL);
	
	if(csstidy::is_important($str_value))
	{
		$important = ' !important';
		$str_value = csstidy::gvw_important($str_value);
	}
	
	$str_value = explode_ws(',',$str_value);
	for($i = 0; $i < count($str_value); $i++)
	{
		$have['clip'] = FALSE; $have['pos'] = FALSE;
		$have['color'] = FALSE; $have['bg'] = FALSE;
		
		$str_value[$i] = explode_ws(' ',trim($str_value[$i]));
		
		for($j = 0; $j < count($str_value[$i]); $j++)
		{
			if($have['bg'] === FALSE && (substr($str_value[$i][$j],0,4) == 'url(' || $str_value[$i][$j] === 'none'))
			{
				$return['background-image'] .= $str_value[$i][$j].',';
				$have['bg'] = TRUE;
			}
			elseif(in_array($str_value[$i][$j],$repeat,TRUE))
			{
				$return['background-repeat'] .= $str_value[$i][$j].',';
			}
			elseif(in_array($str_value[$i][$j],$attachment,TRUE))
			{
				$return['background-attachment'] .= $str_value[$i][$j].',';
			}
			elseif(in_array($str_value[$i][$j],$clip,TRUE) && !$have['clip'])
			{
				$return['background-clip'] .= $str_value[$i][$j].',';
				$have['clip'] = TRUE;
			}
			elseif(in_array($str_value[$i][$j],$origin,TRUE))
			{
				$return['background-origin'] .= $str_value[$i][$j].',';
			}
			elseif($str_value[$i][$j]{0} == '(')
			{
				$return['background-size'] .= substr($str_value[$i][$j],1,-1).',';
			}
			elseif(in_array($str_value[$i][$j],$pos,TRUE) || is_numeric($str_value[$i][$j]{0}) || $str_value[$i][$j]{0} === NULL)
			{
				$return['background-position'] .= $str_value[$i][$j];
				if(!$have['pos']) $return['background-position'] .= ' '; else $return['background-position'].= ',';
				$have['pos'] = TRUE;
			}
			elseif(!$have['color'])
			{
				$return['background-color'] .= $str_value[$i][$j].',';
				$have['color'] = TRUE;
			}
		}
	}
	
	foreach($background_prop_default as $bg_prop => $default_value)
	{
		if($return[$bg_prop] !== NULL)
		{
			$return[$bg_prop] = substr($return[$bg_prop],0,-1).$important;
		}
		else $return[$bg_prop] = $default_value.$important;
	}
	return $return;	
}

/**
 * Merges all background properties
 * @param array $input_css
 * @return array
 * @version 1.0
 * @see dissolve_short_bg()
 * @todo full CSS 3 compliance
 */
function merge_bg($input_css)
{
	$background_prop_default =& $GLOBALS['csstidy']['background_prop_default'];
	// Max number of background images. CSS3 not yet fully implemented
	$number_of_values = @max(count(explode_ws(',',$input_css['background-image'])),count(explode_ws(',',$input_css['background-color'])),1);
	// Array with background images to check if BG image exists
	$bg_img_array = @explode_ws(',',csstidy::gvw_important($input_css['background-image']));
	$new_bg_value = '';
	$important = '';
	
	for($i = 0; $i < $number_of_values; $i++)
	{
		foreach($background_prop_default as $bg_property => $default_value)
		{
			// Skip if property does not exist
			if(!isset($input_css[$bg_property]))
			{
				continue;
			}
			
			$cur_value = $input_css[$bg_property];
			
			// Skip some properties if there is no background image
			if((!isset($bg_img_array[$i]) || $bg_img_array[$i] === 'none')
				&& ($bg_property === 'background-size' || $bg_property === 'background-position'
				|| $bg_property === 'background-attachment' || $bg_property === 'background-repeat'))
			{
				continue;
			}
			
			// Remove !important
			if(csstidy::is_important($cur_value))
			{
				$important = ' !important';
				$cur_value = csstidy::gvw_important($cur_value);
			}
			
			// Do not add default values
			if($cur_value === $default_value)
			{
				continue;
			}
			
			$temp = explode_ws(',',$cur_value);

			if(isset($temp[$i]))
			{					
				if($bg_property == 'background-size')
				{
					$new_bg_value .= '('.$temp[$i].') ';
				}
				else
				{
					$new_bg_value .= $temp[$i].' ';
				}
			}			
		}
		
		$new_bg_value = trim($new_bg_value);
		if($i != $number_of_values-1) $new_bg_value .= ',';
	}
	
	// Delete all background-properties
	foreach($background_prop_default as $bg_property => $default_value)
	{
		unset($input_css[$bg_property]);
	}
	
	// Add new background property
	if($new_bg_value !== '') $input_css['background'] = $new_bg_value.$important;
	
	return $input_css;
}

?>