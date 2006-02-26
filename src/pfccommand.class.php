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
   * Not used for now
   */
  var $name;
  var $desc;
  var $help;

  /**
   * This is the phpFreeChatConfig instance
   */
  var $c;

  /**
   * Used to instanciate a command
   * $tag is the command name : "nick", "me", "update" ...
   */
  function &Factory($tag, &$config)
  {
    $cmd = NULL;
    $classname = "pfcCommand_".strtolower($tag);
    if(!class_exists($classname) &&
       file_exists(dirname(__FILE__)."/".strtolower($classname).".class.php"))
      require_once(dirname(__FILE__)."/".strtolower($classname).".class.php");
    if(class_exists($classname))
      $cmd =& new $classname($config);
    return $cmd;
  }

  /**
   * Default constructor
   */
  function pfcCommand(&$config)
  {
    $this->c =& $config;
  }

  /**
   * Virtual methode which must be implemented by concrete commands
   * It is called by the phpFreeChat::HandleRequest function to execute the wanted command
   */
  function run(&$xml_reponse, $clientid, $param = "")
  {
    die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__));
  }
}

?>