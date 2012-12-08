<?php

/**
 * Route used to know if the skip intro (donation popup) has been checked
 */
$app->get('/skipintro', function () use ($app, $req, $res) {
  
  $datadir = dirname(__FILE__).'/../data';
  $si_file = $datadir.'/skipintro';
  $res->status(file_exists($si_file) ? 200 : 404);

});

/**
 * Route called when the skip intro (donation popup) has been checked
 * Thanks to this flag, it's possible to know if we can hide or show the donation popup
 */
$app->put('/skipintro', function () use ($app, $req, $res) {

  $datadir = dirname(__FILE__).'/../data';
  $si_file = $datadir.'/skipintro';
  $res->status(@touch($si_file) ? 200 : 500);
  
});

/**
 * Route used to know if rewriting rules are enable in the web server
 */
$app->get('/status', function () use ($app, $req, $res) {
  
  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('{ "running": true }');

});