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

  function getOnlineNick()
  {
    $container =& $this->getContainerInstance();
    $users = $container->getOnlineNick();
    return $users;
  }

  function getLastMsg($nb)
  {
    $container   =& $this->getContainerInstance();
    $lastmsg_id  = $container->getLastMsgId();
    $lastmsg_raw = $container->readNewMsg($lastmsg_id-10);
    return $lastmsg_raw;
  }
}

?>
