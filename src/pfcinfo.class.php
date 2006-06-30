<?php

require_once dirname(__FILE__)."/pfcglobalconfig.class.php";

class pfcInfo extends pfcGlobalConfig
{
  var $container;
  
  function pfcInfo( $serverid )
  {
    $cachefile = dirname(__FILE__)."/../data/private/cache/pfcglobalconfig_".$serverid;
    $pfc_configvar = unserialize(file_get_contents($cachefile));
    foreach($pfc_configvar as $key => $val)
      $this->$key = $val;
  }

  /**
   * @return array(string) a list of online nicknames
   */
  function getOnlineNick($channel = NULL)
  {
    $container =& $this->getContainerInstance();
    $res = $container->getOnlineNick($channel);
    $users = array();
    if (isset($res["nickid"]))
      foreach($res["nickid"] as $nickid)
        $users[] = $container->getNickname($nickid);
    return $users;
  }

  function getLastMsg($channel, $nb)
  {
    $container   =& $this->getContainerInstance();
    $lastmsg_id  = $container->getLastId($channel);
    echo $lastmsg_id;
    die();
    $lastmsg_raw = $container->read($channel, $lastmsg_id-10);
    return $lastmsg_raw;
  }
}

?>
