#!/usr/bin/php
<?php

include_once __DIR__.'/../container/indexes.php';

$r1 = Container_indexes::rmIndex('users/name', 'kerphi');
$r2 = Container_indexes::getIndex('users/name', 'kerphi');
$r3 = Container_indexes::setIndex('users/name', 'kerphi', 'xxxx');
$r4 = Container_indexes::getIndex('users/name', 'kerphi');
$r5 = Container_indexes::rmIndex('users/name', 'kerphi');
$r6 = Container_indexes::getIndex('users/name', 'kerphi');
echo ($r4 === 'xxxx' ? 'pass' : 'fail').' - getIndex and setIndex on basic data'."\n";
echo ($r6 === null ? 'pass' : 'fail').' - rmIndex on basic data'."\n";


$r1 = Container_indexes::rmIndex('users/name', 'kéôçö&/li');
$r2 = Container_indexes::getIndex('users/name', 'kéôçö&/li');
$r3 = Container_indexes::setIndex('users/name', 'kéôçö&/li', 'xxxx');
$r4 = Container_indexes::getIndex('users/name', 'kéôçö&/li');
$r5 = Container_indexes::rmIndex('users/name', 'kéôçö&/li');
$r6 = Container_indexes::getIndex('users/name', 'kéôçö&/li');
echo ($r4 === 'xxxx' ? 'pass' : 'fail').' - getIndex and setIndex on data with utf8'."\n";
echo ($r6 === null ? 'pass' : 'fail').' - rmIndex on data with utf8'."\n";
