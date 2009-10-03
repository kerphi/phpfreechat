<?php
/**
 * src/container/oracle.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 * Modifications by Golemwashere
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

/*
Oracle specific parameters:
$params["container_type"] = "oracle";
$params["container_cfg_oracle_host"] = "localhost";
$params["container_cfg_oracle_port"] = 1521; 
$params["container_cfg_oracle_database"] = "XE"; 
$params["container_cfg_oracle_table"] = "phpfreechat"; 
$params["container_cfg_oracle_username"] = "orauser"; 
$params["container_cfg_oracle_password"] = "orapw";

*/


require_once dirname(__FILE__)."/../pfccontainer.class.php";

// include pear DB classes
require_once 'DB.php';

/**
 * pfcContainer_Oracle is a concret container which store data into Oracle database
 *
 * 
 * @author Golemwashere
 * @author Stephane Gully <stephane.gully@gmail.com>
 * @author HenkBB
 */
class pfcContainer_Oracle extends pfcContainerInterface
{
  var $_db = null;
  var $_sql_create_table = "
  CREATE TABLE phpfreechat (
  server varchar2(200) NOT NULL default '',
  groupg varchar2(200) NOT NULL default '',
  subgroup varchar2(200) NOT NULL default '',
  leaf varchar2(200) NOT NULL default '',
  leafvalue varchar2(4000) NOT NULL,
  timestampg number(20) NOT NULL default 0,
);

  PRIMARY KEY  (server,groupg,subgroup,leaf);
  INDEX (server,group,subgroupg,timestampg);
  CREATE SEQUENCE phpfreechat_leafvalue_seq
  
  ";
 
    
  function pfcContainer_Oracle()
  {
    pfcContainerInterface::pfcContainerInterface();
  }

  function getDefaultConfig()
  {   
    $cfg = pfcContainerInterface::getDefaultConfig();
    $cfg["oracle_host"] = 'localhost';
    $cfg["oracle_port"] = 1521;
    $cfg["oracle_database"] = 'XE';
    $cfg["oracle_table"]    = 'phpfreechat';
    $cfg["oracle_username"] = 'phpfreechatuser';
    $cfg["oracle_password"] = 'freechatpass';
    return $cfg;
  }

  function init(&$c)
  {
    
    $errors = pfcContainerInterface::init($c);

    // connect to the db
    $db = $this->_connect($c);
    if ($db === FALSE)
    {
      $errors[] = _pfc("DB container: connect error");
      return $errors;
    }

    // create the db if it doesn't exists
    // golemwashere: commented out this part for now, DB must be manually created
    /*
    $db_exists = false;
    $db_list = mysql_list_dbs($db);
    while (!$db_exists && $row = mysql_fetch_object($db_list))
      $db_exists = ($c->container_cfg_mysql_database == $row->Database);
    if (!$db_exists)
    {
      $query = 'CREATE DATABASE '.$c->container_cfg_mysql_database;
      $result = mysql_query($query, $db);
      if ($result === FALSE)
      {
        $errors[] = _pfc("Mysql container: create database error '%s'",mysql_error($db));
        return $errors;
      }
      mysql_select_db($c->container_cfg_mysql_database, $db);
    }
 
    // create the table if it doesn't exists
    $query = $this->_sql_create_table;
    $query = str_replace('%engine%',              $c->container_cfg_mysql_engine,$query);
    $query = str_replace('%table%',               $c->container_cfg_mysql_table,$query);
    $query = str_replace('%fieldtype_server%',    $c->container_cfg_mysql_fieldtype_server,$query);
    $query = str_replace('%fieldtype_group%',     $c->container_cfg_mysql_fieldtype_group,$query);
    $query = str_replace('%fieldtype_subgroup%',  $c->container_cfg_mysql_fieldtype_subgroup,$query);
    $query = str_replace('%fieldtype_leaf%',      $c->container_cfg_mysql_fieldtype_leaf,$query);
    $query = str_replace('%fieldtype_leafvalue%', $c->container_cfg_mysql_fieldtype_leafvalue,$query);
    $query = str_replace('%fieldtype_timestamp%', $c->container_cfg_mysql_fieldtype_timestamp,$query);    
    $result = mysql_query($query, $db);
    if ($result === FALSE)
    {
      $errors[] = _pfc("Mysql container: create table error '%s'",mysql_error($db));
      return $errors;
    }
    return $errors;
    */
    
  }

  function _connect($c = null)
  {
    if (!$this->_db)
    {
      if ($c == null) $c =& pfcGlobalConfig::Instance();
      
      $dsn = array(
    'phptype'  => 'oci8',
    'username' => $c->container_cfg_oracle_username,
    'password' => $c->container_cfg_oracle_password,
    'hostspec' => '//'.$c->container_cfg_oracle_host.':'.$c->container_cfg_oracle_port.'/'.$c->container_cfg_oracle_database
     );

$this->_db = DB::connect($dsn);
if (DB::isError($this->_db))
{
 echo 'Cannot connect to database: ' . $this->_db->getMessage();
}
      
     
      
    }

    
    
    return $this->_db;
  }

  function setMeta($group, $subgroup, $leaf, $leafvalue = NULL)
  {
    $c =& pfcGlobalConfig::Instance();      
      
    $server = $c->serverid;    
    $db = $this->_connect();

    if ($leafvalue == NULL){$leafvalue=" ";};
    # clean leafvalue:
    $leafvalue=str_replace("'", "''", $leafvalue);
    # GOLEMQUERY #1
    $sql_count = "SELECT COUNT(*) AS C FROM ".$c->container_cfg_oracle_table." WHERE server='$server' AND groupg='$group' AND subgroup='$subgroup' AND leaf='$leaf' and rownum <= 1";
    # GOLEMQUERY #2
    $sql_insert="INSERT INTO ".$c->container_cfg_oracle_table." (server, groupg, subgroup, leaf, leafvalue, timestampg) VALUES('$server', '$group', '$subgroup', '$leaf', '$leafvalue', trunc((to_number(cast((systimestamp AT TIME ZONE 'GMT') as date)-cast(TO_TIMESTAMP_TZ ('01-01-1970 00:00:00 GMT', 'DD-MM-YYYY HH24:MI:SS TZR') as date))*86400)))";
    # mysql was:
    #$sql_update="UPDATE ".$c->container_cfg_mysql_table." SET `leafvalue`='".addslashes($leafvalue)."', `timestamp`='".time()."' WHERE  `server`='$server' AND `group`='$group' AND `subgroup`='$subgroup' AND `leaf`='$leaf'";
    # GOLEMQUERY #3 
    $sql_update="UPDATE ".$c->container_cfg_oracle_table." SET leafvalue='$leafvalue', timestampg= trunc((to_number(cast((systimestamp AT TIME ZONE 'GMT') as date)-cast(TO_TIMESTAMP_TZ ('01-01-1970 00:00:00 GMT', 'DD-MM-YYYY HH24:MI:SS TZR') as date))*86400)) WHERE  server='$server' AND groupg='$group' AND subgroup='$subgroup' AND leaf='$leaf'";
    
    if (DEBUGSQL) error_log("sql_count $sql_count");
    $res = $this->_db->query($sql_count);
    if (DB::isError($res))
    {
 			error_log("sql_count error $sql_count " . $res->getMessage());
    }
    
    
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
    
/* mysql was:
    $res = mysql_query($sql_count, $db);
    $row = mysql_fetch_array($res, MYSQL_ASSOC);
*/    
    
    if( $row['C'] == 0 )
    {
      $res=$this->_db->query($sql_insert);
      if (DB::isError($res)) {  error_log("sql insert error: $sql_insert " . $res->getMessage()); }
      if (DEBUGSQL) error_log("sql_insert: $sql_insert");
      return 0; // value created
    }
    else
    {
      if ($sql_update != "")
      {
        $res=$this->_db->query($sql_update);
              if (DB::isError($res))
										{  error_log("sql update error: $sql_update " . $res->getMessage()); }
       if (DEBUGSQL) error_log("sql_update $sql_update");
      }
      return 1; // value overwritten
    }
  }

  
  function getMeta($group, $subgroup = null, $leaf = null, $withleafvalue = false)
  {
    $c =& pfcGlobalConfig::Instance();      

    $ret = array();
    $ret["timestamp"] = array();
    $ret["value"]     = array();
    
    $server = $c->serverid;    
    $db = $this->_connect();
    
    $sql_where = "";
    $sql_group_by = "";
    $value = "leafvalue";
    
    if ($group != NULL)
    {
      $sql_where   .= " AND groupg='$group'";
      $value        = "subgroup";        
      #$sql_group_by = "GROUP BY '$value'";
      $sql_group_by = "GROUP BY $value";
    }    
    
    if ($subgroup != NULL)
    {
      $sql_where   .= " AND subgroup='$subgroup'";
      $value        = "leaf";        
      $sql_group_by = "";
    }
    
    if ($leaf != NULL)
    {
      $sql_where   .= " AND leaf='$leaf'";
      $value        = "leafvalue";
      $sql_group_by = "";
    }
    
    # GOLEMQUERY #4 
    $sql_select="SELECT $value, timestampg FROM ".$c->container_cfg_oracle_table." WHERE server='$server' $sql_where $sql_group_by ORDER BY timestampg";    
    if ($sql_select != "")
    {
      $thisresult = $this->_db->query($sql_select);
          if (DEBUGSQL) error_log("sql_select: $sql_select");
          if (DB::isError($thisresult))   {  error_log("sql_select error $sql_select " . $thisresult->getMessage());         }
          	
          	
      #if (mysql_num_rows($thisresult))
      $this->_db->setOption('portability', DB_PORTABILITY_NUMROWS); 
      
      #error_log("numrows $numrows");
      
      if ($thisresult->numRows())
      {
        #while ($regel = mysql_fetch_array($thisresult))
        while ($regel = $thisresult->fetchRow(DB_FETCHMODE_ASSOC))
        {
          $ret["timestamp"][] = $regel["TIMESTAMPG"];
          if ($value == "leafvalue")
          {
            if ($withleafvalue)
              $ret["value"][]     = $regel[strtoupper($value)];
            else
              $ret["value"][]     = NULL;
          }
          else
            $ret["value"][] = $regel[strtoupper($value)];
        }
        
      }
      else
        return $ret;
    }
    return $ret;
  }


  function incMeta($group, $subgroup, $leaf)
  {
    $c =& pfcGlobalConfig::Instance();      
      
    $server = $c->serverid;    
    $db = $this->_connect();
    $time = time();

    // search for the existing leafvalue
    # GOLEMQUERY #5 
    $sql_count = "SELECT COUNT(*) AS C FROM ".$c->container_cfg_oracle_table." WHERE server='$server' AND groupg='$group' AND subgroup='$subgroup' AND leaf='$leaf' and rownum <= 1";
    $res = $this->_db->query($sql_count);
    if (DB::isError($res))   {  error_log("sql_count error $sql_count " . $res->getMessage());         }
    if (DEBUGSQL) error_log("sql select $sql_count");
    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
    #$res = mysql_query($sql_count, $db);
    #$row = mysql_fetch_array($res, MYSQL_ASSOC);
    if( $row['C'] == 0 )
    {
      $leafvalue = 1;
      #$sql_insert="REPLACE INTO ".$c->container_cfg_mysql_table." (`server`, `group`, `subgroup`, `leaf`, `leafvalue`, `timestamp`) VALUES('$server', '$group', '$subgroup', '$leaf', '".$leafvalue."', '".$time."')";
      # GOLEMQUERY # 6
      $sql_insert="INSERT INTO ".$c->container_cfg_oracle_table." (server, groupg, subgroup, leaf, leafvalue, timestampg) VALUES('$server', '$group', '$subgroup', '$leaf','$leafvalue', trunc((to_number(cast((systimestamp AT TIME ZONE 'GMT') as date)-cast(TO_TIMESTAMP_TZ ('01-01-1970 00:00:00 GMT', 'DD-MM-YYYY HH24:MI:SS TZR') as date))*86400)))";
      
      #mysql_query($sql_insert, $db);
      $res=$this->_db->query($sql_insert);
      if (DB::isError($res)){ error_log("sql insert error $sql_insert " . $res->getMessage()); }
     if (DEBUGSQL) error_log("sql_insert $sql_insert");
    }
    else
    {
      # mysql was:
      #$sql_update="UPDATE ".$c->container_cfg_mysql_table." SET leafvalue= LAST_INSERT_ID( leafvalue + 1 ), `timestamp`='".$time."' WHERE  server='$server' AND groupg='$group' AND subgroup='$subgroup' AND leaf='$leaf'";
      # GOLEMQUERY #7
      # test using sequence nextval
      $sql_update="UPDATE ".$c->container_cfg_oracle_table." SET leafvalue= phpfreechat_leafvalue_seq.NEXTVAL, timestampg=trunc((to_number(cast((systimestamp AT TIME ZONE 'GMT') as date)-cast(TO_TIMESTAMP_TZ ('01-01-1970 00:00:00 GMT', 'DD-MM-YYYY HH24:MI:SS TZR') as date))*86400)) WHERE  server='$server' AND groupg='$group' AND subgroup='$subgroup' AND leaf='$leaf'";
      
      $res=$this->_db->query($sql_update);
      if (DB::isError($res)){ error_log("problema update: $sql_update " . $res->getMessage()); }
	if (DEBUGSQL) error_log("sql_update $sql_update");      
      # 
      # GOLEMQUERY #8 
      # test using sequence currval
      $sql_last="SELECT phpfreechat_leafvalue_seq.currVAL as lastleaf FROM dual";
      $res = $this->_db->query($sql_last);
      if (DB::isError($res))   {  error_log("error in SELECT lastleaf $sql_last" . $res->getMessage());         }      
       if (DEBUGSQL) error_log("select: SELECT phpfreechat_leafvalue_seq.currVAL as lastleaf FROM dual");
      #$row = mysql_fetch_array($res, MYSQL_ASSOC);
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $leafvalue = $row['LASTLEAF'];
    }
    
    $ret["value"][]     = $leafvalue;
    $ret["timestamp"][] = $time;

    return $ret;
  }


  function rmMeta($group, $subgroup = null, $leaf = null)
  {
    $c =& pfcGlobalConfig::Instance();      
    
    $server = $c->serverid;    
    $db = $this->_connect();
    # GOLEMQUERY #9 
    $sql_delete = "DELETE FROM ".$c->container_cfg_oracle_table." WHERE server='$server'";
    
    if($group != NULL)
      $sql_delete .= " AND groupg='$group'";
    
    if($subgroup != NULL)
      $sql_delete .= " AND subgroup='$subgroup'";

    if ($leaf != NULL)
      $sql_delete .= " AND leaf='$leaf'";
    
    #mysql_query($sql_delete, $db);
    $res=$this->_db->query($sql_delete);
    if (DB::isError($res))
          { error_log('sql_delete $sql_delete ' . $res->getMessage()); }

    if (DEBUGSQL) error_log("sql_delete $sql_delete");
    
    return true;
  }

  function encode($str)
  {
    return $str;
    //return addslashes(urlencode($str));
  }
  
  function decode($str)
  {
  	return $str;
    //return urldecode(stripslashes($str));
  }
  
 
  
}

?>
