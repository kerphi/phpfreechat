<?php
/**
 * src/container/mysql.class.php
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

require_once dirname(__FILE__)."/../pfccontainer.class.php";

/**
 * pfcContainer_Mysql is a concret container which store data into mysql
 *
 * Because of the new storage functions (setMeta, getMeta, rmMeta) 
 * everything can be stored in just one single table.
 * Using type "HEAP" or "MEMORY" mysql loads this table into memory making it very fast
 * There is no routine to create the table if it does not exists so you have to create it by hand
 * Replace the database login info at the top of pfcContainer_mysql class with your own 
 * You also need some config lines in your chat index file:
 * $params["container_type"] = "mysql";
 * $params["container_cfg_mysql_host"] = "localhost";
 * $params["container_cfg_mysql_port"] = 3306; 
 * $params["container_cfg_mysql_database"] = "phpfreechat"; 
 * $params["container_cfg_mysql_table"] = "phpfreechat"; 
 * $params["container_cfg_mysql_username"] = "root"; 
 * $params["container_cfg_mysql_password"] = ""; 
 * 
 * Advanced parameters are :
 * $params["container_cfg_mysql_fieldtype_server"] = 'varchar(32)';
 * $params["container_cfg_mysql_fieldtype_group"] = 'varchar(64)';
 * $params["container_cfg_mysql_fieldtype_subgroup"] = 'varchar(64)';
 * $params["container_cfg_mysql_fieldtype_leaf"] = 'varchar(64)';
 * $params["container_cfg_mysql_fieldtype_leafvalue"] = 'text';
 * $params["container_cfg_mysql_fieldtype_timestamp"] = 'int(11)';
 * $params["container_cfg_mysql_engine"] = 'InnoDB';
 * 
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 * @author HenkBB
 */
class pfcContainer_Mysql extends pfcContainerInterface
{
  var $_db = null;
  var $_sql_create_table = "
 CREATE TABLE IF NOT EXISTS `%table%` (
  `server` %fieldtype_server% NOT NULL default '',
  `group` %fieldtype_group% NOT NULL default '',
  `subgroup` %fieldtype_subgroup% NOT NULL default '',
  `leaf` %fieldtype_leaf% NOT NULL default '',
  `leafvalue` %fieldtype_leafvalue% NOT NULL,
  `timestamp` %fieldtype_timestamp% NOT NULL default 0,
  PRIMARY KEY  (`server`,`group`,`subgroup`,`leaf`),
  INDEX (`server`,`group`,`subgroup`,`timestamp`)
) ENGINE=%engine%;";
    
  function pfcContainer_Mysql()
  {
    pfcContainerInterface::pfcContainerInterface();
  }

  function getDefaultConfig()
  {   
    $cfg = pfcContainerInterface::getDefaultConfig();
    $cfg["mysql_host"] = 'localhost';
    $cfg["mysql_port"] = 3306;
    $cfg["mysql_database"] = 'phpfreechat';
    $cfg["mysql_table"]    = 'phpfreechat';
    $cfg["mysql_username"] = 'root';
    $cfg["mysql_password"] = '';
    // advanced parameters (don't touch if you don't know what your are doing)
    $cfg["mysql_fieldtype_server"] = 'varchar(32)';
    $cfg["mysql_fieldtype_group"] = 'varchar(64)';
    $cfg["mysql_fieldtype_subgroup"] = 'varchar(128)';
    $cfg["mysql_fieldtype_leaf"] = 'varchar(128)';
    $cfg["mysql_fieldtype_leafvalue"] = 'text';
    $cfg["mysql_fieldtype_timestamp"] = 'int(11)';
    $cfg["mysql_engine"] = 'InnoDB';    
    return $cfg;
  }

  function init(&$c)
  {
    $errors = pfcContainerInterface::init($c);

    // connect to the db
    $db = $this->_connect($c);
    if ($db === FALSE)
    {
      $errors[] = _pfc("Mysql container: connect error");
      return $errors;
    }

    // create the db if it doesn't exists
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
  }

  function _connect($c = null)
  {
    if (!$this->_db)
    {
      if ($c == null) $c =& pfcGlobalConfig::Instance();
      $this->_db = mysql_pconnect($c->container_cfg_mysql_host.':'.$c->container_cfg_mysql_port,
                                  $c->container_cfg_mysql_username,
                                  $c->container_cfg_mysql_password);
      mysql_select_db($c->container_cfg_mysql_database, $this->_db);
    }
    return $this->_db;
  }

  function setMeta($group, $subgroup, $leaf, $leafvalue = NULL)
  {
    $c =& pfcGlobalConfig::Instance();      
      
    $server = $c->serverid;    
    $db = $this->_connect();

    if ($leafvalue == NULL){$leafvalue="";};
    
    $sql_count = "SELECT COUNT(*) AS C FROM ".$c->container_cfg_mysql_table." WHERE `server`='$server' AND `group`='$group' AND `subgroup`='$subgroup' AND `leaf`='$leaf' LIMIT 1";
    $sql_insert="REPLACE INTO ".$c->container_cfg_mysql_table." (`server`, `group`, `subgroup`, `leaf`, `leafvalue`, `timestamp`) VALUES('$server', '$group', '$subgroup', '$leaf', '".addslashes($leafvalue)."', '".time()."')";
    $sql_update="UPDATE ".$c->container_cfg_mysql_table." SET `leafvalue`='".addslashes($leafvalue)."', `timestamp`='".time()."' WHERE  `server`='$server' AND `group`='$group' AND `subgroup`='$subgroup' AND `leaf`='$leaf'";

    $res = mysql_query($sql_count, $db);
    $row = mysql_fetch_array($res, MYSQL_ASSOC);
    if( $row['C'] == 0 )
    {
      mysql_query($sql_insert, $db);
      return 0; // value created
    }
    else
    {
      if ($sql_update != "")
      {
        mysql_query($sql_update, $db);
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
      $sql_where   .= " AND `group`='$group'";
      $value        = "subgroup";        
      $sql_group_by = "GROUP BY `$value`";
    }    
    
    if ($subgroup != NULL)
    {
      $sql_where   .= " AND `subgroup`='$subgroup'";
      $value        = "leaf";        
      $sql_group_by = "";
    }
    
    if ($leaf != NULL)
    {
      $sql_where   .= " AND `leaf`='$leaf'";
      $value        = "leafvalue";
      $sql_group_by = "";
    }
    
    $sql_select="SELECT `$value`, `timestamp` FROM ".$c->container_cfg_mysql_table." WHERE `server`='$server' $sql_where $sql_group_by ORDER BY timestamp";    
    if ($sql_select != "")
    {
      $thisresult = mysql_query($sql_select, $db);
      if (mysql_num_rows($thisresult))
      {
        while ($regel = mysql_fetch_array($thisresult))
        {
          $ret["timestamp"][] = $regel["timestamp"];
          if ($value == "leafvalue")
          {
            if ($withleafvalue)
              $ret["value"][]     = $regel[$value];
            else
              $ret["value"][]     = NULL;
          }
          else
            $ret["value"][] = $regel[$value];
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
    $sql_count = "SELECT COUNT(*) AS C FROM ".$c->container_cfg_mysql_table." WHERE `server`='$server' AND `group`='$group' AND `subgroup`='$subgroup' AND `leaf`='$leaf' LIMIT 1";
    $res = mysql_query($sql_count, $db);
    $row = mysql_fetch_array($res, MYSQL_ASSOC);
    if( $row['C'] == 0 )
    {
      $leafvalue = 1;
      $sql_insert="REPLACE INTO ".$c->container_cfg_mysql_table." (`server`, `group`, `subgroup`, `leaf`, `leafvalue`, `timestamp`) VALUES('$server', '$group', '$subgroup', '$leaf', '".$leafvalue."', '".$time."')";
      mysql_query($sql_insert, $db);
    }
    else
    {
      $sql_update="UPDATE ".$c->container_cfg_mysql_table." SET `leafvalue`= LAST_INSERT_ID( leafvalue + 1 ), `timestamp`='".$time."' WHERE  `server`='$server' AND `group`='$group' AND `subgroup`='$subgroup' AND `leaf`='$leaf'";
      mysql_query($sql_update, $db);
      $res = mysql_query('SELECT LAST_INSERT_ID();', $db);      
      $row = mysql_fetch_array($res, MYSQL_ASSOC);
      $leafvalue = $row['LAST_INSERT_ID()'];
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
    
    $sql_delete = "DELETE FROM ".$c->container_cfg_mysql_table." WHERE `server`='$server'";
    
    if($group != NULL)
      $sql_delete .= " AND `group`='$group'";
    
    if($subgroup != NULL)
      $sql_delete .= " AND `subgroup`='$subgroup'";

    if ($leaf != NULL)
      $sql_delete .= " AND `leaf`='$leaf'";
    
    mysql_query($sql_delete, $db);
    return true;
  }

  function encode($str)
  {
    return addslashes(urlencode($str));
  }
  
  function decode($str)
  {
    return urldecode(stripslashes($str));
  }
  
}

?>
