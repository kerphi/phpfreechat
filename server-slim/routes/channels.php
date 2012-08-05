<?php

include_once 'container/users.php';

/**
 * Returns channel list
 */
$app->get('/channels/', function () use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns the channel list');
});

/**
 * Returns channel data or 404
 */
$app->get('/channels/:cid/', function ($cid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns channel data of '.$cid);
});

/**
 * List users on a channel
 */
$app->get('/channels/:cid/users/', function ($cid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('list users on a channel '.$cid);
});

/**
 * Join a channel
 */
$app->put('/channels/:cid/users/:uid', function ($cid, $uid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('join a channel '.$cid);
});

/**
 * Post a message on a channel
 */
$app->post('/channels/:cid/msg/', function ($cid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('post a messages on a channel '.$cid);
});