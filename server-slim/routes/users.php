<?php

include_once 'container/users.php';

/**
 * Returns users list
 */
$app->get('/users/', function () use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns the users list');
});

/**
 * Returns users data or 404
 */
$app->get('/users/:uid/', function ($uid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns users data '.$uid);
});

/**
 * Returns user's pending messages
 */
$app->get('/users/:uid/msg/', function ($uid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns user s pending messages'.$uid);
});
