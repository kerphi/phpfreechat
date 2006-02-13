<?php
/**
 * CSSTidy - CSS Parser and Optimiser
 *
 * CSS Parser class
 * This class represents a CSS parser which reads CSS code and saves it in an array.
 * In opposite to most other CSS parsers, it does not use regular expressions and
 * thus has full CSS2 support and a higher reliability.
 * Additional to that it applies some optimisations and fixes to the CSS code.
 * An online version should be available here: http://cdburnerxp.se/cssparse/css_optimiser.php
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
 * Various CSS data needed for correct optimisations etc.
 *
 * @version 1.2
 */
require('data.inc.php');

/**
 * All functions which are not directly related to the parser class
 *
 * Not required. If this file is not included, csstidy does without these functions.
 * @version 1.2
 */
@include('functions.inc.php');

/**
 * CSS Parser class
 *
 * This class represents a CSS parser which reads CSS code and saves it in an array.
 * In opposite to most other CSS parsers, it does not use regular expressions and
 * thus has full CSS2 support and a higher reliability.
 * Additional to that it applies some optimisations and fixes to the CSS code.
 * An online version should be available here: http://cdburnerxp.se/cssparse/css_optimiser.php
 * @package csstidy
 * @author Florian Schmitz (floele at gmail dot com) 2005
 * @version 1.1
 */
class csstidy {

/**
 * Saves the parsed CSS
 * @var array
 * @access public
 */
var $css = array();

/**
 * Saves the CSS charset (@charset)
 * @var string
 * @access private
 */
var $charset = '';

/**
 * Saves all @import URLs
 * @var array
 * @access private
 */
var $import = array();

/**
 * Saves the namespace
 * @var string
 * @access private
 */
var $namespace = '';

/**
 * Saves the input CSS string
 * @var string
 * @access private
 */
var $input_css = '';

/**
 * Saves the formatted CSS string
 * @var string
 * @access public
 */
var $output_css = '';

/**
 * Saves the formatted CSS string (plain text)
 * @var string
 * @access public
 */
var $output_css_plain = '';

/**
 * Saves the templates
 * @var array
 * @access private
 * @see http://cdburnerxp.se/cssparse/template.htm
 */
var $template = array();

/**
 * Contains the version of csstidy
 * @var string
 * @access private
 */
var $version = '1.1';

/**
 * Stores comments
 * @var array
 * @access private
 */
var $comments = array();

/**
 * Stores the settings
 * @var array
 * @access private
 */
var $settings = array();

/**
 * Saves the parser-status.
 *
 * Possible values:
 * - is = in selector
 * - ip = in property
 * - iv = in value
 * - instr = in string (started at " or ' or ( )
 * - ic = in comment (ignore everything)
 * - at = in @-block
 * 
 * @var string
 * @access private
 */
var $status = 'is';


/**
 * Saves the current at rule (@media)
 * @var string
 * @access private
 */
var $at = '';

/**
 * Saves the current selector
 * @var string
 * @access private
 */
var $selector = '';

/**
 * Saves the current property
 * @var string
 * @access private
 */
var $property = '';

/**
 * Saves the position of , in selectors
 * @var array
 * @access private
 */
var $sel_seperate = array();

/**
 * Saves the current value
 * @var string
 * @access private
 */
var $value = '';

/**
 * Saves the current sub-value
 *
 * Example for a subvalue:
 * background:url(foo.png) red no-repeat;
 * "url(foo.png)", "red", and  "no-repeat" are subvalues,
 * seperated by whitespace
 * @var string
 * @access private
 */
var $sub_value = '';

/**
 * Array which saves all subvalues for a property.
 * @var array
 * @see sub_value
 * @access private
 */
var $sub_value_arr = array();

/**
 * Saves the char which opened the last string
 * @var string
 * @access private
 */
var $str_char = '';

/**
 * Status where the string has been started (is or iv)
 * @var string
 * @access private
 */
var $str_from = '';

/**
 * Variable needed to manage string-in-strings, for example url("foo.png")
 * @var string
 * @access private
 */
var $str_in_str = false;

/**
 * =true if in invalid at-rule
 * @var bool
 * @access private
 */
var $invalid_at = false;

/**
 * =true if something has been added to the current selector
 * @var bool
 * @access private
 */
var $added = false;

/**
 * Status where the comment has been started
 * @var string
 * @access private
 */
var $comment_from = '';

/**
 * Array which saves the message log
 * @var array
 * @access private
 */
var $log = array();

/**
 * Saves the line number
 * @var integer
 * @access private
 */
var $line = 1;

/**
 * Loads standard template and sets default settings
 * @access private
 * @version 1.1
 */
function csstidy()
{	
	$this->settings['remove_bslash'] = true;
	$this->settings['compress_colors'] = true;
	$this->settings['compress_font-weight'] = true;
	$this->settings['lowercase_s'] = false;
	$this->settings['save_ie_hacks'] = false;
	$this->settings['optimise_shorthands'] = true;
	$this->settings['only_safe_optimisations'] = true;
	$this->settings['remove_last_;'] = false;
	$this->settings['uppercase_properties'] = false;
	$this->settings['sort_properties'] = false;
	$this->settings['sort_selectors'] = false;
	$this->settings['merge_selectors'] = 2;
	$this->settings['discard_invalid_properties'] = false;
	$this->settings['save_comments'] = false;
	$this->settings['css_level'] = 'CSS2.1';

	$this->load_template('default');
}

/**
 * Get the value of a setting.
 * @param string $setting 
 * @access public
 * @return mixed
 * @version 1.0
 */
function get_cfg($setting)
{
	if(isset($this->settings[$setting]))
	{
		return $this->settings[$setting];
	}
	return false;
}

/**
 * Set the value of a setting.
 * @param string $setting
 * @param mixed $value
 * @access public
 * @return bool
 * @version 1.0
 */
function set_cfg($setting,$value)
{
	if(isset($this->settings[$setting]) && $value !== '')
	{
		$this->settings[$setting] = $value;
		return true;
	}
	return false;
}

/**
 * Add a message to the message log
 * @param string $message
 * @param string $type
 * @param integer $line
 * @access private
 * @version 1.0
 */
function log($message,$type,$line = -1)
{
	if($line === -1)
	{
		$line = $this->line;
	}
	$line = intval($line);
	$add = array('m' => $message, 't' => $type);
	if(!isset($this->log[$line]) || !in_array($add,$this->log[$line]))
	{
		$this->log[$line][] = $add;
	}
}

/**
 * Parse unicode notations and find a replacement character
 * @param string $string
 * @param integer $i
 * @access private
 * @return string
 * @version 1.2
 */
function unicode(&$string,&$i)
{
	++$i;
	$add = '';
	$tokens =& $GLOBALS['csstidy']['tokens'];
	$replaced = false;
	
	while($i < strlen($string) && (ctype_xdigit($string{$i}) || ctype_space($string{$i})) && strlen($add) < 6)
	{
		$add .= $string{$i};

		if(ctype_space($string{$i}))
		{
			break;
		}
		$i++;
	}

	if(hexdec($add) > 47 && hexdec($add) < 58 || hexdec($add) > 64 && hexdec($add) < 91 || hexdec($add) > 96 && hexdec($add) < 123)
	{
		$this->log('Replaced unicode notation: Changed \\'.$add.' to '.chr(hexdec($add)),'Information');
		$add = chr(hexdec($add));
		$replaced = true;
	}
	else
	{
		$add = trim('\\'.$add);
	}

	if(@ctype_xdigit($string{$i+1}) && ctype_space($string{$i}) && !$replaced || !ctype_space($string{$i}))
	{
		$i--;
	}
	
	if($add != '\\' || !$this->get_cfg('remove_bslash') || strpos($tokens, $string{$i+1}) !== false)
	{
		return $add;
	}
	
	if($add == '\\')
	{
		$this->log('Removed unnecessary backslash','Information');
	}
	return '';
}

/**
 * Compresses shorthand values. Example: margin:1px 1px 1px 1px -> margin:1px 
 * @param string $value
 * @access private
 * @return string
 * @version 1.0
 */
function shorthand($value)
{
	$important = '';
	if(csstidy::is_important($value))
	{
		$values = csstidy::gvw_important($value);
		$important = ' !important';
	}
	else $values = $value;
	
	$values = explode(' ',$values);
	switch(count($values))
	{
		case 4:
		if($values[0] == $values[1] && $values[0] == $values[2] && $values[0] == $values[3])
		{
			return $values[0].$important;
		}
		elseif($values[1] == $values[3] && $values[0] == $values[2])
		{
			return $values[0].' '.$values[1].$important;
		}
		elseif($values[1] == $values[3])
		{
			return $values[0].' '.$values[1].' '.$values[2].$important;
		}
		break;
		
		case 3:
		if($values[0] == $values[1] && $values[0] == $values[2])
		{
			return $values[0].$important;
		}
		elseif($values[0] == $values[2])
		{
			return $values[0].' '.$values[1].$important;
		}
		break;
		
		case 2:
		if($values[0] == $values[1])
		{
			return $values[0].$important;
		}
		break;
	}
	
	return $value;
}

/**
 * Get compression ratio and prints the code if necessary.
 * @access public
 * @return float
 * @version 1.1
 */
function get_ratio()
{
	if(empty($this->output_css_plain))
	{
		$this->print_code($this->css);
	}
	return $ratio = round(((strlen($this->input_css))-(strlen($this->output_css_plain)))/(strlen($this->input_css)),3)*100;
}

/**
 * Get difference between the old and new code in bytes and prints the code if necessary.
 * @access public
 * @return string
 * @version 1.1
 */
function get_diff()
{
	if(empty($this->output_css_plain))
	{
		$this->print_code($this->css);
	}
	$diff = (strlen($this->output_css_plain))-(strlen($this->input_css));
	
	if($diff > 0)
	{
		return '+'.$diff;
	}
	elseif($diff == 0)
	{
		return '+-'.$diff;
	}
	else
	{
		return $diff;
	}
}

/**
 * Get the size of either input or output CSS in KB 
 * @param string $loc default is "output"
 * @access public
 * @return integer
 * @version 1.0
 */
function size($loc = 'output')
{
	if($loc == 'output' && empty($this->output_css))
	{
		$this->print_code($this->css);
	}
	
	if($loc == 'input')
	{
		return (strlen($this->input_css)/1000);
	}
	else
	{
		return (strlen(html_entity_decode(strip_tags($this->output_css)))/1000);
	}
}

/**
 * Loads a new template 
 * @param string $content either filename (if $from_file == true), content of a template file, "high_compression", "highest_compression", "low_compression", or "default"
 * @param bool $from_file uses $content as filename if true
 * @access public
 * @version 1.1
 * @see http://csstidy.sourceforge.net/templates.php
 */
function load_template($content,$from_file=true)
{
	$predefined_templates =& $GLOBALS['csstidy']['predefined_templates'];
	if($content == 'high_compression' || $content == 'default' || $content == 'highest_compression' || $content == 'low_compression')
	{
		$this->template = $predefined_templates[$content];
		return;
	}
	
	if($from_file)
	{
		$content = strip_tags(file_get_contents($content),'<span>');
	}
	$content = str_replace("\r\n","\n",$content); // Unify newlines (because the output also only uses \n)
	$template = explode('|',$content);

	for ($i = 0; $i < count($template); $i++ )
	{
		$this->template[$i] = $template[$i];
	}
}

/**
 * Starts parsing from URL 
 * @param string $url
 * @access public
 * @version 1.0
 */
function parse_from_url($url)
{
	$content = @file_get_contents($url);
	return $this->parse($content);
}

/**
 * Checks if there is a token at the current position
 * @param string $string
 * @param integer $i
 * @access public
 * @version 1.1
 */
function is_token(&$string,$i)
{
	$tokens =& $GLOBALS['csstidy']['tokens'];
	return (strpos($tokens, $string{$i}) !== false && !csstidy::escaped($string,$i));
}


/**
 * Parses CSS in $string. The code is saved as array in $this->css 
 * @param string $string the CSS code
 * @access public
 * @return bool
 * @version 1.1
 */
function parse($string) {

$shorthands =& $GLOBALS['csstidy']['shorthands'];
$all_properties =& $GLOBALS['csstidy']['all_properties'];
$at_rules =& $GLOBALS['csstidy']['at_rules'];

$this->css = array();
$this->input_css = $string;
$string = str_replace("\r\n","\n",$string) . ' ';
$cur_comment = '';

for ($i = 0, $size = strlen($string); $i < $size; $i++ )
{
	if($string{$i} == "\n" || $string{$i} == "\r")
	{
		++$this->line;
	}
	
	switch($this->status)
	{
		/* Case in at-block */
		case 'at':
		if(csstidy::is_token($string,$i))
		{
			if($string{$i} == '/' && @$string{$i+1} == '*')
			{
				$this->status = 'ic'; ++$i;
				$this->comment_from = 'at';
			}
			elseif($string{$i} == '{')
			{
				$this->status = 'is';
			}
			elseif($string{$i} == ',')
			{
				$this->at = trim($this->at).',';
			}
			elseif($string{$i} == '\\')
			{
				$this->at .= $this->unicode($string,$i);
			}
		}
		else
		{
			$lastpos = strlen($this->at)-1;
			if(!( (ctype_space($this->at{$lastpos}) || csstidy::is_token($this->at,$lastpos) && $this->at{$lastpos} == ',') && ctype_space($string{$i})))
			{
				$this->at .= $string{$i};
			}
		}
		break;
		
		/* Case in-selector */
		case 'is':
		if(csstidy::is_token($string,$i))
		{
			if($string{$i} == '/' && @$string{$i+1} == '*' && trim($this->selector) == '')
			{
				$this->status = 'ic'; ++$i;
				$this->comment_from = 'is';
			}
			elseif($string{$i} == '@' && trim($this->selector) == '')
			{
				// Check for at-rule
				$this->invalid_at = true;
				foreach($at_rules as $name => $type)
				{
					if(!strcasecmp(substr($string,$i+1,strlen($name)),$name))
					{
						($type == 'at') ? $this->at = '@'.$name : $this->selector = '@'.$name;
						$this->status = $type;
						$i += strlen($name);
						$this->invalid_at = false;
					}
				}
						
				if($this->invalid_at)
				{
					$this->selector = '@'.$this->selector;
					$invalid_at_name = '';
					for($j = $i+1; $j < $size; ++$j)
					{
						if(!ctype_alpha($string{$j}))
						{
							break;
						}
						$invalid_at_name .= $string{$j};
					}
					$this->log('Invalid @-rule: '.$invalid_at_name.' (removed)','Warning');
				}
			}
			elseif(($string{$i} == '"' || $string{$i} == "'"))
			{
				$this->selector .= $string{$i};
				$this->status = 'instr';
				$this->str_char = $string{$i};
				$this->str_from = 'is';
			}
			elseif($this->invalid_at && $string{$i} == ';')
			{
				$this->invalid_at = false;
				$this->status = 'is';
			}
			elseif($string{$i} == '{')
			{
				$this->status = 'ip';
				$this->added = false;
			}
			elseif($string{$i} == '}')
			{
				$this->at = '';
				$this->selector = '';
				$this->sel_seperate = array();
			}
			elseif($string{$i} == ',') 
			{
				$this->selector = trim($this->selector).',';
				$this->sel_seperate[] = strlen($this->selector);
			}
			elseif($string{$i} == '\\')
			{
				$this->selector .= $this->unicode($string,$i);
			}
			else $this->selector .= $string{$i};
		}
		else
		{
			$lastpos = strlen($this->selector)-1;
			if($lastpos == -1 || !( (ctype_space($this->selector{$lastpos}) || csstidy::is_token($this->selector,$lastpos) && $this->selector{$lastpos} == ',') && ctype_space($string{$i})))
			{
				$this->selector .= $string{$i};
			}
		}
		break;
		
		/* Case in-property */
		case 'ip':
		if(csstidy::is_token($string,$i))
		{
			if(($string{$i} == ':' || $string{$i} == '=') && $this->property != '')
			{
				$this->status = 'iv';
			}
			elseif($string{$i} == '/' && @$string{$i+1} == '*' && $this->property == '')
			{
				$this->status = 'ic'; ++$i;
				$this->comment_from = 'ip';
			}
			elseif($string{$i} == '}')
			{
                $this->explode_selectors();
				$this->status = 'is';
				$this->invalid_at = false;
				if($this->selector{0} != '@' && !$this->added)
				{
					$this->log('Removed empty selector: '.trim($this->selector),'Information');
				}
				$this->selector = '';
				$this->property = '';
			}
			elseif($string{$i} == ';')
			{
				$this->property = '';
			}
			elseif($string{$i} == '\\')
			{
				$this->property .= $this->unicode($string,$i);
			}
		}
		elseif(!ctype_space($string{$i}))
		{
			$this->property .= $string{$i};
		}
		break;
		
		/* Case in-value */
		case 'iv':
		$pn = (ctype_space($string{$i}) && $this->property_is_next($string,$i+1) || $i == strlen($string)-1);
		if(csstidy::is_token($string,$i) || $pn)
		{
			if($string{$i} == '/' && @$string{$i+1} == '*')
			{
				$this->status = 'ic'; ++$i;
				$this->comment_from = 'iv';
			}
			elseif(($string{$i} == '"' || $string{$i} == "'" || $string{$i} == '('))
			{
				$this->sub_value .= $string{$i};
				$this->str_char = ($string{$i} == '(') ? ')' : $string{$i};
				$this->status = 'instr';
				$this->str_from = 'iv';
			}
			elseif($string{$i} == ',')
			{
				$this->sub_value = trim($this->sub_value).',';
			}
			elseif($string{$i} == '\\')
			{
				$this->sub_value .= $this->unicode($string,$i);
			}
			elseif($string{$i} == ';' || $pn)
			{
				if($this->selector{0} == '@' && isset($at_rules[substr($this->selector,1)]) && $at_rules[substr($this->selector,1)] == 'iv')
				{
					$this->sub_value_arr[] = trim($this->sub_value);
					
					$this->status = 'is';
					
					switch($this->selector)
					{
						case '@charset': $this->charset = $this->sub_value_arr[0]; break;
						case '@namespace': $this->namespace = implode(' ',$this->sub_value_arr); break;
						case '@import': $this->import[] = implode(' ',$this->sub_value_arr); break;
					}
	
					$this->sub_value_arr = array();
					$this->sub_value = '';
					$this->selector = '';
					$this->sel_seperate = array();
				}
				else
				{
					$this->status = 'ip';
				}
			}
			elseif($string{$i} != '}')
			{
				$this->sub_value .= $string{$i};
			}
			if(($string{$i} == '}' || $string{$i} == ';' || $pn) && !empty($this->selector))
			{
				if($this->at == '')
				{
					$this->at = 'standard';
				}
				
				// case settings
				if($this->get_cfg('lowercase_s'))
				{
					$this->selector = strtolower($this->selector);
				}
				$this->property = strtolower($this->property);
				
				$this->optimise_add_subvalue();
					
				$this->value = implode(' ',$this->sub_value_arr);
			
				$this->selector = trim($this->selector);
				
				// Remove whitespace at ! important
				if($this->value != $this->c_important($this->value))
				{
					$this->log('Optimised !important','Information');
				}
				
				// optimise shorthand properties
				if(isset($shorthands[$this->property]))
				{
					$temp = csstidy::shorthand($this->value);
					if($temp != $this->value)
					{
						$this->log('Optimised shorthand notation ('.$this->property.'): Changed "'.$this->value.'" to "'.$temp.'"','Information');
					}
					$this->value = $temp;
				}
				
				// Compress font-weight
				if($this->property == 'font-weight' && $this->get_cfg('compress_font-weight'))
				{
					$this->c_font_weight($this->value);
				}

				$valid = (isset($all_properties[$this->property]) && strpos($all_properties[$this->property],strtoupper($this->get_cfg('css_level'))) !== false );
				if(!$this->invalid_at && trim($this->value) !== '' && (!$this->get_cfg('discard_invalid_properties') || $valid))
				{
					$this->css_add_property($this->at,$this->selector,$this->property,$this->value);
				
					// Further Optimisation
					if($this->property === 'background' && $this->get_cfg('optimise_shorthands') && function_exists('dissolve_short_bg') && !$this->get_cfg('only_safe_optimisations'))
					{
						unset($this->css[$this->at][$this->selector]['background']);
						$this->merge_css_blocks($this->at,$this->selector,dissolve_short_bg($this->value));
					}
					if(isset($shorthands[$this->property]) && $this->get_cfg('optimise_shorthands') && function_exists('dissolve_4value_shorthands'))
					{
						$this->merge_css_blocks($this->at,$this->selector,dissolve_4value_shorthands($this->property,$this->value));
						if(is_array($shorthands[$this->property]))
						{
							unset($this->css[$this->at][$this->selector][$this->property]);
						}
					}
				}
				if(!$valid)
				{
					if($this->get_cfg('discard_invalid_properties'))
					{
						$this->log('Removed invalid property: '.$this->property,'Warning');
					}
					else
					{
						$this->log('Invalid property in '.strtoupper($this->get_cfg('css_level')).': '.$this->property,'Warning');
					}
				}
									
				$this->property = '';
				$this->sub_value_arr = array();
				$this->value = '';
				if($this->get_cfg('save_comments') && $cur_comment != '')
				{
					$this->comments[$this->at.$this->selector] = rtrim($cur_comment,"\n\r");
					$cur_comment = '';
				}
			}
			if($string{$i} == '}')
			{
                $this->explode_selectors();
				$this->status = 'is';
				if($this->selector{0} != '@' && !$this->added)
				{
					$this->log('Removed empty selector: '.trim($this->selector),'Information');
				}
				$this->invalid_at = false;
				$this->selector = '';
			}	
		}
		elseif(!$pn)
		{
			$this->sub_value .= $string{$i};

			if(ctype_space($string{$i}))
			{
				$this->optimise_add_subvalue();
			}
		}
		break;
		
		/* Case in string */
		case 'instr':
		if($this->str_char == ')' && $string{$i} == '"' && !$this->str_in_str && !csstidy::escaped($string,$i))
		{
			$this->str_in_str = true;
		}
		elseif($this->str_char == ')' && $string{$i} == '"' && $this->str_in_str && !csstidy::escaped($string,$i))
		{
			$this->str_in_str = false;
		}
		if($string{$i} == $this->str_char && !csstidy::escaped($string,$i) && !$this->str_in_str)
		{
			$this->status = $this->str_from;
		}
		$temp_add = $string{$i};
															// ...and no not-escaped backslash at the previous position
		if( ($string{$i} == "\n" || $string{$i} == "\r") && !($string{$i-1} == '\\' && !csstidy::escaped($string,$i-1)) )
		{
			$temp_add = "\\A ";
			$this->log('Fixed incorrect newline in string','Warning');
		}
		if($this->str_from == 'iv')
		{
			$this->sub_value .= $temp_add;
		}
		elseif($this->str_from == 'is')
		{
			$this->selector .= $temp_add;
		}
		break;
		
		/* Case in-comment */
		case 'ic':
		if($string{$i} == '*' && $string{$i+1} == '/')
		{
			$this->status = $this->comment_from;
			if($this->comment_from == 'is') $cur_comment .= $this->template[15];
			$i++;
		}
		elseif($this->comment_from == 'is' && $this->get_cfg('save_comments'))
		{
			$cur_comment .= $string{$i};
		}
		break;
	}
}
  
if($this->get_cfg('merge_selectors') == 2)
{
	foreach($this->css as $medium => $value)
	{
		csstidy::merge_selectors($this->css[$medium]);
	}
}

if($this->get_cfg('optimise_shorthands'))
{
	foreach($this->css as $medium => $value)
	{
		foreach($value as $selector => $value1)
		{
			if(function_exists('merge_4value_shorthands')) $this->css[$medium][$selector] = merge_4value_shorthands($this->css[$medium][$selector]);
			if(function_exists('merge_bg') && !$this->get_cfg('only_safe_optimisations')) $this->css[$medium][$selector] = merge_bg($this->css[$medium][$selector]);
			if(empty($this->css[$medium][$selector]))
			{
				unset($this->css[$medium][$selector]);
			}
		}
	}
}

return (empty($this->css) && empty($this->import) && empty($this->charset) && empty($this->namespace)) ? false : true;
}

/**
 * Explodes selectors
 * @access private
 * @version 1.0
 */
function explode_selectors()
{
    // Explode multiple selectors
    if($this->get_cfg('merge_selectors') == 1)
    {
        $new_sels = array();
        $lastpos = 0;
        $this->sel_seperate[] = strlen($this->selector);
        foreach($this->sel_seperate as $num => $pos)
        {
            if($num == count($this->sel_seperate)-1) {
                $pos += 1;
            }
            
            $new_sels[] = substr($this->selector,$lastpos,$pos-$lastpos-1);
            $lastpos = $pos;
        }
 
        if(count($new_sels) > 1)
        {
            foreach($new_sels as $selector)
            {
                $this->merge_css_blocks($this->at,$selector,$this->css[$this->at][$this->selector]);
            }
            unset($this->css[$this->at][$this->selector]);
        }
    }
    $this->sel_seperate = array();
}

/**
 * Optimises a sub-value and adds it to sub_value_arr
 * @access private
 * @version 1.0
 */
function optimise_add_subvalue()
{
	$replace_colors =& $GLOBALS['csstidy']['replace_colors'];
	$this->sub_value = trim($this->sub_value);
	if($this->sub_value != '')
	{
		if(function_exists('compress_numbers'))
		{
			$temp = compress_numbers($this->sub_value,$this->property);
			if($temp != $this->sub_value)
			{
				if(strlen($temp) > strlen($this->sub_value))
				{
					$this->log('Fixed invalid number: Changed "'.$this->sub_value.'" to "'.$temp.'"','Warning');
				}
				else
				$this->log('Optimised number: Changed "'.$this->sub_value.'" to "'.$temp.'"','Information');
				$this->sub_value = $temp;
			}
		}
		if($this->get_cfg('compress_colors') && function_exists('cut_color'))
		{
			$temp = cut_color($this->sub_value);
			if($temp != $this->sub_value)
			{
				if(isset($replace_colors[$this->sub_value]))
				{
					$this->log('Fixed invalid color name: Changed "'.$this->sub_value.'" to "'.$temp.'"','Warning');
				}
				else
				$this->log('Optimised color: Changed "'.$this->sub_value.'" to "'.$temp.'"','Information');
				$this->sub_value = $temp;
			}
		}
		$this->sub_value_arr[] = $this->sub_value;
		$this->sub_value = '';
	}
}

/**
 * Checks if a character is escaped (and returns true if it is)
 * @param string $string
 * @param integer $pos
 * @access public
 * @return bool
 * @version 1.01
 */
function escaped(&$string,$pos) 
{
	return !(@($string{$pos-1} != '\\') || csstidy::escaped($string,$pos-1));
}

/**
 * Adds a property with value to the existing CSS code
 * @param string $media
 * @param string $selector
 * @param string $property
 * @param string $new_val
 * @param bool $ie if $ie == false IE Hacks are not saved even if it is enabled in settings
 * @access private
 * @version 1.1
 */
function css_add_property($media,$selector,$property,$new_val,$ie=true)
{
	$whitespace =& $GLOBALS['csstidy']['whitespace'];
	if($this->get_cfg('save_ie_hacks') && $ie)
	{
		if(isset($this->css[$media][$selector][$property]) && csstidy::is_important($this->css[$media][$selector][$property])
		&&  substr(str_replace($whitespace,'',$selector),0,5) !== '*html')
		{
			$this->log('Converted !important-hack in selector "'.$this->selector.'"','Information');
			if(substr(str_replace($whitespace,'',$selector),0,4) == 'html')
			{
				$this->css_add_property($media,'* '.$selector,$property,$new_val.' !important',false);
			}
			else
			{
				$this->css_add_property($media,'* html '.$selector,$property,$new_val.' !important',false);
			}
		}
		else
		{
			$this->css_add_property($media,$selector,$property,$new_val,false);
		}
	}
	else
	{
        $this->added = true;
		if(isset($this->css[$media][$selector][$property]))
		{
			if((csstidy::is_important($this->css[$media][$selector][$property]) && csstidy::is_important($new_val)) || !csstidy::is_important($this->css[$media][$selector][$property]))
			{
				unset($this->css[$media][$selector][$property]);
				$this->css[$media][$selector][$property] = trim($new_val);
			}
		}
		else
		{
			$this->css[$media][$selector][$property] = trim($new_val);
		}
	}
}

/**
 * Adds CSS to an existing media/selector
 * @param string $media
 * @param string $selector
 * @param array $css_add
 * @access private
 * @version 1.1
 */
function merge_css_blocks($media,$selector,$css_add)
{
	foreach($css_add as $property => $value)
	{
		$this->css_add_property($media,$selector,$property,$value,false);
	}
}

/**
 * Merges selectors with same properties. Example: a{color:red} b{color:red} -> a,b{color:red}
 * Very basic and has at least one bug. Hopefully there is a replacement soon.
 * @param array $array
 * @return array
 * @access public
 * @version 1.2
 */
function merge_selectors(&$array)
{
    $css = $array;
	foreach($css as $key => $value)
	{
		if(!isset($css[$key]))
		{
			continue;
		}
		$newsel = '';
		
		// Check if properties also exist in another selector
		$keys = array();
		// PHP bug (?) without $css = $array; here
		foreach($css as $selector => $vali)
		{
			if($selector == $key)
			{
				continue;
			}
			
			if($css[$key] === $vali)
			{
				$keys[] = $selector;
			}
		}

		if(!empty($keys))
		{
			$newsel = $key;
			unset($css[$key]);
			foreach($keys as $selector)
			{
				unset($css[$selector]);
				$newsel .= ','.$selector;
			}
			$css[$newsel] = $value;
		}
	}
	$array = $css;
}


/**
 * Checks if $value is !important.
 * @param string $value
 * @return bool
 * @access public
 * @version 1.0
 */
function is_important(&$value)
{
	$whitespace =& $GLOBALS['csstidy']['whitespace'];
	if(!strcasecmp(substr(str_replace($whitespace,'',$value),-10,10),'!important'))
	{
		return true;
	}
	return false;
}

/**
 * Returns a value without !important
 * @param string $value
 * @return string
 * @access public
 * @version 1.0
 */
function gvw_important($value)
{
	if(csstidy::is_important($value))
	{
		$value = trim($value);
		$value = substr($value,0,-9);
		$value = trim($value);
		$value = substr($value,0,-1);
		$value = trim($value);
		return $value;
	}
	return $value;
}

/**
 * Removes unnecessary whitespace in ! important
 * @param string $string
 * @return string
 * @access public
 * @version 1.1
 */
function c_important(&$string)
{
	if(csstidy::is_important($string))
	{
		$string = csstidy::gvw_important($string) . ' !important';
	}
	return $string;
}

/**
 * Same as htmlspecialchars, only that chars are not replaced if $plain !== true. This makes  print_code() cleaner.
 * @param string $string
 * @param bool $plain
 * @return string
 * @see csstidy::print_code()
 * @access public
 * @version 1.0
 */
function htmlsp($string, $plain)
{
	if($plain !== true)
	{
		return htmlspecialchars($string);
	}
	return $string;
}

/**
 * Returns the formatted CSS Code and saves it into $this->output_css
 * @param array $css
 * @return string
 * @access public
 * @version 1.3
 */
function print_code($css = NULL,$plain = false)
{
	$output = '';
	if($css === NULL)
	{
		$css =& $this->css;
	}
	
	$template = $this->template;

	if($plain)
	{
		$template = array_map("strip_tags", $template);
	}
	
	if(!empty($this->charset))
	{
		$output .= $template[0].'@charset '.$template[5].$this->charset.$template[6].$template[12];
	}
	
	if(!empty($this->import))
	{
		for ($i = 0, $size = count($this->import); $i < $size; $i ++) {
			$output .= $template[0].'@import '.$template[5].$this->import[$i].$template[6].$template[12];
		}
	}
	
	if(!empty($this->namespace))
	{
		$output .= $template[0].'@namespace '.$template[5].$this->namespace.$template[6].$template[12];
	}
	
	ksort($css);
	
	foreach($css as $medium => $val)
	{
		if ($medium !== 'standard')
		{
			$output .= $template[0].csstidy::htmlsp($medium,$plain).$template[1];
		}
		
		if ($this->get_cfg('sort_selectors')) ksort($val);

		foreach($val as $selector => $vali)
		{
			if ($this->get_cfg('sort_properties')) ksort($vali);
			
			if ($medium !== 'standard') $output .= $template[10];
			
			if(isset($this->comments[$medium.$selector]))
			{
				$output .= $template[13].'/*'.$this->comments[$medium.$selector].'*/'.$template[14];
			}
			$output .= ($selector{0} !== '@') ? $template[2].csstidy::htmlsp($selector,$plain) : $template[0].csstidy::htmlsp($selector,$plain);
			$output .= ($medium !== 'standard') ? $template[11] : $template[3];
			
			$i = 0; foreach($vali as $property => $valj)
			{
				$i++;
				if ($medium !== 'standard') $output .= $template[10];
				
				$output .= $template[4];
				$output .= ($this->get_cfg('uppercase_properties')) ? csstidy::htmlsp(strtoupper($property),$plain) : csstidy::htmlsp($property,$plain);
				$output .= ':'.$template[5].csstidy::htmlsp($valj,$plain);
				$output .= ($this->get_cfg('remove_last_;') && $i === count($vali)) ? str_replace(';','',$template[6]) : $template[6];
			}
			
			if ($medium !== 'standard') $output .= $template[10];
			$output .= $template[7];
			if (( end($val) !== $vali && $medium !== 'standard') || $medium === 'standard') $output .= $template[8];
		}
		if ($medium != 'standard') $output .= $template[9];
	}

	$output = trim($output);
	
	if($plain !== true)
	{
		$this->output_css = $output;
		$this->print_code($css, true);
	}
	else
	{
		$this->output_css_plain = $output;
	}
	return $output;
}

/**
 * Checks if the next word in a string from pos is a CSS property
 * @param string $istring
 * @param integer $pos
 * @return bool
 * @access private
 * @version 1.2
 */
function property_is_next($istring, $pos)
{
	$all_properties =& $GLOBALS['csstidy']['all_properties'];
	$istring = substr($istring,$pos,strlen($istring)-$pos);
	$pos = strpos($istring,':');
	if($pos === false)
	{
		return false;
	}
	$istring = strtolower(trim(substr($istring,0,$pos)));
	if(isset($all_properties[$istring]))
	{
		$this->log('Added semicolon to the end of declaration','Warning');
		return true;
	}
	return false;
}

/**
 * Compresses font-weight (not very effective but anyway :-p )
 * @param string $value
 * @access private
 * @return bool
 * @version 1.1
 */
function c_font_weight(&$value)
{
	$important = '';
	if(csstidy::is_important($value))
	{
		$important = ' !important';
		$value = csstidy::gvw_important($value);
	}
	if($value == 'bold')
	{
		$value = '700'.$important;
		$this->log('Optimised font-weight: Changed "bold" to "700"','Information');
	}
	else if($value == 'normal')
	{
		$value = '400'.$important;
		$this->log('Optimised font-weight: Changed "normal" to "400"','Information');
	}
}

}
?>