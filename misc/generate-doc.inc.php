<?php

/**
 * This script is used to parse the parameter descriptions in pfcglobalconfig.class.php
 * in order to keep the official documentation up to date.
 */
function pfc_generate_doc($f = NULL)
{
  $f = ($f != NULL) ? $f : dirname(__FILE__).'/../src/pfcglobalconfig.class.php';

  $ct_params = array();
//   $ct_params['http'] = array( 'proxy' => 'tcp://proxyout.inist.fr:8080', 'request_fulluri' => true );
   $ct = stream_context_create($ct_params);
  $data = file_get_contents($f, false, $ct);

  $offset = 0;
  if (preg_match('/class pfcGlobalConfig/',$data,$matches, PREG_OFFSET_CAPTURE, $offset))
  {
    $offset_start = $matches[0][1];
  }
  if (preg_match('/function pfcGlobalConfig/', $data, $matches, PREG_OFFSET_CAPTURE, $offset))
  {
    $offset_end = $matches[0][1]; 
  }

  $offset = $offset_start;
  $plist = array();
  $continue = true;
  while ($offset < $offset_end)
  {
    $p = array();
  
    // search for the begining of the description
    if (preg_match('/\/\*\*/', $data, $matches1, PREG_OFFSET_CAPTURE, $offset))
      $offset1 = $matches1[0][1];
    else
      $offset = $offset_end;
  
    // search for the end of the description
    if ($offset1 < $offset_end &&
        preg_match('/\*\//', $data, $matches3, PREG_OFFSET_CAPTURE, $offset))
    {
      $offset3 = $matches3[0][1];

      // search for the parameter description
      $offset2 = $offset;
      $p['desc'] = '';
      while($offset2 < $offset3)
      {
        if (preg_match('/\s+\*\s+(.*)/', $data, $matches2, PREG_OFFSET_CAPTURE, $offset))
        {
          $offset2 = $matches2[1][1];
          if ($offset2 < $offset3)
          {
            $offset = $offset2;
            $p['desc'] .= ' '.$matches2[1][0];
          }
        }
        else
          break;
      }
      $p['desc'] = trim($p['desc']);
    
      // search for the parameter name/default value
      if (preg_match('/var\s+\$([a-z_]+)\s+=\s+(.*);/is', $data, $matches4, PREG_OFFSET_CAPTURE, $offset))
      {
        $offset = $matches4[1][1];
        $p['name']  = $matches4[1][0];
        $p['value'] = $matches4[2][0];
      }
      else
        $offset = $offset_end;
    }
    else
      $offset = $offset_end;

    if (count($p) > 0) $plist[] = $p;
  }
  return $plist;
}

?>
