<?php
/**
 * phpfreechat.class.php
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

/**
 * pfcResponse is used to build each ajax response
 * Commands stack contains characteres strings with javascript instructions.
 */
class pfcResponse
{
  var $_commands = array();

  function pfcResponse()
  {
  }
  
  function remove($id)
  {
    $this->_commands[] = '$(\''.$id.'\').remove();';
  }
  
  function update($id,$data)
  {
    $data = preg_replace("/'/","\'",$data);
    $data = preg_replace("/[\n\r]/","",$data);      
    $data = preg_replace("/\s*</"," <",$data);      
    $data = preg_replace("/>\s*/","> ",$data);      
    $this->_commands[] = '$(\''.$id.'\').update(\''.$data.'\');';
  }
  
  function script($js)
  {
    $this->_commands[] = $js;
  }

  function redirect($url)
  {
    $this->script('window.location = "'.$url.'";');
  }
  
  function getCommandCount()
  {
    return count($this->_commands);
  }
    
  function getOutput()
  {
    return implode("\n",$this->_commands);
  }
}
?>