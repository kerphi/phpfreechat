<?php

include_once 'functions.inc.php';

// extract the full route uri
$server_dir = basename(__DIR__);
if (!preg_match("#$server_dir/(.*)$#", $_SERVER['REQUEST_URI'], $matches)) {
  header("HTTP/1.1 404 Unable to locate the server URI");
  die();
}
$uri = parse_url($matches[1]);

// extract the route name
$route_fragments = explode('/', $uri['path']);
$route = $route_fragments[0];

// load the route
$route_file = __DIR__ . '/routes/' . $route . '.php';
if (!file_exists($route_file)) {
  header("HTTP/1.1 404 Unable to locate the route");
  die();
}
include_once $route_file;

// build the request object
$req = array(
  'url' => $route_fragments,
  'body' => '',
  'params' => $_REQUEST,
  'headers' => getallheaders(),
);

// execute the route
$route_class = "Route_$route";
$r = new $route_class();
$method = strtolower($_SERVER['REQUEST_METHOD']);
$r->$method($req);