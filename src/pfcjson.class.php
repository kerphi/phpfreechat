<?php
/**
 * pfcjson.class.php
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

class pfcJSON
{
  var $json = null;
  
  function pfcJSON()
  {
    // if the php5-json module is not available, use a software json implementation
    if (!function_exists('json_encode')) {
      if (!class_exists('Services_JSON'))
        require_once(dirname(__FILE__)."/../lib/json/JSON.php");
      $this->json = new Services_JSON();
    }
  }
  
  function encode($v)
  {
    if ($this->json)
      return $this->json->encode($v);
    else
      return json_encode($v);
  }

  function decode($v)
  {
    if ($this->json)
      return $this->json->decode($v);
    else
      return json_decode($v);
  }
}

?>