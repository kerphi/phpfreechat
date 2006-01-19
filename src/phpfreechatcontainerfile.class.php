<?php
/**
 * phpfreechatcontainerfile.class.php
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

require_once dirname(__FILE__)."/phpfreechatcontainer.class.php";

/**
 * phpFreeChatContainerFile is a concret container which stock data into files
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChatContainerFile extends phpFreeChatContainer
{
  var $_users = NULL;

  function getDefaultConfig()
  {
    $c =& $this->c;
    
    $cfg = array();
    $cfg["chat_dir"]            = $c->data_private."/chat/".$c->channel."/";
    $cfg["data_file"]           = $cfg["chat_dir"]."messages.data";
    $cfg["index_file"]          = $cfg["chat_dir"]."messages.index";
    $cfg["nickname_dir"]        = $cfg["chat_dir"]."nicknames/";
    return $cfg;
  }
  
  function init()
  {
    $c =& $this->c;
    $errors = array();
    $ok = true;
    
    // ---
    // test message file
    if (!is_dir(dirname($c->container_cfg_data_file)))
      @phpFreeChatTools::RecursiveMkdir(dirname($c->container_cfg_data_file));
    if ($ok && !is_dir(dirname($c->container_cfg_data_file)))
    {
      $ok = false;
      $errors[] = dirname($c->container_cfg_data_file)." can't be created";
    }
    if ($ok && !is_writable(dirname($c->container_cfg_data_file)))
    {
      $ok = false;
      $errors[] = dirname($c->container_cfg_data_file)." is not writable";
    }
    if ($ok && !file_exists($c->container_cfg_data_file))
      @touch($c->container_cfg_data_file);
    if ($ok && !file_exists($c->container_cfg_data_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_data_file." can't be created";
    }
    if ($ok && !is_readable($c->container_cfg_data_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_data_file." is not readable";
    }
    if ($ok && !is_writeable($c->container_cfg_data_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_data_file." is not writeable";
    }
    
    // ---
    // file index test
    if (!is_dir(dirname($c->container_cfg_index_file)))
      @phpFreeChatTools::RecursiveMkdir(dirname($c->container_cfg_index_file));
    if ($ok && !is_dir(dirname($c->container_cfg_index_file)))
    {
      $ok = false;
      $errors[] = dirname($c->container_cfg_index_file)." can't be created";
    }
    if ($ok && !is_writable(dirname($c->container_cfg_index_file)))
    {
      $ok = false;
      $errors[] = dirname($c->container_cfg_index_file)." is not writable";
    }    
    if ($ok && !file_exists($c->container_cfg_index_file))
      @touch($c->container_cfg_index_file);      
    if ($ok && !file_exists($c->container_cfg_index_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_index_file." can't be created";
    }
    if ($ok && !is_readable($c->container_cfg_index_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_index_file." is not readable";
    }
    if ($ok && !is_writeable($c->container_cfg_index_file))
    {
      $ok = false;
      $errors[] = $c->container_cfg_index_file." is not writeable";
    }
    if ($ok && filesize($c->container_cfg_index_file) == 0)
    {
      $fp = fopen($c->container_cfg_index_file, "w");
      fwrite($fp, "0");
      fclose($fp);
    }

    // ---
    // file nickname directory
    if (!is_dir($c->container_cfg_nickname_dir))
      @phpFreeChatTools::RecursiveMkdir($c->container_cfg_nickname_dir);
    if ($ok && !is_dir($c->container_cfg_nickname_dir))
    {
      $ok = false;
      $errors[] = $c->container_cfg_nickname_dir." can't be created";
    }
    if ($ok && !is_writable($c->container_cfg_nickname_dir))
    {
      $ok = false;
      $errors[] = $c->container_cfg_nickname_dir." is not writable";
    }
    
    return $errors;
  }
  
  function updateNick($nickname)
  {
    $c =& $this->c;

    // update my online status file
    $my_filename = $c->container_cfg_nickname_dir.$this->_encode($c->nick);
    touch($my_filename);
    
    if ($c->skip_check && !file_exists($my_filename))
      return false;
    else
      return true;
  }

  /**
   * returns the id identifying the nickname's owner session
   */
  function getNickId($nickname)
  {
    $c =& $this->c;
    $nickid = "undefined";
    $myfilename = $c->container_cfg_nickname_dir.$this->_encode($nickname);
    if (file_exists($myfilename) && filesize($myfilename)>0)
    {
      // write the nickid into the new nickname file
      $fp = fopen($myfilename, "r");
      $nickid = fread($fp, filesize($myfilename));
      if ($nickid == "") $nickid = "undefined";
      fclose($fp);
    }
    //if ($c->debug) pxlog("getNickId[".$c->sessionid."]: nickname=".$nickname." nickid=".$nickid, "chat", $c->id);
    return $nickid;
  }

  /**
   * create a file containing the new nickname id
   * and delete oldnickname file if the nickname id match (oldnickname file is mine)
   */
  function changeNick($newnick, $oldnickid = "")
  {
    $c =& $this->c;
    $nickid = $c->sessionid;
    // delete the old nickname file only if the nickid match
    // ie: do not delete nickname if it's not mine
    if ($oldnickid == "") $oldnickid = $this->getNickId($c->nick);
    if ($nickid == $oldnickid)
      unlink($c->container_cfg_nickname_dir.$this->_encode($c->nick));
    
    // write the nickid into the new nickname file
    $fp = fopen($c->container_cfg_nickname_dir.$this->_encode($newnick),"w");
    flock ($fp, LOCK_EX); // lock
    fwrite($fp, $nickid);
    flock ($fp, LOCK_UN); // unlock
    fclose($fp);

    return $newnick;
  }
  
  function removeNick($nick)
  {
    $c =& $this->c;
    $nick_filename = $c->container_cfg_nickname_dir.$this->_encode($nick);
    $nickid = $this->getNickId($nick);
    // don't allow to remove foreign nicknames
    if ( $c->sessionid == $nickid &&
         file_exists($nick_filename) )
    {
      unlink($nick_filename);
      return true;
    }
    else
      return false;
  }
    
  function removeObsoletNick()
  {
    $c =& $this->c;
    
    $deleted_user = array();
    $users = array();
    $dir_handle = opendir($c->container_cfg_nickname_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      if (time() > (filemtime($c->container_cfg_nickname_dir.$file)+($c->refresh_delay/1000)*4) ) // user will be disconnected after refresh_delay*4 secondes of inactivity
      {
        $deleted_user[] = $this->_decode($file);
        unlink($c->container_cfg_nickname_dir.$file); // disconnect expired user
      }
      else
      {
        // optimisation: cache user list for next getOnlineUserList call
        $users[] = $this->_decode($file);
      }
    }
    $this->_users =& $users; // _users will be used by getOnlineUserList
    return $deleted_user;
  }
  
  function getOnlineNick()
  {
    if (is_array($this->_users))
      return $this->_users;

    $c =& $this->c;
    $users = array();
    $dir_handle = opendir($c->container_cfg_nickname_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      $users[] = $this->_decode($file);
    }
    return $users;
  }
   
  /**
   * @todo use one file (filename = msgid) for one message
   */
  function readNewMsg($from_id)
  {
    $c =& $this->c;
    
    // load message from file and truncate it if necessary
    $content = file($c->container_cfg_data_file);
  	
    // remove old messages
    $content = array_slice($content, -$c->max_msg);
    // save the new content (with removed old messages)
    $content_save = implode("\n", $content);
    $content_save = str_replace("\n\n","\n",$content_save);
    $fp = fopen($c->container_cfg_data_file,"w+");
    flock ($fp, LOCK_EX); // lock
    fwrite($fp, $content_save);
    flock ($fp, LOCK_UN); // unlock
    fclose($fp);
  	  	
    // format content in order to extract only necessary information
    $formated_content = array();
    $new_from_id = $from_id;
    foreach ( $content as $line )
    {
      if ($line != "" && $line != "\n")
      {
        $formated_line = explode( "\t", $line );
        if ($from_id < $formated_line[0])
          $formated_content[] = $formated_line;
        if ($new_from_id < $formated_line[0])
          $new_from_id = $formated_line[0];
      }
    }

    return array("messages" => $formated_content,
                 "new_from_id" => $new_from_id );
  }
  
  function writeMsg($nickname, $message)
  {            
    $c =& $this->c;
    
    // write message to file
    $fp = fopen($c->container_cfg_data_file, "a+");
    flock ($fp, LOCK_EX); // lock
    
    // format message
    $msg_id = $this->_requestMsgId();
    $line .= "\n";
    $line .= $msg_id."\t";
    $line .= date("d/m/Y")."\t";
    $line .= date("H:i:s")."\t";
    $line .= $nickname."\t";
    $line .= $message;
		
    // write it to file
    fwrite($fp, $line);
    flock ($fp, LOCK_UN); // unlock
    fclose($fp);
  }
  /**
   * used internaly
   */ 
  function _requestMsgId()
  {
    $c =& $this->c;
    
    // read last message id
    $msg_id = 0;
    $fp = fopen($c->container_cfg_index_file, "rw+");
    if (is_resource($fp))
    {
      flock ($fp, LOCK_EX);
      $msg_id = fread($fp, filesize($c->container_cfg_index_file));
      if (!is_numeric($msg_id)) $msg_id = 0;
      // increment message id and save it
      $msg_id++;
      ftruncate($fp, 0);
      fseek($fp, 0);
      fwrite($fp, $msg_id);
      flock ($fp, LOCK_UN);
      fclose($fp);
    }

    return $msg_id;
  }
  
  function _encode($str)
  {
    return base64_encode($str);
  }
  
  function _decode($str)
  {
    return base64_decode($str);
  }
}

?>
