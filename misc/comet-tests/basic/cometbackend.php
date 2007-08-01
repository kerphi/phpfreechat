<?php

// return a json array
$response = array();
$response['data']       = 'test';
$response['id']         = time();
echo json_encode($response);
flush();
sleep(5);

?>