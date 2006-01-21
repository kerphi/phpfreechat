<?php

function cleanPath($path) {
   $result = array();
   // $pathA = preg_split('/[\/\\\]/', $path);
   $pathA = explode('/', $path);
   if (!$pathA[0])
       $result[] = '';
   foreach ($pathA AS $key => $dir) {
       if ($dir == '..') {
           if (end($result) == '..') {
               $result[] = '..';
           } elseif (!array_pop($result)) {
               $result[] = '..';
           }
       } elseif ($dir && $dir != '.') {
           $result[] = $dir;
       }
   }
   if (!end($pathA))
       $result[] = '';
   return implode('/', $result);
}


?>