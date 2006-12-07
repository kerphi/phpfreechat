<?php

require_once dirname(__FILE__).'/../src/pfccommand.class.php';

$results = array();
$results[] = array('cmdstr' => '/cmdname',
                   'cmdname' => 'cmdname',
                   'params' => array());
$results[] = array('cmdstr' => '/cmdname "param1" "param2"',
                   'cmdname' => 'cmdname',
                   'params' => array('param1','param2'));
$results[] = array('cmdstr' => '/cmdname "param1" "param2" "param3"',
                   'cmdname' => 'cmdname',
                   'params' => array('param1','param2','param3'));
$results[] = array('cmdstr' => '/cmdname "param1 with spaces" "param2 with spaces"',
                   'cmdname' => 'cmdname',
                   'params' => array('param1 with spaces','param2 with spaces'));
$results[] = array('cmdstr' => '/cmdname000 "param1" "param2"',
                   'cmdname' => 'cmdname000',
                   'params' => array('param1','param2'));
$results[] = array('cmdstr' => '/cmdname param1 param2',
                   'cmdname' => 'cmdname',
                   'params' => array('param1','param2'));
$results[] = array('cmdstr' => '/cmdname "param1 with spaces" param2  param3',
                   'cmdname' => 'cmdname',
                   'params' => array('param1 with spaces','param2','param3'));
$results[] = array('cmdstr' => '/cmdname "param1" param2 "param3 with spaces" param4',
                   'cmdname' => 'cmdname',
                   'params' => array('param1', 'param2', 'param3 with spaces', 'param4'));
$results[] = array('cmdstr' => '/cmdname "param1""param2"',
                   'cmdname' => 'cmdname',
                   'params' => array('param1', 'param2'));
$results[] = array('cmdstr' => '/cmdname "param1withoutspace"',
                   'cmdname' => 'cmdname',
                   'params' => array('param1withoutspace'));
echo '<pre>';
for($i = 0; $i<count($results); $i++)
{
  $command = $results[$i]['cmdstr'];
  $result = pfcCommand::ParseCommand($command);
  if ($result == $results[$i])
    echo "OK => $command\n";
  else
  {
    print_r($result);
    echo "KO => $command\n";
  }
}
echo '</pre>';

?>