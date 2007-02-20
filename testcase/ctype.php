<?php

//
// TO RUN THIS TESTCASE : PREFIX THE FUNCTIONS NAMES BY my_ IN lib/ctype/ctype.php
//

require_once dirname(__FILE__).'/../lib/ctype/ctype.php';

// examples for verifying
$test[] = "";
$test[] = "\t";
$test[] = "\r";
$test[] = "\n";
$test[] = "                  ";
$test[] = "\n-";
$test[] = " x";
$test[] = "12 3";
$test[] = ".  abc";
$test[] = "abc";
$test[] = "AzEdFDe";
$test[] = "ABC9";
$test[] = "aBcF4";
$test[] = "0123456789ABCDEFabcdef";
$test[] = "034DEFa5612789ABCbcdef";
$test[] = "012djgfbbku3456789ABCDEFabcdef";


echo "ctype_space()"."<br />";
foreach ($test as $a)
{
    echo $a .  " : " . ((my_ctype_space($a)) ? "true" : "false") ." : " . ((ctype_space($a)) ? "true" : "false") ."<br />";
}


echo "ctype_xdigit()"."<br />";
foreach ($test as $a)
{
    echo $a .  " : " . ((my_ctype_xdigit($a)) ? "true" : "false").  " : " . ((ctype_xdigit($a)) ? "true" : "false") ."<br />";
}

echo "ctype_alpha()"."<br />";
foreach ($test as $a)
{
    echo $a .  " : " . ((my_ctype_alpha($a)) ? "true" : "false") ." : " . ((ctype_alpha($a)) ? "true" : "false") ."<br />";
}

?>