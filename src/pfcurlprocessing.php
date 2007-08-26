<?php

require_once dirname(__FILE__).'/pfcglobalconfig.class.php';

/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 *   to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 *   to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *    to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */
function pfc_make_hyperlink($text)
{
  $c =& pfcGlobalConfig::Instance();
  $openlinknewwindow = $c->openlinknewwindow;

  if ($openlinknewwindow)
    $target = " onclick=\"window.open(this.href,\\'_blank\\');return false;\"";
  else
    $target = "";
  
  $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

  // pad it with a space so we can match things at the start of the 1st line.
  $ret = ' ' . $text;

  // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
  // xxxx can only be alpha characters.
  // yyyy is anything up to the first space, newline, comma, double quote or <
  //$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n \]])([\w]+?://[\w\#$%&~/.\-;:=,?@+]*)#ise", "'\\1<a href=\"\\2\"" . $target . ">' . pfc_shorten_url('\\2') . '</a>'", $ret);

  // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
  // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
  // zzzz is optional.. will contain everything up to the first space, newline, 
  // comma, double quote or <.
  //$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n \]])((www|ftp)\.[\w\#$%&~/.\-;:=,?@+]*)#ise", "'\\1<a href=\"http://\\2\"" . $target . ">' . pfc_shorten_url('\\2') . '</a>'", $ret);

  // matches an email@domain type address at the start of a line, or after a space.
  // Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
  //$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
  $ret = preg_replace("#(^|[\n \]])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#ie", "'\\1<a href=\"mailto:\\2@\\3\">' . pfc_shorten_url('\\2@\\3') . '</a>'", $ret);

  // Remove our padding..
  $ret = substr($ret, 1);

  return($ret);
}

/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
function pfc_undo_make_hyperlink($text)
{
  $text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\".*?>.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
  $text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\".*?>.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);

  return $text;

}

function pfc_shorten_url($url)
{
  $c =& pfcGlobalConfig::Instance();

  if (! $c->short_url)
    return $url;

  // Short URL Width
  $shurl_w = $c->short_url_width;

  $shurl_end_w = floor($shurl_w * .25) - 3;
  if ($shurl_end_w < 3) $shurl_end_w = 3;
  $shurl_begin_w = $shurl_w - $shurl_end_w - 3;
  if ($shurl_begin_w < 3) $shurl_begin_w = 3;
  
  $decodedurl = html_entity_decode($url, ENT_QUOTES);
  
  $len = strlen($decodedurl);
  $short_url = ($len > $shurl_w) ? substr($decodedurl, 0, $shurl_begin_w) . "..." . substr($decodedurl, -$shurl_end_w) : $decodedurl;
  
  return htmlentities($short_url, ENT_QUOTES);
}

?>
