<?php
/**
 * phpfreechat auth hook used for a "ticket" based authentication
 * see also auth.php
 */

$GLOBALS['pfc_hooks']['pfc.before.auth'][5] = function ($app, $req, $res) {
  return function () use ($app, $req, $res) {
    if (!$req->params('ticket')) {
      //debug('redirect to sso');
      $res->redirect(dirname($req->getPath()).'/contrib/phpbb3-auth/auth.php?service='.$req->getUrl().$req->getPath(), 302);
    } else {
      //debug('ticket received');
      $ticket = $req->params('ticket');
      $login = file_get_contents($req->getUrl().dirname($req->getPath()).'/contrib/phpbb3-auth/auth.php?cmd=servicevalidate&ticket='.$ticket);
      //debug($login);
      return $login;
    }
  };
};