<?php
/**
 * whois.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_whois extends pfcCommand
{
  var $usage = "/whois nickname";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    //    $xml_reponse->script("trace('".implode(',',$params)."');");
    //    return;
    
    // get the nickid from the parameters
    // if the run command is whois2 then the parameter is a nickid
    $nickid = '';
    if ($this->name == 'whois2')
      $nickid = $params[0];
    else
      $nickid = $ct->getNickId($params[0]);
    
    if ($nickid)
    {
      $usermeta = $ct->getAllUserMeta($nickid);
      $usermeta['nickid'] = $nickid;
      unset($usermeta['cmdtoplay']); // used internaly

      // remove private usermeta from the list if the client is not admin
      $isadmin = $ct->getUserMeta($u->nickid, 'isadmin');
      if (!$isadmin)
        foreach($c->nickmeta_private as $nmp)
          unset($usermeta[$nmp]);

      // sort the list
      $nickmeta_sorted = array();
      $order = array_merge(array_diff(array_keys($usermeta),array_keys($c->nickmeta)),array_keys($c->nickmeta));
      foreach($order as $o) $nickmeta_sorted[$o] = $usermeta[$o];
      $usermeta = $nickmeta_sorted;
      
      require_once dirname(__FILE__).'/../pfcjson.class.php';
      $json = new pfcJSON();
      $js = $json->encode($usermeta);
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', ".$js.");");
    }
    else
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ko','".$param."');");
  }
}

?>