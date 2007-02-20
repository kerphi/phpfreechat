<?php

if (!function_exists('ctype_alpha')) {
  function ctype_alpha($string)
  {
    return preg_match('/^[a-z]+$/i', $string);
  }
}

if (!function_exists('ctype_xdigit')) {
  function ctype_xdigit($string)
  {
    return preg_match('/^[0-9a-f]+$/i', $string);
  }
} 

if (!function_exists('ctype_space')) {
  function ctype_space($string)
  {
    return preg_match('/^[\s]$/', $string);
  }
} 

?>