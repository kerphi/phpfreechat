<?php

require_once dirname(__FILE__).'/../src/pfccommand.class.php';

$results = array();

$results[] = array('cmdstr' => '/cmdname clientid recipientid',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid param1   param2',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1','param2'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid param1 param2 param3',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1','param2','param3'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1" "param2"',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1','param2'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1" "param2" "param3"',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1','param2','param3'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1 with spaces" "param2 with spaces"',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1 with spaces','param2 with spaces'));
$results[] = array('cmdstr' => '/cmdname000 clientid recipientid "param1" "param2"',
                   'cmdname' => 'cmdname000',
                   'params' => array('clientid', 'recipientid', 'param1','param2'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid param1  param2',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1','param2'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1 with spaces" param2 param3',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1 with spaces','param2', 'param3'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1" param2 "param3 with spaces" param4',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1', 'param2', 'param3 with spaces', 'param4'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1""param2"',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1', 'param2'));
$results[] = array('cmdstr' => '/cmdname clientid recipientid "param1withoutspace"',
                   'cmdname' => 'cmdname',
                   'params' => array('clientid', 'recipientid', 'param1withoutspace'));
$results[] = array('cmdstr' => '/send clientid recipientid my sentance " with double " quotes',
                   'cmdname' => 'send',
                   'params' => array('clientid', 'recipientid', 'my sentance " with double " quotes'));

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