<?php
/**
 * pfccommand.class.php
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
require_once dirname(__FILE__)."/phpfreechati18n.class.php";
require_once dirname(__FILE__)."/pfcuserconfig.class.php";

/**
 * pfcCommand is an abstract class (interface) which must be inherited by each concrete commands
 * Commands examples : /nick /me /update ...
 *
 * @example ../demo/demo27_customized_command.php
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcCommand
{
  /**
   * Command name (lowercase)
   */
  var $name;
  
  /**
   * Not used for now
   */
  var $desc;
  var $help;

  /**
   * This is the pfcGlobalConfig instance
   */
  var $c;
  
  /**
   * This is the pfcUserConfig instance
   */
  var $u;

  /**
   * Used to instanciate a command
   * $tag is the command name : "nick", "me", "update" ...
   */
  function &Factory($name)
  {
    $cmd = NULL;
    $name      = strtolower($name);
    $classname = "pfcCommand_".$name;
    $filename = dirname(__FILE__)."/commands/".$name.".class.php";
    require_once($filename);
    if(class_exists($classname))
    {
      $cmd =& new $classname();
      $cmd->name = $name;
    }
    return $cmd;
  }

  /**
   * Constructor
   * @private
   */
  function pfcCommand()
  {
    $this->c =& pfcGlobalConfig::Instance();
    $this->u =& pfcUserConfig::Instance();
  }

  /**
   * Virtual methode which must be implemented by concrete commands
   * It is called by the phpFreeChat::HandleRequest function to execute the wanted command
   */
  function run(&$xml_reponse, $clientid, &$param, &$sender, &$recipient, &$recipientid)
  {
    die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__));
  }
}

?>
