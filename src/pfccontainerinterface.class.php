<?php
/**
 * pfccontainerinterface.class.php
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
 * pfcContainerInterface is an interface implemented by pfcContainer and each pfcContainer concrete isntances (File,Mysql...)
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 * @abstract
 */
class pfcContainerInterface
{
  function pfcContainerInterface() { }
  function getDefaultConfig()      { return array(); }
  function init(&$c)               { return array(); }

  /**
   * Write a meta data value identified by a group / subgroup / leaf [with a value]
   * As an example in the default file container this  arborescent structure is modelised by simple directories
   * group1/subgroup1/leaf1
   *                 /leaf2
   *       /subgroup2/...
   * Each leaf can contain or not a value.
   * However each leaf and each group/subgroup must store the lastmodified time (timestamp).
   * @param $group root arborescent element
   * @param $subgroup is the root first child which contains leafs
   * @param $leaf is the only element which can contain values
   * @param $leafvalue NULL means the leaf will not contain any values
   * @return 1 if the old leaf has been overwritten, 0 if a new leaf has been created
   */
  function setMeta($group, $subgroup, $leaf, $leafvalue = NULL)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  
  /**
   * Read meta data identified by a group [/ subgroup [/ leaf]]
   * @param $group is mandatory, it's the arborescence's root
   * @param $subgroup if null then the subgroup list names are returned
   * @param $leaf if null then the leaf names are returned
   * @param $withleafvalue if set to true the leaf value will be returned
   * @return array which contains two subarray 'timestamp' and 'value'
   */
  function getMeta($group, $subgroup = null, $leaf = null, $withleafvalue = false)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  

  /**
   * Remove a meta data or a group of metadata
   * @param $group if null then it will remove all the possible groups (all the created metadata)
   * @param $subgroup if null then it will remove the $group's childs (all the subgroup contained by $group)
   * @param $leaf if null then it will remove all the $subgroup's childs (all the leafs contained by $subgroup)
   * @return true on success, false on error
   */
  function rmMeta($group, $subgroup = null, $leaf = null)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  
  
  /**
   * In the default File container: used to encode UTF8 strings to ASCII filenames
   * This method can be overridden by the concrete container
   */  
  function encode($str)
  {
    return $str;
  }
  
  /**
   * In the default File container: used to decode ASCII filenames to UTF8 strings
   * This method can be overridden by the concrete container
   */  
  function decode($str)
  {
    return $str;
  }  
}

?>