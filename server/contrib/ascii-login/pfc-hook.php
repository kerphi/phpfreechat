<?php
/**
 * phpfreechat hook used to filter login which are note ascii
 */

 $GLOBALS['pfc_hooks']['pfc.filter.login'][5] = function ($app, $req, $res) {
  return function ($login) use ($app, $req, $res) {
    $ascii_pattern = '/[^a-z0-9()\/\'"|&,. -]/i';
    return preg_replace($ascii_pattern, '', $login);
  };
};
